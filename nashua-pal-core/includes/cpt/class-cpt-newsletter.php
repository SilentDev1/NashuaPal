<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_CPT_Newsletter {

	const POST_TYPE = 'pal_newsletter';

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
			'name'               => __( 'Newsletters', 'nashua-pal-core' ),
			'singular_name'      => __( 'Newsletter', 'nashua-pal-core' ),
			'menu_name'          => __( 'Newsletters', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Newsletter', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Newsletter', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Newsletter', 'nashua-pal-core' ),
			'not_found'          => __( 'No newsletters found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No newsletters in Trash.', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'          => $labels,
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'has_archive'     => false,
			'menu_position'   => 8,
			'menu_icon'       => 'dashicons-email-alt2',
			'supports'        => array( 'title', 'thumbnail', 'excerpt' ),
			'show_in_rest'    => false,
			'capability_type' => 'post',
		) );
	}

	public function columns( $cols ) {
		$new = array();
		foreach ( $cols as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['pal_nl_date'] = __( 'Publication Date', 'nashua-pal-core' );
				$new['pal_nl_url']  = __( 'External URL', 'nashua-pal-core' );
			}
		}
		return $new;
	}

	public function render_column( $column, $post_id ) {
		if ( 'pal_nl_date' === $column ) {
			$date = get_post_meta( $post_id, '_pal_newsletter_date', true );
			echo $date ? esc_html( wp_date( 'M j, Y', strtotime( $date ) ) ) : '&mdash;';
		}
		if ( 'pal_nl_url' === $column ) {
			$url = get_post_meta( $post_id, '_pal_newsletter_url', true );
			if ( $url ) {
				echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open', 'nashua-pal-core' ) . ' &rarr;</a>';
			} else {
				echo '&mdash;';
			}
		}
	}
}
