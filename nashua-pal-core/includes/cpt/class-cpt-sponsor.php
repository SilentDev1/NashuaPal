<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shared Sponsor CPT for Events and Programs.
 *
 * A single `pal_sponsor` post type with a `_pal_sponsor_context` meta field
 * (event | program) scopes each sponsor to its context. The shared
 * `pal_sponsor_level` taxonomy provides tier management with badge colours.
 *
 * Admin submenus appear under both Events and Programs; a `pre_get_posts`
 * filter auto-filters the list by context based on the `pal_context` URL
 * parameter.
 *
 * @package NashuaPalCore
 * @since   1.23.0
 */
final class NPAL_CPT_Sponsor {

	const POST_TYPE = 'pal_sponsor';
	const TAXONOMY  = 'pal_sponsor_level';

	/** Preset colour palette for auto-assignment (child levels only). */
	const COLOR_PALETTE = array(
		'#D4AF37', '#F2B134', '#11284D', '#2563EB', '#059669',
		'#0D9488', '#F97316', '#DC2626', '#7C3AED', '#DB2777',
		'#0284C7', '#475569', '#B87333',
	);

	/** Name-keyword → colour map. First `stripos` match wins. */
	const NAME_COLOR_MAP = array(
		'title'     => '#D4AF37',
		'premier'   => '#11284D',
		'champion'  => '#7C3AED',
		'gold'      => '#F2B134',
		'silver'    => '#D8DCE2',
		'bronze'    => '#B87333',
		'green'     => '#059669',
		'cart'      => '#0D9488',
		'beverage'  => '#0D9488',
		'breakfast' => '#F97316',
		'lunch'     => '#DC2626',
		'raffle'    => '#DB2777',
		'event'     => '#475569',
		'player'    => '#0284C7',
	);

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( __CLASS__, 'register' ) );

		// Admin columns.
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns',        array( $this, 'admin_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column',  array( $this, 'admin_column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'admin_query' ) );

		// Submenus under Events + Programs.
		add_action( 'admin_menu', array( $this, 'add_submenus' ), 30 );

		// Menu highlighting.
		add_filter( 'parent_file',  array( $this, 'fix_parent_menu' ) );
		add_filter( 'submenu_file', array( $this, 'fix_submenu_highlight' ), 10, 2 );

		// Carry context param into "Add New" links.
		add_filter( 'admin_url', array( $this, 'add_context_to_new_link' ), 10, 2 );

		// Term meta UI for Sponsor Levels.
		add_action( self::TAXONOMY . '_add_form_fields',  array( $this, 'term_add_fields' ) );
		add_action( self::TAXONOMY . '_edit_form_fields', array( $this, 'term_edit_fields' ), 10, 2 );
		add_action( 'created_' . self::TAXONOMY, array( $this, 'term_save' ) );
		add_action( 'edited_' . self::TAXONOMY,  array( $this, 'term_save' ) );

		// Term list table: badge colour column + quick-edit JS.
		add_filter( 'manage_edit-' . self::TAXONOMY . '_columns',  array( $this, 'term_columns' ) );
		add_filter( 'manage_' . self::TAXONOMY . '_custom_column', array( $this, 'term_column_content' ), 10, 3 );
		add_action( 'admin_footer-edit-tags.php',                   array( $this, 'term_quick_edit_js' ) );

		// Seed default levels once.
		add_action( 'admin_init', array( $this, 'maybe_seed_levels' ) );

		// Bulk "Apply Auto Colors" handler + admin notice.
		add_action( 'admin_init',    array( $this, 'handle_bulk_auto_colors' ) );
		add_action( 'admin_notices', array( $this, 'auto_colors_admin_notice' ) );

		// One-time migration: auto-color all existing child levels.
		add_action( 'admin_init', array( $this, 'maybe_migrate_auto_colors' ) );
	}

	/* ================================================================== */
	/*  Registration                                                       */
	/* ================================================================== */

	public static function register() {
		$labels = array(
			'name'               => __( 'Sponsors', 'nashua-pal-core' ),
			'singular_name'      => __( 'Sponsor', 'nashua-pal-core' ),
			'menu_name'          => __( 'Sponsors', 'nashua-pal-core' ),
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
			'show_in_menu'  => false,
			'supports'      => array( 'title', 'thumbnail' ),
			'show_in_rest'  => false,
			'capability_type' => 'post',
		) );

		register_taxonomy( self::TAXONOMY, self::POST_TYPE, array(
			'labels'       => array(
				'name'          => __( 'Sponsor Levels', 'nashua-pal-core' ),
				'singular_name' => __( 'Sponsor Level', 'nashua-pal-core' ),
				'search_items'  => __( 'Search Sponsor Levels', 'nashua-pal-core' ),
				'all_items'     => __( 'All Sponsor Levels', 'nashua-pal-core' ),
				'edit_item'     => __( 'Edit Sponsor Level', 'nashua-pal-core' ),
				'update_item'   => __( 'Update Sponsor Level', 'nashua-pal-core' ),
				'add_new_item'  => __( 'Add New Sponsor Level', 'nashua-pal-core' ),
				'new_item_name' => __( 'New Sponsor Level Name', 'nashua-pal-core' ),
				'menu_name'     => __( 'Sponsor Levels', 'nashua-pal-core' ),
			),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_in_menu'      => false,
			'show_in_rest'      => false,
			'show_admin_column' => false,
			'meta_box_cb'       => false,
			'rewrite'           => false,
		) );
	}

	/* ================================================================== */
	/*  Admin submenus under Events + Programs                             */
	/* ================================================================== */

	public function add_submenus() {
		$event_parent   = 'edit.php?post_type=' . NPAL_CPT_Event::POST_TYPE;
		$program_parent = 'edit.php?post_type=' . NPAL_CPT_Program::POST_TYPE;
		$tax_base       = 'edit-tags.php?taxonomy=' . self::TAXONOMY . '&post_type=' . self::POST_TYPE;

		// Under Events.
		add_submenu_page(
			$event_parent,
			__( 'Event Sponsors', 'nashua-pal-core' ),
			__( 'Sponsors', 'nashua-pal-core' ),
			'edit_posts',
			'edit.php?post_type=' . self::POST_TYPE . '&pal_context=event'
		);
		add_submenu_page(
			$event_parent,
			__( 'Sponsor Levels', 'nashua-pal-core' ),
			__( 'Sponsor Levels', 'nashua-pal-core' ),
			'manage_categories',
			$tax_base . '&pal_context=event'
		);

		// Under Programs.
		add_submenu_page(
			$program_parent,
			__( 'Program Sponsors', 'nashua-pal-core' ),
			__( 'Sponsors', 'nashua-pal-core' ),
			'edit_posts',
			'edit.php?post_type=' . self::POST_TYPE . '&pal_context=program'
		);
		add_submenu_page(
			$program_parent,
			__( 'Sponsor Levels', 'nashua-pal-core' ),
			__( 'Sponsor Levels', 'nashua-pal-core' ),
			'manage_categories',
			$tax_base . '&pal_context=program'
		);
	}

	/* ================================================================== */
	/*  Menu highlighting                                                  */
	/* ================================================================== */

	public function fix_parent_menu( $parent_file ) {
		$screen = get_current_screen();
		if ( ! $screen ) { return $parent_file; }
		if ( self::POST_TYPE !== $screen->post_type ) { return $parent_file; }

		$context = $this->current_context();
		if ( 'program' === $context ) {
			return 'edit.php?post_type=' . NPAL_CPT_Program::POST_TYPE;
		}
		return 'edit.php?post_type=' . NPAL_CPT_Event::POST_TYPE;
	}

	public function fix_submenu_highlight( $submenu_file, $parent_file ) {
		$screen = get_current_screen();
		if ( ! $screen ) { return $submenu_file; }
		if ( self::POST_TYPE !== $screen->post_type ) { return $submenu_file; }

		$context = $this->current_context();

		// Taxonomy screen.
		if ( self::TAXONOMY === $screen->taxonomy ) {
			return 'edit-tags.php?taxonomy=' . self::TAXONOMY . '&post_type=' . self::POST_TYPE . '&pal_context=' . $context;
		}

		// Post list / edit screen.
		return 'edit.php?post_type=' . self::POST_TYPE . '&pal_context=' . $context;
	}

	/**
	 * Append pal_context to "Add New" button URLs on the pal_sponsor list screen.
	 */
	public function add_context_to_new_link( $url, $path ) {
		if ( 'post-new.php?post_type=' . self::POST_TYPE !== $path ) { return $url; }

		$context = $this->current_context();
		if ( $context ) {
			$url = add_query_arg( 'pal_context', $context, $url );
		}
		return $url;
	}

	/**
	 * Read the current context from the URL.
	 */
	private function current_context() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$ctx = isset( $_GET['pal_context'] ) ? sanitize_key( $_GET['pal_context'] ) : '';
		return in_array( $ctx, array( 'event', 'program' ), true ) ? $ctx : 'event';
	}

	/* ================================================================== */
	/*  Admin columns                                                      */
	/* ================================================================== */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']              = $columns['cb'];
		$new['sponsor_logo']    = __( 'Logo', 'nashua-pal-core' );
		$new['title']           = __( 'Sponsor Name', 'nashua-pal-core' );
		$new['sponsor_context'] = __( 'Context', 'nashua-pal-core' );
		$new['sponsor_year']    = __( 'Year', 'nashua-pal-core' );
		$new['sponsor_level']   = __( 'Level', 'nashua-pal-core' );
		$new['sponsor_order']   = __( 'Order', 'nashua-pal-core' );
		$new['date']            = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'sponsor_logo':
				$thumb = get_the_post_thumbnail( $post_id, array( 60, 40 ) );
				echo $thumb ? $thumb : '<span style="color:#999;">—</span>';
				break;
			case 'sponsor_context':
				$ctx = get_post_meta( $post_id, '_pal_sponsor_context', true );
				$labels = array( 'event' => __( 'Event', 'nashua-pal-core' ), 'program' => __( 'Program', 'nashua-pal-core' ) );
				echo esc_html( isset( $labels[ $ctx ] ) ? $labels[ $ctx ] : '—' );
				break;
			case 'sponsor_year':
				echo esc_html( get_post_meta( $post_id, '_pal_sponsor_year', true ) ?: '—' );
				break;
			case 'sponsor_level':
				$terms = wp_get_post_terms( $post_id, self::TAXONOMY, array( 'fields' => 'names' ) );
				echo ! empty( $terms ) && ! is_wp_error( $terms ) ? esc_html( implode( ', ', $terms ) ) : '—';
				break;
			case 'sponsor_order':
				echo esc_html( get_post_meta( $post_id, '_pal_sponsor_order', true ) ?: '0' );
				break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['sponsor_year']  = 'sponsor_year';
		$columns['sponsor_order'] = 'sponsor_order';
		return $columns;
	}

	/* ================================================================== */
	/*  Admin query: context filter + sorting                              */
	/* ================================================================== */

	public function admin_query( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }

		// Context filter.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$context = isset( $_GET['pal_context'] ) ? sanitize_key( $_GET['pal_context'] ) : '';
		if ( in_array( $context, array( 'event', 'program' ), true ) ) {
			$meta = $query->get( 'meta_query' ) ?: array();
			$meta[] = array( 'key' => '_pal_sponsor_context', 'value' => $context );
			$query->set( 'meta_query', $meta );
		}

		// Sorting.
		$orderby = $query->get( 'orderby' );
		if ( 'sponsor_year' === $orderby ) {
			$query->set( 'meta_key', '_pal_sponsor_year' );
			$query->set( 'orderby', 'meta_value' );
		} elseif ( 'sponsor_order' === $orderby ) {
			$query->set( 'meta_key', '_pal_sponsor_order' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/* ================================================================== */
	/*  Term meta: Display Order + Badge Colour                            */
	/* ================================================================== */

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
				<p class="description"><?php esc_html_e( 'Lower numbers appear first.', 'nashua-pal-core' ); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="npal_badge_color"><?php esc_html_e( 'Badge Color', 'nashua-pal-core' ); ?></label></th>
			<td>
				<input type="color" id="npal_badge_color" name="npal_badge_color" value="<?php echo esc_attr( $color ); ?>" style="width:60px;height:36px;padding:2px;cursor:pointer;" />
				<p class="description"><?php esc_html_e( 'Background color for the sponsor level badge.', 'nashua-pal-core' ); ?></p>
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
			$submitted = sanitize_hex_color( wp_unslash( $_POST['npal_badge_color'] ) );
			update_term_meta( $term_id, '_badge_color', $submitted );

			// Mark as custom if the admin chose a non-default colour.
			if ( $submitted && '#11284D' !== $submitted && '#000000' !== $submitted ) {
				update_term_meta( $term_id, '_badge_color_source', 'custom' );
			}
		}
		if ( isset( $_POST['npal_badge_text_color'] ) ) {
			$val = 'dark' === $_POST['npal_badge_text_color'] ? 'dark' : 'light';
			update_term_meta( $term_id, '_badge_text_color', $val );
		}

		// Auto-assign badge colour for child levels when the colour
		// is still the default navy or empty (i.e. not manually chosen).
		$saved_color = get_term_meta( $term_id, '_badge_color', true );
		if ( empty( $saved_color ) || '#11284D' === $saved_color || '#000000' === $saved_color ) {
			$term_obj = get_term( $term_id, self::TAXONOMY );
			if ( $term_obj && ! is_wp_error( $term_obj ) && $term_obj->parent > 0 ) {
				$this->auto_assign_color( $term_id );
			}
		}
	}

	/* ================================================================== */
	/*  Term list table: badge colour column + quick-edit                   */
	/* ================================================================== */

	public function term_columns( $columns ) {
		$columns['badge_color'] = __( 'Badge Color', 'nashua-pal-core' );
		return $columns;
	}

	public function term_column_content( $content, $column, $term_id ) {
		if ( 'badge_color' === $column ) {
			$color  = get_term_meta( $term_id, '_badge_color', true ) ?: '#11284D';
			$text   = get_term_meta( $term_id, '_badge_text_color', true ) ?: 'light';
			$source = get_term_meta( $term_id, '_badge_color_source', true );

			$content = '<span class="npal-badge-swatch"'
				. ' data-badge-color="' . esc_attr( $color ) . '"'
				. ' data-badge-text="' . esc_attr( $text ) . '"'
				. ' style="display:inline-block;width:28px;height:28px;border-radius:4px;'
				. 'background:' . esc_attr( $color ) . ';border:1px solid #ccc;vertical-align:middle;">'
				. '</span>';

			if ( 'auto' === $source ) {
				$content .= ' <span style="color:#999;font-size:11px;">' . esc_html__( '(auto)', 'nashua-pal-core' ) . '</span>';
			}
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
					+ '<label style="display:flex;align-items:center;gap:6px;">'
					+ '<span><?php echo esc_js( __( 'Color', 'nashua-pal-core' ) ); ?></span>'
					+ '<input type="color" name="npal_badge_color" value="' + curColor + '" style="width:50px;height:30px;padding:1px;cursor:pointer;border:1px solid #8c8f94;border-radius:3px;" />'
					+ '</label>'
					+ '<label style="display:flex;align-items:center;gap:6px;">'
					+ '<span><?php echo esc_js( __( 'Text', 'nashua-pal-core' ) ); ?></span>'
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

		// Inject "Apply Auto Colors" button above the term list.
		$auto_url = wp_nonce_url(
			add_query_arg( 'npal_auto_colors', '1' ),
			'npal_auto_colors_nonce'
		);
		?>
		<script>
		(function($){
			$(function(){
				var $form = $( '.search-form' );
				if ( ! $form.length ) { $form = $( '#col-right' ); }
				$form.before(
					'<div style="margin:0 0 12px;">'
					+ '<a href="<?php echo esc_url( $auto_url ); ?>" class="button">'
					+ '<?php echo esc_js( __( 'Apply Auto Colors', 'nashua-pal-core' ) ); ?>'
					+ '</a>'
					+ ' <span style="color:#666;font-size:12px;"><?php echo esc_js( __( 'Assigns badge colors to child levels using name matching + palette cycling.', 'nashua-pal-core' ) ); ?></span>'
					+ '</div>'
				);
			});
		})(jQuery);
		</script>
		<?php
	}

	/* ================================================================== */
	/*  Seed default levels (one-time)                                     */
	/* ================================================================== */

	public function maybe_seed_levels() {
		if ( get_option( 'npal_sponsor_levels_seeded' ) ) { return; }

		$defaults = array(
			'presenting' => array( 'label' => 'Presenting Sponsor', 'order' => 1, 'color' => '#B8860B', 'text' => 'light' ),
			'gold'       => array( 'label' => 'Gold Sponsor',       'order' => 2, 'color' => '#F2B134', 'text' => 'dark' ),
			'silver'     => array( 'label' => 'Silver Sponsor',     'order' => 3, 'color' => '#8C8C8C', 'text' => 'light' ),
			'bronze'     => array( 'label' => 'Bronze Sponsor',     'order' => 4, 'color' => '#CD7F32', 'text' => 'light' ),
			'community'  => array( 'label' => 'Community Sponsor',  'order' => 5, 'color' => '#11284D', 'text' => 'light' ),
		);

		foreach ( $defaults as $slug => $data ) {
			if ( ! term_exists( $slug, self::TAXONOMY ) ) {
				$result = wp_insert_term( $data['label'], self::TAXONOMY, array( 'slug' => $slug ) );
				if ( ! is_wp_error( $result ) ) {
					update_term_meta( $result['term_id'], '_display_order',    $data['order'] );
					update_term_meta( $result['term_id'], '_badge_color',      $data['color'] );
					update_term_meta( $result['term_id'], '_badge_text_color', $data['text'] );
				}
			}
		}

		update_option( 'npal_sponsor_levels_seeded', 1 );
	}

	/* ================================================================== */
	/*  Auto-assign badge colours                                          */
	/* ================================================================== */

	/**
	 * Determine the best text colour for a given background hex.
	 *
	 * Uses perceived brightness (ITU-R BT.601 luma). Returns 'dark'
	 * for light backgrounds, 'light' for dark ones.
	 */
	public static function get_text_color_for_bg( $hex ) {
		$hex = ltrim( $hex, '#' );
		if ( 3 === strlen( $hex ) ) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}
		$r = hexdec( substr( $hex, 0, 2 ) );
		$g = hexdec( substr( $hex, 2, 2 ) );
		$b = hexdec( substr( $hex, 4, 2 ) );

		// Perceived brightness (0–255).
		$luma = 0.299 * $r + 0.587 * $g + 0.114 * $b;
		return $luma > 186 ? 'dark' : 'light';
	}

	/**
	 * Auto-assign a badge colour to a child sponsor level.
	 *
	 * 1. Try name-matching against NAME_COLOR_MAP.
	 * 2. Fall back to the next unused colour in COLOR_PALETTE
	 *    (cycling if all are taken).
	 */
	public function auto_assign_color( $term_id ) {
		$term = get_term( $term_id, self::TAXONOMY );
		if ( ! $term || is_wp_error( $term ) || 0 === (int) $term->parent ) {
			return;
		}

		$chosen = '';

		// 1. Name matching — first keyword hit wins.
		$name_lower = strtolower( $term->name );
		foreach ( self::NAME_COLOR_MAP as $keyword => $hex ) {
			if ( false !== strpos( $name_lower, $keyword ) ) {
				$chosen = $hex;
				break;
			}
		}

		// 2. Palette cycling fallback.
		if ( ! $chosen ) {
			// Collect colours already used by siblings under the same parent.
			$siblings = get_terms( array(
				'taxonomy'   => self::TAXONOMY,
				'parent'     => $term->parent,
				'hide_empty' => false,
				'exclude'    => array( $term_id ),
			) );

			$used = array();
			if ( ! empty( $siblings ) && ! is_wp_error( $siblings ) ) {
				foreach ( $siblings as $sib ) {
					$c = get_term_meta( $sib->term_id, '_badge_color', true );
					if ( $c ) { $used[ strtoupper( $c ) ] = true; }
				}
			}

			// Pick the first palette colour not yet used by a sibling.
			foreach ( self::COLOR_PALETTE as $hex ) {
				if ( ! isset( $used[ strtoupper( $hex ) ] ) ) {
					$chosen = $hex;
					break;
				}
			}

			// All colours used — loop from the start.
			if ( ! $chosen ) {
				$chosen = self::COLOR_PALETTE[0];
			}
		}

		$text = self::get_text_color_for_bg( $chosen );

		update_term_meta( $term_id, '_badge_color',        $chosen );
		update_term_meta( $term_id, '_badge_text_color',   $text );
		update_term_meta( $term_id, '_badge_color_source', 'auto' );
	}

	/* ================================================================== */
	/*  Bulk "Apply Auto Colors" action                                    */
	/* ================================================================== */

	/**
	 * Handle the "Apply Auto Colors" link on the Sponsor Levels page.
	 */
	public function handle_bulk_auto_colors() {
		if ( empty( $_GET['npal_auto_colors'] ) ) { return; }
		if ( ! current_user_can( 'manage_categories' ) ) { return; }

		check_admin_referer( 'npal_auto_colors_nonce' );

		$terms = get_terms( array(
			'taxonomy'   => self::TAXONOMY,
			'hide_empty' => false,
		) );

		$count = 0;
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				// Only process child terms (actual sponsor tiers).
				if ( 0 === (int) $term->parent ) { continue; }

				$color = get_term_meta( $term->term_id, '_badge_color', true );
				// Skip if a custom colour has been set.
				if ( ! empty( $color ) && '#11284D' !== $color && '#000000' !== $color ) {
					continue;
				}

				$this->auto_assign_color( $term->term_id );
				$count++;
			}
		}

		set_transient( 'npal_auto_colors_done', $count, 30 );

		$redirect = admin_url( 'edit-tags.php?taxonomy=' . self::TAXONOMY . '&post_type=' . self::POST_TYPE );
		if ( ! empty( $_GET['pal_context'] ) ) {
			$redirect = add_query_arg( 'pal_context', sanitize_key( $_GET['pal_context'] ), $redirect );
		}
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Show admin notice after bulk auto-colour assignment.
	 */
	public function auto_colors_admin_notice() {
		$count = get_transient( 'npal_auto_colors_done' );
		if ( false === $count ) { return; }
		delete_transient( 'npal_auto_colors_done' );
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php
				printf(
					/* translators: %d = number of sponsor levels updated */
					esc_html( _n(
						'Auto-assigned badge colors to %d sponsor level.',
						'Auto-assigned badge colors to %d sponsor levels.',
						(int) $count,
						'nashua-pal-core'
					) ),
					(int) $count
				);
				?>
			</p>
		</div>
		<?php
	}

	/* ================================================================== */
	/*  One-time migration: auto-colour existing child levels              */
	/* ================================================================== */

	/**
	 * Runs once per plugin version. Auto-assigns badge colours to every
	 * child sponsor level that still has the default navy or black.
	 */
	public function maybe_migrate_auto_colors() {
		$flag = 'npal_sponsor_auto_colors_v1';
		if ( get_option( $flag ) ) { return; }

		$terms = get_terms( array(
			'taxonomy'   => self::TAXONOMY,
			'hide_empty' => false,
		) );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				if ( 0 === (int) $term->parent ) { continue; }

				$color = get_term_meta( $term->term_id, '_badge_color', true );
				if ( ! empty( $color ) && '#11284D' !== $color && '#000000' !== $color ) {
					continue;
				}

				$this->auto_assign_color( $term->term_id );
			}
		}

		update_option( $flag, 1 );
	}

	/* ================================================================== */
	/*  Helpers                                                            */
	/* ================================================================== */

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
