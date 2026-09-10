<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Custom Post Type: Impact Reports.
 *
 * Registers `pal_impact_report` CPT for annual PDF reports.
 *
 * @package NashuaPalCore
 * @since   1.34.0
 */
final class NPAL_CPT_Impact_Report {

	const POST_TYPE = 'pal_impact_report';

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
			'name'               => __( 'Impact Reports', 'nashua-pal-core' ),
			'singular_name'      => __( 'Impact Report', 'nashua-pal-core' ),
			'menu_name'          => __( 'Impact Reports', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Report', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Report', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Report', 'nashua-pal-core' ),
			'new_item'           => __( 'New Report', 'nashua-pal-core' ),
			'view_item'          => __( 'View Report', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Reports', 'nashua-pal-core' ),
			'not_found'          => __( 'No reports found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No reports found in Trash.', 'nashua-pal-core' ),
			'all_items'          => __( 'All Reports', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => 'impact-reports',
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 15,
			'menu_icon'     => 'dashicons-media-document',
			'supports'      => array( 'title', 'thumbnail' ),
			'show_in_rest'  => false,
			'rewrite'       => array( 'slug' => 'impact-report', 'with_front' => false ),
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin list columns                                                 */
	/* ------------------------------------------------------------------ */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']           = $columns['cb'];
		$new['report_thumb'] = __( 'Cover', 'nashua-pal-core' );
		$new['title']        = __( 'Title', 'nashua-pal-core' );
		$new['report_year']  = __( 'Year', 'nashua-pal-core' );
		$new['report_pdf']   = __( 'PDF', 'nashua-pal-core' );
		$new['date']         = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'report_thumb':
				$thumb = get_the_post_thumbnail( $post_id, array( 50, 70 ) );
				echo $thumb ? $thumb : '<span style="color:#999;">—</span>';
				break;
			case 'report_year':
				echo esc_html( get_post_meta( $post_id, '_pal_report_year', true ) );
				break;
			case 'report_pdf':
				$pdf_id = get_post_meta( $post_id, '_pal_report_pdf_id', true );
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
		$columns['report_year'] = 'report_year';
		return $columns;
	}

	public function admin_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }

		$orderby = $query->get( 'orderby' );
		if ( 'report_year' === $orderby ) {
			$query->set( 'meta_key', '_pal_report_year' );
			$query->set( 'orderby', 'meta_value' );
		}
	}
}
