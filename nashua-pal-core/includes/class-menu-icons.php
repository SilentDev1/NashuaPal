<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Adds an "Icon" field to every WordPress nav menu item under
 * Appearance → Menus. Admins type a Font Awesome 6 class
 * (e.g. `fas fa-shield-halved`) or a built-in NPAL_Icons slug
 * (e.g. `shield-check`) and it renders before the menu item label
 * in the mobile drawer.
 *
 * The value is saved to post meta `_pal_menu_icon` on the item's
 * `nav_menu_item` post. NPAL_Mobile_Menu_Walker reads that meta first;
 * the legacy `pal-mi-<name>` CSS-class detection from 1.8.45 still
 * works as a fallback.
 */
final class NPAL_Menu_Icons {

	const META_KEY = '_pal_menu_icon';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		// Render the field on each menu item in nav-menus.php
		add_action( 'wp_nav_menu_item_custom_fields', array( $this, 'render_field' ), 10, 4 );
		// Save the field on menu update
		add_action( 'wp_update_nav_menu_item', array( $this, 'save_field' ), 10, 3 );
		// Enqueue the icon picker assets (CSS + WP media for image picker tab)
		// AND the bundled Font Awesome stylesheet on the nav-menus.php screen.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
		// Render the icon picker modal once in the admin footer of nav-menus.php
		// (the picker's `field()` markup binds buttons that open this modal).
		add_action( 'admin_footer-nav-menus.php', array( $this, 'render_picker_modal' ) );
	}

	public function enqueue_admin( $hook ) {
		if ( 'nav-menus.php' !== $hook ) { return; }
		// Reuse the same visual icon picker that powers Stats / Programs / Site Alert
		NPAL_Icon_Picker::enqueue();
		wp_enqueue_media();
		wp_enqueue_style(
			'nashua-pal-fontawesome',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css',
			array(),
			'6.5.2'
		);
	}

	/**
	 * Outputs the icon picker modal once in the admin footer. Subsequent
	 * calls to NPAL_Icon_Picker::modal() are no-ops thanks to the picker's
	 * own static $modal_rendered flag.
	 */
	public function render_picker_modal() {
		NPAL_Icon_Picker::modal();
	}

	/**
	 * Renders the visual icon picker on each menu item. Uses the same
	 * NPAL_Icon_Picker modal that powers Stats / Programs / Site Alert
	 * pickers — admins click "Choose icon" to open a tabbed modal with:
	 *   • Built-in icons grid (visual click-to-select)
	 *   • Upload custom (WP media library)
	 *   • Font Awesome (type any FA 6 class with live preview)
	 */
	public function render_field( $item_id, $item, $depth, $args ) {
		$value = get_post_meta( $item_id, self::META_KEY, true );
		$name  = 'pal_menu_icon[' . (int) $item_id . ']';
		$id    = 'pal-menu-icon-' . (int) $item_id;
		?>
		<p class="field-pal-menu-icon description description-wide">
			<label style="display:block;margin-bottom:6px;font-weight:600;"><?php esc_html_e( 'Icon', 'nashua-pal-core' ); ?></label>
			<?php NPAL_Icon_Picker::field( $name, $value, array( 'id' => $id ) ); ?>
			<span class="description" style="display:block;margin-top:6px;font-size:11px;color:#646970;">
				<?php esc_html_e( 'Click "Choose icon" to pick from the built-in library, upload a custom image, or paste a Font Awesome class. Icon renders before the menu label in the mobile drawer.', 'nashua-pal-core' ); ?>
			</span>
		</p>
		<?php
	}

	/**
	 * Persist the icon value when the menu is saved. WP fires
	 * wp_update_nav_menu_item once per item; the field name is
	 * `pal_menu_icon[<item_id>]` so we read by item id.
	 */
	public function save_field( $menu_id, $menu_item_db_id, $args ) {
		if ( ! isset( $_POST['pal_menu_icon'] ) || ! is_array( $_POST['pal_menu_icon'] ) ) { return; }
		if ( ! current_user_can( 'edit_theme_options' ) ) { return; }

		$raw = isset( $_POST['pal_menu_icon'][ $menu_item_db_id ] ) ? wp_unslash( $_POST['pal_menu_icon'][ $menu_item_db_id ] ) : '';
		// Allow letters / numbers / spaces / hyphens / colons (just in case
		// admins paste a class with a brand-icon prefix). No HTML.
		$clean = sanitize_text_field( $raw );
		if ( '' === $clean ) {
			delete_post_meta( $menu_item_db_id, self::META_KEY );
		} else {
			update_post_meta( $menu_item_db_id, self::META_KEY, $clean );
		}
	}

	/** Helper used by NPAL_Mobile_Menu_Walker to fetch the saved icon. */
	public static function get_for_item( $item_id ) {
		return get_post_meta( $item_id, self::META_KEY, true );
	}
}
