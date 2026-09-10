<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Meta boxes for the shared pal_sponsor CPT.
 *
 * @package NashuaPalCore
 * @since   1.23.0
 */
final class NPAL_Sponsor_Meta {

	const NONCE = 'npal_sponsor_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Sponsor::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	/* ================================================================== */
	/*  Level helpers                                                      */
	/* ================================================================== */

	public static function level_options() {
		$options = array( '' => __( '— Select Level —', 'nashua-pal-core' ) );
		$terms   = NPAL_CPT_Sponsor::get_ordered_levels();
		foreach ( $terms as $term ) {
			$options[ $term->slug ] = $term->name;
		}
		return $options;
	}

	/* ================================================================== */
	/*  Meta boxes                                                         */
	/* ================================================================== */

	public function add_boxes() {
		add_meta_box(
			'pal_sponsor_details',
			__( 'Sponsor Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			NPAL_CPT_Sponsor::POST_TYPE,
			'normal', 'high'
		);

		add_meta_box(
			'pal_sponsor_display',
			__( 'Display Settings', 'nashua-pal-core' ),
			array( $this, 'render_display' ),
			NPAL_CPT_Sponsor::POST_TYPE,
			'side', 'default'
		);
	}

	/* ================================================================== */
	/*  Render: Details                                                    */
	/* ================================================================== */

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$context = get_post_meta( $post->ID, '_pal_sponsor_context', true );
		$year    = get_post_meta( $post->ID, '_pal_sponsor_year', true );
		$url     = get_post_meta( $post->ID, '_pal_sponsor_url', true );

		// Auto-default context for new posts from URL param.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $context && isset( $_GET['pal_context'] ) ) {
			$context = sanitize_key( $_GET['pal_context'] );
		}
		if ( ! $context ) { $context = 'event'; }

		$current_term = NPAL_CPT_Sponsor::get_sponsor_level( $post->ID );
		$current_slug = $current_term ? $current_term->slug : '';
		$level_options = self::level_options();
		?>
		<p style="margin:0 0 10px;color:#555;font-size:13px;">
			<?php esc_html_e( 'Set the sponsor logo via the "Featured Image" box on the right. Use a transparent PNG for best results.', 'nashua-pal-core' ); ?>
		</p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_sponsor_context"><?php esc_html_e( 'Context', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<select id="pal_sponsor_context" name="pal_sponsor_context" style="min-width:160px;">
						<option value="event" <?php selected( $context, 'event' ); ?>><?php esc_html_e( 'Event', 'nashua-pal-core' ); ?></option>
						<option value="program" <?php selected( $context, 'program' ); ?>><?php esc_html_e( 'Program', 'nashua-pal-core' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Is this sponsor for an Event or a Program?', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_sponsor_year"><?php esc_html_e( 'Sponsor Year', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="number" id="pal_sponsor_year" name="pal_sponsor_year" style="width:100px;"
						value="<?php echo esc_attr( $year ); ?>"
						min="2000" max="2099" step="1"
						placeholder="<?php echo esc_attr( gmdate( 'Y' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_sponsor_level"><?php esc_html_e( 'Sponsor Level', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<select id="pal_sponsor_level" name="pal_sponsor_level" style="min-width:240px;">
						<?php foreach ( $level_options as $val => $label ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_slug, $val ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php
						$tax_url = admin_url( 'edit-tags.php?taxonomy=' . NPAL_CPT_Sponsor::TAXONOMY . '&post_type=' . NPAL_CPT_Sponsor::POST_TYPE );
						printf(
							esc_html__( 'Select a level or %s.', 'nashua-pal-core' ),
							'<a href="' . esc_url( $tax_url ) . '">' . esc_html__( 'manage sponsor levels', 'nashua-pal-core' ) . '</a>'
						);
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_sponsor_url"><?php esc_html_e( 'Sponsor Website', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="url" id="pal_sponsor_url" name="pal_sponsor_url" class="regular-text"
						value="<?php echo esc_attr( $url ); ?>"
						placeholder="https://" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Linked Events / Programs', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<?php
					$linked_raw = get_post_meta( $post->ID, '_pal_sponsor_linked_posts', true );
					$linked_ids = is_array( $linked_raw ) ? array_map( 'absint', $linked_raw ) : array();
					$linked_lookup = array_flip( $linked_ids );

					// Fetch available posts based on context.
					$target_type = 'event' === $context ? NPAL_CPT_Event::POST_TYPE : NPAL_CPT_Program::POST_TYPE;
					$target_label = 'event' === $context ? __( 'events', 'nashua-pal-core' ) : __( 'programs', 'nashua-pal-core' );
					$available = get_posts( array(
						'post_type'      => $target_type,
						'post_status'    => array( 'publish', 'draft', 'future' ),
						'posts_per_page' => -1,
						'orderby'        => 'title',
						'order'          => 'ASC',
					) );

					if ( ! empty( $available ) ) :
					?>
						<div style="max-height:200px;overflow-y:auto;border:1px solid #dcdcde;border-radius:6px;padding:8px;background:#fafafa;">
							<?php foreach ( $available as $ap ) :
								$is_checked = isset( $linked_lookup[ $ap->ID ] );
								$status_badge = '';
								if ( 'draft' === $ap->post_status ) { $status_badge = ' <span style="color:#d63638;font-size:10px;">[Draft]</span>'; }
								if ( 'future' === $ap->post_status ) { $status_badge = ' <span style="color:#2271b1;font-size:10px;">[Scheduled]</span>'; }
								?>
								<label style="display:block;padding:4px 6px;cursor:pointer;font-size:13px;">
									<input type="checkbox" name="pal_sponsor_linked_posts[]" value="<?php echo (int) $ap->ID; ?>" <?php checked( $is_checked ); ?> style="margin-right:6px;" />
									<?php echo esc_html( $ap->post_title ); echo $status_badge; ?>
								</label>
							<?php endforeach; ?>
						</div>
						<p class="description"><?php printf( esc_html__( 'Select which %s this sponsor is for.', 'nashua-pal-core' ), esc_html( $target_label ) ); ?></p>
					<?php else : ?>
						<p class="description"><?php printf( esc_html__( 'No %s found. Create some first.', 'nashua-pal-core' ), esc_html( $target_label ) ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/* ================================================================== */
	/*  Render: Display Settings (sidebar)                                 */
	/* ================================================================== */

	public function render_display( $post ) {
		$order = get_post_meta( $post->ID, '_pal_sponsor_order', true );
		?>
		<p>
			<label for="pal_sponsor_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label><br />
			<input type="number" id="pal_sponsor_order" name="pal_sponsor_order" style="width:80px;"
				value="<?php echo esc_attr( $order ); ?>" min="0" step="1" />
		</p>
		<p class="description"><?php esc_html_e( 'Lower numbers appear first within the same level.', 'nashua-pal-core' ); ?></p>
		<?php
	}

	/* ================================================================== */
	/*  Save                                                               */
	/* ================================================================== */

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( $_POST[ self::NONCE ], self::NONCE ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		// Context.
		$context = isset( $_POST['pal_sponsor_context'] ) ? sanitize_key( $_POST['pal_sponsor_context'] ) : 'event';
		if ( ! in_array( $context, array( 'event', 'program' ), true ) ) { $context = 'event'; }
		update_post_meta( $post_id, '_pal_sponsor_context', $context );

		// Year.
		$year = isset( $_POST['pal_sponsor_year'] ) ? absint( $_POST['pal_sponsor_year'] ) : 0;
		$this->set_or_delete( $post_id, '_pal_sponsor_year', $year ? (string) $year : '' );

		// Level — set taxonomy term.
		$level_slug = isset( $_POST['pal_sponsor_level'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_sponsor_level'] ) ) : '';
		if ( $level_slug ) {
			wp_set_object_terms( $post_id, $level_slug, NPAL_CPT_Sponsor::TAXONOMY );
		} else {
			wp_set_object_terms( $post_id, array(), NPAL_CPT_Sponsor::TAXONOMY );
		}

		// Website URL.
		$url_raw = isset( $_POST['pal_sponsor_url'] ) ? wp_unslash( $_POST['pal_sponsor_url'] ) : '';
		$this->set_or_delete( $post_id, '_pal_sponsor_url', esc_url_raw( trim( $url_raw ) ) );

		// Display Order.
		$order = isset( $_POST['pal_sponsor_order'] ) ? absint( $_POST['pal_sponsor_order'] ) : 0;
		update_post_meta( $post_id, '_pal_sponsor_order', $order );

		// Linked Events/Programs.
		$linked_raw = isset( $_POST['pal_sponsor_linked_posts'] ) ? (array) $_POST['pal_sponsor_linked_posts'] : array();
		$linked_ids = array_values( array_unique( array_filter( array_map( 'absint', $linked_raw ) ) ) );

		// Get previous linked posts so we can compute adds/removes.
		$old_linked = get_post_meta( $post_id, '_pal_sponsor_linked_posts', true );
		$old_linked = is_array( $old_linked ) ? array_map( 'absint', $old_linked ) : array();

		// Store on sponsor side.
		if ( ! empty( $linked_ids ) ) {
			update_post_meta( $post_id, '_pal_sponsor_linked_posts', $linked_ids );
		} else {
			delete_post_meta( $post_id, '_pal_sponsor_linked_posts' );
		}

		// Sync reverse: update the _pal_{context}_linked_sponsors meta on each event/program.
		$meta_key = 'event' === $context ? '_pal_event_linked_sponsors' : '_pal_program_linked_sponsors';

		// Remove this sponsor from posts it was unlinked from.
		$removed = array_diff( $old_linked, $linked_ids );
		foreach ( $removed as $rid ) {
			$existing = get_post_meta( $rid, $meta_key, true );
			if ( is_array( $existing ) ) {
				$existing = array_values( array_diff( $existing, array( $post_id ) ) );
				if ( ! empty( $existing ) ) {
					update_post_meta( $rid, $meta_key, $existing );
				} else {
					delete_post_meta( $rid, $meta_key );
				}
			}
		}

		// Add this sponsor to newly linked posts.
		$added = array_diff( $linked_ids, $old_linked );
		foreach ( $added as $aid ) {
			$existing = get_post_meta( $aid, $meta_key, true );
			$existing = is_array( $existing ) ? $existing : array();
			if ( ! in_array( $post_id, $existing, true ) ) {
				$existing[] = $post_id;
				update_post_meta( $aid, $meta_key, array_values( $existing ) );
			}
		}
	}

	private function set_or_delete( $post_id, $key, $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
