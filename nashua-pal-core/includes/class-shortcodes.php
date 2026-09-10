<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcodes that templates use to render dynamic content.
 * Each shortcode produces a tightly-scoped HTML fragment that the Elementor
 * templates can drop in as an HTML widget.
 */
final class NPAL_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'pal_logo',             array( $this, 'logo' ) );
		add_shortcode( 'pal_promo_bar',        array( $this, 'promo_bar' ) );
		add_shortcode( 'pal_alert',            array( $this, 'alert' ) );
		add_shortcode( 'pal_stats_bar',        array( $this, 'stats_bar' ) );
		add_shortcode( 'pal_stat',             array( $this, 'stat' ) );
		add_shortcode( 'pal_program_cards',    array( $this, 'program_cards' ) );
		add_shortcode( 'pal_program',          array( $this, 'program_single' ) );
		add_shortcode( 'pal_program_info_cards', array( $this, 'program_info_cards' ) );
		add_shortcode( 'pal_programs_page',    array( $this, 'programs_page' ) );
		add_shortcode( 'pal_upcoming_events',  array( $this, 'upcoming_events' ) );
		add_shortcode( 'pal_events',           array( $this, 'events' ) );
		add_shortcode( 'pal_event_date_pill',  array( $this, 'event_date_pill' ) );
		add_shortcode( 'pal_get_involved',     array( $this, 'get_involved' ) );
		add_shortcode( 'pal_latest_news',      array( $this, 'latest_news' ) );
		add_shortcode( 'pal_partner_carousel',      array( $this, 'partner_carousel' ) );
		add_shortcode( 'pal_community_partners',    array( $this, 'community_partners' ) );
		add_shortcode( 'pal_partner_single',        array( $this, 'partner_single' ) );
		add_shortcode( 'pal_search',           array( $this, 'search' ) );
		add_shortcode( 'pal_language',         array( $this, 'language' ) );
		add_shortcode( 'pal_socials',          array( $this, 'socials' ) );
		add_shortcode( 'pal_donate_button',    array( $this, 'donate_button' ) );
		add_shortcode( 'pal_phone',            array( $this, 'phone' ) );
		add_shortcode( 'pal_email',            array( $this, 'email' ) );
		add_shortcode( 'pal_address',          array( $this, 'address' ) );
		add_shortcode( 'pal_newsletter',       array( $this, 'newsletter' ) );
		add_shortcode( 'pal_underline',        array( $this, 'underline' ) );
		add_shortcode( 'pal_footer_legal',     array( $this, 'footer_legal' ) );
		add_shortcode( 'pal_mobile_menu_button', array( $this, 'mobile_menu_button' ) );
		add_shortcode( 'pal_mobile_menu',        array( $this, 'mobile_menu' ) );
		add_shortcode( 'pal_awardees',           array( $this, 'awardees' ) );
		add_shortcode( 'pal_event_sponsors',     array( $this, 'event_sponsors' ) );
		add_shortcode( 'pal_event_embed',        array( $this, 'event_embed' ) );
		add_shortcode( 'pal_previous_champions', array( $this, 'previous_champions' ) );
		add_shortcode( 'pal_job_listings',       array( $this, 'job_listings' ) );
		add_shortcode( 'pal_job_single',         array( $this, 'job_single' ) );
		add_shortcode( 'pal_news_archive',       array( $this, 'news_archive' ) );
	}

	/* -------- helpers -------- */
	private function opt( $key, $default = '' ) {
		return NPAL_Plugin::get_setting( $key, $default );
	}

	/** Human-readable registration status labels. */
	private function reg_status_label( $status ) {
		$map = array(
			'open'    => __( 'Registration Open', 'nashua-pal-core' ),
			'closing' => __( 'Closing Soon', 'nashua-pal-core' ),
			'full'    => __( 'Full', 'nashua-pal-core' ),
			'waitlist'=> __( 'Waitlist', 'nashua-pal-core' ),
			'closed'  => __( 'Closed', 'nashua-pal-core' ),
		);
		return $map[ $status ] ?? ucfirst( str_replace( '_', ' ', $status ) );
	}

	/** Human-readable program status labels. */
	private function program_status_label( $status ) {
		$map = array(
			'active'     => __( 'Active', 'nashua-pal-core' ),
			'upcoming'   => __( 'Upcoming', 'nashua-pal-core' ),
			'on_break'   => __( 'On Break', 'nashua-pal-core' ),
			'reg_open'   => __( 'Registration Open', 'nashua-pal-core' ),
			'reg_closed' => __( 'Registration Closed', 'nashua-pal-core' ),
			'inactive'   => __( 'Inactive', 'nashua-pal-core' ),
		);
		return $map[ $status ] ?? ucfirst( str_replace( '_', ' ', $status ) );
	}

	/** Build a Google Calendar "Add Event" URL. */
	private function build_gcal_url( $title, $start_ts, $end_ts, $location = '', $details_url = '' ) {
		$fmt   = 'Ymd\THis\Z';
		$start = gmdate( $fmt, $start_ts );
		$end   = gmdate( $fmt, $end_ts );
		$params = array(
			'action'   => 'TEMPLATE',
			'text'     => $title,
			'dates'    => $start . '/' . $end,
			'location' => $location,
			'details'  => $details_url ? sprintf( __( 'More info: %s', 'nashua-pal-core' ), $details_url ) : '',
		);
		return 'https://calendar.google.com/calendar/render?' . http_build_query( array_filter( $params ) );
	}

	/**
	 * Resolve a post's featured image URL with a fallback to the
	 * `_thumbnail_external_url` meta — lets sample data and CSV imports use
	 * remote image URLs without requiring an attachment to be created first.
	 */
	private function get_featured_image( $post_id, $size = 'large' ) {
		$url = get_the_post_thumbnail_url( $post_id, $size );
		if ( $url ) {
			return $url;
		}
		$external = get_post_meta( $post_id, '_thumbnail_external_url', true );
		return $external ? esc_url_raw( $external ) : '';
	}

	/**
	 * Extract YouTube video ID from various URL formats.
	 */
	private static function extract_youtube_id( $url ) {
		if ( preg_match( '/(?:youtube\.com\/watch\?v=|youtube\.com\/embed\/|youtu\.be\/|youtube\.com\/v\/)([a-zA-Z0-9_-]{11})/', $url, $m ) ) {
			return $m[1];
		}
		return '';
	}

	/* -------- logo -------- */
	public function logo( $atts ) {
		$a   = shortcode_atts( array( 'width' => '90', 'href' => home_url( '/' ), 'class' => 'pal-logo' ), $atts, 'pal_logo' );
		// Use admin-uploaded high-res logo if set; fall back to the bundled
		// 250x250 PNG (which appears soft when displayed above ~125px).
		$custom = $this->opt( 'site_logo_url' );
		$src    = $custom ? $custom : NPAL_PLUGIN_URL . 'assets/img/Pal-Logo.png';
		return sprintf(
			'<a href="%1$s" class="%2$s" aria-label="%3$s"><img src="%4$s" alt="%3$s" style="width:%5$spx;height:auto;display:block;" /></a>',
			esc_url( $a['href'] ),
			esc_attr( $a['class'] ),
			esc_attr__( 'Nashua PAL — Home', 'nashua-pal-core' ),
			esc_url( $src ),
			esc_attr( $a['width'] )
		);
	}

	/* -------- promo bar -------- */
	public function promo_bar() {
		if ( '1' !== $this->opt( 'promo_enabled', '1' ) ) { return ''; }
		$text     = $this->opt( 'promo_text' );
		$btn      = $this->opt( 'promo_button_text' );
		$url      = $this->opt( 'promo_button_url' );
		$icon     = $this->opt( 'promo_icon', 'megaphone' );
		$position = $this->opt( 'promo_icon_position', 'before' );

		// Strip leading megaphone-ish emoji (📢 / 📣 / 🔊) if the saved
		// promo text still has one — we render the picked icon as a proper
		// SVG/IMG/FA element instead. Keeps existing emoji-prefixed text
		// working without an edit.
		$text = preg_replace( '/^[\x{1F4E2}\x{1F4E3}\x{1F50A}]\s*/u', '', (string) $text );

		$show_icon = ( 'none' !== $position && $icon );

		ob_start(); ?>
		<div class="pal-promo pal-promo--icon-<?php echo esc_attr( $position ); ?>" data-pal-promo>
			<?php if ( $show_icon && 'before' === $position ) : ?>
				<span class="pal-promo__icon" aria-hidden="true"><?php echo NPAL_Icons::render_or_fa( $icon ); ?></span>
			<?php endif; ?>
			<span class="pal-promo__text"><?php echo wp_kses_post( $text ); ?></span>
			<?php if ( $show_icon && 'after' === $position ) : ?>
				<span class="pal-promo__icon pal-promo__icon--after" aria-hidden="true"><?php echo NPAL_Icons::render_or_fa( $icon ); ?></span>
			<?php endif; ?>
			<?php if ( $btn && $url ) : ?>
				<a class="pal-promo-btn" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $btn ); ?> &rarr;</a>
			<?php endif; ?>
		</div>
		<?php return ob_get_clean();
	}

	/* -------- alert banner -------- */
	/** Guard: alert is auto-injected site-wide via wp_body_open AND can be
	 *  placed manually with [pal_alert]. Without this flag both paths would
	 *  render on the same page. */
	private static $alert_rendered = false;

	/** Reset hook used by the admin Live Preview so it can call the alert
	 *  shortcode in a fresh state without colliding with the auto-injection
	 *  guard. Front-end requests never call this. */
	public static function reset_alert_render_flag() {
		self::$alert_rendered = false;
	}

	public function alert() {
		if ( self::$alert_rendered ) { return ''; }
		if ( '1' !== $this->opt( 'alert_enabled', '0' ) ) { return ''; }

		// Image-only mode: skip all the structured rendering and just emit
		// a single-graphic popup. Triggered by Settings → Site Alert →
		// Layout mode = "Single uploaded graphic" with a `Designed graphic`
		// uploaded.
		if ( 'image' === $this->opt( 'alert_layout', 'split' ) ) {
			$full_image = $this->opt( 'alert_full_image' );
			if ( ! $full_image ) { return ''; }
			$full_url    = $this->opt( 'alert_full_url' );
			$dismissible = '1' === $this->opt( 'alert_dismissible', '1' );
			$key         = substr( md5( 'image|' . $full_image . '|' . $full_url ), 0, 10 );
			self::$alert_rendered = true;

			ob_start(); ?>
			<div class="pal-alert-modal pal-alert-modal--image-only"
				role="dialog"
				aria-modal="true"
				aria-label="<?php esc_attr_e( 'Site alert', 'nashua-pal-core' ); ?>"
				data-pal-alert
				<?php echo $dismissible ? 'data-dismiss-key="' . esc_attr( $key ) . '"' : 'data-no-dismiss="1"'; ?>>
				<div class="pal-alert-modal__overlay" data-pal-alert-close></div>
				<?php if ( $dismissible ) : ?>
					<button type="button" class="pal-alert-modal__close pal-alert-modal__close--floating" data-pal-alert-close aria-label="<?php esc_attr_e( 'Close', 'nashua-pal-core' ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
					</button>
				<?php endif; ?>
				<div class="pal-alert-modal__panel" role="document">
					<?php if ( $full_url ) : ?>
						<a class="pal-alert-modal__full-link" href="<?php echo esc_url( $full_url ); ?>">
							<img class="pal-alert-modal__full" src="<?php echo esc_url( $full_image ); ?>" alt="" />
						</a>
					<?php else : ?>
						<img class="pal-alert-modal__full" src="<?php echo esc_url( $full_image ); ?>" alt="" />
					<?php endif; ?>
				</div>
			</div>
			<?php return ob_get_clean();
		}

		// Right panel
		$eyebrow = $this->opt( 'alert_eyebrow' );
		$title   = $this->opt( 'alert_title' );
		$text    = $this->opt( 'alert_text' );
		$btn     = $this->opt( 'alert_button_text' );
		$url     = $this->opt( 'alert_button_url' );
		$btn2    = $this->opt( 'alert_button_2_text' );
		$url2    = $this->opt( 'alert_button_2_url' );
		$footer  = $this->opt( 'alert_footer_text' );

		// Right panel features
		$features = array();
		for ( $i = 1; $i <= 3; $i++ ) {
			$icon  = $this->opt( "alert_feature_{$i}_icon" );
			$label = $this->opt( "alert_feature_{$i}_label" );
			$style = $this->opt( "alert_feature_{$i}_style" ) ?: 'navy';
			if ( $icon || $label ) {
				$features[] = array( 'icon' => $icon, 'label' => $label, 'style' => $style );
			}
		}

		// Left panel — simple-mode override (one designed image)
		$image = $this->opt( 'alert_image' );

		// Left panel — structured fields (used when $image is empty)
		$left_bg       = $this->opt( 'alert_left_bg_image' );
		$left_logo     = $this->opt( 'alert_left_logo' );
		$left_subtitle = $this->opt( 'alert_left_subtitle' );
		$left_title    = $this->opt( 'alert_left_title' );
		$left_script   = $this->opt( 'alert_left_script' );
		$left_year     = $this->opt( 'alert_left_year' );
		$left_strip    = $this->opt( 'alert_left_strip_text' );
		$left_details  = array();
		for ( $i = 1; $i <= 3; $i++ ) {
			$d_icon = $this->opt( "alert_left_detail_{$i}_icon" );
			$d_text = $this->opt( "alert_left_detail_{$i}_text" );
			if ( $d_icon || $d_text ) {
				$left_details[] = array( 'icon' => $d_icon, 'text' => $d_text );
			}
		}
		$has_structured_left = ( $left_logo || $left_subtitle || $left_title || $left_script || $left_year || $left_strip || ! empty( $left_details ) || $left_bg );
		$has_left_panel = ( $image || $has_structured_left );

		// Note: we deliberately don't bail out when every field is empty here.
		// The structural skeleton needs to render so the admin Live Preview
		// can populate it via JS on input. Front-end pages with the alert
		// enabled but no content will show an empty popup card — that's a
		// configuration mistake the admin will see in the preview anyway.

		// Cache key based on every field, so any edit re-shows the popup to
		// previously-dismissed visitors.
		$key_seed = wp_json_encode( array(
			'eyebrow' => $eyebrow, 'title' => $title, 'text' => $text,
			'btn' => $btn, 'url' => $url, 'btn2' => $btn2, 'url2' => $url2,
			'footer' => $footer, 'features' => $features, 'image' => $image,
			'left' => array(
				'bg' => $left_bg, 'logo' => $left_logo, 'subtitle' => $left_subtitle,
				'title' => $left_title, 'script' => $left_script, 'year' => $left_year,
				'details' => $left_details, 'strip' => $left_strip,
			),
		) );
		$key = substr( md5( $key_seed ), 0, 10 );
		$dismissible = '1' === $this->opt( 'alert_dismissible', '1' );
		self::$alert_rendered = true;

		ob_start(); ?>
		<div class="pal-alert-modal<?php echo $has_left_panel ? ' pal-alert-modal--with-image' : ''; ?>"
			role="dialog"
			aria-modal="true"
			aria-labelledby="pal-alert-title"
			data-pal-alert
			<?php echo $dismissible ? 'data-dismiss-key="' . esc_attr( $key ) . '"' : 'data-no-dismiss="1"'; ?>>
			<div class="pal-alert-modal__overlay" data-pal-alert-close></div>
			<div class="pal-alert-modal__panel" role="document">
				<?php if ( $dismissible ) : ?>
					<button type="button" class="pal-alert-modal__close" data-pal-alert-close aria-label="<?php esc_attr_e( 'Close', 'nashua-pal-core' ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
					</button>
				<?php endif; ?>

				<?php
				// We always emit BOTH the simple-mode image overlay AND the
				// structured left panel, with `hidden` controlling which is
				// visible. This lets the admin Live Preview react instantly
				// when the alert_image input is set/cleared (no rebuild
				// needed) and keeps the structured fields editable even when
				// a simple-mode image is also present.
				?>
				<div class="pal-alert-modal__image" role="presentation" data-pal-live-image style="background-image:url(<?php echo esc_url( $image ); ?>);"<?php echo $image ? '' : ' hidden'; ?>></div>
				<div class="pal-alert-modal__left" data-pal-live-structured-left<?php echo $left_bg ? ' style="background-image:url(' . esc_url( $left_bg ) . ');"' : ''; ?><?php echo ( ! $image && $has_structured_left ) ? '' : ' hidden'; ?>>
						<div class="pal-alert-modal__left-inner">
							<img class="pal-alert-modal__left-logo" src="<?php echo esc_url( $left_logo ?: '' ); ?>" alt=""<?php echo $left_logo ? '' : ' hidden'; ?> />
							<p class="pal-alert-modal__left-subtitle" data-pal-live="alert_left_subtitle"<?php echo $left_subtitle ? '' : ' hidden'; ?>><?php echo esc_html( $left_subtitle ); ?></p>
							<p class="pal-alert-modal__left-title" data-pal-live="alert_left_title"<?php echo $left_title ? '' : ' hidden'; ?>><?php echo wp_kses_post( nl2br( $left_title ) ); ?></p>
							<?php
							$script_family = class_exists( 'NPAL_Settings' ) ? NPAL_Settings::script_font_family() : "'Allura', cursive";
							$script_size   = class_exists( 'NPAL_Settings' ) ? NPAL_Settings::script_size_values() : array( 'mobile' => '56px', 'desktop' => '72px' );
							$script_style  = sprintf(
								'font-family:%s;--pal-script-size:%s;--pal-script-size-desktop:%s;',
								esc_attr( $script_family ),
								esc_attr( $script_size['mobile'] ),
								esc_attr( $script_size['desktop'] )
							);
							?>
							<p class="pal-alert-modal__left-script" data-pal-live="alert_left_script" style="<?php echo $script_style; ?>"<?php echo $left_script ? '' : ' hidden'; ?>><?php echo esc_html( $left_script ); ?></p>
							<p class="pal-alert-modal__left-year" data-pal-live="alert_left_year"<?php echo $left_year ? '' : ' hidden'; ?>><?php echo esc_html( $left_year ); ?></p>
							<?php
							// Always render all 3 detail slots — same reasoning as
							// the right-panel feature slots above.
							$detail_slots = array();
							for ( $i = 1; $i <= 3; $i++ ) {
								$detail_slots[ $i ] = array(
									'icon' => $this->opt( "alert_left_detail_{$i}_icon" ),
									'text' => $this->opt( "alert_left_detail_{$i}_text" ),
								);
							}
							$any_detail_visible = false;
							foreach ( $detail_slots as $d ) { if ( $d['icon'] || $d['text'] ) { $any_detail_visible = true; break; } }
							?>
							<ul class="pal-alert-modal__left-details" data-pal-live-details<?php echo $any_detail_visible ? '' : ' hidden'; ?>>
								<?php foreach ( $detail_slots as $row => $d ) : $visible = ( $d['icon'] || $d['text'] ); ?>
									<li data-pal-live-detail-slot="<?php echo (int) $row; ?>"<?php echo $visible ? '' : ' hidden'; ?>>
										<span class="pal-alert-modal__left-detail-icon" data-pal-live-icon="alert_left_detail_<?php echo (int) $row; ?>_icon"><?php if ( $d['icon'] ) echo NPAL_Icons::render_or_fa( $d['icon'] ); ?></span>
										<span class="pal-alert-modal__left-detail-text" data-pal-live="alert_left_detail_<?php echo (int) $row; ?>_text"<?php echo $d['text'] ? '' : ' hidden'; ?>><?php echo wp_kses_post( nl2br( esc_html( $d['text'] ) ) ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<div class="pal-alert-modal__left-strip" data-pal-live="alert_left_strip_text"<?php echo $left_strip ? '' : ' hidden'; ?>><?php echo esc_html( $left_strip ); ?></div>
					</div>

				<div class="pal-alert-modal__content">
					<p class="pal-alert-modal__eyebrow" data-pal-live="alert_eyebrow"<?php echo $eyebrow ? '' : ' hidden'; ?>><?php echo esc_html( $eyebrow ); ?></p>
					<h2 id="pal-alert-title" class="pal-alert-modal__title" data-pal-live="alert_title"<?php echo $title ? '' : ' hidden'; ?>><?php echo wp_kses_post( $title ); ?></h2>
					<div class="pal-alert-modal__text" data-pal-live="alert_text" data-pal-live-multiline="1"<?php echo $text ? '' : ' hidden'; ?>><?php echo $text ? wp_kses_post( wpautop( $text ) ) : ''; ?></div>

					<?php
					// Always render all 3 feature slots so the admin Live Preview
					// has DOM nodes to populate via JS (label live-updates from
					// the input, icons live-update via a MutationObserver on the
					// icon-picker thumbnail). Each slot hides itself when both
					// its icon and label are empty.
					$feature_slots = array();
					for ( $i = 1; $i <= 3; $i++ ) {
						$feature_slots[ $i ] = array(
							'icon'  => $this->opt( "alert_feature_{$i}_icon" ),
							'label' => $this->opt( "alert_feature_{$i}_label" ),
							'style' => $this->opt( "alert_feature_{$i}_style" ) ?: 'navy',
						);
					}
					$any_feature_visible = false;
					foreach ( $feature_slots as $f ) { if ( $f['icon'] || $f['label'] ) { $any_feature_visible = true; break; } }
					?>
					<ul class="pal-alert-modal__features" data-pal-live-features<?php echo $any_feature_visible ? '' : ' hidden'; ?>>
						<?php foreach ( $feature_slots as $slot => $f ) : $visible = ( $f['icon'] || $f['label'] ); ?>
							<li class="pal-alert-modal__feature pal-alert-modal__feature--<?php echo esc_attr( $f['style'] ); ?>" data-pal-live-feature-slot="<?php echo (int) $slot; ?>"<?php echo $visible ? '' : ' hidden'; ?>>
								<span class="pal-alert-modal__feature-icon" data-pal-live-icon="alert_feature_<?php echo (int) $slot; ?>_icon"><?php if ( $f['icon'] ) echo NPAL_Icons::render_or_fa( $f['icon'] ); ?></span>
								<span class="pal-alert-modal__feature-label" data-pal-live="alert_feature_<?php echo (int) $slot; ?>_label"<?php echo $f['label'] ? '' : ' hidden'; ?>><?php echo esc_html( $f['label'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>

					<?php $btn_visible  = ( $btn  && $url );
						$btn2_visible = ( $btn2 && $url2 );
						$any_btn      = ( $btn_visible || $btn2_visible ); ?>
					<div class="pal-alert-modal__actions" data-pal-live-actions<?php echo $any_btn ? '' : ' hidden'; ?>>
						<a class="pal-alert-modal__btn pal-alert-modal__btn--primary" href="<?php echo esc_url( $url ?: '#' ); ?>" data-pal-live-href="alert_button_url" data-pal-live-text-target="alert_button_text"<?php echo $btn_visible ? '' : ' hidden'; ?>>
							<span data-pal-live="alert_button_text"><?php echo esc_html( $btn ); ?></span>
							<span aria-hidden="true">→</span>
						</a>
						<a class="pal-alert-modal__btn pal-alert-modal__btn--secondary" href="<?php echo esc_url( $url2 ?: '#' ); ?>" data-pal-live-href="alert_button_2_url" data-pal-live-text-target="alert_button_2_text"<?php echo $btn2_visible ? '' : ' hidden'; ?>>
							<span data-pal-live="alert_button_2_text"><?php echo esc_html( $btn2 ); ?></span>
						</a>
					</div>

					<p class="pal-alert-modal__footer" data-pal-live-footer<?php echo $footer ? '' : ' hidden'; ?>>
						<svg class="pal-alert-modal__footer-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
						<span data-pal-live="alert_footer_text"><?php echo esc_html( $footer ); ?></span>
					</p>
				</div>
			</div>
		</div>
		<?php return ob_get_clean();
	}

	/* -------- stats bar -------- */
	public function stats_bar() {
		ob_start(); ?>
		<section class="pal-stats" aria-label="<?php esc_attr_e( 'Impact statistics', 'nashua-pal-core' ); ?>">
			<div class="pal-stats__grid">
				<?php for ( $i = 1; $i <= 4; $i++ ) :
					$num   = $this->opt( "stat_{$i}_number" );
					$label = $this->opt( "stat_{$i}_label" );
					$icon  = $this->opt( "stat_{$i}_icon" );
					if ( ! $num && ! $label ) { continue; }
				?>
					<div class="pal-stat">
						<?php if ( $icon ) : ?><span class="pal-stat__icon"><?php echo NPAL_Icons::render_or_fa( $icon ); ?></span><?php endif; ?>
						<div class="pal-stat__text">
							<span class="pal-stat__number" data-animate data-target="<?php echo esc_attr( $num ); ?>"><?php echo esc_html( $num ); ?></span>
							<span class="pal-stat__label"><?php echo esc_html( $label ); ?></span>
						</div>
					</div>
				<?php endfor; ?>
			</div>
		</section>
		<?php return ob_get_clean();
	}

	/* -------- single stat (for ad-hoc placement) -------- */
	public function stat( $atts ) {
		$a = shortcode_atts( array( 'number' => '', 'label' => '', 'icon' => '' ), $atts, 'pal_stat' );
		ob_start(); ?>
		<div class="pal-stat">
			<?php if ( $a['icon'] ) : ?><span class="pal-stat__icon"><?php echo NPAL_Icons::render_or_fa( $a['icon'] ); ?></span><?php endif; ?>
			<div class="pal-stat__text">
				<span class="pal-stat__number" data-animate><?php echo esc_html( $a['number'] ); ?></span>
				<span class="pal-stat__label"><?php echo esc_html( $a['label'] ); ?></span>
			</div>
		</div>
		<?php return ob_get_clean();
	}

	/* -------- program cards (homepage) -------- */
	public function program_cards( $atts ) {
		$a = shortcode_atts( array(
			'count'         => 4,
			'featured_only' => 'yes',
			'view'          => 'grid',  // 'grid' | 'list'
			'columns'       => 4,        // 1–4 (used only when view="grid")
		), $atts, 'pal_program_cards' );
		$view    = ( 'list' === strtolower( (string) $a['view'] ) ) ? 'list' : 'grid';
		$columns = max( 1, min( 4, (int) $a['columns'] ) );
		$args = array(
			'post_type'      => NPAL_CPT_Program::POST_TYPE,
			'posts_per_page' => (int) $a['count'],
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		);
		// Featured filter: "no" (explicit) = show ALL programs (archive use case).
		// Anything else — "yes", empty string, missing — keeps the filter ON
		// (homepage default). This guards against legacy widget instances that
		// stored an empty featured_only key getting misread as "show all".
		$featured_only = ( 'no' !== strtolower( (string) $a['featured_only'] ) );
		if ( $featured_only ) {
			$args['meta_query'] = array(
				array( 'key' => '_pal_program_show_on_home', 'value' => '1' ),
			);
		} else {
			// Archive mode: exclude programs explicitly hidden from the archive.
			$args['meta_query'] = array(
				'relation' => 'OR',
				array( 'key' => '_pal_program_hide_from_archive', 'compare' => 'NOT EXISTS' ),
				array( 'key' => '_pal_program_hide_from_archive', 'value' => '1', 'compare' => '!=' ),
			);
		}
		$q = new WP_Query( $args );
		// Fallback: if "featured only" found nothing but programs exist, show all.
		if ( ! $q->have_posts() && $featured_only ) {
			unset( $args['meta_query'] );
			$q = new WP_Query( $args );
		}
		if ( ! $q->have_posts() ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<p class="pal-empty" style="text-align:center;color:#646970;font-size:13px;">' . esc_html__( 'No programs yet — add some at Programs → Add Program.', 'nashua-pal-core' ) . '</p>';
			}
			return '';
		}

		$wrap_class  = 'pal-programs pal-programs--' . $view;
		if ( 'grid' === $view ) { $wrap_class .= ' pal-programs--cols-' . $columns; }

		ob_start(); ?>
		<div class="<?php echo esc_attr( $wrap_class ); ?>">
		<?php while ( $q->have_posts() ) : $q->the_post();
			$id     = get_the_ID();
			$short  = get_post_meta( $id, '_pal_program_short', true );
			$icon   = get_post_meta( $id, '_pal_program_icon', true );
			$cta    = NPAL_Program_Meta::get_program_url( $id );
			$thumb  = $this->get_featured_image( $id, 'full' );
			$status = get_post_meta( $id, '_pal_program_status', true );
			$cost   = get_post_meta( $id, '_pal_program_cost', true );
			?>
			<a href="<?php echo esc_url( $cta ); ?>" class="pal-program-card-link">
			<article class="pal-program-card<?php echo $thumb ? '' : ' pal-program-card--no-media'; ?>">
				<?php if ( $thumb ) : ?>
					<div class="pal-program-card__media" style="background-image:url(<?php echo esc_url( $thumb ); ?>);">
						<?php if ( $icon ) : ?><span class="pal-program-card__icon"><?php echo NPAL_Icons::render_or_fa( $icon ); ?></span><?php endif; ?>
						<?php if ( $status ) : ?>
							<span class="pal-program-card__badge pal-program-card__badge--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $this->program_status_label( $status ) ); ?></span>
						<?php endif; ?>
					</div>
				<?php elseif ( $icon ) : ?>
					<div class="pal-program-card__media pal-program-card__media--solid">
						<span class="pal-program-card__icon pal-program-card__icon--inline"><?php echo NPAL_Icons::render_or_fa( $icon ); ?></span>
						<?php if ( $status ) : ?>
							<span class="pal-program-card__badge pal-program-card__badge--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $this->program_status_label( $status ) ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<div class="pal-program-card__body">
					<h3 class="pal-program-card__title"><?php the_title(); ?></h3>
					<?php if ( $cost ) : ?>
						<span class="pal-program-card__cost"><?php echo esc_html( $cost ); ?></span>
					<?php endif; ?>
					<p class="pal-program-card__desc"><?php echo esc_html( $short ?: get_the_excerpt() ); ?></p>
				</div>
			</article>
			</a>
		<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php return ob_get_clean();
	}

	/* -------- single program detail view ----------
	 * For a dedicated program page (e.g. /programs/mentoring/), or to
	 * render a specific program by ID anywhere on the site. Pulls from
	 * the Programs CPT and renders the same fields that program_cards
	 * uses (image, title, description, icon, locations, CTA) at full
	 * detail size — image hero + title block + description + locations
	 * grid + CTA button.
	 */
	public function program_single( $atts ) {
		$a = shortcode_atts( array(
			'id'              => 0,
			'slug'            => '',
			'show_image'      => 'no',
			'show_locations'  => 'yes',
			'show_cta'        => 'yes',
		), $atts, 'pal_program' );

		// Resolve which program to show: explicit id → slug → current
		// post. The "current post" detection has to handle three contexts:
		//  1. Normal single-template loop on /programs/{slug}/ — is_singular()
		//     and get_the_ID() both work.
		//  2. Inside an Elementor Theme Builder template rendering on a
		//     /programs/{slug}/ URL — the document renders BEFORE WP's main
		//     loop sets up the global $post for is_singular() to detect
		//     reliably. get_queried_object_id() IS set early in the request
		//     cycle from WP_Query::get_queried_object_id(), so use that as
		//     the primary detector.
		//  3. Editor preview / admin contexts — neither works; we fall through
		//     to the "needs an id/slug" empty-state.
		$post_id = (int) $a['id'];
		if ( ! $post_id && $a['slug'] ) {
			$page = get_page_by_path( $a['slug'], OBJECT, NPAL_CPT_Program::POST_TYPE );
			if ( $page ) { $post_id = $page->ID; }
		}
		if ( ! $post_id ) {
			$qid  = (int) get_queried_object_id();
			$qobj = get_queried_object();
			if ( $qid && $qobj instanceof WP_Post && NPAL_CPT_Program::POST_TYPE === $qobj->post_type ) {
				$post_id = $qid;
			} elseif ( is_singular( NPAL_CPT_Program::POST_TYPE ) ) {
				$post_id = get_the_ID();
			}
		}
		if ( ! $post_id ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<p class="pal-empty" style="color:#646970;font-size:13px;">' . esc_html__( '[pal_program] needs an `id` or `slug` attribute, or must be placed inside a Program post template.', 'nashua-pal-core' ) . '</p>';
			}
			return '';
		}
		$post = get_post( $post_id );
		if ( ! $post || NPAL_CPT_Program::POST_TYPE !== $post->post_type ) {
			return '';
		}

		$show_image = ( 'yes' === strtolower( (string) $a['show_image'] ) || '1' === (string) $a['show_image'] );
		$show_loc   = ( 'yes' === strtolower( (string) $a['show_locations'] ) || '1' === (string) $a['show_locations'] );
		$show_cta   = ( 'yes' === strtolower( (string) $a['show_cta'] ) || '1' === (string) $a['show_cta'] );

		$short        = get_post_meta( $post_id, '_pal_program_short', true );
		$icon         = get_post_meta( $post_id, '_pal_program_icon', true );
		$cta_raw      = NPAL_Program_Meta::get_program_url( $post_id );
		// Hide CTA on the program's own single page (self-referential link).
		$cta          = ( untrailingslashit( $cta_raw ) !== untrailingslashit( get_permalink( $post_id ) ) ) ? $cta_raw : '';
		$cta_label    = get_post_meta( $post_id, '_pal_program_cta_label', true ) ?: __( 'Learn More', 'nashua-pal-core' );
		$thumb        = $show_image ? $this->get_featured_image( $post_id, 'large' ) : '';
		$locs         = get_post_meta( $post_id, '_pal_program_locations', true );
		$season       = get_post_meta( $post_id, '_pal_program_season', true );
		$age_range    = get_post_meta( $post_id, '_pal_program_age_range', true );
		$contacts     = get_post_meta( $post_id, '_pal_program_contacts', true );
		$accred       = get_post_meta( $post_id, '_pal_program_accreditation', true );
		$status       = get_post_meta( $post_id, '_pal_program_status', true );
		$cost         = get_post_meta( $post_id, '_pal_program_cost', true );
		$register_url = get_post_meta( $post_id, '_pal_program_register_url', true );
		$coaches      = get_post_meta( $post_id, '_pal_program_coaches', true );
		$addinfo      = get_post_meta( $post_id, '_pal_program_additional_info', true );
		// Photo gallery.
		$gallery_raw  = get_post_meta( $post_id, '_pal_program_gallery', true );
		$gallery_ids  = ! empty( $gallery_raw ) ? array_filter( array_map( 'absint', explode( ',', $gallery_raw ) ) ) : array();
		$gallery_imgs = array();
		foreach ( $gallery_ids as $gid ) {
			$full  = wp_get_attachment_image_url( $gid, 'large' );
			$thumb = wp_get_attachment_image_url( $gid, 'medium' );
			$alt   = get_post_meta( $gid, '_wp_attachment_image_alt', true );
			if ( $full ) {
				$gallery_imgs[] = array( 'full' => $full, 'thumb' => $thumb ?: $full, 'alt' => $alt ?: '' );
			}
		}
		// YouTube videos (v1.39+ repeater, with BC fallback to old singular key).
		$yt_videos = get_post_meta( $post_id, '_pal_program_youtube_urls', true );
		if ( ! is_array( $yt_videos ) ) { $yt_videos = array(); }
		if ( empty( $yt_videos ) ) {
			$old_yt = get_post_meta( $post_id, '_pal_program_youtube_url', true );
			if ( $old_yt ) { $yt_videos = array( array( 'url' => $old_yt, 'label' => '' ) ); }
		}
		$yt_items = array();
		foreach ( $yt_videos as $v ) {
			$vid = self::extract_youtube_id( $v['url'] ?? '' );
			if ( $vid ) { $yt_items[] = array( 'id' => $vid, 'label' => $v['label'] ?? '' ); }
		}
		$content      = apply_filters( 'the_content', $post->post_content );

		// Scholarship fields.
		$schol_amount      = get_post_meta( $post_id, '_pal_program_schol_amount', true );
		$schol_deadline    = get_post_meta( $post_id, '_pal_program_schol_deadline', true );
		$schol_eligibility = get_post_meta( $post_id, '_pal_program_schol_eligibility', true );
		$schol_recipients  = get_post_meta( $post_id, '_pal_program_schol_recipients', true );
		$schol_donate_url  = get_post_meta( $post_id, '_pal_program_schol_donate_url', true );
		$has_scholarship   = ( $schol_amount || $schol_deadline || $schol_eligibility );

		// Linked upcoming events for this program.
		$linked_events = new WP_Query( array(
			'post_type'      => NPAL_CPT_Event::POST_TYPE,
			'posts_per_page' => 5,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array( 'key' => '_pal_event_program', 'value' => (string) $post_id ),
				array( 'key' => '_pal_event_start', 'value' => current_time( 'Y-m-d H:i:s' ), 'compare' => '>=', 'type' => 'DATETIME' ),
			),
			'orderby'        => 'meta_value',
			'meta_key'       => '_pal_event_start',
			'order'          => 'ASC',
		) );

		ob_start(); ?>
		<article class="pal-program-single">
			<?php if ( $show_image && ! empty( $yt_items ) ) :
				$sc_uid   = 'pal-yt-sc-' . wp_unique_id();
				$sc_count = count( $yt_items );
				$sc_title = esc_attr( get_the_title( $post_id ) );
			?>
				<div class="pal-program-single__media pal-program-single__media--video pal-yt-carousel" id="<?php echo esc_attr( $sc_uid ); ?>" data-count="<?php echo $sc_count; ?>">
					<?php foreach ( $yt_items as $idx => $item ) :
						$hidden = $idx > 0 ? ' style="display:none;"' : '';
					?>
						<div class="pal-yt-carousel__slide" data-index="<?php echo $idx; ?>"<?php echo $hidden; ?>>
							<div class="pal-program-single__video-wrap">
								<iframe src="https://www.youtube-nocookie.com/embed/<?php echo esc_attr( $item['id'] ); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen title="<?php echo $sc_title; ?>"></iframe>
							</div>
							<?php if ( $item['label'] ) : ?>
								<p class="pal-yt-carousel__label"><?php echo esc_html( $item['label'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
					<?php if ( $sc_count > 1 ) : ?>
						<button type="button" class="pal-yt-carousel__arrow pal-yt-carousel__arrow--prev" aria-label="Previous video">&#10094;</button>
						<button type="button" class="pal-yt-carousel__arrow pal-yt-carousel__arrow--next" aria-label="Next video">&#10095;</button>
						<div class="pal-yt-carousel__dots">
							<?php for ( $d = 0; $d < $sc_count; $d++ ) : ?>
								<button type="button" class="pal-yt-carousel__dot<?php echo 0 === $d ? ' is-active' : ''; ?>" data-index="<?php echo $d; ?>" aria-label="Video <?php echo $d + 1; ?>"></button>
							<?php endfor; ?>
						</div>
					<?php endif; ?>
				</div>
				<?php if ( $sc_count > 1 ) : ?>
				<script>
				(function(){
					var w=document.getElementById('<?php echo esc_js( $sc_uid ); ?>');if(!w)return;
					var slides=w.querySelectorAll('.pal-yt-carousel__slide'),
						dots=w.querySelectorAll('.pal-yt-carousel__dot'),
						cur=0,total=slides.length,timer=null,INTERVAL=30000,PAUSE=60000;
					function go(n){
						slides[cur].style.display='none';
						if(dots[cur])dots[cur].classList.remove('is-active');
						cur=(n+total)%total;
						slides[cur].style.display='';
						if(dots[cur])dots[cur].classList.add('is-active');
					}
					function autoStart(){timer=setInterval(function(){go(cur+1);},INTERVAL);}
					function userNav(n){clearInterval(timer);go(n);setTimeout(autoStart,PAUSE);}
					w.querySelector('.pal-yt-carousel__arrow--prev').addEventListener('click',function(){userNav(cur-1);});
					w.querySelector('.pal-yt-carousel__arrow--next').addEventListener('click',function(){userNav(cur+1);});
					dots.forEach(function(d){d.addEventListener('click',function(){userNav(parseInt(d.dataset.index,10));});});
					autoStart();
				})();
				</script>
				<?php endif; ?>
			<?php elseif ( $thumb ) : ?>
				<div class="pal-program-single__media" style="background-image:url(<?php echo esc_url( $thumb ); ?>);">
					<?php if ( $icon ) : ?>
						<span class="pal-program-single__icon"><?php echo NPAL_Icons::render_or_fa( $icon ); ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="pal-program-single__body">
				<?php if ( $status ) : ?>
					<span class="pal-program-single__status pal-program-single__status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $this->program_status_label( $status ) ); ?></span>
				<?php endif; ?>

				<h1 class="pal-program-single__title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>

				<?php
				$cat_terms = wp_get_post_terms( $post_id, 'pal_program_category' );
				$cat_name  = ( ! is_wp_error( $cat_terms ) && ! empty( $cat_terms ) ) ? $cat_terms[0]->name : '';
			?>
				<?php if ( $cat_name || $season || $age_range || $cost ) : ?>
					<div class="pal-program-single__meta-pills">
						<?php if ( $cat_name ) : ?>
							<span class="pal-program-single__pill pal-program-single__pill--category"><?php echo esc_html( $cat_name ); ?></span>
						<?php endif; ?>
						<?php if ( $season ) : ?>
							<span class="pal-program-single__pill"><?php echo esc_html( __( 'Season: ', 'nashua-pal-core' ) . $season ); ?></span>
						<?php endif; ?>
						<?php if ( $age_range ) : ?>
							<span class="pal-program-single__pill"><?php echo esc_html( __( 'Ages ', 'nashua-pal-core' ) . $age_range ); ?></span>
						<?php endif; ?>
						<?php if ( $cost ) : ?>
							<span class="pal-program-single__pill pal-program-single__pill--cost"><?php echo esc_html( __( 'Cost: ', 'nashua-pal-core' ) . $cost ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $register_url ) : ?>
					<div class="pal-program-single__register-wrap">
						<a class="pal-btn pal-btn-yellow" href="<?php echo esc_url( $register_url ); ?>"><?php esc_html_e( 'Register Now', 'nashua-pal-core' ); ?> &rarr;</a>
					</div>
				<?php endif; ?>

				<?php if ( $content ) : ?>
					<div class="pal-program-single__content"><?php echo $content; ?></div>
				<?php endif; ?>

				<?php if ( is_array( $coaches ) && ! empty( $coaches ) ) : ?>
					<div class="pal-program-single__coaches">
						<h3><?php esc_html_e( 'Coaches & Staff', 'nashua-pal-core' ); ?></h3>
						<div class="pal-program-single__coaches-grid">
							<?php foreach ( $coaches as $coach ) : if ( empty( $coach['name'] ) ) continue; ?>
								<div class="pal-program-single__coach">
									<strong><?php echo esc_html( $coach['name'] ); ?></strong>
									<?php if ( ! empty( $coach['role'] ) ) : ?><span class="pal-program-single__coach-role"><?php echo esc_html( $coach['role'] ); ?></span><?php endif; ?>
									<?php if ( ! empty( $coach['bio'] ) ) : ?><p class="pal-program-single__coach-bio"><?php echo esc_html( $coach['bio'] ); ?></p><?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( is_array( $contacts ) && ! empty( $contacts ) ) : ?>
					<div class="pal-program-single__contacts">
						<h3><?php esc_html_e( 'For more information, contact:', 'nashua-pal-core' ); ?></h3>
						<?php foreach ( $contacts as $c ) : if ( empty( $c['name'] ) ) continue; ?>
							<div class="pal-program-single__contact">
								<strong><?php echo esc_html( $c['name'] ); ?></strong>
								<?php if ( ! empty( $c['title'] ) ) : ?><span>, <?php echo esc_html( $c['title'] ); ?></span><?php endif; ?>
								<?php if ( ! empty( $c['phone'] ) ) : ?>
									<br /><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $c['phone'] ) ); ?>"><?php echo esc_html( $c['phone'] ); ?></a>
								<?php endif; ?>
								<?php if ( ! empty( $c['email'] ) ) : ?>
									 — <a href="mailto:<?php echo esc_attr( $c['email'] ); ?>">email <?php echo esc_html( explode( ' ', $c['name'] )[0] ); ?></a>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<?php if ( $accred ) : ?>
					<p class="pal-program-single__accreditation"><em><?php echo esc_html( $accred ); ?></em></p>
				<?php endif; ?>

				<?php if ( $has_scholarship ) : ?>
					<div class="pal-program-single__scholarship">
						<h3><?php esc_html_e( 'Scholarship Information', 'nashua-pal-core' ); ?></h3>
						<?php if ( $schol_amount ) : ?>
							<p><strong><?php esc_html_e( 'Award:', 'nashua-pal-core' ); ?></strong> <?php echo esc_html( $schol_amount ); ?></p>
						<?php endif; ?>
						<?php if ( $schol_deadline ) : ?>
							<p><strong><?php esc_html_e( 'Deadline:', 'nashua-pal-core' ); ?></strong> <?php echo esc_html( $schol_deadline ); ?></p>
						<?php endif; ?>
						<?php if ( $schol_eligibility ) : ?>
							<div class="pal-program-single__schol-elig">
								<strong><?php esc_html_e( 'Eligibility:', 'nashua-pal-core' ); ?></strong>
								<?php echo wp_kses_post( wpautop( $schol_eligibility ) ); ?>
							</div>
						<?php endif; ?>
						<?php if ( $schol_recipients ) : ?>
							<div class="pal-program-single__schol-recipients">
								<strong><?php esc_html_e( 'Past Recipients:', 'nashua-pal-core' ); ?></strong>
								<ul>
									<?php foreach ( preg_split( "/\r\n|\n|\r/", $schol_recipients ) as $line ) :
										$line = trim( $line );
										if ( '' === $line ) continue;
										?>
										<li><?php echo esc_html( $line ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
						<?php if ( $schol_donate_url ) : ?>
							<a class="pal-btn pal-btn-navy" href="<?php echo esc_url( $schol_donate_url ); ?>"><?php esc_html_e( 'Support This Scholarship', 'nashua-pal-core' ); ?> &rarr;</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $show_loc && is_array( $locs ) && ! empty( $locs ) ) : ?>
					<div class="pal-program-single__locations">
						<h3 class="pal-program-single__locations-heading"><?php esc_html_e( 'Where it\'s offered', 'nashua-pal-core' ); ?></h3>
						<div class="pal-program-single__locations-grid">
							<?php foreach ( $locs as $loc ) : if ( empty( $loc['name'] ) && empty( $loc['address'] ) ) continue; ?>
								<div class="pal-program-single__location">
									<?php if ( ! empty( $loc['name'] ) ) : ?>
										<h4 class="pal-program-single__location-name"><?php echo esc_html( $loc['name'] ); ?></h4>
									<?php endif; ?>
									<?php if ( ! empty( $loc['address'] ) ) : ?>
										<p class="pal-program-single__location-address"><?php echo esc_html( $loc['address'] ); ?></p>
									<?php endif; ?>
									<?php if ( ! empty( $loc['when'] ) ) : ?>
										<p class="pal-program-single__location-when"><?php echo esc_html( $loc['when'] ); ?></p>
									<?php endif; ?>
									<?php if ( ! empty( $loc['url'] ) ) : ?>
										<a class="pal-program-single__location-link" href="<?php echo esc_url( $loc['url'] ); ?>"><?php esc_html_e( 'Register / Visit →', 'nashua-pal-core' ); ?></a>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $linked_events->have_posts() ) : ?>
					<div class="pal-program-single__events">
						<h3><?php esc_html_e( 'Upcoming Events', 'nashua-pal-core' ); ?></h3>
						<ul class="pal-program-single__events-list">
							<?php while ( $linked_events->have_posts() ) : $linked_events->the_post();
								$eid   = get_the_ID();
								$estart = get_post_meta( $eid, '_pal_event_start', true );
								$ets   = $estart ? strtotime( $estart ) : 0;
								?>
								<li>
									<div class="pal-event__date" aria-hidden="true">
										<span class="pal-event__date-month"><?php echo esc_html( wp_date( 'M', $ets ) ); ?></span>
										<span class="pal-event__date-day"><?php echo esc_html( wp_date( 'j', $ets ) ); ?></span>
									</div>
									<div>
										<a href="<?php echo esc_url( get_permalink( $eid ) ); ?>"><strong><?php echo esc_html( get_the_title( $eid ) ); ?></strong></a>
										<div class="pal-event__meta"><?php echo esc_html( wp_date( 'l · g:i a', $ets ) ); ?></div>
									</div>
								</li>
							<?php endwhile; wp_reset_postdata(); ?>
						</ul>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $gallery_imgs ) ) : ?>
					<div class="pal-program-single__gallery">
						<h3><i class="fas fa-images" style="margin-right:8px;color:var(--pal-yellow);"></i><?php esc_html_e( 'Photo Gallery', 'nashua-pal-core' ); ?></h3>
						<div class="pal-program-gallery-grid">
							<?php foreach ( $gallery_imgs as $gi => $img ) : ?>
								<div class="pal-program-gallery-grid__item" data-program-lightbox="<?php echo $gi; ?>">
									<img src="<?php echo esc_url( $img['thumb'] ); ?>" alt="<?php echo esc_attr( $img['alt'] ); ?>" loading="lazy" />
								</div>
							<?php endforeach; ?>
						</div>
						<script type="application/json" id="pal-program-gallery-data"><?php echo wp_json_encode( $gallery_imgs ); ?></script>
					</div>
				<?php endif; ?>

				<?php if ( $show_cta && $cta ) : ?>
					<div class="pal-program-single__cta-wrap">
						<a class="pal-btn pal-btn-yellow pal-program-single__cta" href="<?php echo esc_url( $cta ); ?>"><?php echo esc_html( $cta_label ); ?> &rarr;</a>
					</div>
				<?php endif; ?>
			</div>
		</article>

		<?php if ( ! empty( $gallery_imgs ) ) : ?>
		<div class="pal-program-lightbox" id="pal-program-lightbox" style="display:none;">
			<div class="pal-program-lightbox__backdrop"></div>
			<button type="button" class="pal-program-lightbox__close" aria-label="<?php esc_attr_e( 'Close', 'nashua-pal-core' ); ?>">&times;</button>
			<button type="button" class="pal-program-lightbox__arrow pal-program-lightbox__arrow--prev" aria-label="<?php esc_attr_e( 'Previous', 'nashua-pal-core' ); ?>"><i class="fas fa-chevron-left"></i></button>
			<button type="button" class="pal-program-lightbox__arrow pal-program-lightbox__arrow--next" aria-label="<?php esc_attr_e( 'Next', 'nashua-pal-core' ); ?>"><i class="fas fa-chevron-right"></i></button>
			<img class="pal-program-lightbox__img" src="" alt="" />
			<div class="pal-program-lightbox__counter"></div>
		</div>
		<script>
		(function(){
			var lb    = document.getElementById('pal-program-lightbox');
			if(!lb) return;
			var data  = JSON.parse(document.getElementById('pal-program-gallery-data').textContent);
			var img   = lb.querySelector('.pal-program-lightbox__img');
			var ctr   = lb.querySelector('.pal-program-lightbox__counter');
			var cur   = 0, total = data.length;

			function show(i){
				cur = (i % total + total) % total;
				img.src = data[cur].full;
				img.alt = data[cur].alt;
				ctr.textContent = (cur + 1) + ' / ' + total;
				lb.style.display = 'flex';
				document.body.style.overflow = 'hidden';
			}
			function hide(){
				lb.style.display = 'none';
				document.body.style.overflow = '';
			}

			document.querySelectorAll('[data-program-lightbox]').forEach(function(el){
				el.addEventListener('click', function(){ show(parseInt(el.dataset.programLightbox, 10)); });
			});

			lb.querySelector('.pal-program-lightbox__close').addEventListener('click', hide);
			lb.querySelector('.pal-program-lightbox__backdrop').addEventListener('click', hide);
			lb.querySelector('.pal-program-lightbox__arrow--prev').addEventListener('click', function(){ show(cur - 1); });
			lb.querySelector('.pal-program-lightbox__arrow--next').addEventListener('click', function(){ show(cur + 1); });

			document.addEventListener('keydown', function(e){
				if(lb.style.display === 'none') return;
				if(e.key === 'Escape') hide();
				if(e.key === 'ArrowLeft')  show(cur - 1);
				if(e.key === 'ArrowRight') show(cur + 1);
			});
		})();
		</script>
		<?php endif; ?>

		<?php return ob_get_clean();
	}

	/**
	 * Program Info Cards — standalone shortcode for full-width placement.
	 *
	 * Auto-detects the current program (same logic as [pal_program]).
	 * Place in its own full-width Elementor section below the program content.
	 *
	 * Usage: [pal_program_info_cards]
	 *
	 * @since 1.21.33
	 */
	public function program_info_cards( $atts ) {
		$a = shortcode_atts( array( 'id' => 0, 'slug' => '' ), $atts, 'pal_program_info_cards' );

		$post_id = (int) $a['id'];
		if ( ! $post_id && $a['slug'] ) {
			$page = get_page_by_path( $a['slug'], OBJECT, NPAL_CPT_Program::POST_TYPE );
			if ( $page ) { $post_id = $page->ID; }
		}
		if ( ! $post_id ) {
			$qid  = (int) get_queried_object_id();
			$qobj = get_queried_object();
			if ( $qid && $qobj instanceof WP_Post && NPAL_CPT_Program::POST_TYPE === $qobj->post_type ) {
				$post_id = $qid;
			} elseif ( is_singular( NPAL_CPT_Program::POST_TYPE ) ) {
				$post_id = get_the_ID();
			}
		}
		if ( ! $post_id ) { return ''; }

		$data = get_post_meta( $post_id, '_pal_program_additional_info', true );
		if ( ! is_array( $data ) ) { return ''; }

		$heading = isset( $data['heading'] ) ? $data['heading'] : '';
		$cards   = isset( $data['cards'] ) && is_array( $data['cards'] ) ? $data['cards'] : array();
		if ( empty( $cards ) ) { return ''; }

		ob_start(); ?>
		<section class="pal-info-cards-section">
			<div class="pal-info-cards">
				<?php if ( $heading ) : ?>
					<h3 class="pal-info-cards__heading"><?php echo esc_html( $heading ); ?></h3>
				<?php endif; ?>
				<div class="pal-info-cards__grid">
					<?php foreach ( $cards as $card ) : if ( empty( $card['title'] ) ) continue; ?>
						<div class="pal-info-card">
							<?php if ( ! empty( $card['icon'] ) ) : ?>
								<div class="pal-info-card__icon"><?php echo NPAL_Icons::render_or_fa( $card['icon'] ); ?></div>
							<?php endif; ?>
							<h4 class="pal-info-card__title"><?php echo esc_html( $card['title'] ); ?></h4>
							<?php if ( ! empty( $card['desc'] ) ) : ?>
								<div class="pal-info-card__desc">
									<div class="pal-info-card__desc-text"><?php echo wp_kses_post( wpautop( $card['desc'] ) ); ?></div>
								</div>
							<?php endif; ?>
							<?php
							$btn_label = ! empty( $card['btn_label'] ) ? $card['btn_label'] : __( 'Learn More', 'nashua-pal-core' );
							if ( ! empty( $card['btn_url'] ) ) : ?>
								<a class="pal-info-card__btn" href="<?php echo esc_url( $card['btn_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $btn_label ); ?> &rarr;</a>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<script>
		(function(){
			document.querySelectorAll('.pal-info-cards .pal-info-card__desc-text').forEach(function(el){
				if (el.scrollHeight <= el.offsetHeight) return;
				var btn = document.createElement('button');
				btn.className = 'pal-info-card__desc-toggle';
				btn.type = 'button';
				btn.textContent = 'Show more';
				btn.addEventListener('click', function(){
					var expanded = el.classList.toggle('is-expanded');
					btn.textContent = expanded ? 'Show less' : 'Show more';
				});
				el.parentNode.appendChild(btn);
			});
		})();
		</script>
		<?php return ob_get_clean();
	}

	/* -------- Programs Page — category-grouped accordion layout --------
	 * Renders the full Programs page: loops every pal_program_category
	 * term ordered by _pal_cat_order, and for each category outputs a
	 * section with the category image, description, and an accordion of
	 * the programs assigned to that category. Programs with contacts or
	 * accreditation get those rendered inline. JS is self-contained.
	 *
	 * Usage: [pal_programs_page]
	 *
	 * @since 1.10.0
	 */
	public function programs_page( $atts ) {
		$a = shortcode_atts( array(
			'show_empty' => 'no', // show categories with no programs
		), $atts, 'pal_programs_page' );

		$terms = get_terms( array(
			'taxonomy'   => 'pal_program_category',
			'hide_empty' => ( 'yes' !== strtolower( (string) $a['show_empty'] ) ),
			'meta_key'   => '_pal_cat_order',
			'orderby'    => 'meta_value_num',
			'order'      => 'ASC',
		) );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<p class="pal-empty" style="text-align:center;color:#646970;font-size:13px;">'
					. esc_html__( 'No program categories yet — add some at Programs → Program Categories.', 'nashua-pal-core' )
					. '</p>';
			}
			return '';
		}

		// Sort: terms without _pal_cat_order fall back to 0, then alphabetical.
		usort( $terms, function( $a, $b ) {
			$oa = (int) get_term_meta( $a->term_id, '_pal_cat_order', true );
			$ob = (int) get_term_meta( $b->term_id, '_pal_cat_order', true );
			if ( $oa !== $ob ) { return $oa - $ob; }
			return strcasecmp( $a->name, $b->name );
		} );

		ob_start();
		?>
		<div class="pal-progpage">
		<?php foreach ( $terms as $term ) :
			$cat_image_id = (int) get_term_meta( $term->term_id, '_pal_cat_image', true );
			$cat_image    = $cat_image_id ? wp_get_attachment_image_url( $cat_image_id, 'large' ) : '';
			$cat_desc     = term_description( $term->term_id, 'pal_program_category' );
			$slug         = sanitize_html_class( $term->slug );

			// Query programs in this category.
			$programs = get_posts( array(
				'post_type'      => NPAL_CPT_Program::POST_TYPE,
				'posts_per_page' => -1,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
				'tax_query'      => array(
					array(
						'taxonomy' => 'pal_program_category',
						'terms'    => $term->term_id,
					),
				),
			) );
			?>
			<section class="pal-progpage__section" id="prog-<?php echo esc_attr( $slug ); ?>">
				<div class="pal-progpage__header">
					<?php if ( $cat_image ) : ?>
						<div class="pal-progpage__image">
							<img src="<?php echo esc_url( $cat_image ); ?>" alt="<?php echo esc_attr( $term->name ); ?>" loading="lazy" />
						</div>
					<?php endif; ?>
					<h2 class="pal-progpage__title"><?php echo esc_html( $term->name ); ?></h2>
					<?php if ( $cat_desc ) : ?>
						<div class="pal-progpage__desc"><?php echo wp_kses_post( $cat_desc ); ?></div>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $programs ) ) : ?>
					<div class="pal-progpage__accordions">
					<?php foreach ( $programs as $prog ) :
						$pid           = $prog->ID;
						$season        = get_post_meta( $pid, '_pal_program_season', true );
						$age_range     = get_post_meta( $pid, '_pal_program_age_range', true );
						$subtitle      = get_post_meta( $pid, '_pal_program_subtitle', true );
						$cta_url       = NPAL_Program_Meta::get_program_url( $pid );
						$cta_label     = get_post_meta( $pid, '_pal_program_cta_label', true ) ?: __( 'Learn More', 'nashua-pal-core' );
						$accred        = get_post_meta( $pid, '_pal_program_accreditation', true );
						$contacts      = get_post_meta( $pid, '_pal_program_contacts', true );
						$locs          = get_post_meta( $pid, '_pal_program_locations', true );
						$addinfo       = get_post_meta( $pid, '_pal_program_additional_info', true );
						$prog_status   = get_post_meta( $pid, '_pal_program_status', true );
						$prog_cost     = get_post_meta( $pid, '_pal_program_cost', true );
						$prog_reg_url  = get_post_meta( $pid, '_pal_program_register_url', true );
						$content       = apply_filters( 'the_content', $prog->post_content );

						// Build tag pills (season + age + cost).
						$tags = array();
						if ( $season )    { $tags[] = __( 'Season: ', 'nashua-pal-core' ) . $season; }
						if ( $age_range ) { $tags[] = __( 'Ages ', 'nashua-pal-core' ) . $age_range; }
						?>
						<div class="pal-accordion" data-accordion>
							<button class="pal-accordion__trigger" type="button" aria-expanded="false">
								<span class="pal-accordion__label">
									<span class="pal-accordion__name">
										<?php echo esc_html( get_the_title( $pid ) ); ?>
										<?php if ( $prog_status ) : ?>
											<span class="pal-accordion__status pal-accordion__status--<?php echo esc_attr( $prog_status ); ?>"><?php echo esc_html( $this->program_status_label( $prog_status ) ); ?></span>
										<?php endif; ?>
									</span>
									<?php if ( ! empty( $tags ) || $prog_cost ) : ?>
										<span class="pal-accordion__tags">
											<?php foreach ( $tags as $tag ) : ?>
												<span class="pal-accordion__tag"><?php echo esc_html( $tag ); ?></span>
											<?php endforeach; ?>
											<?php if ( $prog_cost ) : ?>
												<span class="pal-accordion__tag pal-accordion__tag--cost"><?php echo esc_html( __( 'Cost: ', 'nashua-pal-core' ) . $prog_cost ); ?></span>
											<?php endif; ?>
										</span>
									<?php endif; ?>
									<?php if ( $subtitle ) : ?>
										<span class="pal-accordion__subtitle"><?php echo esc_html( $subtitle ); ?></span>
									<?php endif; ?>
								</span>
								<span class="pal-accordion__chevron" aria-hidden="true">
									<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
								</span>
							</button>
							<div class="pal-accordion__panel" hidden>
								<div class="pal-accordion__body">

									<?php if ( $content ) : ?>
										<div class="pal-accordion__content"><?php echo $content; ?></div>
									<?php endif; ?>

									<?php
									$ai_heading_pp = isset( $addinfo['heading'] ) ? $addinfo['heading'] : '';
									$ai_cards_pp   = isset( $addinfo['cards'] ) && is_array( $addinfo['cards'] ) ? $addinfo['cards'] : array();
									if ( ! empty( $ai_cards_pp ) ) : ?>
										<div class="pal-info-cards pal-info-cards--nested">
											<?php if ( $ai_heading_pp ) : ?>
												<h4 class="pal-info-cards__heading"><?php echo esc_html( $ai_heading_pp ); ?></h4>
											<?php endif; ?>
											<div class="pal-info-cards__grid">
												<?php foreach ( $ai_cards_pp as $card ) : if ( empty( $card['title'] ) ) continue; ?>
													<div class="pal-info-card">
														<?php if ( ! empty( $card['icon'] ) ) : ?>
															<div class="pal-info-card__icon"><?php echo NPAL_Icons::render_or_fa( $card['icon'] ); ?></div>
														<?php endif; ?>
														<h4 class="pal-info-card__title"><?php echo esc_html( $card['title'] ); ?></h4>
														<?php if ( ! empty( $card['desc'] ) ) : ?>
															<div class="pal-info-card__desc">
																<div class="pal-info-card__desc-text"><?php echo wp_kses_post( wpautop( $card['desc'] ) ); ?></div>
															</div>
														<?php endif; ?>
														<?php
														$btn_label_pp = ! empty( $card['btn_label'] ) ? $card['btn_label'] : __( 'Learn More', 'nashua-pal-core' );
														if ( ! empty( $card['btn_url'] ) ) : ?>
															<a class="pal-info-card__btn" href="<?php echo esc_url( $card['btn_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $btn_label_pp ); ?> &rarr;</a>
														<?php endif; ?>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									<?php endif; ?>

									<?php if ( is_array( $contacts ) && ! empty( $contacts ) ) : ?>
										<div class="pal-accordion__contacts">
											<h4 class="pal-accordion__contacts-heading"><?php esc_html_e( 'For more information, contact:', 'nashua-pal-core' ); ?></h4>
											<?php foreach ( $contacts as $c ) : if ( empty( $c['name'] ) ) continue; ?>
												<div class="pal-accordion__contact">
													<strong><?php echo esc_html( $c['name'] ); ?></strong>
													<?php if ( ! empty( $c['title'] ) ) : ?>
														<span class="pal-accordion__contact-title">, <?php echo esc_html( $c['title'] ); ?></span>
													<?php endif; ?>
													<?php if ( ! empty( $c['phone'] ) ) : ?>
														<br /><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $c['phone'] ) ); ?>"><?php echo esc_html( $c['phone'] ); ?></a>
													<?php endif; ?>
													<?php if ( ! empty( $c['email'] ) ) : ?>
														 — <a href="mailto:<?php echo esc_attr( $c['email'] ); ?>">email <?php echo esc_html( explode( ' ', $c['name'] )[0] ); ?></a>
													<?php endif; ?>
												</div>
											<?php endforeach; ?>
										</div>
									<?php endif; ?>

									<?php if ( $accred ) : ?>
										<p class="pal-accordion__accreditation">
											<em><?php echo esc_html( $accred ); ?></em>
										</p>
									<?php endif; ?>

									<?php if ( is_array( $locs ) && ! empty( $locs ) ) : ?>
										<div class="pal-accordion__locations">
											<h4><?php esc_html_e( 'Where it\'s offered', 'nashua-pal-core' ); ?></h4>
											<div class="pal-accordion__locations-grid">
												<?php foreach ( $locs as $loc ) : if ( empty( $loc['name'] ) && empty( $loc['address'] ) ) continue; ?>
													<div class="pal-accordion__location">
														<?php if ( ! empty( $loc['name'] ) ) : ?><strong><?php echo esc_html( $loc['name'] ); ?></strong><?php endif; ?>
														<?php if ( ! empty( $loc['address'] ) ) : ?><br /><span><?php echo esc_html( $loc['address'] ); ?></span><?php endif; ?>
														<?php if ( ! empty( $loc['when'] ) ) : ?><br /><span><?php echo esc_html( $loc['when'] ); ?></span><?php endif; ?>
														<?php if ( ! empty( $loc['url'] ) ) : ?><br /><a href="<?php echo esc_url( $loc['url'] ); ?>"><?php esc_html_e( 'Register →', 'nashua-pal-core' ); ?></a><?php endif; ?>
													</div>
												<?php endforeach; ?>
											</div>
										</div>
									<?php endif; ?>

									<?php if ( $prog_reg_url || $cta_url ) : ?>
										<div class="pal-accordion__cta-wrap">
											<?php if ( $prog_reg_url ) : ?>
												<a class="pal-btn pal-btn-yellow" href="<?php echo esc_url( $prog_reg_url ); ?>"><?php esc_html_e( 'Register Now', 'nashua-pal-core' ); ?> &rarr;</a>
											<?php endif; ?>
											<?php if ( $cta_url ) : ?>
												<a class="pal-btn pal-btn-navy" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?> &rarr;</a>
											<?php endif; ?>
										</div>
									<?php endif; ?>

								</div>
							</div>
						</div>
					<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
		</div>

		<script>
		(function(){
			document.querySelectorAll('[data-accordion]').forEach(function(acc){
				var trigger = acc.querySelector('.pal-accordion__trigger');
				var panel   = acc.querySelector('.pal-accordion__panel');
				if (!trigger || !panel) return;
				trigger.addEventListener('click', function(){
					var expanded = trigger.getAttribute('aria-expanded') === 'true';
					trigger.setAttribute('aria-expanded', expanded ? 'false' : 'true');
					if (expanded) {
						panel.setAttribute('hidden', '');
					} else {
						panel.removeAttribute('hidden');
					}
				});
			});
		})();
		</script>
		<?php
		return ob_get_clean();
	}

	/* -------- upcoming events list (merges CPT + Google Calendar feeds) -------- */
	public function upcoming_events( $atts ) {
		$a = shortcode_atts( array(
			'count'      => 3,
			'sources'    => 'all',     // 'all' | 'cpt' | 'gcal'
			'show_image' => 'no',      // 'yes' | 'no'
			'image_size' => 'medium',  // any registered image size
			'view'       => 'list',    // 'list' | 'grid'
			'columns'    => 3,         // 1–4 (used only when view="grid")
		), $atts, 'pal_upcoming_events' );

		$show_image = ( 'yes' === strtolower( (string) $a['show_image'] ) || '1' === (string) $a['show_image'] || 'true' === strtolower( (string) $a['show_image'] ) );
		$view       = ( 'grid' === strtolower( (string) $a['view'] ) ) ? 'grid' : 'list';
		$columns    = max( 1, min( 4, (int) $a['columns'] ) );

		$now      = current_time( 'timestamp' );
		$combined = array();

		// Source 1: CPT events.
		if ( in_array( $a['sources'], array( 'all', 'cpt' ), true ) ) {
			$q = new WP_Query( array(
				'post_type'      => NPAL_CPT_Event::POST_TYPE,
				'posts_per_page' => 50,
				'post_status'    => 'publish',
				'meta_key'       => '_pal_event_start',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => array( array( 'key' => '_pal_event_start', 'compare' => 'EXISTS' ) ),
			) );
			while ( $q->have_posts() ) {
				$q->the_post();
				$id    = get_the_ID();
				$start = get_post_meta( $id, '_pal_event_start', true );
				$ts    = $start ? strtotime( $start ) : 0;
				if ( $ts && $ts >= $now ) {
					$end_raw  = get_post_meta( $id, '_pal_event_end', true );
					$combined[] = array(
						'title'      => get_the_title( $id ),
						'start_ts'   => $ts,
						'end_ts'     => $end_raw ? strtotime( $end_raw ) : 0,
						'all_day'    => '1' === get_post_meta( $id, '_pal_event_all_day', true ),
						'location'   => get_post_meta( $id, '_pal_event_location', true ),
						'url'        => get_permalink( $id ),
						'image'      => $show_image ? $this->get_featured_image( $id, $a['image_size'] ) : '',
						'reg_status' => get_post_meta( $id, '_pal_event_reg_status', true ),
						'categories' => wp_get_post_terms( $id, 'pal_event_category', array( 'fields' => 'names' ) ),
						'source'     => 'cpt',
					);
				}
			}
			wp_reset_postdata();
		}

		// Source 2: Google Calendar feeds.
		if ( in_array( $a['sources'], array( 'all', 'gcal' ), true ) ) {
			$gcal = NPAL_Google_Calendar::get_upcoming( 50 );
			foreach ( $gcal as $e ) {
				$combined[] = array(
					'title'      => $e['title'],
					'start_ts'   => $e['start_ts'],
					'end_ts'     => 0,
					'all_day'    => false,
					'location'   => $e['location'] ?? '',
					'url'        => $e['url'] ?? '',
					'image'      => '',
					'reg_status' => '',
					'categories' => array(),
					'source'     => 'gcal',
				);
			}
		}

		usort( $combined, function( $x, $y ) { return $x['start_ts'] <=> $y['start_ts']; } );
		$cap = (int) $a['count'];
		if ( $cap > 0 ) {
			$combined = array_slice( $combined, 0, $cap );
		}

		if ( empty( $combined ) ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<p class="pal-empty" style="color:#646970;font-size:13px;">' . esc_html__( 'No upcoming events — add one at Events → Add Event, or paste a Google Calendar iCal URL under Nashua PAL → Settings.', 'nashua-pal-core' ) . '</p>';
			}
			return '<p class="pal-empty" style="color:#646970;font-size:13px;">' . esc_html__( 'Check back soon for upcoming events.', 'nashua-pal-core' ) . '</p>';
		}

		$ul_class  = 'pal-events';
		$ul_class .= $show_image ? ' pal-events--with-images' : '';
		$ul_class .= ' pal-events--' . $view;
		if ( 'grid' === $view ) { $ul_class .= ' pal-events--cols-' . $columns; }

		ob_start(); ?>
		<ul class="<?php echo esc_attr( $ul_class ); ?>">
		<?php foreach ( $combined as $e ) :
			$ts    = $e['start_ts'];
			$href  = $e['url'] ?: '';
			$title = $e['title'];
			$loc   = $e['location'];
			$img   = $show_image ? ( ! empty( $e['image'] ) ? $e['image'] : '' ) : '';
			$is_clickable = ( $href && '#' !== $href );
			$reg   = ! empty( $e['reg_status'] ) ? $e['reg_status'] : '';
			$cats  = is_array( $e['categories'] ) ? $e['categories'] : array();
			$is_all_day = ! empty( $e['all_day'] );
			?>
			<li class="pal-event<?php echo $img ? ' pal-event--with-image' : ''; ?><?php echo $is_clickable ? ' pal-event--clickable' : ''; ?>">
				<?php if ( $img ) : ?>
					<span class="pal-event__media" style="background-image:url(<?php echo esc_url( $img ); ?>);" aria-hidden="true"></span>
				<?php endif; ?>
				<div class="pal-event__date" aria-hidden="true">
					<span class="pal-event__date-month"><?php echo esc_html( wp_date( 'M', $ts ) ); ?></span>
					<span class="pal-event__date-day"><?php echo esc_html( wp_date( 'j', $ts ) ); ?></span>
				</div>
				<div class="pal-event__body">
					<?php if ( $reg ) : ?>
						<span class="pal-event__reg-badge pal-event__reg-badge--<?php echo esc_attr( $reg ); ?>"><?php echo esc_html( $this->reg_status_label( $reg ) ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $cats ) ) : ?>
						<span class="pal-event__cats"><?php foreach ( $cats as $cat_name ) : ?><span class="pal-event__cat"><?php echo esc_html( $cat_name ); ?></span><?php endforeach; ?></span>
					<?php endif; ?>
					<h4 class="pal-event__title">
						<?php if ( $is_clickable ) : ?>
							<a class="pal-event__link" href="<?php echo esc_url( $href ); ?>"<?php echo 'gcal' === $e['source'] ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $title ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $title ); ?>
						<?php endif; ?>
					</h4>
					<div class="pal-event__meta">
						<?php if ( $is_all_day ) : ?>
							<?php
							echo esc_html( wp_date( 'l, M j, Y', $ts ) );
							if ( ! empty( $e['end_ts'] ) && wp_date( 'Y-m-d', $e['end_ts'] ) !== wp_date( 'Y-m-d', $ts ) ) {
								echo ' – ' . esc_html( wp_date( 'M j, Y', $e['end_ts'] ) );
							}
							echo ' · ' . esc_html__( 'All day', 'nashua-pal-core' );
							?>
						<?php else : ?>
							<?php echo esc_html( wp_date( 'l · g:i a', $ts ) ); ?>
						<?php endif; ?>
						<?php if ( $loc ) echo ' · ' . esc_html( $loc ); ?>
					</div>
					<?php if ( 'cpt' === $e['source'] && $ts >= time() ) : ?>
						<a class="pal-event__add-cal" href="<?php echo esc_url( $this->build_gcal_url( $title, $ts, ! empty( $e['end_ts'] ) ? $e['end_ts'] : $ts + 3600, $loc, $href ) ); ?>" target="_blank" rel="noopener" title="<?php esc_attr_e( 'Add to Google Calendar', 'nashua-pal-core' ); ?>">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
							<?php esc_html_e( 'Add to Calendar', 'nashua-pal-core' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
		</ul>
		<?php return ob_get_clean();
	}

	/* -------- full events list (past + upcoming, with filtering) ----------
	 * Different from [pal_upcoming_events]: this one shows EVERY event
	 * regardless of whether it's in the past, with filter knobs for when /
	 * date range / source, plus display knobs for view / columns / image /
	 * fields shown. Use this for a dedicated /events page or an "Archive"
	 * section that surfaces past events too.
	 */
	public function events( $atts ) {
		$a = shortcode_atts( array(
			// Filtering
			'when'          => 'all',     // 'all' | 'upcoming' | 'past'
			'sources'       => 'all',     // 'all' | 'cpt' | 'gcal'
			'count'         => 12,
			'order'         => 'auto',    // 'auto' | 'asc' | 'desc'
			'from'          => '',        // YYYY-MM-DD or any strtotime() input
			'to'            => '',        // YYYY-MM-DD or any strtotime() input

			// Display
			'view'          => 'grid',    // 'grid' | 'list'
			'columns'       => 3,
			'show_image'    => 'yes',
			'image_size'    => 'medium',
			'show_date'     => 'yes',
			'show_location' => 'yes',
			'show_excerpt'  => 'no',
			'show_status'   => 'no',      // adds "Upcoming" / "Past" badge
		), $atts, 'pal_events' );

		$when    = strtolower( (string) $a['when'] );
		if ( ! in_array( $when, array( 'all', 'upcoming', 'past' ), true ) ) { $when = 'all'; }
		$sources = strtolower( (string) $a['sources'] );
		if ( ! in_array( $sources, array( 'all', 'cpt', 'gcal' ), true ) ) { $sources = 'all'; }
		$order   = strtolower( (string) $a['order'] );
		if ( 'auto' === $order ) {
			$order = ( 'past' === $when ) ? 'desc' : ( 'upcoming' === $when ? 'asc' : 'desc' );
		} elseif ( ! in_array( $order, array( 'asc', 'desc' ), true ) ) {
			$order = 'desc';
		}
		// Allow count="-1" / "0" to mean "no cap" (WP_Query convention).
		// Previously max(1, …) collapsed -1 to 1, hiding all but one event.
		$count_raw = (int) $a['count'];
		$count     = ( $count_raw > 0 ) ? $count_raw : PHP_INT_MAX;
		$show_image = in_array( strtolower( (string) $a['show_image'] ), array( 'yes', '1', 'true' ), true );
		$show_date  = in_array( strtolower( (string) $a['show_date'] ), array( 'yes', '1', 'true' ), true );
		$show_loc   = in_array( strtolower( (string) $a['show_location'] ), array( 'yes', '1', 'true' ), true );
		$show_exc   = in_array( strtolower( (string) $a['show_excerpt'] ), array( 'yes', '1', 'true' ), true );
		$show_stat  = in_array( strtolower( (string) $a['show_status'] ), array( 'yes', '1', 'true' ), true );
		$view       = ( 'list' === strtolower( (string) $a['view'] ) ) ? 'list' : 'grid';
		$columns    = max( 1, min( 4, (int) $a['columns'] ) );

		$now    = current_time( 'timestamp' );
		$from_ts = $a['from'] ? strtotime( $a['from'] ) : 0;
		$to_ts   = $a['to']   ? strtotime( $a['to'] )   : 0;
		// "to" interpreted as end-of-day if it's a bare date with no time.
		if ( $to_ts && false === strpos( $a['to'], ':' ) ) { $to_ts += 86399; }

		$combined = array();

		// Source 1: CPT events.
		if ( in_array( $sources, array( 'all', 'cpt' ), true ) ) {
			$q = new WP_Query( array(
				'post_type'      => NPAL_CPT_Event::POST_TYPE,
				'posts_per_page' => 200,
				'post_status'    => 'publish',
				'meta_key'       => '_pal_event_start',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => array( array( 'key' => '_pal_event_start', 'compare' => 'EXISTS' ) ),
			) );
			while ( $q->have_posts() ) {
				$q->the_post();
				$id    = get_the_ID();
				$start = get_post_meta( $id, '_pal_event_start', true );
				$ts    = $start ? strtotime( $start ) : 0;
				if ( ! $ts ) { continue; }
				$end_raw  = get_post_meta( $id, '_pal_event_end', true );
				$combined[] = array(
					'title'      => get_the_title( $id ),
					'start_ts'   => $ts,
					'end_ts'     => $end_raw ? strtotime( $end_raw ) : 0,
					'all_day'    => '1' === get_post_meta( $id, '_pal_event_all_day', true ),
					'location'   => get_post_meta( $id, '_pal_event_location', true ),
					'url'        => get_permalink( $id ),
					'image'      => $show_image ? $this->get_featured_image( $id, $a['image_size'] ) : '',
					'excerpt'    => $show_exc ? wp_strip_all_tags( get_the_excerpt( $id ) ) : '',
					'reg_status' => get_post_meta( $id, '_pal_event_reg_status', true ),
					'categories' => wp_get_post_terms( $id, 'pal_event_category', array( 'fields' => 'names' ) ),
					'source'     => 'cpt',
				);
			}
			wp_reset_postdata();
		}

		// Source 2: Google Calendar feeds.
		if ( in_array( $sources, array( 'all', 'gcal' ), true ) ) {
			$gcal = NPAL_Google_Calendar::get_upcoming( 200 );
			foreach ( $gcal as $e ) {
				$combined[] = array(
					'title'      => $e['title'],
					'start_ts'   => $e['start_ts'],
					'end_ts'     => 0,
					'all_day'    => false,
					'location'   => $e['location'] ?? '',
					'url'        => $e['url'] ?? '',
					'image'      => '',
					'excerpt'    => $show_exc ? ( $e['description'] ?? '' ) : '',
					'reg_status' => '',
					'categories' => array(),
					'source'     => 'gcal',
				);
			}
		}

		// Apply when / range filters.
		$combined = array_values( array_filter( $combined, function( $e ) use ( $when, $now, $from_ts, $to_ts ) {
			$ts = (int) ( $e['start_ts'] ?? 0 );
			if ( ! $ts ) { return false; }
			if ( 'upcoming' === $when && $ts < $now ) { return false; }
			if ( 'past'     === $when && $ts >= $now ) { return false; }
			if ( $from_ts && $ts < $from_ts ) { return false; }
			if ( $to_ts   && $ts > $to_ts   ) { return false; }
			return true;
		} ) );

		// Sort + cap.
		usort( $combined, function( $x, $y ) use ( $order ) {
			$cmp = $x['start_ts'] <=> $y['start_ts'];
			return ( 'desc' === $order ) ? -$cmp : $cmp;
		} );
		$combined = array_slice( $combined, 0, $count );

		if ( empty( $combined ) ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<p class="pal-empty" style="color:#646970;font-size:13px;">' . esc_html__( 'No events match these filters. Try removing the date range or switching when="all".', 'nashua-pal-core' ) . '</p>';
			}
			return '<p class="pal-empty" style="color:#646970;font-size:13px;">' . esc_html__( 'No events to show.', 'nashua-pal-core' ) . '</p>';
		}

		$ul_class  = 'pal-events';
		$ul_class .= $show_image ? ' pal-events--with-images' : '';
		$ul_class .= ' pal-events--' . $view;
		if ( 'grid' === $view ) { $ul_class .= ' pal-events--cols-' . $columns; }

		ob_start(); ?>
		<ul class="<?php echo esc_attr( $ul_class ); ?>">
		<?php foreach ( $combined as $e ) :
			$ts       = $e['start_ts'];
			$href     = $e['url'] ?: '';
			$title    = $e['title'];
			$loc      = $e['location'];
			$img      = $show_image ? ( ! empty( $e['image'] ) ? $e['image'] : '' ) : '';
			$excerpt  = $show_exc ? trim( (string) $e['excerpt'] ) : '';
			$is_past  = ( $ts < $now );
			$status   = $is_past ? __( 'Past', 'nashua-pal-core' ) : __( 'Upcoming', 'nashua-pal-core' );
			$is_clickable = ( $href && '#' !== $href );
			$reg   = ! empty( $e['reg_status'] ) ? $e['reg_status'] : '';
			$cats  = is_array( $e['categories'] ) ? $e['categories'] : array();
			$is_all_day = ! empty( $e['all_day'] );
			?>
			<li class="pal-event<?php echo $img ? ' pal-event--with-image' : ''; ?> pal-event--<?php echo $is_past ? 'past' : 'upcoming'; ?><?php echo $is_clickable ? ' pal-event--clickable' : ''; ?>">
				<?php if ( $img ) : ?>
					<span class="pal-event__media" style="background-image:url(<?php echo esc_url( $img ); ?>);" aria-hidden="true"></span>
				<?php endif; ?>
				<?php if ( $show_date ) : ?>
					<div class="pal-event__date" aria-hidden="true">
						<span class="pal-event__date-month"><?php echo esc_html( wp_date( 'M', $ts ) ); ?></span>
						<span class="pal-event__date-day"><?php echo esc_html( wp_date( 'j', $ts ) ); ?></span>
					</div>
				<?php endif; ?>
				<div class="pal-event__body">
					<?php if ( $show_stat ) : ?>
						<span class="pal-event__status pal-event__status--<?php echo $is_past ? 'past' : 'upcoming'; ?>"><?php echo esc_html( $status ); ?></span>
					<?php endif; ?>
					<?php if ( $reg ) : ?>
						<span class="pal-event__reg-badge pal-event__reg-badge--<?php echo esc_attr( $reg ); ?>"><?php echo esc_html( $this->reg_status_label( $reg ) ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $cats ) ) : ?>
						<span class="pal-event__cats"><?php foreach ( $cats as $cat_name ) : ?><span class="pal-event__cat"><?php echo esc_html( $cat_name ); ?></span><?php endforeach; ?></span>
					<?php endif; ?>
					<h4 class="pal-event__title">
						<?php if ( $is_clickable ) : ?>
							<a class="pal-event__link" href="<?php echo esc_url( $href ); ?>"<?php echo 'gcal' === $e['source'] ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $title ); ?></a>
						<?php else : ?>
							<?php echo esc_html( $title ); ?>
						<?php endif; ?>
					</h4>
					<div class="pal-event__meta">
						<?php if ( $is_all_day ) : ?>
							<?php
							echo esc_html( wp_date( 'l, M j, Y', $ts ) );
							if ( ! empty( $e['end_ts'] ) && wp_date( 'Y-m-d', $e['end_ts'] ) !== wp_date( 'Y-m-d', $ts ) ) {
								echo ' – ' . esc_html( wp_date( 'M j, Y', $e['end_ts'] ) );
							}
							echo ' · ' . esc_html__( 'All day', 'nashua-pal-core' );
							?>
						<?php else : ?>
							<?php echo esc_html( wp_date( 'l, M j, Y · g:i a', $ts ) ); ?>
						<?php endif; ?>
						<?php if ( $show_loc && $loc ) echo ' · ' . esc_html( $loc ); ?>
					</div>
					<?php if ( $excerpt ) : ?>
						<p class="pal-event__excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 28, '…' ) ); ?></p>
					<?php endif; ?>
					<?php if ( 'cpt' === $e['source'] && $ts >= time() ) : ?>
						<a class="pal-event__add-cal" href="<?php echo esc_url( $this->build_gcal_url( $title, $ts, ! empty( $e['end_ts'] ) ? $e['end_ts'] : $ts + 3600, $loc, $href ) ); ?>" target="_blank" rel="noopener" title="<?php esc_attr_e( 'Add to Google Calendar', 'nashua-pal-core' ); ?>">
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
							<?php esc_html_e( 'Add to Calendar', 'nashua-pal-core' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</li>
		<?php endforeach; ?>
		</ul>
		<?php return ob_get_clean();
	}

	/* -------- single event date pill -------- */
	public function event_date_pill( $atts ) {
		$a = shortcode_atts( array( 'date' => '' ), $atts, 'pal_event_date_pill' );
		$ts = $a['date'] ? strtotime( $a['date'] ) : 0;
		if ( ! $ts ) { return ''; }
		ob_start(); ?>
		<div class="pal-event__date">
			<span class="pal-event__date-month"><?php echo esc_html( wp_date( 'M', $ts ) ); ?></span>
			<span class="pal-event__date-day"><?php echo esc_html( wp_date( 'j', $ts ) ); ?></span>
		</div>
		<?php return ob_get_clean();
	}

	/* -------- get involved list (SVG icons match the design mockup exactly) -------- */
	public function get_involved() {
		$rows = array();
		for ( $i = 1; $i <= 4; $i++ ) {
			$name = $this->opt( "gi_{$i}_name" );
			if ( '' === $name ) {
				continue;
			}
			$rows[] = array(
				'icon' => $this->opt( "gi_{$i}_icon" ),
				'name' => $name,
				'desc' => $this->opt( "gi_{$i}_desc" ),
				'url'  => $this->opt( "gi_{$i}_url" ),
			);
		}
		ob_start(); ?>
		<ul class="pal-getinvolved">
			<?php foreach ( $rows as $r ) : ?>
				<li>
					<span class="pal-getinvolved__icon"><?php echo NPAL_Icons::render_or_fa( $r['icon'] ); ?></span>
					<a href="<?php echo esc_url( $r['url'] ); ?>" style="text-decoration:none;flex:1;">
						<div class="pal-getinvolved__name"><?php echo esc_html( $r['name'] ); ?></div>
						<div class="pal-getinvolved__desc"><?php echo esc_html( $r['desc'] ); ?></div>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php return ob_get_clean();
	}

	/* -------- latest news -------- */
	public function latest_news( $atts ) {
		$a = shortcode_atts( array(
			'count'       => 3,
			'view'        => 'grid',
			'columns'     => 3,
			'show_filter' => '',
			'source'      => '',
			'cta_label'   => '',
			'cta_url'     => '',
			'cta_text'    => '',
			'show_cta'    => '',
		), $atts, 'pal_latest_news' );

		$view        = ( 'list' === strtolower( (string) $a['view'] ) ) ? 'list' : 'grid';
		$columns     = max( 1, min( 4, (int) $a['columns'] ) );
		$count       = max( 1, (int) $a['count'] );
		$show_filter = ( 'true' === strtolower( (string) $a['show_filter'] ) );
		$source      = strtolower( trim( (string) $a['source'] ) );

		// Determine which post types to query.
		$query_posts       = in_array( $source, array( '', 'posts' ), true );
		$query_newsletters = in_array( $source, array( '', 'newsletters' ), true );
		$query_events      = in_array( $source, array( '', 'events' ), true );

		$items = array();

		// --- Blog posts ---
		if ( $query_posts ) {
			$q_posts = new WP_Query( array(
				'post_type'      => 'post',
				'posts_per_page' => $count * 2,
				'post_status'    => 'publish',
				'no_found_rows'  => true,
			) );
			while ( $q_posts->have_posts() ) {
				$q_posts->the_post();
				$items[] = array(
					'type'         => 'news',
					'title'        => get_the_title(),
					'url'          => get_permalink(),
					'target'       => '_self',
					'image'        => $this->get_featured_image( get_the_ID(), 'medium_large' ),
					'date'         => (int) get_the_date( 'U' ),
					'date_display' => get_the_date(),
				);
			}
			wp_reset_postdata();
		}

		// --- Newsletters ---
		if ( $query_newsletters && post_type_exists( 'pal_newsletter' ) ) {
			$q_nl = new WP_Query( array(
				'post_type'      => 'pal_newsletter',
				'posts_per_page' => $count * 2,
				'post_status'    => 'publish',
				'no_found_rows'  => true,
			) );
			while ( $q_nl->have_posts() ) {
				$q_nl->the_post();
				$nl_id       = get_the_ID();
				$nl_url      = get_post_meta( $nl_id, '_pal_newsletter_url', true );
				$nl_date_raw = get_post_meta( $nl_id, '_pal_newsletter_date', true );
				$nl_ts       = $nl_date_raw ? strtotime( $nl_date_raw ) : (int) get_the_date( 'U' );
				$items[] = array(
					'type'         => 'newsletter',
					'title'        => get_the_title(),
					'url'          => $nl_url ? $nl_url : '#',
					'target'       => '_blank',
					'image'        => $this->get_featured_image( $nl_id, 'medium_large' ),
					'date'         => (int) $nl_ts,
					'date_display' => wp_date( get_option( 'date_format' ), $nl_ts ),
				);
			}
			wp_reset_postdata();
		}

		// --- Upcoming Events ---
		if ( $query_events && post_type_exists( 'pal_event' ) ) {
			$now = current_time( 'mysql' );
			$q_ev = new WP_Query( array(
				'post_type'      => 'pal_event',
				'posts_per_page' => $count * 2,
				'post_status'    => 'publish',
				'no_found_rows'  => true,
				'meta_key'       => '_pal_event_start',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => array(
					array(
						'key'     => '_pal_event_start',
						'value'   => $now,
						'compare' => '>=',
						'type'    => 'DATETIME',
					),
				),
			) );
			while ( $q_ev->have_posts() ) {
				$q_ev->the_post();
				$ev_id    = get_the_ID();
				$ev_start = get_post_meta( $ev_id, '_pal_event_start', true );
				$ev_ts    = $ev_start ? strtotime( $ev_start ) : (int) get_the_date( 'U' );
				$items[] = array(
					'type'         => 'event',
					'title'        => get_the_title(),
					'url'          => get_permalink(),
					'target'       => '_self',
					'image'        => $this->get_featured_image( $ev_id, 'medium_large' ),
					'date'         => (int) $ev_ts,
					'date_display' => wp_date( get_option( 'date_format' ), $ev_ts ),
					'reg_status'   => get_post_meta( $ev_id, '_pal_event_reg_status', true ),
				);
			}
			wp_reset_postdata();
		}

		// Fallback: nothing to show.
		if ( empty( $items ) ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<p class="pal-empty" style="text-align:center;color:#646970;font-size:13px;">'
					. esc_html__( 'No news yet — publish a post, add a newsletter, or create an event.', 'nashua-pal-core' )
					. '</p>';
			}
			return '';
		}

		// Guarantee each active source gets at least one slot, fill remainder newest-first.
		$by_type = array();
		foreach ( $items as $item ) {
			$by_type[ $item['type'] ][] = $item;
		}
		foreach ( $by_type as &$group ) {
			usort( $group, function ( $a, $b ) { return $b['date'] <=> $a['date']; } );
		}
		unset( $group );

		$reserved = array();
		foreach ( $by_type as $type => &$group ) {
			if ( ! empty( $group ) ) {
				$reserved[] = array_shift( $group );
			}
		}
		unset( $group );

		// Merge remaining items, sort newest first, fill remaining slots.
		$remaining = array();
		foreach ( $by_type as $group ) {
			$remaining = array_merge( $remaining, $group );
		}
		usort( $remaining, function ( $a, $b ) { return $b['date'] <=> $a['date']; } );

		$items = array_merge( $reserved, array_slice( $remaining, 0, max( 0, $count - count( $reserved ) ) ) );
		// Final sort newest first for display.
		usort( $items, function ( $a, $b ) { return $b['date'] <=> $a['date']; } );

		$type_labels = array(
			'news'       => __( 'News', 'nashua-pal-core' ),
			'newsletter' => __( 'Newsletter', 'nashua-pal-core' ),
			'event'      => __( 'Event', 'nashua-pal-core' ),
		);

		$active_sources = (int) $query_posts + (int) $query_newsletters + (int) $query_events;
		$has_mixed = $active_sources > 1;

		$is_slider   = ( 'grid' === $view && count( $items ) > 4 );
		$wrap_class  = 'pal-news pal-news--' . $view;
		if ( 'grid' === $view ) { $wrap_class .= ' pal-news--cols-' . $columns; }
		if ( $is_slider )       { $wrap_class .= ' pal-news--slider'; }

		ob_start();

		// Filter tabs (only when both sources are queried).
		if ( $show_filter && $has_mixed ) : ?>
		<div class="pal-news-filter" data-pal-news-filter>
			<button class="pal-news-filter__btn is-active" data-filter="all"><?php esc_html_e( 'All', 'nashua-pal-core' ); ?></button>
			<?php if ( $query_posts ) : ?>
				<button class="pal-news-filter__btn" data-filter="news"><?php esc_html_e( 'News', 'nashua-pal-core' ); ?></button>
			<?php endif; ?>
			<?php if ( $query_newsletters ) : ?>
				<button class="pal-news-filter__btn" data-filter="newsletter"><?php esc_html_e( 'Newsletter', 'nashua-pal-core' ); ?></button>
			<?php endif; ?>
			<?php if ( $query_events ) : ?>
				<button class="pal-news-filter__btn" data-filter="event"><?php esc_html_e( 'Events', 'nashua-pal-core' ); ?></button>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if ( $is_slider ) : ?>
		<div class="pal-news-slider" data-pal-news-slider>
			<button class="pal-news-slider__arrow pal-news-slider__arrow--prev" data-pal-news-prev aria-label="<?php esc_attr_e( 'Previous', 'nashua-pal-core' ); ?>">&#10094;</button>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $wrap_class ); ?>" data-pal-news-grid>
		<?php foreach ( $items as $item ) :
			$badge_class = 'pal-news-card__badge pal-news-card__badge--' . esc_attr( $item['type'] );
			$rel_attr    = ( '_blank' === $item['target'] ) ? ' rel="noopener noreferrer"' : '';
			?>
			<a href="<?php echo esc_url( $item['url'] ); ?>" target="<?php echo esc_attr( $item['target'] ); ?>"<?php echo $rel_attr; ?> class="pal-news-card" data-news-type="<?php echo esc_attr( $item['type'] ); ?>">
				<div class="pal-news-card__media" <?php if ( $item['image'] ) echo 'style="background-image:url(' . esc_url( $item['image'] ) . ');"'; ?>>
					<?php if ( $has_mixed ) : ?>
						<span class="<?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $type_labels[ $item['type'] ] ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $item['reg_status'] ) && 'closed' !== $item['reg_status'] ) : ?>
						<span class="pal-news-card__reg pal-news-card__reg--<?php echo esc_attr( $item['reg_status'] ); ?>"><?php echo esc_html( $this->reg_status_label( $item['reg_status'] ) ); ?></span>
					<?php endif; ?>
				</div>
				<div class="pal-news-card__body">
					<h3 class="pal-news-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
					<div class="pal-news-card__date"><?php echo esc_html( $item['date_display'] ); ?></div>
				</div>
			</a>
		<?php endforeach; ?>
		</div>

		<?php if ( $is_slider ) : ?>
			<button class="pal-news-slider__arrow pal-news-slider__arrow--next" data-pal-news-next aria-label="<?php esc_attr_e( 'Next', 'nashua-pal-core' ); ?>">&#10095;</button>
			<div class="pal-news-slider__dots" data-pal-news-dots></div>
		</div><!-- .pal-news-slider -->
		<?php endif; ?>

		<?php
		$show_cta = ( 'true' === strtolower( (string) $a['show_cta'] ) );
		if ( $show_cta && $a['cta_url'] && $a['cta_label'] ) : ?>
		<div class="pal-news-cta">
			<?php if ( $a['cta_text'] ) : ?>
				<p><?php echo esc_html( $a['cta_text'] ); ?></p>
			<?php endif; ?>
			<a href="<?php echo esc_url( $a['cta_url'] ); ?>" class="pal-btn pal-btn--primary" target="_blank" rel="noopener">
				<?php echo esc_html( $a['cta_label'] ); ?>
			</a>
		</div>
		<?php endif; ?>

		<?php return ob_get_clean();
	}

	/* -------- news archive (all news, newsletters, events) -------- */
	public function news_archive( $atts ) {
		$a = shortcode_atts( array(
			'per_page' => 12,
			'columns'  => 3,
		), $atts, 'pal_news_archive' );

		$per_page = max( 1, (int) $a['per_page'] );
		$columns  = max( 1, min( 4, (int) $a['columns'] ) );
		$paged    = max( 1, (int) get_query_var( 'paged', 1 ) );

		$items = array();

		// --- Blog posts ---
		$q_posts = new WP_Query( array(
			'post_type'      => 'post',
			'posts_per_page' => 200,
			'post_status'    => 'publish',
			'no_found_rows'  => true,
		) );
		while ( $q_posts->have_posts() ) {
			$q_posts->the_post();
			$items[] = array(
				'type'         => 'news',
				'title'        => get_the_title(),
				'url'          => get_permalink(),
				'target'       => '_self',
				'image'        => $this->get_featured_image( get_the_ID(), 'medium_large' ),
				'date'         => (int) get_the_date( 'U' ),
				'date_display' => get_the_date(),
			);
		}
		wp_reset_postdata();

		// --- Newsletters ---
		if ( post_type_exists( 'pal_newsletter' ) ) {
			$q_nl = new WP_Query( array(
				'post_type'      => 'pal_newsletter',
				'posts_per_page' => 200,
				'post_status'    => 'publish',
				'no_found_rows'  => true,
			) );
			while ( $q_nl->have_posts() ) {
				$q_nl->the_post();
				$nl_id       = get_the_ID();
				$nl_url      = get_post_meta( $nl_id, '_pal_newsletter_url', true );
				$nl_date_raw = get_post_meta( $nl_id, '_pal_newsletter_date', true );
				$nl_ts       = $nl_date_raw ? strtotime( $nl_date_raw ) : (int) get_the_date( 'U' );
				$items[] = array(
					'type'         => 'newsletter',
					'title'        => get_the_title(),
					'url'          => $nl_url ? $nl_url : '#',
					'target'       => '_blank',
					'image'        => $this->get_featured_image( $nl_id, 'medium_large' ),
					'date'         => (int) $nl_ts,
					'date_display' => wp_date( get_option( 'date_format' ), $nl_ts ),
				);
			}
			wp_reset_postdata();
		}

		// --- Upcoming Events ---
		if ( post_type_exists( 'pal_event' ) ) {
			$now = current_time( 'mysql' );
			$q_ev = new WP_Query( array(
				'post_type'      => 'pal_event',
				'posts_per_page' => 200,
				'post_status'    => 'publish',
				'no_found_rows'  => true,
				'meta_key'       => '_pal_event_start',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				'meta_query'     => array(
					array(
						'key'     => '_pal_event_start',
						'value'   => $now,
						'compare' => '>=',
						'type'    => 'DATETIME',
					),
				),
			) );
			while ( $q_ev->have_posts() ) {
				$q_ev->the_post();
				$ev_id    = get_the_ID();
				$ev_start = get_post_meta( $ev_id, '_pal_event_start', true );
				$ev_ts    = $ev_start ? strtotime( $ev_start ) : (int) get_the_date( 'U' );
				$items[] = array(
					'type'         => 'event',
					'title'        => get_the_title(),
					'url'          => get_permalink(),
					'target'       => '_self',
					'image'        => $this->get_featured_image( $ev_id, 'medium_large' ),
					'date'         => (int) $ev_ts,
					'date_display' => wp_date( get_option( 'date_format' ), $ev_ts ),
					'reg_status'   => get_post_meta( $ev_id, '_pal_event_reg_status', true ),
				);
			}
			wp_reset_postdata();
		}

		if ( empty( $items ) ) {
			return '<p style="text-align:center;color:#646970;">' . esc_html__( 'No news to display.', 'nashua-pal-core' ) . '</p>';
		}

		// Sort newest first.
		usort( $items, function ( $a, $b ) { return $b['date'] <=> $a['date']; } );

		$total_items = count( $items );
		$total_pages = (int) ceil( $total_items / $per_page );
		$offset      = ( $paged - 1 ) * $per_page;
		$page_items  = array_slice( $items, $offset, $per_page );

		$type_labels = array(
			'news'       => __( 'News', 'nashua-pal-core' ),
			'newsletter' => __( 'Newsletter', 'nashua-pal-core' ),
			'event'      => __( 'Event', 'nashua-pal-core' ),
		);

		$has_types = array();
		foreach ( $items as $item ) { $has_types[ $item['type'] ] = true; }
		$has_mixed = count( $has_types ) > 1;

		ob_start();
		?>

		<?php if ( $has_mixed ) : ?>
		<div class="pal-news-filter" data-pal-news-filter>
			<button class="pal-news-filter__btn is-active" data-filter="all"><?php esc_html_e( 'All', 'nashua-pal-core' ); ?></button>
			<?php if ( ! empty( $has_types['news'] ) ) : ?>
				<button class="pal-news-filter__btn" data-filter="news"><?php esc_html_e( 'News', 'nashua-pal-core' ); ?></button>
			<?php endif; ?>
			<?php if ( ! empty( $has_types['newsletter'] ) ) : ?>
				<button class="pal-news-filter__btn" data-filter="newsletter"><?php esc_html_e( 'Newsletter', 'nashua-pal-core' ); ?></button>
			<?php endif; ?>
			<?php if ( ! empty( $has_types['event'] ) ) : ?>
				<button class="pal-news-filter__btn" data-filter="event"><?php esc_html_e( 'Events', 'nashua-pal-core' ); ?></button>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="pal-news pal-news--grid pal-news--cols-<?php echo esc_attr( $columns ); ?>" data-pal-news-grid>
		<?php foreach ( $page_items as $item ) :
			$badge_class = 'pal-news-card__badge pal-news-card__badge--' . esc_attr( $item['type'] );
			$rel_attr    = ( '_blank' === $item['target'] ) ? ' rel="noopener noreferrer"' : '';
			?>
			<a href="<?php echo esc_url( $item['url'] ); ?>" target="<?php echo esc_attr( $item['target'] ); ?>"<?php echo $rel_attr; ?> class="pal-news-card" data-news-type="<?php echo esc_attr( $item['type'] ); ?>">
				<div class="pal-news-card__media" <?php if ( $item['image'] ) echo 'style="background-image:url(' . esc_url( $item['image'] ) . ');"'; ?>>
					<?php if ( $has_mixed ) : ?>
						<span class="<?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( $type_labels[ $item['type'] ] ); ?></span>
					<?php endif; ?>
					<?php if ( ! empty( $item['reg_status'] ) && 'closed' !== $item['reg_status'] ) : ?>
						<span class="pal-news-card__reg pal-news-card__reg--<?php echo esc_attr( $item['reg_status'] ); ?>"><?php echo esc_html( $this->reg_status_label( $item['reg_status'] ) ); ?></span>
					<?php endif; ?>
				</div>
				<div class="pal-news-card__body">
					<h3 class="pal-news-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
					<div class="pal-news-card__date"><?php echo esc_html( $item['date_display'] ); ?></div>
				</div>
			</a>
		<?php endforeach; ?>
		</div>

		<?php if ( $total_pages > 1 ) : ?>
		<nav class="pal-news-archive-pagination">
			<?php
			echo paginate_links( array(
				'total'     => $total_pages,
				'current'   => $paged,
				'prev_text' => '&laquo;',
				'next_text' => '&raquo;',
			) );
			?>
		</nav>
		<?php endif; ?>

		<?php return ob_get_clean();
	}

	/* -------- partner slider (auto-scrolling, 6 logos at a time) -------- */
	public function partner_carousel( $atts ) {
		$a = shortcode_atts( array(
			'title'   => __( 'Proudly Partnered With', 'nashua-pal-core' ),
			'visible' => 6,
			'speed'   => 30, // seconds for one full loop
		), $atts, 'pal_partner_carousel' );

		$q = new WP_Query( array(
			'post_type'      => NPAL_CPT_Partner::POST_TYPE,
			'posts_per_page' => -1,
			'meta_query'     => array(
				'relation' => 'OR',
				'order_clause' => array(
					'key'     => '_pal_partner_order',
					'compare' => 'EXISTS',
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => '_pal_partner_order',
					'compare' => 'NOT EXISTS',
				),
			),
			'orderby'        => array( 'order_clause' => 'ASC', 'title' => 'ASC' ),
			'order'          => 'ASC',
		) );
		if ( ! $q->have_posts() ) { return ''; }

		// Collect partners into an array so we can render them twice for a seamless loop.
		$partners = array();
		while ( $q->have_posts() ) {
			$q->the_post();
			$partners[] = array(
				'name' => get_the_title(),
				'logo' => $this->get_featured_image( get_the_ID(), 'medium' ),
				'url'  => get_post_meta( get_the_ID(), '_pal_partner_url', true ),
			);
		}
		wp_reset_postdata();

		$visible = max( 2, min( 8, (int) $a['visible'] ) );
		$speed   = max( 10, min( 90, (int) $a['speed'] ) );

		// Duplicate the set for seamless infinite scrolling when we have 2+ partners.
		$loop_count = count( $partners ) > 1 ? 2 : 1;

		ob_start(); ?>
		<section class="pal-partners" aria-label="<?php echo esc_attr( $a['title'] ); ?>">
			<?php if ( $a['title'] ) : ?><h3 class="pal-partners__title"><?php echo esc_html( $a['title'] ); ?></h3><?php endif; ?>
			<div class="pal-partners__viewport"
				style="--pal-visible:<?php echo (int) $visible; ?>;--pal-speed:<?php echo (int) $speed; ?>s;">
				<div class="pal-partners__track<?php echo 2 === $loop_count ? ' is-scrolling' : ''; ?>">
					<?php for ( $i = 0; $i < $loop_count; $i++ ) : ?>
						<?php foreach ( $partners as $p ) : ?>
							<div class="pal-partners__item" title="<?php echo esc_attr( $p['name'] ); ?>" <?php echo $i > 0 ? 'aria-hidden="true"' : ''; ?>>
								<?php if ( $p['logo'] && ! empty( $p['url'] ) ) : ?>
									<a href="<?php echo esc_url( $p['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( $p['name'] ); ?>">
										<img src="<?php echo esc_url( $p['logo'] ); ?>" alt="<?php echo esc_attr( $p['name'] ); ?>" loading="lazy" />
									</a>
								<?php elseif ( $p['logo'] ) : ?>
									<img src="<?php echo esc_url( $p['logo'] ); ?>" alt="<?php echo esc_attr( $p['name'] ); ?>" loading="lazy" />
								<?php else : ?>
									<span class="pal-partners__placeholder"><?php echo esc_html( $p['name'] ); ?></span>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					<?php endfor; ?>
				</div>
			</div>
		</section>
		<?php return ob_get_clean();
	}

	/* -------- Community Partners page (grouped by tier) -------- */
	public function community_partners( $atts ) {
		$a = shortcode_atts( array(
			'title'     => __( 'Community Partners', 'nashua-pal-core' ),
			'cta_title' => __( 'Ready to Partner?', 'nashua-pal-core' ),
			'cta_text'  => __( 'Join the growing list of businesses and organizations making a difference in Nashua through PAL.', 'nashua-pal-core' ),
			'cta_url'   => '/sponsor',
			'cta_label' => __( 'Become a Partner', 'nashua-pal-core' ),
		), $atts, 'pal_community_partners' );

		$tiers = NPAL_Partner_Meta::tier_options();
		unset( $tiers[''] ); // remove empty placeholder

		// Tier display config: label, CSS grid modifier, logo sizes
		$tier_config = array(
			'champion' => array( 'grid' => 'diamond',  'cols' => 3 ),
			'all_star' => array( 'grid' => 'platinum', 'cols' => 4 ),
			'friend'   => array( 'grid' => 'gold',     'cols' => 5 ),
			'in_kind'  => array( 'grid' => 'silver',   'cols' => 6 ),
		);

		ob_start(); ?>
		<div class="pal-community-partners">
			<?php foreach ( $tiers as $slug => $label ) :
				$partners = get_posts( array(
					'post_type'      => NPAL_CPT_Partner::POST_TYPE,
					'posts_per_page' => -1,
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'key'   => '_pal_partner_tier',
							'value' => $slug,
						),
						array(
							'relation' => 'OR',
							'order_clause' => array(
								'key'     => '_pal_partner_order',
								'compare' => 'EXISTS',
								'type'    => 'NUMERIC',
							),
							array(
								'key'     => '_pal_partner_order',
								'compare' => 'NOT EXISTS',
							),
						),
					),
					'orderby'        => array( 'order_clause' => 'ASC', 'title' => 'ASC' ),
				) );

				if ( empty( $partners ) ) { continue; }
				$cfg = isset( $tier_config[ $slug ] ) ? $tier_config[ $slug ] : array( 'grid' => 'gold', 'cols' => 4 );
			?>
				<div class="pal-partners-section">
					<div class="pal-partners-tier">
						<span class="pal-partners-tier__label"><?php echo esc_html( $label ); ?></span>
					</div>
					<div class="pal-partners-grid pal-partners-grid--<?php echo esc_attr( $cfg['grid'] ); ?>">
						<?php foreach ( $partners as $p ) :
							$logo    = get_the_post_thumbnail_url( $p->ID, 'medium' );
							$link    = get_permalink( $p->ID );
							$tagline = get_post_meta( $p->ID, '_pal_partner_tagline', true );
							$feat    = get_post_meta( $p->ID, '_pal_partner_featured', true );
						?>
							<a href="<?php echo esc_url( $link ); ?>" class="pal-partner-logo<?php echo $feat ? ' is-featured' : ''; ?>" title="<?php echo esc_attr( $p->post_title ); ?>">
								<?php if ( $logo ) : ?>
									<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $p->post_title ); ?>" loading="lazy" />
								<?php else : ?>
									<span style="font-weight:700;color:var(--pal-navy,#071F3F);font-size:14px;text-align:center;"><?php echo esc_html( $p->post_title ); ?></span>
								<?php endif; ?>
							</a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<?php if ( $a['cta_url'] ) : ?>
			<div class="pal-partners-cta">
				<h2><?php echo esc_html( $a['cta_title'] ); ?></h2>
				<p><?php echo esc_html( $a['cta_text'] ); ?></p>
				<a href="<?php echo esc_url( $a['cta_url'] ); ?>" class="pal-btn pal-btn--primary">
					<?php echo esc_html( $a['cta_label'] ); ?>
				</a>
			</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/* -------- Single Partner profile page -------- */
	public function partner_single( $atts ) {
		$a = shortcode_atts( array(
			'id' => 0,
		), $atts, 'pal_partner_single' );

		$post_id = (int) $a['id'];
		if ( ! $post_id && is_singular( NPAL_CPT_Partner::POST_TYPE ) ) {
			$post_id = get_the_ID();
		}
		if ( ! $post_id ) { return ''; }

		$name     = get_the_title( $post_id );
		$logo     = get_the_post_thumbnail_url( $post_id, 'medium_large' );
		$content  = apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
		$tier     = get_post_meta( $post_id, '_pal_partner_tier', true );
		$tagline  = get_post_meta( $post_id, '_pal_partner_tagline', true );
		$url      = get_post_meta( $post_id, '_pal_partner_url', true );
		$email    = get_post_meta( $post_id, '_pal_partner_email', true );
		$phone    = get_post_meta( $post_id, '_pal_partner_phone', true );
		$contact  = get_post_meta( $post_id, '_pal_partner_contact_name', true );

		$socials = array();
		foreach ( array( 'facebook', 'linkedin', 'twitter', 'instagram' ) as $key ) {
			$v = get_post_meta( $post_id, '_pal_partner_' . $key, true );
			if ( $v ) { $socials[ $key ] = $v; }
		}

		$tiers = NPAL_Partner_Meta::tier_options();
		$tier_label = isset( $tiers[ $tier ] ) ? $tiers[ $tier ] : '';

		$social_icons = array(
			'facebook'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
			'linkedin'  => '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
			'twitter'   => '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
			'instagram' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>',
		);

		$has_sidebar = $url || $contact || $email || $phone || ! empty( $socials );
		$has_content = $content && trim( strip_tags( $content ) );

		ob_start(); ?>
		<div class="pal-pp">
			<!-- Header: logo + meta -->
			<div class="pal-pp__header">
				<?php if ( $logo ) : ?>
					<div class="pal-pp__logo">
						<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
					</div>
				<?php endif; ?>
				<div class="pal-pp__meta">
					<?php if ( $tier_label ) : ?>
						<span class="pal-pp__tier"><?php echo esc_html( $tier_label ); ?></span>
					<?php endif; ?>
					<?php if ( $tagline ) : ?>
						<p class="pal-pp__tagline"><?php echo esc_html( $tagline ); ?></p>
					<?php endif; ?>
					<?php if ( $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>" class="pal-pp__website" target="_blank" rel="noopener">
							<?php echo esc_html( preg_replace( '#^https?://(www\.)?#', '', rtrim( $url, '/' ) ) ); ?>
							<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $socials ) ) : ?>
						<div class="pal-pp__socials">
							<?php foreach ( $socials as $key => $surl ) : ?>
								<a href="<?php echo esc_url( $surl ); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr( ucfirst( $key ) ); ?>">
									<?php echo $social_icons[ $key ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<!-- Two-column body -->
			<div class="pal-pp__body<?php echo $has_sidebar ? '' : ' pal-pp__body--full'; ?>">
				<div class="pal-pp__main">
					<?php if ( $has_content ) : ?>
						<h2 class="pal-pp__heading"><?php esc_html_e( 'About This Partner', 'nashua-pal-core' ); ?></h2>
						<div class="pal-pp__content"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
					<?php else : ?>
						<p class="pal-pp__empty"><?php esc_html_e( 'Partner description coming soon.', 'nashua-pal-core' ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( $has_sidebar ) : ?>
				<aside class="pal-pp__sidebar">
					<?php if ( $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>" class="pal-pp__btn" target="_blank" rel="noopener">
							<?php esc_html_e( 'Visit Website', 'nashua-pal-core' ); ?>
							<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
						</a>
					<?php endif; ?>

					<?php if ( $contact || $email || $phone ) : ?>
						<div class="pal-pp__card">
							<h3 class="pal-pp__card-title"><?php esc_html_e( 'Contact', 'nashua-pal-core' ); ?></h3>
							<?php if ( $contact ) : ?>
								<p class="pal-pp__row"><strong><?php echo esc_html( $contact ); ?></strong></p>
							<?php endif; ?>
							<?php if ( $email ) : ?>
								<p class="pal-pp__row"><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
							<?php endif; ?>
							<?php if ( $phone ) : ?>
								<p class="pal-pp__row"><a href="tel:<?php echo esc_attr( preg_replace( '/[^+\d]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></p>
							<?php endif; ?>
						</div>
					<?php endif; ?>

					<?php if ( ! empty( $socials ) ) : ?>
						<div class="pal-pp__card">
							<h3 class="pal-pp__card-title"><?php esc_html_e( 'Connect', 'nashua-pal-core' ); ?></h3>
							<div class="pal-pp__social-list">
								<?php foreach ( $socials as $key => $surl ) : ?>
									<a href="<?php echo esc_url( $surl ); ?>" target="_blank" rel="noopener">
										<?php echo $social_icons[ $key ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										<?php echo esc_html( ucfirst( $key ) ); ?>
									</a>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endif; ?>
				</aside>
				<?php endif; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/* -------- header tools -------- */
	public function search() {
		if ( '1' !== $this->opt( 'search_enabled', '1' ) ) { return ''; }
		ob_start(); ?>
		<form class="pal-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<svg class="pal-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
			<input type="search" name="s" placeholder="<?php esc_attr_e( 'Search', 'nashua-pal-core' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'nashua-pal-core' ); ?>" />
		</form>
		<?php return ob_get_clean();
	}

	public function language() {
		if ( '1' !== $this->opt( 'gtranslate_enabled', '1' ) ) { return ''; }

		// Skip entirely inside Elementor editor / preview iframe. The Google
		// Translate widget mutates the DOM (wraps text nodes in <font> tags,
		// rewrites attributes, sets cookies) which races Elementor's preview
		// handshake and triggers the "Editor successfully loaded? / Enable
		// Safe Mode" hang. Front-end output is unaffected.
		if (
			( isset( $_GET['elementor-preview'] ) ) ||
			( isset( $_GET['action'] ) && 'elementor' === $_GET['action'] ) ||
			( class_exists( '\\Elementor\\Plugin' ) && isset( \Elementor\Plugin::$instance ) && (
				( isset( \Elementor\Plugin::$instance->editor )  && \Elementor\Plugin::$instance->editor->is_edit_mode() ) ||
				( isset( \Elementor\Plugin::$instance->preview ) && \Elementor\Plugin::$instance->preview->is_preview_mode() )
			) )
		) {
			return '';
		}

		// Custom dropdown UI that drives Google Translate's hidden widget
		// behind the scenes. Reasons we moved off the "invisible <select>
		// overlay" trick (1.8.9):
		//   - .goog-te-gadget is a <div> Google sizes to its own content,
		//     so the underlying <select> rarely covered the full visible
		//     label area; users clicked the icon or label and nothing
		//     happened because the click hit empty parent.
		//   - Programmatically opening a native <select> from a sibling
		//     click is not reliable cross-browser.
		// Now: visible custom <ul> menu we fully control, and on selection
		// we set select.goog-te-combo.value + dispatch 'change' so GT
		// translates the page. The Google widget is still loaded (hidden
		// off-screen) — that script provides the translation engine.
		$instance_id = 'pal-lang-' . wp_unique_id();
		$languages   = array(
			'en'    => __( 'English',    'nashua-pal-core' ),
			'es'    => __( 'Español',    'nashua-pal-core' ),
			'pt'    => __( 'Português',  'nashua-pal-core' ),
			'fr'    => __( 'Français',   'nashua-pal-core' ),
			'vi'    => __( 'Tiếng Việt', 'nashua-pal-core' ),
			'zh-CN' => __( '中文',         'nashua-pal-core' ),
		);
		ob_start(); ?>
		<div class="pal-language notranslate" id="<?php echo esc_attr( $instance_id ); ?>" data-pal-language translate="no">
			<button type="button" class="pal-language__btn notranslate" translate="no" aria-haspopup="listbox" aria-expanded="false" aria-label="<?php esc_attr_e( 'Select Language', 'nashua-pal-core' ); ?>">
				<span class="pal-language__icon" aria-hidden="true"><?php echo NPAL_Icons::render( 'language' ); ?></span>
				<span class="pal-language__label notranslate" translate="no"><?php esc_html_e( 'Select Language', 'nashua-pal-core' ); ?></span>
				<svg class="pal-language__chevron" width="10" height="10" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 4 3 3 3-3"/></svg>
			</button>
			<!-- notranslate + translate="no" so language names always render in their own native form
			     (English, Español, Português, …) regardless of which language GT is currently showing.
			     Standard language-switcher UX — a Vietnamese speaker should always be able to spot
			     "Tiếng Việt" even if the page is currently translated to Spanish. -->
			<ul class="pal-language__menu notranslate" role="listbox" aria-hidden="true" translate="no">
				<?php foreach ( $languages as $code => $label ) : ?>
					<li class="notranslate" role="option" data-pal-lang="<?php echo esc_attr( $code ); ?>" tabindex="-1" translate="no"><?php echo esc_html( $label ); ?></li>
				<?php endforeach; ?>
			</ul>
			<!-- Google Translate's mounting point — visually hidden, only the API needs it -->
			<div class="pal-language__gt-mount" id="<?php echo esc_attr( $instance_id ); ?>-gt" aria-hidden="true"></div>
		</div>
		<script>
		(function(){
			var root = document.getElementById('<?php echo esc_js( $instance_id ); ?>');
			if (!root || root.dataset.palLanguageBound) return;
			root.dataset.palLanguageBound = '1';

			var btn   = root.querySelector('.pal-language__btn');
			var menu  = root.querySelector('.pal-language__menu');
			var label = root.querySelector('.pal-language__label');

			// Display labels for each lang code so we can sync the visible
			// label to whatever cookie was already set when the page loaded.
			var displayLabels = {};
			menu.querySelectorAll('li[data-pal-lang]').forEach(function(li){
				displayLabels[li.getAttribute('data-pal-lang')] = li.textContent;
			});

			// On page load, read googtrans cookie so the visible label
			// reflects the active language (e.g. user picked Spanish on
			// previous page, this page renders translated; show "Español").
			var existing = document.cookie.match(/googtrans=\/[a-zA-Z\-]+\/([a-zA-Z\-]+)/);
			if (existing && displayLabels[existing[1]]) {
				label.textContent = displayLabels[existing[1]];
			}

			function open()  { root.classList.add('pal-language--open');    btn.setAttribute('aria-expanded','true');  menu.setAttribute('aria-hidden','false'); }
			function close() { root.classList.remove('pal-language--open'); btn.setAttribute('aria-expanded','false'); menu.setAttribute('aria-hidden','true');  }
			function toggle(){ root.classList.contains('pal-language--open') ? close() : open(); }

			btn.addEventListener('click', function(e){ e.stopPropagation(); toggle(); });
			document.addEventListener('click', function(e){ if (!root.contains(e.target)) close(); });
			document.addEventListener('keydown', function(e){ if (e.key === 'Escape') close(); });

			menu.addEventListener('click', function(e){
				var li = e.target.closest('li[data-pal-lang]');
				if (!li) return;
				setLanguage(li.getAttribute('data-pal-lang'));
			});

			/**
			 * Cookie + reload approach. Far more reliable than dispatching
			 * synthetic events on the hidden goog-te-combo select — that
			 * approach is fragile across Google Translate versions and
			 * silently fails when the GT widget did not finish mounting.
			 *
			 * Setting the googtrans cookie tells GT which language to render
			 * the page in on next load; reload triggers GT to read the
			 * cookie during its own initialization and translate the DOM.
			 *
			 * We set the cookie on three scopes (path-only, host, dot-host)
			 * because the GT widget cookie-write is inconsistent across CDNs
			 * and reverse proxies — covering all three guarantees it sticks.
			 */
			function setLanguage(code){
				var host = window.location.hostname.replace(/^www\./, '');
				var pairs = [
					'googtrans=/en/' + code + '; path=/',
					'googtrans=/en/' + code + '; path=/; domain=' + host,
					'googtrans=/en/' + code + '; path=/; domain=.' + host
				];
				if (code === 'en') {
					// Reset path: clear all three cookie scopes so GT falls
					// back to original page language.
					var clear = 'expires=Thu, 01 Jan 1970 00:00:00 UTC';
					pairs = [
						'googtrans=; path=/; ' + clear,
						'googtrans=; path=/; domain=' + host + '; ' + clear,
						'googtrans=; path=/; domain=.' + host + '; ' + clear
					];
				}
				pairs.forEach(function(c){ document.cookie = c; });
				window.location.reload();
			}
		})();
		</script>
		<?php
		// One-time loader for the Google Translate engine. We mount it into
		// the FIRST instance's hidden div so the <select.goog-te-combo>
		// exists in the DOM exactly once, even if multiple [pal_language]
		// shortcodes are on the page (header + mobile menu).
		if ( ! self::$gt_script_emitted ) {
			self::$gt_script_emitted = true;
			?>
			<script>
			window.pal_googleTranslateInit = function(){
				if (!window.google || !window.google.translate || !window.google.translate.TranslateElement) return;
				var mount = document.querySelector('.pal-language__gt-mount');
				if (!mount) return;
				new google.translate.TranslateElement({
					pageLanguage: 'en',
					includedLanguages: 'en,es,pt,fr,vi,zh-CN',
					layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
					autoDisplay: false
				}, mount.id);
			};
			</script>
			<script src="https://translate.google.com/translate_a/element.js?cb=pal_googleTranslateInit" async></script>
			<?php
		}
		return ob_get_clean();
	}

	/** Tracks whether the one-time Google Translate loader script has been
	 *  emitted on the current page render — multiple [pal_language]
	 *  shortcodes share a single GT widget instance to avoid duplicate-ID
	 *  collisions and double-loaded scripts. */
	private static $gt_script_emitted = false;

	public function socials() {
		$links = array(
			'facebook'  => array( 'url' => $this->opt( 'social_facebook' ),  'icon' => 'fab fa-facebook-f' ),
			'instagram' => array( 'url' => $this->opt( 'social_instagram' ), 'icon' => 'fab fa-instagram' ),
			'youtube'   => array( 'url' => $this->opt( 'social_youtube' ),   'icon' => 'fab fa-youtube' ),
			'x'         => array( 'url' => $this->opt( 'social_x' ),         'icon' => 'fab fa-x-twitter' ),
		);
		$has_any = false;
		foreach ( $links as $l ) { if ( $l['url'] ) { $has_any = true; break; } }
		if ( ! $has_any ) { return ''; }
		ob_start(); ?>
		<div class="pal-socials">
			<?php foreach ( $links as $name => $l ) : if ( ! $l['url'] ) continue; ?>
				<a href="<?php echo esc_url( $l['url'] ); ?>" target="_blank" rel="noopener" aria-label="<?php echo esc_attr( ucfirst( $name ) ); ?>">
					<i class="<?php echo esc_attr( $l['icon'] ); ?>" aria-hidden="true"></i>
				</a>
			<?php endforeach; ?>
		</div>
		<?php return ob_get_clean();
	}

	/* -------- contact bits -------- */
	public function donate_button( $atts ) {
		$a = shortcode_atts( array( 'label' => __( 'Donate Today', 'nashua-pal-core' ), 'class' => 'pal-btn pal-btn-yellow' ), $atts, 'pal_donate_button' );
		$url = $this->opt( 'donate_url', '/donate' );
		return sprintf(
			'<a class="%1$s" href="%2$s"><i class="fas fa-heart" aria-hidden="true"></i> %3$s</a>',
			esc_attr( $a['class'] ),
			esc_url( $url ),
			esc_html( $a['label'] )
		);
	}

	public function phone() {
		$p = $this->opt( 'phone' );
		if ( ! $p ) { return ''; }
		$tel = preg_replace( '/[^\d+]/', '', $p );
		return sprintf( '<a class="pal-phone" href="tel:%1$s"><i class="fas fa-phone" aria-hidden="true"></i> %2$s</a>', esc_attr( $tel ), esc_html( $p ) );
	}

	public function email() {
		$e = $this->opt( 'email' );
		if ( ! $e ) { return ''; }
		return sprintf( '<a class="pal-email" href="mailto:%1$s"><i class="fas fa-envelope" aria-hidden="true"></i> %2$s</a>', esc_attr( $e ), esc_html( $e ) );
	}

	public function address() {
		$a = $this->opt( 'address' );
		if ( ! $a ) { return ''; }
		return sprintf( '<span class="pal-address"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> %s</span>', esc_html( $a ) );
	}

	/* -------- newsletter signup form -------- */
	public function newsletter() {
		$action = $this->opt( 'newsletter_action' );
		$label  = $this->opt( 'newsletter_label' );
		$button = $this->opt( 'newsletter_button' );
		ob_start(); ?>
		<div class="pal-newsletter">
			<?php if ( $label ) : ?><p style="color:#fff;font-weight:600;margin:0 0 10px;font-size:14px;"><?php echo esc_html( $label ); ?></p><?php endif; ?>
			<form action="<?php echo esc_url( $action ?: '#' ); ?>" method="post" target="_blank">
				<input type="email" name="EMAIL" placeholder="<?php esc_attr_e( 'Email address', 'nashua-pal-core' ); ?>" required aria-label="<?php esc_attr_e( 'Email address', 'nashua-pal-core' ); ?>" />
				<button type="submit"><?php echo esc_html( $button ?: __( 'Subscribe', 'nashua-pal-core' ) ); ?></button>
			</form>
		</div>
		<?php return ob_get_clean();
	}

	/**
	 * [pal_underline] — wraps a word or phrase in the same animated yellow
	 * underline used on the homepage hero ("SAFER COMMUNITY"). Available
	 * everywhere the editor accepts shortcodes (Elementor headings/Text
	 * Editor widget, classic editor, the block editor's shortcode block,
	 * widgets, page titles via `do_shortcode()` filters).
	 *
	 * Both forms work:
	 *   [pal_underline]COMMUNITY[/pal_underline]    ← enclosing
	 *   [pal_underline text="COMMUNITY"]            ← self-closing
	 *
	 * Inside Elementor headings the inline span class is what carries the
	 * effect — an optional `class` attribute lets you stack extra utility
	 * classes if you ever need to scope a one-off style.
	 */
	public function underline( $atts = array(), $content = null ) {
		$atts = shortcode_atts( array(
			'text'  => '',
			'class' => '',
		), $atts, 'pal_underline' );

		// Prefer enclosed content; fall back to the `text` attribute.
		// wpautop can wrap inner content in <p>…</p> when the shortcode
		// sits on its own line in the editor — strip that outer wrap so
		// the underline span stays inline inside a heading or sentence.
		if ( null !== $content && '' !== trim( (string) $content ) ) {
			$inner = do_shortcode( shortcode_unautop( (string) $content ) );
			$inner = trim( preg_replace( '#^<p>(.*?)</p>$#s', '$1', $inner ) );
		} elseif ( '' !== $atts['text'] ) {
			$inner = esc_html( $atts['text'] );
		} else {
			return '';
		}

		$classes = trim( 'pal-hero-underline ' . sanitize_html_class( $atts['class'] ) );
		return '<span class="' . esc_attr( $classes ) . '">' . $inner . '</span>';
	}

	/**
	 * Footer legal bar — replaces the raw HTML widget that used to live
	 * inside the footer template. Pulls copyright + sub-line from Nashua
	 * PAL → Settings → General so a non-developer can edit both lines
	 * without touching Elementor. Bottom utility-link list (Privacy /
	 * Terms / Policies / Contact) stays hardcoded — those URLs don't
	 * change and shipping them as a fifth settings field would be more
	 * complexity than it's worth.
	 *
	 * Tokens:
	 *   {year} → current year (4-digit, server time). Server-side render
	 *            avoids the embedded `<script>` the old HTML widget used.
	 *
	 * The sub-line accepts inline HTML (wp_kses_post on save), so the
	 * "Powered by Cao-Tech LLC" wording can be wrapped in `<a>` directly
	 * from the settings field.
	 *
	 * @since 1.8.83
	 */
	public function footer_legal( $atts = array() ) {
		$copy = (string) $this->opt( 'footer_copyright' );
		$sub  = (string) $this->opt( 'footer_legal_sub' );
		$copy = str_replace( '{year}', (string) gmdate( 'Y' ), $copy );

		// Collect up to 4 bottom-row links from settings. Slot with empty
		// label OR empty URL is skipped, so admins can hide a column by
		// blanking out either side instead of needing a separate toggle.
		$links = array();
		for ( $i = 1; $i <= 4; $i++ ) {
			$label = trim( (string) $this->opt( "footer_link_{$i}_label" ) );
			$url   = trim( (string) $this->opt( "footer_link_{$i}_url" ) );
			if ( $label !== '' && $url !== '' ) {
				$links[] = array( 'label' => $label, 'url' => $url );
			}
		}

		ob_start(); ?>
		<div class="pal-footer-legal">
			<div class="pal-footer-legal__text">
				<?php echo wp_kses_post( $copy ); ?>
				<?php if ( $sub ) : ?>
					<span class="pal-footer-legal__sub"> · <?php echo wp_kses_post( $sub ); ?></span>
				<?php endif; ?>
			</div>
			<?php if ( $links ) : ?>
				<ul class="pal-footer-legal__links">
					<?php foreach ( $links as $link ) : ?>
						<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/* -------- mobile menu button (hamburger) -------- */
	public function mobile_menu_button( $atts ) {
		$a = shortcode_atts( array(
			'label' => __( 'Open menu', 'nashua-pal-core' ),
			'class' => '',
		), $atts, 'pal_mobile_menu_button' );

		$class = trim( 'pal-mobile-toggle ' . sanitize_html_class( $a['class'] ) );
		ob_start(); ?>
		<button type="button" class="<?php echo esc_attr( $class ); ?>" data-pal-mobile-menu-open aria-label="<?php echo esc_attr( $a['label'] ); ?>" aria-controls="pal-mobile-menu" aria-expanded="false">
			<span class="pal-mobile-toggle__bar"></span>
			<span class="pal-mobile-toggle__bar"></span>
			<span class="pal-mobile-toggle__bar"></span>
		</button>
		<?php return ob_get_clean();
	}

	/* -------- mobile menu drawer (full-screen navy panel) ----------
	 * Renders the drawer markup itself; the hamburger toggle is rendered
	 * separately by [pal_mobile_menu_button]. Drop both into the header
	 * Elementor template (button visible only on mobile via CSS, drawer
	 * always present in DOM and shown via .is-open class on click).
	 *
	 * Menu items come from Appearance → Menus → location "Nashua PAL Mobile
	 * Menu". Falls back to a hardcoded list (Our Work / Programs / Get
	 * Involved / Events / Donate) when no menu is assigned, so first-time
	 * setups aren't empty.
	 */
	public function mobile_menu( $atts ) {
		$a = shortcode_atts( array(
			'show_donate'         => 'yes',
			'show_register'       => 'yes',
			'show_search'         => 'yes',
			'show_language'       => 'yes',
			'show_socials'        => 'yes',
			'btn_primary_label'   => '',
			'btn_primary_url'     => '',
			'btn_secondary_label' => '',
			'btn_secondary_url'   => '',
			'menu_source'         => 'wp_menu',
			'custom_menu_items'   => '',
		), $atts, 'pal_mobile_menu' );

		$show = function( $key ) use ( $a ) {
			return in_array( strtolower( (string) $a[ $key ] ), array( 'yes', '1', 'true' ), true );
		};

		$donate_url     = ! empty( $a['btn_primary_url'] )   ? $a['btn_primary_url']   : $this->opt( 'donate_url',   '/donate' );
		$register_url   = ! empty( $a['btn_secondary_url'] ) ? $a['btn_secondary_url'] : $this->opt( 'register_url', '/programs' );
		$donate_label   = ! empty( $a['btn_primary_label'] )   ? $a['btn_primary_label']   : __( 'Donate Today', 'nashua-pal-core' );
		$register_label = ! empty( $a['btn_secondary_label'] ) ? $a['btn_secondary_label'] : __( 'Register Now', 'nashua-pal-core' );

		ob_start(); ?>
		<aside class="pal-mobile-menu" id="pal-mobile-menu" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Mobile navigation', 'nashua-pal-core' ); ?>" data-pal-mobile-menu hidden>
			<div class="pal-mobile-menu__overlay" data-pal-mobile-menu-close></div>
			<div class="pal-mobile-menu__panel" role="document">
				<?php
				$custom_logo  = $this->opt( 'site_logo_url' );
				$drawer_logo  = $custom_logo ? $custom_logo : NPAL_PLUGIN_URL . 'assets/img/Pal-Logo.png';
				?>
				<div class="pal-mobile-menu__head">
					<a class="pal-mobile-menu__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Nashua PAL — Home', 'nashua-pal-core' ); ?>">
						<img src="<?php echo esc_url( $drawer_logo ); ?>" alt="" width="84" />
					</a>
					<button type="button" class="pal-mobile-menu__close" data-pal-mobile-menu-close aria-label="<?php esc_attr_e( 'Close menu', 'nashua-pal-core' ); ?>">
						<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
					</button>
				</div>

				<nav class="pal-mobile-menu__nav" aria-label="<?php esc_attr_e( 'Primary mobile menu', 'nashua-pal-core' ); ?>">
					<?php
					$custom_items = array();
					if ( 'custom' === $a['menu_source'] && ! empty( $a['custom_menu_items'] ) ) {
						$decoded = json_decode( wp_unslash( $a['custom_menu_items'] ), true );
						if ( is_array( $decoded ) ) {
							$custom_items = $decoded;
						}
					}

					if ( ! empty( $custom_items ) ) {
						// Render custom menu items from the Elementor repeater.
						?>
						<ul class="pal-mobile-menu__list">
							<?php foreach ( $custom_items as $ci ) :
								$ci_label = isset( $ci['label'] ) ? $ci['label'] : '';
								$ci_url   = isset( $ci['url'] )   ? $ci['url']   : '#';
								$ci_icon  = isset( $ci['icon'] )  ? $ci['icon']  : '';
							?>
								<li class="menu-item">
									<a href="<?php echo esc_url( $ci_url ); ?>">
										<?php if ( $ci_icon ) : ?>
											<span class="pal-mobile-menu__item-icon" aria-hidden="true"><?php echo NPAL_Icons::render_or_fa( $ci_icon ); ?></span>
										<?php endif; ?>
										<?php echo esc_html( $ci_label ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php } elseif ( has_nav_menu( 'pal-mobile' ) ) {
						wp_nav_menu( array(
							'theme_location' => 'pal-mobile',
							'menu_class'     => 'pal-mobile-menu__list',
							'container'      => false,
							'fallback_cb'    => false,
							'depth'          => 2,
							'walker'         => new NPAL_Mobile_Menu_Walker(),
						) );
					} else {
						$fallback_items = array(
							array( 'icon' => 'shield-check', 'label' => __( 'Our Work',     'nashua-pal-core' ), 'href' => '#' ),
							array( 'icon' => 'basketball',   'label' => __( 'Programs',     'nashua-pal-core' ), 'href' => '#' ),
							array( 'icon' => 'users',        'label' => __( 'Get Involved', 'nashua-pal-core' ), 'href' => '#' ),
							array( 'icon' => 'calendar',     'label' => __( 'Events',       'nashua-pal-core' ), 'href' => esc_url( get_post_type_archive_link( NPAL_CPT_Event::POST_TYPE ) ?: '/events/' ) ),
							array( 'icon' => 'heart',        'label' => __( 'Donate',       'nashua-pal-core' ), 'href' => esc_url( $donate_url ) ),
						);
						?>
						<ul class="pal-mobile-menu__list pal-mobile-menu__list--fallback">
							<?php foreach ( $fallback_items as $f ) : ?>
								<li class="menu-item">
									<a href="<?php echo esc_url( $f['href'] ); ?>">
										<span class="pal-mobile-menu__item-icon" aria-hidden="true"><?php echo NPAL_Icons::render_or_fa( $f['icon'] ); ?></span>
										<?php echo esc_html( $f['label'] ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
						<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
							<p class="pal-mobile-menu__hint"><?php
								echo wp_kses(
									sprintf(
										/* translators: %s: link to Appearance → Menus */
										__( 'Showing fallback items. Assign a menu to "Nashua PAL Mobile Menu" under <a href="%s">Appearance → Menus</a> to customize.', 'nashua-pal-core' ),
										esc_url( admin_url( 'nav-menus.php' ) )
									),
									array( 'a' => array( 'href' => array() ) )
								);
							?></p>
						<?php endif; ?>
					<?php } ?>
				</nav>

				<div class="pal-mobile-menu__actions">
					<?php if ( $show( 'show_donate' ) ) : ?>
						<a class="pal-mobile-menu__btn pal-mobile-menu__btn--primary" href="<?php echo esc_url( $donate_url ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
							<?php echo esc_html( $donate_label ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $show( 'show_register' ) ) : ?>
						<a class="pal-mobile-menu__btn pal-mobile-menu__btn--secondary" href="<?php echo esc_url( $register_url ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 113 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
							<?php echo esc_html( $register_label ); ?>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( $show( 'show_search' ) || $show( 'show_language' ) ) : ?>
					<div class="pal-mobile-menu__tools">
						<?php if ( $show( 'show_search' ) ) : ?>
							<div class="pal-mobile-menu__tool"><?php echo $this->search(); ?></div>
						<?php endif; ?>
						<?php if ( $show( 'show_language' ) ) : ?>
							<div class="pal-mobile-menu__tool"><?php echo $this->language(); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $show( 'show_socials' ) ) : ?>
					<div class="pal-mobile-menu__socials"><?php echo $this->socials(); ?></div>
				<?php endif; ?>
			</div>
		</aside>
		<?php return ob_get_clean();
	}

	/* -------- helpers for event-variant shortcodes -------- */
	private function resolve_event_id( $atts_id = 0 ) {
		$id = (int) $atts_id;
		if ( ! $id && is_singular( NPAL_CPT_Event::POST_TYPE ) ) {
			$id = get_the_ID();
		}
		if ( $id && get_post_type( $id ) !== NPAL_CPT_Event::POST_TYPE ) {
			return 0;
		}
		return $id;
	}

	private function parse_awardees( $raw ) {
		$out = array();
		if ( ! $raw ) { return $out; }
		$lines = preg_split( '/\r?\n/', trim( (string) $raw ) );
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line || 0 === strpos( $line, '#' ) ) { continue; }
			$parts = array_map( 'trim', explode( '|', $line ) );
			$out[] = array(
				'name'   => isset( $parts[0] ) ? $parts[0] : '',
				'photo'  => isset( $parts[1] ) ? $parts[1] : '',
				'grade'  => isset( $parts[2] ) ? $parts[2] : '',
				'bio'    => isset( $parts[3] ) ? $parts[3] : '',
				'url'    => isset( $parts[4] ) ? $parts[4] : '',
			);
		}
		return $out;
	}

	/* -------- [pal_awardees] -------- */
	public function awardees( $atts ) {
		$a = shortcode_atts( array(
			'id'      => 0,
			'columns' => 3,
		), $atts, 'pal_awardees' );

		$post_id = $this->resolve_event_id( $a['id'] );
		if ( ! $post_id ) { return ''; }

		$awardees = $this->parse_awardees( get_post_meta( $post_id, '_pal_event_awardees', true ) );
		if ( empty( $awardees ) ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<p class="pal-empty" style="color:#646970;font-size:13px;">' . esc_html__( 'No awardees set. Add them in the Event Details → Youth Awardees field (one per line, pipe-delimited).', 'nashua-pal-core' ) . '</p>';
			}
			return '';
		}

		$cols = max( 1, min( 4, (int) $a['columns'] ) );

		ob_start(); ?>
		<div class="pal-awardees" style="display:grid;grid-template-columns:repeat(<?php echo esc_attr( $cols ); ?>,minmax(0,1fr));gap:24px;">
			<?php foreach ( $awardees as $aw ) :
				$has_link = ! empty( $aw['url'] );
				$tag      = $has_link ? 'a' : 'div';
				$href     = $has_link ? ' href="' . esc_url( $aw['url'] ) . '"' : '';
				?>
				<<?php echo $tag; ?> class="pal-awardee"<?php echo $href; ?> style="background:#fff;border:1px solid #E6ECF3;border-radius:14px;overflow:hidden;box-shadow:0 8px 20px rgba(11,31,58,.06);text-decoration:none;color:inherit;display:block;">
					<?php if ( $aw['photo'] ) : ?>
						<div style="aspect-ratio:1/1;background:#F7F9FC;overflow:hidden;">
							<img src="<?php echo esc_url( $aw['photo'] ); ?>" alt="<?php echo esc_attr( $aw['name'] ); ?>" style="width:100%;height:100%;object-fit:cover;display:block;" />
						</div>
					<?php endif; ?>
					<div style="padding:20px;">
						<?php if ( $aw['grade'] ) : ?>
							<p style="color:#F4A51C;font-size:11px;font-weight:800;letter-spacing:0.14em;text-transform:uppercase;margin:0 0 4px;"><?php echo esc_html( $aw['grade'] ); ?></p>
						<?php endif; ?>
						<h3 style="color:#071F3F;font-size:19px;font-weight:800;margin:0 0 8px;"><?php echo esc_html( $aw['name'] ); ?></h3>
						<?php if ( $aw['bio'] ) : ?>
							<p style="color:#4A5568;font-size:13px;line-height:1.55;margin:0;"><?php echo esc_html( $aw['bio'] ); ?></p>
						<?php endif; ?>
						<?php if ( $has_link ) : ?>
							<p style="color:#F4A51C;font-size:13px;font-weight:800;margin:12px 0 0;">Read More →</p>
						<?php endif; ?>
					</div>
				</<?php echo $tag; ?>>
			<?php endforeach; ?>
		</div>
		<style>
			@media(max-width:900px){.pal-awardees{grid-template-columns:repeat(2,minmax(0,1fr))!important;}}
			@media(max-width:520px){.pal-awardees{grid-template-columns:1fr!important;}}
		</style>
		<?php
		return ob_get_clean();
	}

	/* -------- [pal_event_sponsors] --------
	 * Legacy shortcode — now delegates to [pal_sponsors] (the tiered sponsor
	 * system). Kept registered so existing Elementor templates that still
	 * reference [pal_event_sponsors] don't break; they'll render the new
	 * scrolling marquee with level badges instead.
	 */
	public function event_sponsors( $atts ) {
		$a = shortcode_atts( array(
			'heading' => __( 'Thank You to Our Sponsors', 'nashua-pal-core' ),
		), $atts, 'pal_event_sponsors' );

		return do_shortcode( '[pal_sponsors context="event" post_id="auto" heading="' . esc_attr( $a['heading'] ) . '"]' );
	}

	/* -------- [pal_event_embed] --------
	 * Wraps the event's stored embed HTML (BetterUnite, Bid Beacon, etc.)
	 * in a designed surface so visitors can't tell it's a third-party
	 * iframe:
	 *   - Wide brand-gradient band so the embed doesn't feel cramped
	 *   - White inner card with rounded corners + soft shadow
	 *   - Small eyebrow ("Secure donation form") for context
	 *   - Animated loading skeleton that fades out the moment the
	 *     iframe is injected into the placeholder div
	 *   - Iframe styled to inherit the card (no border, full width,
	 *     hidden outer scrollbar)
	 *
	 * Supports both raw-iframe embeds (<iframe src=…>) and JS-injected
	 * embeds (BetterUnite's <div id=bu-checkout><script>...</script>).
	 */
	public function event_embed( $atts ) {
		$a = shortcode_atts( array(
			'id'        => 0,
			'max_width' => '1400',
			'eyebrow'   => __( 'Secure donation form', 'nashua-pal-core' ),
		), $atts, 'pal_event_embed' );

		$post_id = $this->resolve_event_id( $a['id'] );
		if ( ! $post_id ) { return ''; }

		$embed = get_post_meta( $post_id, '_pal_event_embed', true );
		if ( ! $embed ) { return ''; }

		$max = max( 800, min( 1800, (int) $a['max_width'] ) );
		$eyebrow = trim( (string) $a['eyebrow'] );

		ob_start(); ?>
		<section class="pal-embed-frame" data-pal-embed-frame>
			<div class="pal-embed-frame__inner" style="max-width:<?php echo esc_attr( $max ); ?>px;">
				<?php if ( $eyebrow ) : ?>
					<div class="pal-embed-frame__eyebrow">
						<svg class="pal-embed-frame__lock" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
						<span><?php echo esc_html( $eyebrow ); ?></span>
					</div>
				<?php endif; ?>

				<div class="pal-embed-frame__card">
					<div class="pal-embed-frame__skeleton" data-pal-embed-skeleton aria-hidden="true">
						<div class="pal-skel pal-skel--bar"></div>
						<div class="pal-skel pal-skel--row"></div>
						<div class="pal-skel pal-skel--row short"></div>
						<div class="pal-skel pal-skel--gap"></div>
						<div class="pal-skel pal-skel--field"></div>
						<div class="pal-skel pal-skel--field"></div>
						<div class="pal-skel pal-skel--field short"></div>
						<div class="pal-skel pal-skel--btn"></div>
					</div>
					<div class="pal-embed-frame__embed" data-pal-embed-target><?php echo $embed; // raw — already sanitized via wp_kses() on save in class-event-meta.php ?></div>
				</div>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}

	/* -------- [pal_previous_champions] -------- */
	public function previous_champions( $atts ) {
		$a = shortcode_atts( array(
			'limit'   => 5,
			'exclude' => 0,
			'columns' => 4,
		), $atts, 'pal_previous_champions' );

		$exclude = (int) $a['exclude'];
		if ( ! $exclude && is_singular( NPAL_CPT_Event::POST_TYPE ) ) {
			$exclude = get_the_ID();
		}

		$args = array(
			'post_type'      => NPAL_CPT_Event::POST_TYPE,
			'posts_per_page' => max( 1, min( 12, (int) $a['limit'] ) ),
			'post__not_in'   => $exclude ? array( $exclude ) : array(),
			'meta_key'       => '_pal_event_start',
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'   => '_pal_event_template',
					'value' => 'champions',
				),
			),
		);
		$q = new WP_Query( $args );
		if ( ! $q->have_posts() ) {
			wp_reset_postdata();
			return '';
		}

		$cols = max( 2, min( 5, (int) $a['columns'] ) );

		ob_start(); ?>
		<div class="pal-prev-champions" style="display:grid;grid-template-columns:repeat(<?php echo esc_attr( $cols ); ?>,minmax(0,1fr));gap:18px;">
			<?php while ( $q->have_posts() ) : $q->the_post();
				$id    = get_the_ID();
				$start = get_post_meta( $id, '_pal_event_start', true );
				$year  = $start ? wp_date( 'Y', strtotime( $start ) ) : '';
				$img   = $this->get_featured_image( $id, 'medium' );
				?>
				<a href="<?php the_permalink(); ?>" style="background:#fff;border:1px solid #E6ECF3;border-radius:12px;overflow:hidden;text-decoration:none;color:inherit;display:block;">
					<?php if ( $img ) : ?>
						<div style="aspect-ratio:16/10;background:#F7F9FC url(<?php echo esc_url( $img ); ?>) center/cover no-repeat;"></div>
					<?php endif; ?>
					<div style="padding:16px;">
						<?php if ( $year ) : ?>
							<p style="color:#F4A51C;font-size:11px;font-weight:800;letter-spacing:0.14em;text-transform:uppercase;margin:0 0 4px;"><?php echo esc_html( $year ); ?> Champions</p>
						<?php endif; ?>
						<h3 style="color:#071F3F;font-size:15px;font-weight:800;margin:0;line-height:1.3;"><?php the_title(); ?></h3>
					</div>
				</a>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<style>
			@media(max-width:900px){.pal-prev-champions{grid-template-columns:repeat(2,minmax(0,1fr))!important;}}
			@media(max-width:520px){.pal-prev-champions{grid-template-columns:1fr!important;}}
		</style>
		<?php
		return ob_get_clean();
	}

	/* -------- job listings grid -------- */
	private function job_type_label( $type ) {
		$map = array(
			'full_time'  => __( 'Full-Time', 'nashua-pal-core' ),
			'part_time'  => __( 'Part-Time', 'nashua-pal-core' ),
			'seasonal'   => __( 'Seasonal', 'nashua-pal-core' ),
			'volunteer'  => __( 'Volunteer', 'nashua-pal-core' ),
			'internship' => __( 'Internship', 'nashua-pal-core' ),
		);
		return isset( $map[ $type ] ) ? $map[ $type ] : '';
	}

	private function job_status_label( $status ) {
		$map = array(
			'open'   => __( 'Open', 'nashua-pal-core' ),
			'closed' => __( 'Closed', 'nashua-pal-core' ),
			'filled' => __( 'Filled', 'nashua-pal-core' ),
		);
		return isset( $map[ $status ] ) ? $map[ $status ] : '';
	}

	public function job_listings( $atts ) {
		$a = shortcode_atts( array(
			'count'      => -1,
			'status'     => 'open',    // 'open' | 'all' | 'closed'
			'view'       => 'list',    // 'list' | 'grid'
			'columns'    => 2,
			'show_image' => 'yes',
		), $atts, 'pal_job_listings' );

		$show_image = in_array( strtolower( (string) $a['show_image'] ), array( 'yes', '1', 'true' ), true );
		$view       = ( 'grid' === strtolower( (string) $a['view'] ) ) ? 'grid' : 'list';
		$columns    = max( 1, min( 4, (int) $a['columns'] ) );

		$args = array(
			'post_type'      => NPAL_CPT_Job::POST_TYPE,
			'posts_per_page' => (int) $a['count'],
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		if ( 'all' !== strtolower( (string) $a['status'] ) ) {
			$args['meta_query'] = array(
				array( 'key' => '_pal_job_status', 'value' => sanitize_text_field( $a['status'] ) ),
			);
		}

		$q = new WP_Query( $args );

		if ( ! $q->have_posts() ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<p class="pal-empty" style="text-align:center;color:#646970;font-size:13px;">' . esc_html__( 'No job listings yet — add some at Jobs → Add Job.', 'nashua-pal-core' ) . '</p>';
			}
			return '<p class="pal-empty" style="text-align:center;color:#646970;font-size:13px;">' . esc_html__( 'No open positions at this time. Check back soon!', 'nashua-pal-core' ) . '</p>';
		}

		$wrap_class  = 'pal-jobs pal-jobs--' . $view;
		if ( 'grid' === $view ) { $wrap_class .= ' pal-jobs--cols-' . $columns; }

		ob_start(); ?>
		<div class="<?php echo esc_attr( $wrap_class ); ?>">
		<?php while ( $q->have_posts() ) : $q->the_post();
			$id         = get_the_ID();
			$type       = get_post_meta( $id, '_pal_job_type', true );
			$location   = get_post_meta( $id, '_pal_job_location', true );
			$department = get_post_meta( $id, '_pal_job_department', true );
			$salary     = get_post_meta( $id, '_pal_job_salary', true );
			$apply_url  = get_post_meta( $id, '_pal_job_apply_url', true );
			$apply_email = get_post_meta( $id, '_pal_job_apply_email', true );
			$status     = get_post_meta( $id, '_pal_job_status', true );
			$thumb      = $show_image ? $this->get_featured_image( $id, 'medium_large' ) : '';
			$permalink  = get_permalink( $id );
			$job_title   = get_the_title( $id );
			$apply_href  = $apply_url ?: ( $apply_email ? 'mailto:' . antispambot( $apply_email ) . '?subject=' . rawurlencode( 'Application: ' . $job_title ) : '' );
			$apply_target = ! $apply_url && $apply_email ? ' target="_blank" rel="noopener"' : '';
			?>
			<article class="pal-job-card<?php echo $thumb ? '' : ' pal-job-card--no-media'; ?>">
				<?php if ( $thumb ) : ?>
					<div class="pal-job-card__media">
						<a href="<?php echo esc_url( $permalink ); ?>">
							<img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" />
						</a>
					</div>
				<?php endif; ?>
				<div class="pal-job-card__body">
					<div class="pal-job-card__badges">
						<?php if ( $type ) : ?>
							<span class="pal-job-card__badge pal-job-card__badge--type"><?php echo esc_html( $this->job_type_label( $type ) ); ?></span>
						<?php endif; ?>
						<?php if ( $status && 'open' !== $status ) : ?>
							<span class="pal-job-card__badge pal-job-card__badge--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $this->job_status_label( $status ) ); ?></span>
						<?php endif; ?>
					</div>
					<h3 class="pal-job-card__title"><a href="<?php echo esc_url( $permalink ); ?>"><?php the_title(); ?></a></h3>
					<?php if ( $department || $location ) : ?>
						<div class="pal-job-card__meta">
							<?php if ( $department ) : ?>
								<span class="pal-job-card__dept"><?php echo esc_html( $department ); ?></span>
							<?php endif; ?>
							<?php if ( $location ) : ?>
								<span class="pal-job-card__location"><?php echo esc_html( $location ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					<div class="pal-job-card__excerpt">
						<?php echo wp_kses_post( wp_trim_words( get_the_excerpt(), 30, '…' ) ); ?>
					</div>
					<div class="pal-job-card__actions">
						<a class="pal-btn pal-btn--primary pal-job-card__apply" href="<?php echo esc_url( $apply_href ?: $permalink ); ?>"<?php echo $apply_target; ?>><?php echo ! $apply_url && $apply_email ? esc_html__( 'Email Cover Letter and Resume', 'nashua-pal-core' ) : esc_html__( 'Apply Today', 'nashua-pal-core' ); ?></a>
						<a class="pal-job-card__details-link" href="<?php echo esc_url( $permalink ); ?>"><?php esc_html_e( 'View Details', 'nashua-pal-core' ); ?> →</a>
					</div>
				</div>
			</article>
		<?php endwhile; wp_reset_postdata(); ?>
		</div>
		<?php return ob_get_clean();
	}

	/* -------- single job detail view -------- */
	public function job_single( $atts ) {
		$a = shortcode_atts( array(
			'id'   => 0,
			'slug' => '',
		), $atts, 'pal_job_single' );

		$post_id = (int) $a['id'];
		if ( ! $post_id && $a['slug'] ) {
			$page = get_page_by_path( $a['slug'], OBJECT, NPAL_CPT_Job::POST_TYPE );
			if ( $page ) { $post_id = $page->ID; }
		}
		if ( ! $post_id ) {
			$qid  = (int) get_queried_object_id();
			$qobj = get_queried_object();
			if ( $qid && $qobj instanceof WP_Post && NPAL_CPT_Job::POST_TYPE === $qobj->post_type ) {
				$post_id = $qid;
			}
		}
		if ( ! $post_id ) {
			return current_user_can( 'edit_posts' )
				? '<p class="pal-empty">' . esc_html__( 'Job not found — specify id or slug, or use on a single job page.', 'nashua-pal-core' ) . '</p>'
				: '';
		}

		$post       = get_post( $post_id );
		$type       = get_post_meta( $post_id, '_pal_job_type', true );
		$location   = get_post_meta( $post_id, '_pal_job_location', true );
		$department = get_post_meta( $post_id, '_pal_job_department', true );
		$salary     = get_post_meta( $post_id, '_pal_job_salary', true );
		$apply_url  = get_post_meta( $post_id, '_pal_job_apply_url', true );
		$apply_email = get_post_meta( $post_id, '_pal_job_apply_email', true );
		$status     = get_post_meta( $post_id, '_pal_job_status', true );
		$posted     = get_post_meta( $post_id, '_pal_job_posted_date', true );
		$deadline   = get_post_meta( $post_id, '_pal_job_deadline', true );
		$program_id = get_post_meta( $post_id, '_pal_job_program', true );
		$job_title    = get_the_title( $post_id );
		$apply_href   = $apply_url ?: ( $apply_email ? 'mailto:' . antispambot( $apply_email ) . '?subject=' . rawurlencode( 'Application: ' . $job_title ) : '' );
		$apply_target = ! $apply_url && $apply_email ? ' target="_blank" rel="noopener"' : '';
		$thumb        = $this->get_featured_image( $post_id, 'large' );

		ob_start(); ?>
		<article class="pal-job-single">
			<?php if ( $thumb ) : ?>
				<div class="pal-job-single__hero" style="background-image:url(<?php echo esc_url( $thumb ); ?>);"></div>
			<?php endif; ?>

			<div class="pal-job-single__grid">
				<div class="pal-job-single__content">
					<?php echo apply_filters( 'the_content', $post->post_content ); ?>
				</div>
				<aside class="pal-job-single__sidebar">
					<?php if ( $apply_href ) : ?>
						<a class="pal-btn pal-btn--primary pal-job-single__apply" href="<?php echo esc_url( $apply_href ); ?>"<?php echo $apply_target; ?>><?php echo ! $apply_url && $apply_email ? esc_html__( 'Email Cover Letter and Resume', 'nashua-pal-core' ) : esc_html__( 'Apply Now', 'nashua-pal-core' ); ?></a>
					<?php endif; ?>
					<dl class="pal-job-single__details">
						<?php if ( $type ) : ?>
							<dt><?php esc_html_e( 'Position Type', 'nashua-pal-core' ); ?></dt>
							<dd><?php echo esc_html( $this->job_type_label( $type ) ); ?></dd>
						<?php endif; ?>
						<?php if ( $department ) : ?>
							<dt><?php esc_html_e( 'Department', 'nashua-pal-core' ); ?></dt>
							<dd><?php echo esc_html( $department ); ?></dd>
						<?php endif; ?>
						<?php if ( $location ) : ?>
							<dt><?php esc_html_e( 'Location', 'nashua-pal-core' ); ?></dt>
							<dd><?php echo esc_html( $location ); ?></dd>
						<?php endif; ?>
						<?php if ( $salary ) : ?>
							<dt><?php esc_html_e( 'Compensation', 'nashua-pal-core' ); ?></dt>
							<dd><?php echo esc_html( $salary ); ?></dd>
						<?php endif; ?>
						<?php if ( $posted ) : ?>
							<dt><?php esc_html_e( 'Date Posted', 'nashua-pal-core' ); ?></dt>
							<dd><?php echo esc_html( wp_date( 'M j, Y', strtotime( $posted ) ) ); ?></dd>
						<?php endif; ?>
						<?php if ( $deadline ) : ?>
							<dt><?php esc_html_e( 'Deadline', 'nashua-pal-core' ); ?></dt>
							<dd><?php echo esc_html( wp_date( 'M j, Y', strtotime( $deadline ) ) ); ?></dd>
						<?php endif; ?>
						<?php if ( $status ) : ?>
							<dt><?php esc_html_e( 'Status', 'nashua-pal-core' ); ?></dt>
							<dd><span class="pal-job-single__status pal-job-single__status--<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $this->job_status_label( $status ) ); ?></span></dd>
						<?php endif; ?>
						<?php if ( $program_id ) :
							$prog_title = get_the_title( $program_id );
							$prog_url   = get_permalink( $program_id );
							if ( $prog_title ) : ?>
								<dt><?php esc_html_e( 'Program', 'nashua-pal-core' ); ?></dt>
								<dd><a href="<?php echo esc_url( $prog_url ); ?>"><?php echo esc_html( $prog_title ); ?></a></dd>
							<?php endif; ?>
						<?php endif; ?>
					</dl>
				</aside>
			</div>
		</article>
		<?php return ob_get_clean();
	}
}
