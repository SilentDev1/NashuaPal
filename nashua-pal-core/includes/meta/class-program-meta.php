<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Program_Meta {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Program::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_footer-post.php', array( $this, 'maybe_render_icon_modal' ) );
		add_action( 'admin_footer-post-new.php', array( $this, 'maybe_render_icon_modal' ) );

		// Admin list: ★ Featured column with one-click toggle so admins can
		// see + flip the homepage flag without opening each program.
		add_filter( 'manage_' . NPAL_CPT_Program::POST_TYPE . '_posts_columns',           array( $this, 'list_columns' ) );
		add_action( 'manage_' . NPAL_CPT_Program::POST_TYPE . '_posts_custom_column',     array( $this, 'render_list_column' ), 10, 2 );
		add_filter( 'manage_edit-' . NPAL_CPT_Program::POST_TYPE . '_sortable_columns',   array( $this, 'list_columns_sortable' ) );
		add_action( 'pre_get_posts',                                                       array( $this, 'maybe_sort_by_featured' ) );
		add_action( 'admin_post_npal_toggle_program_featured',                             array( $this, 'handle_toggle_featured' ) );
	}

	public function list_columns( $cols ) {
		$new = array();
		foreach ( $cols as $k => $v ) {
			$new[ $k ] = $v;
			if ( 'title' === $k ) {
				$new['pal_featured'] = __( '★ Featured', 'nashua-pal-core' );
			}
		}
		if ( ! isset( $new['pal_featured'] ) ) {
			$new['pal_featured'] = __( '★ Featured', 'nashua-pal-core' );
		}
		return $new;
	}

	public function list_columns_sortable( $cols ) {
		$cols['pal_featured'] = 'pal_featured';
		return $cols;
	}

	public function maybe_sort_by_featured( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) { return; }
		if ( $query->get( 'post_type' ) !== NPAL_CPT_Program::POST_TYPE ) { return; }
		if ( 'pal_featured' !== $query->get( 'orderby' ) ) { return; }
		$query->set( 'meta_key', '_pal_program_show_on_home' );
		$query->set( 'orderby',  'meta_value title' );
	}

	public function render_list_column( $col, $post_id ) {
		if ( 'pal_featured' !== $col ) { return; }
		$is_featured = '1' === get_post_meta( $post_id, '_pal_program_show_on_home', true );
		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=npal_toggle_program_featured&post=' . (int) $post_id ),
			'npal_toggle_featured_' . $post_id
		);
		$label = $is_featured ? __( 'Click to unfeature', 'nashua-pal-core' ) : __( 'Click to feature on homepage', 'nashua-pal-core' );
		?>
		<a href="<?php echo esc_url( $url ); ?>" title="<?php echo esc_attr( $label ); ?>"
			style="text-decoration:none;font-size:22px;line-height:1;display:inline-block;
				color:<?php echo $is_featured ? '#F4A51C' : '#c3c4c7'; ?>;">
			<?php echo $is_featured ? '★' : '☆'; ?>
		</a>
		<?php
	}

	public function handle_toggle_featured() {
		$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : 0;
		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'Not allowed.', 'nashua-pal-core' ) );
		}
		check_admin_referer( 'npal_toggle_featured_' . $post_id );
		if ( get_post_type( $post_id ) !== NPAL_CPT_Program::POST_TYPE ) {
			wp_die( esc_html__( 'Not a program.', 'nashua-pal-core' ) );
		}
		$current = get_post_meta( $post_id, '_pal_program_show_on_home', true );
		update_post_meta( $post_id, '_pal_program_show_on_home', '1' === $current ? '0' : '1' );

		$back = wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=' . NPAL_CPT_Program::POST_TYPE );
		wp_safe_redirect( $back );
		exit;
	}

	public function enqueue_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || NPAL_CPT_Program::POST_TYPE !== $screen->post_type ) { return; }
		NPAL_Icon_Picker::enqueue();
		wp_enqueue_media();
	}

	/**
	 * Only inject the icon picker modal markup when actually editing a Program.
	 * Previously this was hooked unconditionally to admin_footer-post.php /
	 * admin_footer-post-new.php, which dumped ~320 lines of HTML + inline CSS
	 * (including SVG !important rules) + an IIFE into every post.php page —
	 * including Elementor's editor (which loads at post.php?action=elementor),
	 * causing the editor to hang on the "LOADING" splash.
	 */
	public function maybe_render_icon_modal() {
		// Bail if we're inside Elementor's editor or preview iframe — even on
		// program posts there's no need for the icon picker there.
		if (
			( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) ||
			( isset( $_GET['elementor-preview'] ) ) ||
			( class_exists( '\\Elementor\\Plugin' ) && isset( \Elementor\Plugin::$instance ) && (
				( isset( \Elementor\Plugin::$instance->editor )  && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ||
				( isset( \Elementor\Plugin::$instance->preview ) && \Elementor\Plugin::$instance->preview->is_preview_mode() )
			) )
		) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || NPAL_CPT_Program::POST_TYPE !== $screen->post_type ) {
			return;
		}

		NPAL_Icon_Picker::modal();
	}

	public function add_boxes() {
		add_meta_box(
			'pal_program_card',
			__( 'Program Card (homepage)', 'nashua-pal-core' ),
			array( $this, 'render_card' ),
			NPAL_CPT_Program::POST_TYPE,
			'normal',
			'high'
		);
		add_meta_box(
			'pal_program_details',
			__( 'Program Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			NPAL_CPT_Program::POST_TYPE,
			'normal',
			'high'
		);
		add_meta_box(
			'pal_program_youtube_videos',
			__( 'YouTube Videos', 'nashua-pal-core' ),
			array( $this, 'render_youtube_videos' ),
			NPAL_CPT_Program::POST_TYPE,
			'normal',
			'high'
		);
		add_meta_box(
			'pal_program_contacts',
			__( 'Program Contacts', 'nashua-pal-core' ),
			array( $this, 'render_contacts' ),
			NPAL_CPT_Program::POST_TYPE,
			'normal',
			'default'
		);
		add_meta_box(
			'pal_program_coaches',
			__( 'Coaches / Staff', 'nashua-pal-core' ),
			array( $this, 'render_coaches' ),
			NPAL_CPT_Program::POST_TYPE,
			'normal',
			'default'
		);
		add_meta_box(
			'pal_program_scholarship',
			__( 'Scholarship Info', 'nashua-pal-core' ),
			array( $this, 'render_scholarship' ),
			NPAL_CPT_Program::POST_TYPE,
			'normal',
			'default'
		);
		add_meta_box(
			'pal_program_locations',
			__( 'Locations Where Offered', 'nashua-pal-core' ),
			array( $this, 'render_locations' ),
			NPAL_CPT_Program::POST_TYPE,
			'normal',
			'default'
		);
		add_meta_box(
			'pal_program_additional_info',
			__( 'Program Additional Info', 'nashua-pal-core' ),
			array( $this, 'render_additional_info' ),
			NPAL_CPT_Program::POST_TYPE,
			'normal',
			'default'
		);
		add_meta_box(
			'pal_program_sponsors',
			__( 'Tiered Sponsors', 'nashua-pal-core' ),
			array( $this, 'render_sponsors' ),
			NPAL_CPT_Program::POST_TYPE,
			'normal',
			'default'
		);
		add_meta_box(
			'pal_program_gallery',
			__( 'Photo Gallery', 'nashua-pal-core' ),
			array( $this, 'render_gallery' ),
			NPAL_CPT_Program::POST_TYPE,
			'normal',
			'default'
		);
		add_meta_box(
			'pal_program_linked_events',
			__( 'Linked Events', 'nashua-pal-core' ),
			array( $this, 'render_linked_events' ),
			NPAL_CPT_Program::POST_TYPE,
			'side',
			'default'
		);
	}

	/**
	 * Resolve the URL a program card should link to.
	 *
	 * Priority: linked page → custom CTA URL → program permalink.
	 */
	public static function get_program_url( $post_id ) {
		$link_page = (int) get_post_meta( $post_id, '_pal_program_link_page', true );
		if ( $link_page && 'publish' === get_post_status( $link_page ) ) {
			return get_permalink( $link_page );
		}
		$cta = get_post_meta( $post_id, '_pal_program_cta', true );
		if ( $cta ) {
			return $cta;
		}
		return get_permalink( $post_id );
	}

	public function render_card( $post ) {
		wp_nonce_field( 'pal_program_meta', 'pal_program_meta_nonce' );
		$short     = get_post_meta( $post->ID, '_pal_program_short', true );
		$icon      = get_post_meta( $post->ID, '_pal_program_icon', true );
		$cta       = get_post_meta( $post->ID, '_pal_program_cta', true );
		$link_page = get_post_meta( $post->ID, '_pal_program_link_page', true );
		$show      = get_post_meta( $post->ID, '_pal_program_show_on_home', true );
		// Default new programs to "show on homepage" so the homepage populates automatically.
		if ( '' === $show && 'auto-draft' === $post->post_status ) {
			$show = '1';
		}
		?>
		<style>
			.pal-meta-row { display:grid; grid-template-columns: 200px 1fr; gap:14px 18px; align-items:center; margin:14px 0; }
			.pal-meta-row label { font-weight:600; }
			.pal-meta-row input[type=text], .pal-meta-row input[type=url] { width:100%; max-width:520px; padding:8px 10px; }
			.pal-meta-row textarea { width:100%; max-width:520px; padding:8px 10px; min-height:80px; }
			.pal-meta-help { color:#646970; font-size:12px; margin-top:4px; display:block; }
		</style>
		<div class="pal-meta-row">
			<label for="pal_program_short"><?php esc_html_e( 'Short description', 'nashua-pal-core' ); ?></label>
			<div>
				<textarea id="pal_program_short" name="pal_program_short" maxlength="1000"><?php echo esc_textarea( $short ); ?></textarea>
				<span class="pal-meta-help"><?php esc_html_e( 'Shown on the homepage program card (max 1000 characters).', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label><?php esc_html_e( 'Icon', 'nashua-pal-core' ); ?></label>
			<div>
				<?php NPAL_Icon_Picker::field( 'pal_program_icon', $icon, array( 'id' => 'pal_program_icon' ) ); ?>
				<span class="pal-meta-help"><?php esc_html_e( 'Click "Choose icon" to pick a built-in icon, upload a custom one, or paste a Font Awesome class.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_link_page"><?php esc_html_e( 'Link card to page', 'nashua-pal-core' ); ?></label>
			<div>
				<?php wp_dropdown_pages( array(
					'name'             => 'pal_program_link_page',
					'id'               => 'pal_program_link_page',
					'selected'         => $link_page,
					'show_option_none' => __( '— None (use default program page) —', 'nashua-pal-core' ),
					'option_none_value'=> '0',
					'sort_column'      => 'post_title',
					'post_status'      => 'publish',
					'echo'             => true,
				) ); ?>
				<span class="pal-meta-help"><?php esc_html_e( 'When set, clicking this program card opens the selected page instead of the program post.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_cta"><?php esc_html_e( 'Custom URL (override)', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="text" id="pal_program_cta" name="pal_program_cta" value="<?php echo esc_attr( $cta ); ?>" placeholder="https://example.com/foo" />
				<span class="pal-meta-help"><?php esc_html_e( 'Only used if no page is selected above. Links card to an external URL.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<?php $hide_archive = get_post_meta( $post->ID, '_pal_program_hide_from_archive', true ); ?>
		<div class="pal-meta-row">
			<label for="pal_program_show_on_home"><?php esc_html_e( 'Show on homepage?', 'nashua-pal-core' ); ?></label>
			<label><input type="checkbox" id="pal_program_show_on_home" name="pal_program_show_on_home" value="1" <?php checked( $show, '1' ); ?> /> <?php esc_html_e( 'Yes, feature this program on the homepage', 'nashua-pal-core' ); ?></label>
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_hide_from_archive"><?php esc_html_e( 'Hide from archive?', 'nashua-pal-core' ); ?></label>
			<div>
				<label><input type="checkbox" id="pal_program_hide_from_archive" name="pal_program_hide_from_archive" value="1" <?php checked( $hide_archive, '1' ); ?> /> <?php esc_html_e( 'Yes, hide this program from the All Programs page', 'nashua-pal-core' ); ?></label>
				<span class="pal-meta-help"><?php esc_html_e( 'The program can still appear on the homepage if featured above.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<?php
	}

	public function render_details( $post ) {
		$season        = get_post_meta( $post->ID, '_pal_program_season', true );
		$age_range     = get_post_meta( $post->ID, '_pal_program_age_range', true );
		$subtitle      = get_post_meta( $post->ID, '_pal_program_subtitle', true );
		$cta_label     = get_post_meta( $post->ID, '_pal_program_cta_label', true );
		$accreditation = get_post_meta( $post->ID, '_pal_program_accreditation', true );
		$register_url  = get_post_meta( $post->ID, '_pal_program_register_url', true );
		$status        = get_post_meta( $post->ID, '_pal_program_status', true );
		$cost          = get_post_meta( $post->ID, '_pal_program_cost', true );
		?>
		<p class="description" style="margin:0 0 12px 0;">
			<?php esc_html_e( 'These fields appear on the Programs Page accordion and individual program pages.', 'nashua-pal-core' ); ?>
		</p>
		<div class="pal-meta-row">
			<label for="pal_program_status"><?php esc_html_e( 'Program status', 'nashua-pal-core' ); ?></label>
			<div>
				<select id="pal_program_status" name="pal_program_status" style="max-width:520px;padding:8px 10px;">
					<option value=""          <?php selected( $status, '' ); ?>><?php esc_html_e( '— None (no badge)', 'nashua-pal-core' ); ?></option>
					<option value="active"    <?php selected( $status, 'active' ); ?>><?php esc_html_e( 'Active', 'nashua-pal-core' ); ?></option>
					<option value="upcoming"  <?php selected( $status, 'upcoming' ); ?>><?php esc_html_e( 'Upcoming', 'nashua-pal-core' ); ?></option>
					<option value="on_break"  <?php selected( $status, 'on_break' ); ?>><?php esc_html_e( 'On Break / Off-Season', 'nashua-pal-core' ); ?></option>
					<option value="reg_open"  <?php selected( $status, 'reg_open' ); ?>><?php esc_html_e( 'Registration Open', 'nashua-pal-core' ); ?></option>
					<option value="reg_closed" <?php selected( $status, 'reg_closed' ); ?>><?php esc_html_e( 'Registration Closed', 'nashua-pal-core' ); ?></option>
					<option value="inactive"  <?php selected( $status, 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'nashua-pal-core' ); ?></option>
				</select>
				<span class="pal-meta-help"><?php esc_html_e( 'Shows a colored badge on program cards, accordion, and detail pages.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_register_url"><?php esc_html_e( 'Register URL', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="text" id="pal_program_register_url" name="pal_program_register_url" value="<?php echo esc_attr( $register_url ); ?>" placeholder="https://… or /register/" style="width:100%;max-width:520px;padding:8px 10px;" />
				<span class="pal-meta-help"><?php esc_html_e( 'Direct sign-up link (separate from "Learn More" CTA). Shows a "Register" button.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_cost"><?php esc_html_e( 'Cost / Fee', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="text" id="pal_program_cost" name="pal_program_cost" value="<?php echo esc_attr( $cost ); ?>" placeholder="e.g. Free, $50/season, Scholarship available" style="width:100%;max-width:520px;padding:8px 10px;" />
				<span class="pal-meta-help"><?php esc_html_e( 'Shown on program cards and detail pages. Leave blank to hide.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_season"><?php esc_html_e( 'Season', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="text" id="pal_program_season" name="pal_program_season" value="<?php echo esc_attr( $season ); ?>" placeholder="e.g. Fall, Fall and Winter, Year-round" style="width:100%;max-width:520px;padding:8px 10px;" />
				<span class="pal-meta-help"><?php esc_html_e( 'When this program runs. Shown in the accordion header on the Programs page.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_age_range"><?php esc_html_e( 'Age range', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="text" id="pal_program_age_range" name="pal_program_age_range" value="<?php echo esc_attr( $age_range ); ?>" placeholder="e.g. Ages 4-7, Ages 7-18, Age 11 and up" style="width:100%;max-width:520px;padding:8px 10px;" />
				<span class="pal-meta-help"><?php esc_html_e( 'Target age range. Shown in the accordion header on the Programs page.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_subtitle"><?php esc_html_e( 'Accordion subtitle', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="text" id="pal_program_subtitle" name="pal_program_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" placeholder="e.g. Everyone makes a team. Cheer at home games and competitions." style="width:100%;max-width:520px;padding:8px 10px;" />
				<span class="pal-meta-help"><?php esc_html_e( 'Extra detail shown below the title in the accordion header. Optional.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_cta_label"><?php esc_html_e( 'CTA button label', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="text" id="pal_program_cta_label" name="pal_program_cta_label" value="<?php echo esc_attr( $cta_label ); ?>" placeholder="Learn More" style="width:100%;max-width:520px;padding:8px 10px;" />
				<span class="pal-meta-help"><?php esc_html_e( 'Custom label for the CTA button. Leave blank for "Learn More".', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_accreditation"><?php esc_html_e( 'Accreditation', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="text" id="pal_program_accreditation" name="pal_program_accreditation" value="<?php echo esc_attr( $accreditation ); ?>" placeholder="e.g. Accredited with the NH Juvenile Court Diversion Network" style="width:100%;max-width:520px;padding:8px 10px;" />
				<span class="pal-meta-help"><?php esc_html_e( 'Accreditation or certification note. Shown on the program page and accordion.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<?php
	}

	public function render_youtube_videos( $post ) {
		$youtube_videos = get_post_meta( $post->ID, '_pal_program_youtube_urls', true );
		if ( ! is_array( $youtube_videos ) ) { $youtube_videos = array(); }
		// Backward compat: migrate old singular key.
		if ( empty( $youtube_videos ) ) {
			$old_yt = get_post_meta( $post->ID, '_pal_program_youtube_url', true );
			if ( $old_yt ) {
				$youtube_videos = array( array( 'url' => $old_yt, 'label' => '' ) );
			}
		}
		?>
		<p class="description" style="margin:0 0 12px 0;">
			<?php esc_html_e( 'Replaces the featured image on the single program page. Multiple videos auto-rotate. Does not affect program cards.', 'nashua-pal-core' ); ?>
		</p>
		<table class="widefat striped" id="pal-youtube-table">
			<thead>
				<tr>
					<th style="width:65%"><?php esc_html_e( 'YouTube URL', 'nashua-pal-core' ); ?></th>
					<th style="width:29%"><?php esc_html_e( 'Label (optional)', 'nashua-pal-core' ); ?></th>
					<th style="width:6%"></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$yt_rows = empty( $youtube_videos ) ? array( array( 'url' => '', 'label' => '' ) ) : $youtube_videos;
			foreach ( $yt_rows as $i => $yt ) :
				?>
				<tr>
					<td><input type="text" name="pal_program_youtube[<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( $yt['url'] ?? '' ); ?>" placeholder="https://www.youtube.com/watch?v=… or https://youtu.be/…" style="width:100%;padding:8px 10px;box-sizing:border-box;" /></td>
					<td><input type="text" name="pal_program_youtube[<?php echo (int) $i; ?>][label]" value="<?php echo esc_attr( $yt['label'] ?? '' ); ?>" placeholder="e.g. Game highlights" style="width:100%;padding:8px 10px;box-sizing:border-box;" /></td>
					<td style="text-align:center;"><button type="button" class="button-link pal-youtube-remove" aria-label="<?php esc_attr_e( 'Remove row', 'nashua-pal-core' ); ?>" style="color:#b32d2e;font-size:18px;text-decoration:none;">&times;</button></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p style="margin-top:8px;"><button type="button" class="button" id="pal-youtube-add"><?php esc_html_e( '+ Add video', 'nashua-pal-core' ); ?></button></p>
		<script>
		(function(){
			const tbl = document.getElementById('pal-youtube-table');
			document.getElementById('pal-youtube-add').addEventListener('click', function(){
				const tbody = tbl.querySelector('tbody');
				const idx = tbody.children.length;
				const row = document.createElement('tr');
				row.innerHTML =
					'<td><input type="text" name="pal_program_youtube['+idx+'][url]" placeholder="https://www.youtube.com/watch?v=… or https://youtu.be/…" style="width:100%;padding:8px 10px;box-sizing:border-box;" /></td>' +
					'<td><input type="text" name="pal_program_youtube['+idx+'][label]" placeholder="e.g. Game highlights" style="width:100%;padding:8px 10px;box-sizing:border-box;" /></td>' +
					'<td style="text-align:center;"><button type="button" class="button-link pal-youtube-remove" style="color:#b32d2e;font-size:18px;text-decoration:none;">&times;</button></td>';
				tbody.appendChild(row);
			});
			tbl.addEventListener('click', function(e){
				if (e.target.classList.contains('pal-youtube-remove')) {
					const tr = e.target.closest('tr');
					if (tbl.querySelectorAll('tbody tr').length > 1) { tr.remove(); }
					else { tr.querySelectorAll('input').forEach(function(i){ i.value=''; }); }
				}
			});
		})();
		</script>
		<?php
	}

	public function render_contacts( $post ) {
		$contacts = get_post_meta( $post->ID, '_pal_program_contacts', true );
		if ( ! is_array( $contacts ) ) { $contacts = array(); }
		?>
		<p class="description" style="margin:0 0 12px 0;">
			<?php esc_html_e( 'Contact people for this program. Shown on the program detail page and Programs Page section.', 'nashua-pal-core' ); ?>
		</p>
		<table class="widefat striped" id="pal-contacts-table">
			<thead>
				<tr>
					<th style="width:25%"><?php esc_html_e( 'Name', 'nashua-pal-core' ); ?></th>
					<th style="width:25%"><?php esc_html_e( 'Title', 'nashua-pal-core' ); ?></th>
					<th style="width:18%"><?php esc_html_e( 'Phone', 'nashua-pal-core' ); ?></th>
					<th style="width:26%"><?php esc_html_e( 'Email', 'nashua-pal-core' ); ?></th>
					<th style="width:6%"></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$rows = empty( $contacts ) ? array( array( 'name' => '', 'title' => '', 'phone' => '', 'email' => '' ) ) : $contacts;
			foreach ( $rows as $i => $c ) :
				?>
				<tr>
					<td><input type="text" name="pal_program_contact[<?php echo (int) $i; ?>][name]" value="<?php echo esc_attr( $c['name'] ?? '' ); ?>" placeholder="Holly Mara" style="width:100%" /></td>
					<td><input type="text" name="pal_program_contact[<?php echo (int) $i; ?>][title]" value="<?php echo esc_attr( $c['title'] ?? '' ); ?>" placeholder="Director of…" style="width:100%" /></td>
					<td><input type="text" name="pal_program_contact[<?php echo (int) $i; ?>][phone]" value="<?php echo esc_attr( $c['phone'] ?? '' ); ?>" placeholder="603-546-6750" style="width:100%" /></td>
					<td><input type="text" name="pal_program_contact[<?php echo (int) $i; ?>][email]" value="<?php echo esc_attr( $c['email'] ?? '' ); ?>" placeholder="holly@example.com" style="width:100%" /></td>
					<td><button type="button" class="button-link pal-contact-remove" aria-label="<?php esc_attr_e( 'Remove row', 'nashua-pal-core' ); ?>">&times;</button></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" id="pal-contact-add"><?php esc_html_e( '+ Add contact', 'nashua-pal-core' ); ?></button></p>
		<script>
		(function(){
			const tbl = document.getElementById('pal-contacts-table');
			document.getElementById('pal-contact-add').addEventListener('click', function(){
				const tbody = tbl.querySelector('tbody');
				const idx = tbody.children.length;
				const row = document.createElement('tr');
				row.innerHTML =
					'<td><input type="text" name="pal_program_contact['+idx+'][name]" placeholder="Holly Mara" style="width:100%" /></td>' +
					'<td><input type="text" name="pal_program_contact['+idx+'][title]" placeholder="Director of…" style="width:100%" /></td>' +
					'<td><input type="text" name="pal_program_contact['+idx+'][phone]" placeholder="603-546-6750" style="width:100%" /></td>' +
					'<td><input type="text" name="pal_program_contact['+idx+'][email]" placeholder="holly@example.com" style="width:100%" /></td>' +
					'<td><button type="button" class="button-link pal-contact-remove">&times;</button></td>';
				tbody.appendChild(row);
			});
			tbl.addEventListener('click', function(e){
				if (e.target.classList.contains('pal-contact-remove')) {
					const tr = e.target.closest('tr');
					if (tbl.querySelectorAll('tbody tr').length > 1) { tr.remove(); }
					else { tr.querySelectorAll('input').forEach(function(i){ i.value=''; }); }
				}
			});
		})();
		</script>
		<?php
	}

	public function render_coaches( $post ) {
		$coaches = get_post_meta( $post->ID, '_pal_program_coaches', true );
		if ( ! is_array( $coaches ) ) { $coaches = array(); }
		?>
		<p class="description" style="margin:0 0 12px 0;">
			<?php esc_html_e( 'Coaches and staff assigned to this program. Shown on the program detail page.', 'nashua-pal-core' ); ?>
		</p>
		<table class="widefat striped" id="pal-coaches-table">
			<thead>
				<tr>
					<th style="width:30%"><?php esc_html_e( 'Name', 'nashua-pal-core' ); ?></th>
					<th style="width:30%"><?php esc_html_e( 'Role', 'nashua-pal-core' ); ?></th>
					<th style="width:34%"><?php esc_html_e( 'Bio (optional)', 'nashua-pal-core' ); ?></th>
					<th style="width:6%"></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$rows = empty( $coaches ) ? array( array( 'name' => '', 'role' => '', 'bio' => '' ) ) : $coaches;
			foreach ( $rows as $i => $c ) :
				?>
				<tr>
					<td><input type="text" name="pal_program_coach[<?php echo (int) $i; ?>][name]" value="<?php echo esc_attr( $c['name'] ?? '' ); ?>" placeholder="Coach Smith" style="width:100%" /></td>
					<td><input type="text" name="pal_program_coach[<?php echo (int) $i; ?>][role]" value="<?php echo esc_attr( $c['role'] ?? '' ); ?>" placeholder="Head Coach" style="width:100%" /></td>
					<td><input type="text" name="pal_program_coach[<?php echo (int) $i; ?>][bio]" value="<?php echo esc_attr( $c['bio'] ?? '' ); ?>" placeholder="Short bio…" style="width:100%" /></td>
					<td><button type="button" class="button-link pal-coach-remove" aria-label="<?php esc_attr_e( 'Remove row', 'nashua-pal-core' ); ?>">&times;</button></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" id="pal-coach-add"><?php esc_html_e( '+ Add coach', 'nashua-pal-core' ); ?></button></p>
		<script>
		(function(){
			const tbl = document.getElementById('pal-coaches-table');
			document.getElementById('pal-coach-add').addEventListener('click', function(){
				const tbody = tbl.querySelector('tbody');
				const idx = tbody.children.length;
				const row = document.createElement('tr');
				row.innerHTML =
					'<td><input type="text" name="pal_program_coach['+idx+'][name]" placeholder="Coach Smith" style="width:100%" /></td>' +
					'<td><input type="text" name="pal_program_coach['+idx+'][role]" placeholder="Head Coach" style="width:100%" /></td>' +
					'<td><input type="text" name="pal_program_coach['+idx+'][bio]" placeholder="Short bio…" style="width:100%" /></td>' +
					'<td><button type="button" class="button-link pal-coach-remove">&times;</button></td>';
				tbody.appendChild(row);
			});
			tbl.addEventListener('click', function(e){
				if (e.target.classList.contains('pal-coach-remove')) {
					const tr = e.target.closest('tr');
					if (tbl.querySelectorAll('tbody tr').length > 1) { tr.remove(); }
					else { tr.querySelectorAll('input').forEach(function(i){ i.value=''; }); }
				}
			});
		})();
		</script>
		<?php
	}

	public function render_scholarship( $post ) {
		$schol_deadline    = get_post_meta( $post->ID, '_pal_program_schol_deadline', true );
		$schol_amount      = get_post_meta( $post->ID, '_pal_program_schol_amount', true );
		$schol_eligibility = get_post_meta( $post->ID, '_pal_program_schol_eligibility', true );
		$schol_recipients  = get_post_meta( $post->ID, '_pal_program_schol_recipients', true );
		$schol_donate_url  = get_post_meta( $post->ID, '_pal_program_schol_donate_url', true );
		?>
		<p class="description" style="margin:0 0 12px 0;">
			<?php esc_html_e( 'Only for scholarship programs. Leave all fields blank for non-scholarship programs.', 'nashua-pal-core' ); ?>
		</p>
		<div class="pal-meta-row">
			<label for="pal_program_schol_amount"><?php esc_html_e( 'Award amount', 'nashua-pal-core' ); ?></label>
			<input type="text" id="pal_program_schol_amount" name="pal_program_schol_amount" value="<?php echo esc_attr( $schol_amount ); ?>" placeholder="e.g. $500, $1,000, Varies" style="width:100%;max-width:520px;padding:8px 10px;" />
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_schol_deadline"><?php esc_html_e( 'Application deadline', 'nashua-pal-core' ); ?></label>
			<input type="text" id="pal_program_schol_deadline" name="pal_program_schol_deadline" value="<?php echo esc_attr( $schol_deadline ); ?>" placeholder="e.g. March 31, 2026 or Rolling" style="width:100%;max-width:520px;padding:8px 10px;" />
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_schol_eligibility"><?php esc_html_e( 'Eligibility', 'nashua-pal-core' ); ?></label>
			<div>
				<textarea id="pal_program_schol_eligibility" name="pal_program_schol_eligibility" style="width:100%;max-width:520px;padding:8px 10px;min-height:60px;"><?php echo esc_textarea( $schol_eligibility ); ?></textarea>
				<span class="pal-meta-help"><?php esc_html_e( 'Who can apply. HTML is allowed.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_schol_recipients"><?php esc_html_e( 'Past recipients', 'nashua-pal-core' ); ?></label>
			<div>
				<textarea id="pal_program_schol_recipients" name="pal_program_schol_recipients" style="width:100%;max-width:520px;padding:8px 10px;min-height:80px;"><?php echo esc_textarea( $schol_recipients ); ?></textarea>
				<span class="pal-meta-help"><?php esc_html_e( 'One per line: "Name — Year — School". Displayed as a list on the page.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_program_schol_donate_url"><?php esc_html_e( 'Donate URL', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="text" id="pal_program_schol_donate_url" name="pal_program_schol_donate_url" value="<?php echo esc_attr( $schol_donate_url ); ?>" placeholder="https://… or /donate/" style="width:100%;max-width:520px;padding:8px 10px;" />
				<span class="pal-meta-help"><?php esc_html_e( 'Shows a "Support This Scholarship" donate button.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<?php
	}

	public function render_locations( $post ) {
		$locations = get_post_meta( $post->ID, '_pal_program_locations', true );
		if ( ! is_array( $locations ) ) { $locations = array(); }
		?>
		<p class="description" style="margin:0 0 12px 0;">
			<?php esc_html_e( 'Add every location where this program is offered. Each row appears on the program page as a tile.', 'nashua-pal-core' ); ?>
		</p>
		<table class="widefat striped" id="pal-locations-table">
			<thead>
				<tr>
					<th style="width:24%"><?php esc_html_e( 'Location name', 'nashua-pal-core' ); ?></th>
					<th style="width:28%"><?php esc_html_e( 'Address', 'nashua-pal-core' ); ?></th>
					<th style="width:18%"><?php esc_html_e( 'Day / time', 'nashua-pal-core' ); ?></th>
					<th style="width:24%"><?php esc_html_e( 'Register URL', 'nashua-pal-core' ); ?></th>
					<th style="width:6%"></th>
				</tr>
			</thead>
			<tbody>
			<?php
			$rows = empty( $locations ) ? array( array( 'name' => '', 'address' => '', 'when' => '', 'url' => '' ) ) : $locations;
			foreach ( $rows as $i => $loc ) :
				?>
				<tr>
					<td><input type="text" name="pal_program_loc[<?php echo (int) $i; ?>][name]" value="<?php echo esc_attr( $loc['name'] ?? '' ); ?>" style="width:100%" /></td>
					<td><input type="text" name="pal_program_loc[<?php echo (int) $i; ?>][address]" value="<?php echo esc_attr( $loc['address'] ?? '' ); ?>" style="width:100%" /></td>
					<td><input type="text" name="pal_program_loc[<?php echo (int) $i; ?>][when]" value="<?php echo esc_attr( $loc['when'] ?? '' ); ?>" placeholder="Mon · 4–6pm" style="width:100%" /></td>
					<td><input type="text" name="pal_program_loc[<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( $loc['url'] ?? '' ); ?>" placeholder="https://… or /relative-path/" style="width:100%" /></td>
					<td><button type="button" class="button-link pal-loc-remove" aria-label="<?php esc_attr_e( 'Remove row', 'nashua-pal-core' ); ?>">&times;</button></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
		<p><button type="button" class="button" id="pal-loc-add"><?php esc_html_e( '+ Add location', 'nashua-pal-core' ); ?></button></p>
		<script>
		(function(){
			const tbl = document.getElementById('pal-locations-table');
			document.getElementById('pal-loc-add').addEventListener('click', function(){
				const tbody = tbl.querySelector('tbody');
				const idx = tbody.children.length;
				const row = document.createElement('tr');
				row.innerHTML =
					'<td><input type="text" name="pal_program_loc['+idx+'][name]" style="width:100%" /></td>' +
					'<td><input type="text" name="pal_program_loc['+idx+'][address]" style="width:100%" /></td>' +
					'<td><input type="text" name="pal_program_loc['+idx+'][when]" placeholder="Mon · 4–6pm" style="width:100%" /></td>' +
					'<td><input type="text" name="pal_program_loc['+idx+'][url]" placeholder="https://… or /relative-path/" style="width:100%" /></td>' +
					'<td><button type="button" class="button-link pal-loc-remove">&times;</button></td>';
				tbody.appendChild(row);
			});
			tbl.addEventListener('click', function(e){
				if (e.target.classList.contains('pal-loc-remove')) {
					const tr = e.target.closest('tr');
					if (tbl.querySelectorAll('tbody tr').length > 1) { tr.remove(); }
					else { tr.querySelectorAll('input').forEach(function(i){ i.value=''; }); }
				}
			});
		})();
		</script>
		<?php
	}

	public function render_additional_info( $post ) {
		$data = get_post_meta( $post->ID, '_pal_program_additional_info', true );
		if ( ! is_array( $data ) ) { $data = array(); }
		$heading = isset( $data['heading'] ) ? $data['heading'] : '';
		$cards   = isset( $data['cards'] ) && is_array( $data['cards'] ) ? $data['cards'] : array();
		?>
		<p class="description" style="margin:0 0 12px 0;">
			<?php esc_html_e( 'Add info cards displayed as a grid on the program page. Each card has an icon, title, description, and button.', 'nashua-pal-core' ); ?>
		</p>
		<div class="pal-meta-row">
			<label for="pal_addinfo_heading"><?php esc_html_e( 'Section Heading', 'nashua-pal-core' ); ?></label>
			<input type="text" id="pal_addinfo_heading" name="pal_addinfo_heading" value="<?php echo esc_attr( $heading ); ?>" placeholder="e.g. Explore Mentoring Opportunities" style="width:100%;max-width:520px;padding:8px 10px;" />
		</div>
		<hr style="margin:18px 0;" />
		<style>
			.pal-addinfo-cards { display:flex; flex-direction:column; gap:20px; }
			.pal-addinfo-card { background:#f9f9f9; border:1px solid #dcdcde; border-radius:8px; padding:18px 20px; position:relative; }
			.pal-addinfo-card__row { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:14px; margin-bottom:14px; }
			.pal-addinfo-card__row label { display:block; font-weight:600; font-size:12px; margin-bottom:4px; color:#1d2327; }
			.pal-addinfo-card__row input { width:100%; padding:6px 8px; }
			.pal-addinfo-card__desc-label { font-weight:600; font-size:12px; margin-bottom:4px; color:#1d2327; display:block; }
			.pal-addinfo-card__remove { position:absolute; top:10px; right:12px; background:none; border:0; color:#b32d2e; font-size:20px; cursor:pointer; line-height:1; padding:4px; }
			.pal-addinfo-card__remove:hover { color:#a00; }
			.pal-addinfo-card .wp-editor-wrap { margin-bottom:0; }
		</style>
		<div class="pal-addinfo-cards" id="pal-addinfo-cards">
		<?php
		$rows = empty( $cards ) ? array( array( 'icon' => '', 'title' => '', 'desc' => '', 'btn_label' => '', 'btn_url' => '' ) ) : $cards;
		foreach ( $rows as $i => $card ) :
			$editor_id = 'pal_addinfo_desc_' . (int) $i;
			?>
			<div class="pal-addinfo-card" data-index="<?php echo (int) $i; ?>">
				<button type="button" class="pal-addinfo-card__remove pal-addinfo-remove" aria-label="<?php esc_attr_e( 'Remove card', 'nashua-pal-core' ); ?>">&times;</button>
				<div class="pal-addinfo-card__row">
					<div>
						<label><?php esc_html_e( 'Icon', 'nashua-pal-core' ); ?></label>
						<?php NPAL_Icon_Picker::field( 'pal_addinfo_card[' . (int) $i . '][icon]', $card['icon'] ?? '', array( 'id' => 'pal-addinfo-icon-' . (int) $i ) ); ?>
					</div>
					<div>
						<label><?php esc_html_e( 'Title', 'nashua-pal-core' ); ?></label>
						<input type="text" name="pal_addinfo_card[<?php echo (int) $i; ?>][title]" value="<?php echo esc_attr( $card['title'] ?? '' ); ?>" placeholder="Card title" />
					</div>
					<div>
						<label><?php esc_html_e( 'Button Label', 'nashua-pal-core' ); ?></label>
						<input type="text" name="pal_addinfo_card[<?php echo (int) $i; ?>][btn_label]" value="<?php echo esc_attr( $card['btn_label'] ?? '' ); ?>" placeholder="Learn More" />
					</div>
					<div>
						<label><?php esc_html_e( 'Button URL', 'nashua-pal-core' ); ?></label>
						<input type="text" name="pal_addinfo_card[<?php echo (int) $i; ?>][btn_url]" value="<?php echo esc_attr( $card['btn_url'] ?? '' ); ?>" placeholder="https://… or /path/" />
					</div>
				</div>
				<span class="pal-addinfo-card__desc-label"><?php esc_html_e( 'Description', 'nashua-pal-core' ); ?></span>
				<?php
				wp_editor( $card['desc'] ?? '', $editor_id, array(
					'textarea_name' => 'pal_addinfo_card[' . (int) $i . '][desc]',
					'media_buttons' => false,
					'teeny'         => true,
					'textarea_rows' => 4,
					'quicktags'     => array( 'buttons' => 'strong,em,link,ul,ol,li' ),
				) );
				?>
			</div>
		<?php endforeach; ?>
		</div>
		<p><button type="button" class="button" id="pal-addinfo-add"><?php esc_html_e( '+ Add card', 'nashua-pal-core' ); ?></button></p>
		<script>
		(function(){
			var wrap = document.getElementById('pal-addinfo-cards');

			document.getElementById('pal-addinfo-add').addEventListener('click', function(){
				var idx = wrap.querySelectorAll('.pal-addinfo-card').length;
				var edId = 'pal_addinfo_desc_' + idx;
				var card = document.createElement('div');
				card.className = 'pal-addinfo-card';
				card.dataset.index = idx;
				card.innerHTML =
					'<button type="button" class="pal-addinfo-card__remove pal-addinfo-remove" aria-label="Remove card">&times;</button>' +
					'<div class="pal-addinfo-card__row">' +
						'<div><label>Icon</label><input type="text" name="pal_addinfo_card['+idx+'][icon]" placeholder="Choose icon" /></div>' +
						'<div><label>Title</label><input type="text" name="pal_addinfo_card['+idx+'][title]" placeholder="Card title" /></div>' +
						'<div><label>Button Label</label><input type="text" name="pal_addinfo_card['+idx+'][btn_label]" placeholder="Learn More" /></div>' +
						'<div><label>Button URL</label><input type="text" name="pal_addinfo_card['+idx+'][btn_url]" placeholder="https://… or /path/" /></div>' +
					'</div>' +
					'<span class="pal-addinfo-card__desc-label">Description</span>' +
					'<textarea id="'+edId+'" name="pal_addinfo_card['+idx+'][desc]" style="width:100%;min-height:120px;" placeholder="Card description — use the toolbar after saving for rich editing"></textarea>';
				wrap.appendChild(card);
				// Try to init WP editor on the new textarea.
				if (window.wp && wp.editor && wp.editor.initialize) {
					wp.editor.initialize(edId, {
						tinymce: { toolbar1: 'bold,italic,link,bullist,numlist,undo,redo', menubar: false, branding: false },
						quicktags: { buttons: 'strong,em,link,ul,ol,li' },
						mediaButtons: false
					});
				}
			});

			// Sync TinyMCE content before form submission so textarea has the latest HTML.
			var form = wrap.closest('form');
			if (form) {
				form.addEventListener('submit', function(){
					if (window.tinyMCE) {
						tinyMCE.triggerSave();
					}
				});
			}

			wrap.addEventListener('click', function(e){
				if (e.target.classList.contains('pal-addinfo-remove')) {
					var card = e.target.closest('.pal-addinfo-card');
					var cards = wrap.querySelectorAll('.pal-addinfo-card');
					if (cards.length > 1) {
						// Destroy TinyMCE instance before removing DOM node.
						var ed = card.querySelector('.wp-editor-area, textarea[id^="pal_addinfo_desc_"]');
						if (ed && window.wp && wp.editor && wp.editor.remove) {
							wp.editor.remove(ed.id);
						}
						card.remove();
					} else {
						card.querySelectorAll('input').forEach(function(i){ i.value=''; });
						var edEl = card.querySelector('.wp-editor-area, textarea[id^="pal_addinfo_desc_"]');
						if (edEl && window.tinyMCE) {
							var inst = tinyMCE.get(edEl.id);
							if (inst) { inst.setContent(''); }
						}
						if (edEl) { edEl.value = ''; }
					}
				}
			});
		})();
		</script>
		<?php
	}

	public function render_sponsors( $post ) {
		if ( ! class_exists( 'NPAL_CPT_Sponsor' ) ) {
			echo '<p>' . esc_html__( 'Sponsor system not available.', 'nashua-pal-core' ) . '</p>';
			return;
		}

		$q = new WP_Query( array(
			'post_type'      => NPAL_CPT_Sponsor::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_key'       => '_pal_sponsor_order',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array(
				array( 'key' => '_pal_sponsor_context', 'value' => 'program' ),
			),
			'no_found_rows'  => true,
		) );

		$linked_raw    = get_post_meta( $post->ID, '_pal_program_linked_sponsors', true );
		$linked_ids    = is_array( $linked_raw ) ? array_map( 'absint', $linked_raw ) : array();
		$linked_lookup = array_flip( $linked_ids );
		?>
		<style>
			.pal-sponsor-grid-program { display:flex; flex-wrap:wrap; gap:8px; max-width:760px; max-height:260px; overflow-y:auto; padding:10px; border:1px solid #DCDCDE; border-radius:8px; background:#FAFAFA; }
			.pal-sponsor-card-program { display:inline-flex; align-items:center; gap:6px; padding:5px 10px 5px 6px; border:1px solid #DCDCDE; border-radius:999px; background:#fff; cursor:pointer; transition:border-color .15s, background .15s; white-space:nowrap; }
			.pal-sponsor-card-program:hover { border-color:#2271b1; }
			.pal-sponsor-card-program input[type=checkbox] { margin:0; flex-shrink:0; width:14px; height:14px; }
			.pal-sponsor-card-program img { width:26px; height:26px; object-fit:contain; border-radius:50%; background:#F0F0F1; flex-shrink:0; }
			.pal-sponsor-card-program .pal-sponsor-card__placeholder { width:26px; height:26px; border-radius:50%; background:#F0F0F1; display:flex; align-items:center; justify-content:center; color:#A7AAAD; font-size:9px; flex-shrink:0; }
			.pal-sponsor-card-program span.pal-sponsor-card__name { font-size:12px; flex:1; line-height:1.2; }
			.pal-sponsor-card-program.is-checked { border-color:#F4A51C; background:#FFFBF0; }
		</style>
		<p style="margin:0 0 10px;color:#555;font-size:13px;">
			<?php
			printf(
				esc_html__( 'Select tiered sponsors for this program. Create sponsors under %s first.', 'nashua-pal-core' ),
				'<a href="' . esc_url( admin_url( 'edit.php?post_type=' . NPAL_CPT_Sponsor::POST_TYPE . '&pal_context=program' ) ) . '">' . esc_html__( 'Programs → Sponsors', 'nashua-pal-core' ) . '</a>'
			);
			?>
		</p>
		<?php if ( $q->have_posts() ) : ?>
			<div class="pal-sponsor-grid-program" id="pal-program-sponsor-grid">
				<?php while ( $q->have_posts() ) : $q->the_post();
					$sid        = get_the_ID();
					$is_checked = isset( $linked_lookup[ $sid ] );
					$thumb      = get_the_post_thumbnail_url( $sid, 'thumbnail' );
					$level_term = NPAL_CPT_Sponsor::get_sponsor_level( $sid );
					$badge_style = $level_term ? NPAL_CPT_Sponsor::get_level_badge_style( $level_term ) : '';
					?>
					<label class="pal-sponsor-card-program<?php echo $is_checked ? ' is-checked' : ''; ?>">
						<input type="checkbox" name="pal_program_linked_sponsors[]" value="<?php echo (int) $sid; ?>" <?php checked( $is_checked ); ?> />
						<?php if ( $thumb ) : ?>
							<img src="<?php echo esc_url( $thumb ); ?>" alt="" />
						<?php else : ?>
							<span class="pal-sponsor-card__placeholder"><?php esc_html_e( 'No logo', 'nashua-pal-core' ); ?></span>
						<?php endif; ?>
						<span class="pal-sponsor-card__name"><?php echo esc_html( get_the_title( $sid ) ); ?></span>
						<?php if ( $level_term ) : ?>
							<span style="font-size:9px;padding:1px 5px;border-radius:3px;margin-left:2px;white-space:nowrap;<?php echo esc_attr( $badge_style ); ?>"><?php echo esc_html( $level_term->name ); ?></span>
						<?php endif; ?>
					</label>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
			<p style="color:#646970;font-size:12px;margin-top:6px;"><?php esc_html_e( 'These render via [pal_sponsors] with level badges and a scrolling marquee.', 'nashua-pal-core' ); ?></p>
			<script>
			(function(){
				document.querySelectorAll('.pal-sponsor-card-program input[type=checkbox]').forEach(function(cb){
					cb.addEventListener('change', function(){
						cb.closest('.pal-sponsor-card-program').classList.toggle('is-checked', cb.checked);
					});
				});
			})();
			</script>
		<?php else : ?>
			<p style="color:#646970;font-size:13px;">
				<?php
				printf(
					esc_html__( 'No program sponsors yet. %s to get started.', 'nashua-pal-core' ),
					'<a href="' . esc_url( admin_url( 'post-new.php?post_type=' . NPAL_CPT_Sponsor::POST_TYPE . '&pal_context=program' ) ) . '">' . esc_html__( 'Create one', 'nashua-pal-core' ) . '</a>'
				);
				?>
			</p>
		<?php endif;
	}

	public function render_linked_events( $post ) {
		$events = get_posts( array(
			'post_type'      => NPAL_CPT_Event::POST_TYPE,
			'posts_per_page' => 20,
			'post_status'    => array( 'publish', 'draft', 'future' ),
			'meta_query'     => array(
				array( 'key' => '_pal_event_program', 'value' => (string) $post->ID ),
			),
			'orderby'        => 'meta_value',
			'meta_key'       => '_pal_event_start',
			'order'          => 'ASC',
		) );

		if ( empty( $events ) ) {
			echo '<p style="color:#646970;font-size:13px;margin:0 0 12px;">' . esc_html__( 'No events linked to this program yet.', 'nashua-pal-core' ) . '</p>';
		} else {
			echo '<ul style="margin:0 0 12px;padding:0;list-style:none;">';
			foreach ( $events as $ev ) {
				$start = get_post_meta( $ev->ID, '_pal_event_start', true );
				$date  = $start ? wp_date( 'M j, Y', strtotime( $start ) ) : '';
				$label = esc_html( $ev->post_title );
				if ( $date ) { $label .= ' <span style="color:#646970;font-size:11px;">(' . esc_html( $date ) . ')</span>'; }
				$status_label = '';
				if ( 'draft' === $ev->post_status ) { $status_label = ' <span style="color:#d63638;font-size:11px;">[Draft]</span>'; }
				if ( 'future' === $ev->post_status ) { $status_label = ' <span style="color:#2271b1;font-size:11px;">[Scheduled]</span>'; }
				echo '<li style="padding:5px 0;border-bottom:1px solid #f0f0f1;font-size:13px;">';
				echo '<a href="' . esc_url( get_edit_post_link( $ev->ID ) ) . '" style="text-decoration:none;">' . $label . '</a>' . $status_label;
				echo '</li>';
			}
			echo '</ul>';
		}

		$add_url = admin_url( 'post-new.php?post_type=' . NPAL_CPT_Event::POST_TYPE . '&pal_link_program=' . (int) $post->ID );
		echo '<a href="' . esc_url( $add_url ) . '" class="button" style="width:100%;text-align:center;">';
		echo esc_html__( '+ Create Event for This Program', 'nashua-pal-core' );
		echo '</a>';
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Photo Gallery                                              */
	/* ------------------------------------------------------------------ */

	public function render_gallery( $post ) {
		$gallery     = get_post_meta( $post->ID, '_pal_program_gallery', true );
		$gallery_ids = ! empty( $gallery ) ? array_filter( array_map( 'absint', explode( ',', $gallery ) ) ) : array();
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Photo Gallery', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<div id="pal-program-gallery-preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px;">
						<?php foreach ( $gallery_ids as $img_id ) :
							$thumb = wp_get_attachment_image_url( $img_id, 'thumbnail' );
							if ( ! $thumb ) continue;
						?>
							<div class="pal-program-gallery-thumb" data-id="<?php echo esc_attr( $img_id ); ?>" style="position:relative;width:80px;height:80px;">
								<img src="<?php echo esc_url( $thumb ); ?>" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:2px solid #ddd;" />
								<button type="button" class="pal-program-gallery-remove" style="position:absolute;top:-6px;right:-6px;background:#d63638;color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:12px;line-height:20px;cursor:pointer;padding:0;">&times;</button>
							</div>
						<?php endforeach; ?>
					</div>
					<input type="hidden" id="pal_program_gallery" name="pal_program_gallery"
						value="<?php echo esc_attr( implode( ',', $gallery_ids ) ); ?>" />
					<button type="button" id="pal-program-gallery-add" class="button">
						<span class="dashicons dashicons-images-alt2" style="vertical-align:middle;margin-right:4px;"></span>
						<?php esc_html_e( 'Add / Manage Photos', 'nashua-pal-core' ); ?>
					</button>
					<p class="description"><?php esc_html_e( 'Select multiple images to display in a gallery on the program page. Drag to reorder in the media modal.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
		</table>

		<script>
		jQuery(function($){
			var $preview = $('#pal-program-gallery-preview'),
				$input   = $('#pal_program_gallery');

			function refreshInput() {
				var ids = [];
				$preview.find('.pal-program-gallery-thumb').each(function(){
					ids.push( $(this).data('id') );
				});
				$input.val( ids.join(',') );
			}

			$preview.on('click', '.pal-program-gallery-remove', function(e){
				e.preventDefault();
				$(this).closest('.pal-program-gallery-thumb').remove();
				refreshInput();
			});

			$('#pal-program-gallery-add').on('click', function(e){
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

						if ( $preview.find('[data-id="' + id + '"]').length ) return;

						$preview.append(
							'<div class="pal-program-gallery-thumb" data-id="' + id + '" style="position:relative;width:80px;height:80px;">'
							+ '<img src="' + thumb + '" style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:2px solid #ddd;" />'
							+ '<button type="button" class="pal-program-gallery-remove" style="position:absolute;top:-6px;right:-6px;background:#d63638;color:#fff;border:none;border-radius:50%;width:20px;height:20px;font-size:12px;line-height:20px;cursor:pointer;padding:0;">&times;</button>'
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

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST['pal_program_meta_nonce'] ) || ! wp_verify_nonce( $_POST['pal_program_meta_nonce'], 'pal_program_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		$short     = isset( $_POST['pal_program_short'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pal_program_short'] ) ) : '';
		$icon      = isset( $_POST['pal_program_icon'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_program_icon'] ) ) : '';
		$cta       = isset( $_POST['pal_program_cta'] ) ? esc_url_raw( wp_unslash( $_POST['pal_program_cta'] ) ) : '';
		$link_page = isset( $_POST['pal_program_link_page'] ) ? absint( $_POST['pal_program_link_page'] ) : 0;
		$show      = ! empty( $_POST['pal_program_show_on_home'] ) ? '1' : '0';

		update_post_meta( $post_id, '_pal_program_short', $short );
		update_post_meta( $post_id, '_pal_program_icon', $icon );
		update_post_meta( $post_id, '_pal_program_cta', $cta );
		$hide_archive = ! empty( $_POST['pal_program_hide_from_archive'] ) ? '1' : '0';

		update_post_meta( $post_id, '_pal_program_link_page', $link_page );
		update_post_meta( $post_id, '_pal_program_show_on_home', $show );
		update_post_meta( $post_id, '_pal_program_hide_from_archive', $hide_archive );

		// Program Details fields.
		$season        = isset( $_POST['pal_program_season'] )        ? sanitize_text_field( wp_unslash( $_POST['pal_program_season'] ) )        : '';
		$age_range     = isset( $_POST['pal_program_age_range'] )     ? sanitize_text_field( wp_unslash( $_POST['pal_program_age_range'] ) )     : '';
		$subtitle      = isset( $_POST['pal_program_subtitle'] )      ? sanitize_text_field( wp_unslash( $_POST['pal_program_subtitle'] ) )      : '';
		$cta_label     = isset( $_POST['pal_program_cta_label'] )     ? sanitize_text_field( wp_unslash( $_POST['pal_program_cta_label'] ) )     : '';
		$accreditation = isset( $_POST['pal_program_accreditation'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_program_accreditation'] ) ) : '';

		update_post_meta( $post_id, '_pal_program_season', $season );
		update_post_meta( $post_id, '_pal_program_age_range', $age_range );
		update_post_meta( $post_id, '_pal_program_subtitle', $subtitle );
		update_post_meta( $post_id, '_pal_program_cta_label', $cta_label );
		update_post_meta( $post_id, '_pal_program_accreditation', $accreditation );

		// New v1.11 fields: status, register URL, cost.
		$status_raw   = isset( $_POST['pal_program_status'] )       ? sanitize_text_field( wp_unslash( $_POST['pal_program_status'] ) )       : '';
		$status       = in_array( $status_raw, array( 'active', 'upcoming', 'on_break', 'reg_open', 'reg_closed', 'inactive' ), true ) ? $status_raw : '';
		$register_url = isset( $_POST['pal_program_register_url'] ) ? esc_url_raw( wp_unslash( $_POST['pal_program_register_url'] ) )        : '';
		$cost         = isset( $_POST['pal_program_cost'] )         ? sanitize_text_field( wp_unslash( $_POST['pal_program_cost'] ) )         : '';
		update_post_meta( $post_id, '_pal_program_status', $status );
		update_post_meta( $post_id, '_pal_program_register_url', $register_url );
		update_post_meta( $post_id, '_pal_program_cost', $cost );

		// YouTube videos repeater.
		$youtube_videos = array();
		if ( isset( $_POST['pal_program_youtube'] ) && is_array( $_POST['pal_program_youtube'] ) ) {
			foreach ( wp_unslash( $_POST['pal_program_youtube'] ) as $yt ) {
				$row = array(
					'url'   => esc_url_raw( $yt['url'] ?? '' ),
					'label' => sanitize_text_field( $yt['label'] ?? '' ),
				);
				if ( $row['url'] ) {
					$youtube_videos[] = $row;
				}
			}
		}
		update_post_meta( $post_id, '_pal_program_youtube_urls', $youtube_videos );
		// Clean up old singular key if new repeater has data.
		if ( ! empty( $youtube_videos ) ) {
			delete_post_meta( $post_id, '_pal_program_youtube_url' );
		}

		// Coaches repeater.
		$coaches = array();
		if ( isset( $_POST['pal_program_coach'] ) && is_array( $_POST['pal_program_coach'] ) ) {
			foreach ( wp_unslash( $_POST['pal_program_coach'] ) as $c ) {
				$row = array(
					'name' => sanitize_text_field( $c['name'] ?? '' ),
					'role' => sanitize_text_field( $c['role'] ?? '' ),
					'bio'  => sanitize_text_field( $c['bio'] ?? '' ),
				);
				if ( $row['name'] || $row['role'] ) {
					$coaches[] = $row;
				}
			}
		}
		update_post_meta( $post_id, '_pal_program_coaches', $coaches );

		// Scholarship fields.
		$schol_amount      = isset( $_POST['pal_program_schol_amount'] )      ? sanitize_text_field( wp_unslash( $_POST['pal_program_schol_amount'] ) )      : '';
		$schol_deadline    = isset( $_POST['pal_program_schol_deadline'] )    ? sanitize_text_field( wp_unslash( $_POST['pal_program_schol_deadline'] ) )    : '';
		$schol_eligibility = isset( $_POST['pal_program_schol_eligibility'] ) ? sanitize_textarea_field( wp_unslash( $_POST['pal_program_schol_eligibility'] ) ) : '';
		$schol_recipients  = isset( $_POST['pal_program_schol_recipients'] )  ? sanitize_textarea_field( wp_unslash( $_POST['pal_program_schol_recipients'] ) )  : '';
		$schol_donate_url  = isset( $_POST['pal_program_schol_donate_url'] )  ? esc_url_raw( wp_unslash( $_POST['pal_program_schol_donate_url'] ) )              : '';

		update_post_meta( $post_id, '_pal_program_schol_amount', $schol_amount );
		update_post_meta( $post_id, '_pal_program_schol_deadline', $schol_deadline );
		update_post_meta( $post_id, '_pal_program_schol_eligibility', $schol_eligibility );
		update_post_meta( $post_id, '_pal_program_schol_recipients', $schol_recipients );
		update_post_meta( $post_id, '_pal_program_schol_donate_url', $schol_donate_url );

		// Contacts repeater.
		$contacts = array();
		if ( isset( $_POST['pal_program_contact'] ) && is_array( $_POST['pal_program_contact'] ) ) {
			foreach ( wp_unslash( $_POST['pal_program_contact'] ) as $c ) {
				$row = array(
					'name'  => sanitize_text_field( $c['name'] ?? '' ),
					'title' => sanitize_text_field( $c['title'] ?? '' ),
					'phone' => sanitize_text_field( $c['phone'] ?? '' ),
					'email' => sanitize_email( $c['email'] ?? '' ),
				);
				if ( $row['name'] || $row['title'] || $row['phone'] || $row['email'] ) {
					$contacts[] = $row;
				}
			}
		}
		update_post_meta( $post_id, '_pal_program_contacts', $contacts );

		// Locations repeater.
		$locs = array();
		if ( isset( $_POST['pal_program_loc'] ) && is_array( $_POST['pal_program_loc'] ) ) {
			foreach ( wp_unslash( $_POST['pal_program_loc'] ) as $loc ) {
				$row = array(
					'name'    => sanitize_text_field( $loc['name'] ?? '' ),
					'address' => sanitize_text_field( $loc['address'] ?? '' ),
					'when'    => sanitize_text_field( $loc['when'] ?? '' ),
					'url'     => esc_url_raw( $loc['url'] ?? '' ),
				);
				if ( $row['name'] || $row['address'] || $row['when'] || $row['url'] ) {
					$locs[] = $row;
				}
			}
		}
		update_post_meta( $post_id, '_pal_program_locations', $locs );

		// Tiered sponsors — pal_sponsor IDs linked to this program.
		$linked_sponsors_raw = isset( $_POST['pal_program_linked_sponsors'] ) ? (array) $_POST['pal_program_linked_sponsors'] : array();
		$linked_sponsors     = array_values( array_unique( array_filter( array_map( 'absint', $linked_sponsors_raw ) ) ) );
		$old_linked_sp       = get_post_meta( $post_id, '_pal_program_linked_sponsors', true );
		$old_linked_sp       = is_array( $old_linked_sp ) ? array_map( 'absint', $old_linked_sp ) : array();

		if ( ! empty( $linked_sponsors ) ) {
			update_post_meta( $post_id, '_pal_program_linked_sponsors', $linked_sponsors );
		} else {
			delete_post_meta( $post_id, '_pal_program_linked_sponsors' );
		}

		// Sync reverse: update each sponsor's _pal_sponsor_linked_posts.
		$removed_sp = array_diff( $old_linked_sp, $linked_sponsors );
		foreach ( $removed_sp as $sid ) {
			$sp_linked = get_post_meta( $sid, '_pal_sponsor_linked_posts', true );
			if ( is_array( $sp_linked ) ) {
				$sp_linked = array_values( array_diff( $sp_linked, array( $post_id ) ) );
				if ( ! empty( $sp_linked ) ) {
					update_post_meta( $sid, '_pal_sponsor_linked_posts', $sp_linked );
				} else {
					delete_post_meta( $sid, '_pal_sponsor_linked_posts' );
				}
			}
		}
		$added_sp = array_diff( $linked_sponsors, $old_linked_sp );
		foreach ( $added_sp as $sid ) {
			$sp_linked = get_post_meta( $sid, '_pal_sponsor_linked_posts', true );
			$sp_linked = is_array( $sp_linked ) ? $sp_linked : array();
			if ( ! in_array( $post_id, $sp_linked, true ) ) {
				$sp_linked[] = $post_id;
				update_post_meta( $sid, '_pal_sponsor_linked_posts', array_values( $sp_linked ) );
			}
		}

		// Additional Info cards.
		$addinfo = array(
			'heading' => isset( $_POST['pal_addinfo_heading'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_addinfo_heading'] ) ) : '',
			'cards'   => array(),
		);
		if ( isset( $_POST['pal_addinfo_card'] ) && is_array( $_POST['pal_addinfo_card'] ) ) {
			foreach ( wp_unslash( $_POST['pal_addinfo_card'] ) as $card ) {
				$row = array(
					'icon'      => sanitize_text_field( $card['icon'] ?? '' ),
					'title'     => sanitize_text_field( $card['title'] ?? '' ),
					'desc'      => wp_kses_post( $card['desc'] ?? '' ),
					'btn_label' => sanitize_text_field( $card['btn_label'] ?? '' ),
					'btn_url'   => esc_url_raw( $card['btn_url'] ?? '' ),
				);
				if ( $row['icon'] || $row['title'] || $row['desc'] ) {
					$addinfo['cards'][] = $row;
				}
			}
		}
		update_post_meta( $post_id, '_pal_program_additional_info', $addinfo );

		// Photo Gallery (comma-separated attachment IDs).
		if ( isset( $_POST['pal_program_gallery'] ) ) {
			$raw_ids   = sanitize_text_field( wp_unslash( $_POST['pal_program_gallery'] ) );
			$clean_ids = implode( ',', array_filter( array_map( 'absint', explode( ',', $raw_ids ) ) ) );
			update_post_meta( $post_id, '_pal_program_gallery', $clean_ids );
		}
	}
}
