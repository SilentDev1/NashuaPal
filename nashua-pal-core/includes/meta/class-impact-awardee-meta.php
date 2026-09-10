<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Meta boxes for Impact Awardees.
 *
 * @package NashuaPalCore
 * @since   1.31.0
 */
final class NPAL_Impact_Awardee_Meta {

	const NONCE = 'npal_impact_awardee_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Impact_Awardee::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/* ------------------------------------------------------------------ */
	/*  Category helpers (taxonomy-driven)                                 */
	/* ------------------------------------------------------------------ */

	public static function category_options() {
		$options = array( '' => __( '— Select Category —', 'nashua-pal-core' ) );
		$terms   = get_terms( array(
			'taxonomy'   => NPAL_CPT_Impact_Awardee::TAXONOMY,
			'hide_empty' => false,
		) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}
		return $options;
	}

	public static function category_label( $slug ) {
		if ( ! $slug ) { return ''; }
		$term = get_term_by( 'slug', $slug, NPAL_CPT_Impact_Awardee::TAXONOMY );
		return $term ? $term->name : '';
	}

	/* ------------------------------------------------------------------ */
	/*  Admin scripts (media uploader for gallery)                         */
	/* ------------------------------------------------------------------ */

	public function admin_scripts( $hook ) {
		global $post_type;
		if ( NPAL_CPT_Impact_Awardee::POST_TYPE !== $post_type ) { return; }
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
		wp_enqueue_media();
	}

	/* ------------------------------------------------------------------ */
	/*  Meta boxes                                                         */
	/* ------------------------------------------------------------------ */

	public function add_boxes() {
		$pt = NPAL_CPT_Impact_Awardee::POST_TYPE;

		add_meta_box(
			'pal_awardee_details',
			__( 'Awardee Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			$pt, 'normal', 'high'
		);

		add_meta_box(
			'pal_awardee_media',
			__( 'Media & Gallery', 'nashua-pal-core' ),
			array( $this, 'render_media' ),
			$pt, 'normal', 'default'
		);

		add_meta_box(
			'pal_awardee_press_release',
			__( 'Press Release', 'nashua-pal-core' ),
			array( $this, 'render_press_release' ),
			$pt, 'normal', 'default'
		);

		add_meta_box(
			'pal_awardee_display',
			__( 'Display Settings', 'nashua-pal-core' ),
			array( $this, 'render_display' ),
			$pt, 'side', 'default'
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Awardee Details                                            */
	/* ------------------------------------------------------------------ */

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$name        = get_post_meta( $post->ID, '_pal_awardee_name', true );
		$award_title = get_post_meta( $post->ID, '_pal_awardee_award_title', true );
		$year        = get_post_meta( $post->ID, '_pal_awardee_year', true );
		$org         = get_post_meta( $post->ID, '_pal_awardee_organization', true );
		$bio         = get_post_meta( $post->ID, '_pal_awardee_bio', true );
		$donate_url  = get_post_meta( $post->ID, '_pal_awardee_donate_url', true );

		$current_term = NPAL_CPT_Impact_Awardee::get_award_category( $post->ID );
		$current_slug = $current_term ? $current_term->slug : '';
		$cat_options  = self::category_options();
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_awardee_name"><?php esc_html_e( 'Full Name', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_awardee_name" name="pal_awardee_name" class="large-text"
						value="<?php echo esc_attr( $name ); ?>"
						placeholder="<?php esc_attr_e( 'Full name of the awardee', 'nashua-pal-core' ); ?>" />
					<p class="description"><?php esc_html_e( 'If different from the post title. Leave blank to use the post title.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_awardee_award_title"><?php esc_html_e( 'Award Title', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_awardee_award_title" name="pal_awardee_award_title" class="large-text"
						value="<?php echo esc_attr( $award_title ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. Staff Member of the Year', 'nashua-pal-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_awardee_year"><?php esc_html_e( 'Award Year', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="number" id="pal_awardee_year" name="pal_awardee_year" style="width:100px;"
						value="<?php echo esc_attr( $year ); ?>"
						min="2000" max="2099" step="1"
						placeholder="<?php echo esc_attr( gmdate( 'Y' ) ); ?>" />
					<p class="description"><?php esc_html_e( 'The year this award was given. Used for filtering and grouping.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_awardee_category"><?php esc_html_e( 'Award Category', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<select id="pal_awardee_category" name="pal_awardee_category" style="min-width:240px;">
						<?php foreach ( $cat_options as $val => $label ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_slug, $val ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php
						$tax_url = admin_url( 'edit-tags.php?taxonomy=' . NPAL_CPT_Impact_Awardee::TAXONOMY . '&post_type=' . NPAL_CPT_Impact_Awardee::POST_TYPE );
						printf(
							esc_html__( 'Select a category or %s.', 'nashua-pal-core' ),
							'<a href="' . esc_url( $tax_url ) . '">' . esc_html__( 'manage categories', 'nashua-pal-core' ) . '</a>'
						);
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_awardee_organization"><?php esc_html_e( 'School / Organization', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_awardee_organization" name="pal_awardee_organization" class="regular-text"
						value="<?php echo esc_attr( $org ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. Nashua High School North', 'nashua-pal-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_awardee_bio"><?php esc_html_e( 'Short Bio', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<textarea id="pal_awardee_bio" name="pal_awardee_bio" class="large-text" rows="4"
						placeholder="<?php esc_attr_e( 'Brief description shown on the card. Use the main editor for the full story.', 'nashua-pal-core' ); ?>"
					><?php echo esc_textarea( $bio ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Displayed as the card excerpt. If blank, the post excerpt is used instead.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_awardee_donate_url"><?php esc_html_e( 'Donate URL', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="url" id="pal_awardee_donate_url" name="pal_awardee_donate_url" class="regular-text"
						value="<?php echo esc_attr( $donate_url ); ?>"
						placeholder="https://" />
					<p class="description"><?php esc_html_e( 'Custom donate link for this awardee. Opens in a new tab on the card.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Media & Gallery                                            */
	/* ------------------------------------------------------------------ */

	public function render_media( $post ) {
		$video_url  = get_post_meta( $post->ID, '_pal_awardee_video_url', true );
		$gallery    = get_post_meta( $post->ID, '_pal_awardee_gallery', true );
		$gallery_ids = ! empty( $gallery ) ? array_filter( array_map( 'absint', explode( ',', $gallery ) ) ) : array();
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_awardee_video_url"><?php esc_html_e( 'YouTube / Video URL', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="url" id="pal_awardee_video_url" name="pal_awardee_video_url" class="regular-text"
						value="<?php echo esc_attr( $video_url ); ?>"
						placeholder="https://www.youtube.com/watch?v=..." />
					<p class="description"><?php esc_html_e( 'YouTube or video link. Embedded on the awardee profile page.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Photo Gallery', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<div id="pal-awardee-gallery-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
						<?php foreach ( $gallery_ids as $img_id ) :
							$thumb = wp_get_attachment_image_url( $img_id, 'thumbnail' );
							if ( ! $thumb ) continue;
						?>
							<div class="pal-awardee-gallery-thumb" data-id="<?php echo esc_attr( $img_id ); ?>" style="position:relative;width:80px;height:80px;">
								<img src="<?php echo esc_url( $thumb ); ?>" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:2px solid #ddd;" />
								<button type="button" class="pal-awardee-gallery-remove" style="position:absolute;top:-6px;right:-6px;background:#d63638;color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:12px;line-height:20px;cursor:pointer;padding:0;">&times;</button>
							</div>
						<?php endforeach; ?>
					</div>
					<input type="hidden" id="pal_awardee_gallery" name="pal_awardee_gallery"
						value="<?php echo esc_attr( implode( ',', $gallery_ids ) ); ?>" />
					<button type="button" id="pal-awardee-gallery-add" class="button">
						<span class="dashicons dashicons-images-alt2" style="vertical-align:middle;margin-right:4px;"></span>
						<?php esc_html_e( 'Add / Manage Photos', 'nashua-pal-core' ); ?>
					</button>
					<p class="description"><?php esc_html_e( 'Select multiple images to display in a gallery on the awardee profile page. Drag to reorder in the media modal.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
		</table>

		<script>
		jQuery(function($){
			var $preview = $('#pal-awardee-gallery-preview'),
				$input   = $('#pal_awardee_gallery');

			function refreshInput() {
				var ids = [];
				$preview.find('.pal-awardee-gallery-thumb').each(function(){
					ids.push( $(this).data('id') );
				});
				$input.val( ids.join(',') );
			}

			// Remove single image.
			$preview.on('click', '.pal-awardee-gallery-remove', function(e){
				e.preventDefault();
				$(this).closest('.pal-awardee-gallery-thumb').remove();
				refreshInput();
			});

			// Open media modal.
			$('#pal-awardee-gallery-add').on('click', function(e){
				e.preventDefault();
				var frame = wp.media({
					title: '<?php echo esc_js( __( 'Select Gallery Images', 'nashua-pal-core' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Add to Gallery', 'nashua-pal-core' ) ); ?>' },
					multiple: true,
					library: { type: 'image' }
				});

				frame.on('select', function(){
					var selection = frame.state().get('selection');
					selection.each(function(attachment){
						var id    = attachment.id,
							thumb = attachment.attributes.sizes && attachment.attributes.sizes.thumbnail
								? attachment.attributes.sizes.thumbnail.url
								: attachment.attributes.url;

						// Skip duplicates.
						if ( $preview.find('[data-id="' + id + '"]').length ) return;

						$preview.append(
							'<div class="pal-awardee-gallery-thumb" data-id="' + id + '" style="position:relative;width:80px;height:80px;">'
							+ '<img src="' + thumb + '" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:2px solid #ddd;" />'
							+ '<button type="button" class="pal-awardee-gallery-remove" style="position:absolute;top:-6px;right:-6px;background:#d63638;color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:12px;line-height:20px;cursor:pointer;padding:0;">&times;</button>'
							+ '</div>'
						);
					});
					refreshInput();
				});

				frame.open();
			});
		});
		</script>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Press Release                                              */
	/* ------------------------------------------------------------------ */

	public function render_press_release( $post ) {
		$press_release = get_post_meta( $post->ID, '_pal_awardee_press_release', true );
		$press_btn     = get_post_meta( $post->ID, '_pal_awardee_press_btn_label', true );
		$press_pdf_id  = absint( get_post_meta( $post->ID, '_pal_awardee_press_pdf', true ) );
		$press_pdf_url = $press_pdf_id ? wp_get_attachment_url( $press_pdf_id ) : '';
		$press_pdf_name = $press_pdf_id ? basename( get_attached_file( $press_pdf_id ) ) : '';
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_awardee_press_btn_label"><?php esc_html_e( 'Button Label', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_awardee_press_btn_label" name="pal_awardee_press_btn_label" class="regular-text"
						value="<?php echo esc_attr( $press_btn ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. Read Press Release', 'nashua-pal-core' ); ?>" />
					<p class="description"><?php esc_html_e( 'Text shown on the button. Defaults to "Read Press Release" if left blank.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Press Release PDF', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<div id="pal-press-pdf-preview" style="<?php echo $press_pdf_id ? '' : 'display:none;'; ?>margin-bottom:10px;">
						<div style="display:inline-flex;align-items:center;gap:10px;background:#f0f0f1;border:1px solid #ddd;border-radius:6px;padding:10px 16px;">
							<span class="dashicons dashicons-pdf" style="color:#d63638;font-size:24px;width:24px;height:24px;"></span>
							<span id="pal-press-pdf-filename" style="font-weight:500;"><?php echo esc_html( $press_pdf_name ); ?></span>
							<a href="<?php echo esc_url( $press_pdf_url ); ?>" target="_blank" rel="noopener" class="button button-small" id="pal-press-pdf-view"><?php esc_html_e( 'View', 'nashua-pal-core' ); ?></a>
							<button type="button" class="button button-small" id="pal-press-pdf-remove" style="color:#d63638;"><?php esc_html_e( 'Remove', 'nashua-pal-core' ); ?></button>
						</div>
					</div>
					<input type="hidden" id="pal_awardee_press_pdf" name="pal_awardee_press_pdf"
						value="<?php echo esc_attr( $press_pdf_id ?: '' ); ?>" />
					<button type="button" id="pal-press-pdf-upload" class="button">
						<span class="dashicons dashicons-upload" style="vertical-align:middle;margin-right:4px;"></span>
						<?php esc_html_e( 'Upload PDF', 'nashua-pal-core' ); ?>
					</button>
					<p class="description"><?php esc_html_e( 'Upload a PDF file. If provided, it will be embedded in the press release popup and available for download.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_awardee_press_release"><?php esc_html_e( 'Press Release Content', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<textarea id="pal_awardee_press_release" name="pal_awardee_press_release" class="large-text" rows="12"
						placeholder="<?php esc_attr_e( 'Paste or type the full press release text here...', 'nashua-pal-core' ); ?>"
					><?php echo esc_textarea( $press_release ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Optional text content. If a PDF is uploaded, this text appears above the PDF embed. You can use either or both.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
		</table>

		<script>
		jQuery(function($){
			var $input   = $('#pal_awardee_press_pdf'),
				$preview = $('#pal-press-pdf-preview'),
				$fname   = $('#pal-press-pdf-filename'),
				$viewBtn = $('#pal-press-pdf-view');

			$('#pal-press-pdf-upload').on('click', function(e){
				e.preventDefault();
				var frame = wp.media({
					title: '<?php echo esc_js( __( 'Select Press Release PDF', 'nashua-pal-core' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Use This PDF', 'nashua-pal-core' ) ); ?>' },
					multiple: false,
					library: { type: 'application/pdf' }
				});
				frame.on('select', function(){
					var attachment = frame.state().get('selection').first().toJSON();
					$input.val(attachment.id);
					$fname.text(attachment.filename);
					$viewBtn.attr('href', attachment.url);
					$preview.show();
				});
				frame.open();
			});

			$('#pal-press-pdf-remove').on('click', function(e){
				e.preventDefault();
				$input.val('');
				$preview.hide();
			});
		});
		</script>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Display Settings (sidebar)                                 */
	/* ------------------------------------------------------------------ */

	public function render_display( $post ) {
		$order = get_post_meta( $post->ID, '_pal_awardee_order', true );
		?>
		<p>
			<label for="pal_awardee_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label><br />
			<input type="number" id="pal_awardee_order" name="pal_awardee_order" style="width:80px;"
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

		$this->save_text( $post_id, 'pal_awardee_name',        '_pal_awardee_name' );
		$this->save_text( $post_id, 'pal_awardee_award_title', '_pal_awardee_award_title' );
		$this->save_text( $post_id, 'pal_awardee_organization','_pal_awardee_organization' );

		$year = isset( $_POST['pal_awardee_year'] ) ? absint( $_POST['pal_awardee_year'] ) : 0;
		$this->set_or_delete( $post_id, '_pal_awardee_year', $year ? (string) $year : '' );

		// Bio — allow basic HTML via wp_kses_post.
		$bio = isset( $_POST['pal_awardee_bio'] ) ? wp_kses_post( wp_unslash( $_POST['pal_awardee_bio'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_awardee_bio', $bio );

		$this->save_url( $post_id, 'pal_awardee_donate_url', '_pal_awardee_donate_url' );
		$this->save_url( $post_id, 'pal_awardee_video_url', '_pal_awardee_video_url' );

		// Press release — plain text.
		$press = isset( $_POST['pal_awardee_press_release'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pal_awardee_press_release'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_awardee_press_release', $press );
		$this->save_text( $post_id, 'pal_awardee_press_btn_label', '_pal_awardee_press_btn_label' );

		// Press release PDF.
		$press_pdf = isset( $_POST['pal_awardee_press_pdf'] ) ? absint( $_POST['pal_awardee_press_pdf'] ) : 0;
		$this->set_or_delete( $post_id, '_pal_awardee_press_pdf', $press_pdf ? (string) $press_pdf : '' );

		// Gallery — comma-separated attachment IDs.
		$gallery_raw = isset( $_POST['pal_awardee_gallery'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_awardee_gallery'] ) ) : '';
		$gallery_ids = array_filter( array_map( 'absint', explode( ',', $gallery_raw ) ) );
		$this->set_or_delete( $post_id, '_pal_awardee_gallery', ! empty( $gallery_ids ) ? implode( ',', $gallery_ids ) : '' );

		// Category — set taxonomy term.
		$cat_slug = isset( $_POST['pal_awardee_category'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_awardee_category'] ) ) : '';
		if ( $cat_slug ) {
			wp_set_object_terms( $post_id, $cat_slug, NPAL_CPT_Impact_Awardee::TAXONOMY );
		} else {
			wp_set_object_terms( $post_id, array(), NPAL_CPT_Impact_Awardee::TAXONOMY );
		}

		$order = isset( $_POST['pal_awardee_order'] ) ? absint( $_POST['pal_awardee_order'] ) : 0;
		update_post_meta( $post_id, '_pal_awardee_order', $order );
	}

	/* ------------------------------------------------------------------ */
	/*  Helpers                                                            */
	/* ------------------------------------------------------------------ */

	private function save_text( $post_id, $field, $meta_key ) {
		$val = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		$this->set_or_delete( $post_id, $meta_key, $val );
	}

	private function save_url( $post_id, $field, $meta_key ) {
		$raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
		$val = esc_url_raw( trim( $raw ) );
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
