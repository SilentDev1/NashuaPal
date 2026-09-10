<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Custom Post Type: Tax 990 Documents.
 *
 * Registers `pal_tax990` CPT for annual IRS Form 990 filings.
 *
 * @package NashuaPalCore
 * @since   1.40.1
 */
final class NPAL_CPT_Tax990 {

	const POST_TYPE = 'pal_tax990';

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
			'name'               => __( 'Tax 990 Documents', 'nashua-pal-core' ),
			'singular_name'      => __( 'Tax 990 Document', 'nashua-pal-core' ),
			'menu_name'          => __( 'Tax 990', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Document', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Tax 990 Document', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Tax 990 Document', 'nashua-pal-core' ),
			'new_item'           => __( 'New Tax 990 Document', 'nashua-pal-core' ),
			'view_item'          => __( 'View Tax 990 Document', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Tax 990 Documents', 'nashua-pal-core' ),
			'not_found'          => __( 'No documents found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No documents found in Trash.', 'nashua-pal-core' ),
			'all_items'          => __( 'All Documents', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => 'tax-990-documents',
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 17,
			'menu_icon'     => 'dashicons-media-spreadsheet',
			'supports'      => array( 'title', 'thumbnail' ),
			'show_in_rest'  => false,
			'rewrite'       => array( 'slug' => 'tax-990', 'with_front' => false ),
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin list columns                                                 */
	/* ------------------------------------------------------------------ */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']           = $columns['cb'];
		$new['tax990_thumb'] = __( 'Cover', 'nashua-pal-core' );
		$new['title']        = __( 'Title', 'nashua-pal-core' );
		$new['tax990_year']  = __( 'Year', 'nashua-pal-core' );
		$new['tax990_pdf']   = __( 'PDF', 'nashua-pal-core' );
		$new['date']         = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'tax990_thumb':
				$thumb = get_the_post_thumbnail( $post_id, array( 50, 70 ) );
				echo $thumb ? $thumb : '<span style="color:#999;">—</span>';
				break;
			case 'tax990_year':
				echo esc_html( get_post_meta( $post_id, '_pal_tax990_year', true ) );
				break;
			case 'tax990_pdf':
				$pdf_id = get_post_meta( $post_id, '_pal_tax990_pdf_id', true );
				if ( $pdf_id ) {
					$url = wp_get_attachment_url( $pdf_id );
					if ( $url ) {
						echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener"><span class="dashicons dashicons-pdf" style="color:#c33;"></span> ' . esc_html__( 'View', 'nashua-pal-core' ) . '</a>';
					} else {
						echo '<span style="color:#999;">—</span>';
					}
				} else {
					echo '<span style="color:#999;">—</span>';
				}
				break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['tax990_year'] = 'tax990_year';
		return $columns;
	}

	public function admin_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }

		$orderby = $query->get( 'orderby' );
		if ( 'tax990_year' === $orderby ) {
			$query->set( 'meta_key', '_pal_tax990_year' );
			$query->set( 'orderby', 'meta_value' );
		}
	}
}
