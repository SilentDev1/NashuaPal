<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Custom Post Type: Donors.
 *
 * Registers `pal_donor` CPT for the Donors recognition page.
 * One post per fiscal year — the post title is the year range label
 * (e.g. "October 1, 2020-September 30, 2021") and a textarea meta
 * field holds all donor names (one per line).
 *
 * Not public (no front-end archive/single) — displayed exclusively
 * via the [pal_donors] shortcode and Elementor widgets.
 *
 * @package NashuaPalCore
 * @since   1.36.0
 */
final class NPAL_CPT_Donor {

	const POST_TYPE = 'pal_donor';

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
			'name'               => __( 'Donors', 'nashua-pal-core' ),
			'singular_name'      => __( 'Donor', 'nashua-pal-core' ),
			'menu_name'          => __( 'Donors', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Donor Year', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Donor Year', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Donor', 'nashua-pal-core' ),
			'new_item'           => __( 'New Donor Year', 'nashua-pal-core' ),
			'view_item'          => __( 'View Donor', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Donors', 'nashua-pal-core' ),
			'not_found'          => __( 'No donors found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No donors found in Trash.', 'nashua-pal-core' ),
			'all_items'          => __( 'All Donors', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => false,
			'has_archive'   => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 12,
			'menu_icon'     => 'dashicons-heart',
			'supports'      => array( 'title' ),
			'show_in_rest'  => false,
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin list columns                                                 */
	/* ------------------------------------------------------------------ */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']                = $columns['cb'];
		$new['title']             = __( 'Year Label', 'nashua-pal-core' );
		$new['donor_fiscal_year'] = __( 'Fiscal Year', 'nashua-pal-core' );
		$new['donor_count']       = __( 'Donors', 'nashua-pal-core' );
		$new['date']              = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'donor_fiscal_year':
				$fy = get_post_meta( $post_id, '_pal_donor_fiscal_year', true );
				echo esc_html( $fy ? 'FY ' . $fy : '—' );
				break;
			case 'donor_count':
				$names = get_post_meta( $post_id, '_pal_donor_names', true );
				if ( $names ) {
					$lines = array_filter( array_map( 'trim', explode( "\n", $names ) ) );
					echo esc_html( count( $lines ) );
				} else {
					echo '0';
				}
				break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['donor_fiscal_year'] = 'donor_fiscal_year';
		return $columns;
	}

	public function admin_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }

		$orderby = $query->get( 'orderby' );
		if ( 'donor_fiscal_year' === $orderby ) {
			$query->set( 'meta_key', '_pal_donor_fiscal_year' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Helpers                                                            */
	/* ------------------------------------------------------------------ */

	/**
	 * Current fiscal year (Oct 1 – Sep 30, stored as ending year).
	 * e.g. June 2026 → FY 2026, November 2026 → FY 2027.
	 */
	public static function current_fiscal_year() {
		$month = (int) gmdate( 'n' );
		$year  = (int) gmdate( 'Y' );
		return $month >= 10 ? $year + 1 : $year;
	}

	/**
	 * Get all distinct fiscal years that have donors.
	 */
	public static function get_fiscal_years() {
		global $wpdb;
		$years = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT pm.meta_value
			 FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key = %s
			   AND p.post_type = %s
			   AND p.post_status = 'publish'
			   AND pm.meta_value != ''
			 ORDER BY pm.meta_value DESC",
			'_pal_donor_fiscal_year',
			self::POST_TYPE
		) );
		return $years;
	}
}
