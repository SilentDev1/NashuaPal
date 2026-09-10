<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Pulls upcoming events from one or more public Google Calendar ICS feeds and
 * normalizes them into the same shape as the CPT events. Cached in a transient
 * so we don't fetch on every page load.
 *
 * Get a calendar's ICS URL from Google Calendar:
 *   Calendar settings → Integrate calendar → "Public address in iCal format".
 * The calendar must be set to public (or shared with "Anyone with the link").
 */
final class NPAL_Google_Calendar {

	const CACHE_KEY      = 'npal_gcal_events_v1';
	const CACHE_DURATION = 900; // 15 minutes
	const FETCH_TIMEOUT  = 8;
	const MAX_PER_FEED   = 50;

	/**
	 * Returns upcoming events from all configured Google Calendars.
	 * Each event: ['title','start_ts','end_ts','location','description','url','source'].
	 */
	public static function get_upcoming( $limit = 20 ) {
		$urls = self::get_feed_urls();
		if ( empty( $urls ) ) {
			return array();
		}

		$cache_key = self::CACHE_KEY . '_' . md5( implode( '|', $urls ) );
		$cached    = get_transient( $cache_key );
		if ( false !== $cached && is_array( $cached ) ) {
			return array_slice( $cached, 0, $limit );
		}

		$all = array();
		foreach ( $urls as $url ) {
			$events = self::fetch_and_parse( $url );
			$all    = array_merge( $all, $events );
		}

		// Filter to upcoming only and sort.
		$now = time();
		$all = array_values( array_filter( $all, function( $e ) use ( $now ) {
			return ! empty( $e['start_ts'] ) && $e['start_ts'] >= $now;
		} ) );
		usort( $all, function( $a, $b ) { return $a['start_ts'] <=> $b['start_ts']; } );

		set_transient( $cache_key, $all, self::CACHE_DURATION );
		return array_slice( $all, 0, $limit );
	}

	public static function clear_cache() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			'_transient_' . self::CACHE_KEY . '_%',
			'_transient_timeout_' . self::CACHE_KEY . '_%'
		) );
	}

	private static function get_feed_urls() {
		$raw = NPAL_Plugin::get_setting( 'google_calendar_urls', '' );
		if ( ! $raw ) { return array(); }
		$lines = preg_split( '/[\r\n]+/', trim( $raw ) );
		$urls  = array();
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( $line && filter_var( $line, FILTER_VALIDATE_URL ) ) {
				// Convert webcal:// to https:// (Google sometimes provides webcal links).
				$line   = preg_replace( '#^webcal://#i', 'https://', $line );
				$urls[] = $line;
			}
		}
		return array_unique( $urls );
	}

	private static function fetch_and_parse( $url ) {
		$response = wp_remote_get( $url, array(
			'timeout'    => self::FETCH_TIMEOUT,
			'user-agent' => 'NashuaPal/1.0 (WordPress)',
		) );
		if ( is_wp_error( $response ) ) {
			return array();
		}
		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );
		if ( 200 !== $code || ! $body ) {
			return array();
		}
		return self::parse_ics( $body, $url );
	}

	/**
	 * Minimal iCalendar parser — handles VEVENT blocks, line continuation,
	 * basic escape sequences, and DTSTART variations (UTC, TZID, date-only).
	 * Skips recurring events (RRULE) — for a nonprofit's needs the simple
	 * single-occurrence case is sufficient.
	 */
	public static function parse_ics( $ics, $source_url = '' ) {
		// Unfold continuation lines: per RFC5545, lines starting with whitespace
		// continue the previous line.
		$ics = preg_replace( "/\r?\n[ \t]/", '', $ics );
		$lines = preg_split( "/\r?\n/", $ics );

		$events = array();
		$in_event = false;
		$current  = array();

		foreach ( $lines as $line ) {
			if ( $line === 'BEGIN:VEVENT' ) {
				$in_event = true;
				$current  = array();
				continue;
			}
			if ( $line === 'END:VEVENT' ) {
				$in_event = false;
				if ( ! empty( $current ) ) {
					$events[] = $current;
				}
				continue;
			}
			if ( ! $in_event || strpos( $line, ':' ) === false ) { continue; }

			list( $name_part, $value ) = explode( ':', $line, 2 );

			// Strip parameters (everything after ;) but remember TZID if present.
			$tzid = '';
			if ( strpos( $name_part, ';' ) !== false ) {
				$bits     = explode( ';', $name_part );
				$name_part = $bits[0];
				foreach ( array_slice( $bits, 1 ) as $param ) {
					if ( stripos( $param, 'TZID=' ) === 0 ) {
						$tzid = substr( $param, 5 );
					}
				}
			}
			$name = strtoupper( trim( $name_part ) );

			switch ( $name ) {
				case 'SUMMARY':
					$current['title'] = self::unescape_text( $value );
					break;
				case 'DESCRIPTION':
					$current['description'] = self::unescape_text( $value );
					break;
				case 'LOCATION':
					$current['location'] = self::unescape_text( $value );
					break;
				case 'URL':
					$current['url'] = trim( $value );
					break;
				case 'UID':
					$current['uid'] = trim( $value );
					break;
				case 'DTSTART':
					$current['start_ts'] = self::parse_dt( $value, $tzid );
					break;
				case 'DTEND':
					$current['end_ts'] = self::parse_dt( $value, $tzid );
					break;
				case 'RRULE':
					// Mark as recurring; we render only the first occurrence (DTSTART).
					$current['recurring'] = true;
					break;
			}
		}

		// Normalize / fill in defaults.
		$out = array();
		foreach ( $events as $e ) {
			if ( empty( $e['start_ts'] ) || empty( $e['title'] ) ) { continue; }
			$out[] = array(
				'title'       => $e['title'],
				'start_ts'    => $e['start_ts'],
				'end_ts'      => $e['end_ts'] ?? 0,
				'location'    => $e['location'] ?? '',
				'description' => $e['description'] ?? '',
				'url'         => $e['url'] ?? '',
				'source'      => 'gcal',
				'source_url'  => $source_url,
			);
		}
		return array_slice( $out, 0, self::MAX_PER_FEED );
	}

	private static function parse_dt( $value, $tzid = '' ) {
		$value = trim( $value );
		if ( ! $value ) { return 0; }

		// Date-only form: YYYYMMDD
		if ( preg_match( '/^\d{8}$/', $value ) ) {
			$y = substr( $value, 0, 4 );
			$m = substr( $value, 4, 2 );
			$d = substr( $value, 6, 2 );
			return mktime( 0, 0, 0, (int) $m, (int) $d, (int) $y );
		}

		// Datetime form: YYYYMMDDTHHMMSS[Z]
		if ( preg_match( '/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})(Z)?$/', $value, $m ) ) {
			$is_utc = ! empty( $m[7] );
			$ts     = gmmktime( (int) $m[4], (int) $m[5], (int) $m[6], (int) $m[2], (int) $m[3], (int) $m[1] );
			if ( $is_utc ) {
				return $ts;
			}
			// Floating or TZID. Convert to UTC using the TZID, fall back to WP timezone.
			$tz = null;
			if ( $tzid ) {
				try { $tz = new DateTimeZone( $tzid ); } catch ( \Exception $e ) { $tz = null; }
			}
			if ( ! $tz ) {
				$tz = wp_timezone();
			}
			$dt = DateTime::createFromFormat( 'YmdHis', sprintf( '%s%s%sT%s%s%s', $m[1], $m[2], $m[3], $m[4], $m[5], $m[6] ), $tz );
			if ( $dt instanceof DateTime ) {
				return $dt->getTimestamp();
			}
			return $ts; // last-resort
		}

		// Fallback: let strtotime try.
		$ts = strtotime( $value );
		return $ts ? $ts : 0;
	}

	private static function unescape_text( $value ) {
		// RFC5545 escape sequences in text values.
		$value = str_replace( array( '\\n', '\\N' ), "\n", $value );
		$value = str_replace( array( '\\,', '\\;', '\\\\' ), array( ',', ';', '\\' ), $value );
		return trim( $value );
	}
}
