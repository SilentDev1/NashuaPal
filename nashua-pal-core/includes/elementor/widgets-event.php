<?php
/**
 * Nashua PAL Elementor widgets — Event variant outputs + single program.
 *
 * These read meta from the current pal_event / pal_program post so they
 * only make sense on those single templates (Theme Builder Single for
 * pal_event, single-program template for pal_program).
 *
 * @package NashuaPalCore
 * @since 1.8.56
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

class NPAL_Widget_Awardees extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'pal_awardees';

	public function get_name()     { return 'pal_awardees'; }
	public function get_title()    { return __( 'PAL Awardees Grid', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-medal'; }
	public function get_keywords() { return array( 'pal', 'awardees', 'champions', 'youth' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Layout', 'nashua-pal-core' ) ) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 3,
			'min'     => 2,
			'max'     => 4,
		) );
		$this->add_control( '_note', array(
			'type'    => \Elementor\Controls_Manager::RAW_HTML,
			'raw'     => __( 'Pulls from the current event\'s Youth Awardees field (pipe-delimited textarea on the Event editor).', 'nashua-pal-core' ),
			'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array( 'columns' => isset( $s['columns'] ) ? (string) $s['columns'] : '' );
	}
}

class NPAL_Widget_Event_Sponsors extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'pal_sponsors';

	public function get_name()     { return 'pal_event_sponsors'; }
	public function get_title()    { return __( 'PAL Event Sponsors', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-image-rollover'; }
	public function get_keywords() { return array( 'pal', 'sponsors', 'event', 'logos', 'tiered' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Sponsors', 'nashua-pal-core' ) ) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Thank You to Our Sponsors', 'nashua-pal-core' ),
		) );
		$this->add_control( 'context', array(
			'label'   => __( 'Context', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'event',
			'options' => array(
				'event'   => __( 'Event', 'nashua-pal-core' ),
				'program' => __( 'Program', 'nashua-pal-core' ),
			),
		) );
		$this->add_control( '_note', array(
			'type'    => \Elementor\Controls_Manager::RAW_HTML,
			'raw'     => __( 'Auto-detects the current event or program and shows its linked tiered sponsors as a scrolling marquee with level badges.', 'nashua-pal-core' ),
			'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'heading' => isset( $s['heading'] ) ? $s['heading'] : '',
			'context' => isset( $s['context'] ) ? $s['context'] : 'event',
			'post_id' => 'auto',
		);
	}
}

class NPAL_Widget_Event_Embed extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_event_embed';
	public function get_name()     { return 'pal_event_embed'; }
	public function get_title()    { return __( 'PAL Event Embed (BetterUnite)', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-code'; }
	public function get_keywords() { return array( 'pal', 'event', 'embed', 'betterunite', 'iframe', 'donation' ); }
}

class NPAL_Widget_Previous_Champions extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_previous_champions';
	public function get_name()     { return 'pal_previous_champions'; }
	public function get_title()    { return __( 'PAL Previous Champions', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-history'; }
	public function get_keywords() { return array( 'pal', 'champions', 'history', 'awardees', 'past' ); }
}

class NPAL_Widget_Single_Program extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_program';
	public function get_name()     { return 'pal_program'; }
	public function get_title()    { return __( 'PAL Single Program', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-circle'; }
	public function get_keywords() { return array( 'pal', 'program', 'detail', 'single' ); }
}
