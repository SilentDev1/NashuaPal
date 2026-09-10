<?php
/**
 * Nashua PAL Elementor widget — YouTube Video Carousel.
 *
 * Standalone widget for any page. Editors add multiple YouTube URLs
 * via the Elementor repeater; the front end renders them as a carousel
 * with auto-rotation (30 s), prev/next arrows, and dot indicators.
 *
 * @package NashuaPalCore
 * @since 1.39.5
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

class NPAL_Widget_Video_Carousel extends NPAL_Elementor_Widget_Base {

	protected $shortcode = '';

	public function get_name()     { return 'pal_video_carousel'; }
	public function get_title()    { return __( 'PAL YouTube Carousel', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-youtube'; }
	public function get_keywords() { return array( 'pal', 'youtube', 'video', 'carousel', 'rotate' ); }

	/* ----- Content controls ------------------------------------------------ */

	protected function register_content_controls() {
		$this->start_controls_section( 'pal_vyc_videos', array(
			'label' => __( 'Videos', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$repeater = new \Elementor\Repeater();

		$repeater->add_control( 'url', array(
			'label'       => __( 'YouTube URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'https://www.youtube.com/watch?v=…',
			'label_block' => true,
		) );

		$repeater->add_control( 'label', array(
			'label'       => __( 'Label (optional)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => __( 'e.g. Game highlights', 'nashua-pal-core' ),
			'label_block' => true,
		) );

		$this->add_control( 'videos', array(
			'label'       => __( 'YouTube Videos', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(
				array( 'url' => '', 'label' => '' ),
			),
			'title_field' => '{{{ label || url || "Video" }}}',
		) );

		$this->add_control( 'interval', array(
			'label'   => __( 'Auto-rotate interval (seconds)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 30,
			'min'     => 5,
			'max'     => 120,
			'step'    => 5,
		) );

		$this->add_control( 'aspect_ratio', array(
			'label'   => __( 'Aspect ratio', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '56.25',
			'options' => array(
				'56.25' => '16 : 9',
				'75'    => '4 : 3',
				'100'   => '1 : 1',
			),
		) );

		$this->end_controls_section();
	}

	/* ----- Style controls -------------------------------------------------- */

	protected function register_style_controls() {
		parent::register_style_controls();

		$this->start_controls_section( 'pal_vyc_style', array(
			'label' => __( 'Carousel Style', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'border_radius', array(
			'label'      => __( 'Video border radius', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 30, 'step' => 2 ) ),
			'default'    => array( 'size' => 8, 'unit' => 'px' ),
			'selectors'  => array(
				'{{WRAPPER}} .pal-vyc__wrap, {{WRAPPER}} .pal-vyc__wrap iframe' => 'border-radius: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'arrow_bg', array(
			'label'     => __( 'Arrow background', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#F4A51C',
			'selectors' => array( '{{WRAPPER}} .pal-vyc__arrow' => 'background: {{VALUE}};' ),
		) );

		$this->add_control( 'playlist_active_color', array(
			'label'     => __( 'Playlist active border', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#F4A51C',
			'selectors' => array(
				'{{WRAPPER}} .pal-vyc-playlist__item.is-active' => 'border-color: {{VALUE}};',
				'{{WRAPPER}} .pal-vyc-playlist__item:hover'     => 'border-color: {{VALUE}};',
			),
		) );

		$this->add_control( 'playlist_title_color', array(
			'label'     => __( 'Playlist title color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .pal-vyc-playlist__title' => 'color: {{VALUE}};' ),
		) );

		$this->end_controls_section();
	}

	/* ----- Render ---------------------------------------------------------- */

	protected function render() {
		$settings = $this->get_settings_for_display();
		$videos   = $settings['videos'] ?? array();
		$ratio    = $settings['aspect_ratio'] ?? '56.25';
		$interval = max( 5, (int) ( $settings['interval'] ?? 30 ) ) * 1000;

		// Filter to valid YouTube IDs.
		$items = array();
		foreach ( $videos as $v ) {
			$vid = self::extract_youtube_id( $v['url'] ?? '' );
			if ( $vid ) {
				$items[] = array( 'id' => $vid, 'label' => $v['label'] ?? '' );
			}
		}

		if ( empty( $items ) ) {
			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
				echo '<p class="pal-empty" style="color:#646970;font-size:13px;">'
					. esc_html__( 'PAL YouTube Carousel: add at least one YouTube URL.', 'nashua-pal-core' )
					. '</p>';
			}
			return;
		}

		$uid   = 'pal-vyc-' . $this->get_id();
		$count = count( $items );

		echo '<div class="pal-vyc" id="' . esc_attr( $uid ) . '">';
		foreach ( $items as $idx => $item ) {
			$hidden = $idx > 0 ? ' style="display:none;"' : '';
			echo '<div class="pal-vyc__slide" data-index="' . $idx . '"' . $hidden . '>';
			echo '<div class="pal-vyc__wrap" style="padding-bottom:' . esc_attr( $ratio ) . '%;">';
			echo '<iframe src="https://www.youtube-nocookie.com/embed/' . esc_attr( $item['id'] ) . '" '
				. 'frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
				. 'allowfullscreen></iframe>';
			echo '</div>';
			if ( $item['label'] ) {
				echo '<p class="pal-vyc__label">' . esc_html( $item['label'] ) . '</p>';
			}
			echo '</div>';
		}
		if ( $count > 1 ) {
			echo '<button type="button" class="pal-vyc__arrow pal-vyc__arrow--prev" aria-label="Previous video">&#10094;</button>';
			echo '<button type="button" class="pal-vyc__arrow pal-vyc__arrow--next" aria-label="Next video">&#10095;</button>';
		}
		echo '</div>'; // close .pal-vyc main player area

		// Playlist thumbnails below the main player.
		if ( $count > 1 ) {
			echo '<div class="pal-vyc-playlist-wrap">';
			echo '<button type="button" class="pal-vyc-playlist__arrow pal-vyc-playlist__arrow--prev" aria-label="Previous thumbnails">&#10094;</button>';
			echo '<div class="pal-vyc-playlist" id="' . esc_attr( $uid ) . '-pl">';
			foreach ( $items as $idx => $item ) {
				$active = 0 === $idx ? ' is-active' : '';
				$thumb  = 'https://img.youtube.com/vi/' . esc_attr( $item['id'] ) . '/mqdefault.jpg';
				$label  = $item['label'] ? esc_html( $item['label'] ) : esc_html__( 'Video', 'nashua-pal-core' ) . ' ' . ( $idx + 1 );
				echo '<button type="button" class="pal-vyc-playlist__item' . $active . '" data-index="' . $idx . '">';
				echo '<span class="pal-vyc-playlist__thumb-wrap">';
				echo '<img src="' . esc_url( $thumb ) . '" alt="" loading="lazy" />';
				echo '<span class="pal-vyc-playlist__play">&#9654;</span>';
				echo '</span>';
				echo '<span class="pal-vyc-playlist__title">' . $label . '</span>';
				echo '</button>';
			}
			echo '</div>';
			echo '<button type="button" class="pal-vyc-playlist__arrow pal-vyc-playlist__arrow--next" aria-label="Next thumbnails">&#10095;</button>';
			echo '</div>';

			?>
			<script>
			(function(){
				var w=document.getElementById('<?php echo esc_js( $uid ); ?>');if(!w)return;
				var pl=document.getElementById('<?php echo esc_js( $uid ); ?>-pl');
				var plWrap=pl.parentElement;
				var slides=w.querySelectorAll('.pal-vyc__slide'),
					thumbs=pl.querySelectorAll('.pal-vyc-playlist__item'),
					cur=0,total=slides.length,timer=null,
					INTERVAL=<?php echo (int) $interval; ?>,PAUSE=60000;
				function go(n){
					slides[cur].style.display='none';
					thumbs[cur].classList.remove('is-active');
					cur=(n+total)%total;
					slides[cur].style.display='';
					thumbs[cur].classList.add('is-active');
					var tRect=thumbs[cur].getBoundingClientRect(),pRect=pl.getBoundingClientRect();
					if(tRect.left<pRect.left||tRect.right>pRect.right){
						pl.scrollTo({left:thumbs[cur].offsetLeft-pl.offsetLeft-(pl.clientWidth/2)+(thumbs[cur].offsetWidth/2),behavior:'smooth'});
					}
				}
				function autoStart(){timer=setInterval(function(){go(cur+1);},INTERVAL);}
				function userNav(n){clearInterval(timer);go(n);setTimeout(autoStart,PAUSE);}
				w.querySelector('.pal-vyc__arrow--prev').addEventListener('click',function(){userNav(cur-1);});
				w.querySelector('.pal-vyc__arrow--next').addEventListener('click',function(){userNav(cur+1);});
				thumbs.forEach(function(t){t.addEventListener('click',function(){userNav(parseInt(t.dataset.index,10));});});
				/* Playlist strip arrows */
				var plPrev=plWrap.querySelector('.pal-vyc-playlist__arrow--prev');
				var plNext=plWrap.querySelector('.pal-vyc-playlist__arrow--next');
				var scrollAmt=200;
				plPrev.addEventListener('click',function(){pl.scrollBy({left:-scrollAmt,behavior:'smooth'});});
				plNext.addEventListener('click',function(){pl.scrollBy({left:scrollAmt,behavior:'smooth'});});
				autoStart();
			})();
			</script>
			<?php
		}
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
}
