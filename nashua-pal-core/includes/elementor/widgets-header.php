<?php
/**
 * Nashua PAL Elementor widgets — Header tools (logo, search, language,
 * socials, promo bar, site alert, mobile menu).
 *
 * Mobile Menu Button and Mobile Menu Drawer now have full Elementor
 * controls so staff can edit them visually without shortcodes.
 *
 * @package NashuaPalCore
 * @since 1.8.56
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

class NPAL_Widget_Logo extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_logo';
	public function get_name()     { return 'pal_logo'; }
	public function get_title()    { return __( 'PAL Logo', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-site-logo'; }
	public function get_keywords() { return array( 'pal', 'logo', 'brand', 'badge' ); }
}

class NPAL_Widget_Search extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_search';
	public function get_name()     { return 'pal_search'; }
	public function get_title()    { return __( 'PAL Search', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-search'; }
	public function get_keywords() { return array( 'pal', 'search', 'header' ); }
}

class NPAL_Widget_Language extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_language';
	public function get_name()     { return 'pal_language'; }
	public function get_title()    { return __( 'PAL Language Switcher', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-globe'; }
	public function get_keywords() { return array( 'pal', 'language', 'translate', 'header' ); }
}

class NPAL_Widget_Socials extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_socials';
	public function get_name()     { return 'pal_socials'; }
	public function get_title()    { return __( 'PAL Social Icons', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-share'; }
	public function get_keywords() { return array( 'pal', 'social', 'facebook', 'instagram', 'youtube', 'x', 'twitter' ); }
}

class NPAL_Widget_Promo_Bar extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_promo_bar';
	public function get_name()     { return 'pal_promo_bar'; }
	public function get_title()    { return __( 'PAL Promo Bar', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-megaphone'; }
	public function get_keywords() { return array( 'pal', 'promo', 'bar', 'announcement', 'banner' ); }
}

class NPAL_Widget_Site_Alert extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_alert';
	public function get_name()     { return 'pal_alert'; }
	public function get_title()    { return __( 'PAL Site Alert (popup)', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-warning'; }
	public function get_keywords() { return array( 'pal', 'alert', 'popup', 'modal', 'notice' ); }
}

/**
 * PAL Mobile Menu Button — hamburger toggle with Elementor controls.
 *
 * Controls: bar color, bar width, button alignment.
 * Renders directly (no shortcode) so the Elementor editor shows
 * accurate preview and users can style it visually.
 */
class NPAL_Widget_Mobile_Menu_Button extends \Elementor\Widget_Base {

	public function get_name()       { return 'pal_mobile_menu_button'; }
	public function get_title()      { return __( 'PAL Mobile Menu Button', 'nashua-pal-core' ); }
	public function get_icon()       { return 'eicon-menu-bar'; }
	public function get_categories() { return array( 'nashua-pal' ); }
	public function get_keywords()   { return array( 'pal', 'mobile', 'menu', 'hamburger', 'toggle' ); }

	protected function register_controls() {

		$this->start_controls_section( 'section_content', array(
			'label' => __( 'Button', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'aria_label', array(
			'label'   => __( 'Accessibility Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Open menu', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();

		/* ---- Style ---- */
		$this->start_controls_section( 'section_style', array(
			'label' => __( 'Style', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'button_size', array(
			'label'      => __( 'Button Size', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 30, 'max' => 64 ) ),
			'default'    => array( 'size' => 44, 'unit' => 'px' ),
			'selectors'  => array(
				'{{WRAPPER}} .pal-mobile-toggle' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_responsive_control( 'bar_width', array(
			'label'      => __( 'Bar Width', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 14, 'max' => 40 ) ),
			'default'    => array( 'size' => 22, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .pal-mobile-toggle__bar' => 'width: {{SIZE}}{{UNIT}};' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings  = $this->get_settings_for_display();
		$label     = ! empty( $settings['aria_label'] ) ? $settings['aria_label'] : __( 'Open menu', 'nashua-pal-core' );
		$btn_size  = ! empty( $settings['button_size']['size'] ) ? (int) $settings['button_size']['size'] : 44;
		$bar_width = ! empty( $settings['bar_width']['size'] )  ? (int) $settings['bar_width']['size']  : 22;
		$uid       = 'pal-burger-' . $this->get_id();
		?>
		<style>
			#<?php echo $uid; ?> { display:inline-flex !important; flex-direction:column; justify-content:center; align-items:center; gap:5px; width:<?php echo $btn_size; ?>px; height:<?php echo $btn_size; ?>px; background:transparent !important; border:0 !important; cursor:pointer; padding:0 !important; -webkit-appearance:none; appearance:none; color:transparent !important; }
			#<?php echo $uid; ?> .pal-mobile-toggle__bar { display:block; width:<?php echo $bar_width; ?>px; height:2.5px; border-radius:2px; background:#ffffff !important; transition:background .2s; }
			#<?php echo $uid; ?>:hover .pal-mobile-toggle__bar { background:#F4A51C !important; }
		</style>
		<button type="button" class="pal-mobile-toggle" id="<?php echo $uid; ?>" data-pal-mobile-menu-open
			aria-label="<?php echo esc_attr( $label ); ?>"
			aria-controls="pal-mobile-menu" aria-expanded="false">
			<span class="pal-mobile-toggle__bar"></span>
			<span class="pal-mobile-toggle__bar"></span>
			<span class="pal-mobile-toggle__bar"></span>
		</button>
		<?php
	}
}

/**
 * PAL Mobile Menu Drawer — off-canvas panel with Elementor controls.
 *
 * Controls: menu selection, show/hide sections, panel background,
 * link colors. Renders directly for accurate Elementor preview.
 */
class NPAL_Widget_Mobile_Menu extends \Elementor\Widget_Base {

	public function get_name()       { return 'pal_mobile_menu'; }
	public function get_title()      { return __( 'PAL Mobile Menu Drawer', 'nashua-pal-core' ); }
	public function get_icon()       { return 'eicon-menu-bar'; }
	public function get_categories() { return array( 'nashua-pal' ); }
	public function get_keywords()   { return array( 'pal', 'mobile', 'menu', 'drawer', 'offcanvas' ); }

	protected function register_controls() {

		/* ---- Content ---- */
		$this->start_controls_section( 'section_content', array(
			'label' => __( 'Menu Content', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'menu_source', array(
			'label'   => __( 'Menu Source', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'wp_menu',
			'options' => array(
				'wp_menu' => __( 'WordPress Menu (Appearance → Menus)', 'nashua-pal-core' ),
				'custom'  => __( 'Custom Items (edit below)', 'nashua-pal-core' ),
			),
		) );

		$this->add_control( 'menu_info', array(
			'type' => \Elementor\Controls_Manager::RAW_HTML,
			'raw'  => __( 'Assign a menu to "Nashua PAL Mobile Menu" under Appearance &rarr; Menus.', 'nashua-pal-core' ),
			'content_classes' => 'elementor-descriptor',
			'condition' => array( 'menu_source' => 'wp_menu' ),
		) );

		$menu_items = new \Elementor\Repeater();

		$menu_items->add_control( 'label', array(
			'label'   => __( 'Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Menu Item', 'nashua-pal-core' ),
		) );

		$menu_items->add_control( 'url', array(
			'label'   => __( 'URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '#',
		) );

		$menu_items->add_control( 'icon', array(
			'label'   => __( 'Icon Name', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
			'placeholder' => __( 'e.g. shield-check, basketball, users, calendar, heart', 'nashua-pal-core' ),
		) );

		$this->add_control( 'custom_menu_items', array(
			'label'       => __( 'Menu Items', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $menu_items->get_controls(),
			'default'     => array(
				array( 'label' => __( 'Our Work', 'nashua-pal-core' ),     'url' => '/our-work',      'icon' => 'shield-check' ),
				array( 'label' => __( 'Programs', 'nashua-pal-core' ),     'url' => '/programs',      'icon' => 'basketball' ),
				array( 'label' => __( 'Get Involved', 'nashua-pal-core' ), 'url' => '/get-involved',  'icon' => 'users' ),
				array( 'label' => __( 'Events', 'nashua-pal-core' ),       'url' => '/events',        'icon' => 'calendar' ),
				array( 'label' => __( 'Donate', 'nashua-pal-core' ),       'url' => '/donate',        'icon' => 'heart' ),
			),
			'title_field' => '{{{ label }}}',
			'condition'   => array( 'menu_source' => 'custom' ),
		) );

		$this->add_control( 'show_donate', array(
			'label'        => __( 'Show Donate Button', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'label_on'     => __( 'Show', 'nashua-pal-core' ),
			'label_off'    => __( 'Hide', 'nashua-pal-core' ),
		) );

		$this->add_control( 'btn_primary_label', array(
			'label'       => __( 'Primary Button Label', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => __( 'Donate Today', 'nashua-pal-core' ),
			'condition'   => array( 'show_donate' => 'yes' ),
		) );

		$this->add_control( 'btn_primary_url', array(
			'label'       => __( 'Primary Button URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Leave blank to use Site Settings', 'nashua-pal-core' ),
			'condition'   => array( 'show_donate' => 'yes' ),
		) );

		$this->add_control( 'show_register', array(
			'label'        => __( 'Show Register Button', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'label_on'     => __( 'Show', 'nashua-pal-core' ),
			'label_off'    => __( 'Hide', 'nashua-pal-core' ),
		) );

		$this->add_control( 'btn_secondary_label', array(
			'label'       => __( 'Secondary Button Label', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => __( 'Register Now', 'nashua-pal-core' ),
			'condition'   => array( 'show_register' => 'yes' ),
		) );

		$this->add_control( 'btn_secondary_url', array(
			'label'       => __( 'Secondary Button URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Leave blank to use Site Settings', 'nashua-pal-core' ),
			'condition'   => array( 'show_register' => 'yes' ),
		) );

		$this->add_control( 'show_search', array(
			'label'        => __( 'Show Search', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'label_on'     => __( 'Show', 'nashua-pal-core' ),
			'label_off'    => __( 'Hide', 'nashua-pal-core' ),
		) );

		$this->add_control( 'show_language', array(
			'label'        => __( 'Show Language Switcher', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'label_on'     => __( 'Show', 'nashua-pal-core' ),
			'label_off'    => __( 'Hide', 'nashua-pal-core' ),
		) );

		$this->add_control( 'show_socials', array(
			'label'        => __( 'Show Social Icons', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'label_on'     => __( 'Show', 'nashua-pal-core' ),
			'label_off'    => __( 'Hide', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();

		/* ---- Style ---- */
		$this->start_controls_section( 'section_style', array(
			'label' => __( 'Drawer Style', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'panel_bg', array(
			'label'     => __( 'Panel Background', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#071F3F',
			'selectors' => array( '{{WRAPPER}} .pal-mobile-menu__panel' => 'background-color: {{VALUE}};' ),
		) );

		$this->add_control( 'link_color', array(
			'label'     => __( 'Link Color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => array( '{{WRAPPER}} .pal-mobile-menu__list > li > a' => 'color: {{VALUE}};' ),
		) );

		$this->add_control( 'link_hover_color', array(
			'label'     => __( 'Link Hover / Active Color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#F4A51C',
			'selectors' => array(
				'{{WRAPPER}} .pal-mobile-menu__list > li > a:hover' => 'color: {{VALUE}};',
				'{{WRAPPER}} .pal-mobile-menu__list > li.current-menu-item > a' => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'icon_color', array(
			'label'     => __( 'Icon Color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#F4A51C',
			'selectors' => array( '{{WRAPPER}} .pal-mobile-menu__item-icon' => 'color: {{VALUE}};' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		// Build shortcode atts from widget settings.
		$sc_atts = array(
			'show_donate'       => 'yes' === $settings['show_donate'] ? 'yes' : 'no',
			'show_register'     => 'yes' === $settings['show_register'] ? 'yes' : 'no',
			'show_search'       => 'yes' === $settings['show_search'] ? 'yes' : 'no',
			'show_language'     => 'yes' === $settings['show_language'] ? 'yes' : 'no',
			'show_socials'      => 'yes' === $settings['show_socials'] ? 'yes' : 'no',
			'btn_primary_label' => $settings['btn_primary_label'],
			'btn_primary_url'   => $settings['btn_primary_url'],
			'btn_secondary_label' => $settings['btn_secondary_label'],
			'btn_secondary_url'   => $settings['btn_secondary_url'],
			'menu_source'       => $settings['menu_source'],
		);

		// Pass custom menu items as JSON-encoded attribute.
		if ( 'custom' === $settings['menu_source'] && ! empty( $settings['custom_menu_items'] ) ) {
			$sc_atts['custom_menu_items'] = wp_json_encode( $settings['custom_menu_items'] );
		}

		$attr_str = '';
		foreach ( $sc_atts as $k => $v ) {
			$attr_str .= ' ' . $k . '="' . esc_attr( $v ) . '"';
		}

		echo do_shortcode( '[pal_mobile_menu' . $attr_str . ']' );

		// Show a visible placeholder in the Elementor editor so staff can
		// select / configure the widget (the real drawer is hidden until
		// the hamburger button opens it on the frontend).
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			$pri_label = ! empty( $settings['btn_primary_label'] ) ? $settings['btn_primary_label'] : __( 'Donate Today', 'nashua-pal-core' );
			$sec_label = ! empty( $settings['btn_secondary_label'] ) ? $settings['btn_secondary_label'] : __( 'Register Now', 'nashua-pal-core' );
			?>
			<div style="background:#071F3F;color:#fff;padding:20px 24px;border-radius:8px;font-family:sans-serif;font-size:14px;">
				<strong style="color:#F4A51C;">&#9776; PAL Mobile Menu Drawer</strong>
				<p style="margin:10px 0 6px;opacity:.7;font-size:12px;">Hidden until hamburger opens it. Preview on the frontend.</p>
				<?php if ( 'custom' === $settings['menu_source'] && ! empty( $settings['custom_menu_items'] ) ) : ?>
					<div style="margin:8px 0 12px;padding:8px 0;border-top:1px solid rgba(255,255,255,0.15);">
						<?php foreach ( $settings['custom_menu_items'] as $item ) : ?>
							<div style="padding:4px 0;color:rgba(255,255,255,0.85);font-size:13px;">&bull; <?php echo esc_html( $item['label'] ); ?></div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<p style="margin:6px 0 10px;opacity:.5;font-size:11px;">Menu items: Appearance &rarr; Menus &rarr; "Nashua PAL Mobile Menu"</p>
				<?php endif; ?>
				<span style="display:inline-block;background:#F4A51C;color:#071F3F;padding:6px 14px;border-radius:4px;font-weight:600;font-size:13px;margin-right:8px;"><?php echo esc_html( $pri_label ); ?></span>
				<span style="display:inline-block;border:2px solid #F4A51C;color:#F4A51C;padding:5px 14px;border-radius:4px;font-weight:600;font-size:13px;"><?php echo esc_html( $sec_label ); ?></span>
			</div>
			<?php
		}
	}
}
