<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Meta boxes for Cone with a Cop sponsors.
 *
 * @package NashuaPalCore
 * @since   1.26.0
 */
final class NPAL_CWAC_Sponsor_Meta {

	const NONCE = 'npal_cwac_sponsor_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_CWAC_Sponsor::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	/* ------------------------------------------------------------------ */
	/*  Sponsor level helpers                                              */
	/* ------------------------------------------------------------------ */

	public static function level_options() {
		$options = array( '' => __( '— Select Level —', 'nashua-pal-core' ) );
		$terms   = NPAL_CPT_CWAC_Sponsor::get_ordered_levels();
		foreach ( $terms as $term ) {
			$options[ $term->slug ] = $term->name;
		}
		return $options;
	}

	/* ------------------------------------------------------------------ */
	/*  Meta boxes                                                         */
	/* ------------------------------------------------------------------ */

	public function add_boxes() {
		add_meta_box(
			'pal_cwac_sponsor_details',
			__( 'Sponsor Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			NPAL_CPT_CWAC_Sponsor::POST_TYPE,
			'normal', 'high'
		);

		add_meta_box(
			'pal_cwac_sponsor_display',
			__( 'Display Settings', 'nashua-pal-core' ),
			array( $this, 'render_display' ),
			NPAL_CPT_CWAC_Sponsor::POST_TYPE,
			'side', 'default'
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Details                                                    */
	/* ------------------------------------------------------------------ */

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$year = get_post_meta( $post->ID, '_pal_cwac_sponsor_year', true );
		$url  = get_post_meta( $post->ID, '_pal_cwac_sponsor_url', true );

		$current_term = NPAL_CPT_CWAC_Sponsor::get_sponsor_level( $post->ID );
		$current_slug = $current_term ? $current_term->slug : '';

		$level_options = self::level_options();
		?>
		<p style="margin:0 0 10px;color:#555;font-size:13px;">
			<?php esc_html_e( 'Set the sponsor logo via the "Featured Image" box on the right. Use a transparent PNG for best results.', 'nashua-pal-core' ); ?>
		</p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_cwac_sponsor_year"><?php esc_html_e( 'Sponsor Year', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="number" id="pal_cwac_sponsor_year" name="pal_cwac_sponsor_year" style="width:100px;"
						value="<?php echo esc_attr( $year ); ?>"
						min="2000" max="2099" step="1"
						placeholder="<?php echo esc_attr( gmdate( 'Y' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_cwac_sponsor_level"><?php esc_html_e( 'Sponsor Level', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<select id="pal_cwac_sponsor_level" name="pal_cwac_sponsor_level" style="min-width:240px;">
						<?php foreach ( $level_options as $val => $label ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_slug, $val ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php
						$tax_url = admin_url( 'edit-tags.php?taxonomy=' . NPAL_CPT_CWAC_Sponsor::TAXONOMY . '&post_type=' . NPAL_CPT_CWAC_Sponsor::POST_TYPE );
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
					<label for="pal_cwac_sponsor_url"><?php esc_html_e( 'Sponsor Website', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="url" id="pal_cwac_sponsor_url" name="pal_cwac_sponsor_url" class="regular-text"
						value="<?php echo esc_attr( $url ); ?>"
						placeholder="https://" />
				</td>
			</tr>
		</table>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Display Settings (sidebar)                                 */
	/* ------------------------------------------------------------------ */

	public function render_display( $post ) {
		$order = get_post_meta( $post->ID, '_pal_cwac_sponsor_order', true );
		?>
		<p>
			<label for="pal_cwac_sponsor_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label><br />
			<input type="number" id="pal_cwac_sponsor_order" name="pal_cwac_sponsor_order" style="width:80px;"
				value="<?php echo esc_attr( $order ); ?>" min="0" step="1" />
		</p>
		<p class="description"><?php esc_html_e( 'Lower numbers appear first within the same level.', 'nashua-pal-core' ); ?></p>
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
		$year = isset( $_POST['pal_cwac_sponsor_year'] ) ? absint( $_POST['pal_cwac_sponsor_year'] ) : 0;
		$this->set_or_delete( $post_id, '_pal_cwac_sponsor_year', $year ? (string) $year : '' );

		// Level — set taxonomy term.
		$level_slug = isset( $_POST['pal_cwac_sponsor_level'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_cwac_sponsor_level'] ) ) : '';
		if ( $level_slug ) {
			wp_set_object_terms( $post_id, $level_slug, NPAL_CPT_CWAC_Sponsor::TAXONOMY );
		} else {
			wp_set_object_terms( $post_id, array(), NPAL_CPT_CWAC_Sponsor::TAXONOMY );
		}

		// Website URL.
		$url_raw = isset( $_POST['pal_cwac_sponsor_url'] ) ? wp_unslash( $_POST['pal_cwac_sponsor_url'] ) : '';
		$this->set_or_delete( $post_id, '_pal_cwac_sponsor_url', esc_url_raw( trim( $url_raw ) ) );

		// Display Order.
		$order = isset( $_POST['pal_cwac_sponsor_order'] ) ? absint( $_POST['pal_cwac_sponsor_order'] ) : 0;
		update_post_meta( $post_id, '_pal_cwac_sponsor_order', $order );
	}

	private function set_or_delete( $post_id, $key, $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
