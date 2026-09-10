<?php
/**
 * Nashua PAL Elementor widgets — Cone with a Cop.
 *
 * @package NashuaPalCore
 * @since   1.26.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ======================================================================
 *  CWAC Sponsors (marquee logo scroll)
 * ==================================================================== */

class NPAL_Widget_CWAC_Sponsors extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'cwac_sponsors';

	public function get_name()     { return 'pal_cwac_sponsors'; }
	public function get_title()    { return __( 'PAL CWAC Sponsors', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-image-rollover'; }
	public function get_keywords() { return array( 'pal', 'cwac', 'cone', 'cop', 'sponsors', 'logos' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'CWAC Sponsors', 'nashua-pal-core' ),
		) );

		$this->add_control( 'year', array(
			'label'       => __( 'Year', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. 2026', 'nashua-pal-core' ),
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Thank You to Our Sweet Sponsors!', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'year'    => isset( $s['year'] )    ? $s['year']    : '',
			'heading' => isset( $s['heading'] ) ? $s['heading'] : '',
		);
	}
}
