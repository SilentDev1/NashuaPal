<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Template overrides for PAL People single pages.
 *
 * @package NashuaPalCore
 * @since   1.20.0
 */
final class NPAL_People_Templates {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'single_template', array( $this, 'single_template' ) );
	}

	public function single_template( $template ) {
		$types = array(
			NPAL_CPT_Board_Member::POST_TYPE    => 'single-pal-people.php',
			NPAL_CPT_Team_Member::POST_TYPE     => 'single-pal-people.php',
			NPAL_CPT_Volunteer_Leader::POST_TYPE => 'single-pal-people.php',
		);

		foreach ( $types as $pt => $file ) {
			if ( is_singular( $pt ) ) {
				$plugin_tmpl = NPAL_PLUGIN_DIR . 'templates/' . $file;
				if ( file_exists( $plugin_tmpl ) ) {
					return $plugin_tmpl;
				}
			}
		}

		return $template;
	}
}
