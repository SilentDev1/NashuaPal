<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Assets {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	public function enqueue() {
		// Font Awesome 6 (free) — Elementor bundles its own copy of FA but only loads
		// it when an Elementor icon widget is on the page. Force-load it from the CDN
		// so all our [pal_*] shortcodes (which use FA classes) render correctly.
		wp_enqueue_style(
			'nashua-pal-fontawesome',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
			array(),
			'6.5.2'
		);

		// Google Font for the alert popup's structured-left "script line"
		// (cursive yellow text). The user picks one of ~10 options in
		// Settings → Site Alert; we load only that one on the front end.
		if ( '1' === NPAL_Plugin::get_setting( 'alert_enabled', '0' ) && class_exists( 'NPAL_Settings' ) ) {
			$slug = NPAL_Settings::script_font_google( 'current' );
			if ( $slug ) {
				wp_enqueue_style(
					'nashua-pal-fonts',
					'https://fonts.googleapis.com/css2?family=' . $slug . '&display=swap',
					array(),
					null
				);
			}
		}

		wp_enqueue_style(
			'nashua-pal-core',
			NPAL_PLUGIN_URL . 'assets/css/nashua-pal.css',
			array( 'nashua-pal-fontawesome' ),
			NPAL_VERSION
		);
		wp_enqueue_script(
			'nashua-pal-core',
			NPAL_PLUGIN_URL . 'assets/js/nashua-pal.js',
			array(),
			NPAL_VERSION,
			true
		);

		// Night of Champions assets — loaded on all front-end pages (lightweight).
		wp_enqueue_style(
			'nashua-pal-champions',
			NPAL_PLUGIN_URL . 'assets/css/champions.css',
			array( 'nashua-pal-core' ),
			NPAL_VERSION
		);
		wp_enqueue_script(
			'nashua-pal-champions',
			NPAL_PLUGIN_URL . 'assets/js/champions.js',
			array(),
			NPAL_VERSION,
			true
		);

		// Impact Awardees assets.
		wp_enqueue_style(
			'nashua-pal-impact-awardee',
			NPAL_PLUGIN_URL . 'assets/css/impact-awardee.css',
			array( 'nashua-pal-core' ),
			NPAL_VERSION
		);
		wp_enqueue_script(
			'nashua-pal-impact-awardee',
			NPAL_PLUGIN_URL . 'assets/js/impact-awardee.js',
			array(),
			NPAL_VERSION,
			true
		);

		// Impact Reports assets.
		wp_enqueue_style(
			'nashua-pal-impact-report',
			NPAL_PLUGIN_URL . 'assets/css/impact-report.css',
			array( 'nashua-pal-core' ),
			NPAL_VERSION
		);
		wp_enqueue_script(
			'nashua-pal-impact-report',
			NPAL_PLUGIN_URL . 'assets/js/impact-report.js',
			array(),
			NPAL_VERSION,
			true
		);

		// PAL People (Board & Staff) assets.
		wp_enqueue_style(
			'nashua-pal-people',
			NPAL_PLUGIN_URL . 'assets/css/people.css',
			array( 'nashua-pal-core' ),
			NPAL_VERSION
		);

		// Live Auction assets.
		wp_enqueue_style(
			'nashua-pal-auction',
			NPAL_PLUGIN_URL . 'assets/css/auction.css',
			array( 'nashua-pal-core' ),
			NPAL_VERSION
		);

		// Scholarship assets.
		wp_enqueue_style(
			'nashua-pal-scholarship',
			NPAL_PLUGIN_URL . 'assets/css/scholarship.css',
			array( 'nashua-pal-core' ),
			NPAL_VERSION
		);
		wp_enqueue_script(
			'nashua-pal-scholarship',
			NPAL_PLUGIN_URL . 'assets/js/scholarship.js',
			array(),
			NPAL_VERSION,
			true
		);

		// Google Fonts used by Membership + Scholarship "script line" text.
		wp_enqueue_style(
			'nashua-pal-google-fonts',
			'https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;700&family=Playfair+Display:wght@400;700&display=swap',
			array(),
			null
		);

		// Membership assets.
		wp_enqueue_style(
			'nashua-pal-membership',
			NPAL_PLUGIN_URL . 'assets/css/membership.css',
			array( 'nashua-pal-core' ),
			NPAL_VERSION
		);

		// Donor assets.
		wp_enqueue_style(
			'nashua-pal-donor',
			NPAL_PLUGIN_URL . 'assets/css/donor.css',
			array( 'nashua-pal-core' ),
			NPAL_VERSION
		);
		wp_enqueue_script(
			'nashua-pal-donor',
			NPAL_PLUGIN_URL . 'assets/js/donor.js',
			array(),
			NPAL_VERSION,
			true
		);

		// Partner List assets.
		wp_enqueue_style(
			'nashua-pal-partner-list',
			NPAL_PLUGIN_URL . 'assets/css/partner-list.css',
			array( 'nashua-pal-core' ),
			NPAL_VERSION
		);
		wp_enqueue_script(
			'nashua-pal-partner-list',
			NPAL_PLUGIN_URL . 'assets/js/partner-list.js',
			array(),
			NPAL_VERSION,
			true
		);

		// Tax 990 Documents assets.
		wp_enqueue_style(
			'nashua-pal-tax990',
			NPAL_PLUGIN_URL . 'assets/css/tax990.css',
			array( 'nashua-pal-core' ),
			NPAL_VERSION
		);
		wp_enqueue_script(
			'nashua-pal-tax990',
			NPAL_PLUGIN_URL . 'assets/js/tax990.js',
			array(),
			NPAL_VERSION,
			true
		);

		// Board Documents assets.
		wp_enqueue_style(
			'nashua-pal-board-doc',
			NPAL_PLUGIN_URL . 'assets/css/board-doc.css',
			array( 'nashua-pal-core' ),
			NPAL_VERSION
		);
		wp_enqueue_script(
			'nashua-pal-board-doc',
			NPAL_PLUGIN_URL . 'assets/js/board-doc.js',
			array(),
			NPAL_VERSION,
			true
		);

		// Sponsorship Packages assets.
		wp_enqueue_style(
			'nashua-pal-sponsor-package',
			NPAL_PLUGIN_URL . 'assets/css/sponsor-package.css',
			array( 'nashua-pal-core' ),
			NPAL_VERSION
		);
		wp_enqueue_script(
			'nashua-pal-sponsor-package',
			NPAL_PLUGIN_URL . 'assets/js/sponsor-package.js',
			array(),
			NPAL_VERSION,
			true
		);
	}
}
