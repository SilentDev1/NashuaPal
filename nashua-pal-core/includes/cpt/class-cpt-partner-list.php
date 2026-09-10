<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Custom Post Type: Partner List.
 *
 * Registers `pal_partner_list` CPT for the Partners recognition page.
 * One post per partner tier — the post title is the tier label
 * (e.g. "Visionary") and a textarea meta field holds all partner
 * names (one per line).
 *
 * Not public (no front-end archive/single) — displayed exclusively
 * via the [pal_partner_list] shortcode and Elementor widgets.
 *
 * @package NashuaPalCore
 * @since   1.40.0
 */
final class NPAL_CPT_Partner_List {

	const POST_TYPE = 'pal_partner_list';

	/**
	 * Available tier slugs in display order.
	 */
	const TIERS = array(
		'visionary'    => 'Visionary',
		'empowerment'  => 'Empowerment',
		'inspiration'  => 'Inspiration',
		'engagement'   => 'Engagement Partners',
		'friends'      => 'Friends of PAL',
	);

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( __CLASS__, 'register' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns',  array( $this, 'admin_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'admin_column_content' ), 10, 2 );

		// Featured Partners admin page.
		add_action( 'admin_menu', array( $this, 'register_featured_partners_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_featured_partners_media' ) );
	}

	public static function register() {
		$labels = array(
			'name'               => __( 'Partners List', 'nashua-pal-core' ),
			'singular_name'      => __( 'Partner Tier', 'nashua-pal-core' ),
			'menu_name'          => __( 'Partners List', 'nashua-pal-core' ),
			'add_new'            => __( 'Add Partner Tier', 'nashua-pal-core' ),
			'add_new_item'       => __( 'Add New Partner Tier', 'nashua-pal-core' ),
			'edit_item'          => __( 'Edit Partner Tier', 'nashua-pal-core' ),
			'new_item'           => __( 'New Partner Tier', 'nashua-pal-core' ),
			'view_item'          => __( 'View Partner Tier', 'nashua-pal-core' ),
			'search_items'       => __( 'Search Partners List', 'nashua-pal-core' ),
			'not_found'          => __( 'No partner tiers found.', 'nashua-pal-core' ),
			'not_found_in_trash' => __( 'No partner tiers found in Trash.', 'nashua-pal-core' ),
			'all_items'          => __( 'All Partner Tiers', 'nashua-pal-core' ),
		);

		register_post_type( self::POST_TYPE, array(
			'labels'        => $labels,
			'public'        => false,
			'has_archive'   => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 11,
			'menu_icon'     => 'dashicons-groups',
			'supports'      => array( 'title' ),
			'show_in_rest'  => false,
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin list columns                                                 */
	/* ------------------------------------------------------------------ */

	public function admin_columns( $columns ) {
		$new = array();
		$new['cb']                    = $columns['cb'];
		$new['title']                 = __( 'Tier Label', 'nashua-pal-core' );
		$new['partner_list_tier']     = __( 'Tier', 'nashua-pal-core' );
		$new['partner_list_count']    = __( 'Partners', 'nashua-pal-core' );
		$new['date']                  = $columns['date'];
		return $new;
	}

	public function admin_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'partner_list_tier':
				$tier = get_post_meta( $post_id, '_pal_partner_list_tier', true );
				$label = isset( self::TIERS[ $tier ] ) ? self::TIERS[ $tier ] : '—';
				echo esc_html( $label );
				break;
			case 'partner_list_count':
				$names = get_post_meta( $post_id, '_pal_partner_list_names', true );
				if ( $names ) {
					$lines = array_filter( array_map( 'trim', explode( "\n", $names ) ) );
					echo esc_html( count( $lines ) );
				} else {
					echo '0';
				}
				break;
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Helpers                                                            */
	/* ------------------------------------------------------------------ */

	/* ------------------------------------------------------------------ */
	/*  Featured Partners admin page                                       */
	/* ------------------------------------------------------------------ */

	public function register_featured_partners_page() {
		add_submenu_page(
			'edit.php?post_type=' . self::POST_TYPE,
			__( 'Featured Partners', 'nashua-pal-core' ),
			__( 'Featured Partners', 'nashua-pal-core' ),
			'manage_options',
			'pal-featured-partners',
			array( $this, 'render_featured_partners_page' )
		);
	}

	public function enqueue_featured_partners_media( $hook ) {
		if ( 'pal_partner_list_page_pal-featured-partners' !== $hook ) {
			return;
		}
		wp_enqueue_media();
	}

	public function render_featured_partners_page() {
		// Save handler.
		if ( isset( $_POST['pal_featured_partners_nonce'] ) &&
			 wp_verify_nonce( $_POST['pal_featured_partners_nonce'], 'pal_save_featured_partners' ) &&
			 current_user_can( 'manage_options' )
		) {
			$partners = array();
			if ( ! empty( $_POST['fp_name'] ) && is_array( $_POST['fp_name'] ) ) {
				foreach ( $_POST['fp_name'] as $i => $name ) {
					$name     = sanitize_text_field( wp_unslash( $name ) );
					$logo_url = isset( $_POST['fp_logo'][ $i ] ) ? esc_url_raw( wp_unslash( $_POST['fp_logo'][ $i ] ) ) : '';
					$url      = isset( $_POST['fp_url'][ $i ] )  ? esc_url_raw( wp_unslash( $_POST['fp_url'][ $i ] ) )  : '';
					if ( '' === $name && '' === $logo_url ) {
						continue; // skip empty rows
					}
					$partners[] = array(
						'logo_url' => $logo_url,
						'name'     => $name,
						'url'      => $url,
					);
				}
			}
			update_option( 'npal_featured_partners', $partners );
			echo '<div class="updated notice is-dismissible"><p>' . esc_html__( 'Featured Partners saved.', 'nashua-pal-core' ) . '</p></div>';
		}

		$partners = get_option( 'npal_featured_partners', array() );
		if ( ! is_array( $partners ) ) { $partners = array(); }
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Featured Partners', 'nashua-pal-core' ); ?></h1>
			<p><?php esc_html_e( 'Manage the featured partners that appear with logos above the partner name list.', 'nashua-pal-core' ); ?></p>

			<form method="post">
				<?php wp_nonce_field( 'pal_save_featured_partners', 'pal_featured_partners_nonce' ); ?>
				<table class="widefat" id="pal-fp-table">
					<thead>
						<tr>
							<th style="width:200px;"><?php esc_html_e( 'Logo', 'nashua-pal-core' ); ?></th>
							<th><?php esc_html_e( 'Partner Name', 'nashua-pal-core' ); ?></th>
							<th><?php esc_html_e( 'Website URL', 'nashua-pal-core' ); ?></th>
							<th style="width:80px;"></th>
						</tr>
					</thead>
					<tbody id="pal-fp-rows">
						<?php if ( ! empty( $partners ) ) : ?>
							<?php foreach ( $partners as $idx => $p ) : ?>
								<tr class="pal-fp-row">
									<td>
										<input type="hidden" name="fp_logo[<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( $p['logo_url'] ); ?>" class="pal-fp-logo-input">
										<div class="pal-fp-logo-preview" style="margin-bottom:6px;">
											<?php if ( $p['logo_url'] ) : ?>
												<img src="<?php echo esc_url( $p['logo_url'] ); ?>" style="max-height:60px;max-width:160px;">
											<?php endif; ?>
										</div>
										<button type="button" class="button pal-fp-upload-btn"><?php esc_html_e( 'Upload Logo', 'nashua-pal-core' ); ?></button>
									</td>
									<td><input type="text" name="fp_name[<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( $p['name'] ); ?>" class="regular-text"></td>
									<td><input type="url" name="fp_url[<?php echo (int) $idx; ?>]" value="<?php echo esc_attr( $p['url'] ); ?>" class="regular-text" placeholder="https://"></td>
									<td><button type="button" class="button pal-fp-remove-btn">&times; <?php esc_html_e( 'Remove', 'nashua-pal-core' ); ?></button></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
				<p>
					<button type="button" class="button button-secondary" id="pal-fp-add"><?php esc_html_e( '+ Add Featured Partner', 'nashua-pal-core' ); ?></button>
				</p>
				<?php submit_button( __( 'Save Featured Partners', 'nashua-pal-core' ) ); ?>
			</form>
		</div>

		<script>
		(function($){
			var idx = <?php echo count( $partners ); ?>;

			$('#pal-fp-add').on('click', function(){
				var row = '<tr class="pal-fp-row">' +
					'<td>' +
						'<input type="hidden" name="fp_logo[' + idx + ']" value="" class="pal-fp-logo-input">' +
						'<div class="pal-fp-logo-preview" style="margin-bottom:6px;"></div>' +
						'<button type="button" class="button pal-fp-upload-btn"><?php echo esc_js( __( 'Upload Logo', 'nashua-pal-core' ) ); ?></button>' +
					'</td>' +
					'<td><input type="text" name="fp_name[' + idx + ']" value="" class="regular-text"></td>' +
					'<td><input type="url" name="fp_url[' + idx + ']" value="" class="regular-text" placeholder="https://"></td>' +
					'<td><button type="button" class="button pal-fp-remove-btn">&times; <?php echo esc_js( __( 'Remove', 'nashua-pal-core' ) ); ?></button></td>' +
				'</tr>';
				$('#pal-fp-rows').append(row);
				idx++;
			});

			$(document).on('click', '.pal-fp-remove-btn', function(){
				$(this).closest('tr').remove();
			});

			$(document).on('click', '.pal-fp-upload-btn', function(){
				var btn = $(this);
				var row = btn.closest('tr');
				var frame = wp.media({ title: '<?php echo esc_js( __( 'Choose Logo', 'nashua-pal-core' ) ); ?>', multiple: false });
				frame.on('select', function(){
					var attachment = frame.state().get('selection').first().toJSON();
					row.find('.pal-fp-logo-input').val(attachment.url);
					row.find('.pal-fp-logo-preview').html('<img src="' + attachment.url + '" style="max-height:60px;max-width:160px;">');
				});
				frame.open();
			});
		})(jQuery);
		</script>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Helpers                                                            */
	/* ------------------------------------------------------------------ */

	/**
	 * Get all distinct tiers that have partner list posts.
	 *
	 * @return array Tier slugs in the canonical display order.
	 */
	public static function get_tiers() {
		global $wpdb;
		$raw = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT pm.meta_value
			 FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key = %s
			   AND p.post_type = %s
			   AND p.post_status = 'publish'
			   AND pm.meta_value != ''
			 ORDER BY pm.meta_value ASC",
			'_pal_partner_list_tier',
			self::POST_TYPE
		) );

		// Return in canonical display order (not alphabetical).
		$ordered = array();
		foreach ( array_keys( self::TIERS ) as $slug ) {
			if ( in_array( $slug, $raw, true ) ) {
				$ordered[] = $slug;
			}
		}
		return $ordered;
	}
}
