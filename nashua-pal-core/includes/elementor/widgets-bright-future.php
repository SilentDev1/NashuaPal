<?php
/**
 * Elementor widget: PAL Bright Futures — Meet Our Youth & Alumni.
 *
 * @package NashuaPalCore
 * @since   1.53.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( 'NPAL_Elementor_Widget_Base' ) ) { return; }

class NPAL_Widget_Bright_Futures extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_bright_futures';

	public function get_name()     { return 'pal_bright_futures'; }
	public function get_title()    { return __( 'PAL Bright Futures', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-testimonial'; }
	public function get_keywords() { return array( 'pal', 'bright', 'futures', 'youth', 'alumni', 'stories', 'video' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'bf_content', array(
			'label' => __( 'Content', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Meet Our Youth & Alumni', 'nashua-pal-core' ),
		) );

		$this->add_control( 'subtitle', array(
			'label'   => __( 'Subtitle', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => __( 'These young people are living proof that when kids are given support, opportunity, and a place to belong — incredible things can happen.', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'title'    => $s['title'] ?? '',
			'subtitle' => $s['subtitle'] ?? '',
		);
	}
}
