<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_CPT_Board_Member {

	const POST_TYPE = 'pal_board_member';

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
			'name'               => __( 'Board Members', 'nashua-pal-core' ),
			'singular_name'      => __( 'Board Member', 'nashua-pal-core' ),
			'menu_name'          => __( 'PAL People', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Board Member', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Board Member', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Board Member', 'nashua-pal-core' ),
			'new_item'           => __( 'New Board Member', 'nashua-pal-core' ),
			'view_item'          => __( 'View Board Member', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Board Members', 'nashua-pal-core' ),
			'not_found'          => __( 'No board members found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No board members found in Trash.', 'nashua-pal-core' ),
			'all_items'          => __( 'Board Members', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 9,
			'menu_icon'     => 'dashicons-groups',
			'supports'      => array( 'title', 'thumbnail' ),
			'show_in_rest'  => false,
			'rewrite'       => array( 'slug' => 'board-member', 'with_front' => false ),
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin columns                                                      */
	/* ------------------------------------------------------------------ */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']           = $columns['cb'];
		$new['people_thumb'] = __( 'Photo', 'nashua-pal-core' );
		$new['title']        = __( 'Name', 'nashua-pal-core' );
		$new['people_role']  = __( 'Title / Role', 'nashua-pal-core' );
		$new['people_org']   = __( 'Organization', 'nashua-pal-core' );
		$new['people_order'] = __( 'Order', 'nashua-pal-core' );
		$new['date']         = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'people_thumb':
				$thumb = get_the_post_thumbnail( $post_id, array( 50, 50 ) );
				echo $thumb ? $thumb : '<span style="color:#999;">—</span>';
				break;
			case 'people_role':
				echo esc_html( get_post_meta( $post_id, '_pal_people_role', true ) );
				break;
			case 'people_org':
				echo esc_html( get_post_meta( $post_id, '_pal_people_organization', true ) );
				break;
			case 'people_order':
				echo esc_html( get_post_meta( $post_id, '_pal_people_order', true ) ?: '0' );
				break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['people_order'] = 'people_order';
		return $columns;
	}

	public function admin_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }
		if ( 'people_order' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', '_pal_people_order' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}
}
