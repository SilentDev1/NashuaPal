<?php
/**
 * Nashua PAL Elementor widget — Tax 990 Documents.
 *
 * @package NashuaPalCore
 * @since   1.40.1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

class NPAL_Widget_Tax990_Documents extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'tax990_documents';

	public function get_name()     { return 'pal_tax990_documents'; }
	public function get_title()    { return __( 'PAL Tax 990 Documents Grid', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-document-file'; }
	public function get_keywords() { return array( 'pal', 'tax', '990', 'document', 'pdf', 'irs' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Tax 990 Documents', 'nashua-pal-core' ),
		) );

		$this->add_control( 'year', array(
			'label'       => __( 'Year', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. 2026 (blank = all)', 'nashua-pal-core' ),
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
			'label'   => __( 'Number of Documents', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 50,
			'min'     => 1,
			'max'     => 200,
		) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns (Desktop)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '3',
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
			'placeholder' => __( 'e.g. TAX DOCUMENTS', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'year'        => isset( $s['year'] ) ? $s['year'] : '',
			'show_filter' => isset( $s['show_filter'] ) ? $s['show_filter'] : 'true',
			'count'       => isset( $s['count'] ) ? $s['count'] : 50,
			'columns'     => isset( $s['columns'] ) ? $s['columns'] : '3',
			'eyebrow'     => isset( $s['eyebrow'] ) ? $s['eyebrow'] : '',
		);
	}
}
