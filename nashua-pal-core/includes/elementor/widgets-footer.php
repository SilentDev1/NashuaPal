<?php
/**
 * Nashua PAL Elementor widgets — Footer (link list menus, legal bar).
 *
 * Replaces shortcode-only footer menus with fully editable Elementor
 * widgets so staff can visually manage footer links without touching
 * Appearance -> Menus or shortcodes.
 *
 * @package NashuaPalCore
 * @since 1.21.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/**
 * PAL Footer Menu — always-visible vertical link list.
 *
 * Two modes:
 *  1. WordPress Menu mode: select an existing WP menu.
 *  2. Manual mode: add items via Elementor repeater.
 *
 * Never collapses to a hamburger icon on any viewport.
 */
class NPAL_Widget_Footer_Menu extends \Elementor\Widget_Base {

	public function get_name()       { return 'pal_footer_menu'; }
	public function get_title()      { return __( 'PAL Footer Menu', 'nashua-pal-core' ); }
	public function get_icon()       { return 'eicon-nav-menu'; }
	public function get_categories() { return array( 'nashua-pal' ); }
	public function get_keywords()   { return array( 'pal', 'footer', 'menu', 'links', 'nav', 'quick links', 'get involved' ); }

	protected function register_controls() {

		/* ======== Content ======== */
		$this->start_controls_section( 'section_content', array(
			'label' => __( 'Menu Content', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'source', array(
			'label'   => __( 'Source', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'wp_menu',
			'options' => array(
				'wp_menu' => __( 'WordPress Menu', 'nashua-pal-core' ),
				'manual'  => __( 'Manual Items', 'nashua-pal-core' ),
			),
		) );

		// WordPress menu selector.
		$menus    = wp_get_nav_menus();
		$menu_opt = array( '' => __( '— Select a Menu —', 'nashua-pal-core' ) );
		foreach ( $menus as $m ) {
			$menu_opt[ $m->slug ] = $m->name;
		}
		$this->add_control( 'wp_menu', array(
			'label'     => __( 'Menu', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::SELECT,
			'options'   => $menu_opt,
			'default'   => '',
			'condition' => array( 'source' => 'wp_menu' ),
		) );

		// Manual items repeater.
		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'text', array(
			'label'       => __( 'Label', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => __( 'Link', 'nashua-pal-core' ),
			'label_block' => true,
		) );
		$repeater->add_control( 'link', array(
			'label'       => __( 'URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::URL,
			'placeholder' => 'https://',
			'default'     => array( 'url' => '#' ),
		) );

		$this->add_control( 'items', array(
			'label'       => __( 'Menu Items', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(
				array( 'text' => 'About PAL',  'link' => array( 'url' => '#' ) ),
				array( 'text' => 'Programs',   'link' => array( 'url' => '#' ) ),
				array( 'text' => 'Events',     'link' => array( 'url' => '#' ) ),
				array( 'text' => 'Contact',    'link' => array( 'url' => '#' ) ),
			),
			'title_field' => '{{{ text }}}',
			'condition'   => array( 'source' => 'manual' ),
		) );

		$this->end_controls_section();

		/* ======== Style — List ======== */
		$this->start_controls_section( 'section_style_list', array(
			'label' => __( 'List Style', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'link_color', array(
			'label'     => __( 'Link Color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => 'rgba(255,255,255,0.8)',
			'selectors' => array( '{{WRAPPER}} .pal-footer-menu a' => 'color: {{VALUE}};' ),
		) );

		$this->add_control( 'link_hover_color', array(
			'label'     => __( 'Hover Color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#F4A51C',
			'selectors' => array( '{{WRAPPER}} .pal-footer-menu a:hover' => 'color: {{VALUE}};' ),
		) );

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'link_typography',
				'label'    => __( 'Typography', 'nashua-pal-core' ),
				'selector' => '{{WRAPPER}} .pal-footer-menu a',
			)
		);

		$this->add_responsive_control( 'item_spacing', array(
			'label'      => __( 'Item Spacing', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 40 ) ),
			'default'    => array( 'size' => 10, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .pal-footer-menu li' => 'margin-bottom: {{SIZE}}{{UNIT}};' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$source   = $settings['source'];

		echo '<nav class="pal-footer-menu">';

		if ( 'wp_menu' === $source && ! empty( $settings['wp_menu'] ) ) {
			wp_nav_menu( array(
				'menu'       => $settings['wp_menu'],
				'menu_class' => 'pal-footer-menu__list',
				'container'  => false,
				'depth'      => 1,
				'fallback_cb' => false,
			) );
		} elseif ( 'manual' === $source && ! empty( $settings['items'] ) ) {
			echo '<ul class="pal-footer-menu__list">';
			foreach ( $settings['items'] as $item ) {
				$url    = ! empty( $item['link']['url'] ) ? $item['link']['url'] : '#';
				$target = ! empty( $item['link']['is_external'] ) ? ' target="_blank"' : '';
				$rel    = ! empty( $item['link']['nofollow'] ) ? ' rel="nofollow"' : '';
				echo '<li><a href="' . esc_url( $url ) . '"' . $target . $rel . '>' . esc_html( $item['text'] ) . '</a></li>';
			}
			echo '</ul>';
		} else {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p style="color:rgba(255,255,255,0.5);font-size:13px;">' . esc_html__( 'Select a WordPress menu or add manual items.', 'nashua-pal-core' ) . '</p>';
			}
		}

		echo '</nav>';
	}
}

/**
 * PAL Footer Legal — editable copyright & utility-link bar.
 *
 * Pulls defaults from Nashua PAL -> Settings but allows Elementor
 * override for copyright text and up to 4 utility links.
 */
class NPAL_Widget_Footer_Legal extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_footer_legal';
	public function get_name()     { return 'pal_footer_legal'; }
	public function get_title()    { return __( 'PAL Footer Legal Bar', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-circle-o'; }
	public function get_keywords() { return array( 'pal', 'footer', 'legal', 'copyright' ); }
}
