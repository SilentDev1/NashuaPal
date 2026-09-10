<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_CPT_Program {

	const POST_TYPE = 'pal_program';

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
			'name'               => __( 'Programs', 'nashua-pal-core' ),
			'singular_name'      => __( 'Program', 'nashua-pal-core' ),
			'menu_name'          => __( 'Programs', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Program', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Program', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Program', 'nashua-pal-core' ),
			'view_item'          => __( 'View Program', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Programs', 'nashua-pal-core' ),
			'not_found'          => __( 'No programs found.', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => true,
			'rewrite'       => array( 'slug' => 'programs' ),
			'menu_position' => 3,
			'menu_icon'     => 'dashicons-awards',
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'show_in_rest'  => true,
		) );

		register_taxonomy( 'pal_program_category', self::POST_TYPE, array(
			'labels'       => array(
				'name'          => __( 'Program Categories', 'nashua-pal-core' ),
				'singular_name' => __( 'Program Category', 'nashua-pal-core' ),
			),
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'program-category' ),
		) );
	}
}
