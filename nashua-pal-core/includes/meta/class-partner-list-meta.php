<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Meta boxes for Partner List.
 *
 * One post per tier. Meta keys:
 *  - _pal_partner_list_tier   (slug — one of the TIERS constants)
 *  - _pal_partner_list_names  (textarea — all partner names, one per line)
 *
 * @package NashuaPalCore
 * @since   1.40.0
 */
final class NPAL_Partner_List_Meta {

	const NONCE = 'npal_partner_list_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Partner_List::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	/* ------------------------------------------------------------------ */
	/*  Meta boxes                                                         */
	/* ------------------------------------------------------------------ */

	public function add_boxes() {
		$pt = NPAL_CPT_Partner_List::POST_TYPE;

		add_meta_box(
			'pal_partner_list_details',
			__( 'Partner Tier Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			$pt, 'normal', 'high'
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Partner Tier Details                                        */
	/* ------------------------------------------------------------------ */

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$tier  = get_post_meta( $post->ID, '_pal_partner_list_tier', true );
		$names = get_post_meta( $post->ID, '_pal_partner_list_names', true );

		// Count names for display.
		$name_count = 0;
		if ( $names ) {
			$name_count = count( array_filter( array_map( 'trim', explode( "\n", $names ) ) ) );
		}
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_partner_list_tier"><?php esc_html_e( 'Partner Tier', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<select id="pal_partner_list_tier" name="pal_partner_list_tier" style="min-width:250px;">
						<option value=""><?php esc_html_e( '— Select a Tier —', 'nashua-pal-core' ); ?></option>
						<?php foreach ( NPAL_CPT_Partner_List::TIERS as $slug => $label ) : ?>
							<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $tier, $slug ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php esc_html_e( 'Select the tier level for this group of partners.', 'nashua-pal-core' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_partner_list_names"><?php esc_html_e( 'Partner Names', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<textarea id="pal_partner_list_names" name="pal_partner_list_names" rows="25" class="large-text code"
						placeholder="<?php esc_attr_e( "Paste all partner names here, one per line...\n\nAcme Corporation\nSmith & Associates\nDowntown Business Group", 'nashua-pal-core' ); ?>"
					><?php echo esc_textarea( $names ); ?></textarea>
					<p class="description">
						<?php
						printf(
							esc_html__( 'One partner name per line. Currently %s partners listed. They will be displayed alphabetically on the front end.', 'nashua-pal-core' ),
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

		// Tier.
		$tier = isset( $_POST['pal_partner_list_tier'] ) ? sanitize_key( $_POST['pal_partner_list_tier'] ) : '';
		if ( $tier && isset( NPAL_CPT_Partner_List::TIERS[ $tier ] ) ) {
			update_post_meta( $post_id, '_pal_partner_list_tier', $tier );
		} else {
			delete_post_meta( $post_id, '_pal_partner_list_tier' );
		}

		// Partner Names (preserve raw text with newlines, sanitize each line).
		$raw = isset( $_POST['pal_partner_list_names'] ) ? wp_unslash( $_POST['pal_partner_list_names'] ) : '';
		if ( '' !== trim( $raw ) ) {
			$lines = explode( "\n", $raw );
			$clean = array();
			foreach ( $lines as $line ) {
				$trimmed = trim( $line );
				if ( '' !== $trimmed ) {
					$clean[] = sanitize_text_field( $trimmed );
				}
			}
			update_post_meta( $post_id, '_pal_partner_list_names', implode( "\n", $clean ) );
		} else {
			delete_post_meta( $post_id, '_pal_partner_list_names' );
		}
	}
}
