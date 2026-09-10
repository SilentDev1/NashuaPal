<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_CPT_Champions_Sponsor {

	const POST_TYPE = 'champions_sponsor';
	const TAXONOMY  = 'champion_sponsor_level';

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
		add_filter( 'manage_edit-' . self::TAXONOMY . '_columns',       array( $this, 'term_columns' ) );
		add_filter( 'manage_' . self::TAXONOMY . '_custom_column',      array( $this, 'term_column_content' ), 10, 3 );
		add_action( 'admin_footer-edit-tags.php',                        array( $this, 'term_quick_edit_js' ) );

		// One-time migration from meta → taxonomy.
		add_action( 'admin_init', array( $this, 'maybe_migrate' ) );

		// Reorder submenu so "Sponsor Levels" sits right after "All Sponsors".
		add_action( 'admin_menu', array( $this, 'reorder_submenu' ), 99 );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'Champions Sponsors', 'nashua-pal-core' ),
			'singular_name'      => __( 'Champions Sponsor', 'nashua-pal-core' ),
			'menu_name'          => __( 'Champions Sponsors', 'nashua-pal-core' ),
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
			'show_in_menu'  => 'edit.php?post_type=' . NPAL_CPT_Champions_Winner::POST_TYPE,
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
			'hierarchical' => true,
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => 'edit.php?post_type=' . NPAL_CPT_Champions_Winner::POST_TYPE,
			'show_in_rest' => false,
			'show_admin_column' => false,
			'meta_box_cb'  => false,
			'rewrite'      => false,
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Submenu: ensure Sponsor Levels appears after All Sponsors          */
	/* ------------------------------------------------------------------ */

	public function reorder_submenu() {
		$parent   = 'edit.php?post_type=' . NPAL_CPT_Champions_Winner::POST_TYPE;
		$tax_slug = self::TAXONOMY;
		$tax_url  = 'edit-tags.php?taxonomy=' . $tax_slug . '&post_type=' . self::POST_TYPE;

		if ( empty( $GLOBALS['submenu'][ $parent ] ) ) { return; }

		$sub = &$GLOBALS['submenu'][ $parent ];

		// Check if the taxonomy menu already exists.
		$found = false;
		foreach ( $sub as $item ) {
			if ( isset( $item[2] ) && false !== strpos( $item[2], 'taxonomy=' . $tax_slug ) ) {
				$found = true;
				break;
			}
		}

		// If not found, manually add it.
		if ( ! $found ) {
			$sub[] = array(
				__( 'Sponsor Levels', 'nashua-pal-core' ),
				'manage_categories',
				$tax_url,
			);
		}

		// Now reorder: put Sponsor Levels right after All Sponsors.
		$sponsors_idx = null;
		$levels_idx   = null;
		$levels_item  = null;

		foreach ( $sub as $idx => $item ) {
			if ( isset( $item[2] ) && 'edit.php?post_type=' . self::POST_TYPE === $item[2] ) {
				$sponsors_idx = $idx;
			}
			if ( isset( $item[2] ) && false !== strpos( $item[2], 'taxonomy=' . $tax_slug ) ) {
				$levels_idx  = $idx;
				$levels_item = $item;
			}
		}

		if ( null === $sponsors_idx && null !== $levels_idx ) { return; }
		if ( null === $levels_idx ) { return; }
		if ( $sponsors_idx === $levels_idx ) { return; }

		unset( $sub[ $levels_idx ] );

		$new = array();
		foreach ( $sub as $idx => $item ) {
			$new[] = $item;
			if ( $idx === $sponsors_idx ) {
				$new[] = $levels_item;
			}
		}

		$sub = $new;
	}

	/* ------------------------------------------------------------------ */
	/*  Term meta: Display Order                                           */
	/* ------------------------------------------------------------------ */

	public function term_add_fields() {
		?>
		<div class="form-field">
			<label for="npal_display_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label>
			<input type="number" id="npal_display_order" name="npal_display_order" value="0" min="0" step="1" style="width:80px;" />
			<p class="description"><?php esc_html_e( 'Lower numbers appear first. Controls the order sponsor tiers are displayed on the page.', 'nashua-pal-core' ); ?></p>
		</div>
		<div class="form-field">
			<label for="npal_badge_color"><?php esc_html_e( 'Badge Color', 'nashua-pal-core' ); ?></label>
			<input type="color" id="npal_badge_color" name="npal_badge_color" value="#11284D" style="width:60px;height:36px;padding:2px;cursor:pointer;" />
			<p class="description"><?php esc_html_e( 'Background color for the sponsor level badge shown in the All Sponsors view.', 'nashua-pal-core' ); ?></p>
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
			<td>
				<input type="number" id="npal_display_order" name="npal_display_order" value="<?php echo esc_attr( $order ); ?>" min="0" step="1" style="width:80px;" />
				<p class="description"><?php esc_html_e( 'Lower numbers appear first. Controls the order sponsor tiers are displayed on the page.', 'nashua-pal-core' ); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="npal_badge_color"><?php esc_html_e( 'Badge Color', 'nashua-pal-core' ); ?></label></th>
			<td>
				<input type="color" id="npal_badge_color" name="npal_badge_color" value="<?php echo esc_attr( $color ); ?>" style="width:60px;height:36px;padding:2px;cursor:pointer;" />
				<p class="description"><?php esc_html_e( 'Background color for the sponsor level badge shown in the All Sponsors view.', 'nashua-pal-core' ); ?></p>
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
			$color      = get_term_meta( $term_id, '_badge_color', true ) ?: '#11284D';
			$text_color = get_term_meta( $term_id, '_badge_text_color', true ) ?: 'light';
			$content = '<span class="npal-badge-swatch" '
				. 'data-badge-color="' . esc_attr( $color ) . '" '
				. 'data-badge-text="' . esc_attr( $text_color ) . '" '
				. 'style="display:inline-block;width:28px;height:28px;border-radius:4px;'
				. 'background:' . esc_attr( $color ) . ';border:1px solid #ccc;vertical-align:middle;">'
				. '</span>';
		}
		return $content;
	}

	/**
	 * Inject badge color + text color fields into the taxonomy quick-edit row.
	 * Runs only on the Sponsor Levels term list screen.
	 */
	public function term_quick_edit_js() {
		$screen = get_current_screen();
		if ( ! $screen || $screen->taxonomy !== self::TAXONOMY ) { return; }
		?>
		<script>
		(function($){
			if ( typeof inlineEditTax === 'undefined' ) return;

			// Store reference to the original edit method.
			var origEdit = inlineEditTax.edit;

			inlineEditTax.edit = function( id ) {
				// Call the original.
				origEdit.apply( this, arguments );

				if ( typeof id === 'object' ) {
					id = this.getId( id );
				}

				var row      = $( '#tag-' + id );
				var editRow  = $( '#edit-' + id );
				var swatch   = row.find( '.npal-badge-swatch' );
				var curColor = swatch.data( 'badge-color' ) || '#11284D';
				var curText  = swatch.data( 'badge-text' )  || 'light';

				// Only inject once.
				if ( editRow.find( '.npal-qe-badge' ).length ) {
					editRow.find( 'input[name="npal_badge_color"]' ).val( curColor );
					editRow.find( 'select[name="npal_badge_text_color"]' ).val( curText );
					return;
				}

				var html = '<fieldset class="npal-qe-badge" style="margin:8px 0;">'
					+ '<legend class="inline-edit-legend" style="font-weight:600;margin-bottom:4px;"><?php echo esc_js( __( 'Badge Color', 'nashua-pal-core' ) ); ?></legend>'
					+ '<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">'
					+ '<label style="display:flex;align-items:center;gap:6px;">'
					+ '<span><?php echo esc_js( __( 'Color', 'nashua-pal-core' ) ); ?></span>'
					+ '<input type="color" name="npal_badge_color" value="' + curColor + '" style="width:50px;height:30px;padding:1px;cursor:pointer;border:1px solid #8c8f94;border-radius:3px;" />'
					+ '</label>'
					+ '<label style="display:flex;align-items:center;gap:6px;">'
					+ '<span><?php echo esc_js( __( 'Text', 'nashua-pal-core' ) ); ?></span>'
					+ '<select name="npal_badge_text_color" style="min-width:100px;">'
					+ '<option value="light"' + ( curText === 'light' ? ' selected' : '' ) + '><?php echo esc_js( __( 'White text', 'nashua-pal-core' ) ); ?></option>'
					+ '<option value="dark"'  + ( curText === 'dark'  ? ' selected' : '' ) + '><?php echo esc_js( __( 'Dark text', 'nashua-pal-core' ) ); ?></option>'
					+ '</select>'
					+ '</label>'
					+ '</div>'
					+ '</fieldset>';

				// Insert before the submit buttons.
				var submitWrap = editRow.find( '.inline-edit-save' );
				if ( submitWrap.length ) {
					submitWrap.before( html );
				} else {
					editRow.find( 'td' ).last().append( html );
				}
			};
		})(jQuery);
		</script>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Migration: _pal_champ_sponsor_level meta → taxonomy                */
	/* ------------------------------------------------------------------ */

	public function maybe_migrate() {
		if ( get_option( 'npal_sponsor_level_migrated' ) ) { return; }

		// Default terms with display order and badge color.
		$defaults = array(
			'presenting' => array( 'label' => 'Presenting Sponsor', 'order' => 1, 'color' => '#B8860B', 'text' => 'light' ),
			'gold'       => array( 'label' => 'Gold Sponsor',       'order' => 2, 'color' => '#F2B134', 'text' => 'dark' ),
			'silver'     => array( 'label' => 'Silver Sponsor',     'order' => 3, 'color' => '#8C8C8C', 'text' => 'light' ),
			'community'  => array( 'label' => 'Community Sponsor',  'order' => 4, 'color' => '#11284D', 'text' => 'light' ),
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

		// Migrate existing posts.
		$posts = get_posts( array(
			'post_type'      => self::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'meta_key'       => '_pal_champ_sponsor_level',
			'fields'         => 'ids',
		) );

		foreach ( $posts as $pid ) {
			$level_slug = get_post_meta( $pid, '_pal_champ_sponsor_level', true );
			if ( $level_slug ) {
				wp_set_object_terms( $pid, $level_slug, self::TAXONOMY );
				delete_post_meta( $pid, '_pal_champ_sponsor_level' );
			}
		}

		update_option( 'npal_sponsor_level_migrated', 1 );
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
				echo esc_html( get_post_meta( $post_id, '_pal_champ_sponsor_year', true ) );
				break;
			case 'sponsor_level':
				$terms = wp_get_post_terms( $post_id, self::TAXONOMY, array( 'fields' => 'names' ) );
				echo ! empty( $terms ) && ! is_wp_error( $terms ) ? esc_html( implode( ', ', $terms ) ) : '—';
				break;
			case 'sponsor_order':
				echo esc_html( get_post_meta( $post_id, '_pal_champ_sponsor_order', true ) ?: '0' );
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
			$query->set( 'meta_key', '_pal_champ_sponsor_year' );
			$query->set( 'orderby', 'meta_value' );
		} elseif ( 'sponsor_order' === $orderby ) {
			$query->set( 'meta_key', '_pal_champ_sponsor_order' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Helpers                                                            */
	/* ------------------------------------------------------------------ */

	/**
	 * Get the primary sponsor level term for a post.
	 */
	public static function get_sponsor_level( $post_id ) {
		$terms = wp_get_post_terms( $post_id, self::TAXONOMY );
		return ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0] : null;
	}

	/**
	 * Get badge inline style for a sponsor level term.
	 */
	public static function get_level_badge_style( $term ) {
		if ( ! $term ) { return ''; }
		$bg    = get_term_meta( $term->term_id, '_badge_color', true ) ?: '#11284D';
		$text  = get_term_meta( $term->term_id, '_badge_text_color', true ) ?: 'light';
		$color = 'dark' === $text ? '#11284D' : '#fff';
		return 'background:' . esc_attr( $bg ) . ';color:' . esc_attr( $color );
	}

	/**
	 * Get all sponsor levels ordered by _display_order term meta.
	 */
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
