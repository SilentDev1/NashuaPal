<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_CPT_Sponsor_Package {

	const POST_TYPE = 'pal_spon_pkg';
	const TAXONOMY  = 'spon_pkg_cat';

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

		// Term meta UI for Package Categories.
		add_action( self::TAXONOMY . '_add_form_fields',  array( $this, 'term_add_fields' ) );
		add_action( self::TAXONOMY . '_edit_form_fields', array( $this, 'term_edit_fields' ), 10, 2 );
		add_action( 'created_' . self::TAXONOMY, array( $this, 'term_save' ) );
		add_action( 'edited_' . self::TAXONOMY,  array( $this, 'term_save' ) );

		// Term list table: badge color column + quick-edit JS.
		add_filter( 'manage_edit-' . self::TAXONOMY . '_columns',  array( $this, 'term_columns' ) );
		add_filter( 'manage_' . self::TAXONOMY . '_custom_column',  array( $this, 'term_column_content' ), 10, 3 );
		add_action( 'admin_footer-edit-tags.php',                    array( $this, 'term_quick_edit_js' ) );

		// Seed default categories.
		add_action( 'admin_init', array( $this, 'seed_categories' ) );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'Sponsorship Packages', 'nashua-pal-core' ),
			'singular_name'      => __( 'Sponsorship Package', 'nashua-pal-core' ),
			'menu_name'          => __( 'Sponsorship Packages', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Package', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Package', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Package', 'nashua-pal-core' ),
			'new_item'           => __( 'New Package', 'nashua-pal-core' ),
			'view_item'          => __( 'View Package', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Packages', 'nashua-pal-core' ),
			'not_found'          => __( 'No packages found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No packages found in Trash.', 'nashua-pal-core' ),
			'all_items'          => __( 'All Packages', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => 'sponsorship-packages',
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 13,
			'menu_icon'     => 'dashicons-money-alt',
			'supports'      => array( 'title', 'thumbnail' ),
			'show_in_rest'  => false,
			'rewrite'       => array( 'slug' => 'sponsorship-package', 'with_front' => false ),
		) );

		register_taxonomy( self::TAXONOMY, self::POST_TYPE, array(
			'labels'       => array(
				'name'              => __( 'Package Categories', 'nashua-pal-core' ),
				'singular_name'     => __( 'Package Category', 'nashua-pal-core' ),
				'search_items'      => __( 'Search Package Categories', 'nashua-pal-core' ),
				'all_items'         => __( 'All Package Categories', 'nashua-pal-core' ),
				'edit_item'         => __( 'Edit Package Category', 'nashua-pal-core' ),
				'update_item'       => __( 'Update Package Category', 'nashua-pal-core' ),
				'add_new_item'      => __( 'Add New Package Category', 'nashua-pal-core' ),
				'new_item_name'     => __( 'New Package Category Name', 'nashua-pal-core' ),
				'menu_name'         => __( 'Package Categories', 'nashua-pal-core' ),
			),
			'hierarchical' => true,
			'public'       => false,
			'show_ui'      => true,
			'show_in_rest' => false,
			'show_admin_column' => false,
			'meta_box_cb'  => false,
			'rewrite'      => false,
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Term meta: Badge Color                                             */
	/* ------------------------------------------------------------------ */

	public function term_add_fields() {
		?>
		<div class="form-field">
			<label for="npal_badge_color"><?php esc_html_e( 'Badge Color', 'nashua-pal-core' ); ?></label>
			<input type="color" id="npal_badge_color" name="npal_badge_color" value="#F2B134" style="width:60px;height:36px;padding:2px;cursor:pointer;" />
			<p class="description"><?php esc_html_e( 'Background color for the category badge on package cards.', 'nashua-pal-core' ); ?></p>
		</div>
		<div class="form-field">
			<label for="npal_badge_text_color"><?php esc_html_e( 'Badge Text Color', 'nashua-pal-core' ); ?></label>
			<select id="npal_badge_text_color" name="npal_badge_text_color">
				<option value="light"><?php esc_html_e( 'White text', 'nashua-pal-core' ); ?></option>
				<option value="dark"><?php esc_html_e( 'Dark text', 'nashua-pal-core' ); ?></option>
			</select>
		</div>
		<?php
	}

	public function term_edit_fields( $term ) {
		$color      = get_term_meta( $term->term_id, '_badge_color', true ) ?: '#F2B134';
		$text_color = get_term_meta( $term->term_id, '_badge_text_color', true ) ?: 'light';
		?>
		<tr class="form-field">
			<th scope="row"><label for="npal_badge_color"><?php esc_html_e( 'Badge Color', 'nashua-pal-core' ); ?></label></th>
			<td>
				<input type="color" id="npal_badge_color" name="npal_badge_color" value="<?php echo esc_attr( $color ); ?>" style="width:60px;height:36px;padding:2px;cursor:pointer;" />
				<p class="description"><?php esc_html_e( 'Background color for the category badge on package cards.', 'nashua-pal-core' ); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="npal_badge_text_color"><?php esc_html_e( 'Badge Text Color', 'nashua-pal-core' ); ?></label></th>
			<td>
				<select id="npal_badge_text_color" name="npal_badge_text_color">
					<option value="light" <?php selected( $text_color, 'light' ); ?>><?php esc_html_e( 'White text', 'nashua-pal-core' ); ?></option>
					<option value="dark" <?php selected( $text_color, 'dark' ); ?>><?php esc_html_e( 'Dark text', 'nashua-pal-core' ); ?></option>
				</select>
			</td>
		</tr>
		<?php
	}

	public function term_save( $term_id ) {
		if ( isset( $_POST['npal_badge_color'] ) ) {
			update_term_meta( $term_id, '_badge_color', sanitize_hex_color( wp_unslash( $_POST['npal_badge_color'] ) ) );
		}
		if ( isset( $_POST['npal_badge_text_color'] ) ) {
			$val = 'dark' === $_POST['npal_badge_text_color'] ? 'dark' : 'light';
			update_term_meta( $term_id, '_badge_text_color', $val );
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Term list table: badge color column + quick edit                    */
	/* ------------------------------------------------------------------ */

	public function term_columns( $columns ) {
		$columns['badge_color'] = __( 'Badge Color', 'nashua-pal-core' );
		return $columns;
	}

	public function term_column_content( $content, $column, $term_id ) {
		if ( 'badge_color' === $column ) {
			$color = get_term_meta( $term_id, '_badge_color', true ) ?: '#F2B134';
			$content = '<span class="npal-badge-swatch" '
				. 'data-badge-color="' . esc_attr( $color ) . '" '
				. 'data-badge-text="' . esc_attr( get_term_meta( $term_id, '_badge_text_color', true ) ?: 'light' ) . '" '
				. 'style="display:inline-block;width:28px;height:28px;border-radius:4px;'
				. 'background:' . esc_attr( $color ) . ';border:1px solid #ccc;vertical-align:middle;">'
				. '</span>';
		}
		return $content;
	}

	public function term_quick_edit_js() {
		$screen = get_current_screen();
		if ( ! $screen || $screen->taxonomy !== self::TAXONOMY ) { return; }
		?>
		<script>
		(function($){
			if ( typeof inlineEditTax === 'undefined' ) return;
			var origEdit = inlineEditTax.edit;
			inlineEditTax.edit = function( id ) {
				origEdit.apply( this, arguments );
				if ( typeof id === 'object' ) { id = this.getId( id ); }
				var row      = $( '#tag-' + id );
				var editRow  = $( '#edit-' + id );
				var swatch   = row.find( '.npal-badge-swatch' );
				var curColor = swatch.data( 'badge-color' ) || '#F2B134';
				var curText  = swatch.data( 'badge-text' )  || 'light';
				if ( editRow.find( '.npal-qe-badge' ).length ) {
					editRow.find( 'input[name="npal_badge_color"]' ).val( curColor );
					editRow.find( 'select[name="npal_badge_text_color"]' ).val( curText );
					return;
				}
				var html = '<fieldset class="npal-qe-badge" style="margin:8px 0;">'
					+ '<legend class="inline-edit-legend" style="font-weight:600;margin-bottom:4px;">Badge Color</legend>'
					+ '<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">'
					+ '<label style="display:flex;align-items:center;gap:6px;"><span>Color</span>'
					+ '<input type="color" name="npal_badge_color" value="' + curColor + '" style="width:50px;height:30px;padding:1px;cursor:pointer;border:1px solid #8c8f94;border-radius:3px;" /></label>'
					+ '<label style="display:flex;align-items:center;gap:6px;"><span>Text</span>'
					+ '<select name="npal_badge_text_color" style="min-width:100px;">'
					+ '<option value="light"' + ( curText === 'light' ? ' selected' : '' ) + '>White text</option>'
					+ '<option value="dark"'  + ( curText === 'dark'  ? ' selected' : '' ) + '>Dark text</option>'
					+ '</select></label></div></fieldset>';
				var submitWrap = editRow.find( '.inline-edit-save' );
				if ( submitWrap.length ) { submitWrap.before( html ); }
				else { editRow.find( 'td' ).last().append( html ); }
			};
		})(jQuery);
		</script>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Seed default package categories                                    */
	/* ------------------------------------------------------------------ */

	public function seed_categories() {
		if ( get_option( 'npal_spon_pkg_cat_seeded' ) ) { return; }

		$defaults = array(
			'title'     => array( 'label' => 'Title Sponsor',     'color' => '#B8860B', 'text' => 'light' ),
			'platinum'  => array( 'label' => 'Platinum Sponsor',  'color' => '#8B8BAE', 'text' => 'light' ),
			'gold'      => array( 'label' => 'Gold Sponsor',      'color' => '#F2B134', 'text' => 'dark' ),
			'silver'    => array( 'label' => 'Silver Sponsor',    'color' => '#8C8C8C', 'text' => 'light' ),
			'bronze'    => array( 'label' => 'Bronze Sponsor',    'color' => '#CD7F32', 'text' => 'light' ),
			'community' => array( 'label' => 'Community Sponsor', 'color' => '#11284D', 'text' => 'light' ),
		);

		foreach ( $defaults as $slug => $data ) {
			if ( ! term_exists( $slug, self::TAXONOMY ) ) {
				$result = wp_insert_term( $data['label'], self::TAXONOMY, array( 'slug' => $slug ) );
				if ( ! is_wp_error( $result ) ) {
					update_term_meta( $result['term_id'], '_badge_color', $data['color'] );
					update_term_meta( $result['term_id'], '_badge_text_color', $data['text'] );
				}
			}
		}

		update_option( 'npal_spon_pkg_cat_seeded', 1 );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin list columns                                                 */
	/* ------------------------------------------------------------------ */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']           = $columns['cb'];
		$new['pkg_thumb']    = __( 'Image', 'nashua-pal-core' );
		$new['title']        = __( 'Package Name', 'nashua-pal-core' );
		$new['pkg_category'] = __( 'Category', 'nashua-pal-core' );
		$new['pkg_year']     = __( 'Year', 'nashua-pal-core' );
		$new['date']         = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'pkg_thumb':
				$thumb = get_the_post_thumbnail( $post_id, array( 50, 50 ) );
				echo $thumb ? $thumb : '<span style="color:#999;">—</span>';
				break;
			case 'pkg_category':
				$terms = wp_get_post_terms( $post_id, self::TAXONOMY, array( 'fields' => 'names' ) );
				echo ! empty( $terms ) && ! is_wp_error( $terms ) ? esc_html( implode( ', ', $terms ) ) : '—';
				break;
			case 'pkg_year':
				echo esc_html( get_post_meta( $post_id, '_pal_pkg_year', true ) );
				break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['pkg_year'] = 'pkg_year';
		return $columns;
	}

	public function admin_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }

		if ( 'pkg_year' === $query->get( 'orderby' ) ) {
			$query->set( 'meta_key', '_pal_pkg_year' );
			$query->set( 'orderby', 'meta_value' );
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Helpers                                                            */
	/* ------------------------------------------------------------------ */

	public static function get_package_category( $post_id ) {
		$terms = wp_get_post_terms( $post_id, self::TAXONOMY );
		return ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	}

	public static function get_badge_style( $term ) {
		if ( ! $term ) { return ''; }
		$bg    = get_term_meta( $term->term_id, '_badge_color', true ) ?: '#F2B134';
		$text  = get_term_meta( $term->term_id, '_badge_text_color', true ) ?: 'light';
		$color = 'dark' === $text ? '#11284D' : '#fff';
		return 'background:' . esc_attr( $bg ) . ';color:' . esc_attr( $color );
	}
}
