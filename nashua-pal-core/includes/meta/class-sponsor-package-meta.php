<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Sponsor_Package_Meta {

	const NONCE = 'npal_sponsor_package_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Sponsor_Package::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	public static function category_options() {
		$options = array( '' => __( '— Select Category —', 'nashua-pal-core' ) );
		$terms   = get_terms( array(
			'taxonomy'   => NPAL_CPT_Sponsor_Package::TAXONOMY,
			'hide_empty' => false,
		) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}
		return $options;
	}

	/* ------------------------------------------------------------------ */
	/*  Admin scripts (media uploader)                                     */
	/* ------------------------------------------------------------------ */

	public function admin_scripts( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== NPAL_CPT_Sponsor_Package::POST_TYPE ) { return; }
		wp_enqueue_media();
	}

	/* ------------------------------------------------------------------ */
	/*  Meta box                                                           */
	/* ------------------------------------------------------------------ */

	public function add_boxes() {
		add_meta_box(
			'pal_pkg_details',
			__( 'Package Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			NPAL_CPT_Sponsor_Package::POST_TYPE, 'normal', 'high'
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Package Details                                            */
	/* ------------------------------------------------------------------ */

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$year    = get_post_meta( $post->ID, '_pal_pkg_year', true );
		$gallery = get_post_meta( $post->ID, '_pal_pkg_gallery', true );
		$pdf_id  = absint( get_post_meta( $post->ID, '_pal_pkg_pdf', true ) );
		$pdf_url = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';

		$current_term = NPAL_CPT_Sponsor_Package::get_package_category( $post->ID );
		$current_slug = $current_term ? $current_term->slug : '';
		$cat_options  = self::category_options();

		// Parse gallery IDs.
		$gallery_ids = $gallery ? array_filter( array_map( 'absint', explode( ',', $gallery ) ) ) : array();
		?>
		<p style="margin:0 0 10px;color:#555;font-size:13px;">
			<?php esc_html_e( 'The card thumbnail is set via the standard "Featured Image" box on the right. Add additional gallery images or a PDF below.', 'nashua-pal-core' ); ?>
		</p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="pal_pkg_year"><?php esc_html_e( 'Year', 'nashua-pal-core' ); ?></label></th>
				<td>
					<input type="number" id="pal_pkg_year" name="pal_pkg_year" style="width:100px;"
						value="<?php echo esc_attr( $year ); ?>"
						min="2000" max="2099" step="1"
						placeholder="<?php echo esc_attr( gmdate( 'Y' ) ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="pal_pkg_category"><?php esc_html_e( 'Package Category', 'nashua-pal-core' ); ?></label></th>
				<td>
					<select id="pal_pkg_category" name="pal_pkg_category" style="min-width:240px;">
						<?php foreach ( $cat_options as $val => $label ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_slug, $val ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php
						$tax_url = admin_url( 'edit-tags.php?taxonomy=' . NPAL_CPT_Sponsor_Package::TAXONOMY . '&post_type=' . NPAL_CPT_Sponsor_Package::POST_TYPE );
						printf(
							esc_html__( 'Select a category or %s.', 'nashua-pal-core' ),
							'<a href="' . esc_url( $tax_url ) . '">' . esc_html__( 'manage categories', 'nashua-pal-core' ) . '</a>'
						);
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'PDF File', 'nashua-pal-core' ); ?></th>
				<td>
					<input type="hidden" id="pal_pkg_pdf" name="pal_pkg_pdf"
						value="<?php echo esc_attr( $pdf_id ? $pdf_id : '' ); ?>" />
					<div id="pal-pkg-pdf-preview" style="margin-bottom:10px;<?php echo $pdf_url ? '' : 'display:none;'; ?>">
						<div style="display:inline-flex;align-items:center;gap:8px;padding:8px 14px;background:#f0f0f1;border:1px solid #ddd;border-radius:6px;">
							<span class="dashicons dashicons-pdf" style="color:#c0392b;font-size:24px;width:24px;height:24px;"></span>
							<span id="pal-pkg-pdf-name" style="font-weight:600;"><?php
								if ( $pdf_id ) {
									echo esc_html( get_the_title( $pdf_id ) ?: basename( $pdf_url ) );
								}
							?></span>
							<button type="button" id="pal-pkg-pdf-remove" class="button-link" style="color:#b32d2e;text-decoration:none;margin-left:8px;">
								<span class="dashicons dashicons-no-alt" style="font-size:18px;width:18px;height:18px;"></span>
							</button>
						</div>
					</div>
					<button type="button" id="pal-pkg-pdf-add" class="button">
						<span class="dashicons dashicons-pdf" style="margin-top:4px;"></span>
						<?php esc_html_e( 'Upload PDF', 'nashua-pal-core' ); ?>
					</button>
					<p class="description"><?php esc_html_e( 'Optional. If a PDF is set, "View Details" will open the PDF instead of the image lightbox.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Gallery Images', 'nashua-pal-core' ); ?></th>
				<td>
					<input type="hidden" id="pal_pkg_gallery" name="pal_pkg_gallery"
						value="<?php echo esc_attr( $gallery ); ?>" />
					<div id="pal-pkg-gallery-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
						<?php foreach ( $gallery_ids as $aid ) :
							$thumb = wp_get_attachment_image_url( $aid, 'thumbnail' );
							if ( ! $thumb ) { continue; }
							?>
							<div class="pal-pkg-gallery-thumb" data-id="<?php echo esc_attr( $aid ); ?>"
								style="position:relative;width:80px;height:80px;border-radius:6px;overflow:hidden;border:1px solid #ddd;">
								<img src="<?php echo esc_url( $thumb ); ?>" style="width:100%;height:100%;object-fit:cover;" />
								<button type="button" class="pal-pkg-gallery-remove"
									style="position:absolute;top:2px;right:2px;width:20px;height:20px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;font-size:14px;line-height:20px;text-align:center;cursor:pointer;padding:0;">&times;</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" id="pal-pkg-gallery-add" class="button">
						<span class="dashicons dashicons-images-alt2" style="margin-top:4px;"></span>
						<?php esc_html_e( 'Add Images', 'nashua-pal-core' ); ?>
					</button>
					<p class="description"><?php esc_html_e( 'Select multiple images. Used in the lightbox when no PDF is set.', 'nashua-pal-core' ); ?></p>

					<script>
					(function($){
						/* --- PDF uploader --- */
						var $pdfInput   = $('#pal_pkg_pdf');
						var $pdfPreview = $('#pal-pkg-pdf-preview');
						var $pdfName    = $('#pal-pkg-pdf-name');
						var pdfFrame;

						$('#pal-pkg-pdf-add').on('click', function(e){
							e.preventDefault();
							if ( pdfFrame ) { pdfFrame.open(); return; }
							pdfFrame = wp.media({
								title: '<?php echo esc_js( __( 'Select PDF', 'nashua-pal-core' ) ); ?>',
								button: { text: '<?php echo esc_js( __( 'Use this PDF', 'nashua-pal-core' ) ); ?>' },
								multiple: false,
								library: { type: 'application/pdf' }
							});
							pdfFrame.on('select', function(){
								var att = pdfFrame.state().get('selection').first();
								$pdfInput.val(att.get('id'));
								$pdfName.text(att.get('title') || att.get('filename'));
								$pdfPreview.show();
							});
							pdfFrame.open();
						});

						$('#pal-pkg-pdf-remove').on('click', function(e){
							e.preventDefault();
							$pdfInput.val('');
							$pdfName.text('');
							$pdfPreview.hide();
						});

						/* --- Gallery uploader --- */
						var $input   = $('#pal_pkg_gallery');
						var $preview = $('#pal-pkg-gallery-preview');
						var frame;

						$('#pal-pkg-gallery-add').on('click', function(e){
							e.preventDefault();
							if ( frame ) { frame.open(); return; }
							frame = wp.media({
								title: '<?php echo esc_js( __( 'Select Gallery Images', 'nashua-pal-core' ) ); ?>',
								button: { text: '<?php echo esc_js( __( 'Add to Gallery', 'nashua-pal-core' ) ); ?>' },
								multiple: true,
								library: { type: 'image' }
							});
							frame.on('select', function(){
								var sel   = frame.state().get('selection');
								var ids   = $input.val() ? $input.val().split(',').map(Number).filter(Boolean) : [];
								sel.each(function(att){
									var id  = att.get('id');
									if ( ids.indexOf(id) !== -1 ) return;
									ids.push(id);
									var url = att.get('sizes') && att.get('sizes').thumbnail
										? att.get('sizes').thumbnail.url : att.get('url');
									$preview.append(
										'<div class="pal-pkg-gallery-thumb" data-id="'+id+'" style="position:relative;width:80px;height:80px;border-radius:6px;overflow:hidden;border:1px solid #ddd;">'
										+ '<img src="'+url+'" style="width:100%;height:100%;object-fit:cover;" />'
										+ '<button type="button" class="pal-pkg-gallery-remove" style="position:absolute;top:2px;right:2px;width:20px;height:20px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;font-size:14px;line-height:20px;text-align:center;cursor:pointer;padding:0;">&times;</button>'
										+ '</div>'
									);
								});
								$input.val(ids.join(','));
							});
							frame.open();
						});

						$preview.on('click', '.pal-pkg-gallery-remove', function(e){
							e.preventDefault();
							var $thumb = $(this).closest('.pal-pkg-gallery-thumb');
							var rid    = $thumb.data('id');
							$thumb.remove();
							var ids = $input.val().split(',').map(Number).filter(function(id){ return id && id !== rid; });
							$input.val(ids.join(','));
						});
					})(jQuery);
					</script>
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

		$year = isset( $_POST['pal_pkg_year'] ) ? absint( $_POST['pal_pkg_year'] ) : 0;
		$this->set_or_delete( $post_id, '_pal_pkg_year', $year ? (string) $year : '' );

		$cat_slug = isset( $_POST['pal_pkg_category'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_pkg_category'] ) ) : '';
		if ( $cat_slug ) {
			wp_set_object_terms( $post_id, $cat_slug, NPAL_CPT_Sponsor_Package::TAXONOMY );
		} else {
			wp_set_object_terms( $post_id, array(), NPAL_CPT_Sponsor_Package::TAXONOMY );
		}

		// PDF attachment ID.
		$pdf_id = isset( $_POST['pal_pkg_pdf'] ) ? absint( $_POST['pal_pkg_pdf'] ) : 0;
		$this->set_or_delete( $post_id, '_pal_pkg_pdf', $pdf_id ? (string) $pdf_id : '' );

		// Gallery: sanitize comma-separated IDs.
		$gallery_raw = isset( $_POST['pal_pkg_gallery'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_pkg_gallery'] ) ) : '';
		$gallery_ids = array_filter( array_map( 'absint', explode( ',', $gallery_raw ) ) );
		$this->set_or_delete( $post_id, '_pal_pkg_gallery', ! empty( $gallery_ids ) ? implode( ',', $gallery_ids ) : '' );
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

	/**
	 * Get PDF URL for a package (if set).
	 */
	public static function get_pdf_url( $post_id ) {
		$pdf_id = absint( get_post_meta( $post_id, '_pal_pkg_pdf', true ) );
		if ( ! $pdf_id ) { return ''; }
		return wp_get_attachment_url( $pdf_id ) ?: '';
	}

	/**
	 * Get gallery image URLs for a package.
	 * Includes the featured image as the first item if set.
	 */
	public static function get_gallery_urls( $post_id, $size = 'full' ) {
		$urls = array();

		// Featured image first.
		$featured = get_the_post_thumbnail_url( $post_id, $size );
		if ( $featured ) {
			$urls[] = $featured;
		}

		// Gallery images.
		$gallery = get_post_meta( $post_id, '_pal_pkg_gallery', true );
		if ( $gallery ) {
			$ids = array_filter( array_map( 'absint', explode( ',', $gallery ) ) );
			foreach ( $ids as $aid ) {
				$url = wp_get_attachment_image_url( $aid, $size );
				if ( $url && $url !== $featured ) {
					$urls[] = $url;
				}
			}
		}

		return $urls;
	}
}
