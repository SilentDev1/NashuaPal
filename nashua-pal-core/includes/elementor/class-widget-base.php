<?php
/**
 * Shared base for every Nashua PAL Elementor widget.
 *
 * Centralizes:
 *  - The widget category ('nashua-pal').
 *  - A two-step controls registration: content (subclass-specific) +
 *    style (baseline typography/color/bg overridable per-widget).
 *  - The shortcode-args render helper: subclass declares the pal_*
 *    shortcode + an `args_for_shortcode()` map; base composes
 *    [shortcode attr="val"…] and pipes through do_shortcode().
 *
 * Keeps each per-shortcode widget tiny (15–60 lines) instead of
 * duplicating render boilerplate 28 times.
 *
 * @package NashuaPalCore
 * @since 1.8.56
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

abstract class NPAL_Elementor_Widget_Base extends \Elementor\Widget_Base {

	/** The pal_* shortcode this widget wraps (e.g. 'pal_donate_button'). */
	protected $shortcode = '';

	public function get_categories() { return array( 'nashua-pal' ); }

	/**
	 * Elementor entry point. Splits into content + style so subclasses
	 * only override the half they care about.
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/** No-op default — subclasses override to add a Content section. */
	protected function register_content_controls() {}

	/**
	 * Baseline Style section. Every widget gets typography, text color,
	 * background color, and border radius targeted at the wrapper +
	 * common inner elements. Override entirely in a subclass for richer
	 * controls (button hover states, per-element colors, etc.) — call
	 * parent::register_style_controls() at the top to keep the baseline.
	 */
	protected function register_style_controls() {
		$inner = '{{WRAPPER}}, {{WRAPPER}} a, {{WRAPPER}} h1, {{WRAPPER}} h2, {{WRAPPER}} h3, {{WRAPPER}} h4, {{WRAPPER}} h5, {{WRAPPER}} p, {{WRAPPER}} span, {{WRAPPER}} li';

		$this->start_controls_section( 'pal_style', array(
			'label' => __( 'Style', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'pal_typography',
				'label'    => __( 'Typography', 'nashua-pal-core' ),
				'selector' => $inner,
			)
		);

		$this->add_control( 'pal_text_color', array(
			'label'     => __( 'Text color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( $inner => 'color: {{VALUE}};' ),
		) );

		$this->add_control( 'pal_link_color', array(
			'label'     => __( 'Link / accent color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} a' => 'color: {{VALUE}};' ),
		) );

		$this->add_control( 'pal_bg_color', array(
			'label'     => __( 'Background color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}}' => 'background-color: {{VALUE}};' ),
		) );

		$this->add_control( 'pal_border_radius', array(
			'label'      => __( 'Border radius', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'selectors'  => array( '{{WRAPPER}}' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );

		$this->end_controls_section();
	}

	/** Subclass returns [ shortcode_attr => value ] from its controls. */
	protected function args_for_shortcode() { return array(); }

	/** Override for enclosed-content shortcodes like [pal_underline]…[/pal_underline]. */
	protected function enclosed_content() { return ''; }

	protected function render() {
		$attr_str = '';
		foreach ( $this->args_for_shortcode() as $k => $v ) {
			if ( '' === $v || null === $v ) { continue; }
			$attr_str .= ' ' . $k . '="' . esc_attr( $v ) . '"';
		}

		$enclosed = $this->enclosed_content();
		$sc       = '' !== $enclosed
			? '[' . $this->shortcode . $attr_str . ']' . $enclosed . '[/' . $this->shortcode . ']'
			: '[' . $this->shortcode . $attr_str . ']';

		echo do_shortcode( $sc ); // phpcs:ignore WordPress.Security.EscapeOutput -- shortcode handles its own escaping
	}
}
