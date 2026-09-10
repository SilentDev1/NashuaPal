<?php
/**
 * Nashua PAL Elementor widgets — Contact + inline helpers.
 *
 * Phone, email, address, newsletter form, and the inline yellow underline
 * highlight effect.
 *
 * @package NashuaPalCore
 * @since 1.8.56
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

class NPAL_Widget_Phone extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_phone';
	public function get_name()     { return 'pal_phone'; }
	public function get_title()    { return __( 'PAL Phone', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-phone'; }
	public function get_keywords() { return array( 'pal', 'phone', 'contact', 'tel' ); }
}

class NPAL_Widget_Email extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_email';
	public function get_name()     { return 'pal_email'; }
	public function get_title()    { return __( 'PAL Email', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-envelope'; }
	public function get_keywords() { return array( 'pal', 'email', 'contact', 'mailto' ); }
}

class NPAL_Widget_Address extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_address';
	public function get_name()     { return 'pal_address'; }
	public function get_title()    { return __( 'PAL Address', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-map-pin'; }
	public function get_keywords() { return array( 'pal', 'address', 'contact', 'location' ); }
}

class NPAL_Widget_Newsletter extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_newsletter';
	public function get_name()     { return 'pal_newsletter'; }
	public function get_title()    { return __( 'PAL Newsletter Form', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-email-field'; }
	public function get_keywords() { return array( 'pal', 'newsletter', 'subscribe', 'email' ); }
}

class NPAL_Widget_Underline extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'pal_underline';

	public function get_name()     { return 'pal_underline'; }
	public function get_title()    { return __( 'PAL Underline Highlight', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-underline'; }
	public function get_keywords() { return array( 'pal', 'underline', 'highlight', 'emphasis', 'inline' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'nashua-pal-core' ) ) );

		$this->add_control( 'text', array(
			'label'       => __( 'Text', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXTAREA,
			'default'     => 'COMMUNITY',
			'description' => __( 'The phrase to underline with the yellow accent swoosh.', 'nashua-pal-core' ),
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

	protected function enclosed_content() {
		$s = $this->get_settings_for_display();
		return isset( $s['text'] ) ? wp_kses_post( $s['text'] ) : '';
	}
}
