<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_CPT_Champions_Auction {

	const POST_TYPE = 'champ_auction';

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
			'name'               => __( 'Auction Packages', 'nashua-pal-core' ),
			'singular_name'      => __( 'Auction Package', 'nashua-pal-core' ),
			'menu_name'          => __( 'Auction', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Package', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Auction Package', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Auction Package', 'nashua-pal-core' ),
			'new_item'           => __( 'New Auction Package', 'nashua-pal-core' ),
			'view_item'          => __( 'View Auction Package', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Auction Packages', 'nashua-pal-core' ),
			'not_found'          => __( 'No auction packages found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No auction packages found in Trash.', 'nashua-pal-core' ),
			'all_items'          => __( 'Auction', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => false,
			'has_archive'   => false,
			'show_ui'       => true,
			'show_in_menu'  => 'edit.php?post_type=champions_winner',
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest'  => false,
			'rewrite'       => false,
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin list columns                                                 */
	/* ------------------------------------------------------------------ */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']             = $columns['cb'];
		$new['auction_thumb']  = __( 'Image', 'nashua-pal-core' );
		$new['title']          = __( 'Package Name', 'nashua-pal-core' );
		$new['auction_value']  = __( 'Value', 'nashua-pal-core' );
		$new['auction_year']   = __( 'Year', 'nashua-pal-core' );
		$new['auction_feat']   = __( 'Featured', 'nashua-pal-core' );
		$new['auction_order']  = __( 'Order', 'nashua-pal-core' );
		$new['date']           = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'auction_thumb':
				$thumb = get_the_post_thumbnail( $post_id, array( 50, 50 ) );
				echo $thumb ? $thumb : '<span style="color:#999;">—</span>';
				break;
			case 'auction_value':
				echo esc_html( get_post_meta( $post_id, '_pal_auction_value', true ) );
				break;
			case 'auction_year':
				echo esc_html( get_post_meta( $post_id, '_pal_auction_year', true ) );
				break;
			case 'auction_feat':
				echo '1' === get_post_meta( $post_id, '_pal_auction_featured', true )
					? '<span style="color:#f4a51c;font-size:18px;" title="Featured">&#9733;</span>'
					: '<span style="color:#ccc;">&#9734;</span>';
				break;
			case 'auction_order':
				echo esc_html( get_post_meta( $post_id, '_pal_auction_order', true ) ?: '0' );
				break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['auction_year']  = 'auction_year';
		$columns['auction_order'] = 'auction_order';
		return $columns;
	}

	public function admin_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }

		$orderby = $query->get( 'orderby' );
		if ( 'auction_year' === $orderby ) {
			$query->set( 'meta_key', '_pal_auction_year' );
			$query->set( 'orderby', 'meta_value' );
		} elseif ( 'auction_order' === $orderby ) {
			$query->set( 'meta_key', '_pal_auction_order' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}
}
