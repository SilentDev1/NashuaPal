<?php
/**
 * Nashua PAL Elementor widgets — Content / design building blocks.
 *
 * These are the widgets the Events + Programs archive templates use to
 * compose their hero, featured-event card, video gallery, photo gallery,
 * and CTA banner sections — replacing the inline-HTML approach the
 * original archive JSONs used.
 *
 * Each widget renders its own HTML directly (not via `[shortcode]`
 * indirection) so visual configuration happens entirely through
 * Elementor's panel controls — no HTML editing required.
 *
 * Featured Event pulls the next upcoming event from the merged CPT +
 * Google Calendar source (same logic the [pal_upcoming_events] shortcode
 * uses), so the widget always reflects whatever event is next without
 * any manual wiring.
 *
 * @package NashuaPalCore
 * @since 1.8.65
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ============================================================================
   Helpers shared by every widget below.
   ========================================================================= */

if ( ! function_exists( 'npal_widget_inline_icon' ) ) {
	function npal_widget_inline_icon( $name ) {
		$icons = array(
			'calendar' => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" style="vertical-align:-2px;margin-right:5px;flex-shrink:0;"><path d="M7 11h2v2H7v-2zm14-5v14a2 2 0 01-2 2H5a2 2 0 01-2-2V6c0-1.1.9-2 2-2h1V2h2v2h8V2h2v2h1a2 2 0 012 2zM5 8h14V6H5v2zm14 12V10H5v10h14zm-4-7h2v-2h-2v2zm-4 0h2v-2h-2v2z"/></svg>',
			'clock'    => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" style="vertical-align:-2px;margin-right:5px;flex-shrink:0;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67V7z"/></svg>',
			'pin'      => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" style="vertical-align:-2px;margin-right:5px;flex-shrink:0;"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 010-5 2.5 2.5 0 010 5z"/></svg>',
			'play'     => '<svg viewBox="0 0 24 24" width="22" height="22" fill="#fff"><path d="M8 5v14l11-7z"/></svg>',
			'play-lg'  => '<svg viewBox="0 0 24 24" width="56" height="56" fill="rgba(255,255,255,0.95)" style="filter:drop-shadow(0 2px 8px rgba(0,0,0,0.4));"><path d="M8 5v14l11-7z"/></svg>',
			'heart'    => '<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" style="vertical-align:-2px;margin-right:5px;flex-shrink:0;"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>',
			'hands'    => '<svg viewBox="0 0 24 24" width="64" height="64" fill="none" stroke="#F4A51C" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M7 11V7a2 2 0 0 1 4 0v4M13 11V5a2 2 0 0 1 4 0v6"/><path d="M3 11h2l1 7a3 3 0 0 0 3 3h6a3 3 0 0 0 3-3l1-7h2"/><path d="M9 4l-1-2M15 4l1-2"/></svg>',
		);
		return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
	}
}

/**
 * Extract a YouTube video ID from any of the common URL formats:
 *   https://www.youtube.com/watch?v=VIDEO_ID
 *   https://youtu.be/VIDEO_ID
 *   https://www.youtube.com/embed/VIDEO_ID
 *   https://www.youtube.com/shorts/VIDEO_ID
 *   https://m.youtube.com/watch?v=VIDEO_ID
 * Returns the 11-character ID, or null if the URL isn't recognized.
 */
if ( ! function_exists( 'npal_youtube_id' ) ) {
	function npal_youtube_id( $url ) {
		if ( ! $url ) { return null; }
		if ( preg_match( '#(?:youtube\.com/(?:watch\?v=|embed/|v/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{11})#', $url, $m ) ) {
			return $m[1];
		}
		return null;
	}
}

/**
 * Fetch the next upcoming event by merging the pal_event CPT and the
 * Google Calendar source. Returns an associative array with the event's
 * normalized fields, or null if nothing is upcoming.
 */
if ( ! function_exists( 'npal_next_upcoming_event' ) ) {
	function npal_next_upcoming_event() {
		$now    = current_time( 'timestamp' );
		$merged = array();

		// CPT events — actual meta key is `_pal_event_start` (matches the
		// [pal_upcoming_events] shortcode in class-shortcodes.php). The
		// value is a strtotime-parseable string (typically "YYYY-MM-DD HH:MM"
		// or "YYYY-MM-DD"). We don't filter by date in the SQL query because
		// it's a string compare against an arbitrary format — just pull all
		// events that have a start and filter in PHP via strtotime.
		$q = new WP_Query( array(
			'post_type'      => 'pal_event',
			'posts_per_page' => 50,
			'meta_key'       => '_pal_event_start',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			'meta_query'     => array(
				array( 'key' => '_pal_event_start', 'compare' => 'EXISTS' ),
			),
			'no_found_rows'  => true,
		) );
		foreach ( $q->posts as $p ) {
			$start = get_post_meta( $p->ID, '_pal_event_start', true );
			$ts    = $start ? strtotime( $start ) : 0;
			if ( ! $ts || $ts < $now ) { continue; }
			$merged[] = array(
				'ts'       => $ts,
				'title'    => get_the_title( $p ),
				'date_str' => wp_date( 'l, F j, Y', $ts ),
				'time_str' => wp_date( 'g:i a', $ts ),
				'location' => get_post_meta( $p->ID, '_pal_event_location', true ),
				'url'      => get_permalink( $p ),
				'image'    => get_the_post_thumbnail_url( $p, 'large' ),
				'excerpt'  => has_excerpt( $p ) ? get_the_excerpt( $p ) : wp_trim_words( $p->post_content, 30 ),
				'source'   => 'cpt',
			);
		}

		// Google Calendar — uses the same NPAL_Google_Calendar::get_upcoming()
		// the [pal_upcoming_events] shortcode uses, so we get the same merge.
		if ( class_exists( 'NPAL_Google_Calendar' ) ) {
			$gcal = NPAL_Google_Calendar::get_upcoming( 50 );
			foreach ( (array) $gcal as $e ) {
				$ts = isset( $e['ts'] ) ? (int) $e['ts'] : 0;
				if ( ! $ts || $ts < $now ) { continue; }
				$merged[] = array(
					'ts'       => $ts,
					'title'    => isset( $e['title'] )    ? $e['title']    : '',
					'date_str' => isset( $e['date_str'] ) ? $e['date_str'] : wp_date( 'l, F j, Y', $ts ),
					'time_str' => isset( $e['time_str'] ) ? $e['time_str'] : wp_date( 'g:i a', $ts ),
					'location' => isset( $e['location'] ) ? $e['location'] : '',
					'url'      => isset( $e['url'] )      ? $e['url']      : '',
					'image'    => '',
					'excerpt'  => isset( $e['description'] ) ? $e['description'] : '',
					'source'   => 'gcal',
				);
			}
		}

		if ( empty( $merged ) ) { return null; }
		usort( $merged, function ( $a, $b ) { return $a['ts'] - $b['ts']; } );
		return $merged[0];
	}
}

/* ============================================================================
   Widget: PAL Section Heading — eyebrow + title combo for section openers
   ========================================================================= */

class NPAL_Widget_Section_Heading extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_section_heading'; }
	public function get_title()    { return __( 'PAL Section Heading', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-t-letter'; }
	public function get_keywords() { return array( 'pal', 'heading', 'section', 'eyebrow', 'title' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Heading', 'nashua-pal-core' ) ) );

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow (small uppercase label)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'UPCOMING EVENTS',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Upcoming Events', 'nashua-pal-core' ),
		) );
		$this->add_responsive_control( 'align', array(
			'label'   => __( 'Alignment', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::CHOOSE,
			'options' => array(
				'left'   => array( 'title' => 'Left',   'icon' => 'eicon-text-align-left' ),
				'center' => array( 'title' => 'Center', 'icon' => 'eicon-text-align-center' ),
				'right'  => array( 'title' => 'Right',  'icon' => 'eicon-text-align-right' ),
			),
			'default'   => 'left',
			'selectors' => array( '{{WRAPPER}} .pal-section-heading' => 'text-align: {{VALUE}};' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="pal-section-heading" style="max-width:1200px;margin:0 auto 24px;padding:0 20px;">
			<?php if ( $s['eyebrow'] ) : ?><span style="display:inline-block;color:#F4A51C;font-family:Montserrat,sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:0.14em;font-size:0.75rem;margin-bottom:6px;"><?php echo esc_html( $s['eyebrow'] ); ?></span><?php endif; ?>
			<h2 style="font-family:Montserrat,sans-serif;font-weight:800;color:#071F3F;margin:0;font-size:clamp(1.6rem,3vw,2.2rem);"><?php echo esc_html( $s['title'] ); ?></h2>
		</div>
		<?php
	}
}

/* ============================================================================
   Widget: PAL Page Hero
   ========================================================================= */

class NPAL_Widget_Page_Hero extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_page_hero'; }
	public function get_title()    { return __( 'PAL Page Hero', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-header'; }
	public function get_keywords() { return array( 'pal', 'hero', 'header', 'banner', 'page' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Hero', 'nashua-pal-core' ) ) );

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow (small uppercase label)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( "WHAT'S HAPPENING", 'nashua-pal-core' ),
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Community Events', 'nashua-pal-core' ),
		) );
		$this->add_control( 'lede', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => __( 'Stay connected with upcoming programs, special events, and community happenings hosted by Nashua PAL.', 'nashua-pal-core' ),
		) );
		$this->add_control( 'photo', array(
			'label'   => __( 'Side photo', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$photo = ( ! empty( $s['photo']['url'] ) ) ? $s['photo']['url'] : '';
		?>
		<section class="pal-page-hero">
			<div class="pal-page-hero__inner<?php echo $photo ? ' pal-page-hero__inner--has-photo' : ''; ?>">
				<div class="pal-page-hero__text">
					<?php if ( $s['eyebrow'] ) : ?><span class="pal-page-hero__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span><?php endif; ?>
					<h1 class="pal-page-hero__title"><?php echo esc_html( $s['title'] ); ?></h1>
					<?php if ( $s['lede'] ) : ?><p class="pal-page-hero__lede"><?php echo esc_html( $s['lede'] ); ?></p><?php endif; ?>
				</div>
				<?php if ( $photo ) : ?>
					<div class="pal-page-hero__photo" style="background-image:url(<?php echo esc_url( $photo ); ?>);">
						<div class="pal-page-hero__photo-overlay"></div>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ============================================================================
   Widget: PAL Featured Event — auto-pulls next upcoming from CPT + GCal
   ========================================================================= */

class NPAL_Widget_Featured_Event extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_featured_event'; }
	public function get_title()    { return __( 'PAL Featured Event', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-star-o'; }
	public function get_keywords() { return array( 'pal', 'event', 'featured', 'next', 'upcoming' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Featured Event', 'nashua-pal-core' ) ) );

		$this->add_control( '_info', array(
			'type'    => \Elementor\Controls_Manager::RAW_HTML,
			'raw'     => __( 'Auto-pulls the next upcoming event from the <code>pal_event</code> CPT + Google Calendar feed (whichever is sooner). Override fields below if you want a fixed featured event regardless of dates.', 'nashua-pal-core' ),
			'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
		) );

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'FEATURED EVENT', 'nashua-pal-core' ),
		) );
		$this->add_control( 'title_override', array(
			'label'       => __( 'Title override', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => __( 'Leave empty to use the event title', 'nashua-pal-core' ),
		) );
		$this->add_control( 'description_override', array(
			'label'       => __( 'Description override', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXTAREA,
			'placeholder' => __( 'Leave empty to use the event excerpt', 'nashua-pal-core' ),
		) );
		$this->add_control( 'photo_override', array(
			'label'   => __( 'Photo override', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );
		$this->add_control( 'details_label', array(
			'label'   => __( 'Details button label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'View Details', 'nashua-pal-core' ),
		) );
		$this->add_control( 'show_calendar_btn', array(
			'label'        => __( 'Show "Add to Calendar" button', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$ev = npal_next_upcoming_event();
		if ( ! $ev ) {
			echo '<div style="padding:30px;background:#F7F9FC;border-radius:10px;text-align:center;color:#646970;">' . esc_html__( 'No upcoming events found. Add an event under Events → Add Event, or wire a Google Calendar feed under Nashua PAL → Settings → Calendar.', 'nashua-pal-core' ) . '</div>';
			return;
		}

		$title    = $s['title_override']       ? $s['title_override']       : $ev['title'];
		$desc     = $s['description_override'] ? $s['description_override'] : $ev['excerpt'];
		$photo    = ! empty( $s['photo_override']['url'] ) ? $s['photo_override']['url'] : $ev['image'];
		$mon      = wp_date( 'M', $ev['ts'] );
		$day      = wp_date( 'j',  $ev['ts'] );
		$cal_icon = npal_widget_inline_icon( 'calendar' );
		$clk_icon = npal_widget_inline_icon( 'clock' );
		$pin_icon = npal_widget_inline_icon( 'pin' );
		$heart    = npal_widget_inline_icon( 'heart' );
		?>
		<section class="pal-feat-event">
		<div class="pal-feat-event__wrap">
			<div class="pal-feat-event__card">
				<div class="pal-feat-event__photo" <?php if ( $photo ) echo 'style="background-image:url(' . esc_url( $photo ) . ');"'; ?>>
					<div class="pal-feat-event__date-badge">
						<span class="pal-feat-event__date-mon"><?php echo esc_html( strtoupper( $mon ) ); ?></span>
						<span class="pal-feat-event__date-day"><?php echo esc_html( $day ); ?></span>
					</div>
				</div>
				<div class="pal-feat-event__body">
					<?php if ( $s['eyebrow'] ) : ?><span class="pal-feat-event__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span><?php endif; ?>
					<h2 class="pal-feat-event__title"><?php echo esc_html( $title ); ?></h2>
					<div class="pal-feat-event__meta">
						<?php if ( $ev['date_str'] ) : ?><span class="pal-feat-event__meta-item"><?php echo $cal_icon . esc_html( $ev['date_str'] ); ?></span><?php endif; ?>
						<?php if ( $ev['time_str'] ) : ?><span class="pal-feat-event__meta-item"><?php echo $clk_icon . esc_html( $ev['time_str'] ); ?></span><?php endif; ?>
						<?php if ( $ev['location'] ) : ?><span class="pal-feat-event__meta-item"><?php echo $pin_icon . esc_html( $ev['location'] ); ?></span><?php endif; ?>
					</div>
					<?php if ( $desc ) : ?><p class="pal-feat-event__desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
					<div class="pal-feat-event__actions">
						<a href="<?php echo esc_url( $ev['url'] ); ?>" class="pal-feat-event__btn pal-feat-event__btn--primary"><?php echo $heart; ?><?php echo esc_html( $s['details_label'] ); ?> →</a>
						<?php if ( 'yes' === $s['show_calendar_btn'] && $ev['ts'] >= time() ) : ?>
							<a href="<?php echo esc_url( $ev['url'] ); ?>" class="pal-feat-event__btn pal-feat-event__btn--secondary"><?php echo $cal_icon; ?>Add to Calendar</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		</section>
		<?php
	}
}

/* ============================================================================
   Widget: PAL Video Gallery — main video + side thumbnails
   ========================================================================= */

class NPAL_Widget_Video_Gallery extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_video_gallery'; }
	public function get_title()    { return __( 'PAL Video Gallery', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-youtube'; }
	public function get_keywords() { return array( 'pal', 'video', 'gallery', 'youtube', 'highlights' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Video Gallery', 'nashua-pal-core' ) ) );

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'PAL IN ACTION',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'See Our Community In Motion', 'nashua-pal-core' ),
		) );
		$this->add_control( 'lede', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => __( "Watch highlights from recent events and see the impact we're making together.", 'nashua-pal-core' ),
		) );
		$this->add_control( 'cta_label', array(
			'label'   => __( 'CTA button label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Watch More on YouTube', 'nashua-pal-core' ),
		) );
		$this->add_control( 'cta_url', array(
			'label'   => __( 'CTA button URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'https://www.youtube.com/@nashuapal',
		) );
		$this->add_control( 'main_url', array(
			'label'       => __( 'Main video URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'https://www.youtube.com/watch?v=…',
			'description' => __( 'Paste any YouTube URL (watch / youtu.be / shorts / embed). The video plays inline when a visitor clicks it.', 'nashua-pal-core' ),
		) );
		$this->add_control( 'main_thumb', array(
			'label'       => __( 'Main video thumbnail (optional)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::MEDIA,
			'default'     => array( 'url' => '' ),
			'description' => __( 'Leave empty to auto-fetch YouTube\'s poster for the URL above.', 'nashua-pal-core' ),
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'thumb_title',    array( 'label' => __( 'Title',     'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT,    'default' => 'Video title' ) );
		$repeater->add_control( 'thumb_duration', array( 'label' => __( 'Duration',  'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT,    'default' => '2:45' ) );
		$repeater->add_control( 'thumb_url',      array(
			'label'       => __( 'YouTube URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'https://www.youtube.com/watch?v=…',
			'description' => __( 'YouTube URL — clicking the thumb swaps the main player to this video.', 'nashua-pal-core' ),
		) );
		$repeater->add_control( 'thumb_image',    array(
			'label'       => __( 'Thumbnail (optional)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::MEDIA,
			'default'     => array( 'url' => '' ),
			'description' => __( 'Leave empty to auto-fetch YouTube\'s poster.', 'nashua-pal-core' ),
		) );

		$this->add_control( 'thumbs', array(
			'label'       => __( 'Thumbnail videos', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(
				array( 'thumb_title' => 'Youth Sports Day Highlights', 'thumb_duration' => '2:45', 'thumb_url' => '', 'thumb_image' => array( 'url' => '' ) ),
				array( 'thumb_title' => 'Summer Program Kickoff',      'thumb_duration' => '1:58', 'thumb_url' => '', 'thumb_image' => array( 'url' => '' ) ),
				array( 'thumb_title' => 'Community Cleanup Day Recap', 'thumb_duration' => '1:42', 'thumb_url' => '', 'thumb_image' => array( 'url' => '' ) ),
			),
			'title_field' => '{{{ thumb_title }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s          = $this->get_settings_for_display();
		$play_lg    = npal_widget_inline_icon( 'play-lg' );
		$play_sm    = npal_widget_inline_icon( 'play' );
		$main_yt    = npal_youtube_id( $s['main_url'] );
		$main_thumb = ! empty( $s['main_thumb']['url'] ) ? $s['main_thumb']['url'] : '';
		if ( ! $main_thumb && $main_yt ) {
			$main_thumb = 'https://img.youtube.com/vi/' . $main_yt . '/maxresdefault.jpg';
		}

		// Each widget instance gets a unique element id so the click-to-swap
		// JS can target the right main-video container even if multiple video
		// galleries appear on the same page.
		static $instance_counter = 0;
		$instance_counter++;
		$wid = 'palvg-' . absint( $this->get_id_int() ) . '-' . $instance_counter;
		?>
		<section style="background:#071F3F;padding:60px 40px;">
		<div style="max-width:1320px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr 0.75fr;gap:30px;align-items:start;">
			<div style="padding-right:10px;">
				<?php if ( $s['eyebrow'] ) : ?><span style="display:inline-block;color:#F4A51C;font-family:Montserrat,sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:0.14em;font-size:0.75rem;margin-bottom:8px;"><?php echo esc_html( $s['eyebrow'] ); ?></span><?php endif; ?>
				<h2 style="color:#fff;font-family:Montserrat,sans-serif;font-weight:800;margin:0 0 16px;font-size:clamp(1.5rem,2.5vw,2rem);line-height:1.15;"><?php echo esc_html( $s['title'] ); ?></h2>
				<?php if ( $s['lede'] ) : ?><p style="color:#C4CCD8;font-size:0.95rem;line-height:1.6;margin:0 0 22px;"><?php echo esc_html( $s['lede'] ); ?></p><?php endif; ?>
				<?php if ( $s['cta_label'] && $s['cta_url'] ) : ?>
					<a href="<?php echo esc_url( $s['cta_url'] ); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;padding:12px 24px;background:#F4A51C;color:#071F3F;font-family:Montserrat,sans-serif;font-weight:700;font-size:0.9rem;border-radius:8px;text-decoration:none;box-shadow:0 4px 10px rgba(244,165,28,0.3);"><?php echo $play_sm; ?> <?php echo esc_html( $s['cta_label'] ); ?></a>
				<?php endif; ?>
			</div>
			<div id="<?php echo esc_attr( $wid ); ?>" data-palvg-main style="position:relative;aspect-ratio:16/10;border-radius:10px;overflow:hidden;background:<?php echo $main_thumb ? "url('" . esc_url( $main_thumb ) . "') center/cover no-repeat" : '#0D2952'; ?>;">
				<?php if ( $main_yt ) : ?>
					<a href="https://www.youtube.com/watch?v=<?php echo esc_attr( $main_yt ); ?>" data-palvg-id="<?php echo esc_attr( $main_yt ); ?>" data-palvg-target="<?php echo esc_attr( $wid ); ?>" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(7,31,63,0.25);text-decoration:none;" aria-label="<?php esc_attr_e( 'Play video', 'nashua-pal-core' ); ?>"><?php echo $play_lg; ?></a>
				<?php elseif ( $s['main_url'] ) : ?>
					<a href="<?php echo esc_url( $s['main_url'] ); ?>" target="_blank" rel="noopener" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(7,31,63,0.25);text-decoration:none;"><?php echo $play_lg; ?></a>
				<?php endif; ?>
			</div>
			<div style="display:flex;flex-direction:column;gap:10px;">
				<?php foreach ( (array) $s['thumbs'] as $t ) :
					$t_yt    = npal_youtube_id( $t['thumb_url'] );
					$t_thumb = isset( $t['thumb_image']['url'] ) ? $t['thumb_image']['url'] : '';
					if ( ! $t_thumb && $t_yt ) {
						$t_thumb = 'https://img.youtube.com/vi/' . $t_yt . '/mqdefault.jpg';
					}
					$href     = $t_yt ? 'https://www.youtube.com/watch?v=' . $t_yt : ( $t['thumb_url'] ?: '#' );
					$data_yt  = $t_yt ? ' data-palvg-id="' . esc_attr( $t_yt ) . '" data-palvg-target="' . esc_attr( $wid ) . '"' : '';
					$target   = $t_yt ? '' : ' target="_blank" rel="noopener"';
					?>
					<a href="<?php echo esc_url( $href ); ?>"<?php echo $data_yt; ?><?php echo $target; ?> style="display:flex;align-items:center;gap:12px;padding:10px;background:rgba(255,255,255,0.05);border-radius:8px;text-decoration:none;color:#fff;transition:background .15s ease;">
						<div style="position:relative;width:90px;height:60px;flex-shrink:0;border-radius:6px;overflow:hidden;background:<?php echo $t_thumb ? "url('" . esc_url( $t_thumb ) . "') center/cover no-repeat" : '#0D2952'; ?>;">
							<div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.25);"><?php echo $play_sm; ?></div>
						</div>
						<div style="flex:1;min-width:0;">
							<div style="font-family:Montserrat,sans-serif;font-weight:700;font-size:0.92rem;color:#fff;margin:0 0 3px;"><?php echo esc_html( $t['thumb_title'] ); ?></div>
							<div style="color:#C4CCD8;font-size:0.78rem;"><?php echo esc_html( $t['thumb_duration'] ); ?></div>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		</section>
		<?php
		// Inline JS that swaps the main video to an autoplaying YouTube
		// iframe when the visitor clicks the main play button OR any side
		// thumbnail. Guarded so the listener only registers once even if
		// the widget appears multiple times on the same page (e.g. on the
		// Events archive + Programs archive both being on screen via
		// builder preview).
		?>
		<script>
		(function(){
			if ( window.__palvgInit ) { return; }
			window.__palvgInit = true;
			document.addEventListener( 'click', function ( e ) {
				var link = e.target.closest( 'a[data-palvg-id][data-palvg-target]' );
				if ( ! link ) { return; }
				e.preventDefault();
				var target = document.getElementById( link.getAttribute( 'data-palvg-target' ) );
				if ( ! target ) { return; }
				var ytId = link.getAttribute( 'data-palvg-id' );
				target.innerHTML = '<iframe src="https://www.youtube.com/embed/' + encodeURIComponent( ytId ) + '?autoplay=1&rel=0" style="position:absolute;inset:0;width:100%;height:100%;border:0;" allow="autoplay;encrypted-media;picture-in-picture" allowfullscreen></iframe>';
			} );
		})();
		</script>
		<?php
	}
}

/* ============================================================================
   Widget: PAL Photo Gallery — multi-col image gallery
   ========================================================================= */

class NPAL_Widget_Photo_Gallery extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_photo_gallery'; }
	public function get_title()    { return __( 'PAL Photo Gallery', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-gallery-grid'; }
	public function get_keywords() { return array( 'pal', 'photo', 'gallery', 'images', 'highlights' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Photo Gallery', 'nashua-pal-core' ) ) );

		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Photo Highlights', 'nashua-pal-core' ),
		) );
		$this->add_control( 'view_all_label', array(
			'label'   => __( 'View All button label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'View Full Gallery', 'nashua-pal-core' ),
		) );
		/* view_all_url removed in 1.12.8 — button now opens a lightbox popup */
		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 5,
			'min'     => 3,
			'max'     => 6,
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'image', array( 'label' => __( 'Image', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => '' ) ) );
		$repeater->add_control( 'link',  array( 'label' => __( 'Link (optional)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT ) );

		$this->add_control( 'photos', array(
			'label'       => __( 'Photos', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(
				array( 'image' => array( 'url' => '' ), 'link' => '' ),
				array( 'image' => array( 'url' => '' ), 'link' => '' ),
				array( 'image' => array( 'url' => '' ), 'link' => '' ),
				array( 'image' => array( 'url' => '' ), 'link' => '' ),
				array( 'image' => array( 'url' => '' ), 'link' => '' ),
			),
			'title_field' => 'Photo',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$cols = max( 3, min( 6, intval( $s['columns'] ) ) );
		$photos = (array) $s['photos'];
		$label  = ! empty( $s['view_all_label'] ) ? $s['view_all_label'] : '';
		$id     = 'pal-gallery-' . $this->get_id();
		?>
		<section style="background:#F7F9FC;padding:50px 40px;">
		<div style="max-width:1200px;margin:0 auto;">
			<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
				<h2 style="font-family:Montserrat,sans-serif;font-weight:800;color:#071F3F;margin:0;font-size:clamp(1.5rem,2.5vw,2rem);"><?php echo esc_html( $s['title'] ); ?></h2>
				<?php if ( $label ) : ?>
					<button type="button" data-pal-gallery-open="<?php echo esc_attr( $id ); ?>" style="display:inline-flex;align-items:center;padding:10px 20px;background:#fff;color:#071F3F;border:1px solid #C4CCD8;font-family:Montserrat,sans-serif;font-weight:700;font-size:0.85rem;border-radius:8px;cursor:pointer;"><?php echo esc_html( $label ); ?></button>
				<?php endif; ?>
			</div>
			<div style="display:flex;gap:14px;overflow-x:auto;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;padding-bottom:6px;">
				<?php foreach ( $photos as $p ) :
					$u = isset( $p['image']['url'] ) ? $p['image']['url'] : '';
					?>
					<div style="flex:0 0 calc((100% - <?php echo ( $cols - 1 ) * 14; ?>px) / <?php echo (int) $cols; ?>);aspect-ratio:1/1;border-radius:10px;overflow:hidden;background:<?php echo $u ? "url('" . esc_url( $u ) . "') center/cover no-repeat" : '#E8EDF5'; ?>;cursor:pointer;scroll-snap-align:start;" data-pal-gallery-open="<?php echo esc_attr( $id ); ?>"></div>
				<?php endforeach; ?>
			</div>
		</div>
		</section>

		<?php /* ---- Lightbox modal (hidden until opened via JS) ---- */ ?>
		<div class="pal-gallery-lightbox" id="<?php echo esc_attr( $id ); ?>" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( $s['title'] ); ?>">
			<div class="pal-gallery-lightbox__overlay" data-pal-gallery-close></div>
			<button type="button" class="pal-gallery-lightbox__close" data-pal-gallery-close aria-label="<?php esc_attr_e( 'Close', 'nashua-pal-core' ); ?>">
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M15 5L5 15M5 5l10 10" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
			</button>
			<div class="pal-gallery-lightbox__content">
				<h2 class="pal-gallery-lightbox__title"><?php echo esc_html( $s['title'] ); ?></h2>
				<div class="pal-gallery-lightbox__grid">
					<?php foreach ( $photos as $p ) :
						$u = isset( $p['image']['url'] ) ? $p['image']['url'] : '';
						if ( ! $u ) { continue; }
						?>
						<img src="<?php echo esc_url( $u ); ?>" alt="" loading="lazy" class="pal-gallery-lightbox__img" />
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}
}

/* ============================================================================
   Widget: PAL CTA Banner — "Together, We Build Stronger Futures" style
   ========================================================================= */

class NPAL_Widget_CTA_Banner extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_cta_banner'; }
	public function get_title()    { return __( 'PAL CTA Banner', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-call-to-action'; }
	public function get_keywords() { return array( 'pal', 'cta', 'banner', 'donate', 'volunteer', 'sponsor' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'CTA Banner', 'nashua-pal-core' ) ) );

		$this->add_control( 'icon', array(
			'label'   => __( 'Icon', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'hands' => __( 'Hands',     'nashua-pal-core' ),
				'heart' => __( 'Heart',     'nashua-pal-core' ),
				''      => __( 'No icon',   'nashua-pal-core' ),
			),
			'default' => 'hands',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Together, We Build Stronger Futures', 'nashua-pal-core' ),
		) );
		$this->add_control( 'tagline', array(
			'label'   => __( 'Tagline', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => __( 'Support Nashua PAL and help make a difference in the lives of local youth.', 'nashua-pal-core' ),
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'btn_label', array( 'label' => __( 'Label', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Donate' ) );
		$repeater->add_control( 'btn_url',   array( 'label' => __( 'URL',   'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '/donate/' ) );
		$repeater->add_control( 'btn_style', array(
			'label'   => __( 'Style', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'primary' => __( 'Primary (yellow filled)', 'nashua-pal-core' ),
				'outline' => __( 'Outline (transparent)',   'nashua-pal-core' ),
			),
			'default' => 'primary',
		) );

		$this->add_control( 'buttons', array(
			'label'       => __( 'Buttons', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(
				array( 'btn_label' => 'Donate Today',     'btn_url' => '/donate/',                  'btn_style' => 'primary' ),
				array( 'btn_label' => 'Volunteer',        'btn_url' => '/get-involved/volunteer/',  'btn_style' => 'outline' ),
				array( 'btn_label' => 'Become a Sponsor', 'btn_url' => '/get-involved/partner/',    'btn_style' => 'outline' ),
			),
			'title_field' => '{{{ btn_label }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s        = $this->get_settings_for_display();
		$icon_svg = $s['icon'] ? npal_widget_inline_icon( $s['icon'] ) : '';
		?>
		<section style="background:#071F3F;padding:40px;">
		<div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:1fr 1fr;gap:30px;align-items:center;">
			<div style="display:flex;align-items:center;gap:20px;">
				<?php if ( $icon_svg ) : ?><div style="flex-shrink:0;"><?php echo $icon_svg; ?></div><?php endif; ?>
				<div>
					<?php if ( $s['title'] ) : ?><h2 style="color:#fff;font-family:Montserrat,sans-serif;font-weight:800;margin:0 0 6px;font-size:clamp(1.3rem,2.4vw,1.9rem);line-height:1.15;"><?php echo esc_html( $s['title'] ); ?></h2><?php endif; ?>
					<?php if ( $s['tagline'] ) : ?><p style="color:#C4CCD8;font-size:0.95rem;margin:0;line-height:1.55;"><?php echo esc_html( $s['tagline'] ); ?></p><?php endif; ?>
				</div>
			</div>
			<div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;align-items:center;">
				<?php foreach ( (array) $s['buttons'] as $b ) :
					if ( 'primary' === $b['btn_style'] ) {
						$btn_css = 'background:#F4A51C;color:#071F3F;box-shadow:0 4px 10px rgba(244,165,28,0.3);';
					} else {
						$btn_css = 'background:transparent;color:#fff;border:1px solid rgba(255,255,255,0.5);';
					}
					?>
					<a href="<?php echo esc_url( $b['btn_url'] ); ?>" style="display:inline-flex;align-items:center;padding:13px 22px;<?php echo $btn_css; ?>font-family:Montserrat,sans-serif;font-weight:700;font-size:0.88rem;border-radius:8px;text-decoration:none;"><?php echo esc_html( $b['btn_label'] ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
		</section>
		<?php
	}
}
