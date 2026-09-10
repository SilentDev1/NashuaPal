<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_CPT_Bright_Future {

	const POST_TYPE = 'pal_bright_future';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'Bright Futures', 'nashua-pal-core' ),
			'singular_name'      => __( 'Bright Future', 'nashua-pal-core' ),
			'menu_name'          => __( 'Bright Futures', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Story', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Story', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Story', 'nashua-pal-core' ),
			'view_item'          => __( 'View Story', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Stories', 'nashua-pal-core' ),
			'not_found'          => __( 'No stories found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No stories found in Trash.', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'has_archive'   => false,
			'rewrite'       => false,
			'menu_position' => 6,
			'menu_icon'     => 'dashicons-star-filled',
			'supports'      => array( 'title', 'thumbnail' ),
			'show_in_rest'  => true,
		) );
	}
}
