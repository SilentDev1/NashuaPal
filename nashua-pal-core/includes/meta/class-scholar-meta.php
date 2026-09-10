<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Scholar_Meta {

	const NONCE = 'npal_scholar_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Scholar::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	public function add_boxes() {
		$pt = NPAL_CPT_Scholar::POST_TYPE;

		add_meta_box(
			'pal_scholar_details',
			__( 'Recipient Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			$pt, 'normal', 'high'
		);

		add_meta_box(
			'pal_scholar_display',
			__( 'Display Settings', 'nashua-pal-core' ),
			array( $this, 'render_display' ),
			$pt, 'side', 'default'
		);
	}

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$amount       = get_post_meta( $post->ID, '_pal_scholar_amount', true );
		$school       = get_post_meta( $post->ID, '_pal_scholar_school', true );
		$alumni       = get_post_meta( $post->ID, '_pal_scholar_alumni', true );
		$year         = get_post_meta( $post->ID, '_pal_scholar_year', true );
		$scholarship  = get_post_meta( $post->ID, '_pal_scholar_scholarship', true );

		// Get all scholarship programs for the dropdown.
		$programs = get_posts( array(
			'post_type'      => NPAL_CPT_Scholarship::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'orderby'        => 'title',
			'order'          => 'ASC',
		) );
		?>
		<p style="margin:0 0 10px;color:#555;font-size:13px;">
			<?php esc_html_e( 'The recipient photo is set via the "Featured Image" box on the right. It will be displayed as a circle on the front end.', 'nashua-pal-core' ); ?>
		</p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_scholar_scholarship"><?php esc_html_e( 'Scholarship Program', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<select id="pal_scholar_scholarship" name="pal_scholar_scholarship" style="min-width:300px;">
						<option value=""><?php esc_html_e( '— Select a Scholarship Program —', 'nashua-pal-core' ); ?></option>
						<?php foreach ( $programs as $prog ) : ?>
							<option value="<?php echo esc_attr( $prog->ID ); ?>" <?php selected( $scholarship, $prog->ID ); ?>>
								<?php
								$prog_year = get_post_meta( $prog->ID, '_pal_scholarship_year', true );
								echo esc_html( $prog->post_title );
								if ( $prog_year ) {
									echo ' (' . esc_html( $prog_year ) . ')';
								}
								?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Link this recipient to a scholarship program. Programs can be managed under Scholarships → All Programs.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_scholar_amount"><?php esc_html_e( 'Scholarship Amount', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_scholar_amount" name="pal_scholar_amount" class="regular-text"
						value="<?php echo esc_attr( $amount ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. $1,500', 'nashua-pal-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_scholar_school"><?php esc_html_e( 'School', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_scholar_school" name="pal_scholar_school" class="regular-text"
						value="<?php echo esc_attr( $school ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. Nashua High School North', 'nashua-pal-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_scholar_alumni"><?php esc_html_e( 'PAL Alumni', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_scholar_alumni" name="pal_scholar_alumni" class="regular-text"
						value="<?php echo esc_attr( $alumni ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. Nashua PAL Storm Alumni', 'nashua-pal-core' ); ?>" />
					<p class="description"><?php esc_html_e( 'If this recipient is a PAL alumni, enter the program name (e.g. "Nashua PAL Storm Alumni", "Nashua PAL Boxing & Youth Safe Haven Alumni").', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_scholar_year"><?php esc_html_e( 'Scholarship Year', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="number" id="pal_scholar_year" name="pal_scholar_year" style="width:100px;"
						value="<?php echo esc_attr( $year ); ?>"
						min="2000" max="2099" step="1"
						placeholder="<?php echo esc_attr( gmdate( 'Y' ) ); ?>" />
					<p class="description"><?php esc_html_e( 'The year this scholarship was awarded. Recipients are grouped and sorted by year on the front end.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_scholar_bio"><?php esc_html_e( 'Bio', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<textarea id="pal_scholar_bio" name="pal_scholar_bio" rows="5" class="large-text"
						placeholder="<?php esc_attr_e( 'Brief bio about this recipient — displayed in a popup when visitors click their profile photo.', 'nashua-pal-core' ); ?>"><?php echo esc_textarea( get_post_meta( $post->ID, '_pal_scholar_bio', true ) ); ?></textarea>
				</td>
			</tr>
		</table>
		<?php
	}

	public function render_display( $post ) {
		$order = get_post_meta( $post->ID, '_pal_scholar_order', true );
		?>
		<p>
			<label for="pal_scholar_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label><br />
			<input type="number" id="pal_scholar_order" name="pal_scholar_order" style="width:80px;"
				value="<?php echo esc_attr( $order ); ?>" min="0" step="1" />
		</p>
		<p class="description"><?php esc_html_e( 'Lower numbers appear first within their year group. Default is 0.', 'nashua-pal-core' ); ?></p>
		<?php
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( $_POST[ self::NONCE ], self::NONCE ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		$scholarship = isset( $_POST['pal_scholar_scholarship'] ) ? absint( $_POST['pal_scholar_scholarship'] ) : 0;
		$this->set_or_delete( $post_id, '_pal_scholar_scholarship', $scholarship ? (string) $scholarship : '' );

		$amount = isset( $_POST['pal_scholar_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_scholar_amount'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_scholar_amount', $amount );

		$school = isset( $_POST['pal_scholar_school'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_scholar_school'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_scholar_school', $school );

		$alumni = isset( $_POST['pal_scholar_alumni'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_scholar_alumni'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_scholar_alumni', $alumni );

		$year = isset( $_POST['pal_scholar_year'] ) ? absint( $_POST['pal_scholar_year'] ) : 0;
		$this->set_or_delete( $post_id, '_pal_scholar_year', $year ? (string) $year : '' );

		$bio = isset( $_POST['pal_scholar_bio'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pal_scholar_bio'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_scholar_bio', $bio );

		$order = isset( $_POST['pal_scholar_order'] ) ? absint( $_POST['pal_scholar_order'] ) : 0;
		update_post_meta( $post_id, '_pal_scholar_order', $order );
	}

	private function set_or_delete( $post_id, $key, $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
