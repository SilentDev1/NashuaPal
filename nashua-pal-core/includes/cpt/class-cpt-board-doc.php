<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Custom Post Type: Board Documents.
 *
 * Registers `pal_board_doc` CPT for Board Member page documents
 * (Event Materials, Board Documents, Meeting Minutes).
 *
 * Admin menu splits into three "Add" pages, one per section.
 *
 * @package NashuaPalCore
 * @since   1.54.0
 */
final class NPAL_CPT_Board_Doc {

	const POST_TYPE = 'pal_board_doc';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 20 );
		add_action( 'admin_print_footer_scripts', array( $this, 'fix_add_new_link' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns',  array( $this, 'admin_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'admin_column_content' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'sortable_columns' ) );
		add_action( 'pre_get_posts', array( $this, 'admin_sort' ) );
		add_action( 'restrict_manage_posts', array( $this, 'admin_section_filter' ) );
		add_action( 'pre_get_posts', array( $this, 'admin_filter_query' ) );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'Board Documents', 'nashua-pal-core' ),
			'singular_name'      => __( 'Board Document', 'nashua-pal-core' ),
			'menu_name'          => __( 'Board Documents', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Document', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Board Document', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Board Document', 'nashua-pal-core' ),
			'new_item'           => __( 'New Board Document', 'nashua-pal-core' ),
			'view_item'          => __( 'View Board Document', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Board Documents', 'nashua-pal-core' ),
			'not_found'          => __( 'No documents found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No documents found in Trash.', 'nashua-pal-core' ),
			'all_items'          => __( 'All Documents', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 18,
			'menu_icon'     => 'dashicons-media-document',
			'supports'      => array( 'title' ),
			'show_in_rest'  => false,
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin menu: replace "Add New" with 3 section-specific links        */
	/* ------------------------------------------------------------------ */

	public function admin_menu() {
		$parent = 'edit.php?post_type=' . self::POST_TYPE;

		// Remove the default "Add New Board Document" submenu.
		remove_submenu_page( $parent, 'post-new.php?post_type=' . self::POST_TYPE );

		// Add section-specific "Add" pages.
		add_submenu_page(
			$parent,
			__( 'Add Event Material', 'nashua-pal-core' ),
			__( 'Add Event Material', 'nashua-pal-core' ),
			'edit_posts',
			'post-new.php?post_type=' . self::POST_TYPE . '&board_doc_section=event_materials'
		);

		add_submenu_page(
			$parent,
			__( 'Add Board Document', 'nashua-pal-core' ),
			__( 'Add Board Document', 'nashua-pal-core' ),
			'edit_posts',
			'post-new.php?post_type=' . self::POST_TYPE . '&board_doc_section=board_documents'
		);

		add_submenu_page(
			$parent,
			__( 'Add Meeting Minutes', 'nashua-pal-core' ),
			__( 'Add Meeting Minutes', 'nashua-pal-core' ),
			'edit_posts',
			'post-new.php?post_type=' . self::POST_TYPE . '&board_doc_section=meeting_minutes'
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Fix "Add New" button on edit / list screens                        */
	/* ------------------------------------------------------------------ */

	public function fix_add_new_link() {
		$screen = get_current_screen();
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) { return; }

		$section = '';

		// On the post edit screen, read section from saved meta.
		if ( 'post' === $screen->base ) {
			global $post;
			if ( $post ) {
				$section = get_post_meta( $post->ID, '_board_doc_section', true );
			}
		}

		// On the post-new screen, read section from URL param.
		if ( 'post' === $screen->base && 'add' === $screen->action ) {
			if ( ! empty( $_GET['board_doc_section'] ) ) {
				$section = sanitize_text_field( wp_unslash( $_GET['board_doc_section'] ) );
			}
		}

		if ( ! $section ) { return; }

		$labels = array(
			'event_materials' => array(
				'add_new'   => __( 'Add New Event Material', 'nashua-pal-core' ),
				'edit'      => __( 'Edit Event Material', 'nashua-pal-core' ),
				'add_title' => __( 'Add New Event Material', 'nashua-pal-core' ),
			),
			'board_documents' => array(
				'add_new'   => __( 'Add New Board Document', 'nashua-pal-core' ),
				'edit'      => __( 'Edit Board Document', 'nashua-pal-core' ),
				'add_title' => __( 'Add New Board Document', 'nashua-pal-core' ),
			),
			'meeting_minutes' => array(
				'add_new'   => __( 'Add New Meeting Minutes', 'nashua-pal-core' ),
				'edit'      => __( 'Edit Meeting Minutes', 'nashua-pal-core' ),
				'add_title' => __( 'Add New Meeting Minutes', 'nashua-pal-core' ),
			),
		);

		$lbl = isset( $labels[ $section ] ) ? $labels[ $section ] : $labels['board_documents'];
		$url = admin_url( 'post-new.php?post_type=' . self::POST_TYPE . '&board_doc_section=' . $section );

		$is_edit = ( 'post' === $screen->base && 'add' !== $screen->action );
		$page_title = $is_edit ? $lbl['edit'] : $lbl['add_title'];
		$btn_text   = $lbl['add_new'];
		?>
		<script>
		(function(){
			var btn = document.querySelector('.page-title-action');
			if (btn) {
				btn.href = <?php echo wp_json_encode( esc_url_raw( $url ) ); ?>;
				btn.textContent = <?php echo wp_json_encode( $btn_text ); ?>;
			}
			var heading = document.querySelector('.wrap h1');
			if (heading && heading.firstChild && heading.firstChild.nodeType === 3) {
				heading.firstChild.textContent = <?php echo wp_json_encode( $page_title . ' ' ); ?>;
			}
		})();
		</script>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Admin list: section filter dropdown                                */
	/* ------------------------------------------------------------------ */

	public function admin_section_filter( $post_type ) {
		if ( self::POST_TYPE !== $post_type ) { return; }

		// Section filter.
		$current_section = isset( $_GET['board_doc_section_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['board_doc_section_filter'] ) ) : '';
		$options = array(
			''                 => __( 'All Sections', 'nashua-pal-core' ),
			'event_materials'  => __( 'Event Materials', 'nashua-pal-core' ),
			'board_documents'  => __( 'Board Documents', 'nashua-pal-core' ),
			'meeting_minutes'  => __( 'Meeting Minutes', 'nashua-pal-core' ),
		);
		echo '<select name="board_doc_section_filter">';
		foreach ( $options as $val => $label ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $val ), selected( $current_section, $val, false ), esc_html( $label ) );
		}
		echo '</select>';

		// Category filter — gather unique _board_doc_group values.
		$current_cat = isset( $_GET['board_doc_category_filter'] ) ? sanitize_text_field( wp_unslash( $_GET['board_doc_category_filter'] ) ) : '';
		global $wpdb;
		$categories = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key = %s AND pm.meta_value != '' AND p.post_type = %s AND p.post_status != 'trash'
			 ORDER BY pm.meta_value ASC",
			'_board_doc_group',
			self::POST_TYPE
		) );

		echo '<select name="board_doc_category_filter">';
		printf( '<option value="">%s</option>', esc_html__( 'All Categories', 'nashua-pal-core' ) );
		foreach ( $categories as $cat ) {
			printf( '<option value="%s"%s>%s</option>', esc_attr( $cat ), selected( $current_cat, $cat, false ), esc_html( $cat ) );
		}
		echo '</select>';
	}

	public function admin_filter_query( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }

		$meta_query = $query->get( 'meta_query' ) ?: array();

		if ( ! empty( $_GET['board_doc_section_filter'] ) ) {
			$section = sanitize_text_field( wp_unslash( $_GET['board_doc_section_filter'] ) );
			$meta_query[] = array( 'key' => '_board_doc_section', 'value' => $section );
		}

		if ( ! empty( $_GET['board_doc_category_filter'] ) ) {
			$category = sanitize_text_field( wp_unslash( $_GET['board_doc_category_filter'] ) );
			$meta_query[] = array( 'key' => '_board_doc_group', 'value' => $category );
		}

		if ( ! empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Admin list columns                                                 */
	/* ------------------------------------------------------------------ */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']               = $columns['cb'];
		$new['title']            = __( 'Title', 'nashua-pal-core' );
		$new['board_doc_section'] = __( 'Section', 'nashua-pal-core' );
		$new['board_doc_group']  = __( 'Group', 'nashua-pal-core' );
		$new['board_doc_year']   = __( 'Year', 'nashua-pal-core' );
		$new['board_doc_file']   = __( 'File', 'nashua-pal-core' );
		$new['board_doc_order']  = __( 'Order', 'nashua-pal-core' );
		$new['date']             = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'board_doc_section':
				$section = get_post_meta( $post_id, '_board_doc_section', true );
				$labels  = array(
					'event_materials'  => __( 'Event Materials', 'nashua-pal-core' ),
					'board_documents'  => __( 'Board Documents', 'nashua-pal-core' ),
					'meeting_minutes'  => __( 'Meeting Minutes', 'nashua-pal-core' ),
				);
				echo isset( $labels[ $section ] ) ? esc_html( $labels[ $section ] ) : '<span style="color:#999;">—</span>';
				break;
			case 'board_doc_group':
				$group = get_post_meta( $post_id, '_board_doc_group', true );
				echo $group ? esc_html( $group ) : '<span style="color:#999;">—</span>';
				break;
			case 'board_doc_year':
				$year = get_post_meta( $post_id, '_board_doc_year', true );
				echo $year ? esc_html( $year ) : '<span style="color:#999;">—</span>';
				break;
			case 'board_doc_file':
				$file_id = get_post_meta( $post_id, '_board_doc_file_id', true );
				if ( $file_id ) {
					$url = wp_get_attachment_url( $file_id );
					if ( $url ) {
						echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener"><span class="dashicons dashicons-media-document" style="color:#071F3F;"></span> ' . esc_html__( 'View', 'nashua-pal-core' ) . '</a>';
					} else {
						echo '<span style="color:#999;">—</span>';
					}
				} else {
					echo '<span style="color:#999;">—</span>';
				}
				break;
			case 'board_doc_order':
				echo esc_html( get_post_meta( $post_id, '_board_doc_order', true ) );
				break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['board_doc_year']  = 'board_doc_year';
		$columns['board_doc_order'] = 'board_doc_order';
		return $columns;
	}

	public function admin_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }

		$orderby = $query->get( 'orderby' );
		if ( 'board_doc_year' === $orderby ) {
			$query->set( 'meta_key', '_board_doc_year' );
			$query->set( 'orderby', 'meta_value_num' );
		} elseif ( 'board_doc_order' === $orderby ) {
			$query->set( 'meta_key', '_board_doc_order' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}
}
