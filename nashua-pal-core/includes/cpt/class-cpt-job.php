<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_CPT_Job {

	const POST_TYPE = 'pal_job';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_column' ), 10, 2 );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'Jobs', 'nashua-pal-core' ),
			'singular_name'      => __( 'Job', 'nashua-pal-core' ),
			'menu_name'          => __( 'Jobs', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Job', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Job', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Job', 'nashua-pal-core' ),
			'new_item'           => __( 'New Job', 'nashua-pal-core' ),
			'view_item'          => __( 'View Job', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Jobs', 'nashua-pal-core' ),
			'not_found'          => __( 'No jobs found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No jobs in Trash.', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'              => $labels,
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'jobs' ),
			'menu_position'       => 16,
			'menu_icon'           => 'dashicons-businessperson',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest'        => true,
			'capability_type'     => 'post',
		) );

		register_taxonomy( 'pal_job_category', self::POST_TYPE, array(
			'labels'       => array(
				'name'          => __( 'Job Categories', 'nashua-pal-core' ),
				'singular_name' => __( 'Job Category', 'nashua-pal-core' ),
				'add_new_item'  => __( 'Add New Job Category', 'nashua-pal-core' ),
			),
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'job-category' ),
		) );
	}

	public function columns( $cols ) {
		$new = array();
		foreach ( $cols as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['pal_job_type']     = __( 'Type', 'nashua-pal-core' );
				$new['pal_job_location'] = __( 'Location', 'nashua-pal-core' );
				$new['pal_job_status']   = __( 'Status', 'nashua-pal-core' );
			}
		}
		return $new;
	}

	public function render_column( $column, $post_id ) {
		if ( 'pal_job_type' === $column ) {
			$type = get_post_meta( $post_id, '_pal_job_type', true );
			$labels = array(
				'full_time'  => __( 'Full-Time', 'nashua-pal-core' ),
				'part_time'  => __( 'Part-Time', 'nashua-pal-core' ),
				'seasonal'   => __( 'Seasonal', 'nashua-pal-core' ),
				'volunteer'  => __( 'Volunteer', 'nashua-pal-core' ),
				'internship' => __( 'Internship', 'nashua-pal-core' ),
			);
			echo esc_html( isset( $labels[ $type ] ) ? $labels[ $type ] : '—' );
		}
		if ( 'pal_job_location' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_pal_job_location', true ) ?: '—' );
		}
		if ( 'pal_job_status' === $column ) {
			$status = get_post_meta( $post_id, '_pal_job_status', true );
			$labels = array(
				'open'   => __( 'Open', 'nashua-pal-core' ),
				'closed' => __( 'Closed', 'nashua-pal-core' ),
				'filled' => __( 'Filled', 'nashua-pal-core' ),
			);
			echo esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : '—' );
		}
	}
}
