<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Newsletter_Meta {

	const NONCE = 'npal_newsletter_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Newsletter::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	public function add_boxes() {
		add_meta_box(
			'pal_newsletter_details',
			__( 'Newsletter Details', 'nashua-pal-core' ),
			array( $this, 'render' ),
			NPAL_CPT_Newsletter::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );
		$url  = get_post_meta( $post->ID, '_pal_newsletter_url', true );
		$date = get_post_meta( $post->ID, '_pal_newsletter_date', true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_newsletter_url"><?php esc_html_e( 'Newsletter URL', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="url" id="pal_newsletter_url" name="pal_newsletter_url" class="large-text"
						value="<?php echo esc_attr( $url ); ?>"
						placeholder="https://myemail.constantcontact.com/..." />
					<p class="description">
						<?php esc_html_e( 'The Constant Contact (or external) URL for this newsletter issue. Clicking the card in Latest News opens this link in a new tab.', 'nashua-pal-core' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_newsletter_date"><?php esc_html_e( 'Publication Date', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="date" id="pal_newsletter_date" name="pal_newsletter_date"
						value="<?php echo esc_attr( $date ); ?>" />
					<p class="description">
						<?php esc_html_e( 'Used for sorting alongside blog posts in Latest News. Defaults to the publish date if left empty.', 'nashua-pal-core' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( $_POST[ self::NONCE ], self::NONCE ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		$url_raw = isset( $_POST['pal_newsletter_url'] ) ? wp_unslash( $_POST['pal_newsletter_url'] ) : '';
		$url     = esc_url_raw( trim( $url_raw ) );
		$this->set_or_delete( $post_id, '_pal_newsletter_url', $url );

		$date_raw = isset( $_POST['pal_newsletter_date'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_newsletter_date'] ) ) : '';
		$date     = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_raw ) ? $date_raw : '';
		$this->set_or_delete( $post_id, '_pal_newsletter_date', $date );
	}

	private function set_or_delete( $post_id, $key, $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
