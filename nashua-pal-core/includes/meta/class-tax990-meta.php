<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Meta boxes for Tax 990 Documents.
 *
 * @package NashuaPalCore
 * @since   1.40.1
 */
final class NPAL_Tax990_Meta {

	const NONCE = 'npal_tax990_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Tax990::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/* ------------------------------------------------------------------ */
	/*  Admin scripts (media uploader for PDF)                             */
	/* ------------------------------------------------------------------ */

	public function admin_scripts( $hook ) {
		global $post_type;
		if ( NPAL_CPT_Tax990::POST_TYPE !== $post_type ) { return; }
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
		wp_enqueue_media();
	}

	/* ------------------------------------------------------------------ */
	/*  Meta boxes                                                         */
	/* ------------------------------------------------------------------ */

	public function add_boxes() {
		$pt = NPAL_CPT_Tax990::POST_TYPE;

		add_meta_box(
			'pal_tax990_details',
			__( 'Document Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			$pt, 'normal', 'high'
		);

		add_meta_box(
			'pal_tax990_display',
			__( 'Display Settings', 'nashua-pal-core' ),
			array( $this, 'render_display' ),
			$pt, 'side', 'default'
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Document Details                                           */
	/* ------------------------------------------------------------------ */

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$year        = get_post_meta( $post->ID, '_pal_tax990_year', true );
		$pdf_id      = get_post_meta( $post->ID, '_pal_tax990_pdf_id', true );
		$description = get_post_meta( $post->ID, '_pal_tax990_description', true );

		$pdf_url      = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
		$pdf_filename = $pdf_id ? basename( get_attached_file( $pdf_id ) ) : '';
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_tax990_year"><?php esc_html_e( 'Tax Year', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="number" id="pal_tax990_year" name="pal_tax990_year" style="width:100px;"
						value="<?php echo esc_attr( $year ); ?>"
						min="2000" max="2099" step="1"
						placeholder="<?php echo esc_attr( gmdate( 'Y' ) ); ?>" />
					<p class="description"><?php esc_html_e( 'The tax year of this Form 990 filing. Used for filtering.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'PDF File', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<div id="pal-tax990-pdf-preview" style="margin-bottom:10px;<?php echo $pdf_id ? '' : 'display:none;'; ?>">
						<span class="dashicons dashicons-pdf" style="color:#c33;font-size:24px;vertical-align:middle;margin-right:6px;"></span>
						<a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener" id="pal-tax990-pdf-link">
							<?php echo esc_html( $pdf_filename ); ?>
						</a>
						<button type="button" id="pal-tax990-pdf-remove" class="button-link" style="color:#b32d2e;margin-left:12px;">
							<span class="dashicons dashicons-dismiss" style="vertical-align:middle;"></span> <?php esc_html_e( 'Remove', 'nashua-pal-core' ); ?>
						</button>
					</div>
					<input type="hidden" id="pal_tax990_pdf_id" name="pal_tax990_pdf_id"
						value="<?php echo esc_attr( $pdf_id ); ?>" />
					<button type="button" id="pal-tax990-pdf-upload" class="button">
						<span class="dashicons dashicons-upload" style="vertical-align:middle;margin-right:4px;"></span>
						<?php echo $pdf_id ? esc_html__( 'Replace PDF', 'nashua-pal-core' ) : esc_html__( 'Upload PDF', 'nashua-pal-core' ); ?>
					</button>
					<p class="description"><?php esc_html_e( 'Upload the Form 990 PDF. Visitors can view and download this file.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_tax990_description"><?php esc_html_e( 'Short Description', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<textarea id="pal_tax990_description" name="pal_tax990_description" class="large-text" rows="3"
						placeholder="<?php esc_attr_e( 'Optional description shown on the document card.', 'nashua-pal-core' ); ?>"
					><?php echo esc_textarea( $description ); ?></textarea>
				</td>
			</tr>
		</table>

		<script>
		jQuery(function($){
			var $input   = $('#pal_tax990_pdf_id'),
				$preview = $('#pal-tax990-pdf-preview'),
				$link    = $('#pal-tax990-pdf-link'),
				$btn     = $('#pal-tax990-pdf-upload');

			// Upload / replace.
			$btn.on('click', function(e){
				e.preventDefault();
				var frame = wp.media({
					title: '<?php echo esc_js( __( 'Select PDF File', 'nashua-pal-core' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Use This PDF', 'nashua-pal-core' ) ); ?>' },
					multiple: false,
					library: { type: 'application/pdf' }
				});
				frame.on('select', function(){
					var attachment = frame.state().get('selection').first().toJSON();
					$input.val( attachment.id );
					$link.attr('href', attachment.url).text( attachment.filename );
					$preview.show();
					$btn.text('<?php echo esc_js( __( 'Replace PDF', 'nashua-pal-core' ) ); ?>');
				});
				frame.open();
			});

			// Remove.
			$('#pal-tax990-pdf-remove').on('click', function(e){
				e.preventDefault();
				$input.val('');
				$preview.hide();
				$btn.html('<span class="dashicons dashicons-upload" style="vertical-align:middle;margin-right:4px;"></span> <?php echo esc_js( __( 'Upload PDF', 'nashua-pal-core' ) ); ?>');
			});
		});
		</script>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Display Settings (sidebar)                                 */
	/* ------------------------------------------------------------------ */

	public function render_display( $post ) {
		$order = get_post_meta( $post->ID, '_pal_tax990_order', true );
		?>
		<p>
			<label for="pal_tax990_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label><br />
			<input type="number" id="pal_tax990_order" name="pal_tax990_order" style="width:80px;"
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

		$year = isset( $_POST['pal_tax990_year'] ) ? absint( $_POST['pal_tax990_year'] ) : 0;
		$this->set_or_delete( $post_id, '_pal_tax990_year', $year ? (string) $year : '' );

		$pdf_id = isset( $_POST['pal_tax990_pdf_id'] ) ? absint( $_POST['pal_tax990_pdf_id'] ) : 0;
		$this->set_or_delete( $post_id, '_pal_tax990_pdf_id', $pdf_id ? (string) $pdf_id : '' );

		$desc = isset( $_POST['pal_tax990_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pal_tax990_description'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_tax990_description', $desc );

		$order = isset( $_POST['pal_tax990_order'] ) ? absint( $_POST['pal_tax990_order'] ) : 0;
		update_post_meta( $post_id, '_pal_tax990_order', $order );
	}

	/* ------------------------------------------------------------------ */
	/*  Helpers                                                            */
	/* ------------------------------------------------------------------ */

	private function set_or_delete( $post_id, $key, $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
