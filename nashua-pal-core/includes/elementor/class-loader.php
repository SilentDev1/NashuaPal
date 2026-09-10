<?php
/**
 * Elementor integration loader.
 *
 * Registers the "Nashua PAL" widget category (pinned to the TOP of the
 * widget panel via reflection on Elementor's Elements_Manager) and
 * dynamically loads every NPAL_Widget_* class shipped under
 * includes/elementor/widgets-*.php.
 *
 * @package NashuaPalCore
 * @since 1.8.56
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Elementor_Loader {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		// Hook with priority 1 so our category registers before Elementor's own
		// (those run at default 10), then we reach back in and prepend ours.
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ), 1 );

		// Register all widgets when Elementor walks the registry.
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );

		// Editor-only scripts (e.g. dynamic panel labels for mega menu).
		add_action( 'elementor/editor/after_enqueue_scripts', array( $this, 'editor_scripts' ) );
	}

	public function register_category( $elements_manager ) {
		$elements_manager->add_category(
			'nashua-pal',
			array(
				'title' => __( 'Nashua PAL', 'nashua-pal-core' ),
				'icon'  => 'eicon-favorite',
			)
		);

		// Move our category to the TOP of the widget panel. Elementor's
		// `categories` property is private/protected, so we reflect into it.
		// Wrapped in try/catch so a future Elementor refactor (property rename)
		// silently falls through to default ordering rather than fatal-ing.
		try {
			$ref      = new \ReflectionClass( $elements_manager );
			$prop_key = null;
			foreach ( array( 'categories', '_categories' ) as $candidate ) {
				if ( $ref->hasProperty( $candidate ) ) {
					$prop_key = $candidate;
					break;
				}
			}
			if ( ! $prop_key ) { return; }

			$prop = $ref->getProperty( $prop_key );
			$prop->setAccessible( true );
			$cats = $prop->getValue( $elements_manager );

			if ( is_array( $cats ) && isset( $cats['nashua-pal'] ) ) {
				$npal = array( 'nashua-pal' => $cats['nashua-pal'] );
				unset( $cats['nashua-pal'] );
				$prop->setValue( $elements_manager, $npal + $cats );
			}
		} catch ( \Exception $e ) {
			// Category still works — it just won't be at the top.
		}
	}

	public function editor_scripts() {
		wp_enqueue_script(
			'pal-megamenu-editor',
			NPAL_PLUGIN_URL . 'assets/js/megamenu-editor.js',
			array( 'elementor-editor', 'jquery' ),
			NPAL_VERSION,
			true
		);
	}

	public function register_widgets( $widgets_manager ) {
		// Load base + every widget file. Each file declares one or more
		// NPAL_Widget_* classes extending NPAL_Elementor_Widget_Base.
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/class-widget-base.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-buttons.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-header.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-homepage.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-contact.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-event.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-content.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-pages.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-program.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-champions.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-cwac.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-auction.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-scholar.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-sponsor-package.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-impact-awardee.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-impact-report.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-membership.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-donor.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-partner-list.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-tax990.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-megamenu.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-people.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-footer.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-video-carousel.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-bright-future.php';
		require_once NPAL_PLUGIN_DIR . 'includes/elementor/widgets-board-doc.php';

		$widget_classes = array(
			// Buttons / CTAs.
			'NPAL_Widget_Donate_Button',

			// Header tools.
			'NPAL_Widget_Logo',
			'NPAL_Widget_Search',
			'NPAL_Widget_Language',
			'NPAL_Widget_Socials',
			'NPAL_Widget_Promo_Bar',
			'NPAL_Widget_Site_Alert',
			'NPAL_Widget_Mobile_Menu_Button',
			'NPAL_Widget_Mobile_Menu',

			// Homepage features.
			'NPAL_Widget_Stats_Bar',
			'NPAL_Widget_Single_Stat',
			'NPAL_Widget_Program_Cards',
			'NPAL_Widget_Upcoming_Events',
			'NPAL_Widget_Events',
			'NPAL_Widget_Event_Date_Pill',
			'NPAL_Widget_Latest_News',
			'NPAL_Widget_News_Archive',
			'NPAL_Widget_Partner_Carousel',
			'NPAL_Widget_Community_Partners',
			'NPAL_Widget_Partner_Single',
			'NPAL_Widget_Get_Involved',

			// Contact / inline.
			'NPAL_Widget_Phone',
			'NPAL_Widget_Email',
			'NPAL_Widget_Address',
			'NPAL_Widget_Newsletter',
			'NPAL_Widget_Underline',

			// Event variants.
			'NPAL_Widget_Awardees',
			'NPAL_Widget_Event_Sponsors',
			'NPAL_Widget_Event_Embed',
			'NPAL_Widget_Previous_Champions',
			'NPAL_Widget_Single_Program',

			// Program single-page widgets (1.8.80+).
			'NPAL_Widget_Program_Featured_Image',
			'NPAL_Widget_Program_Info_Cards',

			// Content / design building blocks (1.8.65+).
			'NPAL_Widget_Section_Heading',
			'NPAL_Widget_Page_Hero',
			'NPAL_Widget_Featured_Event',
			'NPAL_Widget_Video_Gallery',
			'NPAL_Widget_Photo_Gallery',
			'NPAL_Widget_CTA_Banner',

			// Inner-page building blocks (1.8.72+).
			'NPAL_Widget_Hero_Banner',
			'NPAL_Widget_Card_Grid',
			'NPAL_Widget_Tier_List',
			'NPAL_Widget_Icon_List',
			'NPAL_Widget_Content_Box',
			'NPAL_Widget_Quote',

			// Night of Champions (1.15.0+).
			'NPAL_Widget_Champions_Winners',
			'NPAL_Widget_Champions_Sponsors',
			'NPAL_Widget_NOC_Hero',
			'NPAL_Widget_NOC_About',
			'NPAL_Widget_NOC_Past_Champions',
			'NPAL_Widget_NOC_Gallery',
			'NPAL_Widget_NOC_Donate_Banner',
			'NPAL_Widget_NOC_CTA',

			// Cone with a Cop (1.26.0+).
			'NPAL_Widget_CWAC_Sponsors',

			// Live Auction (1.28.0+).
			'NPAL_Widget_Auction_Hero',
			'NPAL_Widget_Auction_Packages',
			'NPAL_Widget_Auction_Info_Strip',

			// Scholarships (1.29.0+).
			'NPAL_Widget_Scholar_Hero',
			'NPAL_Widget_Scholar_Values',
			'NPAL_Widget_Scholar_Info',
			'NPAL_Widget_Scholar_Recipients',
			'NPAL_Widget_Scholar_About',
			'NPAL_Widget_Scholar_CTA',
			'NPAL_Widget_Scholar_Featured',

			// Impact Awardees (1.31.0+).
			'NPAL_Widget_Impact_Awardees',

			// Impact Reports (1.34.0+).
			'NPAL_Widget_Impact_Reports',
			'NPAL_Widget_Impact_Report_Carousel',

			// Membership (1.35.0+).
			'NPAL_Widget_Membership_Header',
			'NPAL_Widget_Membership_Programs',
			'NPAL_Widget_Membership_Values',
			'NPAL_Widget_Membership_Story',
			'NPAL_Widget_Membership_Notice',
			'NPAL_Widget_Membership_Info_Cards',
			'NPAL_Widget_Membership_Application',

			// Donors (1.36.0+).
			'NPAL_Widget_Donor_Hero',
			'NPAL_Widget_Donor_Info_Banner',
			'NPAL_Widget_Donor_List',
			'NPAL_Widget_Donor_CTA',

			// Partners List (1.40.0+).
			'NPAL_Widget_Partner_List_Hero',
			'NPAL_Widget_Partner_List_Info_Banner',
			'NPAL_Widget_Partner_List',
			'NPAL_Widget_Featured_Partners',
			'NPAL_Widget_Partner_List_CTA',

			// Tax 990 Documents (1.40.1+).
			'NPAL_Widget_Tax990_Documents',

			// Sponsorship Packages (1.30.0+).
			'NPAL_Widget_Spon_Pkg_Hero',
			'NPAL_Widget_Spon_Pkg_About',
			'NPAL_Widget_Spon_Pkg_Grid',
			'NPAL_Widget_Spon_Pkg_Featured',
			'NPAL_Widget_Spon_Pkg_CTA',
			'NPAL_Widget_Spon_Pkg_Info',
			'NPAL_Widget_Spon_Pkg_Values',

			// Mega Menu (1.16.0+).
			'NPAL_Widget_Mega_Menu',

			// PAL People / Board & Staff (1.20.0+).
			'NPAL_Widget_Board_Members',
			'NPAL_Widget_Team_Members',
			'NPAL_Widget_Volunteer_Leaders',
			'NPAL_Widget_People_Hero',
			'NPAL_Widget_Values_Bar',
			'NPAL_Widget_People_CTA',

			// Footer (1.21.0+).
			'NPAL_Widget_Footer_Menu',
			'NPAL_Widget_Footer_Legal',

			// YouTube Video Carousel (1.39.5+).
			'NPAL_Widget_Video_Carousel',

			// Bright Futures — Meet Our Youth & Alumni (1.53.0+).
			'NPAL_Widget_Bright_Futures',

			// Board Documents (1.54.0+).
			'NPAL_Widget_Board_Page_Hero',
			'NPAL_Widget_Board_Event_Materials',
			'NPAL_Widget_Board_Documents',
			'NPAL_Widget_Board_Meeting_Minutes',
			'NPAL_Widget_Board_Doc_CTA',
		);

		foreach ( $widget_classes as $class ) {
			if ( class_exists( $class ) ) {
				$widgets_manager->register( new $class() );
			}
		}
	}
}
