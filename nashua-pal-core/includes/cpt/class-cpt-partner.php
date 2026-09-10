<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_CPT_Partner {

	const POST_TYPE = 'pal_partner';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( __CLASS__, 'register' ) );

		// Admin columns.
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns',  array( $this, 'admin_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'admin_column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'admin_sort' ) );

		// Quick Edit: Display Order field.
		add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_field' ), 10, 2 );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'quick_edit_save' ), 10, 2 );
		add_action( 'admin_footer-edit.php', array( $this, 'quick_edit_js' ) );
	}

	public static function register() {
		$labels = array(
			'name'          => __( 'Partners', 'nashua-pal-core' ),
			'singular_name' => __( 'Partner', 'nashua-pal-core' ),
			'menu_name'     => __( 'Partners', 'nashua-pal-core' ),
			'add_new'       => __( 'Add Partner', 'nashua-pal-core' ),
			'add_new_item'  => __( 'Add New Partner', 'nashua-pal-core' ),
			'edit_item'     => __( 'Edit Partner', 'nashua-pal-core' ),
			'not_found'     => __( 'No partners found.', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => 'partners',
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 10,
			'menu_icon'     => 'dashicons-groups',
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'show_in_rest'  => false,
			'rewrite'       => array( 'slug' => 'partner', 'with_front' => false ),
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin columns                                                      */
	/* ------------------------------------------------------------------ */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']            = $columns['cb'];
		$new['title']         = $columns['title'];
		$new['partner_order'] = __( 'Order', 'nashua-pal-core' );
		$new['date']          = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		if ( 'partner_order' === $column ) {
			$order_val = get_post_meta( $post_id, '_pal_partner_order', true );
			$order_val = ( '' !== $order_val ) ? $order_val : '0';
			echo '<span data-pal-order="' . esc_attr( $order_val ) . '">' . esc_html( $order_val ) . '</span>';
		}
	}

	public function sortable_columns( $columns ) {
		$columns['partner_order'] = 'partner_order';
		return $columns;
	}

	public function admin_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }

		$orderby = $query->get( 'orderby' );

		// Use EXISTS/NOT EXISTS pattern to avoid INNER JOIN hiding posts without meta.
		$meta_query = array(
			'relation' => 'OR',
			'order_clause' => array(
				'key'     => '_pal_partner_order',
				'compare' => 'EXISTS',
				'type'    => 'NUMERIC',
			),
			array(
				'key'     => '_pal_partner_order',
				'compare' => 'NOT EXISTS',
			),
		);

		// When clicking the Order column header.
		if ( 'partner_order' === $orderby ) {
			$query->set( 'meta_query', $meta_query );
			$query->set( 'orderby', array( 'order_clause' => $query->get( 'order' ) ?: 'ASC' ) );
			return;
		}

		// Default sort: order ASC, then title ASC.
		if ( empty( $orderby ) ) {
			$query->set( 'meta_query', $meta_query );
			$query->set( 'orderby', array( 'order_clause' => 'ASC', 'title' => 'ASC' ) );
			$query->set( 'order', 'ASC' );
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Quick Edit — Display Order                                         */
	/* ------------------------------------------------------------------ */

	public function quick_edit_field( $column_name, $post_type ) {
		if ( self::POST_TYPE !== $post_type || 'partner_order' !== $column_name ) { return; }
		?>
		<fieldset class="inline-edit-col-right">
			<div class="inline-edit-col">
				<label>
					<span class="title"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></span>
					<span class="input-text-wrap">
						<input type="number" name="pal_partner_order" class="pal-qe-partner-order" min="0" step="1" style="width:60px;" />
					</span>
				</label>
			</div>
		</fieldset>
		<?php
	}

	public function quick_edit_save( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		if ( ! isset( $_POST['pal_partner_order'] ) ) { return; }
		// Skip when the full meta-box nonce is present (handled by NPAL_Partner_Meta::save).
		if ( isset( $_POST[ NPAL_Partner_Meta::NONCE ] ) ) { return; }

		$order = absint( $_POST['pal_partner_order'] );
		update_post_meta( $post_id, '_pal_partner_order', $order );
	}

	public function quick_edit_js() {
		$screen = get_current_screen();
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) { return; }
		?>
		<script>
		(function($){
			var orig = inlineEditPost.edit;
			inlineEditPost.edit = function(id){
				orig.apply(this, arguments);
				if(typeof id === 'object') id = this.getId(id);
				var row   = $('#post-' + id);
				var order = row.find('[data-pal-order]').data('pal-order');
				$('#edit-' + id + ' .pal-qe-partner-order').val(order);
			};
		})(jQuery);
		</script>
		<?php
	}
}
