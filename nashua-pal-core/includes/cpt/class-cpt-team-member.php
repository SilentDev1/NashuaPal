<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_CPT_Team_Member {

	const POST_TYPE = 'pal_team_member';

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
		add_action( 'admin_menu', array( $this, 'move_submenu' ), 99 );

		// Quick Edit: Display Order field.
		add_action( 'quick_edit_custom_box', array( $this, 'quick_edit_field' ), 10, 2 );
		add_action( 'save_post_' . self::POST_TYPE, array( $this, 'quick_edit_save' ), 10, 2 );
		add_action( 'admin_footer-edit.php', array( $this, 'quick_edit_js' ) );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'Team Members', 'nashua-pal-core' ),
			'singular_name'      => __( 'Team Member', 'nashua-pal-core' ),
			'menu_name'          => __( 'Team Members', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Team Member', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Team Member', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Team Member', 'nashua-pal-core' ),
			'new_item'           => __( 'New Team Member', 'nashua-pal-core' ),
			'view_item'          => __( 'View Team Member', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Team Members', 'nashua-pal-core' ),
			'not_found'          => __( 'No team members found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No team members found in Trash.', 'nashua-pal-core' ),
			'all_items'          => __( 'Team Members', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => false,
			'show_ui'       => true,
			'show_in_menu'  => 'edit.php?post_type=' . NPAL_CPT_Board_Member::POST_TYPE,
			'supports'      => array( 'title', 'thumbnail' ),
			'show_in_rest'  => false,
			'rewrite'       => array( 'slug' => 'team-member', 'with_front' => false ),
		) );
	}

	/**
	 * Ensure Team Members appears after Board Members in the PAL People menu.
	 */
	public function move_submenu() {
		$parent = 'edit.php?post_type=' . NPAL_CPT_Board_Member::POST_TYPE;
		if ( ! isset( $GLOBALS['submenu'][ $parent ] ) ) { return; }
		// Already positioned by show_in_menu — just verify order.
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
			case 'people_order':
				$order_val = get_post_meta( $post_id, '_pal_people_order', true ) ?: '0';
				echo '<span data-pal-order="' . esc_attr( $order_val ) . '">' . esc_html( $order_val ) . '</span>';
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

	/* ------------------------------------------------------------------ */
	/*  Quick Edit — Display Order                                         */
	/* ------------------------------------------------------------------ */

	public function quick_edit_field( $column_name, $post_type ) {
		if ( self::POST_TYPE !== $post_type || 'people_order' !== $column_name ) { return; }
		?>
		<fieldset class="inline-edit-col-right">
			<div class="inline-edit-col">
				<label>
					<span class="title"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></span>
					<span class="input-text-wrap">
						<input type="number" name="pal_people_order" class="pal-qe-order" min="0" step="1" style="width:60px;" />
					</span>
				</label>
			</div>
		</fieldset>
		<?php
	}

	public function quick_edit_save( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
		if ( ! isset( $_POST['pal_people_order'] ) ) { return; }
		// Skip when the full meta-box nonce is present (handled by NPAL_Team_Member_Meta::save).
		if ( isset( $_POST[ NPAL_Team_Member_Meta::NONCE ] ) ) { return; }

		$order = absint( $_POST['pal_people_order'] );
		update_post_meta( $post_id, '_pal_people_order', $order );
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
				$('#edit-' + id + ' .pal-qe-order').val(order);
			};
		})(jQuery);
		</script>
		<?php
	}
}
