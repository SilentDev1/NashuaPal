<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_CPT_Event {

	const POST_TYPE = 'pal_event';

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
			'name'               => __( 'Events', 'nashua-pal-core' ),
			'singular_name'      => __( 'Event', 'nashua-pal-core' ),
			'menu_name'          => __( 'Events', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Event', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Event', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Event', 'nashua-pal-core' ),
			'new_item'           => __( 'New Event', 'nashua-pal-core' ),
			'view_item'          => __( 'View Event', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Events', 'nashua-pal-core' ),
			'not_found'          => __( 'No events found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No events in Trash.', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'              => $labels,
			'public'              => true,
			'has_archive'         => true,
			'rewrite'             => array( 'slug' => 'events' ),
			'menu_position'       => 4,
			'menu_icon'           => 'dashicons-calendar-alt',
			'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'show_in_rest'        => true,
			'capability_type'     => 'post',
		) );

		register_taxonomy( 'pal_event_category', self::POST_TYPE, array(
			'labels'       => array(
				'name'          => __( 'Event Categories', 'nashua-pal-core' ),
				'singular_name' => __( 'Event Category', 'nashua-pal-core' ),
				'add_new_item'  => __( 'Add New Event Category', 'nashua-pal-core' ),
			),
			'hierarchical' => true,
			'public'       => true,
			'show_in_rest' => true,
			'rewrite'      => array( 'slug' => 'event-category' ),
		) );
	}

	public function columns( $cols ) {
		$new = array();
		foreach ( $cols as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['pal_when']  = __( 'Date / Time', 'nashua-pal-core' );
				$new['pal_where'] = __( 'Location', 'nashua-pal-core' );
			}
		}
		return $new;
	}

	public function render_column( $column, $post_id ) {
		if ( 'pal_when' === $column ) {
			$start = get_post_meta( $post_id, '_pal_event_start', true );
			if ( $start ) {
				echo esc_html( wp_date( 'M j, Y · g:i a', strtotime( $start ) ) );
			} else {
				echo '—';
			}
		}
		if ( 'pal_where' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_pal_event_location', true ) ?: '—' );
		}
	}
}
