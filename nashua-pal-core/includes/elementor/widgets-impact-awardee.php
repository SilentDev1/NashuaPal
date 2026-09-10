<?php
/**
 * Nashua PAL Elementor widget — Impact Awardees.
 *
 * @package NashuaPalCore
 * @since   1.31.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ======================================================================
 *  Impact Awardees Grid
 * ==================================================================== */

class NPAL_Widget_Impact_Awardees extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'impact_awardees';

	public function get_name()     { return 'pal_impact_awardees'; }
	public function get_title()    { return __( 'PAL Impact Awardees Grid', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-star'; }
	public function get_keywords() { return array( 'pal', 'national', 'awardee', 'recognition', 'impact' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Impact Awardees', 'nashua-pal-core' ),
		) );

		$this->add_control( 'year', array(
			'label'       => __( 'Year', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. 2026 (blank = all)', 'nashua-pal-core' ),
		) );

		$cat_opts = array( '' => __( 'All Categories', 'nashua-pal-core' ) );
		$cat_terms = get_terms( array( 'taxonomy' => NPAL_CPT_Impact_Awardee::TAXONOMY, 'hide_empty' => false ) );
		if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
			foreach ( $cat_terms as $ct ) { $cat_opts[ $ct->slug ] = $ct->name; }
		}
		$this->add_control( 'category', array(
			'label'   => __( 'Category', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '',
			'options' => $cat_opts,
		) );

		$this->add_control( 'show_filter', array(
			'label'   => __( 'Show Year Filter', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'true',
			'options' => array(
				'true'  => __( 'Yes', 'nashua-pal-core' ),
				'false' => __( 'No', 'nashua-pal-core' ),
			),
		) );

		$this->add_control( 'count', array(
			'label'   => __( 'Number of Awardees', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 50,
			'min'     => 1,
			'max'     => 200,
		) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns (Desktop)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '4',
			'options' => array(
				'2' => '2',
				'3' => '3',
				'4' => '4',
			),
		) );

		$this->add_control( 'eyebrow', array(
			'label'       => __( 'Section Eyebrow', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. NATIONAL RECOGNITION', 'nashua-pal-core' ),
		) );

		$this->add_control( 'view_all_label', array(
			'label'       => __( 'View All Button Label', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. View All Awardees', 'nashua-pal-core' ),
		) );

		$this->add_control( 'view_all_url', array(
			'label'       => __( 'View All Button URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. /impact-awardees/', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'year'           => isset( $s['year'] ) ? $s['year'] : '',
			'category'       => isset( $s['category'] ) ? $s['category'] : '',
			'show_filter'    => isset( $s['show_filter'] ) ? $s['show_filter'] : 'true',
			'count'          => isset( $s['count'] ) ? $s['count'] : 50,
			'columns'        => isset( $s['columns'] ) ? $s['columns'] : '4',
			'eyebrow'        => isset( $s['eyebrow'] ) ? $s['eyebrow'] : '',
			'view_all_label' => isset( $s['view_all_label'] ) ? $s['view_all_label'] : '',
			'view_all_url'   => isset( $s['view_all_url'] ) ? $s['view_all_url'] : '',
		);
	}
}
