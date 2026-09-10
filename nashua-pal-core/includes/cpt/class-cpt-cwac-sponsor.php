<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Cone with a Cop — Sponsor CPT.
 *
 * Manages event sponsors independently from Night of Champions sponsors.
 *
 * @package NashuaPalCore
 * @since   1.26.0
 */
final class NPAL_CPT_CWAC_Sponsor {

	const POST_TYPE = 'cwac_sponsor';
	const TAXONOMY  = 'cwac_sponsor_level';

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

		// Term meta UI for Sponsor Levels.
		add_action( self::TAXONOMY . '_add_form_fields',  array( $this, 'term_add_fields' ) );
		add_action( self::TAXONOMY . '_edit_form_fields', array( $this, 'term_edit_fields' ), 10, 2 );
		add_action( 'created_' . self::TAXONOMY, array( $this, 'term_save' ) );
		add_action( 'edited_' . self::TAXONOMY,  array( $this, 'term_save' ) );

		// Term list table: badge color column + quick-edit JS.
		add_filter( 'manage_edit-' . self::TAXONOMY . '_columns',  array( $this, 'term_columns' ) );
		add_filter( 'manage_' . self::TAXONOMY . '_custom_column',  array( $this, 'term_column_content' ), 10, 3 );
		add_action( 'admin_footer-edit-tags.php',                    array( $this, 'term_quick_edit_js' ) );

		// Seed default terms.
		add_action( 'admin_init', array( $this, 'maybe_seed_terms' ) );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'CWAC Sponsors', 'nashua-pal-core' ),
			'singular_name'      => __( 'CWAC Sponsor', 'nashua-pal-core' ),
			'menu_name'          => __( 'Cone with a Cop', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Sponsor', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Sponsor', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Sponsor', 'nashua-pal-core' ),
			'new_item'           => __( 'New Sponsor', 'nashua-pal-core' ),
			'view_item'          => __( 'View Sponsor', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Sponsors', 'nashua-pal-core' ),
			'not_found'          => __( 'No sponsors found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No sponsors found in Trash.', 'nashua-pal-core' ),
			'all_items'          => __( 'All Sponsors', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => false,
			'has_archive'   => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_icon'     => 'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="black"><path d="M367.1 185.7c.7-4.4 1-8.9 1-13.5C368.1 108.1 316 56 251.9 56c-25.9 0-49.8 8.5-69.1 22.9C169.4 32.6 125.6 0 74.1 0 33.2 0 0 33.2 0 74.1c0 28.2 15.8 52.8 39 65.3C15.7 154.3 0 179.3 0 208c0 44.2 35.8 80 80 80h256c44.2 0 80-35.8 80-80 0-25.5-11.9-48.2-30.4-62.9l.1-.1c.5-.1 1-.2 1.4-.3zM224 480l80-192H144l80 192z"/></svg>' ),
			'menu_position' => 7,
			'supports'      => array( 'title', 'thumbnail' ),
			'show_in_rest'  => false,
		) );

		register_taxonomy( self::TAXONOMY, self::POST_TYPE, array(
			'labels'       => array(
				'name'              => __( 'Sponsor Levels', 'nashua-pal-core' ),
				'singular_name'     => __( 'Sponsor Level', 'nashua-pal-core' ),
				'search_items'      => __( 'Search Sponsor Levels', 'nashua-pal-core' ),
				'all_items'         => __( 'All Sponsor Levels', 'nashua-pal-core' ),
				'edit_item'         => __( 'Edit Sponsor Level', 'nashua-pal-core' ),
				'update_item'       => __( 'Update Sponsor Level', 'nashua-pal-core' ),
				'add_new_item'      => __( 'Add New Sponsor Level', 'nashua-pal-core' ),
				'new_item_name'     => __( 'New Sponsor Level Name', 'nashua-pal-core' ),
				'menu_name'         => __( 'Sponsor Levels', 'nashua-pal-core' ),
			),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => false,
			'show_admin_column' => false,
			'meta_box_cb'       => false,
			'rewrite'           => false,
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Seed default sponsor level terms                                   */
	/* ------------------------------------------------------------------ */

	public function maybe_seed_terms() {
		if ( get_option( 'npal_cwac_sponsor_level_seeded' ) ) { return; }

		$defaults = array(
			'sundae'       => array( 'label' => 'Sundae Sponsors',       'order' => 1, 'color' => '#B8860B', 'text' => 'light' ),
			'sprinkle'     => array( 'label' => 'Sprinkle Sponsors',     'order' => 2, 'color' => '#F2B134', 'text' => 'dark' ),
			'cone'         => array( 'label' => 'Cone Sponsors',         'order' => 3, 'color' => '#D2691E', 'text' => 'light' ),
			'double-scoop' => array( 'label' => 'Double Scoop Sponsors', 'order' => 4, 'color' => '#E75480', 'text' => 'light' ),
			'single-scoop' => array( 'label' => 'Single Scoop Sponsors', 'order' => 5, 'color' => '#11284D', 'text' => 'light' ),
		);

		foreach ( $defaults as $slug => $data ) {
			if ( ! term_exists( $slug, self::TAXONOMY ) ) {
				$result = wp_insert_term( $data['label'], self::TAXONOMY, array( 'slug' => $slug ) );
				if ( ! is_wp_error( $result ) ) {
					update_term_meta( $result['term_id'], '_display_order', $data['order'] );
					update_term_meta( $result['term_id'], '_badge_color', $data['color'] );
					update_term_meta( $result['term_id'], '_badge_text_color', $data['text'] );
				}
			}
		}

		update_option( 'npal_cwac_sponsor_level_seeded', 1 );
	}

	/* ------------------------------------------------------------------ */
	/*  Term meta: Display Order + Badge Color                             */
	/* ------------------------------------------------------------------ */

	public function term_add_fields() {
		?>
		<div class="form-field">
			<label for="npal_display_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label>
			<input type="number" id="npal_display_order" name="npal_display_order" value="0" min="0" step="1" style="width:80px;" />
			<p class="description"><?php esc_html_e( 'Lower numbers appear first.', 'nashua-pal-core' ); ?></p>
		</div>
		<div class="form-field">
			<label for="npal_badge_color"><?php esc_html_e( 'Badge Color', 'nashua-pal-core' ); ?></label>
			<input type="color" id="npal_badge_color" name="npal_badge_color" value="#11284D" style="width:60px;height:36px;padding:2px;cursor:pointer;" />
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
		$order      = get_term_meta( $term->term_id, '_display_order', true ) ?: '0';
		$color      = get_term_meta( $term->term_id, '_badge_color', true ) ?: '#11284D';
		$text_color = get_term_meta( $term->term_id, '_badge_text_color', true ) ?: 'light';
		?>
		<tr class="form-field">
			<th scope="row"><label for="npal_display_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label></th>
			<td><input type="number" id="npal_display_order" name="npal_display_order" value="<?php echo esc_attr( $order ); ?>" min="0" step="1" style="width:80px;" /></td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="npal_badge_color"><?php esc_html_e( 'Badge Color', 'nashua-pal-core' ); ?></label></th>
			<td><input type="color" id="npal_badge_color" name="npal_badge_color" value="<?php echo esc_attr( $color ); ?>" style="width:60px;height:36px;padding:2px;cursor:pointer;" /></td>
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
		if ( isset( $_POST['npal_display_order'] ) ) {
			update_term_meta( $term_id, '_display_order', absint( $_POST['npal_display_order'] ) );
		}
		if ( isset( $_POST['npal_badge_color'] ) ) {
			update_term_meta( $term_id, '_badge_color', sanitize_hex_color( wp_unslash( $_POST['npal_badge_color'] ) ) );
		}
		if ( isset( $_POST['npal_badge_text_color'] ) ) {
			$val = 'dark' === $_POST['npal_badge_text_color'] ? 'dark' : 'light';
			update_term_meta( $term_id, '_badge_text_color', $val );
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Term list table: badge color column                                */
	/* ------------------------------------------------------------------ */

	public function term_columns( $columns ) {
		$columns['badge_color'] = __( 'Badge Color', 'nashua-pal-core' );
		return $columns;
	}

	public function term_column_content( $content, $column, $term_id ) {
		if ( 'badge_color' === $column ) {
			$color = get_term_meta( $term_id, '_badge_color', true ) ?: '#11284D';
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
				var curColor = swatch.data( 'badge-color' ) || '#11284D';
				var curText  = swatch.data( 'badge-text' )  || 'light';
				if ( editRow.find( '.npal-qe-badge' ).length ) {
					editRow.find( 'input[name="npal_badge_color"]' ).val( curColor );
					editRow.find( 'select[name="npal_badge_text_color"]' ).val( curText );
					return;
				}
				var html = '<fieldset class="npal-qe-badge" style="margin:8px 0;">'
					+ '<legend class="inline-edit-legend" style="font-weight:600;margin-bottom:4px;"><?php echo esc_js( __( 'Badge Color', 'nashua-pal-core' ) ); ?></legend>'
					+ '<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">'
					+ '<label style="display:flex;align-items:center;gap:6px;"><span><?php echo esc_js( __( 'Color', 'nashua-pal-core' ) ); ?></span>'
					+ '<input type="color" name="npal_badge_color" value="' + curColor + '" style="width:50px;height:30px;padding:1px;cursor:pointer;border:1px solid #8c8f94;border-radius:3px;" /></label>'
					+ '<label style="display:flex;align-items:center;gap:6px;"><span><?php echo esc_js( __( 'Text', 'nashua-pal-core' ) ); ?></span>'
					+ '<select name="npal_badge_text_color" style="min-width:100px;">'
					+ '<option value="light"' + ( curText === 'light' ? ' selected' : '' ) + '><?php echo esc_js( __( 'White text', 'nashua-pal-core' ) ); ?></option>'
					+ '<option value="dark"'  + ( curText === 'dark'  ? ' selected' : '' ) + '><?php echo esc_js( __( 'Dark text', 'nashua-pal-core' ) ); ?></option>'
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
	/*  Admin list columns                                                 */
	/* ------------------------------------------------------------------ */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']             = $columns['cb'];
		$new['sponsor_logo']   = __( 'Logo', 'nashua-pal-core' );
		$new['title']          = __( 'Sponsor Name', 'nashua-pal-core' );
		$new['sponsor_year']   = __( 'Year', 'nashua-pal-core' );
		$new['sponsor_level']  = __( 'Level', 'nashua-pal-core' );
		$new['sponsor_order']  = __( 'Order', 'nashua-pal-core' );
		$new['date']           = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'sponsor_logo':
				$thumb = get_the_post_thumbnail( $post_id, array( 60, 40 ) );
				echo $thumb ? $thumb : '<span style="color:#999;">—</span>';
				break;
			case 'sponsor_year':
				echo esc_html( get_post_meta( $post_id, '_pal_cwac_sponsor_year', true ) );
				break;
			case 'sponsor_level':
				$terms = wp_get_post_terms( $post_id, self::TAXONOMY, array( 'fields' => 'names' ) );
				echo ! empty( $terms ) && ! is_wp_error( $terms ) ? esc_html( implode( ', ', $terms ) ) : '—';
				break;
			case 'sponsor_order':
				echo esc_html( get_post_meta( $post_id, '_pal_cwac_sponsor_order', true ) ?: '0' );
				break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['sponsor_year']  = 'sponsor_year';
		$columns['sponsor_order'] = 'sponsor_order';
		return $columns;
	}

	public function admin_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }

		$orderby = $query->get( 'orderby' );
		if ( 'sponsor_year' === $orderby ) {
			$query->set( 'meta_key', '_pal_cwac_sponsor_year' );
			$query->set( 'orderby', 'meta_value' );
		} elseif ( 'sponsor_order' === $orderby ) {
			$query->set( 'meta_key', '_pal_cwac_sponsor_order' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Helpers                                                            */
	/* ------------------------------------------------------------------ */

	public static function get_sponsor_level( $post_id ) {
		$terms = wp_get_post_terms( $post_id, self::TAXONOMY );
		return ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	}

	public static function get_level_badge_style( $term ) {
		if ( ! $term ) { return ''; }
		$bg    = get_term_meta( $term->term_id, '_badge_color', true ) ?: '#11284D';
		$text  = get_term_meta( $term->term_id, '_badge_text_color', true ) ?: 'light';
		$color = 'dark' === $text ? '#11284D' : '#fff';
		return 'background:' . esc_attr( $bg ) . ';color:' . esc_attr( $color );
	}

	public static function get_ordered_levels() {
		$terms = get_terms( array(
			'taxonomy'   => self::TAXONOMY,
			'hide_empty' => false,
			'meta_key'   => '_display_order',
			'orderby'    => 'meta_value_num',
			'order'      => 'ASC',
		) );
		return ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms : array();
	}
}
