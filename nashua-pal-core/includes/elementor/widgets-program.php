<?php
/**
 * Nashua PAL Elementor widget — Program Featured Image.
 *
 * Dedicated widget for displaying a program's featured image on single
 * program pages. Replaces the old shortcode-based image rendering so
 * editors can control aspect ratio, object-fit, max height, and border
 * radius directly from the Elementor panel.
 *
 * @package NashuaPalCore
 * @since 1.8.80
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

class NPAL_Widget_Program_Featured_Image extends NPAL_Elementor_Widget_Base {

	/* Not shortcode-backed — we override render() directly. */
	protected $shortcode = '';

	public function get_name()     { return 'pal_program_featured_image'; }
	public function get_title()    { return __( 'PAL Program Featured Image', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-image'; }
	public function get_keywords() { return array( 'pal', 'program', 'image', 'featured', 'hero' ); }

	/* ----- Controls ---------------------------------------------------- */

	protected function register_content_controls() {
		$this->start_controls_section( 'pal_pfi_content', array(
			'label' => __( 'Image', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'image_size', array(
			'label'   => __( 'Image size', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'large',
			'options' => array(
				'thumbnail'    => __( 'Thumbnail', 'nashua-pal-core' ),
				'medium'       => __( 'Medium', 'nashua-pal-core' ),
				'medium_large' => __( 'Medium Large', 'nashua-pal-core' ),
				'large'        => __( 'Large', 'nashua-pal-core' ),
				'full'         => __( 'Full', 'nashua-pal-core' ),
			),
		) );

		$this->add_control( 'aspect_ratio', array(
			'label'   => __( 'Aspect ratio', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'auto',
			'options' => array(
				'auto' => __( 'Auto (original)', 'nashua-pal-core' ),
				'16/9' => '16 : 9',
				'4/3'  => '4 : 3',
				'3/2'  => '3 : 2',
				'16/8' => '16 : 8',
				'1/1'  => '1 : 1',
			),
			'selectors' => array(
				'{{WRAPPER}} .pal-program-featured-img__img' => 'aspect-ratio: {{VALUE}};',
			),
		) );

		$this->add_control( 'object_fit', array(
			'label'   => __( 'Object fit', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'contain',
			'options' => array(
				'contain' => __( 'Contain (show full image)', 'nashua-pal-core' ),
				'cover'   => __( 'Cover (fill, may crop)', 'nashua-pal-core' ),
				'fill'    => __( 'Fill (stretch)', 'nashua-pal-core' ),
				'none'    => __( 'None (original size)', 'nashua-pal-core' ),
			),
			'selectors' => array(
				'{{WRAPPER}} .pal-program-featured-img__img' => 'object-fit: {{VALUE}};',
			),
		) );

		$this->add_responsive_control( 'max_height', array(
			'label'      => __( 'Max height', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px', 'vh' ),
			'range'      => array(
				'px' => array( 'min' => 100, 'max' => 900, 'step' => 10 ),
				'vh' => array( 'min' => 10,  'max' => 100, 'step' => 5 ),
			),
			'selectors'  => array(
				'{{WRAPPER}} .pal-program-featured-img__img' => 'max-height: {{SIZE}}{{UNIT}};',
			),
		) );

		$this->add_control( 'show_icon', array(
			'label'        => __( 'Show program icon overlay', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => __( 'Yes', 'nashua-pal-core' ),
			'label_off'    => __( 'No', 'nashua-pal-core' ),
			'return_value' => 'yes',
			'default'      => 'yes',
		) );

		$this->end_controls_section();
	}

	protected function register_style_controls() {
		parent::register_style_controls();

		$this->start_controls_section( 'pal_pfi_style', array(
			'label' => __( 'Image Style', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'img_border_radius', array(
			'label'      => __( 'Border radius', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px', '%' ),
			'selectors'  => array(
				'{{WRAPPER}} .pal-program-featured-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
			),
		) );

		$this->end_controls_section();
	}

	/* ----- Render ------------------------------------------------------ */

	protected function render() {
		$post_id = $this->resolve_program_id();
		if ( ! $post_id ) {
			if ( current_user_can( 'edit_posts' ) ) {
				echo '<p class="pal-empty" style="color:#646970;font-size:13px;">'
					. esc_html__( 'PAL Program Featured Image: place this widget inside a Program single template.', 'nashua-pal-core' )
					. '</p>';
			}
			return;
		}

		// YouTube video(s) replace the featured image on the single view.
		$yt_videos = get_post_meta( $post_id, '_pal_program_youtube_urls', true );
		if ( ! is_array( $yt_videos ) ) { $yt_videos = array(); }
		// Backward compat: migrate old singular key.
		if ( empty( $yt_videos ) ) {
			$old_yt = get_post_meta( $post_id, '_pal_program_youtube_url', true );
			if ( $old_yt ) {
				$yt_videos = array( array( 'url' => $old_yt, 'label' => '' ) );
			}
		}
		// Filter to valid IDs.
		$yt_items = array();
		foreach ( $yt_videos as $v ) {
			$vid = self::extract_youtube_id( $v['url'] ?? '' );
			if ( $vid ) {
				$yt_items[] = array( 'id' => $vid, 'label' => $v['label'] ?? '' );
			}
		}
		if ( ! empty( $yt_items ) ) {
			$uid   = 'pal-yt-' . wp_unique_id();
			$count = count( $yt_items );
			$title = esc_attr( get_the_title( $post_id ) );
			echo '<div class="pal-program-featured-img pal-program-featured-img--video pal-yt-carousel" id="' . esc_attr( $uid ) . '" data-count="' . $count . '">';
			foreach ( $yt_items as $idx => $item ) {
				$hidden = $idx > 0 ? ' style="display:none;"' : '';
				echo '<div class="pal-yt-carousel__slide" data-index="' . $idx . '"' . $hidden . '>';
				echo '<div class="pal-program-featured-img__video-wrap">';
				echo '<iframe src="https://www.youtube-nocookie.com/embed/' . esc_attr( $item['id'] ) . '" '
					. 'frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" '
					. 'allowfullscreen title="' . $title . '"></iframe>';
				echo '</div>';
				if ( $item['label'] ) {
					echo '<p class="pal-yt-carousel__label">' . esc_html( $item['label'] ) . '</p>';
				}
				echo '</div>';
			}
			if ( $count > 1 ) {
				echo '<button type="button" class="pal-yt-carousel__arrow pal-yt-carousel__arrow--prev" aria-label="Previous video">&#10094;</button>';
				echo '<button type="button" class="pal-yt-carousel__arrow pal-yt-carousel__arrow--next" aria-label="Next video">&#10095;</button>';
				echo '<div class="pal-yt-carousel__dots">';
				for ( $d = 0; $d < $count; $d++ ) {
					echo '<button type="button" class="pal-yt-carousel__dot' . ( 0 === $d ? ' is-active' : '' ) . '" data-index="' . $d . '" aria-label="Video ' . ( $d + 1 ) . '"></button>';
				}
				echo '</div>';
			}
			echo '</div>';
			?>
			<script>
			(function(){
				var w=document.getElementById('<?php echo esc_js( $uid ); ?>');if(!w)return;
				<?php if ( $count > 1 ) : ?>
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
				<?php endif; ?>
				// Widen this column from default 40% to 50% for better video display.
				var col=w.closest('.elementor-column,.elementor-widget-wrap,.e-con');
				if(col&&col.classList.contains('elementor-column')){
					col.style.width='50%';col.style.flex='0 0 50%';col.style.maxWidth='50%';
					var sib=col.nextElementSibling||col.previousElementSibling;
					if(sib&&sib.classList.contains('elementor-column')){
						sib.style.width='50%';sib.style.flex='0 0 50%';sib.style.maxWidth='50%';
					}
				}
				var sec=w.closest('.elementor-section,.e-con');
				if(sec)sec.classList.add('has-pal-video');
			})();
			</script>
			<?php
			return;
		}

		$size  = $this->get_settings_for_display( 'image_size' ) ?: 'large';
		$thumb = get_the_post_thumbnail_url( $post_id, $size );
		if ( ! $thumb ) {
			$thumb = get_post_meta( $post_id, '_thumbnail_external_url', true );
			$thumb = $thumb ? esc_url_raw( $thumb ) : '';
		}
		if ( ! $thumb ) { return; }

		$show_icon = 'yes' === $this->get_settings_for_display( 'show_icon' );
		$icon      = $show_icon ? get_post_meta( $post_id, '_pal_program_icon', true ) : '';
		$alt       = esc_attr( get_the_title( $post_id ) );

		echo '<div class="pal-program-featured-img">';
		echo '<img class="pal-program-featured-img__img" src="' . esc_url( $thumb ) . '" alt="' . $alt . '" loading="eager" />';
		if ( $icon && class_exists( 'NPAL_Icons' ) ) {
			echo '<span class="pal-program-featured-img__icon">' . NPAL_Icons::render_or_fa( $icon ) . '</span>';
		}
		echo '</div>';
	}

	/**
	 * Extract YouTube video ID from various URL formats.
	 */
	private static function extract_youtube_id( $url ) {
		$patterns = array(
			'/(?:youtube\.com\/watch\?v=|youtube\.com\/embed\/|youtu\.be\/|youtube\.com\/v\/)([a-zA-Z0-9_-]{11})/',
		);
		foreach ( $patterns as $pattern ) {
			if ( preg_match( $pattern, $url, $m ) ) {
				return $m[1];
			}
		}
		return '';
	}

	/* ----- Helpers ----------------------------------------------------- */

	private function resolve_program_id() {
		$qid  = (int) get_queried_object_id();
		$qobj = get_queried_object();
		if ( $qid && $qobj instanceof \WP_Post && NPAL_CPT_Program::POST_TYPE === $qobj->post_type ) {
			return $qid;
		}
		if ( is_singular( NPAL_CPT_Program::POST_TYPE ) ) {
			return get_the_ID();
		}
		return 0;
	}
}

/**
 * Program Info Cards — dedicated Elementor widget.
 *
 * Reads _pal_program_additional_info meta directly and renders the
 * cards grid with full Elementor style controls. Drop into its own
 * full-width Elementor section on the single program template.
 *
 * @since 1.21.36
 */
class NPAL_Widget_Program_Info_Cards extends NPAL_Elementor_Widget_Base {

	protected $shortcode = '';

	public function get_name()     { return 'pal_program_info_cards'; }
	public function get_title()    { return __( 'PAL Program Info Cards', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-box'; }
	public function get_keywords() { return array( 'pal', 'program', 'info', 'cards', 'additional' ); }

	/* ----- Controls ---------------------------------------------------- */

	protected function register_content_controls() {
		$this->start_controls_section( 'pal_pic_content', array(
			'label' => __( 'Info Cards', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '3',
			'options' => array(
				'2' => '2',
				'3' => '3',
				'4' => '4',
			),
			'selectors' => array(
				'{{WRAPPER}} .pal-info-cards__grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
			),
		) );

		$this->add_control( 'hide_empty_note', array(
			'type' => \Elementor\Controls_Manager::RAW_HTML,
			'raw'  => '<p style="color:#646970;font-size:12px;">' . esc_html__( 'Cards are populated from the "Program Additional Info" meta box on each program. This widget auto-hides when no cards exist.', 'nashua-pal-core' ) . '</p>',
		) );

		$this->end_controls_section();
	}

	protected function register_style_controls() {
		parent::register_style_controls();

		$this->start_controls_section( 'pal_pic_card_style', array(
			'label' => __( 'Card Style', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'card_bg', array(
			'label'     => __( 'Card background', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .pal-info-card' => 'background-color: {{VALUE}};' ),
		) );

		$this->add_control( 'icon_bg', array(
			'label'     => __( 'Icon circle background', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .pal-info-card__icon' => 'background-color: {{VALUE}};' ),
		) );

		$this->add_control( 'icon_color', array(
			'label'     => __( 'Icon color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array(
				'{{WRAPPER}} .pal-info-card__icon svg' => 'stroke: {{VALUE}} !important;',
				'{{WRAPPER}} .pal-info-card__icon i'   => 'color: {{VALUE}};',
			),
		) );

		$this->add_control( 'heading_color', array(
			'label'     => __( 'Section heading color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .pal-info-cards__heading' => 'color: {{VALUE}};' ),
		) );

		$this->add_control( 'btn_bg', array(
			'label'     => __( 'Button background', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .pal-info-card__btn' => 'background-color: {{VALUE}};' ),
		) );

		$this->end_controls_section();
	}

	/* ----- Render ------------------------------------------------------ */

	protected function render() {
		$post_id = $this->resolve_program_id();
		if ( ! $post_id ) {
			if ( current_user_can( 'edit_posts' ) ) {
				echo '<p class="pal-empty" style="color:#646970;font-size:13px;">'
					. esc_html__( 'PAL Program Info Cards: place this widget inside a Program single template.', 'nashua-pal-core' )
					. '</p>';
			}
			return;
		}

		$data = get_post_meta( $post_id, '_pal_program_additional_info', true );
		if ( ! is_array( $data ) ) { return; }

		$heading = isset( $data['heading'] ) ? $data['heading'] : '';
		$cards   = isset( $data['cards'] ) && is_array( $data['cards'] ) ? $data['cards'] : array();
		$cards   = array_filter( $cards, function( $c ) { return ! empty( $c['title'] ); } );
		if ( empty( $cards ) ) { return; }

		echo '<div class="pal-info-cards">';
		if ( $heading ) {
			echo '<h3 class="pal-info-cards__heading">' . esc_html( $heading ) . '</h3>';
		}
		$uid = 'pal-ic-' . $this->get_id();
		echo '<div class="pal-info-cards__grid" id="' . esc_attr( $uid ) . '">';
		foreach ( $cards as $card ) {
			echo '<div class="pal-info-card">';
			if ( ! empty( $card['icon'] ) && class_exists( 'NPAL_Icons' ) ) {
				echo '<div class="pal-info-card__icon">' . NPAL_Icons::render_or_fa( $card['icon'] ) . '</div>';
			}
			echo '<h4 class="pal-info-card__title">' . esc_html( $card['title'] ) . '</h4>';
			if ( ! empty( $card['desc'] ) ) {
				echo '<div class="pal-info-card__desc"><div class="pal-info-card__desc-text">' . wp_kses_post( wpautop( $card['desc'] ) ) . '</div></div>';
			}
			$btn_label = ! empty( $card['btn_label'] ) ? $card['btn_label'] : __( 'Learn More', 'nashua-pal-core' );
			if ( ! empty( $card['btn_url'] ) ) {
				echo '<a class="pal-info-card__btn" href="' . esc_url( $card['btn_url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $btn_label ) . ' &rarr;</a>';
			}
			echo '</div>';
		}
		echo '</div>';
		echo '</div>';

		// JS: add "Read more / Read less" toggle for descriptions that overflow.
		?>
		<script>
		(function(){
			var wrap = document.getElementById('<?php echo esc_js( $uid ); ?>');
			if (!wrap) return;
			wrap.querySelectorAll('.pal-info-card__desc-text').forEach(function(el){
				// Wait for layout to calculate scroll height.
				requestAnimationFrame(function(){
					if (el.scrollHeight <= el.offsetHeight + 2) return;
					var btn = document.createElement('button');
					btn.className = 'pal-info-card__desc-toggle';
					btn.type = 'button';
					btn.textContent = 'Read more';
					btn.addEventListener('click', function(){
						var expanded = el.classList.toggle('is-expanded');
						btn.textContent = expanded ? 'Read less' : 'Read more';
					});
					el.parentNode.appendChild(btn);
				});
			});
		})();
		</script>
		<?php
	}

	/* ----- Helpers ----------------------------------------------------- */

	private function resolve_program_id() {
		$qid  = (int) get_queried_object_id();
		$qobj = get_queried_object();
		if ( $qid && $qobj instanceof \WP_Post && NPAL_CPT_Program::POST_TYPE === $qobj->post_type ) {
			return $qid;
		}
		if ( is_singular( NPAL_CPT_Program::POST_TYPE ) ) {
			return get_the_ID();
		}
		return 0;
	}
}
