<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Bright_Future_Meta {

	const NONCE = 'npal_bright_future_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Bright_Future::POST_TYPE, array( $this, 'save' ), 10, 2 );

		// Admin list columns.
		add_filter( 'manage_' . NPAL_CPT_Bright_Future::POST_TYPE . '_posts_columns',       array( $this, 'list_columns' ) );
		add_action( 'manage_' . NPAL_CPT_Bright_Future::POST_TYPE . '_posts_custom_column', array( $this, 'render_list_column' ), 10, 2 );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin list columns                                                 */
	/* ------------------------------------------------------------------ */

	public function list_columns( $cols ) {
		$new = array();
		foreach ( $cols as $k => $v ) {
			$new[ $k ] = $v;
			if ( 'title' === $k ) {
				$new['bf_role']     = __( 'Role', 'nashua-pal-core' );
				$new['bf_featured'] = __( '★ Featured', 'nashua-pal-core' );
				$new['bf_order']    = __( 'Order', 'nashua-pal-core' );
			}
		}
		return $new;
	}

	public function render_list_column( $col, $post_id ) {
		if ( 'bf_role' === $col ) {
			echo esc_html( get_post_meta( $post_id, '_bf_role', true ) );
		} elseif ( 'bf_featured' === $col ) {
			echo get_post_meta( $post_id, '_bf_featured', true ) ? '★' : '—';
		} elseif ( 'bf_order' === $col ) {
			echo esc_html( get_post_meta( $post_id, '_bf_order', true ) ?: '0' );
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Meta boxes                                                         */
	/* ------------------------------------------------------------------ */

	public function add_boxes() {
		$pt = NPAL_CPT_Bright_Future::POST_TYPE;

		add_meta_box(
			'bf_story_details',
			__( 'Story Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			$pt, 'normal', 'high'
		);

		add_meta_box(
			'bf_display_settings',
			__( 'Display Settings', 'nashua-pal-core' ),
			array( $this, 'render_display' ),
			$pt, 'side', 'default'
		);
	}

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$role      = get_post_meta( $post->ID, '_bf_role', true );
		$youtube   = get_post_meta( $post->ID, '_bf_youtube_url', true );
		$duration  = get_post_meta( $post->ID, '_bf_video_duration', true );
		$excerpt   = get_post_meta( $post->ID, '_bf_excerpt', true );
		$quote     = get_post_meta( $post->ID, '_bf_quote', true );
		$bio       = get_post_meta( $post->ID, '_bf_bio', true );
		?>
		<p style="margin:0 0 10px;color:#555;font-size:13px;">
			<?php esc_html_e( 'The video thumbnail is set via the "Featured Image" box on the right. If not set, the YouTube thumbnail will be used automatically.', 'nashua-pal-core' ); ?>
		</p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="bf_role"><?php esc_html_e( 'Role / Title', 'nashua-pal-core' ); ?></label></th>
				<td>
					<input type="text" id="bf_role" name="bf_role" class="large-text"
						value="<?php echo esc_attr( $role ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. PAL Boxer, PAL Youth Safe Haven Member', 'nashua-pal-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="bf_youtube_url"><?php esc_html_e( 'YouTube Video URL', 'nashua-pal-core' ); ?></label></th>
				<td>
					<input type="url" id="bf_youtube_url" name="bf_youtube_url" class="large-text"
						value="<?php echo esc_attr( $youtube ); ?>"
						placeholder="https://www.youtube.com/watch?v=..." />
					<p class="description"><?php esc_html_e( 'Paste the full YouTube URL. The video will play in an embedded player on the card.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="bf_video_duration"><?php esc_html_e( 'Video Duration', 'nashua-pal-core' ); ?></label></th>
				<td>
					<input type="text" id="bf_video_duration" name="bf_video_duration" class="small-text"
						value="<?php echo esc_attr( $duration ); ?>"
						placeholder="2:45" />
					<p class="description"><?php esc_html_e( 'Duration shown on the thumbnail badge (e.g. 2:45, 1:30).', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="bf_excerpt"><?php esc_html_e( 'Story Excerpt', 'nashua-pal-core' ); ?></label></th>
				<td>
					<textarea id="bf_excerpt" name="bf_excerpt" class="large-text" rows="3"
						placeholder="<?php esc_attr_e( 'Short summary shown on featured cards (2-3 sentences).', 'nashua-pal-core' ); ?>"
					><?php echo esc_textarea( $excerpt ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="bf_quote"><?php esc_html_e( 'Short Quote', 'nashua-pal-core' ); ?></label></th>
				<td>
					<textarea id="bf_quote" name="bf_quote" class="large-text" rows="2"
						placeholder="<?php esc_attr_e( '"You better come to PAL… you won\'t regret it."', 'nashua-pal-core' ); ?>"
					><?php echo esc_textarea( $quote ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Short quote shown on "More Stories" cards (include quotation marks).', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="bf_bio"><?php esc_html_e( 'Full Bio / Story', 'nashua-pal-core' ); ?></label></th>
				<td>
					<?php
					wp_editor( $bio, 'bf_bio', array(
						'textarea_name' => 'bf_bio',
						'textarea_rows' => 8,
						'media_buttons' => true,
						'teeny'         => false,
						'quicktags'     => true,
					) );
					?>
					<p class="description"><?php esc_html_e( 'Full biography shown in the popup when "Read Story" is clicked.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function render_display( $post ) {
		$order    = get_post_meta( $post->ID, '_bf_order', true );
		$featured = get_post_meta( $post->ID, '_bf_featured', true );
		?>
		<p>
			<label for="bf_featured">
				<input type="checkbox" id="bf_featured" name="bf_featured" value="1" <?php checked( $featured, '1' ); ?> />
				<?php esc_html_e( 'Featured Story (top row, larger card)', 'nashua-pal-core' ); ?>
			</label>
		</p>
		<p class="description"><?php esc_html_e( 'Featured stories show a full excerpt. Non-featured show a short quote in the "More Stories" row.', 'nashua-pal-core' ); ?></p>
		<hr />
		<p>
			<label for="bf_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label><br />
			<input type="number" id="bf_order" name="bf_order" style="width:80px;"
				value="<?php echo esc_attr( $order ); ?>" min="0" step="1" />
		</p>
		<p class="description"><?php esc_html_e( 'Lower numbers appear first. Default is 0.', 'nashua-pal-core' ); ?></p>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Save                                                               */
	/* ------------------------------------------------------------------ */

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( $_POST[ self::NONCE ], self::NONCE ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		$this->save_text( $post_id, 'bf_role', '_bf_role' );
		$this->save_text( $post_id, 'bf_youtube_url', '_bf_youtube_url' );
		$this->save_text( $post_id, 'bf_video_duration', '_bf_video_duration' );

		$excerpt = isset( $_POST['bf_excerpt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bf_excerpt'] ) ) : '';
		$this->set_or_delete( $post_id, '_bf_excerpt', $excerpt );

		$quote = isset( $_POST['bf_quote'] ) ? sanitize_textarea_field( wp_unslash( $_POST['bf_quote'] ) ) : '';
		$this->set_or_delete( $post_id, '_bf_quote', $quote );

		$bio = isset( $_POST['bf_bio'] ) ? wp_kses_post( wp_unslash( $_POST['bf_bio'] ) ) : '';
		$this->set_or_delete( $post_id, '_bf_bio', $bio );

		$featured = isset( $_POST['bf_featured'] ) ? '1' : '';
		$this->set_or_delete( $post_id, '_bf_featured', $featured );

		$order = isset( $_POST['bf_order'] ) ? absint( $_POST['bf_order'] ) : 0;
		update_post_meta( $post_id, '_bf_order', $order );
	}

	private function save_text( $post_id, $field, $meta_key ) {
		$val = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		$this->set_or_delete( $post_id, $meta_key, $val );
	}

	private function set_or_delete( $post_id, $key, $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
