<?php
/**
 * Nashua PAL Elementor widgets — Buttons / primary CTAs.
 *
 * @package NashuaPalCore
 * @since 1.8.56
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

class NPAL_Widget_Donate_Button extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'pal_donate_button';

	public function get_name()     { return 'pal_donate_button'; }
	public function get_title()    { return __( 'PAL Donate Button', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-heart'; }
	public function get_keywords() { return array( 'pal', 'donate', 'button', 'cta', 'give', 'support' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Button', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'label', array(
			'label'   => __( 'Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Donate Today', 'nashua-pal-core' ),
		) );

		$this->add_control( 'url', array(
			'label'       => __( 'URL override', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => '/donate or https://…',
			'description' => __( 'Leave empty to use the Donate URL from Nashua PAL → Settings.', 'nashua-pal-core' ),
		) );

		$this->add_responsive_control( 'align', array(
			'label'     => __( 'Alignment', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::CHOOSE,
			'options'   => array(
				'left'   => array( 'title' => __( 'Left',   'nashua-pal-core' ), 'icon' => 'eicon-text-align-left' ),
				'center' => array( 'title' => __( 'Center', 'nashua-pal-core' ), 'icon' => 'eicon-text-align-center' ),
				'right'  => array( 'title' => __( 'Right',  'nashua-pal-core' ), 'icon' => 'eicon-text-align-right' ),
			),
			'default'   => '',
			'selectors' => array( '{{WRAPPER}}' => 'text-align: {{VALUE}};' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'label' => isset( $s['label'] ) ? $s['label'] : '',
			'url'   => isset( $s['url'] )   ? $s['url']   : '',
		);
	}
}
