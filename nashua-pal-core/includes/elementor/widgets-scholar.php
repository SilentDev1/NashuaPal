<?php
/**
 * Nashua PAL Elementor widgets — Scholarships.
 *
 * @package NashuaPalCore
 * @since   1.29.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ======================================================================
 *  Scholarship Hero
 * ==================================================================== */

class NPAL_Widget_Scholar_Hero extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_scholar_hero'; }
	public function get_title()    { return __( 'PAL Scholarship Hero', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-banner'; }
	public function get_keywords() { return array( 'pal', 'scholarship', 'hero', 'banner' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'hero_content', array(
			'label' => __( 'Hero Content', 'nashua-pal-core' ),
		) );

		$this->add_control( 'bg_image', array(
			'label'   => __( 'Background Image', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );
		$this->add_control( 'logo_image', array(
			'label'       => __( 'Logo Image', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::MEDIA,
			'default'     => array( 'url' => '' ),
			'description' => __( 'PAL crest/logo displayed on the left. Leave empty to hide.', 'nashua-pal-core' ),
		) );
		$this->add_responsive_control( 'hero_height', array(
			'label'      => __( 'Hero Height (px)', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 250, 'max' => 800, 'step' => 10 ) ),
			'default'    => array( 'size' => 420, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .pal-scholar-hero' => 'min-height: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'EDUCATION  ·  OPPORTUNITY  ·  FUTURE',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Nashua PAL Scholarships',
		) );
		$this->add_control( 'subtitle', array(
			'label'       => __( 'Subtitle (cursive)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => 'In Partnership with the Allard Family',
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Investing in the leaders of tomorrow. Our scholarships support students who have demonstrated academic excellence, community involvement, and leadership.",
		) );

		$btn_repeater = new \Elementor\Repeater();
		$btn_repeater->add_control( 'btn_label', array( 'label' => __( 'Label', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Button' ) );
		$btn_repeater->add_control( 'btn_url',   array( 'label' => __( 'URL', 'nashua-pal-core' ),   'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#' ) );
		$btn_repeater->add_control( 'btn_style', array(
			'label'   => __( 'Style', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'primary' => __( 'Primary (gold)', 'nashua-pal-core' ),
				'outline' => __( 'Outline (white)', 'nashua-pal-core' ),
			),
			'default' => 'primary',
		) );

		$this->add_control( 'buttons', array(
			'label'       => __( 'Buttons', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $btn_repeater->get_controls(),
			'default'     => array(
				array( 'btn_label' => 'Donate to Support Scholarships', 'btn_url' => '/donate/', 'btn_style' => 'primary' ),
				array( 'btn_label' => 'Learn More About Scholarships',  'btn_url' => '#about',   'btn_style' => 'outline' ),
			),
			'title_field' => '{{{ btn_label }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$bg_url = ! empty( $s['bg_image']['url'] ) ? $s['bg_image']['url'] : '';
		$has_bg = (bool) $bg_url;
		$bg_css = $has_bg ? "background-image:url('" . esc_url( $bg_url ) . "');" : '';
		$cls    = 'pal-scholar-hero' . ( $has_bg ? ' pal-scholar-hero--has-bg' : '' );
		$logo   = ! empty( $s['logo_image']['url'] ) ? $s['logo_image']['url'] : '';
		?>
		<section class="<?php echo esc_attr( $cls ); ?>" style="<?php echo esc_attr( $bg_css ); ?>">
			<div class="pal-scholar-hero__overlay"></div>
			<div class="pal-scholar-hero__container">
				<?php if ( $logo ) : ?>
					<img src="<?php echo esc_url( $logo ); ?>" alt="PAL" class="pal-scholar-hero__logo" />
				<?php endif; ?>
				<div class="pal-scholar-hero__content">
					<?php if ( $s['eyebrow'] ) : ?>
						<span class="pal-scholar-hero__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span>
					<?php endif; ?>
					<?php if ( $s['title'] ) : ?>
						<h1 class="pal-scholar-hero__title"><?php echo esc_html( $s['title'] ); ?></h1>
					<?php endif; ?>
					<?php if ( $s['subtitle'] ) : ?>
						<p class="pal-scholar-hero__subtitle"><?php echo esc_html( $s['subtitle'] ); ?></p>
					<?php endif; ?>
					<?php if ( $s['description'] ) : ?>
						<p class="pal-scholar-hero__desc"><?php echo esc_html( $s['description'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $s['buttons'] ) ) : ?>
						<div class="pal-scholar-hero__buttons">
							<?php foreach ( (array) $s['buttons'] as $btn ) :
								$btn_cls = 'primary' === $btn['btn_style'] ? 'pal-scholar-hero__btn--primary' : 'pal-scholar-hero__btn--outline';
								?>
								<a href="<?php echo esc_url( $btn['btn_url'] ); ?>" class="pal-scholar-hero__btn <?php echo esc_attr( $btn_cls ); ?>"><?php echo esc_html( $btn['btn_label'] ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Scholarship Values Strip
 * ==================================================================== */

class NPAL_Widget_Scholar_Values extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_scholar_values'; }
	public function get_title()    { return __( 'PAL Scholarship Values', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-icon-box'; }
	public function get_keywords() { return array( 'pal', 'scholarship', 'values', 'icons' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Values Strip', 'nashua-pal-core' ),
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'icon',  array( 'label' => __( 'Icon Class', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'fas fa-star' ) );
		$repeater->add_control( 'title', array( 'label' => __( 'Title', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Value' ) );

		$this->add_control( 'items', array(
			'label'       => __( 'Values', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(
				array( 'icon' => 'fas fa-graduation-cap', 'title' => 'Supporting Education' ),
				array( 'icon' => 'fas fa-award',          'title' => 'Building Leaders' ),
				array( 'icon' => 'fas fa-handshake',      'title' => 'Stronger Community' ),
				array( 'icon' => 'fas fa-shield-alt',     'title' => 'Powered by PAL' ),
			),
			'title_field' => '{{{ title }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = (array) $s['items'];
		if ( empty( $items ) ) { return; }
		?>
		<section class="pal-scholar-values">
			<div class="pal-scholar-values__inner">
				<?php foreach ( $items as $item ) : ?>
					<div class="pal-scholar-values__item">
						<?php if ( ! empty( $item['icon'] ) ) : ?>
							<div class="pal-scholar-values__icon"><i class="<?php echo esc_attr( $item['icon'] ); ?>"></i></div>
						<?php endif; ?>
						<span class="pal-scholar-values__title"><?php echo esc_html( $item['title'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Scholarship Info Block
 * ==================================================================== */

class NPAL_Widget_Scholar_Info extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_scholar_info'; }
	public function get_title()    { return __( 'PAL Scholarship Info', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-text'; }
	public function get_keywords() { return array( 'pal', 'scholarship', 'info', 'deadline' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Info Block', 'nashua-pal-core' ),
		) );

		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Nashua PAL is proud to award scholarships to outstanding students who demonstrate academic excellence, community involvement, and a commitment to the mission of PAL. These scholarships are made possible through the generosity of the Allard Family and community donors.",
		) );
		$this->add_control( 'deadline_text', array(
			'label'   => __( 'Deadline / Highlight Text', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'DEADLINE FOR APPLY: APRIL 1, 2026',
		) );
		$this->add_control( 'announcement_text', array(
			'label'   => __( 'Announcement Text', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'AWARD NOTIFICATION: JUNE 1, 2026 AT OUR NIGHT OF CHAMPIONS GALA',
		) );
		$this->add_control( 'btn_label', array(
			'label'   => __( 'Button Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Download Application',
		) );
		$this->add_control( 'btn_url', array(
			'label'   => __( 'Button URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '#',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="pal-scholar-info">
			<div class="pal-scholar-info__inner">
				<?php if ( $s['description'] ) : ?>
					<p class="pal-scholar-info__desc"><?php echo esc_html( $s['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( $s['deadline_text'] ) : ?>
					<p class="pal-scholar-info__highlight"><?php echo esc_html( $s['deadline_text'] ); ?></p>
				<?php endif; ?>
				<?php if ( $s['announcement_text'] ) : ?>
					<p class="pal-scholar-info__highlight"><?php echo esc_html( $s['announcement_text'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $s['btn_label'] ) && ! empty( $s['btn_url'] ) ) : ?>
					<a href="<?php echo esc_url( $s['btn_url'] ); ?>" class="pal-scholar-info__btn"><?php echo esc_html( $s['btn_label'] ); ?></a>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Scholarship Recipients (wraps [pal_scholarships])
 * ==================================================================== */

class NPAL_Widget_Scholar_Recipients extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'pal_scholarships';

	public function get_name()     { return 'pal_scholar_recipients'; }
	public function get_title()    { return __( 'PAL Scholarship Recipients', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-person'; }
	public function get_keywords() { return array( 'pal', 'scholarship', 'recipients', 'winners', 'grid' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Recipients', 'nashua-pal-core' ),
		) );

		$this->add_control( 'max_years', array(
			'label'   => __( 'Max Years to Show', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 10,
			'min'     => 1,
			'max'     => 30,
		) );
		$this->add_control( 'count_per_year', array(
			'label'   => __( 'Max Recipients per Year', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 20,
			'min'     => 1,
			'max'     => 100,
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'max_years'      => isset( $s['max_years'] )      ? (string) $s['max_years']      : '10',
			'count_per_year' => isset( $s['count_per_year'] ) ? (string) $s['count_per_year'] : '20',
		);
	}
}

/* ======================================================================
 *  Scholarship About Section
 * ==================================================================== */

class NPAL_Widget_Scholar_About extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_scholar_about'; }
	public function get_title()    { return __( 'PAL Scholarship About', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-circle'; }
	public function get_keywords() { return array( 'pal', 'scholarship', 'about', 'fund' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'About Section', 'nashua-pal-core' ),
		) );

		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'About the Nashua PAL Scholarship Fund',
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "The Nashua PAL Scholarship Fund was established to support graduating seniors who have shown exceptional commitment to academics, athletics, and community service.\n\nThrough the generous partnership with the Allard Family, we are able to provide financial support to deserving students as they pursue higher education and continue to make a positive impact in their communities.",
		) );
		$this->add_control( 'photo', array(
			'label'   => __( 'Photo', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$photo = ! empty( $s['photo']['url'] ) ? $s['photo']['url'] : '';
		?>
		<section class="pal-scholar-about" id="about">
			<div class="pal-scholar-about__inner">
				<div class="pal-scholar-about__text">
					<?php if ( $s['title'] ) : ?>
						<h2 class="pal-scholar-about__title"><i class="fas fa-graduation-cap"></i> <?php echo esc_html( $s['title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( $s['description'] ) : ?>
						<?php foreach ( explode( "\n", $s['description'] ) as $para ) :
							$para = trim( $para );
							if ( '' === $para ) { continue; }
							?>
							<p class="pal-scholar-about__desc"><?php echo esc_html( $para ); ?></p>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
				<?php if ( $photo ) : ?>
					<div class="pal-scholar-about__media">
						<img src="<?php echo esc_url( $photo ); ?>" alt="" class="pal-scholar-about__photo" loading="lazy" />
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Scholarship CTA Bar
 * ==================================================================== */

class NPAL_Widget_Scholar_CTA extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_scholar_cta'; }
	public function get_title()    { return __( 'PAL Scholarship CTA', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-call-to-action'; }
	public function get_keywords() { return array( 'pal', 'scholarship', 'cta', 'donate' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'CTA Bar', 'nashua-pal-core' ),
		) );

		$this->add_control( 'icon', array(
			'label'   => __( 'Icon Class', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'fas fa-heart',
		) );
		$this->add_control( 'tagline', array(
			'label'   => __( 'Tagline', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => "Help us continue investing in the future of Nashua\xe2\x80\x99s youth.",
		) );
		$this->add_control( 'btn_label', array(
			'label'   => __( 'Button Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Donate to Scholarships',
		) );
		$this->add_control( 'btn_url', array(
			'label'   => __( 'Button URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '/donate/',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="pal-scholar-cta">
			<div class="pal-scholar-cta__inner">
				<?php if ( $s['icon'] ) : ?>
					<div class="pal-scholar-cta__icon"><i class="<?php echo esc_attr( $s['icon'] ); ?>"></i></div>
				<?php endif; ?>
				<?php if ( $s['tagline'] ) : ?>
					<p class="pal-scholar-cta__tagline"><?php echo esc_html( $s['tagline'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $s['btn_label'] ) && ! empty( $s['btn_url'] ) ) : ?>
					<a href="<?php echo esc_url( $s['btn_url'] ); ?>" class="pal-scholar-cta__btn"><?php echo esc_html( $s['btn_label'] ); ?></a>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Scholarship Featured Section (Homepage promo card)
 * ==================================================================== */

class NPAL_Widget_Scholar_Featured extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_scholar_featured'; }
	public function get_title()    { return __( 'PAL Scholarship Featured', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-featured-image'; }
	public function get_keywords() { return array( 'pal', 'scholarship', 'featured', 'homepage', 'promo' ); }

	protected function register_content_controls() {
		/* ----- Image Panel ----- */
		$this->start_controls_section( 'image_section', array(
			'label' => __( 'Image', 'nashua-pal-core' ),
		) );
		$this->add_control( 'photo', array(
			'label'   => __( 'Photo', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );
		$this->add_control( 'icon', array(
			'label'       => __( 'Overlay Icon Class', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Uses Site Settings value', 'nashua-pal-core' ),
		) );
		$this->end_controls_section();

		/* ----- Content Panel ----- */
		$this->start_controls_section( 'content_section', array(
			'label' => __( 'Content', 'nashua-pal-core' ),
		) );
		$this->add_control( 'eyebrow', array(
			'label'       => __( 'Eyebrow', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Uses Site Settings value', 'nashua-pal-core' ),
		) );
		$this->add_control( 'title', array(
			'label'       => __( 'Title', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Uses Site Settings value', 'nashua-pal-core' ),
		) );
		$this->add_control( 'subtitle', array(
			'label'       => __( 'Subtitle (cursive)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Uses Site Settings value', 'nashua-pal-core' ),
		) );
		$this->add_control( 'highlight', array(
			'label'       => __( 'Highlight Text (bold)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Uses Site Settings value', 'nashua-pal-core' ),
			'description' => __( 'Bold line shown above the description.', 'nashua-pal-core' ),
		) );
		$this->add_control( 'description', array(
			'label'       => __( 'Description', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXTAREA,
			'default'     => '',
			'placeholder' => __( 'Uses Site Settings value', 'nashua-pal-core' ),
		) );
		$this->end_controls_section();

		/* ----- Info Items Panel ----- */
		$this->start_controls_section( 'info_section', array(
			'label' => __( 'Info Items', 'nashua-pal-core' ),
			'description' => __( 'Leave empty to use Site Settings values.', 'nashua-pal-core' ),
		) );
		$info_repeater = new \Elementor\Repeater();
		$info_repeater->add_control( 'info_icon',  array( 'label' => __( 'Icon Class', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$info_repeater->add_control( 'info_label', array( 'label' => __( 'Label', 'nashua-pal-core' ),     'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$info_repeater->add_control( 'info_value', array( 'label' => __( 'Value', 'nashua-pal-core' ),     'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'info_items', array(
			'label'       => __( 'Info Items', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $info_repeater->get_controls(),
			'default'     => array(),
			'title_field' => '{{{ info_label }}} {{{ info_value }}}',
		) );
		$this->end_controls_section();

		/* ----- Buttons Panel ----- */
		$this->start_controls_section( 'buttons_section', array(
			'label' => __( 'Buttons', 'nashua-pal-core' ),
		) );
		$this->add_control( 'btn_primary_label', array(
			'label'       => __( 'Primary Button Label', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Uses Site Settings value', 'nashua-pal-core' ),
		) );
		$this->add_control( 'btn_primary_url', array(
			'label'       => __( 'Primary Button URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Uses Site Settings value', 'nashua-pal-core' ),
		) );
		$this->add_control( 'btn_secondary_label', array(
			'label'       => __( 'Secondary Button Label', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Uses Site Settings value', 'nashua-pal-core' ),
		) );
		$this->add_control( 'btn_secondary_url', array(
			'label'       => __( 'Secondary Button URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Uses Site Settings value', 'nashua-pal-core' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		// Widget controls override Site Settings; Site Settings is the fallback.
		$photo      = ! empty( $s['photo']['url'] )      ? $s['photo']['url']      : NPAL_Plugin::get_setting( 'scholar_photo' );
		$icon       = ! empty( $s['icon'] )              ? $s['icon']              : NPAL_Plugin::get_setting( 'scholar_icon' );
		$eyebrow    = ! empty( $s['eyebrow'] )           ? $s['eyebrow']           : NPAL_Plugin::get_setting( 'scholar_eyebrow' );
		$title      = ! empty( $s['title'] )             ? $s['title']             : NPAL_Plugin::get_setting( 'scholar_title' );
		$subtitle   = ! empty( $s['subtitle'] )          ? $s['subtitle']          : NPAL_Plugin::get_setting( 'scholar_subtitle' );
		$highlight  = ! empty( $s['highlight'] )         ? $s['highlight']         : NPAL_Plugin::get_setting( 'scholar_highlight' );
		$desc       = ! empty( $s['description'] )       ? $s['description']       : NPAL_Plugin::get_setting( 'scholar_description' );
		$btn1_label = ! empty( $s['btn_primary_label'] ) ? $s['btn_primary_label'] : NPAL_Plugin::get_setting( 'scholar_btn_primary_label' );
		$btn1_url   = ! empty( $s['btn_primary_url'] )   ? $s['btn_primary_url']   : NPAL_Plugin::get_setting( 'scholar_btn_primary_url' );
		$btn2_label = ! empty( $s['btn_secondary_label'] ) ? $s['btn_secondary_label'] : NPAL_Plugin::get_setting( 'scholar_btn_secondary_label' );
		$btn2_url   = ! empty( $s['btn_secondary_url'] ) ? $s['btn_secondary_url'] : NPAL_Plugin::get_setting( 'scholar_btn_secondary_url' );

		// Info items — widget repeater is primary; Site Settings is fallback.
		$items = ! empty( $s['info_items'] ) ? (array) $s['info_items'] : array();
		if ( empty( $items ) ) {
			for ( $i = 1; $i <= 4; $i++ ) {
				$il = NPAL_Plugin::get_setting( "scholar_info_{$i}_label" );
				$iv = NPAL_Plugin::get_setting( "scholar_info_{$i}_value" );
				if ( $il || $iv ) {
					$items[] = array(
						'info_icon'  => NPAL_Plugin::get_setting( "scholar_info_{$i}_icon" ),
						'info_label' => $il,
						'info_value' => $iv,
					);
				}
			}
		}
		?>
		<section class="pal-scholar-featured">
			<div class="pal-scholar-featured__inner">
				<div class="pal-scholar-featured__media">
					<?php if ( $photo ) : ?>
						<img src="<?php echo esc_url( $photo ); ?>" alt="" class="pal-scholar-featured__photo" loading="lazy" />
					<?php endif; ?>
				</div>
				<?php if ( $icon ) : ?>
					<div class="pal-scholar-featured__icon">
						<i class="<?php echo esc_attr( $icon ); ?>"></i>
					</div>
				<?php endif; ?>
				<div class="pal-scholar-featured__content">
					<?php if ( $eyebrow ) : ?>
						<span class="pal-scholar-featured__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
					<?php endif; ?>
					<?php if ( $title ) : ?>
						<h2 class="pal-scholar-featured__title"><?php echo esc_html( $title ); ?>
							<?php if ( $subtitle ) : ?>
								<span class="pal-scholar-featured__subtitle"><?php echo esc_html( $subtitle ); ?></span>
							<?php endif; ?>
						</h2>
					<?php endif; ?>
					<?php if ( $highlight ) : ?>
						<p class="pal-scholar-featured__highlight"><?php echo esc_html( $highlight ); ?></p>
					<?php endif; ?>
					<?php if ( $desc ) : ?>
						<p class="pal-scholar-featured__desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $items ) ) : ?>
						<div class="pal-scholar-featured__info">
							<?php foreach ( $items as $item ) : ?>
								<div class="pal-scholar-featured__info-item">
									<?php if ( ! empty( $item['info_icon'] ) ) : ?>
										<div class="pal-scholar-featured__info-icon"><i class="<?php echo esc_attr( $item['info_icon'] ); ?>"></i></div>
									<?php endif; ?>
									<div class="pal-scholar-featured__info-text">
										<?php if ( ! empty( $item['info_label'] ) ) : ?>
											<span class="pal-scholar-featured__info-label"><?php echo esc_html( $item['info_label'] ); ?></span>
										<?php endif; ?>
										<?php if ( ! empty( $item['info_value'] ) ) : ?>
											<span class="pal-scholar-featured__info-value"><?php echo esc_html( $item['info_value'] ); ?></span>
										<?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<div class="pal-scholar-featured__buttons">
						<?php if ( $btn1_label && $btn1_url ) : ?>
							<a href="<?php echo esc_url( $btn1_url ); ?>" class="pal-scholar-featured__btn pal-scholar-featured__btn--primary">
								<?php echo esc_html( $btn1_label ); ?> <i class="fas fa-arrow-right"></i>
							</a>
						<?php endif; ?>
						<?php if ( $btn2_label && $btn2_url ) : ?>
							<a href="<?php echo esc_url( $btn2_url ); ?>" class="pal-scholar-featured__btn pal-scholar-featured__btn--outline">
								<?php echo esc_html( $btn2_label ); ?> <i class="fas fa-arrow-right"></i>
							</a>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</section>
		<?php
	}
}
