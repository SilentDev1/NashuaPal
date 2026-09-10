<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Settings {

	const PAGE_SLUG = 'nashua-pal-settings';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'admin_init', array( $this, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	public function enqueue_admin_assets( $hook ) {
		// Only on our settings screen.
		if ( ! is_string( $hook ) || false === strpos( $hook, self::PAGE_SLUG ) ) { return; }
		NPAL_Icon_Picker::enqueue();
		// NPAL_Icon_Picker::enqueue() already calls wp_enqueue_media(), but be
		// explicit so a future refactor of the icon picker doesn't break the
		// alert image picker.
		wp_enqueue_media();
		// WP's built-in color picker for the Theme tab (1.8.55).
		wp_enqueue_style(  'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		// Load every cursive Google Font option so each card in the script-
		// font picker renders in its own face. Bundled into one stylesheet
		// request via Google's `&family=` chaining.
		$slugs = self::script_font_google( 'all' );
		if ( ! empty( $slugs ) ) {
			$url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $slugs ) . '&display=swap';
			wp_enqueue_style( 'nashua-pal-script-fonts-all', $url, array(), null );
		}
	}

	/**
	 * Cursive Google Font choices for the alert popup's "script line"
	 * (the "Champions"-style flowing yellow text on the structured left
	 * panel). Each entry has the Google Fonts family slug, a CSS font-family
	 * stack, and a label for the picker.
	 */
	public static function script_font_choices() {
		return array(
			'allura'         => array( 'label' => 'Allura',           'family' => "'Allura', 'Brush Script MT', cursive",           'google' => 'Allura' ),
			'pinyon-script'  => array( 'label' => 'Pinyon Script',    'family' => "'Pinyon Script', 'Brush Script MT', cursive",    'google' => 'Pinyon+Script' ),
			'great-vibes'    => array( 'label' => 'Great Vibes',      'family' => "'Great Vibes', 'Brush Script MT', cursive",      'google' => 'Great+Vibes' ),
			'sacramento'     => array( 'label' => 'Sacramento',       'family' => "'Sacramento', 'Brush Script MT', cursive",       'google' => 'Sacramento' ),
			'pacifico'       => array( 'label' => 'Pacifico',         'family' => "'Pacifico', 'Brush Script MT', cursive",         'google' => 'Pacifico' ),
			'dancing-script' => array( 'label' => 'Dancing Script',   'family' => "'Dancing Script', 'Brush Script MT', cursive",   'google' => 'Dancing+Script:wght@700' ),
			'lobster'        => array( 'label' => 'Lobster',          'family' => "'Lobster', 'Brush Script MT', cursive",          'google' => 'Lobster' ),
			'satisfy'        => array( 'label' => 'Satisfy',          'family' => "'Satisfy', 'Brush Script MT', cursive",          'google' => 'Satisfy' ),
			'kaushan-script' => array( 'label' => 'Kaushan Script',   'family' => "'Kaushan Script', 'Brush Script MT', cursive",   'google' => 'Kaushan+Script' ),
			'tangerine'      => array( 'label' => 'Tangerine',        'family' => "'Tangerine', 'Brush Script MT', cursive",        'google' => 'Tangerine:wght@700' ),
		);
	}

	/** CSS font-family stack for the saved script font. */
	public static function script_font_family() {
		$choice = NPAL_Plugin::get_setting( 'alert_left_script_font', 'allura' );
		$choices = self::script_font_choices();
		return isset( $choices[ $choice ] ) ? $choices[ $choice ]['family'] : $choices['allura']['family'];
	}

	/**
	 * Size presets for the script line — paired (mobile, desktop) px values
	 * applied via CSS variables on the element so the existing media query
	 * scaling continues to work.
	 */
	public static function script_size_choices() {
		return array(
			'sm' => array( 'label' => 'Small',       'mobile' => '28px', 'desktop' => '36px' ),
			'md' => array( 'label' => 'Medium',      'mobile' => '40px', 'desktop' => '54px' ),
			'lg' => array( 'label' => 'Large',       'mobile' => '56px', 'desktop' => '72px' ),
			'xl' => array( 'label' => 'Extra Large', 'mobile' => '72px', 'desktop' => '96px' ),
		);
	}

	/** Returns ['mobile' => '..', 'desktop' => '..'] px tuple for the saved
	 *  script size, falling back to 'lg' if the saved value is unknown. */
	public static function script_size_values() {
		$choice = NPAL_Plugin::get_setting( 'alert_left_script_size', 'lg' );
		$sizes = self::script_size_choices();
		return isset( $sizes[ $choice ] ) ? $sizes[ $choice ] : $sizes['lg'];
	}

	/** Google Fonts family slug for the saved script font (or 'all' for the
	 *  settings page so every option is preview-able in the picker). */
	public static function script_font_google( $which = 'current' ) {
		$choices = self::script_font_choices();
		if ( 'all' === $which ) {
			$slugs = array();
			foreach ( $choices as $c ) { $slugs[] = $c['google']; }
			return $slugs;
		}
		$choice = NPAL_Plugin::get_setting( 'alert_left_script_font', 'allura' );
		return isset( $choices[ $choice ] ) ? $choices[ $choice ]['google'] : $choices['allura']['google'];
	}

	public static function defaults() {
		return array(
			'promo_text'          => 'SUMMER PROGRAM REGISTRATION IS NOW OPEN!',
			'promo_button_text'   => 'Register Today',
			'promo_button_url'    => '/programs',
			'promo_enabled'       => '1',
			'promo_icon'          => 'megaphone',
			'promo_icon_position' => 'before',

			'alert_enabled'           => '0',
			'alert_dismissible'       => '1',

			// Layout mode: 'split' = structured left + right panel (default);
			// 'image' = single uploaded graphic, no fields. For users who design
			// the whole popup in Canva/Photoshop and just want to drop it in.
			'alert_layout'            => 'split',
			'alert_full_image'        => '',
			'alert_full_url'          => '',

			// Simple-mode override: when set, replaces the entire structured
			// left panel with one designed graphic.
			'alert_image'             => '',

			// Left panel — structured fields (used when alert_image is empty).
			'alert_left_bg_image'     => '',
			'alert_left_logo'         => '',
			'alert_left_subtitle'     => '',
			'alert_left_title'        => '',
			'alert_left_script'       => '',
			'alert_left_script_font'  => 'allura',
			'alert_left_script_size'  => 'lg',
			'alert_left_year'         => '',
			'alert_left_detail_1_icon' => '',
			'alert_left_detail_1_text' => '',
			'alert_left_detail_2_icon' => '',
			'alert_left_detail_2_text' => '',
			'alert_left_detail_3_icon' => '',
			'alert_left_detail_3_text' => '',
			'alert_left_strip_text'   => '',

			// Right panel — content.
			'alert_eyebrow'           => '',
			'alert_title'             => '',
			'alert_text'              => '',

			// Right panel — feature icons row (3 circles).
			'alert_feature_1_icon'    => '',
			'alert_feature_1_label'   => '',
			'alert_feature_1_style'   => 'navy',
			'alert_feature_2_icon'    => '',
			'alert_feature_2_label'   => '',
			'alert_feature_2_style'   => 'yellow',
			'alert_feature_3_icon'    => '',
			'alert_feature_3_label'   => '',
			'alert_feature_3_style'   => 'navy',

			// Buttons + footer.
			'alert_button_text'       => '',
			'alert_button_url'        => '',
			'alert_button_2_text'     => '',
			'alert_button_2_url'      => '',
			'alert_footer_text'       => '',

			'donate_url'        => '/donate',
			'register_url'      => '/programs',
			'phone'             => '(603) 555-PAL1',
			'email'             => 'info@nashuapal.org',
			'address'           => '50 Crown St, Nashua, NH 03060',
			'site_logo_url'     => '',

			// Footer legal line — rendered by [pal_footer_legal]. Two fields so
			// the bar can be edited without touching HTML in the footer template.
			// {year} token in copyright is replaced server-side at render time.
			'footer_copyright'  => '© {year} Nashua Police Athletic League. All rights reserved.',
			'footer_legal_sub'  => '501(c)(3) nonprofit · Powered by <a href="https://cao-tech.com/" target="_blank" rel="noopener">Cao-Tech LLC</a>',

			// Footer bottom-link row — 4 label/URL pairs. Leave a slot's label
			// OR url blank to omit it from the rendered list. The shortcode
			// reads these via NPAL_Plugin::get_setting() and skips empties.
			'footer_link_1_label' => 'Privacy',
			'footer_link_1_url'   => '/privacy/',
			'footer_link_2_label' => 'Terms',
			'footer_link_2_url'   => '/terms/',
			'footer_link_3_label' => 'Policies',
			'footer_link_3_url'   => '/policies/',
			'footer_link_4_label' => 'Contact',
			'footer_link_4_url'   => '/contact/',

			'social_facebook'   => '',
			'social_instagram'  => '',
			'social_youtube'    => '',
			'social_x'          => '',

			'stat_1_number'     => '20+',
			'stat_1_label'      => 'Years of Service',
			'stat_1_icon'       => 'shield-check',
			'stat_2_number'     => '1,000+',
			'stat_2_label'      => 'Youth Impacted',
			'stat_2_icon'       => 'users',
			'stat_3_number'     => '50+',
			'stat_3_label'      => 'Programs Offered Yearly',
			'stat_3_icon'       => 'target',
			'stat_4_number'     => '100+',
			'stat_4_label'      => 'Community Partners',
			'stat_4_icon'       => 'handshake',

			'newsletter_action' => '',
			'newsletter_label'  => 'Sign up for our newsletter',
			'newsletter_button' => 'Subscribe',

			'cc_api_key'       => '',
			'cc_api_secret'    => '',
			'cc_access_token'  => '',
			'cc_refresh_token' => '',
			'cc_sync_enabled'       => '0',
			'cc_selected_campaigns' => '',

			'gtranslate_enabled' => '1',
			'search_enabled'     => '1',

			'google_calendar_urls' => '',

			// Get Involved strip — 4 icon/text rows editable from settings.
			'gi_1_icon' => 'hands-helping',
			'gi_1_name' => 'Volunteer',
			'gi_1_desc' => 'Give your time and make a difference.',
			'gi_1_url'  => '/volunteer',
			'gi_2_icon' => 'whistle',
			'gi_2_name' => 'Coach / Mentor',
			'gi_2_desc' => 'Inspire and guide the next generation.',
			'gi_2_url'  => '/mentor',
			'gi_3_icon' => 'handshake',
			'gi_3_name' => 'Partner',
			'gi_3_desc' => 'Partner with us to strengthen our community.',
			'gi_3_url'  => '/sponsor',
			'gi_4_icon' => 'graduation-cap',
			'gi_4_name' => 'Tutor',
			'gi_4_desc' => 'Help students succeed academically.',
			'gi_4_url'  => '/tutor',

			// Scholarship Featured Banner (homepage promo).
			'scholar_photo'              => '',
			'scholar_icon'               => 'fas fa-graduation-cap',
			'scholar_eyebrow'            => 'FEATURED OPPORTUNITY',
			'scholar_title'              => 'PAL Scholarships',
			'scholar_subtitle'           => 'Are Now Open!',
			'scholar_highlight'          => 'Open to all current and former Nashua PAL members in any program!',
			'scholar_description'        => 'The Nashua PAL Scholarship Fund was established to help graduating high school seniors who have shown exceptional commitment to academics, athletics, and community service. Through the generous partnership with the Allard Family, we provide financial support as they pursue higher education.',
			'scholar_info_1_icon'        => 'fas fa-users',
			'scholar_info_1_label'       => 'Open to:',
			'scholar_info_1_value'       => 'All Education Paths',
			'scholar_info_2_icon'        => 'fas fa-calendar-alt',
			'scholar_info_2_label'       => 'Application Deadline:',
			'scholar_info_2_value'       => 'July 10, 2026',
			'scholar_info_3_icon'        => 'fas fa-envelope-open',
			'scholar_info_3_label'       => 'Award Notifications:',
			'scholar_info_3_value'       => 'End of July (via email)',
			'scholar_info_4_icon'        => 'fas fa-dollar-sign',
			'scholar_info_4_label'       => 'Award Range:',
			'scholar_info_4_value'       => 'Up to $2,500',
			'scholar_btn_primary_label'  => 'Apply for a Scholarship',
			'scholar_btn_primary_url'    => '/scholarship/',
			'scholar_btn_secondary_label'=> 'Learn More',
			'scholar_btn_secondary_url'  => '/scholarship/',

			// Theme tab — brand & layout overrides emitted by NPAL_Theme_Vars
			// as inline CSS vars + rule overrides at wp_head priority 999
			// (so it beats both the plugin stylesheet and the active theme).
			'theme_color_primary'     => '#071F3F',
			'theme_color_primary_alt' => '#0D2952',
			'theme_color_accent'      => '#F4A51C',
			'theme_color_accent_dark' => '#D88E0F',
			'theme_color_bg'          => '#F7F9FC',
			'theme_color_text'        => '#3A4658',
			'theme_btn_padding_y'     => '14',
			'theme_btn_padding_x'     => '28',
			'theme_btn_radius'        => '12',
			'theme_btn_font_size'     => '0.95',
			'theme_section_padding'   => '88',
			'theme_font_heading'      => 'montserrat',
			'theme_font_body'         => 'open-sans',
			'theme_heading_weight'    => '800',
			'theme_base_font_size'    => '16',
		);
	}

	public function menu() {
		add_menu_page(
			__( 'Nashua PAL', 'nashua-pal-core' ),
			__( 'Nashua PAL', 'nashua-pal-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render' ),
			'dashicons-shield-alt',
			2
		);
		// Rename the auto-generated first submenu from "Nashua PAL" to "Settings".
		add_submenu_page(
			self::PAGE_SLUG,
			__( 'Settings', 'nashua-pal-core' ),
			__( 'Settings', 'nashua-pal-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render' )
		);
	}

	public function register() {
		register_setting( 'nashua_pal_group', NPAL_OPTION_KEY, array(
			'type'              => 'array',
			'sanitize_callback' => array( $this, 'sanitize' ),
			'default'           => self::defaults(),
		) );
	}

	public function sanitize( $input ) {
		$out      = array();
		$defaults = self::defaults();
		// Boolean / checkbox fields. Unchecked checkboxes don't submit ANY
		// value, so `isset( $input[$key] )` is false on save. Without
		// special-casing, the fallback below uses `defaults()` (which has
		// `promo_enabled => '1'` etc.) and the unchecked state silently
		// reverts to the default — i.e. unchecking the box doesn't stick.
		// For these keys, missing input = unchecked = '0'.
		$boolean_keys = array( 'promo_enabled', 'alert_enabled', 'alert_dismissible', 'gtranslate_enabled', 'search_enabled', 'cc_sync_enabled' );
		foreach ( $defaults as $key => $default ) {
			if ( ! isset( $input[ $key ] ) ) {
				$out[ $key ] = in_array( $key, $boolean_keys, true ) ? '0' : $default;
				continue;
			}
			$value = wp_unslash( $input[ $key ] );

			if ( in_array( $key, array( 'promo_button_url', 'donate_url', 'register_url', 'alert_button_url', 'alert_button_2_url', 'alert_image', 'alert_full_image', 'alert_full_url', 'alert_left_bg_image', 'alert_left_logo', 'site_logo_url', 'social_facebook', 'social_instagram', 'social_youtube', 'social_x', 'newsletter_action', 'footer_link_1_url', 'footer_link_2_url', 'footer_link_3_url', 'footer_link_4_url', 'gi_1_url', 'gi_2_url', 'gi_3_url', 'gi_4_url', 'scholar_photo', 'scholar_btn_primary_url', 'scholar_btn_secondary_url' ), true ) ) {
				$out[ $key ] = esc_url_raw( $value );
			} elseif ( in_array( $key, array( 'footer_copyright', 'footer_legal_sub' ), true ) ) {
				// Footer legal line — allow links + basic inline tags so a
				// staff editor can wrap "Cao-Tech LLC" in an `<a>` without
				// touching the footer template. wp_kses_post strips scripts
				// and anything not in the post-content allowlist.
				$out[ $key ] = wp_kses_post( $value );
			} elseif ( 'alert_layout' === $key ) {
				$out[ $key ] = in_array( $value, array( 'split', 'image' ), true ) ? $value : 'split';
			} elseif ( in_array( $key, array( 'promo_enabled', 'alert_enabled', 'alert_dismissible', 'gtranslate_enabled', 'search_enabled' ), true ) ) {
				$out[ $key ] = '1' === $value ? '1' : '0';
			} elseif ( in_array( $key, array( 'alert_feature_1_style', 'alert_feature_2_style', 'alert_feature_3_style' ), true ) ) {
				$out[ $key ] = in_array( $value, array( 'navy', 'yellow' ), true ) ? $value : 'navy';
			} elseif ( 'promo_icon_position' === $key ) {
				$out[ $key ] = in_array( $value, array( 'before', 'after', 'none' ), true ) ? $value : 'before';
			} elseif ( 'alert_left_script_font' === $key ) {
				$choices = self::script_font_choices();
				$out[ $key ] = isset( $choices[ $value ] ) ? $value : 'allura';
			} elseif ( 'alert_left_script_size' === $key ) {
				$choices = self::script_size_choices();
				$out[ $key ] = isset( $choices[ $value ] ) ? $value : 'lg';
			} elseif ( in_array( $key, array( 'theme_color_primary', 'theme_color_primary_alt', 'theme_color_accent', 'theme_color_accent_dark', 'theme_color_bg', 'theme_color_text' ), true ) ) {
				// Hex colors — sanitize_hex_color() returns null on bad input.
				$clean       = sanitize_hex_color( $value );
				$out[ $key ] = $clean ? $clean : $default;
			} elseif ( in_array( $key, array( 'theme_btn_padding_y', 'theme_btn_padding_x', 'theme_btn_radius', 'theme_section_padding', 'theme_base_font_size' ), true ) ) {
				$n = intval( $value );
				$out[ $key ] = $n > 0 ? (string) $n : $default;
			} elseif ( 'theme_heading_weight' === $key ) {
				$w = intval( $value );
				$out[ $key ] = in_array( $w, array( 400, 500, 600, 700, 800, 900 ), true ) ? (string) $w : $default;
			} elseif ( 'theme_btn_font_size' === $key ) {
				$f = floatval( $value );
				$out[ $key ] = $f > 0 ? (string) $f : $default;
			} elseif ( in_array( $key, array( 'theme_font_heading', 'theme_font_body' ), true ) ) {
				$choices     = NPAL_Theme_Vars::font_choices();
				$out[ $key ] = isset( $choices[ $value ] ) ? $value : $default;
			} elseif ( 'google_calendar_urls' === $key ) {
				// Multiline list of URLs — sanitize each line with esc_url_raw.
				$lines = preg_split( '/[\r\n]+/', (string) $value );
				$clean = array();
				foreach ( $lines as $line ) {
					$line = trim( $line );
					if ( $line ) {
						$line = preg_replace( '#^webcal://#i', 'https://', $line );
						$url  = esc_url_raw( $line, array( 'http', 'https' ) );
						if ( $url ) { $clean[] = $url; }
					}
				}
				$out[ $key ] = implode( "\n", $clean );
				NPAL_Google_Calendar::clear_cache();
			} elseif ( 'cc_selected_campaigns' === $key ) {
				// Comma-separated campaign IDs — sanitize each segment.
				$ids = array_map( 'sanitize_text_field', explode( ',', (string) $value ) );
				$ids = array_filter( array_map( 'trim', $ids ) );
				$out[ $key ] = implode( ',', $ids );
			} elseif ( in_array( $key, array( 'cc_api_key', 'cc_api_secret', 'cc_access_token', 'cc_refresh_token' ), true ) ) {
				$out[ $key ] = sanitize_text_field( $value );
				// Clear CC cache when credentials change so next page load re-fetches.
				if ( class_exists( 'NPAL_Constant_Contact' ) ) {
					NPAL_Constant_Contact::clear_cache();
				}
			} else {
				$out[ $key ] = sanitize_text_field( $value );
			}
		}
		return $out;
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }
		$opts = wp_parse_args( get_option( NPAL_OPTION_KEY, array() ), self::defaults() );

		$tabs = array(
			'general'  => array( 'icon' => 'dashicons-admin-site',         'label' => __( 'General',      'nashua-pal-core' ) ),
			'topbars'  => array( 'icon' => 'dashicons-megaphone',          'label' => __( 'Promo Bar',    'nashua-pal-core' ) ),
			'alert'    => array( 'icon' => 'dashicons-warning',            'label' => __( 'Site Alert',   'nashua-pal-core' ) ),
			'stats'    => array( 'icon' => 'dashicons-chart-bar',          'label' => __( 'Impact Stats', 'nashua-pal-core' ) ),
			'social'   => array( 'icon' => 'dashicons-share',              'label' => __( 'Social',       'nashua-pal-core' ) ),
			'news'     => array( 'icon' => 'dashicons-email-alt',          'label' => __( 'Newsletter',   'nashua-pal-core' ) ),
			'involve'  => array( 'icon' => 'dashicons-heart',               'label' => __( 'Get Involved', 'nashua-pal-core' ) ),
			'calendar' => array( 'icon' => 'dashicons-calendar-alt',       'label' => __( 'Calendar',     'nashua-pal-core' ) ),
			'theme'    => array( 'icon' => 'dashicons-art',                'label' => __( 'Theme',        'nashua-pal-core' ) ),
		);
		?>
		<div class="wrap pal-settings-wrap">
			<h1><?php esc_html_e( 'Nashua PAL — Site Settings', 'nashua-pal-core' ); ?></h1>
			<p class="description" style="max-width:760px;"><?php esc_html_e( 'Edit site-wide content here. Changes update the header, footer, and homepage automatically. For events / programs / partners / news, use those menus on the left.', 'nashua-pal-core' ); ?></p>

			<?php
			// Show WP Settings API "Settings saved." notice. Top-level admin
			// pages (add_menu_page) don't auto-render this — only Settings >
			// sub-pages do. Without this call the user clicks Save, the save
			// succeeds, but no confirmation appears.
			settings_errors();

			// Defensive fallback: WP only stores the success message in a
			// transient until the next render. If a host's object cache loses
			// the transient between options.php's redirect and our render
			// (caching plugins / Cloudflare page rules / etc.), settings_errors()
			// shows nothing. The ?settings-updated=true query arg is set
			// unconditionally on the redirect, so use that as a backup signal.
			if ( ! empty( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated'] ) :
				$_already = false;
				global $wp_settings_errors;
				if ( is_array( $wp_settings_errors ) ) {
					foreach ( $wp_settings_errors as $err ) {
						if ( isset( $err['code'] ) && 'settings_updated' === $err['code'] ) { $_already = true; break; }
					}
				}
				if ( ! $_already ) : ?>
					<div class="notice notice-success is-dismissible"><p><strong><?php esc_html_e( 'Settings saved.', 'nashua-pal-core' ); ?></strong></p></div>
				<?php endif;
			endif;
			?>

			<style>
				.pal-settings-wrap .pal-settings-layout { display:flex; gap:24px; align-items:flex-start; margin-top:18px; }
				.pal-settings-wrap .pal-settings-tabs { flex: 0 0 220px; background:#fff; border:1px solid #c3c4c7; border-radius:6px; overflow:hidden; position:sticky; top:46px; }
				.pal-settings-wrap .pal-settings-tabs button {
					display:flex; align-items:center; gap:10px;
					width:100%; padding:12px 16px; text-align:left;
					background:#fff; border:0; border-bottom:1px solid #f0f0f1;
					color:#1d2327; font-size:14px; font-weight:600; cursor:pointer;
				}
				.pal-settings-wrap .pal-settings-tabs button:last-child { border-bottom:0; }
				.pal-settings-wrap .pal-settings-tabs button .dashicons { font-size:18px; width:18px; height:18px; color:#646970; }
				.pal-settings-wrap .pal-settings-tabs button:hover { background:#f7f9fc; }
				.pal-settings-wrap .pal-settings-tabs button.is-active { background:#071F3F; color:#F4A51C; }
				.pal-settings-wrap .pal-settings-tabs button.is-active .dashicons { color:#F4A51C; }
				.pal-settings-wrap .pal-settings-form { flex: 1 1 auto; min-width:0; }
				.pal-settings-wrap .pal-tab-panel { background:#fff; border:1px solid #c3c4c7; border-radius:6px; padding:18px 24px; }
				.pal-settings-wrap .pal-tab-panel h2 { margin:0 0 4px; font-size:18px; font-weight:700; color:#071F3F; }
				.pal-settings-wrap .pal-tab-panel .pal-tab-desc { color:#646970; font-size:13px; margin:0 0 18px; }
				.pal-settings-wrap .pal-tab-panel.is-hidden { display:none; }
				.pal-settings-wrap .pal-stat-block { background:#f7f9fc; border-radius:6px; padding:14px 18px; margin:10px 0 14px; }
				.pal-settings-wrap .pal-stat-block strong { color:#071F3F; font-size:13px; letter-spacing:0.04em; text-transform:uppercase; }
				.pal-settings-wrap .form-table { margin-top:6px; }
				.pal-settings-wrap .form-table th { padding-left:0; }
				.pal-settings-wrap .pal-submit-row { margin-top:18px; padding-top:14px; border-top:1px solid #f0f0f1; }
				@media (max-width: 960px) {
					.pal-settings-wrap .pal-settings-layout { flex-direction:column; }
					.pal-settings-wrap .pal-settings-tabs { position:static; flex:0 0 auto; width:100%; display:flex; flex-wrap:wrap; }
					.pal-settings-wrap .pal-settings-tabs button { flex: 1 1 auto; border-bottom:0; border-right:1px solid #f0f0f1; }
				}

				/* Site Alert tab — preview on top (full width), fields below */
				.pal-alert-edit-preview {
					background: #fff;
					border: 1px solid #c3c4c7;
					border-radius: 8px;
					padding: 16px 18px 18px;
					margin: 8px 0 18px;
					box-shadow: 0 4px 14px rgba(0,0,0,0.05);
				}
				.pal-alert-edit-preview--top { /* full-width variant at top of tab */ }
				.pal-alert-edit-preview__head {
					display:flex; align-items:center; justify-content:space-between;
					margin: 0 0 12px;
					padding-bottom: 10px;
					border-bottom: 1px solid #f0f0f1;
					flex-wrap: wrap;
					gap: 8px;
				}
				.pal-alert-edit-preview__head strong { font-size: 12px; text-transform: uppercase; letter-spacing: .06em; color: #071F3F; }
				.pal-alert-edit-preview__head .description { font-size: 11px; color: #5D6B7A; }
				.pal-alert-preview-frame {
					background: #324356;
					border-radius: 10px;
					padding: 32px 24px;
					display: flex;
					align-items: center;
					justify-content: center;
					min-height: 420px;
					overflow: hidden;
				}
				.pal-alert-preview-frame--off {
					background: #F7F9FC;
					border: 1px dashed #C7D2DC;
				}
				.pal-alert-preview-frame__inner {
					width: 100%;
					display: block;
				}
				.pal-alert-preview-frame--off .pal-alert-preview-frame__inner {
					display: flex;
					flex-direction: column;
					align-items: center;
					justify-content: center;
					padding: 30px 16px;
				}

				/* Collapsible field groups — replace the always-open .pal-stat-block */
				.pal-alert-group {
					background: #fff;
					border: 1px solid #c3c4c7;
					border-radius: 6px;
					margin: 0 0 10px;
					overflow: hidden;
				}
				.pal-alert-group > summary {
					list-style: none;
					cursor: pointer;
					padding: 12px 16px;
					font-size: 13px;
					font-weight: 700;
					color: #071F3F;
					background: #F7F9FC;
					border-bottom: 1px solid transparent;
					display: flex;
					align-items: center;
					justify-content: space-between;
					transition: background .12s ease;
					user-select: none;
				}
				.pal-alert-group > summary::-webkit-details-marker { display: none; }
				.pal-alert-group > summary::after {
					content: "▸";
					font-size: 12px;
					color: #5D6B7A;
					transition: transform .15s ease;
				}
				.pal-alert-group[open] > summary { border-bottom-color: #f0f0f1; }
				.pal-alert-group[open] > summary::after { transform: rotate(90deg); }
				.pal-alert-group > summary:hover { background: #EEF3FA; }
				.pal-alert-group > p.description,
				.pal-alert-group > .form-table,
				.pal-alert-group > .pal-feature-row {
					margin: 0;
					padding: 0 18px;
				}
				.pal-alert-group > p.description { padding-top: 12px; padding-bottom: 4px; }
				.pal-alert-group > .form-table { padding-top: 4px; padding-bottom: 12px; }
				.pal-alert-group > .pal-feature-row { padding-bottom: 6px; }
				.pal-alert-group > .pal-feature-row + .pal-feature-row { border-top: 1px dashed #E4EAF2; padding-top: 8px; margin-top: 4px; }
				.pal-alert-group .form-table th { padding: 10px 0; width: 200px; }
				.pal-alert-group .form-table td { padding: 8px 0; }

				.pal-alert-toplevel { margin: 0 0 14px !important; background: #fff; border: 1px solid #c3c4c7; border-radius: 6px; padding: 6px 16px; }
				.pal-alert-toplevel th { padding: 10px 0 !important; }
				.pal-alert-toplevel td { padding: 8px 0 !important; }

				@media (max-width: 900px) {
					.pal-alert-preview-frame { min-height: 320px; padding: 22px 14px; }
				}
			</style>

			<form method="post" action="options.php" class="pal-settings-form-outer" novalidate>
				<?php settings_fields( 'nashua_pal_group' ); ?>
				<div class="pal-settings-layout">
					<nav class="pal-settings-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Settings sections', 'nashua-pal-core' ); ?>">
						<?php foreach ( $tabs as $key => $tab ) : ?>
							<button type="button" data-pal-tab="<?php echo esc_attr( $key ); ?>" role="tab">
								<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
								<span><?php echo esc_html( $tab['label'] ); ?></span>
							</button>
						<?php endforeach; ?>
					</nav>

					<div class="pal-settings-form">

						<!-- TAB: General -->
						<div class="pal-tab-panel" data-pal-panel="general">
							<h2><?php esc_html_e( 'General', 'nashua-pal-core' ); ?></h2>
							<p class="pal-tab-desc"><?php esc_html_e( 'Core URLs and contact info used everywhere on the site.', 'nashua-pal-core' ); ?></p>
							<table class="form-table" role="presentation">
								<?php $this->image_row( 'site_logo_url', __( 'Site Logo (high-res)', 'nashua-pal-core' ), $opts, __( 'Upload a high-resolution PAL logo (recommended 1024×1024 or larger PNG, or an SVG). Used everywhere [pal_logo] renders — header, mobile menu drawer, footer, alerts. Fallback to the bundled 250×250 logo if empty (which appears soft when displayed above ~125px). Strongly recommended for sharp rendering on retina displays.', 'nashua-pal-core' ) ); ?>
								<?php $this->text_row( 'donate_url',   __( 'Donate URL',         'nashua-pal-core' ), $opts, 'url' ); ?>
								<?php $this->text_row( 'register_url', __( 'Register URL',       'nashua-pal-core' ), $opts, 'url' ); ?>
								<?php $this->text_row( 'phone',        __( 'Phone',              'nashua-pal-core' ), $opts ); ?>
								<?php $this->text_row( 'email',        __( 'Email',              'nashua-pal-core' ), $opts ); ?>
								<?php $this->text_row( 'address',      __( 'Mailing address',    'nashua-pal-core' ), $opts ); ?>

								<tr><th colspan="2" style="padding-top:22px;"><strong><?php esc_html_e( 'Footer Legal Line', 'nashua-pal-core' ); ?></strong>
									<p class="description" style="font-weight:400;margin-top:4px;"><?php
										printf(
											/* translators: %s: literal {year} token */
											esc_html__( 'Bottom strip of the footer. The %s token is replaced with the current year on every page load. Both fields accept basic HTML (links, bold, italic).', 'nashua-pal-core' ),
											'<code>{year}</code>'
										);
									?></p>
								</th></tr>
								<tr>
									<th scope="row"><label for="footer_copyright"><?php esc_html_e( 'Copyright text', 'nashua-pal-core' ); ?></label></th>
									<td><input type="text" id="footer_copyright" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[footer_copyright]" value="<?php echo esc_attr( $opts['footer_copyright'] ); ?>" class="large-text" /></td>
								</tr>
								<tr>
									<th scope="row"><label for="footer_legal_sub"><?php esc_html_e( 'Sub-line', 'nashua-pal-core' ); ?></label></th>
									<td><input type="text" id="footer_legal_sub" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[footer_legal_sub]" value="<?php echo esc_attr( $opts['footer_legal_sub'] ); ?>" class="large-text" />
										<p class="description"><?php esc_html_e( 'E.g. "501(c)(3) nonprofit · Powered by Cao-Tech LLC". Add an <a href=…> tag to make any word clickable.', 'nashua-pal-core' ); ?></p>
									</td>
								</tr>

								<tr><th colspan="2" style="padding-top:18px;"><strong><?php esc_html_e( 'Footer Bottom Links', 'nashua-pal-core' ); ?></strong>
									<p class="description" style="font-weight:400;margin-top:4px;"><?php esc_html_e( 'The right-side link row at the bottom of the footer. Up to 4 links — leave a slot\'s label OR URL blank to omit it from the rendered list. URLs accept either an absolute https:// link or a relative path like /privacy/.', 'nashua-pal-core' ); ?></p>
								</th></tr>
								<?php for ( $i = 1; $i <= 4; $i++ ) :
									$label_key = 'footer_link_' . $i . '_label';
									$url_key   = 'footer_link_' . $i . '_url';
									?>
									<tr>
										<th scope="row"><label for="<?php echo esc_attr( $label_key ); ?>"><?php
											/* translators: %d: slot number 1-4 */
											printf( esc_html__( 'Link %d', 'nashua-pal-core' ), $i );
										?></label></th>
										<td>
											<input type="text" id="<?php echo esc_attr( $label_key ); ?>" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[<?php echo esc_attr( $label_key ); ?>]" value="<?php echo esc_attr( $opts[ $label_key ] ); ?>" placeholder="<?php esc_attr_e( 'Label', 'nashua-pal-core' ); ?>" style="width:200px;margin-right:8px;" />
											<input type="text" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[<?php echo esc_attr( $url_key ); ?>]" value="<?php echo esc_attr( $opts[ $url_key ] ); ?>" placeholder="<?php esc_attr_e( 'https://… or /relative-path/', 'nashua-pal-core' ); ?>" style="width:320px;" />
										</td>
									</tr>
								<?php endfor; ?>

								<tr><th colspan="2" style="padding-top:22px;"><strong><?php esc_html_e( 'Header Tools', 'nashua-pal-core' ); ?></strong></th></tr>
								<tr><th><?php esc_html_e( 'Show search', 'nashua-pal-core' ); ?></th><td><label><input type="checkbox" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[search_enabled]" value="1" <?php checked( $opts['search_enabled'], '1' ); ?> /> <?php esc_html_e( 'Show the search field in the top bar', 'nashua-pal-core' ); ?></label></td></tr>
								<tr><th><?php esc_html_e( 'Show language switcher', 'nashua-pal-core' ); ?></th><td><label><input type="checkbox" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[gtranslate_enabled]" value="1" <?php checked( $opts['gtranslate_enabled'], '1' ); ?> /> <?php esc_html_e( 'Show the Google Translate dropdown', 'nashua-pal-core' ); ?></label></td></tr>
							</table>
						</div>

						<!-- TAB: Promo Bar -->
						<div class="pal-tab-panel is-hidden" data-pal-panel="topbars">
							<h2><?php esc_html_e( 'Promo Bar', 'nashua-pal-core' ); ?></h2>
							<p class="pal-tab-desc"><?php esc_html_e( 'The yellow bar above the site header — always visible (not dismissible). For dismissible alerts and event popups, use the Site Alert tab.', 'nashua-pal-core' ); ?></p>
							<table class="form-table" role="presentation">
								<tr><th><?php esc_html_e( 'Show promo bar', 'nashua-pal-core' ); ?></th><td><label><input type="checkbox" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[promo_enabled]" value="1" <?php checked( $opts['promo_enabled'], '1' ); ?> /> <?php esc_html_e( 'Display the yellow bar above the header', 'nashua-pal-core' ); ?></label></td></tr>
								<?php $this->text_row( 'promo_text',        __( 'Promo text',   'nashua-pal-core' ), $opts ); ?>
								<?php $this->icon_picker_row( 'promo_icon', __( 'Icon',         'nashua-pal-core' ), $opts ); ?>
								<tr>
									<th scope="row"><label for="promo_icon_position"><?php esc_html_e( 'Icon position', 'nashua-pal-core' ); ?></label></th>
									<td>
										<select id="promo_icon_position" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[promo_icon_position]">
											<option value="before" <?php selected( $opts['promo_icon_position'], 'before' ); ?>><?php esc_html_e( 'Before message',  'nashua-pal-core' ); ?></option>
											<option value="after"  <?php selected( $opts['promo_icon_position'], 'after' );  ?>><?php esc_html_e( 'After message',   'nashua-pal-core' ); ?></option>
											<option value="none"   <?php selected( $opts['promo_icon_position'], 'none' );   ?>><?php esc_html_e( 'No icon',         'nashua-pal-core' ); ?></option>
										</select>
										<p class="description"><?php esc_html_e( 'Where the icon renders relative to the message text.', 'nashua-pal-core' ); ?></p>
									</td>
								</tr>
								<?php $this->text_row( 'promo_button_text', __( 'Button label', 'nashua-pal-core' ), $opts ); ?>
								<?php $this->text_row( 'promo_button_url',  __( 'Button URL',   'nashua-pal-core' ), $opts, 'url' ); ?>
							</table>
						</div>

						<!-- TAB: Site Alert (preview on top, fields below) -->
						<div class="pal-tab-panel is-hidden" data-pal-panel="alert">
							<h2><?php esc_html_e( 'Site Alert (popup modal)', 'nashua-pal-core' ); ?></h2>
							<p class="pal-tab-desc"><?php esc_html_e( 'Centered popup with a dimmed backdrop, shown to every visitor on the front end until they dismiss it. The preview below updates live as you type — hit Save Changes to publish.', 'nashua-pal-core' ); ?></p>

							<!-- Live preview — full width at the top -->
							<div class="pal-alert-edit-preview pal-alert-edit-preview--top">
								<div class="pal-alert-edit-preview__head">
									<strong><?php esc_html_e( 'Live preview', 'nashua-pal-core' ); ?></strong>
									<span class="pal-alert-edit-preview__hint description"><?php esc_html_e( 'Text updates as you type · images and icons update on Save Changes', 'nashua-pal-core' ); ?></span>
								</div>
								<?php $this->render_alert_live_preview( $opts ); ?>
							</div>

							<script>
							/* Site Alert live preview wiring. Must run AFTER the form fields
							   below this preview have been parsed into the DOM, so we defer
							   to DOMContentLoaded. (The inline-script-before-form-fields ordering
							   was the silent failure reason in 1.8.23 — every getInputForKey()
							   returned null because no inputs existed yet.) */
							(function(){
								function init() {
									var preview = document.querySelector('.pal-alert-preview-frame');
									if (!preview) return;
									var fieldsRoot = document.querySelector('.pal-alert-edit-fields');
									if (!fieldsRoot) return;
									var optionKey = <?php echo wp_json_encode( NPAL_OPTION_KEY ); ?>;

									// Build name → input lookup. Iterating once and indexing
									// avoids any CSS-attribute-selector escaping problems
									// caused by the brackets in `nashua_pal_settings[key]`.
									var inputMap = {};
									var prefix = optionKey + '[';
									Array.prototype.forEach.call(
										fieldsRoot.querySelectorAll('input[name], textarea[name], select[name]'),
										function(el){
											var name = el.getAttribute('name') || '';
											if (name.indexOf(prefix) === 0 && name.charAt(name.length - 1) === ']') {
												var key = name.substring(prefix.length, name.length - 1);
												inputMap[key] = el;
											}
										}
									);

									// Map preview elements with data-pal-live="<setting_key>".
									var liveMap = {};
									Array.prototype.forEach.call(
										preview.querySelectorAll('[data-pal-live]'),
										function(el){
											var key = el.getAttribute('data-pal-live');
											(liveMap[key] = liveMap[key] || []).push(el);
										}
									);

									function setText(el, value, multiline) {
										if (multiline) {
											el.innerHTML = value
												? value.split(/\n{2,}/).map(function(p){
													return '<p>' + p
														.replace(/[<>&]/g, function(c){return {'<':'&lt;','>':'&gt;','&':'&amp;'}[c];})
														.replace(/\n/g, '<br />') + '</p>';
												}).join('')
												: '';
										} else {
											el.textContent = value || '';
										}
										if (value) el.removeAttribute('hidden');
										else el.setAttribute('hidden', '');
									}

									function syncKey(key) {
										var input = inputMap[key];
										var targets = liveMap[key];
										if (!input || !targets) return;
										var value = input.value;
										targets.forEach(function(el){
											var multiline = el.getAttribute('data-pal-live-multiline') === '1';
											setText(el, value, multiline);
										});
									}

									// Wire all text-mirrored fields. Iterate inputMap so we
									// also sync keys that have an input but no current preview
									// element (for first-time setups where the element will
									// be created on Save).
									Object.keys(liveMap).forEach(function(key){
										if (!inputMap[key]) return;
										inputMap[key].addEventListener('input', function(){ syncKey(key); });
										syncKey(key);
									});

									// Buttons: hide entirely when label OR url is empty.
									function syncButton(textKey, urlKey) {
										var btn = preview.querySelector('[data-pal-live-text-target="' + textKey + '"]');
										if (!btn) return;
										var textInput = inputMap[textKey];
										var urlInput  = inputMap[urlKey];
										if (!textInput || !urlInput) return;
										function update() {
											btn.setAttribute('href', urlInput.value || '#');
											if (textInput.value && urlInput.value) btn.removeAttribute('hidden');
											else btn.setAttribute('hidden', '');
											syncActionsContainerVisibility();
										}
										textInput.addEventListener('input', update);
										urlInput.addEventListener('input', update);
										update();
									}
									function syncActionsContainerVisibility() {
										var actions = preview.querySelector('[data-pal-live-actions]');
										if (!actions) return;
										if (actions.querySelectorAll('a:not([hidden])').length > 0) {
											actions.removeAttribute('hidden');
										} else {
											actions.setAttribute('hidden', '');
										}
									}
									syncButton('alert_button_text',   'alert_button_url');
									syncButton('alert_button_2_text', 'alert_button_2_url');

									// Footer container visibility tied to the footer text input.
									(function(){
										var footerInput = inputMap['alert_footer_text'];
										var footerEl    = preview.querySelector('[data-pal-live-footer]');
										if (!footerInput || !footerEl) return;
										function update() {
											if (footerInput.value) footerEl.removeAttribute('hidden');
											else footerEl.setAttribute('hidden', '');
										}
										footerInput.addEventListener('input', update);
										update();
									})();

									/* Icons: the icon-picker thumbnail in the form already renders
									   the SVG/IMG via NPAL_Icons. We mirror that markup into the
									   preview using a MutationObserver — when the user picks an
									   icon, the picker JS re-renders the thumbnail; we copy the
									   resulting innerHTML into the matching preview icon slot. */
									function syncIcon(iconKey, slotKey) {
										var input = inputMap[iconKey];
										if (!input) return;
										var fieldRoot = input.closest('[data-pal-icon-field]');
										if (!fieldRoot) return;
										var thumb = fieldRoot.querySelector('[data-pal-icon-preview]');
										var previewIcon = preview.querySelector('[data-pal-live-icon="' + iconKey + '"]');
										if (!thumb || !previewIcon) return;
										var previewLi = previewIcon.closest('[data-pal-live-feature-slot], [data-pal-live-detail-slot]');

										function update() {
											previewIcon.innerHTML = thumb.innerHTML || '';
											// Slot visibility — show LI if icon OR label has content.
											if (previewLi) {
												var labelEl = previewLi.querySelector('.pal-alert-modal__feature-label, .pal-alert-modal__left-detail-text');
												var hasIcon  = !!input.value;
												var hasLabel = labelEl && labelEl.textContent && labelEl.textContent.trim().length > 0;
												if (hasIcon || hasLabel) previewLi.removeAttribute('hidden');
												else previewLi.setAttribute('hidden', '');
												refreshSlotContainerVisibility(previewLi);
											}
											// Left-side icons can flip whether the entire left
											// panel should show — re-evaluate.
											if (iconKey.indexOf('alert_left_') === 0 && typeof syncLeftPanelMode === 'function') {
												syncLeftPanelMode();
											}
										}

										// Picker JS swaps the thumbnail's innerHTML AND fires no
										// event, so use MutationObserver to detect the change.
										var observer = new MutationObserver(update);
										observer.observe(thumb, { childList: true, subtree: true });

										// Also sync when the input value itself is changed (e.g.
										// someone manually types an icon name into the input).
										input.addEventListener('input', update);
										update();
									}

									function refreshSlotContainerVisibility(slotLi) {
										var ul = slotLi.parentNode;
										if (!ul) return;
										var visibleSlots = ul.querySelectorAll('li:not([hidden])').length;
										if (visibleSlots > 0) ul.removeAttribute('hidden');
										else ul.setAttribute('hidden', '');
									}

									// Wire all icon slots — features (right panel) + details (left panel)
									for (var i = 1; i <= 3; i++) {
										syncIcon('alert_feature_' + i + '_icon', i);
										syncIcon('alert_left_detail_' + i + '_icon', i);
									}

									// Feature/detail LABEL inputs also need to toggle their LI
									// visibility (so typing a label without an icon shows the slot).
									function wireSlotLabel(labelKey, liSelectorAttr, slotIndex) {
										var input = inputMap[labelKey];
										if (!input) return;
										var li = preview.querySelector('[' + liSelectorAttr + '="' + slotIndex + '"]');
										if (!li) return;
										var isLeft = (liSelectorAttr === 'data-pal-live-detail-slot');
										function update() {
											var iconKey = isLeft
												? 'alert_left_detail_' + slotIndex + '_icon'
												: 'alert_feature_' + slotIndex + '_icon';
											var iconInput = inputMap[iconKey];
											var hasIcon  = iconInput && iconInput.value;
											var hasLabel = input.value && input.value.trim().length > 0;
											if (hasIcon || hasLabel) li.removeAttribute('hidden');
											else li.setAttribute('hidden', '');
											refreshSlotContainerVisibility(li);
											if (isLeft && typeof syncLeftPanelMode === 'function') {
												syncLeftPanelMode();
											}
										}
										input.addEventListener('input', update);
										update();
									}
									for (var j = 1; j <= 3; j++) {
										wireSlotLabel('alert_feature_'      + j + '_label', 'data-pal-live-feature-slot', j);
										wireSlotLabel('alert_left_detail_'  + j + '_text',  'data-pal-live-detail-slot',  j);
									}

									/* Left-panel reactivity: simple-mode image (alert_image) overlays
									   and overrides the structured panel. JS toggles which is
									   visible based on input state, plus the modal's --with-image
									   class which controls the desktop two-column layout. */
									var modalEl       = preview.querySelector('.pal-alert-modal');
									var simpleImageEl = preview.querySelector('[data-pal-live-image]');
									var structuredEl  = preview.querySelector('[data-pal-live-structured-left]');
									var leftLogoEl    = preview.querySelector('.pal-alert-modal__left-logo');

									function leftHasStructuredContent() {
										// Any of the structured-left fields with content?
										var keys = [
											'alert_left_bg_image','alert_left_logo',
											'alert_left_subtitle','alert_left_title',
											'alert_left_script','alert_left_year',
											'alert_left_strip_text',
											'alert_left_detail_1_icon','alert_left_detail_1_text',
											'alert_left_detail_2_icon','alert_left_detail_2_text',
											'alert_left_detail_3_icon','alert_left_detail_3_text'
										];
										for (var k = 0; k < keys.length; k++) {
											var inp = inputMap[keys[k]];
											if (inp && inp.value && inp.value.trim().length > 0) return true;
										}
										return false;
									}

									function syncLeftPanelMode() {
										if (!simpleImageEl || !structuredEl || !modalEl) return;
										var simpleVal      = inputMap['alert_image'] ? inputMap['alert_image'].value : '';
										var hasSimple      = !!simpleVal;
										var hasStructured  = leftHasStructuredContent();
										var anyLeftContent = hasSimple || hasStructured;

										// Simple mode wins when set
										if (hasSimple) {
											simpleImageEl.style.backgroundImage = 'url(' + simpleVal + ')';
											simpleImageEl.removeAttribute('hidden');
											structuredEl.setAttribute('hidden', '');
										} else {
											simpleImageEl.setAttribute('hidden', '');
											simpleImageEl.style.backgroundImage = '';
											if (hasStructured) structuredEl.removeAttribute('hidden');
											else structuredEl.setAttribute('hidden', '');
										}

										// Two-column desktop layout class
										if (anyLeftContent) modalEl.classList.add('pal-alert-modal--with-image');
										else modalEl.classList.remove('pal-alert-modal--with-image');
									}

									// alert_image (simple-mode overlay)
									if (inputMap['alert_image']) {
										inputMap['alert_image'].addEventListener('input', syncLeftPanelMode);
									}

									// alert_left_bg_image — applied as background-image on
									// .pal-alert-modal__left
									if (inputMap['alert_left_bg_image'] && structuredEl) {
										var bgInput = inputMap['alert_left_bg_image'];
										function syncBg() {
											var v = bgInput.value || '';
											if (v) structuredEl.style.backgroundImage = 'url(' + v + ')';
											else structuredEl.style.backgroundImage = '';
											syncLeftPanelMode();
										}
										bgInput.addEventListener('input', syncBg);
										syncBg();
									}

									// alert_left_logo — applied as src on the logo <img>
									if (inputMap['alert_left_logo'] && leftLogoEl) {
										var logoInput = inputMap['alert_left_logo'];
										function syncLogo() {
											var v = logoInput.value || '';
											if (v) {
												leftLogoEl.setAttribute('src', v);
												leftLogoEl.removeAttribute('hidden');
											} else {
												leftLogoEl.removeAttribute('src');
												leftLogoEl.setAttribute('hidden', '');
											}
											syncLeftPanelMode();
										}
										logoInput.addEventListener('input', syncLogo);
										syncLogo();
									}

									// Any structured-text input changing should re-evaluate
									// whether the left panel as a whole should show/hide.
									['alert_left_subtitle','alert_left_title','alert_left_script',
									 'alert_left_year','alert_left_strip_text',
									 'alert_left_detail_1_text','alert_left_detail_2_text','alert_left_detail_3_text',
									 'alert_left_detail_1_icon','alert_left_detail_2_icon','alert_left_detail_3_icon'
									].forEach(function(key){
										var inp = inputMap[key];
										if (inp) inp.addEventListener('input', syncLeftPanelMode);
									});

									// Also re-evaluate after icon picker mutations.
									var leftIconObservers = preview.querySelectorAll('[data-pal-live-detail-slot] [data-pal-live-icon]');
									leftIconObservers.forEach(function(){ /* observed in syncIcon already; container sync triggered there */ });

									// Initial sync
									syncLeftPanelMode();

									/* Layout-mode toggle: show/hide the "Single image" details
									   section + the hint paragraph as user flips between
									   Designed layout / Single uploaded graphic. */
									(function(){
										var radios = document.querySelectorAll('[data-pal-alert-layout]');
										var imageSection = document.querySelector('[data-pal-alert-mode-section="image"]');
										var hint = document.querySelector('[data-pal-alert-mode-hint]');
										if (!radios.length) return;
										function update() {
											var mode = 'split';
											radios.forEach(function(r){ if (r.checked) mode = r.value; });
											if (imageSection) {
												if (mode === 'image') imageSection.setAttribute('open', '');
												else imageSection.removeAttribute('open');
											}
											if (hint) hint.style.display = (mode === 'image') ? '' : 'none';
										}
										radios.forEach(function(r){ r.addEventListener('change', update); });
									})();

									/* Script-font picker: the picker writes a slug like "allura"
									   to the hidden input. Find the corresponding font-family
									   stack from the picker buttons (which carry it via
									   data-pal-font-family) and apply it to the preview's
									   script element inline style. */
									(function(){
										var fontInput = inputMap['alert_left_script_font'];
										var scriptEl  = preview.querySelector('[data-pal-live="alert_left_script"]');
										if (!fontInput || !scriptEl) return;
										var grid = document.querySelector('[data-pal-script-font-grid]');
										function applyFromSlug(slug) {
											if (!grid) return;
											var btn = grid.querySelector('[data-pal-font="' + slug + '"]');
											if (!btn) return;
											var fam = btn.getAttribute('data-pal-font-family');
											if (fam) scriptEl.style.fontFamily = fam;
										}
										fontInput.addEventListener('input', function(){ applyFromSlug(fontInput.value); });
										fontInput.addEventListener('change', function(){ applyFromSlug(fontInput.value); });
										applyFromSlug(fontInput.value);
									})();

									/* Script-size picker: applies the chosen size pair
									   (mobile + desktop) as CSS variables on the script
									   element. The shortcode's CSS reads these vars
									   inside its base + min-width:760px rules. */
									(function(){
										var sizeInput = inputMap['alert_left_script_size'];
										var scriptEl  = preview.querySelector('[data-pal-live="alert_left_script"]');
										if (!sizeInput || !scriptEl) return;
										var grid = document.querySelector('[data-pal-script-size-grid]');
										function applyFromSlug(slug) {
											if (!grid) return;
											var btn = grid.querySelector('[data-pal-size="' + slug + '"]');
											if (!btn) return;
											var m = btn.getAttribute('data-pal-size-mobile');
											var d = btn.getAttribute('data-pal-size-desktop');
											if (m) scriptEl.style.setProperty('--pal-script-size', m);
											if (d) scriptEl.style.setProperty('--pal-script-size-desktop', d);
										}
										sizeInput.addEventListener('input', function(){ applyFromSlug(sizeInput.value); });
										sizeInput.addEventListener('change', function(){ applyFromSlug(sizeInput.value); });
										applyFromSlug(sizeInput.value);
									})();
								}

								if (document.readyState === 'loading') {
									document.addEventListener('DOMContentLoaded', init);
								} else {
									init();
								}
							})();
							</script>

							<div class="pal-alert-edit-fields">

									<table class="form-table pal-alert-toplevel" role="presentation">
										<tr><th><?php esc_html_e( 'Show alert', 'nashua-pal-core' ); ?></th><td><label><input type="checkbox" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[alert_enabled]" value="1" <?php checked( $opts['alert_enabled'], '1' ); ?> /> <?php esc_html_e( 'Show the alert popup site-wide', 'nashua-pal-core' ); ?></label></td></tr>
										<tr><th><?php esc_html_e( 'Dismissible', 'nashua-pal-core' ); ?></th><td><label><input type="checkbox" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[alert_dismissible]" value="1" <?php checked( $opts['alert_dismissible'], '1' ); ?> /> <?php esc_html_e( 'Visitor can close it (close X button + ESC + click backdrop)', 'nashua-pal-core' ); ?></label></td></tr>
										<tr>
											<th scope="row"><?php esc_html_e( 'Layout mode', 'nashua-pal-core' ); ?></th>
											<td>
												<label style="display:block;margin-bottom:6px;">
													<input type="radio" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[alert_layout]" value="split" <?php checked( $opts['alert_layout'], 'split' ); ?> data-pal-alert-layout />
													<strong><?php esc_html_e( 'Designed layout', 'nashua-pal-core' ); ?></strong> — <?php esc_html_e( 'structured left + right panel built from the fields below.', 'nashua-pal-core' ); ?>
												</label>
												<label style="display:block;">
													<input type="radio" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[alert_layout]" value="image" <?php checked( $opts['alert_layout'], 'image' ); ?> data-pal-alert-layout />
													<strong><?php esc_html_e( 'Single uploaded graphic', 'nashua-pal-core' ); ?></strong> — <?php esc_html_e( 'just upload one designed image (e.g. an event flyer made in Canva). Skips all the field-by-field building.', 'nashua-pal-core' ); ?>
												</label>
											</td>
										</tr>
									</table>

									<details class="pal-alert-group" data-pal-alert-mode-section="image"<?php echo 'image' === $opts['alert_layout'] ? ' open' : ''; ?>>
										<summary><?php esc_html_e( 'Single image (used when Layout mode = Single uploaded graphic)', 'nashua-pal-core' ); ?></summary>
										<p class="description"><?php esc_html_e( 'Upload one designed graphic that becomes the entire popup. The close × button is overlaid on top automatically. Optionally set a click URL so the whole popup links somewhere when tapped. Recommended dimensions: 800×800 to 1200×1200, square or portrait.', 'nashua-pal-core' ); ?></p>
										<table class="form-table" role="presentation">
											<?php $this->image_row( 'alert_full_image', __( 'Designed graphic', 'nashua-pal-core' ), $opts ); ?>
											<?php $this->text_row(  'alert_full_url',   __( 'Click URL (optional)', 'nashua-pal-core' ), $opts, 'url' ); ?>
										</table>
									</details>

									<p class="description" data-pal-alert-mode-hint style="margin:6px 0 14px;<?php echo 'image' === $opts['alert_layout'] ? '' : 'display:none;'; ?>">
										<?php esc_html_e( 'Layout mode is "Single uploaded graphic" — the structured fields below are ignored on the front end. Switch back to Designed layout above to use them.', 'nashua-pal-core' ); ?>
									</p>

									<details class="pal-alert-group" open>
										<summary><?php esc_html_e( 'Left panel — simple mode (one designed graphic)', 'nashua-pal-core' ); ?></summary>
										<p class="description"><?php esc_html_e( 'Optional. Upload one designed graphic for the entire left panel. When set, this OVERRIDES every "Left panel — structured" field below. Recommended ~600×800 portrait.', 'nashua-pal-core' ); ?></p>
										<table class="form-table" role="presentation">
											<?php $this->image_row( 'alert_image', __( 'Designed image', 'nashua-pal-core' ), $opts ); ?>
										</table>
									</details>

									<details class="pal-alert-group">
										<summary><?php esc_html_e( 'Left panel — structured (logo, titles, details, strip)', 'nashua-pal-core' ); ?></summary>
										<p class="description"><?php esc_html_e( 'Used when the simple-mode image above is empty. Each field renders with the brand styling — navy background, yellow accents, white text.', 'nashua-pal-core' ); ?></p>
										<table class="form-table" role="presentation">
											<?php $this->image_row( 'alert_left_bg_image', __( 'Background image (optional)', 'nashua-pal-core' ), $opts, __( 'Decorative — e.g. confetti. Behind everything else.', 'nashua-pal-core' ) ); ?>
											<?php $this->image_row( 'alert_left_logo',     __( 'Logo image',                   'nashua-pal-core' ), $opts ); ?>
											<?php $this->text_row(  'alert_left_subtitle', __( 'Subtitle (small caps)',        'nashua-pal-core' ), $opts ); ?>
											<?php $this->text_row(  'alert_left_title',    __( 'Title (block)',                'nashua-pal-core' ), $opts ); ?>
											<?php $this->text_row(  'alert_left_script',   __( 'Script line (cursive yellow)', 'nashua-pal-core' ), $opts ); ?>
											<?php $this->script_font_row( 'alert_left_script_font', __( 'Script font', 'nashua-pal-core' ), $opts ); ?>
											<?php $this->script_size_row( 'alert_left_script_size', __( 'Script size', 'nashua-pal-core' ), $opts ); ?>
											<?php $this->text_row(  'alert_left_year',     __( 'Year / line under script',     'nashua-pal-core' ), $opts ); ?>
										</table>
										<p class="description" style="margin-top:14px;"><strong><?php esc_html_e( 'Detail rows', 'nashua-pal-core' ); ?></strong> — <?php esc_html_e( 'three icon + text rows (date / time / location). Leave blank to skip.', 'nashua-pal-core' ); ?></p>
										<table class="form-table" role="presentation">
											<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
												<?php $this->icon_picker_row( "alert_left_detail_{$i}_icon", sprintf( __( 'Detail %d icon', 'nashua-pal-core' ), $i ), $opts ); ?>
												<?php $this->text_row(        "alert_left_detail_{$i}_text", sprintf( __( 'Detail %d text', 'nashua-pal-core' ), $i ), $opts ); ?>
											<?php endfor; ?>
											<?php $this->text_row( 'alert_left_strip_text', __( 'Yellow footer strip', 'nashua-pal-core' ), $opts ); ?>
										</table>
									</details>

									<details class="pal-alert-group" open>
										<summary><?php esc_html_e( 'Right panel — content', 'nashua-pal-core' ); ?></summary>
										<table class="form-table" role="presentation">
											<?php $this->text_row( 'alert_eyebrow', __( 'Eyebrow label (small caps)', 'nashua-pal-core' ), $opts ); ?>
											<?php $this->text_row( 'alert_title',   __( 'Title',                      'nashua-pal-core' ), $opts ); ?>
											<?php $this->text_row( 'alert_text',    __( 'Message',                    'nashua-pal-core' ), $opts ); ?>
										</table>
									</details>

									<details class="pal-alert-group">
										<summary><?php esc_html_e( 'Right panel — feature icons (3 circles)', 'nashua-pal-core' ); ?></summary>
										<p class="description"><?php esc_html_e( 'Three round icon callouts under the message. Leave the icon blank to skip a slot.', 'nashua-pal-core' ); ?></p>
										<?php for ( $i = 1; $i <= 3; $i++ ) : ?>
											<div class="pal-feature-row">
												<table class="form-table" role="presentation">
													<?php $this->icon_picker_row( "alert_feature_{$i}_icon",  sprintf( __( 'Feature %d icon',  'nashua-pal-core' ), $i ), $opts ); ?>
													<?php $this->text_row(        "alert_feature_{$i}_label", sprintf( __( 'Feature %d label', 'nashua-pal-core' ), $i ), $opts ); ?>
													<tr>
														<th scope="row"><label for="alert_feature_<?php echo $i; ?>_style"><?php echo esc_html( sprintf( __( 'Feature %d style', 'nashua-pal-core' ), $i ) ); ?></label></th>
														<td>
															<select id="alert_feature_<?php echo $i; ?>_style" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[alert_feature_<?php echo $i; ?>_style]">
																<option value="navy"   <?php selected( $opts[ "alert_feature_{$i}_style" ], 'navy' ); ?>><?php esc_html_e( 'Navy circle (yellow icon)',  'nashua-pal-core' ); ?></option>
																<option value="yellow" <?php selected( $opts[ "alert_feature_{$i}_style" ], 'yellow' ); ?>><?php esc_html_e( 'Yellow circle (navy icon)','nashua-pal-core' ); ?></option>
															</select>
														</td>
													</tr>
												</table>
											</div>
										<?php endfor; ?>
									</details>

									<details class="pal-alert-group" open>
										<summary><?php esc_html_e( 'Right panel — buttons & footer', 'nashua-pal-core' ); ?></summary>
										<table class="form-table" role="presentation">
											<?php $this->text_row( 'alert_button_text',   __( 'Primary button label',   'nashua-pal-core' ), $opts ); ?>
											<?php $this->text_row( 'alert_button_url',    __( 'Primary button URL',     'nashua-pal-core' ), $opts, 'url' ); ?>
											<?php $this->text_row( 'alert_button_2_text', __( 'Secondary button label', 'nashua-pal-core' ), $opts ); ?>
											<?php $this->text_row( 'alert_button_2_url',  __( 'Secondary button URL',   'nashua-pal-core' ), $opts, 'url' ); ?>
											<?php $this->text_row( 'alert_footer_text',   __( 'Footer line (heart icon)', 'nashua-pal-core' ), $opts ); ?>
										</table>
									</details>

							</div>
						</div>

						<!-- TAB: Stats -->
						<div class="pal-tab-panel is-hidden" data-pal-panel="stats">
							<h2><?php esc_html_e( 'Impact Stats', 'nashua-pal-core' ); ?></h2>
							<p class="pal-tab-desc"><?php esc_html_e( 'The 4-stat navy bar on the homepage. Numbers animate when scrolled into view. Icons accept either a built-in icon name (shield-check, users, target, handshake) or a Font Awesome class (e.g. "fas fa-shield-alt").', 'nashua-pal-core' ); ?></p>
							<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
								<div class="pal-stat-block">
									<strong><?php echo esc_html( sprintf( __( 'Stat %d', 'nashua-pal-core' ), $i ) ); ?></strong>
									<table class="form-table" role="presentation">
										<?php $this->text_row( "stat_{$i}_number", __( 'Number', 'nashua-pal-core' ), $opts ); ?>
										<?php $this->text_row( "stat_{$i}_label",  __( 'Label',  'nashua-pal-core' ), $opts ); ?>
										<?php $this->icon_picker_row( "stat_{$i}_icon", __( 'Icon', 'nashua-pal-core' ), $opts ); ?>
									</table>
								</div>
							<?php endfor; ?>
						</div>

						<!-- TAB: Social -->
						<div class="pal-tab-panel is-hidden" data-pal-panel="social">
							<h2><?php esc_html_e( 'Social Links', 'nashua-pal-core' ); ?></h2>
							<p class="pal-tab-desc"><?php esc_html_e( 'Used in the header toolbar and footer. Leave a field blank to hide that icon everywhere.', 'nashua-pal-core' ); ?></p>
							<table class="form-table" role="presentation">
								<?php $this->text_row( 'social_facebook',  __( 'Facebook URL',    'nashua-pal-core' ), $opts, 'url' ); ?>
								<?php $this->text_row( 'social_instagram', __( 'Instagram URL',   'nashua-pal-core' ), $opts, 'url' ); ?>
								<?php $this->text_row( 'social_youtube',   __( 'YouTube URL',     'nashua-pal-core' ), $opts, 'url' ); ?>
								<?php $this->text_row( 'social_x',         __( 'X (Twitter) URL', 'nashua-pal-core' ), $opts, 'url' ); ?>
							</table>
						</div>

						<!-- TAB: Newsletter -->
						<div class="pal-tab-panel is-hidden" data-pal-panel="news">
							<h2><?php esc_html_e( 'Newsletter (footer signup)', 'nashua-pal-core' ); ?></h2>
							<p class="pal-tab-desc"><?php esc_html_e( 'Point the form at Mailchimp / Constant Contact / your provider\'s subscribe-action URL. Visitors\' submissions go straight to that service.', 'nashua-pal-core' ); ?></p>
							<table class="form-table" role="presentation">
								<?php $this->text_row( 'newsletter_label',  __( 'Heading text', 'nashua-pal-core' ), $opts ); ?>
								<?php $this->text_row( 'newsletter_button', __( 'Button label', 'nashua-pal-core' ), $opts ); ?>
								<?php $this->text_row( 'newsletter_action', __( 'Form action URL', 'nashua-pal-core' ), $opts, 'url' ); ?>
							</table>

							<hr style="margin:32px 0;" />

							<h2><?php esc_html_e( 'Constant Contact API (auto-import newsletters)', 'nashua-pal-core' ); ?></h2>
							<p class="pal-tab-desc"><?php esc_html_e( 'Connect your Constant Contact account to automatically import sent email campaigns as Newsletter cards in Latest News. Requires a CC Developer application with campaign:read scope.', 'nashua-pal-core' ); ?></p>

							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="cc_sync_enabled"><?php esc_html_e( 'Enable auto-sync', 'nashua-pal-core' ); ?></label></th>
									<td>
										<label>
											<input type="checkbox" id="cc_sync_enabled" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[cc_sync_enabled]" value="1" <?php checked( $opts['cc_sync_enabled'], '1' ); ?> />
											<?php esc_html_e( 'Automatically import sent campaigns from Constant Contact', 'nashua-pal-core' ); ?>
										</label>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="cc_api_key"><?php esc_html_e( 'API Key (Client ID)', 'nashua-pal-core' ); ?></label></th>
									<td>
										<input type="text" id="cc_api_key" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[cc_api_key]" value="<?php echo esc_attr( $opts['cc_api_key'] ); ?>" class="regular-text" autocomplete="off" />
										<p class="description"><?php esc_html_e( 'From your Constant Contact Developer application.', 'nashua-pal-core' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row"><label for="cc_api_secret"><?php esc_html_e( 'API Secret (Client Secret)', 'nashua-pal-core' ); ?></label></th>
									<td>
										<input type="password" id="cc_api_secret" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[cc_api_secret]" value="<?php echo esc_attr( $opts['cc_api_secret'] ); ?>" class="regular-text" autocomplete="off" />
									</td>
								</tr>
							</table>

							<?php
							// --- OAuth2 Connect / Disconnect UI ---
							$has_tokens   = ! empty( $opts['cc_access_token'] );
							$has_api_key  = ! empty( $opts['cc_api_key'] ) && ! empty( $opts['cc_api_secret'] );
							$redirect_uri = NPAL_Constant_Contact::get_redirect_uri();
							?>

							<?php if ( isset( $_GET['npal_cc_connected'] ) ) : ?>
								<div class="notice notice-success inline" style="margin:12px 0;"><p><?php esc_html_e( 'Successfully connected to Constant Contact!', 'nashua-pal-core' ); ?></p></div>
							<?php elseif ( isset( $_GET['npal_cc_disconnected'] ) ) : ?>
								<div class="notice notice-info inline" style="margin:12px 0;"><p><?php esc_html_e( 'Disconnected from Constant Contact.', 'nashua-pal-core' ); ?></p></div>
							<?php endif; ?>

							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><?php esc_html_e( 'Authorization', 'nashua-pal-core' ); ?></th>
									<td>
										<?php if ( $has_tokens ) : ?>
											<span style="color:#00a32a;font-weight:600;">&#10003; <?php esc_html_e( 'Connected', 'nashua-pal-core' ); ?></span>
											&nbsp;&nbsp;
											<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => NPAL_Settings::PAGE_SLUG, 'npal_cc_disconnect' => '1' ), admin_url( 'admin.php' ) ), 'npal_cc_disconnect' ) ); ?>" class="button button-secondary" onclick="return confirm('<?php esc_attr_e( 'Disconnect from Constant Contact? You can reconnect at any time.', 'nashua-pal-core' ); ?>');"><?php esc_html_e( 'Disconnect', 'nashua-pal-core' ); ?></a>
										<?php elseif ( $has_api_key ) : ?>
											<?php $auth_url = NPAL_Constant_Contact::get_authorize_url(); ?>
											<a href="<?php echo esc_url( $auth_url ); ?>" class="button button-primary" style="font-size:14px;padding:4px 16px;"><?php esc_html_e( 'Connect to Constant Contact', 'nashua-pal-core' ); ?></a>
											<p class="description" style="margin-top:8px;"><?php esc_html_e( 'Click to authorize this site with your Constant Contact account. You will be redirected to Constant Contact and back.', 'nashua-pal-core' ); ?></p>
										<?php else : ?>
											<p class="description" style="color:#d63638;"><?php esc_html_e( 'Enter your API Key and API Secret above, then save settings. The Connect button will appear here.', 'nashua-pal-core' ); ?></p>
										<?php endif; ?>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Redirect URI', 'nashua-pal-core' ); ?></th>
									<td>
										<code style="display:inline-block;padding:6px 10px;background:#f0f0f1;user-select:all;"><?php echo esc_html( $redirect_uri ); ?></code>
										<p class="description"><?php esc_html_e( 'Copy this URL and add it as a Redirect URI in your Constant Contact Developer application settings.', 'nashua-pal-core' ); ?></p>
									</td>
								</tr>
							</table>

							<?php if ( $has_tokens ) :
								$available_campaigns = NPAL_Constant_Contact::get_available_campaigns();
								$selected_csv = isset( $opts['cc_selected_campaigns'] ) ? $opts['cc_selected_campaigns'] : '';
								$selected_ids = $selected_csv ? array_map( 'trim', explode( ',', $selected_csv ) ) : array();
							?>
							<hr style="margin:24px 0;" />
							<h3 style="margin:18px 0 6px;color:#071F3F;"><?php esc_html_e( 'Campaign Filter', 'nashua-pal-core' ); ?></h3>

							<?php if ( isset( $_GET['npal_cc_campaigns_loaded'] ) ) : ?>
								<div class="notice notice-success inline" style="margin:12px 0;"><p><?php printf( esc_html__( '%d campaigns loaded.', 'nashua-pal-core' ), (int) $_GET['npal_cc_campaigns_loaded'] ); ?></p></div>
							<?php elseif ( isset( $_GET['npal_cc_campaigns_error'] ) ) : ?>
								<div class="notice notice-error inline" style="margin:12px 0;"><p><?php printf( esc_html__( 'Failed to load campaigns: %s', 'nashua-pal-core' ), esc_html( urldecode( $_GET['npal_cc_campaigns_error'] ) ) ); ?></p></div>
							<?php endif; ?>

							<p style="margin-bottom:12px;">
								<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => NPAL_Settings::PAGE_SLUG, 'npal_cc_load_campaigns' => '1' ), admin_url( 'admin.php' ) ), 'npal_cc_load_campaigns' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Load Available Campaigns', 'nashua-pal-core' ); ?></a>
								<span class="description" style="margin-left:8px;"><?php esc_html_e( 'Fetch sent campaigns from Constant Contact.', 'nashua-pal-core' ); ?></span>
							</p>

							<?php if ( ! empty( $available_campaigns ) ) : ?>
							<fieldset style="background:#f0f0f1;border:1px solid #c3c4c7;padding:12px 16px;margin:8px 0 16px;max-width:600px;">
								<legend style="font-weight:600;padding:0 6px;"><?php esc_html_e( 'Select campaigns to import', 'nashua-pal-core' ); ?></legend>
								<?php foreach ( $available_campaigns as $ac ) :
									$checked = in_array( $ac['id'], $selected_ids, true );
									$label   = esc_html( $ac['name'] );
									if ( ! empty( $ac['date'] ) ) {
										$ts = strtotime( $ac['date'] );
										if ( $ts ) {
											$label .= ' <small style="color:#666;">(' . esc_html( wp_date( 'M j, Y', $ts ) ) . ')</small>';
										}
									}
								?>
								<label style="display:block;margin:6px 0;cursor:pointer;">
									<input type="checkbox" class="npal-cc-campaign-cb" value="<?php echo esc_attr( $ac['id'] ); ?>" <?php checked( $checked ); ?> />
									<?php echo $label; // Already escaped above. ?>
								</label>
								<?php endforeach; ?>
								<p class="description" style="margin-top:10px;"><?php esc_html_e( 'Select which campaigns to import as newsletters. Leave all unchecked to sync everything.', 'nashua-pal-core' ); ?></p>
							</fieldset>
							<input type="hidden" id="npal_cc_selected_campaigns" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[cc_selected_campaigns]" value="<?php echo esc_attr( $selected_csv ); ?>" />
							<script>
							(function(){
								var cbs = document.querySelectorAll('.npal-cc-campaign-cb');
								var hidden = document.getElementById('npal_cc_selected_campaigns');
								function sync(){
									var ids = [];
									cbs.forEach(function(cb){ if(cb.checked) ids.push(cb.value); });
									hidden.value = ids.join(',');
								}
								cbs.forEach(function(cb){ cb.addEventListener('change', sync); });
							})();
							</script>
							<?php else : ?>
								<p class="description" style="margin:8px 0;"><?php esc_html_e( 'No campaigns loaded yet. Click "Load Available Campaigns" to see your sent campaigns.', 'nashua-pal-core' ); ?></p>
								<input type="hidden" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[cc_selected_campaigns]" value="<?php echo esc_attr( $selected_csv ); ?>" />
							<?php endif; ?>
							<?php endif; ?>

							<!-- Hidden: preserve token values during form submission -->
							<input type="hidden" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[cc_access_token]" value="<?php echo esc_attr( $opts['cc_access_token'] ); ?>" />
							<input type="hidden" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[cc_refresh_token]" value="<?php echo esc_attr( $opts['cc_refresh_token'] ); ?>" />

							<?php if ( isset( $_GET['npal_cc_synced'] ) ) : ?>
								<div class="notice notice-success inline" style="margin:12px 0;"><p><?php printf( esc_html__( 'Sync complete! %d newsletters imported.', 'nashua-pal-core' ), (int) $_GET['npal_cc_synced'] ); ?></p></div>
							<?php elseif ( isset( $_GET['npal_cc_sync_error'] ) ) : ?>
								<div class="notice notice-error inline" style="margin:12px 0;"><p><?php printf( esc_html__( 'Sync failed: %s', 'nashua-pal-core' ), esc_html( urldecode( $_GET['npal_cc_sync_error'] ) ) ); ?></p></div>
							<?php endif; ?>

							<?php if ( class_exists( 'NPAL_Constant_Contact' ) ) :
								$status = NPAL_Constant_Contact::get_sync_status();
							?>
							<div style="background:#f0f0f1;border-left:4px solid <?php echo $status['ok'] ? '#00a32a' : '#d63638'; ?>;padding:12px 16px;margin:16px 0;">
								<strong><?php esc_html_e( 'Sync Status:', 'nashua-pal-core' ); ?></strong>
								<?php echo esc_html( $status['message'] ); ?>
								<?php if ( $status['last_sync'] ) : ?>
									<br /><small><?php printf( esc_html__( 'Last sync: %s', 'nashua-pal-core' ), esc_html( $status['last_sync'] ) ); ?></small>
								<?php endif; ?>
							</div>
							<?php if ( NPAL_Constant_Contact::has_credentials() ) : ?>
								<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => NPAL_Settings::PAGE_SLUG, 'npal_cc_sync_now' => '1' ), admin_url( 'admin.php' ) ), 'npal_cc_sync_now' ) ); ?>" class="button button-secondary" style="margin-top:4px;"><?php esc_html_e( 'Sync Now', 'nashua-pal-core' ); ?></a>
								<span class="description" style="margin-left:8px;"><?php esc_html_e( 'Manually fetch and import newsletters from Constant Contact.', 'nashua-pal-core' ); ?></span>
							<?php endif; ?>
							<?php endif; ?>
						</div>

						<!-- TAB: Get Involved -->
						<div class="pal-tab-panel is-hidden" data-pal-panel="involve">
							<h2><?php esc_html_e( 'Get Involved Strip', 'nashua-pal-core' ); ?></h2>
							<p class="pal-tab-desc"><?php esc_html_e( 'Edit the icon, title, description, and link for each row in the [pal_get_involved] shortcode. Leave a row\'s Name blank to hide it.', 'nashua-pal-core' ); ?></p>
							<?php for ( $gi = 1; $gi <= 4; $gi++ ) :
								$prefix = 'gi_' . $gi . '_';
							?>
								<div class="pal-stat-block">
									<strong><?php printf( esc_html__( 'Row %d', 'nashua-pal-core' ), $gi ); ?></strong>
									<table class="form-table" role="presentation" style="margin-top:0;">
										<?php $this->icon_picker_row( $prefix . 'icon', __( 'Icon', 'nashua-pal-core' ), $opts ); ?>
										<tr>
											<th scope="row"><label for="<?php echo esc_attr( $prefix . 'name' ); ?>"><?php esc_html_e( 'Name', 'nashua-pal-core' ); ?></label></th>
											<td><input type="text" id="<?php echo esc_attr( $prefix . 'name' ); ?>" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[<?php echo esc_attr( $prefix . 'name' ); ?>]" value="<?php echo esc_attr( $opts[ $prefix . 'name' ] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Volunteer', 'nashua-pal-core' ); ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><label for="<?php echo esc_attr( $prefix . 'desc' ); ?>"><?php esc_html_e( 'Description', 'nashua-pal-core' ); ?></label></th>
											<td><input type="text" id="<?php echo esc_attr( $prefix . 'desc' ); ?>" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[<?php echo esc_attr( $prefix . 'desc' ); ?>]" value="<?php echo esc_attr( $opts[ $prefix . 'desc' ] ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'Give your time and make a difference.', 'nashua-pal-core' ); ?>" /></td>
										</tr>
										<tr>
											<th scope="row"><label for="<?php echo esc_attr( $prefix . 'url' ); ?>"><?php esc_html_e( 'Link URL', 'nashua-pal-core' ); ?></label></th>
											<td><input type="text" id="<?php echo esc_attr( $prefix . 'url' ); ?>" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[<?php echo esc_attr( $prefix . 'url' ); ?>]" value="<?php echo esc_attr( $opts[ $prefix . 'url' ] ); ?>" class="regular-text" placeholder="<?php esc_attr_e( '/volunteer', 'nashua-pal-core' ); ?>" /></td>
										</tr>
									</table>
								</div>
							<?php endfor; ?>
						</div>

						<!-- TAB: Calendar -->
						<div class="pal-tab-panel is-hidden" data-pal-panel="calendar">
							<h2><?php esc_html_e( 'Google Calendar Feed (Upcoming Events)', 'nashua-pal-core' ); ?></h2>
							<p class="pal-tab-desc">
								<?php esc_html_e( 'Paste public Google Calendar iCal URLs (one per line). Events appear alongside any added manually under Events → Add Event. Cached for 15 minutes.', 'nashua-pal-core' ); ?>
								<a href="https://support.google.com/calendar/answer/37648?hl=en#zippy=%2Cget-your-calendar-view-only" target="_blank" rel="noopener" style="margin-left:6px;"><?php esc_html_e( 'How to find a calendar\'s iCal URL →', 'nashua-pal-core' ); ?></a>
							</p>
							<table class="form-table" role="presentation">
								<tr>
									<th scope="row"><label for="google_calendar_urls"><?php esc_html_e( 'Calendar iCal URLs', 'nashua-pal-core' ); ?></label></th>
									<td>
										<textarea id="google_calendar_urls" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[google_calendar_urls]" rows="5" cols="60" class="large-text code" placeholder="https://calendar.google.com/calendar/ical/.../basic.ics&#10;https://calendar.google.com/calendar/ical/.../basic.ics"><?php echo esc_textarea( $opts['google_calendar_urls'] ); ?></textarea>
										<p class="description"><?php esc_html_e( 'One URL per line. The calendar must be public.', 'nashua-pal-core' ); ?></p>
									</td>
								</tr>
							</table>
						</div>

						<div class="pal-tab-panel is-hidden" data-pal-panel="theme">
							<h2><?php esc_html_e( 'Theme', 'nashua-pal-core' ); ?></h2>
							<p class="pal-tab-desc"><?php esc_html_e( 'Re-skin the site without touching code — brand colors, button size, section spacing, typography. Changes apply on the front-end as soon as you Save (override the bundled stylesheet via an inline <style> block at the end of <head>).', 'nashua-pal-core' ); ?></p>

							<h3 style="margin:18px 0 6px;color:#071F3F;"><?php esc_html_e( 'Brand colors', 'nashua-pal-core' ); ?></h3>
							<table class="form-table" role="presentation">
								<?php $this->theme_color_row( 'theme_color_primary',     __( 'Primary (navy)',                 'nashua-pal-core' ), __( 'The main dark brand color — used for headers, footers, headings, hero gradients.',          'nashua-pal-core' ), $opts ); ?>
								<?php $this->theme_color_row( 'theme_color_primary_alt', __( 'Primary gradient stop',          'nashua-pal-core' ), __( 'Lighter navy used as the second stop in hero/section gradients.',                          'nashua-pal-core' ), $opts ); ?>
								<?php $this->theme_color_row( 'theme_color_accent',      __( 'Accent (yellow / gold)',         'nashua-pal-core' ), __( 'Primary CTA color — the Donate button, promo bar, accent highlights.',                       'nashua-pal-core' ), $opts ); ?>
								<?php $this->theme_color_row( 'theme_color_accent_dark', __( 'Accent (hover)',                 'nashua-pal-core' ), __( 'Darker yellow used when CTA buttons are hovered.',                                          'nashua-pal-core' ), $opts ); ?>
								<?php $this->theme_color_row( 'theme_color_bg',          __( 'Light background',               'nashua-pal-core' ), __( 'Soft gray panel background — past-events strip, sponsor strip, donate tier rows.',          'nashua-pal-core' ), $opts ); ?>
								<?php $this->theme_color_row( 'theme_color_text',       __( 'Body text',                      'nashua-pal-core' ), __( 'Default paragraph & caption color across the site.',                                        'nashua-pal-core' ), $opts ); ?>
							</table>

							<h3 style="margin:24px 0 6px;color:#071F3F;"><?php esc_html_e( 'Buttons', 'nashua-pal-core' ); ?></h3>
							<table class="form-table" role="presentation">
								<?php $this->theme_number_row( 'theme_btn_padding_y', __( 'Vertical padding (px)',   'nashua-pal-core' ), $opts, 4, 60,  __( 'Top/bottom padding on the .pal-btn / [pal_donate_button].',  'nashua-pal-core' ) ); ?>
								<?php $this->theme_number_row( 'theme_btn_padding_x', __( 'Horizontal padding (px)', 'nashua-pal-core' ), $opts, 4, 100, __( 'Left/right padding on the .pal-btn / [pal_donate_button].',  'nashua-pal-core' ) ); ?>
								<?php $this->theme_number_row( 'theme_btn_radius',    __( 'Border radius (px)',      'nashua-pal-core' ), $opts, 0, 60,  __( 'Higher = rounder. Set to a large number for fully-pill buttons.', 'nashua-pal-core' ) ); ?>
								<?php $this->theme_float_row(  'theme_btn_font_size', __( 'Font size (rem)',         'nashua-pal-core' ), $opts, 0.6, 2, __( 'Button label size, in rem (1 rem = base body font size).',  'nashua-pal-core' ) ); ?>
							</table>

							<h3 style="margin:24px 0 6px;color:#071F3F;"><?php esc_html_e( 'Spacing', 'nashua-pal-core' ); ?></h3>
							<table class="form-table" role="presentation">
								<?php $this->theme_number_row( 'theme_section_padding', __( 'Section padding (px)',  'nashua-pal-core' ), $opts, 16, 200, __( 'Top + bottom padding on .pal-section (hero band, page sections). Tighten for a denser look.', 'nashua-pal-core' ) ); ?>
							</table>

							<h3 style="margin:24px 0 6px;color:#071F3F;"><?php esc_html_e( 'Typography', 'nashua-pal-core' ); ?></h3>
							<table class="form-table" role="presentation">
								<?php $this->theme_font_row( 'theme_font_heading', __( 'Heading font', 'nashua-pal-core' ), $opts ); ?>
								<?php $this->theme_font_row( 'theme_font_body',    __( 'Body font',    'nashua-pal-core' ), $opts ); ?>
								<tr>
									<th scope="row"><label for="theme_heading_weight"><?php esc_html_e( 'Heading weight', 'nashua-pal-core' ); ?></label></th>
									<td>
										<select id="theme_heading_weight" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[theme_heading_weight]">
											<?php foreach ( array( 400 => '400 — Normal', 500 => '500 — Medium', 600 => '600 — Semibold', 700 => '700 — Bold', 800 => '800 — Extra Bold', 900 => '900 — Black' ) as $w => $lbl ) : ?>
												<option value="<?php echo (int) $w; ?>" <?php selected( (string) $opts['theme_heading_weight'], (string) $w ); ?>><?php echo esc_html( $lbl ); ?></option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								<?php $this->theme_number_row( 'theme_base_font_size', __( 'Base font size (px)',  'nashua-pal-core' ), $opts, 12, 22, __( 'Body text size. Affects rem-based scaling across the site.', 'nashua-pal-core' ) ); ?>
							</table>
						</div>

						<div class="pal-submit-row">
							<?php submit_button( __( 'Save Changes', 'nashua-pal-core' ), 'primary', 'submit', false ); ?>
							<span class="description" style="margin-left:14px;"><?php esc_html_e( 'Saving updates every tab — you don\'t have to save each section separately.', 'nashua-pal-core' ); ?></span>
						</div>
					</div>
				</div>
			</form>

			<?php NPAL_Icon_Picker::modal(); ?>

			<script>
			(function(){
				const tabs   = document.querySelectorAll('.pal-settings-tabs button[data-pal-tab]');
				const panels = document.querySelectorAll('.pal-tab-panel[data-pal-panel]');
				if (!tabs.length || !panels.length) return;

				function activate(name) {
					tabs.forEach(t => t.classList.toggle('is-active', t.dataset.palTab === name));
					panels.forEach(p => p.classList.toggle('is-hidden', p.dataset.palPanel !== name));
					try { localStorage.setItem('pal_settings_tab', name); } catch(e) {}
					if (history.replaceState) history.replaceState(null, '', '#' + name);
				}

				tabs.forEach(t => t.addEventListener('click', () => activate(t.dataset.palTab)));

				// Restore last-used tab on load — URL hash wins, then localStorage, else first tab.
				let initial = (location.hash || '').replace('#', '');
				if (!initial) { try { initial = localStorage.getItem('pal_settings_tab') || ''; } catch(e) {} }
				if (!initial || !document.querySelector('[data-pal-panel="' + initial + '"]')) {
					initial = tabs[0].dataset.palTab;
				}
				activate(initial);
			})();

			// Theme tab — wire WP color pickers (1.8.55).
			jQuery(function($){
				if (typeof $.fn.wpColorPicker === 'function') {
					$('.pal-color-picker').wpColorPicker();
				}
			});
			</script>
		</div>
		<?php
	}

	// (icon picker modal lives below the form)

	/**
	 * Theme tab — color picker row. Renders a text input with the
	 * .pal-color-picker class; an inline jQuery init turns each one into a
	 * WP color picker on DOMContentLoaded.
	 */
	private function theme_color_row( $key, $label, $description, $opts ) {
		$value = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="pal-color-picker" data-default-color="<?php echo esc_attr( $value ); ?>" />
				<?php if ( $description ) : ?><p class="description"><?php echo esc_html( $description ); ?></p><?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Theme tab — integer-with-min/max input row.
	 */
	private function theme_number_row( $key, $label, $opts, $min, $max, $description = '' ) {
		$value = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input type="number" min="<?php echo (int) $min; ?>" max="<?php echo (int) $max; ?>" step="1" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="small-text" />
				<?php if ( $description ) : ?><p class="description"><?php echo esc_html( $description ); ?></p><?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Theme tab — float-with-min/max input row (e.g. rem-based font sizes).
	 */
	private function theme_float_row( $key, $label, $opts, $min, $max, $description = '' ) {
		$value = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input type="number" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" step="0.05" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="small-text" />
				<?php if ( $description ) : ?><p class="description"><?php echo esc_html( $description ); ?></p><?php endif; ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Theme tab — font picker row. Curated Google Fonts list from
	 * NPAL_Theme_Vars::font_choices(). Each `<option>` is styled with the
	 * font it represents so the user previews while selecting.
	 */
	private function theme_font_row( $key, $label, $opts ) {
		$value   = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
		$choices = NPAL_Theme_Vars::font_choices();
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<select id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]">
					<?php foreach ( $choices as $slug => $def ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>" style="font-family: '<?php echo esc_attr( $def['family'] ); ?>', sans-serif;" <?php selected( $value, $slug ); ?>><?php echo esc_html( $def['family'] ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Loaded from Google Fonts. Heading + body fonts are bundled into a single request.', 'nashua-pal-core' ); ?></p>
			</td>
		</tr>
		<?php
	}

	private function text_row( $key, $label, $opts, $type = 'text' ) {
		$value = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
		// `url` callers expect the field to accept full URLs, but the browser's
		// type="url" validation rejects relative paths like "/programs/". Render
		// as type="text" with a hint placeholder; server-side `esc_url_raw()`
		// already handles both forms cleanly.
		$input_type = ( 'url' === $type ) ? 'text' : $type;
		$placeholder = ( 'url' === $type ) ? 'https://… or /relative-path/' : '';
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><input type="<?php echo esc_attr( $input_type ); ?>" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="regular-text"<?php if ( $placeholder ) : ?> placeholder="<?php echo esc_attr( $placeholder ); ?>"<?php endif; ?> /></td>
		</tr>
		<?php
	}

	/**
	 * Renders an actual scaled-down version of the live popup using the
	 * current saved values, scoped inside .pal-alert-edit-preview so the
	 * front-end's `position: fixed` doesn't escape into the admin chrome.
	 * Static (PHP from saved values) — admins see what the real visitor sees.
	 */
	private function render_alert_live_preview( $opts ) {
		$enabled = '1' === ( isset( $opts['alert_enabled'] ) ? $opts['alert_enabled'] : '0' );
		if ( ! $enabled ) {
			?>
			<div class="pal-alert-preview-frame pal-alert-preview-frame--off">
				<div class="pal-alert-preview-frame__inner">
					<span class="dashicons dashicons-warning" style="font-size:36px;width:36px;height:36px;color:#a7aaaf;"></span>
					<p style="margin:8px 0 0;color:#646970;font-size:13px;text-align:center;"><?php esc_html_e( 'Alert is currently OFF.', 'nashua-pal-core' ); ?><br /><?php esc_html_e( 'Toggle "Show alert" above to preview.', 'nashua-pal-core' ); ?></p>
				</div>
			</div>
			<?php
			return;
		}

		// Render the real shortcode output, then transform it to a non-fixed
		// inline preview by stripping the modal wrapper styling.
		$html = do_shortcode( '[pal_alert]' );

		// Reset the static so subsequent renders work (the shortcode method
		// short-circuits if it has already rendered once on this request).
		if ( method_exists( 'NPAL_Shortcodes', 'reset_alert_render_flag' ) ) {
			NPAL_Shortcodes::reset_alert_render_flag();
		}

		if ( ! $html ) {
			?>
			<div class="pal-alert-preview-frame pal-alert-preview-frame--off">
				<div class="pal-alert-preview-frame__inner">
					<p style="margin:0;color:#646970;font-size:13px;text-align:center;"><?php esc_html_e( 'No content yet — fill in any of the fields below to populate the preview.', 'nashua-pal-core' ); ?></p>
				</div>
			</div>
			<?php
			return;
		}

		// Force the front-end CSS to load on the settings page so the preview
		// looks identical to the live popup (font-awesome + nashua-pal.css +
		// Allura font are all enqueued once via wp_enqueue_style — calling
		// here makes sure they're in the admin footer if not already).
		wp_enqueue_style( 'nashua-pal-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2' );
		wp_enqueue_style( 'nashua-pal-fonts', 'https://fonts.googleapis.com/css2?family=Allura&display=swap', array(), null );
		wp_enqueue_style( 'nashua-pal-core', NPAL_PLUGIN_URL . 'assets/css/nashua-pal.css', array( 'nashua-pal-fontawesome' ), NPAL_VERSION );
		?>
		<div class="pal-alert-preview-frame">
			<div class="pal-alert-preview-frame__inner">
				<?php echo $html; ?>
			</div>
		</div>
		<style>
			/* Override the front-end fixed/overlay positioning ONLY inside the
			   preview frame, so the popup renders as an inline block at full
			   desktop size. The frame is full-width at the top of the tab. */
			.pal-alert-preview-frame .pal-alert-modal {
				position: relative !important;
				inset: auto !important;
				padding: 0 !important;
				opacity: 1 !important;
				pointer-events: auto !important;
				display: block !important;
				z-index: 1 !important;
			}
			.pal-alert-preview-frame .pal-alert-modal__overlay { display: none !important; }
			.pal-alert-preview-frame .pal-alert-modal__close   { display: none !important; }
			.pal-alert-preview-frame .pal-alert-modal__panel {
				transform: none !important;
				width: 100%;
				max-width: 940px;
				margin: 0 auto;
				max-height: none !important;
				box-shadow: 0 6px 16px rgba(7, 31, 63, 0.18);
			}
			/* Stack columns on narrow admin widths */
			@media (max-width: 880px) {
				.pal-alert-preview-frame .pal-alert-modal--with-image .pal-alert-modal__panel { flex-direction: column !important; }
				.pal-alert-preview-frame .pal-alert-modal--with-image .pal-alert-modal__image,
				.pal-alert-preview-frame .pal-alert-modal--with-image .pal-alert-modal__left {
					flex: 0 0 auto !important;
					min-height: 240px !important;
				}
			}
		</style>
		<?php
	}

	/**
	 * Visual wireframe showing which Site Alert popup region each field
	 * controls. Brand-colored (navy / yellow / white) faux-popup mockup with
	 * the exact field labels overlaid on each section, so admins can scan
	 * from the diagram down to the form fields and know what they're editing.
	 * Pure HTML/CSS — no images bundled.
	 */
	private function render_alert_diagram() {
		?>
		<div class="pal-alert-diagram" aria-hidden="true">
			<style>
				.pal-alert-diagram { margin: 0 0 18px; }
				.pal-alert-diagram__head {
					display:flex; align-items:center; justify-content:space-between;
					margin: 0 0 10px;
				}
				.pal-alert-diagram__head h4 { margin:0; font-size:13px; font-weight:700; color:#071F3F; text-transform:uppercase; letter-spacing:.06em; }
				.pal-alert-diagram__head p  { margin:0; font-size:12px; color:#5D6B7A; }
				.pal-alert-diagram__stage {
					position: relative;
					background: #324356;
					border-radius: 12px;
					padding: 36px 40px;
					box-shadow: inset 0 0 0 1px rgba(0,0,0,.15);
				}
				.pal-alert-diagram__close {
					position: absolute; top: 12px; right: 12px;
					width: 30px; height: 30px; border-radius: 50%;
					background: rgba(0,0,0,.45);
					border: 1.5px solid rgba(255,255,255,.85);
					display:flex; align-items:center; justify-content:center;
					color:#fff; font-size:14px; line-height:1;
				}
				.pal-alert-diagram__close-tag {
					position: absolute; top: 14px; right: 50px;
					font-size: 11px; color:#fff; font-weight:600;
					background: rgba(0,0,0,.4); padding: 3px 8px; border-radius: 4px;
					white-space: nowrap;
				}
				.pal-alert-diagram__panel {
					display: grid;
					grid-template-columns: 1fr 1fr;
					background: #fff;
					border-radius: 10px;
					overflow: hidden;
					min-height: 460px;
					box-shadow: 0 12px 30px rgba(0,0,0,.25);
				}
				@media (max-width: 720px) {
					.pal-alert-diagram__stage { padding: 24px 18px 18px; }
					.pal-alert-diagram__panel { grid-template-columns: 1fr; min-height: auto; }
				}
				.pal-alert-diagram__left,
				.pal-alert-diagram__right {
					padding: 18px 18px 14px;
					display:flex; flex-direction:column; gap:8px;
				}
				.pal-alert-diagram__left {
					background:#071F3F; color:#fff; position:relative;
					border-right: 1px solid rgba(255,255,255,.08);
				}
				.pal-alert-diagram__right { background:#fff; color:#071F3F; }

				.pal-alert-diagram__chip {
					display: inline-flex; align-items:center; gap:6px;
					padding: 6px 9px;
					font-size: 11px; font-weight: 700; letter-spacing:.04em;
					border-radius: 5px;
					border: 1px dashed currentColor;
					background: rgba(255,255,255,.06);
					line-height: 1.1;
					align-self: flex-start;
				}
				.pal-alert-diagram__right .pal-alert-diagram__chip {
					background: #F7F9FC;
					border-color: #C7D2DC;
					color: #071F3F;
				}
				.pal-alert-diagram__chip code {
					font-size: 10px; font-weight: 600;
					background: rgba(0,0,0,.18);
					padding: 2px 5px; border-radius: 3px;
					color: inherit;
				}
				.pal-alert-diagram__right .pal-alert-diagram__chip code {
					background: #DCE4EE; color:#071F3F;
				}
				.pal-alert-diagram__chip--center { align-self: center; }
				.pal-alert-diagram__bg-marker {
					position: absolute; inset: 6px 6px auto auto;
					font-size: 10px; color: rgba(255,255,255,.65);
					letter-spacing: .04em; text-transform: uppercase;
				}
				.pal-alert-diagram__details-stack {
					display:flex; flex-direction:column; gap:5px;
					margin: 4px 0 0;
				}
				.pal-alert-diagram__details-stack .pal-alert-diagram__chip {
					align-self: stretch;
				}
				.pal-alert-diagram__strip {
					margin: 8px -18px -14px;
					padding: 8px 12px;
					background: #F4A51C;
					color:#071F3F;
					text-align:center;
					font-size: 10px;
					font-weight: 800;
					letter-spacing: .12em;
					text-transform: uppercase;
					border-radius: 0;
					border: 1px dashed rgba(7,31,63,.5);
				}
				.pal-alert-diagram__features {
					display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;
					margin: 4px 0;
				}
				.pal-alert-diagram__features .pal-alert-diagram__chip {
					justify-content:center; text-align:center; align-self:auto;
				}
				.pal-alert-diagram__buttons {
					display:flex; flex-direction:column; gap:6px; margin-top: 4px;
				}
				.pal-alert-diagram__buttons .pal-alert-diagram__chip { align-self:stretch; justify-content:center; }
				.pal-alert-diagram__btn--primary {
					background: #F4A51C !important;
					border-color: #F4A51C !important;
					color: #071F3F !important;
				}
				.pal-alert-diagram__btn--secondary {
					background: #fff !important;
					border-color: #071F3F !important;
					color: #071F3F !important;
				}
				.pal-alert-diagram__legend {
					display:flex; flex-wrap:wrap; gap: 8px 14px;
					margin: 10px 0 0;
					font-size: 11px;
					color:#3D4A57;
				}
				.pal-alert-diagram__legend code {
					background: #EEF2F7; padding: 1px 5px; border-radius: 3px;
					color:#071F3F; font-size: 11px;
				}
			</style>

			<div class="pal-alert-diagram__head">
				<h4><?php esc_html_e( 'Where each field appears', 'nashua-pal-core' ); ?></h4>
				<p><?php esc_html_e( 'The dashed labels show the setting that controls each region.', 'nashua-pal-core' ); ?></p>
			</div>

			<div class="pal-alert-diagram__stage">
				<span class="pal-alert-diagram__close-tag"><?php esc_html_e( 'Always shown when', 'nashua-pal-core' ); ?> <code>Dismissible</code></span>
				<div class="pal-alert-diagram__close">×</div>

				<div class="pal-alert-diagram__panel">

					<!-- LEFT PANEL -->
					<div class="pal-alert-diagram__left">
						<span class="pal-alert-diagram__bg-marker"><?php esc_html_e( 'Bg:', 'nashua-pal-core' ); ?> <code>Background image</code></span>

						<span class="pal-alert-diagram__chip pal-alert-diagram__chip--center" style="margin-top:14px;width:48px;height:48px;justify-content:center;border-radius:50%;border-style:solid;">LOGO</span>
						<span class="pal-alert-diagram__chip pal-alert-diagram__chip--center"><?php esc_html_e( 'Subtitle', 'nashua-pal-core' ); ?></span>
						<span class="pal-alert-diagram__chip pal-alert-diagram__chip--center" style="font-size:13px;letter-spacing:.06em;"><?php esc_html_e( 'Title (block)', 'nashua-pal-core' ); ?></span>
						<span class="pal-alert-diagram__chip pal-alert-diagram__chip--center" style="font-family:'Allura','Brush Script MT',cursive;font-size:22px;color:#F4A51C;border-color:#F4A51C;letter-spacing:0;text-transform:none;"><?php esc_html_e( 'Script line', 'nashua-pal-core' ); ?></span>
						<span class="pal-alert-diagram__chip pal-alert-diagram__chip--center" style="color:#F4A51C;border-color:#F4A51C;"><?php esc_html_e( 'Year', 'nashua-pal-core' ); ?></span>

						<div class="pal-alert-diagram__details-stack">
							<span class="pal-alert-diagram__chip">📅 <?php esc_html_e( 'Detail 1 icon + text', 'nashua-pal-core' ); ?></span>
							<span class="pal-alert-diagram__chip">🕐 <?php esc_html_e( 'Detail 2 icon + text', 'nashua-pal-core' ); ?></span>
							<span class="pal-alert-diagram__chip">📍 <?php esc_html_e( 'Detail 3 icon + text', 'nashua-pal-core' ); ?></span>
						</div>

						<div class="pal-alert-diagram__strip"><?php esc_html_e( 'Yellow footer strip', 'nashua-pal-core' ); ?></div>
					</div>

					<!-- RIGHT PANEL -->
					<div class="pal-alert-diagram__right">
						<span class="pal-alert-diagram__chip pal-alert-diagram__chip--center" style="color:#F4A51C;border-color:#F4A51C;background:#FFF8E7;"><?php esc_html_e( 'Eyebrow', 'nashua-pal-core' ); ?></span>
						<span class="pal-alert-diagram__chip pal-alert-diagram__chip--center" style="font-size:13px;letter-spacing:.06em;"><?php esc_html_e( 'Title', 'nashua-pal-core' ); ?></span>
						<span class="pal-alert-diagram__chip pal-alert-diagram__chip--center" style="font-weight:500;letter-spacing:0;text-transform:none;"><?php esc_html_e( 'Message', 'nashua-pal-core' ); ?></span>

						<div class="pal-alert-diagram__features">
							<span class="pal-alert-diagram__chip" style="background:#071F3F;color:#F4A51C;border-color:#071F3F;flex-direction:column;gap:4px;padding:10px 6px;"><span style="font-size:14px;">●</span><?php esc_html_e( 'Feature 1', 'nashua-pal-core' ); ?></span>
							<span class="pal-alert-diagram__chip" style="background:#F4A51C;color:#071F3F;border-color:#F4A51C;flex-direction:column;gap:4px;padding:10px 6px;"><span style="font-size:14px;">●</span><?php esc_html_e( 'Feature 2', 'nashua-pal-core' ); ?></span>
							<span class="pal-alert-diagram__chip" style="background:#071F3F;color:#F4A51C;border-color:#071F3F;flex-direction:column;gap:4px;padding:10px 6px;"><span style="font-size:14px;">●</span><?php esc_html_e( 'Feature 3', 'nashua-pal-core' ); ?></span>
						</div>

						<div class="pal-alert-diagram__buttons">
							<span class="pal-alert-diagram__chip pal-alert-diagram__btn--primary"><?php esc_html_e( 'Primary button', 'nashua-pal-core' ); ?> →</span>
							<span class="pal-alert-diagram__chip pal-alert-diagram__btn--secondary"><?php esc_html_e( 'Secondary button', 'nashua-pal-core' ); ?></span>
						</div>

						<span class="pal-alert-diagram__chip pal-alert-diagram__chip--center" style="font-size:10px;letter-spacing:.02em;text-transform:none;border-style:dotted;">♡ <?php esc_html_e( 'Footer line', 'nashua-pal-core' ); ?></span>
					</div>

				</div>
			</div>

			<div class="pal-alert-diagram__legend">
				<span><strong><?php esc_html_e( 'Left panel:', 'nashua-pal-core' ); ?></strong> <?php esc_html_e( 'edited under', 'nashua-pal-core' ); ?> <code><?php esc_html_e( 'Left panel — structured', 'nashua-pal-core' ); ?></code></span>
				<span><strong><?php esc_html_e( 'Right panel:', 'nashua-pal-core' ); ?></strong> <?php esc_html_e( 'edited under', 'nashua-pal-core' ); ?> <code><?php esc_html_e( 'Right panel — content / features / buttons', 'nashua-pal-core' ); ?></code></span>
				<span><strong><?php esc_html_e( 'Override the whole left panel:', 'nashua-pal-core' ); ?></strong> <?php esc_html_e( 'upload an image under', 'nashua-pal-core' ); ?> <code><?php esc_html_e( 'Left panel — simple mode', 'nashua-pal-core' ); ?></code></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders a media-library image picker row: thumbnail + URL input + Choose/Remove buttons.
	 * Uses wp_enqueue_media() (already called in enqueue_admin_assets).
	 */
	private function image_row( $key, $label, $opts, $description = '' ) {
		$value = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
		$id    = esc_attr( $key );
		$name  = esc_attr( NPAL_OPTION_KEY . '[' . $key . ']' );
		?>
		<tr>
			<th scope="row"><label for="<?php echo $id; ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<div class="pal-image-field" data-pal-image-field>
					<div class="pal-image-field__preview" data-pal-image-preview style="width:64px;height:64px;border-radius:6px;background:#F7F9FC;border:1px solid #E4EAF2;display:inline-flex;align-items:center;justify-content:center;overflow:hidden;vertical-align:middle;margin-right:10px;">
						<?php if ( $value ) : ?>
							<img src="<?php echo esc_url( $value ); ?>" alt="" style="max-width:100%;max-height:100%;display:block;" />
						<?php else : ?>
							<span style="color:#a7aaaf;font-size:18px;">—</span>
						<?php endif; ?>
					</div>
					<input type="text" id="<?php echo $id; ?>" name="<?php echo $name; ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text" data-pal-image-input placeholder="https://…" style="vertical-align:middle;" />
					<button type="button" class="button button-secondary" data-pal-image-choose style="vertical-align:middle;"><?php esc_html_e( 'Choose image', 'nashua-pal-core' ); ?></button>
					<button type="button" class="button button-link-delete" data-pal-image-remove style="vertical-align:middle;<?php echo $value ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'nashua-pal-core' ); ?></button>
				</div>
				<?php if ( $description ) : ?>
					<p class="description"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
				<script>
				(function(){
					var field = document.querySelector('[data-pal-image-field] [data-pal-image-input][id="<?php echo esc_js( $key ); ?>"]').closest('[data-pal-image-field]');
					if (!field || field.dataset.palImageBound) return;
					field.dataset.palImageBound = '1';
					var input   = field.querySelector('[data-pal-image-input]');
					var preview = field.querySelector('[data-pal-image-preview]');
					var chooseBtn = field.querySelector('[data-pal-image-choose]');
					var removeBtn = field.querySelector('[data-pal-image-remove]');
					var frame = null;

					function setValue(url) {
						input.value = url || '';
						if (url) {
							preview.innerHTML = '<img src="' + url.replace(/"/g, '&quot;') + '" alt="" style="max-width:100%;max-height:100%;display:block;" />';
							removeBtn.style.display = '';
						} else {
							preview.innerHTML = '<span style="color:#a7aaaf;font-size:18px;">—</span>';
							removeBtn.style.display = 'none';
						}
						// Notify any subscribers (live preview JS) that the value
						// changed programmatically — input events don't fire when
						// .value is set directly.
						input.dispatchEvent(new Event('input', { bubbles: true }));
					}

					chooseBtn.addEventListener('click', function(e){
						e.preventDefault();
						if (!window.wp || !wp.media) {
							alert('<?php echo esc_js( __( 'Media library is not available on this screen.', 'nashua-pal-core' ) ); ?>');
							return;
						}
						if (!frame) {
							frame = wp.media({
								title: '<?php echo esc_js( __( 'Choose alert image', 'nashua-pal-core' ) ); ?>',
								button: { text: '<?php echo esc_js( __( 'Use this image', 'nashua-pal-core' ) ); ?>' },
								library: { type: 'image' },
								multiple: false,
							});
							frame.on('select', function(){
								var att = frame.state().get('selection').first().toJSON();
								setValue(att.url);
							});
						}
						frame.open();
					});

					removeBtn.addEventListener('click', function(e){
						e.preventDefault();
						setValue('');
					});

					// Keep preview in sync if the user pastes a URL directly into the input.
					input.addEventListener('change', function(){
						setValue(input.value.trim());
					});
				})();
				</script>
			</td>
		</tr>
		<?php
	}

	/**
	 * Cursive-font picker row: visual cards (one per Google Font choice)
	 * styled with each font so admins can pick by appearance, not name.
	 * Also renders the chosen font's name + a sample preview live as the
	 * user clicks. The corresponding `<link>` tags for ALL options are
	 * loaded on the settings page so every preview card renders correctly.
	 */
	private function script_font_row( $key, $label, $opts ) {
		$value   = isset( $opts[ $key ] ) ? $opts[ $key ] : 'allura';
		$choices = self::script_font_choices();
		?>
		<tr>
			<th scope="row"><label><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input type="hidden" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" data-pal-script-font-input />
				<div class="pal-script-font-grid" data-pal-script-font-grid>
					<?php foreach ( $choices as $slug => $cfg ) : ?>
						<button type="button" class="pal-script-font-card<?php echo $slug === $value ? ' is-selected' : ''; ?>" data-pal-font="<?php echo esc_attr( $slug ); ?>" data-pal-font-family="<?php echo esc_attr( $cfg['family'] ); ?>" style="font-family:<?php echo esc_attr( $cfg['family'] ); ?>;">
							<span class="pal-script-font-card__sample">Champions</span>
							<span class="pal-script-font-card__name"><?php echo esc_html( $cfg['label'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
				<p class="description" style="margin-top:6px;font-size:12px;"><?php esc_html_e( 'Click a font to apply it. The chosen font is loaded only on pages that show the popup.', 'nashua-pal-core' ); ?></p>
				<style>
					.pal-script-font-grid {
						display: grid;
						grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
						gap: 10px;
						margin: 4px 0 0;
					}
					.pal-script-font-card {
						background: #F7F9FC;
						border: 2px solid #E4EAF2;
						border-radius: 8px;
						padding: 14px 10px 10px;
						cursor: pointer;
						display: flex;
						flex-direction: column;
						align-items: center;
						gap: 6px;
						transition: border-color .12s ease, background .12s ease, transform .08s ease;
						min-height: 80px;
					}
					.pal-script-font-card:hover { background: #fff; border-color: #F4A51C; transform: translateY(-1px); }
					.pal-script-font-card.is-selected { border-color: #F4A51C; background: #FFF8E7; }
					.pal-script-font-card__sample {
						font-size: 30px;
						line-height: 1;
						color: #F4A51C;
						font-weight: 400;
					}
					.pal-script-font-card__name {
						font-size: 11px;
						font-weight: 700;
						color: #5D6B7A;
						font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
						letter-spacing: 0.04em;
					}
				</style>
				<script>
				(function(){
					var rows = document.currentScript.parentNode.querySelectorAll('[data-pal-font]');
					var input = document.currentScript.parentNode.querySelector('[data-pal-script-font-input]');
					if (!rows || !input) return;
					rows.forEach(function(btn){
						btn.addEventListener('click', function(e){
							e.preventDefault();
							rows.forEach(function(b){ b.classList.remove('is-selected'); });
							btn.classList.add('is-selected');
							input.value = btn.getAttribute('data-pal-font');
							// Notify the live-preview JS that the value changed.
							input.dispatchEvent(new Event('input', { bubbles: true }));
							input.dispatchEvent(new Event('change', { bubbles: true }));
						});
					});
				})();
				</script>
			</td>
		</tr>
		<?php
	}

	/**
	 * Script size picker — Small / Medium / Large / Extra Large segmented
	 * control. Click an option to apply (live in preview, persisted on Save).
	 */
	private function script_size_row( $key, $label, $opts ) {
		$value = isset( $opts[ $key ] ) ? $opts[ $key ] : 'lg';
		$sizes = self::script_size_choices();
		?>
		<tr>
			<th scope="row"><label><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input type="hidden" name="<?php echo esc_attr( NPAL_OPTION_KEY ); ?>[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" data-pal-script-size-input />
				<div class="pal-script-size-grid" data-pal-script-size-grid>
					<?php foreach ( $sizes as $slug => $cfg ) : ?>
						<button type="button" class="pal-script-size-card<?php echo $slug === $value ? ' is-selected' : ''; ?>" data-pal-size="<?php echo esc_attr( $slug ); ?>" data-pal-size-mobile="<?php echo esc_attr( $cfg['mobile'] ); ?>" data-pal-size-desktop="<?php echo esc_attr( $cfg['desktop'] ); ?>">
							<span class="pal-script-size-card__sample" style="font-size:<?php echo esc_attr( $cfg['mobile'] ); ?>;">Aa</span>
							<span class="pal-script-size-card__name"><?php echo esc_html( $cfg['label'] ); ?></span>
							<span class="pal-script-size-card__px"><?php echo esc_html( $cfg['mobile'] ); ?> · <?php echo esc_html( $cfg['desktop'] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
				<p class="description" style="margin-top:6px;font-size:12px;"><?php esc_html_e( 'Pixel pairs are mobile · desktop. Long script lines benefit from Small or Medium so the text fits without wrapping.', 'nashua-pal-core' ); ?></p>
				<style>
					.pal-script-size-grid {
						display: grid;
						grid-template-columns: repeat(4, minmax(0, 1fr));
						gap: 8px;
						margin: 4px 0 0;
						max-width: 540px;
					}
					.pal-script-size-card {
						background: #F7F9FC;
						border: 2px solid #E4EAF2;
						border-radius: 8px;
						padding: 10px 8px;
						cursor: pointer;
						display: flex;
						flex-direction: column;
						align-items: center;
						gap: 4px;
						transition: border-color .12s ease, background .12s ease, transform .08s ease;
					}
					.pal-script-size-card:hover { background: #fff; border-color: #F4A51C; transform: translateY(-1px); }
					.pal-script-size-card.is-selected { border-color: #F4A51C; background: #FFF8E7; }
					.pal-script-size-card__sample {
						font-family: 'Allura', 'Brush Script MT', cursive;
						color: #F4A51C;
						line-height: 1;
						min-height: 1em;
						display: inline-flex;
						align-items: center;
					}
					.pal-script-size-card__name {
						font-size: 11px;
						font-weight: 700;
						color: #5D6B7A;
						letter-spacing: 0.04em;
						margin-top: 2px;
					}
					.pal-script-size-card__px {
						font-size: 10px;
						color: #a7aaaf;
						font-family: Menlo, Consolas, monospace;
					}
				</style>
				<script>
				(function(){
					var rows = document.currentScript.parentNode.querySelectorAll('[data-pal-size]');
					var input = document.currentScript.parentNode.querySelector('[data-pal-script-size-input]');
					if (!rows || !input) return;
					rows.forEach(function(btn){
						btn.addEventListener('click', function(e){
							e.preventDefault();
							rows.forEach(function(b){ b.classList.remove('is-selected'); });
							btn.classList.add('is-selected');
							input.value = btn.getAttribute('data-pal-size');
							input.dispatchEvent(new Event('input', { bubbles: true }));
							input.dispatchEvent(new Event('change', { bubbles: true }));
						});
					});
				})();
				</script>
			</td>
		</tr>
		<?php
	}

	private function icon_picker_row( $key, $label, $opts ) {
		$value = isset( $opts[ $key ] ) ? $opts[ $key ] : '';
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td><?php NPAL_Icon_Picker::field( NPAL_OPTION_KEY . '[' . $key . ']', $value, array( 'id' => $key ) ); ?></td>
		</tr>
		<?php
	}

}
