<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_CPT_Scholar {

	const POST_TYPE = 'pal_scholar';

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
		add_action( 'admin_menu', array( $this, 'settings_submenu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_assets' ) );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'Scholarship Recipients', 'nashua-pal-core' ),
			'singular_name'      => __( 'Scholarship Recipient', 'nashua-pal-core' ),
			'menu_name'          => __( 'Scholarships', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Recipient', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Recipient', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Recipient', 'nashua-pal-core' ),
			'new_item'           => __( 'New Recipient', 'nashua-pal-core' ),
			'view_item'          => __( 'View Recipient', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Recipients', 'nashua-pal-core' ),
			'not_found'          => __( 'No recipients found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No recipients found in Trash.', 'nashua-pal-core' ),
			'all_items'          => __( 'All Recipients', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => 'scholarships',
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 5,
			'menu_icon'     => 'dashicons-welcome-learn-more',
			'supports'      => array( 'title', 'thumbnail' ),
			'show_in_rest'  => false,
			'rewrite'       => array( 'slug' => 'scholar', 'with_front' => false ),
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin list columns                                                 */
	/* ------------------------------------------------------------------ */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']                = $columns['cb'];
		$new['scholar_thumb']     = __( 'Photo', 'nashua-pal-core' );
		$new['title']             = __( 'Recipient Name', 'nashua-pal-core' );
		$new['scholar_program']   = __( 'Program', 'nashua-pal-core' );
		$new['scholar_amount']    = __( 'Amount', 'nashua-pal-core' );
		$new['scholar_school']    = __( 'School', 'nashua-pal-core' );
		$new['scholar_alumni']    = __( 'Alumni', 'nashua-pal-core' );
		$new['scholar_year']      = __( 'Year', 'nashua-pal-core' );
		$new['scholar_order']     = __( 'Order', 'nashua-pal-core' );
		$new['date']              = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'scholar_thumb':
				$thumb = get_the_post_thumbnail( $post_id, array( 50, 50 ) );
				echo $thumb ? '<span style="display:inline-block;width:50px;height:50px;border-radius:50%;overflow:hidden;">' . $thumb . '</span>' : '<span style="color:#999;">—</span>';
				break;
			case 'scholar_program':
				$prog_id = get_post_meta( $post_id, '_pal_scholar_scholarship', true );
				if ( $prog_id ) {
					$prog = get_post( (int) $prog_id );
					echo $prog ? esc_html( $prog->post_title ) : '<span style="color:#999;">—</span>';
				} else {
					echo '<span style="color:#999;">—</span>';
				}
				break;
			case 'scholar_amount':
				echo esc_html( get_post_meta( $post_id, '_pal_scholar_amount', true ) );
				break;
			case 'scholar_school':
				echo esc_html( get_post_meta( $post_id, '_pal_scholar_school', true ) );
				break;
			case 'scholar_alumni':
				echo esc_html( get_post_meta( $post_id, '_pal_scholar_alumni', true ) ?: '—' );
				break;
			case 'scholar_year':
				echo esc_html( get_post_meta( $post_id, '_pal_scholar_year', true ) );
				break;
			case 'scholar_order':
				echo esc_html( get_post_meta( $post_id, '_pal_scholar_order', true ) ?: '0' );
				break;
		}
	}

	public function sortable_columns( $columns ) {
		$columns['scholar_year']  = 'scholar_year';
		$columns['scholar_order'] = 'scholar_order';
		return $columns;
	}

	public function admin_sort( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) { return; }

		$orderby = $query->get( 'orderby' );
		if ( 'scholar_year' === $orderby ) {
			$query->set( 'meta_key', '_pal_scholar_year' );
			$query->set( 'orderby', 'meta_value' );
		} elseif ( 'scholar_order' === $orderby ) {
			$query->set( 'meta_key', '_pal_scholar_order' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Scholarship Featured Banner Settings (submenu page)                */
	/* ------------------------------------------------------------------ */

	const SETTINGS_SLUG = 'pal-scholarship-settings';

	public function settings_submenu() {
		add_submenu_page(
			'edit.php?post_type=' . self::POST_TYPE,
			__( 'Featured Banner Settings', 'nashua-pal-core' ),
			__( 'Banner Settings', 'nashua-pal-core' ),
			'manage_options',
			self::SETTINGS_SLUG,
			array( $this, 'render_settings' )
		);
	}

	public function enqueue_settings_assets( $hook ) {
		if ( ! is_string( $hook ) || false === strpos( $hook, self::SETTINGS_SLUG ) ) { return; }
		wp_enqueue_media();
	}

	public function render_settings() {
		// Handle save.
		if ( isset( $_POST['pal_scholar_settings_nonce'] ) && wp_verify_nonce( $_POST['pal_scholar_settings_nonce'], 'pal_scholar_settings' ) ) {
			$this->save_settings();
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'nashua-pal-core' ) . '</p></div>';
		}

		$opts = get_option( NPAL_OPTION_KEY, array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Scholarship Featured Banner', 'nashua-pal-core' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Default content for the "PAL Scholarship Featured" Elementor widget. Values entered directly in the widget will override these defaults.', 'nashua-pal-core' ); ?></p>

			<form method="post">
				<?php wp_nonce_field( 'pal_scholar_settings', 'pal_scholar_settings_nonce' ); ?>

				<h2 style="margin:24px 0 6px;color:#071F3F;"><?php esc_html_e( 'Image', 'nashua-pal-core' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label><?php esc_html_e( 'Photo', 'nashua-pal-core' ); ?></label></th>
						<td>
							<?php $scholar_photo = $opts['scholar_photo'] ?? ''; ?>
							<input type="hidden" id="scholar_photo" name="scholar_photo" value="<?php echo esc_attr( $scholar_photo ); ?>" />
							<div style="margin-bottom:8px;">
								<?php if ( $scholar_photo ) : ?>
									<img id="scholar_photo_preview" src="<?php echo esc_url( $scholar_photo ); ?>" style="max-width:300px;max-height:180px;border-radius:8px;border:1px solid #ddd;" />
								<?php else : ?>
									<img id="scholar_photo_preview" src="" style="max-width:300px;max-height:180px;border-radius:8px;border:1px solid #ddd;display:none;" />
								<?php endif; ?>
							</div>
							<button type="button" class="button" onclick="var f=wp.media({title:'Select Photo',multiple:false});f.on('select',function(){var a=f.state().get('selection').first().toJSON();document.getElementById('scholar_photo').value=a.url;var p=document.getElementById('scholar_photo_preview');p.src=a.url;p.style.display='';});f.open();"><?php esc_html_e( 'Select Image', 'nashua-pal-core' ); ?></button>
							<button type="button" class="button" onclick="document.getElementById('scholar_photo').value='';document.getElementById('scholar_photo_preview').style.display='none';"><?php esc_html_e( 'Remove', 'nashua-pal-core' ); ?></button>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="scholar_icon"><?php esc_html_e( 'Overlay Icon Class', 'nashua-pal-core' ); ?></label></th>
						<td><input type="text" id="scholar_icon" name="scholar_icon" class="regular-text" value="<?php echo esc_attr( $opts['scholar_icon'] ?? '' ); ?>" placeholder="fas fa-graduation-cap" /></td>
					</tr>
				</table>

				<h2 style="margin:24px 0 6px;color:#071F3F;"><?php esc_html_e( 'Content', 'nashua-pal-core' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="scholar_eyebrow"><?php esc_html_e( 'Eyebrow', 'nashua-pal-core' ); ?></label></th>
						<td><input type="text" id="scholar_eyebrow" name="scholar_eyebrow" class="regular-text" value="<?php echo esc_attr( $opts['scholar_eyebrow'] ?? '' ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="scholar_title"><?php esc_html_e( 'Title', 'nashua-pal-core' ); ?></label></th>
						<td><input type="text" id="scholar_title" name="scholar_title" class="regular-text" value="<?php echo esc_attr( $opts['scholar_title'] ?? '' ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="scholar_subtitle"><?php esc_html_e( 'Subtitle (cursive)', 'nashua-pal-core' ); ?></label></th>
						<td><input type="text" id="scholar_subtitle" name="scholar_subtitle" class="regular-text" value="<?php echo esc_attr( $opts['scholar_subtitle'] ?? '' ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="scholar_highlight"><?php esc_html_e( 'Highlight Text (bold)', 'nashua-pal-core' ); ?></label></th>
						<td><input type="text" id="scholar_highlight" name="scholar_highlight" class="large-text" value="<?php echo esc_attr( $opts['scholar_highlight'] ?? '' ); ?>" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="scholar_description"><?php esc_html_e( 'Description', 'nashua-pal-core' ); ?></label></th>
						<td><textarea id="scholar_description" name="scholar_description" rows="4" class="large-text"><?php echo esc_textarea( $opts['scholar_description'] ?? '' ); ?></textarea></td>
					</tr>
				</table>

				<h2 style="margin:24px 0 6px;color:#071F3F;"><?php esc_html_e( 'Info Items', 'nashua-pal-core' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
						<tr>
							<th scope="row"><?php printf( esc_html__( 'Item %d', 'nashua-pal-core' ), $i ); ?></th>
							<td>
								<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
									<input type="text" name="scholar_info_<?php echo $i; ?>_icon" value="<?php echo esc_attr( $opts["scholar_info_{$i}_icon"] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Icon class', 'nashua-pal-core' ); ?>" style="width:160px;" />
									<input type="text" name="scholar_info_<?php echo $i; ?>_label" value="<?php echo esc_attr( $opts["scholar_info_{$i}_label"] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Label', 'nashua-pal-core' ); ?>" style="width:180px;" />
									<input type="text" name="scholar_info_<?php echo $i; ?>_value" value="<?php echo esc_attr( $opts["scholar_info_{$i}_value"] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Value', 'nashua-pal-core' ); ?>" style="width:200px;" />
								</div>
							</td>
						</tr>
					<?php endfor; ?>
				</table>

				<h2 style="margin:24px 0 6px;color:#071F3F;"><?php esc_html_e( 'Buttons', 'nashua-pal-core' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Primary Button', 'nashua-pal-core' ); ?></th>
						<td>
							<div style="display:flex;gap:8px;flex-wrap:wrap;">
								<input type="text" name="scholar_btn_primary_label" value="<?php echo esc_attr( $opts['scholar_btn_primary_label'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Label', 'nashua-pal-core' ); ?>" style="width:220px;" />
								<input type="text" name="scholar_btn_primary_url" value="<?php echo esc_attr( $opts['scholar_btn_primary_url'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'URL', 'nashua-pal-core' ); ?>" style="width:280px;" />
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Secondary Button', 'nashua-pal-core' ); ?></th>
						<td>
							<div style="display:flex;gap:8px;flex-wrap:wrap;">
								<input type="text" name="scholar_btn_secondary_label" value="<?php echo esc_attr( $opts['scholar_btn_secondary_label'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Label', 'nashua-pal-core' ); ?>" style="width:220px;" />
								<input type="text" name="scholar_btn_secondary_url" value="<?php echo esc_attr( $opts['scholar_btn_secondary_url'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'URL', 'nashua-pal-core' ); ?>" style="width:280px;" />
							</div>
						</td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	private function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		$opts = get_option( NPAL_OPTION_KEY, array() );

		$text_fields = array(
			'scholar_photo', 'scholar_icon', 'scholar_eyebrow', 'scholar_title',
			'scholar_subtitle', 'scholar_highlight', 'scholar_description',
			'scholar_btn_primary_label', 'scholar_btn_primary_url',
			'scholar_btn_secondary_label', 'scholar_btn_secondary_url',
		);
		for ( $i = 1; $i <= 4; $i++ ) {
			$text_fields[] = "scholar_info_{$i}_icon";
			$text_fields[] = "scholar_info_{$i}_label";
			$text_fields[] = "scholar_info_{$i}_value";
		}

		foreach ( $text_fields as $key ) {
			$value = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
			if ( strpos( $key, '_url' ) !== false || $key === 'scholar_photo' ) {
				$opts[ $key ] = esc_url_raw( $value );
			} else {
				$opts[ $key ] = sanitize_text_field( $value );
			}
		}

		update_option( NPAL_OPTION_KEY, $opts );
	}
}
