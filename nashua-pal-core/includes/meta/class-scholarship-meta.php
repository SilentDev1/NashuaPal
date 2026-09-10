<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Meta boxes for Scholarship Programs.
 *
 * Meta keys:
 *  - _pal_scholarship_year   (number — the year this scholarship applies to)
 *  - _pal_scholarship_about  (textarea — description of the scholarship)
 *  - _pal_scholarship_amount (text — optional scholarship amount)
 *
 * @package NashuaPalCore
 * @since   1.37.0
 */
final class NPAL_Scholarship_Meta {

	const NONCE = 'npal_scholarship_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Scholarship::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	/* ------------------------------------------------------------------ */
	/*  Meta boxes                                                         */
	/* ------------------------------------------------------------------ */

	public function add_boxes() {
		$pt = NPAL_CPT_Scholarship::POST_TYPE;

		add_meta_box(
			'pal_scholarship_details',
			__( 'Scholarship Program Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			$pt, 'normal', 'high'
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Render                                                             */
	/* ------------------------------------------------------------------ */

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$year   = get_post_meta( $post->ID, '_pal_scholarship_year', true );
		$about  = get_post_meta( $post->ID, '_pal_scholarship_about', true );
		$amount = get_post_meta( $post->ID, '_pal_scholarship_amount', true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_scholarship_year"><?php esc_html_e( 'Year', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="number" id="pal_scholarship_year" name="pal_scholarship_year" style="width:100px;"
						value="<?php echo esc_attr( $year ); ?>"
						min="2000" max="2099" step="1"
						placeholder="<?php echo esc_attr( gmdate( 'Y' ) ); ?>" />
					<p class="description"><?php esc_html_e( 'The year this scholarship program applies to.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_scholarship_about"><?php esc_html_e( 'About this Scholarship', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<textarea id="pal_scholarship_about" name="pal_scholarship_about" rows="8" class="large-text"
						placeholder="<?php esc_attr_e( 'Describe this scholarship program...', 'nashua-pal-core' ); ?>"
					><?php echo esc_textarea( $about ); ?></textarea>
					<p class="description"><?php esc_html_e( 'This text is shown in the popup when visitors click "About this Scholarship" on a recipient card.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_scholarship_amount"><?php esc_html_e( 'Amount', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_scholarship_amount" name="pal_scholarship_amount" class="regular-text"
						value="<?php echo esc_attr( $amount ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. $2,000 (optional)', 'nashua-pal-core' ); ?>" />
				</td>
			</tr>
		</table>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Save                                                               */
	/* ------------------------------------------------------------------ */

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( $_POST[ self::NONCE ], self::NONCE ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		// Year.
		$year = isset( $_POST['pal_scholarship_year'] ) ? absint( $_POST['pal_scholarship_year'] ) : 0;
		if ( $year ) {
			update_post_meta( $post_id, '_pal_scholarship_year', (string) $year );
		} else {
			delete_post_meta( $post_id, '_pal_scholarship_year' );
		}

		// About.
		$about = isset( $_POST['pal_scholarship_about'] ) ? wp_unslash( $_POST['pal_scholarship_about'] ) : '';
		$about = sanitize_textarea_field( $about );
		if ( '' !== $about ) {
			update_post_meta( $post_id, '_pal_scholarship_about', $about );
		} else {
			delete_post_meta( $post_id, '_pal_scholarship_about' );
		}

		// Amount.
		$amount = isset( $_POST['pal_scholarship_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_scholarship_amount'] ) ) : '';
		if ( '' !== $amount ) {
			update_post_meta( $post_id, '_pal_scholarship_amount', $amount );
		} else {
			delete_post_meta( $post_id, '_pal_scholarship_amount' );
		}
	}
}
