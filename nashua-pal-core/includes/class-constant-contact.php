<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Constant Contact v3 API integration.
 *
 * Fetches sent email campaigns and upserts them as pal_newsletter CPT posts
 * so they appear in the [pal_latest_news] shortcode alongside blog posts.
 *
 * Requires OAuth2 credentials stored in Nashua PAL → Settings → Newsletter:
 *   - cc_api_key       (Client ID)
 *   - cc_api_secret    (Client Secret)
 *   - cc_access_token  (Bearer token — auto-refreshed)
 *   - cc_refresh_token (Used to obtain new access tokens)
 *
 * @package NashuaPalCore
 * @since   1.14.0
 */
final class NPAL_Constant_Contact {

	const CACHE_KEY      = 'npal_cc_newsletters_v1';
	const CACHE_DURATION = 43200; // 12 hours
	const FETCH_TIMEOUT  = 15;
	const API_BASE       = 'https://api.cc.email/v3';
	const TOKEN_URL      = 'https://authz.constantcontact.com/oauth2/default/v1/token';
	const AUTHORIZE_URL  = 'https://authz.constantcontact.com/oauth2/default/v1/authorize';

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		// Handle OAuth2 callback before anything else.
		add_action( 'admin_init', array( $this, 'handle_oauth_callback' ), 5 );
		// Handle disconnect request.
		add_action( 'admin_init', array( $this, 'handle_disconnect' ), 5 );
		// Handle manual "Sync Now" request.
		add_action( 'admin_init', array( $this, 'handle_manual_sync' ), 5 );
		// Handle "Load Available Campaigns" request.
		add_action( 'admin_init', array( $this, 'handle_refresh_campaigns' ), 5 );
		// Run sync on admin_init (after settings save) if enabled.
		add_action( 'admin_init', array( $this, 'maybe_sync' ), 20 );
		// Daily WP Cron sync.
		add_action( 'npal_cc_daily_sync', array( __CLASS__, 'cron_sync' ) );
		add_action( 'init', array( $this, 'schedule_daily_sync' ) );
	}

	/* ------------------------------------------------------------------
	 *  OAuth2 Authorization Flow
	 * ---------------------------------------------------------------- */

	/**
	 * Returns the redirect URI for the OAuth2 callback.
	 * This URL must be registered in the CC Developer portal.
	 */
	public static function get_redirect_uri() {
		// Force HTTPS — CC requires exact match and most hosts serve admin over SSL.
		return set_url_scheme( admin_url( 'admin.php?page=' . NPAL_Settings::PAGE_SLUG ), 'https' );
	}

	/**
	 * Builds the Constant Contact OAuth2 authorize URL.
	 * Returns empty string if API Key is not configured.
	 */
	public static function get_authorize_url() {
		$api_key = NPAL_Plugin::get_setting( 'cc_api_key', '' );
		if ( ! $api_key ) {
			return '';
		}

		// Generate and store a CSRF state token.
		$state = wp_generate_password( 32, false );
		set_transient( 'npal_cc_oauth_state', $state, 600 ); // 10 min expiry.

		return add_query_arg( array(
			'client_id'     => $api_key,
			'redirect_uri'  => self::get_redirect_uri(),
			'response_type' => 'code',
			'scope'         => 'campaign_data offline_access',
			'state'         => $state,
		), self::AUTHORIZE_URL );
	}

	/**
	 * Processes the OAuth2 callback — exchanges the authorization code
	 * for access + refresh tokens and stores them in plugin settings.
	 */
	public function handle_oauth_callback() {
		// Only act when CC returns us an authorization code.
		if ( ! isset( $_GET['code'] ) || ! isset( $_GET['state'] ) ) {
			return;
		}
		// Only on our settings page.
		if ( ! isset( $_GET['page'] ) || NPAL_Settings::PAGE_SLUG !== $_GET['page'] ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Verify CSRF state.
		$saved_state = get_transient( 'npal_cc_oauth_state' );
		if ( ! $saved_state || ! hash_equals( $saved_state, sanitize_text_field( wp_unslash( $_GET['state'] ) ) ) ) {
			add_settings_error( NPAL_OPTION_KEY, 'cc_oauth', __( 'OAuth state mismatch. Please try connecting again.', 'nashua-pal-core' ), 'error' );
			return;
		}
		delete_transient( 'npal_cc_oauth_state' );

		$code      = sanitize_text_field( wp_unslash( $_GET['code'] ) );
		$api_key   = NPAL_Plugin::get_setting( 'cc_api_key', '' );
		$api_secret = NPAL_Plugin::get_setting( 'cc_api_secret', '' );

		if ( ! $api_key || ! $api_secret ) {
			add_settings_error( NPAL_OPTION_KEY, 'cc_oauth', __( 'API Key and API Secret must be saved before connecting.', 'nashua-pal-core' ), 'error' );
			return;
		}

		// Exchange authorization code for tokens.
		$response = wp_remote_post( self::TOKEN_URL, array(
			'timeout' => self::FETCH_TIMEOUT,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $api_key . ':' . $api_secret ),
				'Content-Type'  => 'application/x-www-form-urlencoded',
			),
			'body' => array(
				'grant_type'   => 'authorization_code',
				'code'         => $code,
				'redirect_uri' => self::get_redirect_uri(),
			),
		) );

		if ( is_wp_error( $response ) ) {
			add_settings_error( NPAL_OPTION_KEY, 'cc_oauth', sprintf( __( 'Token exchange failed: %s', 'nashua-pal-core' ), $response->get_error_message() ), 'error' );
			return;
		}

		$status = wp_remote_retrieve_response_code( $response );
		$data   = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $status || empty( $data['access_token'] ) ) {
			$err = isset( $data['error_description'] ) ? $data['error_description'] : 'HTTP ' . $status;
			add_settings_error( NPAL_OPTION_KEY, 'cc_oauth', sprintf( __( 'Token exchange failed: %s', 'nashua-pal-core' ), $err ), 'error' );
			return;
		}

		// Save tokens to settings.
		$opts = get_option( NPAL_OPTION_KEY, array() );
		$opts['cc_access_token']  = sanitize_text_field( $data['access_token'] );
		if ( ! empty( $data['refresh_token'] ) ) {
			$opts['cc_refresh_token'] = sanitize_text_field( $data['refresh_token'] );
		}
		update_option( NPAL_OPTION_KEY, $opts );

		// Clear cache so sync runs with the new tokens.
		self::clear_cache();

		// Redirect to clean URL to remove the code from the address bar.
		wp_safe_redirect( add_query_arg( array(
			'page'          => NPAL_Settings::PAGE_SLUG,
			'npal_cc_connected' => '1',
		), admin_url( 'admin.php' ) ) . '#news' );
		exit;
	}

	/**
	 * Handles the "Disconnect" action — clears stored tokens.
	 */
	public function handle_disconnect() {
		if ( ! isset( $_GET['npal_cc_disconnect'] ) || '1' !== $_GET['npal_cc_disconnect'] ) {
			return;
		}
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'npal_cc_disconnect' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$opts = get_option( NPAL_OPTION_KEY, array() );
		$opts['cc_access_token']  = '';
		$opts['cc_refresh_token'] = '';
		update_option( NPAL_OPTION_KEY, $opts );

		self::clear_cache();

		wp_safe_redirect( add_query_arg( array(
			'page'             => NPAL_Settings::PAGE_SLUG,
			'npal_cc_disconnected' => '1',
		), admin_url( 'admin.php' ) ) . '#news' );
		exit;
	}

	/**
	 * Handles the manual "Sync Now" button — clears cache and forces a sync.
	 */
	public function handle_manual_sync() {
		if ( ! isset( $_GET['npal_cc_sync_now'] ) || '1' !== $_GET['npal_cc_sync_now'] ) {
			return;
		}
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'npal_cc_sync_now' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		self::clear_cache();
		$result = self::sync_campaigns();

		$query_args = array(
			'page' => NPAL_Settings::PAGE_SLUG,
		);

		if ( is_wp_error( $result ) ) {
			$query_args['npal_cc_sync_error'] = urlencode( $result->get_error_message() );
		} else {
			$query_args['npal_cc_synced'] = (int) $result;
		}

		wp_safe_redirect( add_query_arg( $query_args, admin_url( 'admin.php' ) ) . '#news' );
		exit;
	}

	/* ------------------------------------------------------------------
	 *  Public: trigger sync if enabled and cache is stale
	 * ---------------------------------------------------------------- */

	public function maybe_sync() {
		if ( '1' !== NPAL_Plugin::get_setting( 'cc_sync_enabled', '0' ) ) {
			return;
		}
		if ( ! self::has_credentials() ) {
			return;
		}
		// Only sync if cache is expired (transient miss).
		$cached = get_transient( self::CACHE_KEY );
		if ( false !== $cached ) {
			return;
		}
		self::sync_campaigns();
	}

	/**
	 * Schedule the daily cron event (called on init when WP is ready).
	 */
	public function schedule_daily_sync() {
		if ( ! wp_next_scheduled( 'npal_cc_daily_sync' ) ) {
			wp_schedule_event( time(), 'daily', 'npal_cc_daily_sync' );
		}
	}

	/**
	 * WP Cron callback — runs daily regardless of admin visits.
	 */
	public static function cron_sync() {
		if ( ! self::has_credentials() ) {
			return;
		}
		// Clear cache so sync_campaigns actually runs.
		delete_transient( self::CACHE_KEY );
		self::sync_campaigns();
	}

	/* ------------------------------------------------------------------
	 *  Public: full sync — fetch campaigns from CC and upsert CPT posts
	 * ---------------------------------------------------------------- */

	public static function sync_campaigns() {
		$campaigns = self::fetch_sent_campaigns();
		if ( is_wp_error( $campaigns ) ) {
			set_transient( self::CACHE_KEY . '_error', $campaigns->get_error_message(), self::CACHE_DURATION );
			// Still set the cache key so we don't hammer the API on every admin page load.
			set_transient( self::CACHE_KEY, array(), self::CACHE_DURATION );
			return $campaigns;
		}

		$synced = 0;
		foreach ( $campaigns as $campaign ) {
			$did = self::upsert_newsletter( $campaign );
			if ( $did ) { $synced++; }
		}

		// Store sync metadata.
		set_transient( self::CACHE_KEY, array(
			'count'   => count( $campaigns ),
			'synced'  => $synced,
			'time'    => current_time( 'mysql' ),
		), self::CACHE_DURATION );

		delete_transient( self::CACHE_KEY . '_error' );

		return $synced;
	}

	/* ------------------------------------------------------------------
	 *  Campaign picker — fetch & store available campaigns for admin UI
	 * ---------------------------------------------------------------- */

	const AVAILABLE_CAMPAIGNS_KEY = 'npal_cc_available_campaigns';

	/**
	 * Handles the "Load Available Campaigns" button — clears and repopulates
	 * the available campaigns transient.
	 */
	public function handle_refresh_campaigns() {
		if ( ! isset( $_GET['npal_cc_load_campaigns'] ) || '1' !== $_GET['npal_cc_load_campaigns'] ) {
			return;
		}
		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'npal_cc_load_campaigns' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$result = self::fetch_available_campaigns();

		$query_args = array( 'page' => NPAL_Settings::PAGE_SLUG );

		if ( is_wp_error( $result ) ) {
			$query_args['npal_cc_campaigns_error'] = urlencode( $result->get_error_message() );
		} else {
			$query_args['npal_cc_campaigns_loaded'] = count( $result );
		}

		wp_safe_redirect( add_query_arg( $query_args, admin_url( 'admin.php' ) ) . '#news' );
		exit;
	}

	/**
	 * Fetches all sent campaigns from CC and stores them in a transient
	 * for the campaign picker UI. Returns the array on success or WP_Error.
	 */
	public static function fetch_available_campaigns() {
		$token = self::get_valid_token();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$url = self::API_BASE . '/emails?limit=50';

		$response = wp_remote_get( $url, array(
			'timeout' => self::FETCH_TIMEOUT,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Accept'        => 'application/json',
			),
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'cc_fetch_failed', $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );

		// Token expired — try refresh once.
		if ( 401 === $code ) {
			$refreshed = self::refresh_access_token();
			if ( is_wp_error( $refreshed ) ) {
				return $refreshed;
			}
			$response = wp_remote_get( $url, array(
				'timeout' => self::FETCH_TIMEOUT,
				'headers' => array(
					'Authorization' => 'Bearer ' . $refreshed,
					'Accept'        => 'application/json',
				),
			) );
			if ( is_wp_error( $response ) ) {
				return new WP_Error( 'cc_fetch_failed', $response->get_error_message() );
			}
			$code = wp_remote_retrieve_response_code( $response );
		}

		if ( 200 !== $code ) {
			$body = wp_remote_retrieve_body( $response );
			return new WP_Error( 'cc_api_error', sprintf( 'CC API returned %d: %s', $code, substr( $body, 0, 200 ) ) );
		}

		$data      = json_decode( wp_remote_retrieve_body( $response ), true );
		$campaigns = isset( $data['campaigns'] ) ? $data['campaigns'] : array();

		// Filter to sent ("Done") campaigns and build a simple list.
		$available = array();
		foreach ( $campaigns as $c ) {
			if ( isset( $c['current_status'] ) && 'Done' === $c['current_status'] ) {
				$available[] = array(
					'id'   => $c['campaign_id'],
					'name' => isset( $c['name'] ) ? $c['name'] : '',
					'date' => isset( $c['updated_at'] ) ? $c['updated_at'] : '',
				);
			}
		}

		// Store for 24 hours — the admin can refresh any time.
		set_transient( self::AVAILABLE_CAMPAIGNS_KEY, $available, 86400 );

		return $available;
	}

	/**
	 * Returns the stored list of available campaigns from the transient.
	 * Each entry: array( 'id' => '...', 'name' => '...', 'date' => '...' )
	 */
	public static function get_available_campaigns() {
		$data = get_transient( self::AVAILABLE_CAMPAIGNS_KEY );
		return is_array( $data ) ? $data : array();
	}

	/* ------------------------------------------------------------------
	 *  Fetch sent campaigns from CC API
	 * ---------------------------------------------------------------- */

	private static function fetch_sent_campaigns() {
		$token = self::get_valid_token();
		if ( is_wp_error( $token ) ) {
			return $token;
		}

		// Fetch campaigns — only "Done" (sent) ones.
		$url = self::API_BASE . '/emails?limit=50';

		$response = wp_remote_get( $url, array(
			'timeout' => self::FETCH_TIMEOUT,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Accept'        => 'application/json',
			),
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'cc_fetch_failed', $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );

		// Token expired — try refresh once.
		if ( 401 === $code ) {
			$refreshed = self::refresh_access_token();
			if ( is_wp_error( $refreshed ) ) {
				return $refreshed;
			}
			// Retry with new token.
			$response = wp_remote_get( $url, array(
				'timeout' => self::FETCH_TIMEOUT,
				'headers' => array(
					'Authorization' => 'Bearer ' . $refreshed,
					'Accept'        => 'application/json',
				),
			) );
			if ( is_wp_error( $response ) ) {
				return new WP_Error( 'cc_fetch_failed', $response->get_error_message() );
			}
			$code = wp_remote_retrieve_response_code( $response );
		}

		if ( 200 !== $code ) {
			$body = wp_remote_retrieve_body( $response );
			return new WP_Error( 'cc_api_error', sprintf( 'CC API returned %d: %s', $code, substr( $body, 0, 200 ) ) );
		}

		$data      = json_decode( wp_remote_retrieve_body( $response ), true );
		$campaigns = isset( $data['campaigns'] ) ? $data['campaigns'] : array();

		// Filter to sent campaigns only.
		$sent = array();
		foreach ( $campaigns as $c ) {
			if ( isset( $c['current_status'] ) && 'Done' === $c['current_status'] ) {
				$sent[] = $c;
			}
		}

		// If specific campaigns are selected, filter to only those.
		$selected_raw = NPAL_Plugin::get_setting( 'cc_selected_campaigns', '' );
		if ( is_string( $selected_raw ) && '' !== trim( $selected_raw ) ) {
			$selected_ids = array_map( 'trim', explode( ',', $selected_raw ) );
			$selected_ids = array_filter( $selected_ids );
			if ( ! empty( $selected_ids ) ) {
				$sent = array_filter( $sent, function( $c ) use ( $selected_ids ) {
					return isset( $c['campaign_id'] ) && in_array( $c['campaign_id'], $selected_ids, true );
				} );
				$sent = array_values( $sent );
			}
		}

		// For each sent campaign, fetch the activity details to get permalink_url + first image.
		$results = array();
		foreach ( $sent as $c ) {
			$detail = self::fetch_campaign_detail( $c['campaign_id'], $token );
			$permalink   = '';
			$first_image = '';
			if ( is_array( $detail ) ) {
				$permalink   = isset( $detail['permalink'] )   ? $detail['permalink']   : '';
				$first_image = isset( $detail['first_image'] ) ? $detail['first_image'] : '';
			}
			$results[] = array(
				'campaign_id' => $c['campaign_id'],
				'name'        => isset( $c['name'] ) ? $c['name'] : '',
				'updated_at'  => isset( $c['updated_at'] ) ? $c['updated_at'] : '',
				'created_at'  => isset( $c['created_at'] ) ? $c['created_at'] : '',
				'permalink'   => $permalink,
				'first_image' => $first_image,
			);
		}

		return $results;
	}

	/* ------------------------------------------------------------------
	 *  Fetch single campaign detail to get permalink_url
	 * ---------------------------------------------------------------- */

	private static function fetch_campaign_detail( $campaign_id, $token ) {
		// First get the campaign to find the primary activity ID.
		$response = wp_remote_get( self::API_BASE . '/emails/' . $campaign_id, array(
			'timeout' => self::FETCH_TIMEOUT,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Accept'        => 'application/json',
			),
		) );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return '';
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $data['campaign_activities'] ) ) {
			return '';
		}

		// Find the primary_email activity.
		$activity_id = '';
		foreach ( $data['campaign_activities'] as $act ) {
			if ( isset( $act['role'] ) && 'primary_email' === $act['role'] ) {
				$activity_id = $act['campaign_activity_id'];
				break;
			}
		}
		if ( ! $activity_id && ! empty( $data['campaign_activities'][0]['campaign_activity_id'] ) ) {
			$activity_id = $data['campaign_activities'][0]['campaign_activity_id'];
		}
		if ( ! $activity_id ) {
			return '';
		}

		// Fetch the activity detail with permalink_url and html_content.
		$act_response = wp_remote_get( self::API_BASE . '/emails/activities/' . $activity_id . '?include=permalink_url,html_content', array(
			'timeout' => self::FETCH_TIMEOUT,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'Accept'        => 'application/json',
			),
		) );
		if ( is_wp_error( $act_response ) || 200 !== wp_remote_retrieve_response_code( $act_response ) ) {
			return '';
		}
		$act_data = json_decode( wp_remote_retrieve_body( $act_response ), true );

		$permalink = isset( $act_data['permalink_url'] ) ? $act_data['permalink_url'] : '';

		// Extract the first image from the email HTML content.
		$first_image = '';
		$q = '"\x27'; // double-quote + single-quote for character class
		if ( ! empty( $act_data['html_content'] ) ) {
			$html_c = $act_data['html_content'];
			if ( preg_match( '/<img[^>]+src=[' . $q . ']([^' . $q . ']+)[' . $q . ']/', $html_c, $m ) ) {
				$candidate = $m[1];
				$skip = false;
				if ( preg_match( '/<img[^>]*src=[' . $q . ']' . preg_quote( $candidate, '/' ) . '[' . $q . '][^>]*>/', $html_c, $tag_match ) ) {
					$tag = $tag_match[0];
					if ( preg_match( '/width=[' . $q . '](\d+)/', $tag, $w ) && (int) $w[1] < 50 ) {
						$skip = true;
					}
					if ( preg_match( '/height=[' . $q . '](\d+)/', $tag, $h ) && (int) $h[1] < 50 ) {
						$skip = true;
					}
				}
				if ( ! $skip && filter_var( $candidate, FILTER_VALIDATE_URL ) ) {
					$first_image = $candidate;
				}
				if ( ! $first_image && preg_match_all( '/<img[^>]+src=[' . $q . ']([^' . $q . ']+)[' . $q . '][^>]*>/i', $html_c, $all_matches ) ) {
					foreach ( $all_matches[1] as $idx => $url ) {
						$tag = $all_matches[0][ $idx ];
						$too_small = false;
						if ( preg_match( '/width=[' . $q . '](\d+)/', $tag, $w ) && (int) $w[1] < 50 ) {
							$too_small = true;
						}
						if ( preg_match( '/height=[' . $q . '](\d+)/', $tag, $h ) && (int) $h[1] < 50 ) {
							$too_small = true;
						}
						if ( ! $too_small && filter_var( $url, FILTER_VALIDATE_URL ) ) {
							$first_image = $url;
							break;
						}
					}
				}
			}
		}

		// Fallback: if no image from html_content, try the campaign's thumbnail_url field.
		if ( ! $first_image && ! empty( $act_data['thumbnail_url'] ) ) {
			$first_image = $act_data['thumbnail_url'];
		}

		// Fallback: scrape the permalink page for the first large image.
		if ( ! $first_image && $permalink ) {
			$first_image = self::scrape_first_image( $permalink );
		}

		return array(
			'permalink'   => $permalink,
			'first_image' => $first_image,
		);
	}

	/**
	 * Fetch a URL and extract the first usable image from its HTML.
	 */
	private static function scrape_first_image( $url ) {
		$response = wp_remote_get( $url, array(
			'timeout'     => 15,
			'redirection' => 5,
			'user-agent'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
			'headers'     => array( 'Accept' => 'text/html,application/xhtml+xml' ),
		) );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return '';
		}
		$html = wp_remote_retrieve_body( $response );
		if ( ! $html ) {
			return '';
		}

		// Try og:image meta tag first (handle both attribute orders).
		if ( preg_match( '/<meta[^>]+property=["\x27]og:image["\x27][^>]+content=["\x27]([^"\x27]+)["\x27]/i', $html, $og ) ) {
			if ( filter_var( $og[1], FILTER_VALIDATE_URL ) ) {
				return $og[1];
			}
		}
		if ( preg_match( '/<meta[^>]+content=["\x27]([^"\x27]+)["\x27][^>]+property=["\x27]og:image["\x27]/i', $html, $og2 ) ) {
			if ( filter_var( $og2[1], FILTER_VALIDATE_URL ) ) {
				return $og2[1];
			}
		}

		// CC emails use files.constantcontact.com for images — grab the first one directly.
		if ( preg_match_all( '/https?:\/\/files\.constantcontact\.com\/[^\s"<>]+\.(?:jpe?g|png|gif)/i', $html, $cc_imgs ) ) {
			foreach ( $cc_imgs[0] as $cc_url ) {
				return $cc_url;
			}
		}

		// Fall back to first large img tag.
		if ( preg_match_all( '/<img[^>]+src=["\x27]([^"\x27]+)["\x27][^>]*>/i', $html, $matches ) ) {
			foreach ( $matches[1] as $idx => $img_url ) {
				$tag = $matches[0][ $idx ];
				$too_small = false;
				if ( preg_match( '/width=["\x27](\d+)/', $tag, $w ) && (int) $w[1] < 80 ) {
					$too_small = true;
				}
				if ( preg_match( '/height=["\x27](\d+)/', $tag, $h ) && (int) $h[1] < 80 ) {
					$too_small = true;
				}
				if ( ! $too_small && filter_var( $img_url, FILTER_VALIDATE_URL ) ) {
					return $img_url;
				}
			}
		}

		return '';
	}

	/* ------------------------------------------------------------------
	 *  Upsert a pal_newsletter post from a CC campaign
	 * ---------------------------------------------------------------- */

	private static function upsert_newsletter( $campaign ) {
		if ( empty( $campaign['campaign_id'] ) || empty( $campaign['name'] ) ) {
			return false;
		}

		$cc_id = $campaign['campaign_id'];

		// Check if this campaign already exists by meta key.
		$existing = get_posts( array(
			'post_type'      => 'pal_newsletter',
			'posts_per_page' => 1,
			'post_status'    => 'any',
			'meta_query'     => array(
				array(
					'key'   => '_pal_newsletter_cc_campaign_id',
					'value' => $cc_id,
				),
			),
			'fields' => 'ids',
		) );

		// Determine the newsletter date from updated_at (sent date).
		$date_str = '';
		if ( ! empty( $campaign['updated_at'] ) ) {
			$ts = strtotime( $campaign['updated_at'] );
			if ( $ts ) {
				$date_str = wp_date( 'Y-m-d', $ts );
			}
		}

		$post_date = $date_str ? $date_str . ' 12:00:00' : current_time( 'mysql' );

		$post_data = array(
			'post_type'   => 'pal_newsletter',
			'post_title'  => sanitize_text_field( $campaign['name'] ),
			'post_status' => 'publish',
			'post_date'   => $post_date,
		);

		if ( ! empty( $existing ) ) {
			// Update existing post.
			$post_id = $existing[0];
			$post_data['ID'] = $post_id;
			wp_update_post( $post_data );
		} else {
			// Create new post.
			$post_id = wp_insert_post( $post_data );
			if ( is_wp_error( $post_id ) || ! $post_id ) {
				return false;
			}
		}

		// Set meta fields.
		update_post_meta( $post_id, '_pal_newsletter_cc_campaign_id', $cc_id );
		if ( $date_str ) {
			update_post_meta( $post_id, '_pal_newsletter_date', $date_str );
		}
		if ( ! empty( $campaign['permalink'] ) ) {
			update_post_meta( $post_id, '_pal_newsletter_url', esc_url_raw( $campaign['permalink'] ) );
		}

		// Sideload first image from CC email as featured image (only if none set).
		if ( ! has_post_thumbnail( $post_id ) ) {
			$image_url = '';

			// 1. Image from API html_content / thumbnail_url.
			if ( ! empty( $campaign['first_image'] ) ) {
				$image_url = $campaign['first_image'];
			}

			// 2. Fallback: scrape the permalink page.
			if ( ! $image_url ) {
				$permalink = get_post_meta( $post_id, '_pal_newsletter_url', true );
				if ( $permalink ) {
					$image_url = self::scrape_first_image( $permalink );
				}
			}

			if ( $image_url ) {
				self::sideload_featured_image( $post_id, $image_url, $campaign['name'] );
			}
		}

		return true;
	}

	/* ------------------------------------------------------------------
	 *  Sideload an external image and set it as featured image
	 * ---------------------------------------------------------------- */

	private static function sideload_featured_image( $post_id, $image_url, $title = '' ) {
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		// Download the image to a temp file.
		$tmp = download_url( $image_url, 15 );
		if ( is_wp_error( $tmp ) ) {
			return;
		}

		// Determine a filename from the URL.
		$url_path = wp_parse_url( $image_url, PHP_URL_PATH );
		$filename = $url_path ? basename( $url_path ) : 'newsletter-image.jpg';
		// Ensure it has an image extension.
		if ( ! preg_match( '/\.(jpe?g|png|gif|webp)$/i', $filename ) ) {
			$filename .= '.jpg';
		}

		$file_array = array(
			'name'     => sanitize_file_name( $filename ),
			'tmp_name' => $tmp,
		);

		$attachment_id = media_handle_sideload( $file_array, $post_id, $title ? sanitize_text_field( $title ) : '' );

		if ( is_wp_error( $attachment_id ) ) {
			@unlink( $tmp );
			return;
		}

		set_post_thumbnail( $post_id, $attachment_id );
	}

	/* ------------------------------------------------------------------
	 *  OAuth2 token management
	 * ---------------------------------------------------------------- */

	private static function get_valid_token() {
		$token = NPAL_Plugin::get_setting( 'cc_access_token', '' );
		if ( ! $token ) {
			return new WP_Error( 'cc_no_token', __( 'No Constant Contact access token configured.', 'nashua-pal-core' ) );
		}
		return $token;
	}

	private static function refresh_access_token() {
		$api_key       = NPAL_Plugin::get_setting( 'cc_api_key', '' );
		$api_secret    = NPAL_Plugin::get_setting( 'cc_api_secret', '' );
		$refresh_token = NPAL_Plugin::get_setting( 'cc_refresh_token', '' );

		if ( ! $api_key || ! $api_secret || ! $refresh_token ) {
			return new WP_Error( 'cc_missing_creds', __( 'Missing CC API key, secret, or refresh token.', 'nashua-pal-core' ) );
		}

		$response = wp_remote_post( self::TOKEN_URL, array(
			'timeout' => self::FETCH_TIMEOUT,
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $api_key . ':' . $api_secret ),
				'Content-Type'  => 'application/x-www-form-urlencoded',
			),
			'body' => array(
				'grant_type'    => 'refresh_token',
				'refresh_token' => $refresh_token,
			),
		) );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'cc_refresh_failed', $response->get_error_message() );
		}

		$code = wp_remote_retrieve_response_code( $response );
		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $code || empty( $data['access_token'] ) ) {
			$err = isset( $data['error_description'] ) ? $data['error_description'] : 'Token refresh failed (HTTP ' . $code . ')';
			return new WP_Error( 'cc_refresh_failed', $err );
		}

		// Save the new tokens back to settings.
		$opts = get_option( NPAL_OPTION_KEY, array() );
		$opts['cc_access_token'] = sanitize_text_field( $data['access_token'] );
		if ( ! empty( $data['refresh_token'] ) ) {
			$opts['cc_refresh_token'] = sanitize_text_field( $data['refresh_token'] );
		}
		update_option( NPAL_OPTION_KEY, $opts );

		return $data['access_token'];
	}

	/* ------------------------------------------------------------------
	 *  Helpers
	 * ---------------------------------------------------------------- */

	public static function has_credentials() {
		$key   = NPAL_Plugin::get_setting( 'cc_api_key', '' );
		$token = NPAL_Plugin::get_setting( 'cc_access_token', '' );
		return ( $key && $token );
	}

	public static function clear_cache() {
		delete_transient( self::CACHE_KEY );
		delete_transient( self::CACHE_KEY . '_error' );
	}

	/**
	 * Returns sync status for the admin settings panel.
	 */
	public static function get_sync_status() {
		$enabled = '1' === NPAL_Plugin::get_setting( 'cc_sync_enabled', '0' );

		if ( ! $enabled ) {
			return array(
				'ok'        => false,
				'message'   => __( 'Auto-sync is disabled.', 'nashua-pal-core' ),
				'last_sync' => '',
			);
		}

		$api_key = NPAL_Plugin::get_setting( 'cc_api_key', '' );
		$token   = NPAL_Plugin::get_setting( 'cc_access_token', '' );

		if ( ! $api_key ) {
			return array(
				'ok'        => false,
				'message'   => __( 'Enter your API Key (Client ID) and API Secret above, save settings, then click "Connect to Constant Contact".', 'nashua-pal-core' ),
				'last_sync' => '',
			);
		}

		if ( ! $token ) {
			return array(
				'ok'        => false,
				'message'   => __( 'API Key saved. Click "Connect to Constant Contact" above to authorize and start syncing newsletters.', 'nashua-pal-core' ),
				'last_sync' => '',
			);
		}

		// Check for error transient.
		$error = get_transient( self::CACHE_KEY . '_error' );
		if ( $error ) {
			return array(
				'ok'        => false,
				'message'   => sprintf( __( 'Last sync failed: %s', 'nashua-pal-core' ), $error ),
				'last_sync' => '',
			);
		}

		// Check for successful sync data.
		$cached = get_transient( self::CACHE_KEY );
		if ( is_array( $cached ) && isset( $cached['time'] ) ) {
			return array(
				'ok'        => true,
				'message'   => sprintf(
					__( 'Connected. %d campaigns found, %d synced.', 'nashua-pal-core' ),
					$cached['count'],
					$cached['synced']
				),
				'last_sync' => $cached['time'],
			);
		}

		return array(
			'ok'        => true,
			'message'   => __( 'Credentials configured. Sync will run on next page load.', 'nashua-pal-core' ),
			'last_sync' => '',
		);
	}
}
