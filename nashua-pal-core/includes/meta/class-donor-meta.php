<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Meta boxes for Donors.
 *
 * One post per fiscal year. Meta keys:
 *  - _pal_donor_fiscal_year  (number — ending year of fiscal year Oct-Sep)
 *  - _pal_donor_names        (textarea — all donor names, one per line)
 *
 * @package NashuaPalCore
 * @since   1.36.0
 */
final class NPAL_Donor_Meta {

	const NONCE = 'npal_donor_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Donor::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	/* ------------------------------------------------------------------ */
	/*  Meta boxes                                                         */
	/* ------------------------------------------------------------------ */

	public function add_boxes() {
		$pt = NPAL_CPT_Donor::POST_TYPE;

		add_meta_box(
			'pal_donor_details',
			__( 'Donor Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			$pt, 'normal', 'high'
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Donor Details                                              */
	/* ------------------------------------------------------------------ */

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$fiscal_year = get_post_meta( $post->ID, '_pal_donor_fiscal_year', true );
		$names       = get_post_meta( $post->ID, '_pal_donor_names', true );
		$current_fy  = NPAL_CPT_Donor::current_fiscal_year();

		// Count names for display.
		$name_count = 0;
		if ( $names ) {
			$name_count = count( array_filter( array_map( 'trim', explode( "\n", $names ) ) ) );
		}
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_donor_fiscal_year"><?php esc_html_e( 'Fiscal Year', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="number" id="pal_donor_fiscal_year" name="pal_donor_fiscal_year" style="width:100px;"
						value="<?php echo esc_attr( $fiscal_year ); ?>"
						min="2000" max="2099" step="1"
						placeholder="<?php echo esc_attr( $current_fy ); ?>" />
					<p class="description">
						<?php
						printf(
							esc_html__( 'Ending year of the fiscal year (Oct 1 – Sep 30). Current fiscal year: %s.', 'nashua-pal-core' ),
							'<strong>' . esc_html( $current_fy ) . '</strong>'
						);
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_donor_names"><?php esc_html_e( 'Donor Names', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<textarea id="pal_donor_names" name="pal_donor_names" rows="25" class="large-text code"
						placeholder="<?php esc_attr_e( "Paste all donor names here, one per line...\n\n603 Brewery\n7 Start Pizza & Restaurant\n92.5 The River\nAbellard Restaurant Group (603 Bistro)\nAbramson Family", 'nashua-pal-core' ); ?>"
					><?php echo esc_textarea( $names ); ?></textarea>
					<p class="description">
						<?php
						printf(
							esc_html__( 'One donor name per line. Currently %s donors listed. They will be displayed alphabetically on the front end.', 'nashua-pal-core' ),
							'<strong>' . esc_html( $name_count ) . '</strong>'
						);
						?>
					</p>
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

		// Fiscal Year.
		$fy = isset( $_POST['pal_donor_fiscal_year'] ) ? absint( $_POST['pal_donor_fiscal_year'] ) : 0;
		if ( $fy ) {
			update_post_meta( $post_id, '_pal_donor_fiscal_year', (string) $fy );
		} else {
			delete_post_meta( $post_id, '_pal_donor_fiscal_year' );
		}

		// Donor Names (preserve raw text with newlines, sanitize each line).
		$raw = isset( $_POST['pal_donor_names'] ) ? wp_unslash( $_POST['pal_donor_names'] ) : '';
		if ( '' !== trim( $raw ) ) {
			$lines = explode( "\n", $raw );
			$clean = array();
			foreach ( $lines as $line ) {
				$trimmed = trim( $line );
				if ( '' !== $trimmed ) {
					$clean[] = sanitize_text_field( $trimmed );
				}
			}
			update_post_meta( $post_id, '_pal_donor_names', implode( "\n", $clean ) );
		} else {
			delete_post_meta( $post_id, '_pal_donor_names' );
		}
	}
}
