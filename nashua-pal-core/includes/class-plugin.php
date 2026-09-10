<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Plugin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		NPAL_CPT_Event::instance();
		NPAL_CPT_Program::instance();
		NPAL_CPT_Partner::instance();
		NPAL_Event_Meta::instance();
		NPAL_Program_Meta::instance();
		NPAL_Program_Category_Meta::instance();
		NPAL_Partner_Meta::instance();
		NPAL_CPT_Newsletter::instance();
		NPAL_Newsletter_Meta::instance();
		NPAL_Settings::instance();
		NPAL_Admin_Shortcodes::instance();
		NPAL_Assets::instance();
		NPAL_Theme_Vars::instance();
		NPAL_Shortcodes::instance();
		NPAL_Elementor_Loader::instance();
		NPAL_Template_Importer::instance();
		NPAL_Constant_Contact::instance();
		NPAL_CPT_Champions_Winner::instance();
		NPAL_CPT_Champions_Sponsor::instance();
		NPAL_Champions_Winner_Meta::instance();
		NPAL_Champions_Sponsor_Meta::instance();
		NPAL_Champions_Shortcodes::instance();
		NPAL_CPT_Champions_Auction::instance();
		NPAL_Champions_Auction_Meta::instance();
		NPAL_Champions_Auction_Shortcodes::instance();
		NPAL_CPT_CWAC_Sponsor::instance();
		NPAL_CWAC_Sponsor_Meta::instance();
		NPAL_CWAC_Shortcodes::instance();
		NPAL_CPT_Board_Member::instance();
		NPAL_CPT_Team_Member::instance();
		NPAL_CPT_Volunteer_Leader::instance();
		NPAL_Board_Member_Meta::instance();
		NPAL_Team_Member_Meta::instance();
		NPAL_Volunteer_Leader_Meta::instance();
		NPAL_CPT_Job::instance();
		NPAL_Job_Meta::instance();
		NPAL_CPT_Sponsor::instance();
		NPAL_Sponsor_Meta::instance();
		NPAL_Sponsor_Shortcodes::instance();
		NPAL_CPT_Scholar::instance();
		NPAL_CPT_Scholarship::instance();
		NPAL_Scholar_Meta::instance();
		NPAL_Scholarship_Meta::instance();
		NPAL_Scholar_Shortcodes::instance();
		NPAL_CPT_Sponsor_Package::instance();
		NPAL_Sponsor_Package_Meta::instance();
		NPAL_Sponsor_Package_Shortcodes::instance();
		NPAL_CPT_Impact_Awardee::instance();
		NPAL_Impact_Awardee_Meta::instance();
		NPAL_Impact_Awardee_Shortcodes::instance();
		NPAL_CPT_Impact_Report::instance();
		NPAL_Impact_Report_Meta::instance();
		NPAL_Impact_Report_Shortcodes::instance();
		NPAL_CPT_Donor::instance();
		NPAL_Donor_Meta::instance();
		NPAL_Donor_Shortcodes::instance();
		NPAL_CPT_Partner_List::instance();
		NPAL_Partner_List_Meta::instance();
		NPAL_Partner_List_Shortcodes::instance();
		NPAL_CPT_Tax990::instance();
		NPAL_Tax990_Meta::instance();
		NPAL_Tax990_Shortcodes::instance();
		NPAL_CPT_Bright_Future::instance();
		NPAL_Bright_Future_Meta::instance();
		NPAL_Bright_Future_Shortcodes::instance();
		NPAL_CPT_Board_Doc::instance();
		NPAL_Board_Doc_Meta::instance();
		NPAL_Board_Doc_Shortcodes::instance();
		NPAL_Board_Doc_Categories::instance();
		NPAL_People_Templates::instance();
		NPAL_Password_Form::instance();
		if ( class_exists( 'NPAL_Menu_Icons' ) ) { NPAL_Menu_Icons::instance(); }

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'maybe_flush_rewrites' ), 99 );

		// Mobile menu location — populated by admins under
		// Appearance → Menus → "Display location: Nashua PAL Mobile Menu".
		// Used by the [pal_mobile_menu] shortcode.
		add_action( 'after_setup_theme', array( $this, 'register_menu_locations' ) );

		// One-shot starter-menu seeding for the two footer columns
		// (Quick Links + Get Involved). Runs once on the first admin page
		// load after install/upgrade so the footer isn't empty before the
		// admin sets up menus manually. Idempotent — protected by the
		// `npal_footer_menus_seeded` option flag.
		add_action( 'admin_init', array( $this, 'ensure_footer_menus' ) );

		// Auto-inject the site-wide alert popup. Primary hook is wp_body_open
		// (modern themes); wp_footer is a fallback for themes that don't call
		// wp_body_open() in their header.php — the popup is `position: fixed`
		// so DOM placement doesn't matter visually. The shortcode's internal
		// $alert_rendered static flag prevents double-rendering when both
		// hooks fire on the same request.
		add_action( 'wp_body_open', array( $this, 'auto_render_alert' ) );
		add_action( 'wp_footer',    array( $this, 'auto_render_alert' ) );

		// Elementor's HTML widget doesn't parse shortcodes by default; this makes
		// every widget's rendered output run through do_shortcode() so that
		// templates can mix HTML + [pal_*] freely.
		add_filter( 'elementor/widget/render_content', array( $this, 'parse_shortcodes_in_widgets' ), 10, 2 );

		// Make sure widget templates rendered through Elementor's frontend also resolve shortcodes.
		add_filter( 'elementor/frontend/the_content', 'do_shortcode' );

		// Reorder admin menu: PAL items at top, WordPress defaults at bottom.
		add_action( 'admin_menu', array( $this, 'add_menu_separator' ), 99 );
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( $this, 'admin_menu_order' ) );
	}

	public function auto_render_alert() {
		if ( is_admin() ) { return; }
		if (
			( isset( $_GET['elementor-preview'] ) ) ||
			( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) ||
			( class_exists( '\\Elementor\\Plugin' ) && isset( \Elementor\Plugin::$instance ) && (
				( isset( \Elementor\Plugin::$instance->editor )  && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ||
				( isset( \Elementor\Plugin::$instance->preview ) && \Elementor\Plugin::$instance->preview->is_preview_mode() )
			) )
		) {
			return;
		}
		echo do_shortcode( '[pal_alert]' );
	}

	public function parse_shortcodes_in_widgets( $content, $widget = null ) {
		if ( false === strpos( (string) $content, '[' ) ) {
			return $content;
		}
		return do_shortcode( $content );
	}

	public function add_menu_separator() {
		global $menu;
		$menu['separator-pal'] = array( '', 'read', 'separator-pal', '', 'wp-menu-separator' );
	}

	/**
	 * Reorder admin menu: all PAL items grouped at top, WP defaults below.
	 */
	public function admin_menu_order( $menu_order ) {
		$pal_items = array(
			'nashua-pal-settings',                          // Nashua PAL (Settings)
			'edit.php?post_type=pal_program',               // Programs
			'edit.php?post_type=pal_event',                 // Events
			'edit.php?post_type=pal_partner',               // Partners
			'edit.php?post_type=pal_partner_list',          // Partners List
			'edit.php?post_type=pal_donor',                 // Donors
			'edit.php?post_type=pal_spon_pkg',              // Sponsor Packages
			'edit.php?post_type=pal_scholar',               // Scholars
			'edit.php?post_type=champions_winner',          // Night of Champions
			'edit.php?post_type=cwac_sponsor',              // Cone with a Cop
			'edit.php?post_type=pal_impact_awardee',        // Impact Awardees
			'edit.php?post_type=pal_impact_report',         // Impact Reports
			'edit.php?post_type=pal_bright_future',          // Bright Futures
			'edit.php?post_type=pal_board_member',          // Board & Staff
			'edit.php?post_type=pal_newsletter',            // Newsletters
			'edit.php?post_type=pal_job',                   // Jobs
			'edit.php?post_type=pal_tax990',                // Tax 990
			'edit.php?post_type=pal_board_doc',             // Board Documents
		);

		$ordered    = array();
		$wp_items   = array();

		// First pass: pull PAL items in our defined order.
		foreach ( $pal_items as $slug ) {
			if ( in_array( $slug, $menu_order, true ) ) {
				$ordered[] = $slug;
			}
		}

		// Second pass: everything else (WP defaults) in their original order.
		foreach ( $menu_order as $slug ) {
			if ( ! in_array( $slug, $pal_items, true ) ) {
				$wp_items[] = $slug;
			}
		}

		// Dashboard stays first, then PAL items, then separator, then WP items.
		$final = array();
		foreach ( $wp_items as $item ) {
			if ( 'index.php' === $item ) {
				$final[] = $item;
				break;
			}
		}
		$final   = array_merge( $final, $ordered );
		$final[] = 'separator-pal';
		foreach ( $wp_items as $item ) {
			if ( 'index.php' !== $item ) {
				$final[] = $item;
			}
		}

		return $final;
	}

	public function register_menu_locations() {
		register_nav_menu( 'pal-mobile',              __( 'Nashua PAL Mobile Menu',             'nashua-pal-core' ) );
		register_nav_menu( 'pal-footer-quick-links',  __( 'Nashua PAL Footer — Quick Links',    'nashua-pal-core' ) );
		register_nav_menu( 'pal-footer-get-involved', __( 'Nashua PAL Footer — Get Involved',   'nashua-pal-core' ) );
	}

	/**
	 * Create starter menus for the two footer locations on first run so the
	 * footer isn't empty out-of-the-box. Bails silently if the menus exist
	 * already (idempotent) and if a location is already assigned (respects
	 * a hand-built menu). Runs once via a stored option flag so re-saving
	 * settings or re-importing templates doesn't recreate them.
	 *
	 * @since 1.8.87
	 */
	public function ensure_footer_menus() {
		if ( get_option( 'npal_footer_menus_seeded' ) ) { return; }

		$locations = get_nav_menu_locations();

		$seeds = array(
			'pal-footer-quick-links' => array(
				'name'  => __( 'Footer — Quick Links', 'nashua-pal-core' ),
				'items' => array(
					array( 'About PAL', '/about/' ),
					array( 'Programs',  '/programs/' ),
					array( 'Events',    '/events/' ),
					array( 'Impact',    '/impact/' ),
					array( 'News',      '/news/' ),
					array( 'Contact',   '/contact/' ),
				),
			),
			'pal-footer-get-involved' => array(
				'name'  => __( 'Footer — Get Involved', 'nashua-pal-core' ),
				'items' => array(
					array( 'Volunteer',          '/volunteer/' ),
					array( 'Mentor',             '/mentor/' ),
					array( 'Coach',              '/coach/' ),
					array( 'Partner',            '/partner/' ),
					array( 'Community Partners', '/community-partners/' ),
					array( 'Donate Now',         '/donate/' ),
				),
			),
		);

		$changed = false;
		foreach ( $seeds as $location => $seed ) {
			// Skip if this location already has a menu wired up — respects any
			// hand-built menu the admin may have already assigned.
			if ( ! empty( $locations[ $location ] ) ) { continue; }

			$menu = wp_get_nav_menu_object( $seed['name'] );
			if ( ! $menu ) {
				$menu_id = wp_create_nav_menu( $seed['name'] );
				if ( is_wp_error( $menu_id ) ) { continue; }
				foreach ( $seed['items'] as $item ) {
					list( $title, $url ) = $item;
					wp_update_nav_menu_item( $menu_id, 0, array(
						'menu-item-title'   => $title,
						'menu-item-url'     => $url,
						'menu-item-type'    => 'custom',
						'menu-item-status'  => 'publish',
					) );
				}
			} else {
				$menu_id = (int) $menu->term_id;
			}

			$locations[ $location ] = $menu_id;
			$changed = true;
		}

		if ( $changed ) {
			set_theme_mod( 'nav_menu_locations', $locations );
		}

		update_option( 'npal_footer_menus_seeded', 1 );
	}

	/**
	 * Flush rewrite rules once when the plugin version changes so new CPT
	 * archive/single URLs resolve without a manual Settings → Permalinks save.
	 */
	public function maybe_flush_rewrites() {
		$stored = get_option( 'npal_flush_version', '' );
		if ( $stored !== NPAL_VERSION ) {
			flush_rewrite_rules();
			update_option( 'npal_flush_version', NPAL_VERSION );
		}
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'nashua-pal-core', false, dirname( plugin_basename( NPAL_PLUGIN_FILE ) ) . '/languages' );
	}

	public static function activate() {
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-event.php';
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-program.php';
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-partner.php';
		NPAL_CPT_Event::register();
		NPAL_CPT_Program::register();
		NPAL_CPT_Partner::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-newsletter.php';
		NPAL_CPT_Newsletter::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-champions-winner.php';
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-champions-sponsor.php';
		NPAL_CPT_Champions_Winner::register();
		NPAL_CPT_Champions_Sponsor::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-cwac-sponsor.php';
		NPAL_CPT_CWAC_Sponsor::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-champions-auction.php';
		NPAL_CPT_Champions_Auction::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-job.php';
		NPAL_CPT_Job::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-sponsor.php';
		NPAL_CPT_Sponsor::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-board-member.php';
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-team-member.php';
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-volunteer-leader.php';
		NPAL_CPT_Board_Member::register();
		NPAL_CPT_Team_Member::register();
		NPAL_CPT_Volunteer_Leader::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-scholar.php';
		NPAL_CPT_Scholar::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-scholarship.php';
		NPAL_CPT_Scholarship::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-sponsor-package.php';
		NPAL_CPT_Sponsor_Package::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-impact-awardee.php';
		NPAL_CPT_Impact_Awardee::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-impact-report.php';
		NPAL_CPT_Impact_Report::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-donor.php';
		NPAL_CPT_Donor::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-partner-list.php';
		NPAL_CPT_Partner_List::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-tax990.php';
		NPAL_CPT_Tax990::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-bright-future.php';
		NPAL_CPT_Bright_Future::register();
		require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-board-doc.php';
		NPAL_CPT_Board_Doc::register();
		flush_rewrite_rules();

		if ( false === get_option( NPAL_OPTION_KEY ) ) {
			add_option( NPAL_OPTION_KEY, NPAL_Settings::defaults() );
		}
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}

	public static function get_setting( $key, $default = '' ) {
		$opts = get_option( NPAL_OPTION_KEY, array() );
		if ( ! is_array( $opts ) ) {
			$opts = array();
		}
		// Merge with NPAL_Settings::defaults() so keys added in a plugin update
		// resolve to their bundled default even before the user has visited
		// Nashua PAL → Settings and clicked Save (which is what runs sanitize()
		// and writes the new keys into the saved option). Without this merge,
		// any newly-added settings field returns empty string until first save
		// — caught when [pal_footer_legal] (1.8.83) rendered blank text in the
		// Elementor editor preview because `footer_copyright` / `footer_legal_sub`
		// weren't yet in the saved option for this site.
		if ( class_exists( 'NPAL_Settings' ) ) {
			$defaults = NPAL_Settings::defaults();
			$opts     = array_merge( $defaults, $opts );
		}
		return isset( $opts[ $key ] ) && '' !== $opts[ $key ] ? $opts[ $key ] : $default;
	}
}
