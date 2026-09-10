<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Meta boxes for Board Documents.
 *
 * Shows section-specific fields based on the document type:
 * - Event Materials: Event Name, File, Description
 * - Board Documents: Document Type, Year, File, Description
 * - Meeting Minutes: Date, Meeting Minutes (file), Financial Report (file2), Description
 *
 * @package NashuaPalCore
 * @since   1.54.0
 */
final class NPAL_Board_Doc_Meta {

	const NONCE = 'npal_board_doc_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Board_Doc::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_filter( 'enter_title_here', array( $this, 'title_placeholder' ), 10, 2 );
	}

	/* ------------------------------------------------------------------ */
	/*  Helpers                                                            */
	/* ------------------------------------------------------------------ */

	/**
	 * Get the section for the current post — from saved meta or URL param.
	 */
	private function get_section( $post ) {
		$section = get_post_meta( $post->ID, '_board_doc_section', true );
		if ( ! $section && isset( $_GET['board_doc_section'] ) ) {
			$section = sanitize_text_field( wp_unslash( $_GET['board_doc_section'] ) );
		}
		$allowed = array( 'event_materials', 'board_documents', 'meeting_minutes' );
		return in_array( $section, $allowed, true ) ? $section : '';
	}

	private static function section_labels() {
		return array(
			'event_materials' => __( 'Event Material', 'nashua-pal-core' ),
			'board_documents' => __( 'Board Document', 'nashua-pal-core' ),
			'meeting_minutes' => __( 'Meeting Minutes', 'nashua-pal-core' ),
		);
	}

	public function admin_scripts( $hook ) {
		global $post_type;
		if ( NPAL_CPT_Board_Doc::POST_TYPE !== $post_type ) { return; }
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
		wp_enqueue_media();
	}

	/**
	 * Contextual title placeholder based on section.
	 */
	public function title_placeholder( $placeholder, $post ) {
		if ( NPAL_CPT_Board_Doc::POST_TYPE !== $post->post_type ) { return $placeholder; }
		$section = $this->get_section( $post );
		switch ( $section ) {
			case 'event_materials': return __( 'Enter material title (e.g. Sponsorship Packet)', 'nashua-pal-core' );
			case 'board_documents': return __( 'Enter document title', 'nashua-pal-core' );
			case 'meeting_minutes': return __( 'Enter meeting title (e.g. January Board Meeting)', 'nashua-pal-core' );
			default: return __( 'Enter document title', 'nashua-pal-core' );
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Meta boxes                                                         */
	/* ------------------------------------------------------------------ */

	public function add_boxes() {
		$pt = NPAL_CPT_Board_Doc::POST_TYPE;
		add_meta_box( 'pal_board_doc_details', __( 'Document Details', 'nashua-pal-core' ), array( $this, 'render_details' ), $pt, 'normal', 'high' );
		add_meta_box( 'pal_board_doc_display', __( 'Display Settings', 'nashua-pal-core' ), array( $this, 'render_display' ), $pt, 'side', 'default' );
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Document Details (section-specific)                        */
	/* ------------------------------------------------------------------ */

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$section     = $this->get_section( $post );
		$group       = get_post_meta( $post->ID, '_board_doc_group', true );
		$file_id     = get_post_meta( $post->ID, '_board_doc_file_id', true );
		$file2_id    = get_post_meta( $post->ID, '_board_doc_file2_id', true );
		$file3_id    = get_post_meta( $post->ID, '_board_doc_file3_id', true );
		$file4_id    = get_post_meta( $post->ID, '_board_doc_file4_id', true );
		$year        = get_post_meta( $post->ID, '_board_doc_year', true );
		$date        = get_post_meta( $post->ID, '_board_doc_date', true );
		$description = get_post_meta( $post->ID, '_board_doc_description', true );

		$file_url      = $file_id ? wp_get_attachment_url( $file_id ) : '';
		$file_filename = $file_id ? basename( get_attached_file( $file_id ) ) : '';
		$file2_url      = $file2_id ? wp_get_attachment_url( $file2_id ) : '';
		$file2_filename = $file2_id ? basename( get_attached_file( $file2_id ) ) : '';
		$file3_url      = $file3_id ? wp_get_attachment_url( $file3_id ) : '';
		$file3_filename = $file3_id ? basename( get_attached_file( $file3_id ) ) : '';
		$file4_url      = $file4_id ? wp_get_attachment_url( $file4_id ) : '';
		$file4_filename = $file4_id ? basename( get_attached_file( $file4_id ) ) : '';

		$labels = self::section_labels();

		// If no section is set (shouldn't happen with new menu, but handle gracefully).
		if ( ! $section ) {
			?>
			<p><?php esc_html_e( 'Please select a document type to add:', 'nashua-pal-core' ); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . NPAL_CPT_Board_Doc::POST_TYPE . '&board_doc_section=event_materials' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add Event Material', 'nashua-pal-core' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . NPAL_CPT_Board_Doc::POST_TYPE . '&board_doc_section=board_documents' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add Board Document', 'nashua-pal-core' ); ?></a>
				<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . NPAL_CPT_Board_Doc::POST_TYPE . '&board_doc_section=meeting_minutes' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Add Meeting Minutes', 'nashua-pal-core' ); ?></a>
			</p>
			<?php
			return;
		}

		// Hidden section field.
		?>
		<input type="hidden" name="board_doc_section" value="<?php echo esc_attr( $section ); ?>" />
		<div style="margin-bottom:16px;padding:10px 14px;background:#f0f6fc;border-left:4px solid #071F3F;border-radius:4px;">
			<strong><?php echo esc_html( $labels[ $section ] ?? '' ); ?></strong>
		</div>

		<table class="form-table" role="presentation">
		<?php
		// ---- EVENT MATERIALS ----
		if ( 'event_materials' === $section ) {
			$this->render_field_group( $group, __( 'Category', 'nashua-pal-core' ), __( 'e.g. Cone With A Cop, PAL Golf Classic', 'nashua-pal-core' ), __( 'Materials with the same category are grouped together under the same tab.', 'nashua-pal-core' ), $section );
			$this->render_field_file( $file_id, $file_url, $file_filename, 'board_doc_file_id', 'pal-board-doc-file', __( 'File', 'nashua-pal-core' ), __( 'Upload the event material file (PDF, Word, etc.).', 'nashua-pal-core' ) );
			$this->render_field_description( $description );
		}

		// ---- BOARD DOCUMENTS ----
		if ( 'board_documents' === $section ) {
			$this->render_field_group( $group, __( 'Category', 'nashua-pal-core' ), __( 'e.g. Meeting Minutes, Board Retreat Materials', 'nashua-pal-core' ), __( 'Documents with the same category are grouped together under the same card.', 'nashua-pal-core' ), $section );
			$this->render_field_year( $year );
			$this->render_field_file( $file_id, $file_url, $file_filename, 'board_doc_file_id', 'pal-board-doc-file', __( 'File (optional)', 'nashua-pal-core' ), __( 'Upload a file, or enter text content below, or both.', 'nashua-pal-core' ) );
			$this->render_field_richtext( $description, __( 'Minutes', 'nashua-pal-core' ), __( 'Rich text content displayed on the frontend. Use this for meeting minutes, notes, etc.', 'nashua-pal-core' ) );
		}

		// ---- MEETING MINUTES ----
		if ( 'meeting_minutes' === $section ) {
			$this->render_field_date( $date );
			$this->render_field_richtext( $description, __( 'Meeting Minutes', 'nashua-pal-core' ), __( 'Enter the meeting minutes text. This will display in a popup when clicked on the frontend.', 'nashua-pal-core' ) );
			$this->render_field_file( $file_id, $file_url, $file_filename, 'board_doc_file_id', 'pal-board-doc-file', __( 'Minutes File (optional)', 'nashua-pal-core' ), __( 'Optional PDF/document version of the minutes.', 'nashua-pal-core' ) );
			$this->render_field_file( $file2_id, $file2_url, $file2_filename, 'board_doc_file2_id', 'pal-board-doc-file2', __( 'Financial Report', 'nashua-pal-core' ), __( 'Optional financial report file for this meeting date.', 'nashua-pal-core' ) );
			$this->render_field_file( $file3_id, $file3_url, $file3_filename, 'board_doc_file3_id', 'pal-board-doc-file3', __( 'Revised Financial Report', 'nashua-pal-core' ), __( 'Upload a revised version of the financial report. Both files will appear on the frontend.', 'nashua-pal-core' ) );
			$this->render_field_file( $file4_id, $file4_url, $file4_filename, 'board_doc_file4_id', 'pal-board-doc-file4', __( 'Additional Financial Report', 'nashua-pal-core' ), __( 'Optional third financial report file for this meeting date.', 'nashua-pal-core' ) );
		}
		?>
		</table>

		<script>
		jQuery(function($){
			// Generic media uploader factory.
			function initUploader(inputId, previewId, linkId, removeId, btnId, uploadLabel, replaceLabel) {
				var $input=$('#'+inputId),$preview=$('#'+previewId),$link=$('#'+linkId),$btn=$('#'+btnId);
				$btn.on('click',function(e){
					e.preventDefault();
					var frame=wp.media({title:'<?php echo esc_js( __( 'Select File', 'nashua-pal-core' ) ); ?>',button:{text:'<?php echo esc_js( __( 'Use This File', 'nashua-pal-core' ) ); ?>'},multiple:false});
					frame.on('select',function(){var a=frame.state().get('selection').first().toJSON();$input.val(a.id);$link.attr('href',a.url).text(a.filename);$preview.show();$btn.text(replaceLabel);});
					frame.open();
				});
				$('#'+removeId).on('click',function(e){e.preventDefault();$input.val('');$preview.hide();$btn.html('<span class="dashicons dashicons-upload" style="vertical-align:middle;margin-right:4px;"></span> '+uploadLabel);});
			}

			// Init file uploaders that exist on this page.
			if ($('#pal-board-doc-file-upload').length) {
				initUploader('board_doc_file_id','pal-board-doc-file-preview','pal-board-doc-file-link','pal-board-doc-file-remove','pal-board-doc-file-upload',
					'<?php echo esc_js( __( 'Upload File', 'nashua-pal-core' ) ); ?>','<?php echo esc_js( __( 'Replace File', 'nashua-pal-core' ) ); ?>');
			}
			if ($('#pal-board-doc-file2-upload').length) {
				initUploader('board_doc_file2_id','pal-board-doc-file2-preview','pal-board-doc-file2-link','pal-board-doc-file2-remove','pal-board-doc-file2-upload',
					'<?php echo esc_js( __( 'Upload File', 'nashua-pal-core' ) ); ?>','<?php echo esc_js( __( 'Replace File', 'nashua-pal-core' ) ); ?>');
			}
			if ($('#pal-board-doc-file3-upload').length) {
				initUploader('board_doc_file3_id','pal-board-doc-file3-preview','pal-board-doc-file3-link','pal-board-doc-file3-remove','pal-board-doc-file3-upload',
					'<?php echo esc_js( __( 'Upload File', 'nashua-pal-core' ) ); ?>','<?php echo esc_js( __( 'Replace File', 'nashua-pal-core' ) ); ?>');
			}
			if ($('#pal-board-doc-file4-upload').length) {
				initUploader('board_doc_file4_id','pal-board-doc-file4-preview','pal-board-doc-file4-link','pal-board-doc-file4-remove','pal-board-doc-file4-upload',
					'<?php echo esc_js( __( 'Upload File', 'nashua-pal-core' ) ); ?>','<?php echo esc_js( __( 'Replace File', 'nashua-pal-core' ) ); ?>');
			}
		});
		</script>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Field renderers                                                    */
	/* ------------------------------------------------------------------ */

	private function render_field_group( $value, $label, $placeholder, $desc, $section ) {
		// Pull categories from the Manage Categories page (ordered).
		$categories = NPAL_Board_Doc_Categories::get_names( $section );

		// Include the current value if it's not yet in the categories list (legacy data).
		if ( $value && ! in_array( $value, $categories, true ) ) {
			$categories[] = $value;
		}

		$manage_url = admin_url( 'edit.php?post_type=' . NPAL_CPT_Board_Doc::POST_TYPE . '&page=board-doc-categories' );
		?>
		<tr>
			<th scope="row"><label for="board_doc_group"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<select id="board_doc_group" name="board_doc_group" style="width:250px;">
					<option value=""><?php echo '— ' . esc_html__( 'Select', 'nashua-pal-core' ) . ' ' . esc_html( $label ) . ' —'; ?></option>
					<?php foreach ( $categories as $cat ) : ?>
						<option value="<?php echo esc_attr( $cat ); ?>" <?php selected( $value, $cat ); ?>><?php echo esc_html( $cat ); ?></option>
					<?php endforeach; ?>
				</select>
				<a href="<?php echo esc_url( $manage_url ); ?>" class="button" style="margin-left:8px;" target="_blank">
					<span class="dashicons dashicons-plus-alt2" style="vertical-align:middle;margin-right:2px;"></span>
					<?php esc_html_e( 'Manage Categories', 'nashua-pal-core' ); ?>
				</a>
				<p class="description"><?php echo esc_html( $desc ); ?></p>
			</td>
		</tr>
		<?php
	}

	private function render_field_year( $value ) {
		?>
		<tr>
			<th scope="row"><label for="board_doc_year"><?php esc_html_e( 'Year', 'nashua-pal-core' ); ?></label></th>
			<td>
				<input type="number" id="board_doc_year" name="board_doc_year" style="width:100px;"
					value="<?php echo esc_attr( $value ); ?>" min="2000" max="2099" step="1"
					placeholder="<?php echo esc_attr( gmdate( 'Y' ) ); ?>" />
				<p class="description"><?php esc_html_e( 'Documents are grouped by year on the frontend.', 'nashua-pal-core' ); ?></p>
			</td>
		</tr>
		<?php
	}

	private function render_field_date( $value ) {
		?>
		<tr>
			<th scope="row"><label for="board_doc_date"><?php esc_html_e( 'Meeting Date', 'nashua-pal-core' ); ?></label></th>
			<td>
				<input type="date" id="board_doc_date" name="board_doc_date" style="width:180px;" value="<?php echo esc_attr( $value ); ?>" />
				<p class="description"><?php esc_html_e( 'The date of the board meeting. Used for sorting.', 'nashua-pal-core' ); ?></p>
			</td>
		</tr>
		<?php
	}

	private function render_field_file( $file_id, $file_url, $file_filename, $input_name, $prefix, $label, $desc ) {
		?>
		<tr>
			<th scope="row"><label><?php echo esc_html( $label ); ?></label></th>
			<td>
				<div id="<?php echo esc_attr( $prefix ); ?>-preview" style="margin-bottom:10px;<?php echo $file_id ? '' : 'display:none;'; ?>">
					<span class="dashicons dashicons-media-document" style="color:#071F3F;font-size:24px;vertical-align:middle;margin-right:6px;"></span>
					<a href="<?php echo esc_url( $file_url ); ?>" target="_blank" rel="noopener" id="<?php echo esc_attr( $prefix ); ?>-link"><?php echo esc_html( $file_filename ); ?></a>
					<button type="button" id="<?php echo esc_attr( $prefix ); ?>-remove" class="button-link" style="color:#b32d2e;margin-left:12px;">
						<span class="dashicons dashicons-dismiss" style="vertical-align:middle;"></span> <?php esc_html_e( 'Remove', 'nashua-pal-core' ); ?>
					</button>
				</div>
				<input type="hidden" id="<?php echo esc_attr( $input_name ); ?>" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $file_id ); ?>" />
				<button type="button" id="<?php echo esc_attr( $prefix ); ?>-upload" class="button">
					<span class="dashicons dashicons-upload" style="vertical-align:middle;margin-right:4px;"></span>
					<?php echo $file_id ? esc_html__( 'Replace File', 'nashua-pal-core' ) : esc_html__( 'Upload File', 'nashua-pal-core' ); ?>
				</button>
				<p class="description"><?php echo esc_html( $desc ); ?></p>
			</td>
		</tr>
		<?php
	}

	private function render_field_description( $value ) {
		?>
		<tr>
			<th scope="row"><label for="board_doc_description"><?php esc_html_e( 'Description', 'nashua-pal-core' ); ?></label></th>
			<td>
				<textarea id="board_doc_description" name="board_doc_description" class="large-text" rows="3"
					placeholder="<?php esc_attr_e( 'Optional description shown on the frontend.', 'nashua-pal-core' ); ?>"
				><?php echo esc_textarea( $value ); ?></textarea>
			</td>
		</tr>
		<?php
	}

	private function render_field_richtext( $value, $label, $desc ) {
		?>
		<tr>
			<th scope="row"><label><?php echo esc_html( $label ); ?></label></th>
			<td>
				<?php
				wp_editor( $value, 'board_doc_description', array(
					'textarea_name' => 'board_doc_description',
					'textarea_rows' => 12,
					'media_buttons' => true,
					'teeny'         => false,
					'quicktags'     => true,
				) );
				?>
				<p class="description" style="margin-top:8px;"><?php echo esc_html( $desc ); ?></p>
			</td>
		</tr>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Display Settings sidebar                                   */
	/* ------------------------------------------------------------------ */

	public function render_display( $post ) {
		$order = get_post_meta( $post->ID, '_board_doc_order', true );
		?>
		<p>
			<label for="board_doc_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label><br />
			<input type="number" id="board_doc_order" name="board_doc_order" style="width:80px;"
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

		$section = isset( $_POST['board_doc_section'] ) ? sanitize_text_field( wp_unslash( $_POST['board_doc_section'] ) ) : '';
		$allowed = array( 'event_materials', 'board_documents', 'meeting_minutes' );
		$this->set_or_delete( $post_id, '_board_doc_section', in_array( $section, $allowed, true ) ? $section : '' );

		$group = isset( $_POST['board_doc_group'] ) ? sanitize_text_field( wp_unslash( $_POST['board_doc_group'] ) ) : '';
		$this->set_or_delete( $post_id, '_board_doc_group', $group );

		$file_id = isset( $_POST['board_doc_file_id'] ) ? absint( $_POST['board_doc_file_id'] ) : 0;
		$this->set_or_delete( $post_id, '_board_doc_file_id', $file_id ? (string) $file_id : '' );

		$file2_id = isset( $_POST['board_doc_file2_id'] ) ? absint( $_POST['board_doc_file2_id'] ) : 0;
		$this->set_or_delete( $post_id, '_board_doc_file2_id', $file2_id ? (string) $file2_id : '' );

		$file3_id = isset( $_POST['board_doc_file3_id'] ) ? absint( $_POST['board_doc_file3_id'] ) : 0;
		$this->set_or_delete( $post_id, '_board_doc_file3_id', $file3_id ? (string) $file3_id : '' );

		$file4_id = isset( $_POST['board_doc_file4_id'] ) ? absint( $_POST['board_doc_file4_id'] ) : 0;
		$this->set_or_delete( $post_id, '_board_doc_file4_id', $file4_id ? (string) $file4_id : '' );

		$year = isset( $_POST['board_doc_year'] ) ? absint( $_POST['board_doc_year'] ) : 0;
		$this->set_or_delete( $post_id, '_board_doc_year', $year ? (string) $year : '' );

		$date = isset( $_POST['board_doc_date'] ) ? sanitize_text_field( wp_unslash( $_POST['board_doc_date'] ) ) : '';
		$this->set_or_delete( $post_id, '_board_doc_date', $date );

		$desc = isset( $_POST['board_doc_description'] ) ? wp_kses_post( wp_unslash( $_POST['board_doc_description'] ) ) : '';
		$this->set_or_delete( $post_id, '_board_doc_description', $desc );

		$order = isset( $_POST['board_doc_order'] ) ? absint( $_POST['board_doc_order'] ) : 0;
		update_post_meta( $post_id, '_board_doc_order', $order );
	}

	private function set_or_delete( $post_id, $key, $value ) {
		if ( '' === $value ) { delete_post_meta( $post_id, $key ); }
		else { update_post_meta( $post_id, $key, $value ); }
	}
}
