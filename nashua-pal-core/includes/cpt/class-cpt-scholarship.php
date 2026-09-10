<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Custom Post Type: Scholarship Programs.
 *
 * Registers `pal_scholarship` CPT for managing scholarship types/programs.
 * Nested under the existing Scholarships (pal_scholar) menu.
 *
 * @package NashuaPalCore
 * @since   1.37.0
 */
final class NPAL_CPT_Scholarship {

	const POST_TYPE = 'pal_scholarship';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns',  array( $this, 'admin_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'admin_column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'admin_sort' ) );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'Scholarship Programs', 'nashua-pal-core' ),
			'singular_name'      => __( 'Scholarship Program', 'nashua-pal-core' ),
			'menu_name'          => __( 'Programs', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Program', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Scholarship Program', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Scholarship Program', 'nashua-pal-core' ),
			'new_item'           => __( 'New Scholarship Program', 'nashua-pal-core' ),
			'view_item'          => __( 'View Program', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Programs', 'nashua-pal-core' ),
			'not_found'          => __( 'No scholarship programs found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No scholarship programs found in Trash.', 'nashua-pal-core' ),
			'all_items'          => __( 'All Programs', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => false,
			'has_archive'   => false,
			'show_ui'       => true,
			'show_in_menu'  => 'edit.php?post_type=' . NPAL_CPT_Scholar::POST_TYPE,
			'supports'      => array( 'title' ),
			'show_in_rest'  => false,
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin list columns                                                 */
	/* ------------------------------------------------------------------ */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']                    = $columns['cb'];
		$new['title']                 = __( 'Program Name', 'nashua-pal-core' );
		$new['scholarship_year']      = __( 'Year', 'nashua-pal-core' );
		$new['scholarship_amount']    = __( 'Amount', 'nashua-pal-core' );
		$new['date']                  = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'scholarship_year':
				echo esc_html( get_post_meta( $post_id, '_pal_scholarship_year', true ) ?: '—' );
				break;
			case 'scholarship_amount':
				echo esc_html( get_post_meta( $post_id, '_pal_scholarship_amount', true ) ?: '—' );
				break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['scholarship_year'] = 'scholarship_year';
		return $columns;
	}

	public function admin_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }

		$orderby = $query->get( 'orderby' );
		if ( 'scholarship_year' === $orderby ) {
			$query->set( 'meta_key', '_pal_scholarship_year' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}
}
