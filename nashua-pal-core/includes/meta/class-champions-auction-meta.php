<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Champions_Auction_Meta {

	const NONCE = 'npal_champion_auction_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Champions_Auction::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	/* ------------------------------------------------------------------ */
	/*  Meta boxes                                                         */
	/* ------------------------------------------------------------------ */

	public function add_boxes() {
		$pt = NPAL_CPT_Champions_Auction::POST_TYPE;

		add_meta_box(
			'pal_auction_details',
			__( 'Package Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			$pt, 'normal', 'high'
		);

		add_meta_box(
			'pal_auction_display',
			__( 'Display Settings', 'nashua-pal-core' ),
			array( $this, 'render_display' ),
			$pt, 'side', 'default'
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Package Details                                            */
	/* ------------------------------------------------------------------ */

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$value = get_post_meta( $post->ID, '_pal_auction_value', true );
		$year  = get_post_meta( $post->ID, '_pal_auction_year', true );
		?>
		<p style="margin:0 0 10px;color:#555;font-size:13px;">
			<?php esc_html_e( 'The package image is set via the standard "Featured Image" box on the right. Use the main editor above for the package description.', 'nashua-pal-core' ); ?>
		</p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_auction_value"><?php esc_html_e( 'Package Value', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_auction_value" name="pal_auction_value" class="regular-text"
						value="<?php echo esc_attr( $value ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. $2,500 Value!', 'nashua-pal-core' ); ?>" />
					<p class="description"><?php esc_html_e( 'Displayed beneath the package title on the front end.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_auction_year"><?php esc_html_e( 'Gala Year', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="number" id="pal_auction_year" name="pal_auction_year" style="width:100px;"
						value="<?php echo esc_attr( $year ); ?>"
						min="2000" max="2099" step="1"
						placeholder="<?php echo esc_attr( gmdate( 'Y' ) ); ?>" />
					<p class="description"><?php esc_html_e( 'Filter packages by gala year on the front end.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Display Settings (sidebar)                                 */
	/* ------------------------------------------------------------------ */

	public function render_display( $post ) {
		$featured = get_post_meta( $post->ID, '_pal_auction_featured', true );
		$order    = get_post_meta( $post->ID, '_pal_auction_order', true );
		?>
		<p>
			<label>
				<input type="checkbox" name="pal_auction_featured" value="1" <?php checked( $featured, '1' ); ?> />
				<?php esc_html_e( 'Featured Package', 'nashua-pal-core' ); ?>
			</label>
		</p>
		<p class="description" style="margin:0 0 14px;"><?php esc_html_e( 'Featured packages are shown in the highlighted grid at the top of the Live Auction page.', 'nashua-pal-core' ); ?></p>

		<p>
			<label for="pal_auction_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label><br />
			<input type="number" id="pal_auction_order" name="pal_auction_order" style="width:80px;"
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

		// Value.
		$value = isset( $_POST['pal_auction_value'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_auction_value'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_auction_value', $value );

		// Year.
		$year = isset( $_POST['pal_auction_year'] ) ? absint( $_POST['pal_auction_year'] ) : 0;
		$this->set_or_delete( $post_id, '_pal_auction_year', $year ? (string) $year : '' );

		// Featured.
		$featured = ! empty( $_POST['pal_auction_featured'] ) ? '1' : '';
		$this->set_or_delete( $post_id, '_pal_auction_featured', $featured );

		// Order.
		$order = isset( $_POST['pal_auction_order'] ) ? absint( $_POST['pal_auction_order'] ) : 0;
		update_post_meta( $post_id, '_pal_auction_order', $order );
	}

	private function set_or_delete( $post_id, $key, $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
