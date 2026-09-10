<?php
/**
 * Theme Vars — emit inline CSS variable overrides + targeted rule overrides
 * from the "Theme" settings tab so the user can re-skin the site (brand
 * colors, button size, section spacing, typography) without touching code.
 *
 * Strategy: at priority 999 on wp_head — after every stylesheet has been
 * linked — emit a `<style id="pal-theme-vars">` block that targets `:root`
 * for CSS vars and a handful of element selectors for rule-level overrides
 * (.pal-btn, .pal-section, body, h1-h6). Because :root has equal specificity
 * to other :root declarations, the LAST one wins — so this beats both the
 * plugin's own stylesheet and the active theme's style.css.
 *
 * Also enqueues the chosen Google Fonts (heading + body) in one combined
 * request, deduping if both pick the same family.
 *
 * @package NashuaPalCore
 * @since 1.8.55
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Theme_Vars {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_fonts' ), 11 );
		add_action( 'wp_head',            array( $this, 'render' ),       999 );
	}

	/**
	 * Curated theme-font choices. Key is the option value; `family` is the
	 * display name used in CSS; `google` is the Google Fonts family slug +
	 * weight set requested from the CDN.
	 */
	public static function font_choices() {
		return array(
			'montserrat'   => array( 'family' => 'Montserrat',        'google' => 'Montserrat:wght@400;600;700;800' ),
			'open-sans'    => array( 'family' => 'Open Sans',         'google' => 'Open+Sans:wght@400;500;600;700' ),
			'inter'        => array( 'family' => 'Inter',             'google' => 'Inter:wght@400;500;600;700;800' ),
			'roboto'       => array( 'family' => 'Roboto',            'google' => 'Roboto:wght@400;500;700' ),
			'lato'         => array( 'family' => 'Lato',              'google' => 'Lato:wght@400;700;900' ),
			'poppins'      => array( 'family' => 'Poppins',           'google' => 'Poppins:wght@400;500;600;700;800' ),
			'playfair'     => array( 'family' => 'Playfair Display',  'google' => 'Playfair+Display:wght@400;700;800' ),
			'merriweather' => array( 'family' => 'Merriweather',      'google' => 'Merriweather:wght@400;700;900' ),
			'source-sans'  => array( 'family' => 'Source Sans 3',     'google' => 'Source+Sans+3:wght@400;500;600;700' ),
			'nunito'       => array( 'family' => 'Nunito',            'google' => 'Nunito:wght@400;500;600;700;800' ),
		);
	}

	private function opt( $key, $default = '' ) {
		return NPAL_Plugin::get_setting( $key, $default );
	}

	public function enqueue_fonts() {
		$choices = self::font_choices();
		$heading = $this->opt( 'theme_font_heading', 'montserrat' );
		$body    = $this->opt( 'theme_font_body',    'open-sans' );

		$families = array();
		if ( isset( $choices[ $heading ] ) ) { $families[ $choices[ $heading ]['google'] ] = true; }
		if ( isset( $choices[ $body ] ) )    { $families[ $choices[ $body ]['google'] ]    = true; }

		if ( empty( $families ) ) { return; }

		$url = 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', array_keys( $families ) ) . '&display=swap';
		wp_enqueue_style( 'nashua-pal-theme-fonts', $url, array(), null );
	}

	public function render() {
		if ( is_admin() ) { return; }

		$opts    = wp_parse_args( get_option( NPAL_OPTION_KEY, array() ), NPAL_Settings::defaults() );
		$choices = self::font_choices();

		$primary     = sanitize_hex_color( $opts['theme_color_primary'] )      ? $opts['theme_color_primary']      : '#071F3F';
		$primary_alt = sanitize_hex_color( $opts['theme_color_primary_alt'] )  ? $opts['theme_color_primary_alt']  : '#0D2952';
		$accent      = sanitize_hex_color( $opts['theme_color_accent'] )       ? $opts['theme_color_accent']       : '#F4A51C';
		$accent_dark = sanitize_hex_color( $opts['theme_color_accent_dark'] )  ? $opts['theme_color_accent_dark']  : '#D88E0F';
		$bg          = sanitize_hex_color( $opts['theme_color_bg'] )           ? $opts['theme_color_bg']           : '#F7F9FC';
		$text        = sanitize_hex_color( $opts['theme_color_text'] )         ? $opts['theme_color_text']         : '#3A4658';

		$btn_py  = max( 4,  min( 60,  intval( $opts['theme_btn_padding_y'] ) ) );
		$btn_px  = max( 4,  min( 100, intval( $opts['theme_btn_padding_x'] ) ) );
		$btn_rad = max( 0,  min( 60,  intval( $opts['theme_btn_radius'] ) ) );
		$btn_fs  = max( 0.6, min( 2,  floatval( $opts['theme_btn_font_size'] ) ) );
		$sec_pad = max( 16, min( 200, intval( $opts['theme_section_padding'] ) ) );
		$base_fs = max( 12, min( 22,  intval( $opts['theme_base_font_size'] ) ) );

		$heading_key = isset( $choices[ $opts['theme_font_heading'] ] ) ? $opts['theme_font_heading'] : 'montserrat';
		$body_key    = isset( $choices[ $opts['theme_font_body'] ] )    ? $opts['theme_font_body']    : 'open-sans';
		$heading_fam = $choices[ $heading_key ]['family'];
		$body_fam    = $choices[ $body_key ]['family'];

		$heading_w   = intval( $opts['theme_heading_weight'] );
		if ( ! in_array( $heading_w, array( 400, 500, 600, 700, 800, 900 ), true ) ) {
			$heading_w = 800;
		}

		?>
<style id="pal-theme-vars">
:root {
	--pal-navy:         <?php echo esc_html( $primary ); ?>;
	--pal-navy-2:       <?php echo esc_html( $primary_alt ); ?>;
	--pal-yellow:       <?php echo esc_html( $accent ); ?>;
	--pal-yellow-dark:  <?php echo esc_html( $accent_dark ); ?>;
	--pal-gray-100:     <?php echo esc_html( $bg ); ?>;
	--pal-gray-700:     <?php echo esc_html( $text ); ?>;
	--pal-radius-md:    <?php echo (int) $btn_rad; ?>px;
	--pal-space-xl:     <?php echo (int) $sec_pad; ?>px;
	--pal-font-heading: '<?php echo esc_html( $heading_fam ); ?>', system-ui, sans-serif;
	--pal-font-body:    '<?php echo esc_html( $body_fam ); ?>', system-ui, sans-serif;
}
body {
	font-size: <?php echo (int) $base_fs; ?>px;
	font-family: '<?php echo esc_html( $body_fam ); ?>', system-ui, sans-serif;
	color: <?php echo esc_html( $text ); ?>;
}
h1, h2, h3, h4, h5, h6 {
	font-family: '<?php echo esc_html( $heading_fam ); ?>', system-ui, sans-serif;
	font-weight: <?php echo (int) $heading_w; ?>;
}
.pal-btn {
	padding: <?php echo (int) $btn_py; ?>px <?php echo (int) $btn_px; ?>px;
	border-radius: <?php echo (int) $btn_rad; ?>px;
	font-size: <?php echo esc_html( number_format( $btn_fs, 2, '.', '' ) ); ?>rem;
}
.pal-section { padding: <?php echo (int) $sec_pad; ?>px 0; }
.pal-section--tight { padding: <?php echo (int) max( 24, $sec_pad - 32 ); ?>px 0; }
</style>
		<?php
	}
}
