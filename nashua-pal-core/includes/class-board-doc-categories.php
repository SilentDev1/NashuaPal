<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Manage categories (Event Names & Document Types) for Board Documents.
 *
 * Stored in option `npal_board_doc_categories`:
 *   array(
 *     'event_materials' => array( array('name'=>'...','order'=>0), ... ),
 *     'board_documents' => array( array('name'=>'...','order'=>0), ... ),
 *   )
 *
 * @package NashuaPalCore
 * @since   1.54.5
 */
final class NPAL_Board_Doc_Categories {

	const OPTION_KEY = 'npal_board_doc_categories';
	const NONCE      = 'npal_board_doc_cats';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 25 );
		add_action( 'admin_init', array( $this, 'handle_form' ) );
	}

	/* ------------------------------------------------------------------ */
	/*  Data helpers                                                        */
	/* ------------------------------------------------------------------ */

	public static function get_all() {
		$data = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $data ) ) { $data = array(); }
		if ( ! isset( $data['event_materials'] ) )  { $data['event_materials']  = array(); }
		if ( ! isset( $data['board_documents'] ) )   { $data['board_documents']  = array(); }

		// Auto-import any group values from existing posts that aren't yet in categories.
		$changed = false;
		foreach ( array( 'event_materials', 'board_documents' ) as $section ) {
			$existing_names = array_map( function( $c ) { return strtolower( $c['name'] ); }, $data[ $section ] );
			$post_groups    = self::query_post_groups( $section );
			foreach ( $post_groups as $grp ) {
				if ( ! in_array( strtolower( $grp ), $existing_names, true ) ) {
					$data[ $section ][] = array( 'name' => $grp, 'order' => 0 );
					$existing_names[]   = strtolower( $grp );
					$changed = true;
				}
			}
		}
		if ( $changed ) {
			update_option( self::OPTION_KEY, $data );
		}

		return $data;
	}

	/**
	 * Query distinct _board_doc_group values from posts for a given section.
	 */
	private static function query_post_groups( $section ) {
		global $wpdb;
		return $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->postmeta} ps ON pm.post_id = ps.post_id AND ps.meta_key = '_board_doc_section' AND ps.meta_value = %s
			 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID AND p.post_status != 'trash'
			 WHERE pm.meta_key = '_board_doc_group' AND pm.meta_value != ''
			 ORDER BY pm.meta_value ASC",
			$section
		) );
	}

	/**
	 * Get ordered category names for a section.
	 */
	public static function get_for_section( $section ) {
		$data = self::get_all();
		$cats = isset( $data[ $section ] ) ? $data[ $section ] : array();
		usort( $cats, function( $a, $b ) {
			return (int) $a['order'] - (int) $b['order'];
		} );
		return $cats;
	}

	/**
	 * Get just the names (ordered) for a section — used in dropdowns and shortcodes.
	 */
	public static function get_names( $section ) {
		return array_map( function( $c ) { return $c['name']; }, self::get_for_section( $section ) );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin menu                                                         */
	/* ------------------------------------------------------------------ */

	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=' . NPAL_CPT_Board_Doc::POST_TYPE,
			__( 'Manage Categories', 'nashua-pal-core' ),
			__( 'Manage Categories', 'nashua-pal-core' ),
			'edit_posts',
			'board-doc-categories',
			array( $this, 'render_page' )
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Handle form submission                                             */
	/* ------------------------------------------------------------------ */

	public function handle_form() {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( $_POST[ self::NONCE ], self::NONCE ) ) { return; }
		if ( ! current_user_can( 'edit_posts' ) ) { return; }

		$action = isset( $_POST['board_doc_cat_action'] ) ? sanitize_text_field( wp_unslash( $_POST['board_doc_cat_action'] ) ) : '';
		$data   = self::get_all();

		// Add new category.
		if ( 'add' === $action ) {
			$section = sanitize_text_field( wp_unslash( $_POST['cat_section'] ?? '' ) );
			$name    = sanitize_text_field( wp_unslash( $_POST['cat_name'] ?? '' ) );
			$order   = isset( $_POST['cat_order'] ) ? absint( $_POST['cat_order'] ) : 0;

			if ( $name && isset( $data[ $section ] ) ) {
				// Prevent duplicates.
				$exists = false;
				foreach ( $data[ $section ] as $c ) {
					if ( strtolower( $c['name'] ) === strtolower( $name ) ) { $exists = true; break; }
				}
				if ( ! $exists ) {
					$data[ $section ][] = array( 'name' => $name, 'order' => $order );
				}
			}
		}

		// Delete category.
		if ( 'delete' === $action ) {
			$section = sanitize_text_field( wp_unslash( $_POST['cat_section'] ?? '' ) );
			$name    = sanitize_text_field( wp_unslash( $_POST['cat_name'] ?? '' ) );
			if ( isset( $data[ $section ] ) ) {
				$data[ $section ] = array_values( array_filter( $data[ $section ], function( $c ) use ( $name ) {
					return $c['name'] !== $name;
				} ) );
			}
		}

		// Save order (bulk update).
		if ( 'save_order' === $action ) {
			$section = sanitize_text_field( wp_unslash( $_POST['cat_section'] ?? '' ) );
			$orders  = isset( $_POST['cat_orders'] ) && is_array( $_POST['cat_orders'] ) ? $_POST['cat_orders'] : array();
			if ( isset( $data[ $section ] ) ) {
				foreach ( $data[ $section ] as &$cat ) {
					if ( isset( $orders[ $cat['name'] ] ) ) {
						$cat['order'] = absint( $orders[ $cat['name'] ] );
					}
				}
				unset( $cat );
			}
		}

		update_option( self::OPTION_KEY, $data );

		// Redirect to avoid resubmit.
		wp_safe_redirect( admin_url( 'edit.php?post_type=' . NPAL_CPT_Board_Doc::POST_TYPE . '&page=board-doc-categories&updated=1' ) );
		exit;
	}

	/* ------------------------------------------------------------------ */
	/*  Render page                                                        */
	/* ------------------------------------------------------------------ */

	public function render_page() {
		$data = self::get_all();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Manage Categories', 'nashua-pal-core' ); ?></h1>

			<?php if ( ! empty( $_GET['updated'] ) ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Categories updated.', 'nashua-pal-core' ); ?></p></div>
			<?php endif; ?>

			<div style="display:flex;gap:40px;flex-wrap:wrap;margin-top:20px;">
				<?php
				$this->render_section_table(
					'event_materials',
					__( 'Event Material Categories', 'nashua-pal-core' ),
					__( 'These appear as tabs in the Event Materials section on the frontend.', 'nashua-pal-core' ),
					$data['event_materials']
				);
				$this->render_section_table(
					'board_documents',
					__( 'Board Document Categories', 'nashua-pal-core' ),
					__( 'These appear as expandable cards in the Board Documents section on the frontend.', 'nashua-pal-core' ),
					$data['board_documents']
				);
				?>
			</div>
		</div>
		<?php
	}

	private function render_section_table( $section, $title, $desc, $cats ) {
		usort( $cats, function( $a, $b ) { return (int) $a['order'] - (int) $b['order']; } );
		?>
		<div style="flex:1;min-width:400px;">
			<h2><?php echo esc_html( $title ); ?></h2>
			<p class="description" style="margin-bottom:12px;"><?php echo esc_html( $desc ); ?></p>

			<?php if ( $cats ) : ?>
			<!-- Save order form -->
			<form method="post">
				<?php wp_nonce_field( self::NONCE, self::NONCE ); ?>
				<input type="hidden" name="board_doc_cat_action" value="save_order" />
				<input type="hidden" name="cat_section" value="<?php echo esc_attr( $section ); ?>" />
				<table class="widefat striped" style="margin-bottom:16px;">
					<thead>
						<tr>
							<th style="width:50%;"><?php esc_html_e( 'Category Name', 'nashua-pal-core' ); ?></th>
							<th style="width:25%;"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></th>
							<th style="width:25%;"><?php esc_html_e( 'Actions', 'nashua-pal-core' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $cats as $cat ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $cat['name'] ); ?></strong></td>
							<td>
								<input type="number" name="cat_orders[<?php echo esc_attr( $cat['name'] ); ?>]"
									value="<?php echo esc_attr( $cat['order'] ); ?>" min="0" step="1" style="width:70px;" />
							</td>
							<td>
								<button type="submit" name="board_doc_cat_action" value="delete"
									class="button-link" style="color:#b32d2e;"
									onclick="this.form.querySelector('[name=board_doc_cat_action]').value='delete';this.form.querySelector('[name=cat_name]').value='<?php echo esc_js( $cat['name'] ); ?>';return confirm('<?php echo esc_js( __( 'Delete this category?', 'nashua-pal-core' ) ); ?>');">
									<span class="dashicons dashicons-trash" style="vertical-align:middle;"></span> <?php esc_html_e( 'Delete', 'nashua-pal-core' ); ?>
								</button>
							</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<input type="hidden" name="cat_name" value="" />
				<?php submit_button( __( 'Save Order', 'nashua-pal-core' ), 'secondary', '', false ); ?>
			</form>
			<?php else : ?>
				<p style="color:#999;margin-bottom:16px;"><?php esc_html_e( 'No categories yet.', 'nashua-pal-core' ); ?></p>
			<?php endif; ?>

			<!-- Add new form -->
			<form method="post" style="margin-top:16px;padding:16px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;">
				<?php wp_nonce_field( self::NONCE, self::NONCE ); ?>
				<input type="hidden" name="board_doc_cat_action" value="add" />
				<input type="hidden" name="cat_section" value="<?php echo esc_attr( $section ); ?>" />
				<h3 style="margin-top:0;"><?php esc_html_e( 'Add New Category', 'nashua-pal-core' ); ?></h3>
				<p>
					<label><?php esc_html_e( 'Name', 'nashua-pal-core' ); ?><br />
						<input type="text" name="cat_name" class="regular-text" required />
					</label>
				</p>
				<p>
					<label><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?><br />
						<input type="number" name="cat_order" value="0" min="0" step="1" style="width:70px;" />
					</label>
				</p>
				<?php submit_button( __( 'Add Category', 'nashua-pal-core' ), 'primary', '', false ); ?>
			</form>
		</div>
		<?php
	}
}
