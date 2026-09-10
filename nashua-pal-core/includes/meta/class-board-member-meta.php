<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Board_Member_Meta {

	const NONCE = 'npal_board_member_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Board_Member::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	public function add_boxes() {
		$pt = NPAL_CPT_Board_Member::POST_TYPE;

		add_meta_box(
			'pal_people_details',
			__( 'Board Member Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			$pt, 'normal', 'high'
		);

		add_meta_box(
			'pal_people_display',
			__( 'Display Settings', 'nashua-pal-core' ),
			array( $this, 'render_display' ),
			$pt, 'side', 'default'
		);
	}

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$role  = get_post_meta( $post->ID, '_pal_people_role', true );
		$org   = get_post_meta( $post->ID, '_pal_people_organization', true );
		$email = get_post_meta( $post->ID, '_pal_people_email', true );
		$phone = get_post_meta( $post->ID, '_pal_people_phone', true );
		$bio   = get_post_meta( $post->ID, '_pal_people_bio', true );
		?>
		<p style="margin:0 0 10px;color:#555;font-size:13px;">
			<?php esc_html_e( 'The member photo is set via the "Featured Image" box on the right.', 'nashua-pal-core' ); ?>
		</p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="pal_people_role"><?php esc_html_e( 'Title / Role', 'nashua-pal-core' ); ?></label></th>
				<td>
					<input type="text" id="pal_people_role" name="pal_people_role" class="large-text"
						value="<?php echo esc_attr( $role ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. President, Vice President, Treasurer', 'nashua-pal-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="pal_people_organization"><?php esc_html_e( 'Organization', 'nashua-pal-core' ); ?></label></th>
				<td>
					<input type="text" id="pal_people_organization" name="pal_people_organization" class="regular-text"
						value="<?php echo esc_attr( $org ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. Nashua Police Athletic League', 'nashua-pal-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="pal_people_email"><?php esc_html_e( 'Email', 'nashua-pal-core' ); ?></label></th>
				<td>
					<input type="email" id="pal_people_email" name="pal_people_email" class="regular-text"
						value="<?php echo esc_attr( $email ); ?>" placeholder="email@example.com" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="pal_people_phone"><?php esc_html_e( 'Phone', 'nashua-pal-core' ); ?></label></th>
				<td>
					<input type="tel" id="pal_people_phone" name="pal_people_phone" class="regular-text"
						value="<?php echo esc_attr( $phone ); ?>" placeholder="(603) 555-0100" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="pal_people_bio"><?php esc_html_e( 'Bio', 'nashua-pal-core' ); ?></label></th>
				<td>
					<textarea id="pal_people_bio" name="pal_people_bio" class="large-text" rows="4"
						placeholder="<?php esc_attr_e( 'Short biography shown on the profile page.', 'nashua-pal-core' ); ?>"
					><?php echo esc_textarea( $bio ); ?></textarea>
				</td>
			</tr>
		</table>
		<?php
	}

	public function render_display( $post ) {
		$order = get_post_meta( $post->ID, '_pal_people_order', true );
		?>
		<p>
			<label for="pal_people_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label><br />
			<input type="number" id="pal_people_order" name="pal_people_order" style="width:80px;"
				value="<?php echo esc_attr( $order ); ?>" min="0" step="1" />
		</p>
		<p class="description"><?php esc_html_e( 'Lower numbers appear first. Default is 0.', 'nashua-pal-core' ); ?></p>
		<?php
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( $_POST[ self::NONCE ], self::NONCE ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		$this->save_text( $post_id, 'pal_people_role', '_pal_people_role' );
		$this->save_text( $post_id, 'pal_people_organization', '_pal_people_organization' );
		$this->save_text( $post_id, 'pal_people_email', '_pal_people_email' );
		$this->save_text( $post_id, 'pal_people_phone', '_pal_people_phone' );

		$bio = isset( $_POST['pal_people_bio'] ) ? wp_kses_post( wp_unslash( $_POST['pal_people_bio'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_people_bio', $bio );

		$order = isset( $_POST['pal_people_order'] ) ? absint( $_POST['pal_people_order'] ) : 0;
		update_post_meta( $post_id, '_pal_people_order', $order );
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
