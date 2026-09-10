<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Inline SVG icon library that matches the Nashua PAL design mockup.
 *
 * Why SVGs instead of Font Awesome? FA Free is missing whistle and shield-check
 * (Pro-only). SVGs let us match the mockup exactly without an FA Pro license.
 *
 * Usage:
 *   echo NPAL_Icons::render( 'shield-check' );
 *   echo NPAL_Icons::render_or_fa( 'shield-check' );        // SVG
 *   echo NPAL_Icons::render_or_fa( 'fas fa-shield-alt' );   // <i> tag
 */
final class NPAL_Icons {

	/**
	 * Returns true if $value is one of our registered SVG icon names.
	 */
	public static function has( $name ) {
		return is_string( $name ) && '' !== $name && method_exists( __CLASS__, 'svg_' . str_replace( '-', '_', $name ) );
	}

	/**
	 * Catalog of every built-in SVG icon, keyed by name → human label.
	 * Used to render the visual icon picker in admin.
	 */
	public static function registry() {
		return array(
			// Stats / impact
			'shield-check'    => __( 'Shield with check',  'nashua-pal-core' ),
			'users'           => __( 'Users / group',       'nashua-pal-core' ),
			'target'          => __( 'Target / bullseye',   'nashua-pal-core' ),
			'handshake'       => __( 'Handshake',           'nashua-pal-core' ),
			// Programs
			'mentoring'       => __( 'Mentoring (star)',    'nashua-pal-core' ),
			'basketball'      => __( 'Basketball',          'nashua-pal-core' ),
			'book-open'       => __( 'Open book',           'nashua-pal-core' ),
			'sun'             => __( 'Sun',                 'nashua-pal-core' ),
			// Get involved
			'hands-helping'   => __( 'Helping hand',        'nashua-pal-core' ),
			'whistle'         => __( 'Whistle / coach',     'nashua-pal-core' ),
			'graduation-cap'  => __( 'Graduation cap',      'nashua-pal-core' ),
			// CTAs / UI
			'heart'           => __( 'Heart',               'nashua-pal-core' ),
			'arrow-right'     => __( 'Arrow right',         'nashua-pal-core' ),
			'megaphone'       => __( 'Megaphone',           'nashua-pal-core' ),
			'calendar'        => __( 'Calendar',            'nashua-pal-core' ),
			'clock'           => __( 'Clock',               'nashua-pal-core' ),
			'location-pin'    => __( 'Location pin',        'nashua-pal-core' ),
			'search'          => __( 'Search',              'nashua-pal-core' ),
			'language'        => __( 'Language / globe',    'nashua-pal-core' ),
			'mail'            => __( 'Mail',                'nashua-pal-core' ),
			'phone'           => __( 'Phone',               'nashua-pal-core' ),
			'newspaper'       => __( 'Newspaper / news',    'nashua-pal-core' ),
		);
	}

	/**
	 * Returns true if the value looks like an HTTP(S) URL (custom uploaded image).
	 */
	public static function is_url( $value ) {
		return is_string( $value ) && preg_match( '#^https?://#i', $value );
	}

	/**
	 * Render a registered SVG icon. Returns empty string if unknown.
	 */
	public static function render( $name, $class = '' ) {
		if ( ! self::has( $name ) ) {
			return '';
		}
		$method = 'svg_' . str_replace( '-', '_', $name );
		$svg    = self::$method();
		if ( $class ) {
			$svg = preg_replace( '/<svg /', '<svg class="' . esc_attr( $class ) . '" ', $svg, 1 );
		}
		return $svg;
	}

	/**
	 * Smart helper: render the right markup for whatever the user picked.
	 * - Registered SVG name (e.g. "shield-check") → inline SVG
	 * - URL (e.g. "https://...png")               → <img>
	 * - Font Awesome class (e.g. "fas fa-shield") → <i class>
	 */
	public static function render_or_fa( $value, $svg_class = '' ) {
		if ( self::has( $value ) ) {
			return self::render( $value, $svg_class );
		}
		if ( self::is_url( $value ) ) {
			return '<img src="' . esc_url( $value ) . '" alt="" loading="lazy" class="' . esc_attr( $svg_class ?: 'pal-icon-img' ) . '" />';
		}
		if ( $value ) {
			return '<i class="' . esc_attr( $value ) . '" aria-hidden="true"></i>';
		}
		return '';
	}

	/* Each SVG below is 24x24 with currentColor stroke (outline style),
	   stroke-width 2, rounded line caps. Designed to match the design system. */

	private static function svg_shield_check() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-5"/></svg>';
	}

	private static function svg_users() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
	}

	private static function svg_target() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>';
	}

	private static function svg_handshake() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 17 8 14l-4 4"/><path d="m13 17 3-3 4 4"/><path d="M14 7 9 12l2 2 5-5"/><path d="m7 8 2-2h3l2 2"/><path d="M2 12 6 8"/><path d="m22 12-4-4"/></svg>';
	}

	private static function svg_mentoring() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="8" r="3"/><path d="M2 21v-2a5 5 0 0 1 5-5h2"/><path d="M16 4l1.2 2.6L20 8l-2.8 1.4L16 12l-1.2-2.6L12 8l2.8-1.4L16 4z"/><path d="M17 14v7"/><path d="M14 18h6"/></svg>';
	}

	private static function svg_basketball() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M4.93 4.93c4.68 4.68 9.46 9.46 14.14 14.14"/><path d="M19.07 4.93c-4.68 4.68-9.46 9.46-14.14 14.14"/><path d="M12 2c2.5 3 2.5 17 0 20"/><path d="M2 12c3 2.5 17 2.5 20 0"/></svg>';
	}

	private static function svg_book_open() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/><path d="M8 6h8"/><path d="M8 10h6"/></svg>';
	}

	private static function svg_sun() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>';
	}

	private static function svg_hands_helping() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 17h4l4 4 7-7"/><path d="M14 14h4a3 3 0 0 0 3-3V8"/><path d="M12 7a4 4 0 1 0-8 0v5"/><path d="M8 7v10"/></svg>';
	}

	private static function svg_whistle() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 14a6 6 0 1 0 12 0v-4h4"/><circle cx="10" cy="14" r="2"/><path d="M16 10 20 6"/><path d="M20 6h2"/></svg>';
	}

	private static function svg_graduation_cap() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/><path d="M22 10v6"/></svg>';
	}

	private static function svg_heart() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>';
	}

	private static function svg_arrow_right() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>';
	}

	private static function svg_megaphone() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11v2a2 2 0 0 0 2 2h2l4 5v-5h2l8 4V5l-8 4H5a2 2 0 0 0-2 2z"/><path d="M17 9v6"/></svg>';
	}

	private static function svg_calendar() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>';
	}

	private static function svg_clock() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>';
	}

	private static function svg_location_pin() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>';
	}

	private static function svg_search() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>';
	}

	private static function svg_language() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 0 20"/><path d="M12 2a15.3 15.3 0 0 0 0 20"/></svg>';
	}

	private static function svg_mail() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>';
	}

	private static function svg_phone() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 3 5.18 2 2 0 0 1 5 3h3a2 2 0 0 1 2 1.72c.12.9.33 1.78.62 2.63a2 2 0 0 1-.45 2.11L9 10.91a16 16 0 0 0 4.09 4.09l1.45-1.17a2 2 0 0 1 2.11-.45c.85.29 1.73.5 2.63.62A2 2 0 0 1 22 16.92z"/></svg>';
	}

	private static function svg_newspaper() {
		return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h14a2 2 0 0 1 2 2v14H6a2 2 0 0 1-2-2V4z"/><path d="M8 8h6"/><path d="M8 12h8"/><path d="M8 16h5"/><path d="M20 8h1a1 1 0 0 1 1 1v9a2 2 0 0 1-2 2"/></svg>';
	}
}
