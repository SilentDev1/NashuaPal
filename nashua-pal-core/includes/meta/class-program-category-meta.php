<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Adds custom fields to the pal_program_category taxonomy:
 *  - Category image (media library picker)
 *  - Display order (integer — controls sort on the Programs Page)
 *
 * All values are stored as term meta via the Term Meta API (WP 4.4+).
 *
 * @since 1.10.0
 */
final class NPAL_Program_Category_Meta {

	const TAXONOMY = 'pal_program_category';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		// Add fields to the "Add New Category" form.
		add_action( self::TAXONOMY . '_add_form_fields',  array( $this, 'add_form_fields' ) );
		// Add fields to the "Edit Category" form.
		add_action( self::TAXONOMY . '_edit_form_fields', array( $this, 'edit_form_fields' ), 10 );
		// Save.
		add_action( 'created_' . self::TAXONOMY, array( $this, 'save' ) );
		add_action( 'edited_'  . self::TAXONOMY, array( $this, 'save' ) );
		// Admin columns.
		add_filter( 'manage_edit-' . self::TAXONOMY . '_columns',        array( $this, 'columns' ) );
		add_filter( 'manage_' . self::TAXONOMY . '_custom_column',       array( $this, 'column_content' ), 10, 3 );
		// Enqueue media picker on taxonomy screens.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/* ------------------------------------------------------------------
	 * "Add New" form (before the submit button)
	 * ---------------------------------------------------------------- */
	public function add_form_fields() {
		?>
		<div class="form-field">
			<label for="pal_cat_image"><?php esc_html_e( 'Category Image', 'nashua-pal-core' ); ?></label>
			<div id="pal-cat-image-wrap">
				<img id="pal-cat-image-preview" src="" style="max-width:200px;max-height:120px;display:none;margin-bottom:8px;" />
				<input type="hidden" id="pal_cat_image" name="pal_cat_image" value="" />
				<button type="button" class="button" id="pal-cat-image-btn"><?php esc_html_e( 'Choose Image', 'nashua-pal-core' ); ?></button>
				<button type="button" class="button" id="pal-cat-image-remove" style="display:none;"><?php esc_html_e( 'Remove', 'nashua-pal-core' ); ?></button>
			</div>
			<p class="description"><?php esc_html_e( 'Photo shown above the category description on the Programs page.', 'nashua-pal-core' ); ?></p>
		</div>
		<div class="form-field">
			<label for="pal_cat_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label>
			<input type="number" id="pal_cat_order" name="pal_cat_order" value="0" min="0" step="1" style="width:80px;" />
			<p class="description"><?php esc_html_e( 'Lower numbers appear first on the Programs page. Default: 0.', 'nashua-pal-core' ); ?></p>
		</div>
		<?php
		$this->inline_js();
	}

	/* ------------------------------------------------------------------
	 * "Edit" form (table rows)
	 * ---------------------------------------------------------------- */
	public function edit_form_fields( $term ) {
		$image_id = (int) get_term_meta( $term->term_id, '_pal_cat_image', true );
		$order    = (int) get_term_meta( $term->term_id, '_pal_cat_order', true );
		$src      = $image_id ? wp_get_attachment_image_url( $image_id, 'medium' ) : '';
		?>
		<tr class="form-field">
			<th scope="row"><label for="pal_cat_image"><?php esc_html_e( 'Category Image', 'nashua-pal-core' ); ?></label></th>
			<td>
				<div id="pal-cat-image-wrap">
					<img id="pal-cat-image-preview" src="<?php echo esc_url( $src ); ?>" style="max-width:200px;max-height:120px;<?php echo $src ? '' : 'display:none;'; ?>margin-bottom:8px;" />
					<br />
					<input type="hidden" id="pal_cat_image" name="pal_cat_image" value="<?php echo esc_attr( $image_id ); ?>" />
					<button type="button" class="button" id="pal-cat-image-btn"><?php esc_html_e( 'Choose Image', 'nashua-pal-core' ); ?></button>
					<button type="button" class="button" id="pal-cat-image-remove" style="<?php echo $image_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove', 'nashua-pal-core' ); ?></button>
				</div>
				<p class="description"><?php esc_html_e( 'Photo shown above the category description on the Programs page.', 'nashua-pal-core' ); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="pal_cat_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label></th>
			<td>
				<input type="number" id="pal_cat_order" name="pal_cat_order" value="<?php echo esc_attr( $order ); ?>" min="0" step="1" style="width:80px;" />
				<p class="description"><?php esc_html_e( 'Lower numbers appear first on the Programs page. Default: 0.', 'nashua-pal-core' ); ?></p>
			</td>
		</tr>
		<?php
		$this->inline_js();
	}

	/* ------------------------------------------------------------------
	 * Save
	 * ---------------------------------------------------------------- */
	public function save( $term_id ) {
		if ( ! current_user_can( 'manage_categories' ) ) { return; }
		if ( isset( $_POST['pal_cat_image'] ) ) {
			update_term_meta( $term_id, '_pal_cat_image', (int) $_POST['pal_cat_image'] );
		}
		if ( isset( $_POST['pal_cat_order'] ) ) {
			update_term_meta( $term_id, '_pal_cat_order', (int) $_POST['pal_cat_order'] );
		}
	}

	/* ------------------------------------------------------------------
	 * Admin columns
	 * ---------------------------------------------------------------- */
	public function columns( $cols ) {
		$cols['pal_order'] = __( 'Order', 'nashua-pal-core' );
		$cols['pal_image'] = __( 'Image', 'nashua-pal-core' );
		return $cols;
	}

	public function column_content( $content, $column, $term_id ) {
		if ( 'pal_order' === $column ) {
			return (int) get_term_meta( $term_id, '_pal_cat_order', true );
		}
		if ( 'pal_image' === $column ) {
			$id = (int) get_term_meta( $term_id, '_pal_cat_image', true );
			if ( $id ) {
				$src = wp_get_attachment_image_url( $id, 'thumbnail' );
				return $src ? '<img src="' . esc_url( $src ) . '" style="width:40px;height:40px;object-fit:cover;border-radius:4px;" />' : '—';
			}
			return '—';
		}
		return $content;
	}

	/* ------------------------------------------------------------------
	 * Enqueue WP media picker on taxonomy screens
	 * ---------------------------------------------------------------- */
	public function enqueue( $hook ) {
		if ( ! in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) ) { return; }
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || self::TAXONOMY !== $screen->taxonomy ) { return; }
		wp_enqueue_media();
	}

	/* ------------------------------------------------------------------
	 * Inline JS for the media picker button
	 * ---------------------------------------------------------------- */
	private function inline_js() {
		static $printed = false;
		if ( $printed ) { return; }
		$printed = true;
		?>
		<script>
		jQuery(function($){
			var frame;
			$(document).on('click', '#pal-cat-image-btn', function(e){
				e.preventDefault();
				if (frame) { frame.open(); return; }
				frame = wp.media({
					title: '<?php echo esc_js( __( 'Choose Category Image', 'nashua-pal-core' ) ); ?>',
					button: { text: '<?php echo esc_js( __( 'Use Image', 'nashua-pal-core' ) ); ?>' },
					multiple: false
				});
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					$('#pal_cat_image').val(att.id);
					$('#pal-cat-image-preview').attr('src', att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url).show();
					$('#pal-cat-image-remove').show();
				});
				frame.open();
			});
			$(document).on('click', '#pal-cat-image-remove', function(e){
				e.preventDefault();
				$('#pal_cat_image').val('');
				$('#pal-cat-image-preview').hide();
				$(this).hide();
			});
			// Reset fields after "Add New" AJAX submit so the form is clean.
			$(document).ajaxComplete(function(e, xhr, settings){
				if (settings.data && typeof settings.data === 'string' && settings.data.indexOf('action=add-tag') !== -1) {
					$('#pal_cat_image').val('');
					$('#pal-cat-image-preview').hide();
					$('#pal-cat-image-remove').hide();
					$('#pal_cat_order').val('0');
				}
			});
		});
		</script>
		<?php
	}
}
