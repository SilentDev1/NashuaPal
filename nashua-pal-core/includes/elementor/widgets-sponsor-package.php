<?php
/**
 * Nashua PAL Elementor widgets — Sponsorship Packages.
 *
 * @package NashuaPalCore
 * @since   1.30.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ======================================================================
 *  Sponsorship Packages — Hero Section with Stats Bar
 * ==================================================================== */

class NPAL_Widget_Spon_Pkg_Hero extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_spon_pkg_hero'; }
	public function get_title()    { return __( 'PAL Sponsorship Hero', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-banner'; }
	public function get_keywords() { return array( 'pal', 'sponsorship', 'packages', 'hero' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'hero_content', array(
			'label' => __( 'Hero Content', 'nashua-pal-core' ),
		) );

		$this->add_control( 'bg_image', array(
			'label'   => __( 'Background Image', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );
		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'NASHUA POLICE ATHLETIC LEAGUE',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Sponsorship Packages',
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Partner with Nashua PAL and make a lasting impact on our community\'s youth.',
		) );

		$btn_repeater = new \Elementor\Repeater();
		$btn_repeater->add_control( 'label', array( 'label' => __( 'Label', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Button' ) );
		$btn_repeater->add_control( 'url',   array( 'label' => __( 'URL', 'nashua-pal-core' ),   'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#' ) );
		$btn_repeater->add_control( 'style', array(
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
				array( 'label' => 'Contact Us',    'url' => '/contact/',  'style' => 'primary' ),
				array( 'label' => 'View Packages', 'url' => '#packages', 'style' => 'outline' ),
			),
			'title_field' => '{{{ label }}}',
		) );

		$this->end_controls_section();

		/* -- Stats Bar ---------------------------------------------------- */
		$this->start_controls_section( 'stats_content', array(
			'label' => __( 'Stats Bar', 'nashua-pal-core' ),
		) );

		$this->add_control( 'show_stats', array(
			'label'        => __( 'Show Stats Bar', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'default'      => 'yes',
			'return_value' => 'yes',
		) );

		$stat_repeater = new \Elementor\Repeater();
		$stat_repeater->add_control( 'number',     array( 'label' => __( 'Number', 'nashua-pal-core' ),     'type' => \Elementor\Controls_Manager::TEXT, 'default' => '100+' ) );
		$stat_repeater->add_control( 'label',      array( 'label' => __( 'Label', 'nashua-pal-core' ),      'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Stat Label' ) );
		$stat_repeater->add_control( 'icon_class', array( 'label' => __( 'Icon Class', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'fas fa-star' ) );

		$this->add_control( 'stats', array(
			'label'       => __( 'Stats', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $stat_repeater->get_controls(),
			'default'     => array(
				array( 'number' => '5,000+', 'label' => 'Youth Served',  'icon_class' => 'fas fa-child' ),
				array( 'number' => '30+',    'label' => 'Programs',      'icon_class' => 'fas fa-running' ),
				array( 'number' => '50+',    'label' => 'Events',        'icon_class' => 'fas fa-calendar-alt' ),
			),
			'title_field' => '{{{ label }}}',
			'condition'   => array( 'show_stats' => 'yes' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s       = $this->get_settings_for_display();
		$bg_url  = ! empty( $s['bg_image']['url'] ) ? $s['bg_image']['url'] : '';
		$show_st = 'yes' === $s['show_stats'];
		?>
		<section class="pal-pkg-hero"<?php if ( $bg_url ) : ?> style="background-image:url('<?php echo esc_url( $bg_url ); ?>');"<?php endif; ?>>
			<div class="pal-pkg-hero__overlay"></div>
			<div class="pal-pkg-hero__container">
				<div class="pal-pkg-hero__content">
					<?php if ( $s['eyebrow'] ) : ?>
						<span class="pal-pkg-hero__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span>
					<?php endif; ?>
					<h1 class="pal-pkg-hero__title"><?php echo esc_html( $s['title'] ); ?></h1>
					<?php if ( $s['description'] ) : ?>
						<p class="pal-pkg-hero__desc"><?php echo esc_html( $s['description'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $s['buttons'] ) ) : ?>
						<div class="pal-pkg-hero__buttons">
							<?php foreach ( (array) $s['buttons'] as $btn ) :
								$cls = 'primary' === ( isset( $btn['style'] ) ? $btn['style'] : '' ) ? 'pal-pkg-hero__btn--primary' : 'pal-pkg-hero__btn--outline';
								$btn_label = isset( $btn['label'] ) ? $btn['label'] : ( isset( $btn['btn_label'] ) ? $btn['btn_label'] : '' );
								$btn_url   = isset( $btn['url'] )   ? $btn['url']   : ( isset( $btn['btn_url'] )   ? $btn['btn_url']   : '#' );
								?>
								<a href="<?php echo esc_url( $btn_url ); ?>" class="pal-pkg-hero__btn <?php echo esc_attr( $cls ); ?>"><?php echo esc_html( $btn_label ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php if ( $show_st && ! empty( $s['stats'] ) ) : ?>
		<section class="pal-pkg-stats-section">
			<div class="pal-pkg-stats-card">
				<?php foreach ( (array) $s['stats'] as $i => $stat ) : ?>
					<?php if ( $i > 0 ) : ?><div class="pal-pkg-stats-card__divider"></div><?php endif; ?>
					<div class="pal-pkg-stats-card__item">
						<?php
						$icon = isset( $stat['icon_class'] ) ? $stat['icon_class'] : ( isset( $stat['stat_icon'] ) ? $stat['stat_icon'] : '' );
						$num  = isset( $stat['number'] )     ? $stat['number']     : ( isset( $stat['stat_number'] ) ? $stat['stat_number'] : '' );
						$lbl  = isset( $stat['label'] )      ? $stat['label']      : ( isset( $stat['stat_label'] )  ? $stat['stat_label']  : '' );
						?>
						<?php if ( $icon ) : ?>
							<div class="pal-pkg-stats-card__icon"><i class="<?php echo esc_attr( $icon ); ?>"></i></div>
						<?php endif; ?>
						<div class="pal-pkg-stats-card__text">
							<span class="pal-pkg-stats-card__number"><?php echo esc_html( $num ); ?></span>
							<span class="pal-pkg-stats-card__label"><?php echo esc_html( $lbl ); ?></span>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php endif; ?>
		<?php
	}
}

/* ======================================================================
 *  Sponsorship Packages — About Section
 * ==================================================================== */

class NPAL_Widget_Spon_Pkg_About extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_spon_pkg_about'; }
	public function get_title()    { return __( 'PAL Sponsorship About', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-circle'; }
	public function get_keywords() { return array( 'pal', 'sponsorship', 'about', 'mission' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'About Section', 'nashua-pal-core' ),
		) );

		$this->add_control( 'photo', array(
			'label'   => __( 'Photo', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );
		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'OUR MISSION',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Why Sponsor Nashua PAL?',
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "The Nashua Police Athletic League has been serving the community for decades, providing youth with safe spaces, athletic programs, mentorship, and leadership development.\n\nEvery dollar raised through sponsorships goes directly to supporting our programs, events, and the young people who depend on them.",
		) );
		$this->add_control( 'btn_label', array(
			'label'   => __( 'Button Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Learn More About PAL',
		) );
		$this->add_control( 'btn_url', array(
			'label'   => __( 'Button URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '/about/',
		) );
		$this->add_control( 'layout', array(
			'label'   => __( 'Layout', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'image-left'  => __( 'Image Left / Text Right', 'nashua-pal-core' ),
				'image-right' => __( 'Image Right / Text Left', 'nashua-pal-core' ),
			),
			'default' => 'image-right',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$photo  = ! empty( $s['photo']['url'] ) ? $s['photo']['url'] : '';
		$layout = $s['layout'] ? $s['layout'] : 'image-right';
		$cls    = 'image-right' === $layout ? ' pal-pkg-about--reversed' : '';
		// Handle both TEXT (string) and URL (array) control types for backwards compat.
		$btn_url = $s['btn_url'];
		if ( is_array( $btn_url ) ) { $btn_url = ! empty( $btn_url['url'] ) ? $btn_url['url'] : ''; }
		?>
		<section class="pal-pkg-about<?php echo esc_attr( $cls ); ?>">
			<div class="pal-pkg-about__inner">
				<div class="pal-pkg-about__media">
					<?php if ( $photo ) : ?>
						<div class="pal-pkg-about__image" style="background-image:url('<?php echo esc_url( $photo ); ?>');"></div>
					<?php else : ?>
						<div class="pal-pkg-about__image pal-pkg-about__image--placeholder"></div>
					<?php endif; ?>
				</div>
				<div class="pal-pkg-about__content">
					<?php if ( $s['eyebrow'] ) : ?>
						<span class="pal-pkg-about__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span>
					<?php endif; ?>
					<h2 class="pal-pkg-about__title"><?php echo esc_html( $s['title'] ); ?></h2>
					<?php if ( $s['description'] ) : ?>
						<?php foreach ( explode( "\n", $s['description'] ) as $para ) :
							$para = trim( $para );
							if ( '' === $para ) { continue; }
							?>
							<p class="pal-pkg-about__desc"><?php echo esc_html( $para ); ?></p>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if ( ! empty( $s['btn_label'] ) && ! empty( $btn_url ) ) : ?>
						<a href="<?php echo esc_url( $btn_url ); ?>" class="pal-pkg-about__btn"><?php echo esc_html( $s['btn_label'] ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Sponsorship Packages — Grid (shortcode wrapper)
 * ==================================================================== */

class NPAL_Widget_Spon_Pkg_Grid extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'sponsor_packages';

	public function get_name()     { return 'pal_spon_pkg_grid'; }
	public function get_title()    { return __( 'PAL Sponsorship Packages Grid', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-gallery-grid'; }
	public function get_keywords() { return array( 'pal', 'sponsorship', 'packages', 'grid' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Sponsorship Packages Grid', 'nashua-pal-core' ),
		) );

		$this->add_control( 'year', array(
			'label'       => __( 'Year', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. 2026 (blank = all)', 'nashua-pal-core' ),
		) );

		$this->add_control( 'category', array(
			'label'       => __( 'Category', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Category slug (blank = all)', 'nashua-pal-core' ),
		) );

		$this->add_control( 'show_filter', array(
			'label'   => __( 'Show Category Filter', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'true',
			'options' => array(
				'true'  => __( 'Yes', 'nashua-pal-core' ),
				'false' => __( 'No', 'nashua-pal-core' ),
			),
		) );

		$this->add_control( 'count', array(
			'label'   => __( 'Number of Packages', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 50,
			'min'     => 1,
			'max'     => 200,
		) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns (Desktop)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '3',
			'options' => array( '2' => '2', '3' => '3', '4' => '4' ),
		) );

		$this->add_control( 'featured_only', array(
			'label'   => __( 'Featured Only', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '',
			'options' => array(
				''     => __( 'No', 'nashua-pal-core' ),
				'true' => __( 'Yes', 'nashua-pal-core' ),
			),
		) );

		$this->add_control( 'eyebrow', array(
			'label'       => __( 'Section Eyebrow', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. OUR PACKAGES', 'nashua-pal-core' ),
		) );

		$this->add_control( 'view_all_label', array(
			'label'       => __( 'View All Button Label', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. View All Packages', 'nashua-pal-core' ),
		) );

		$this->add_control( 'view_all_url', array(
			'label'       => __( 'View All Button URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. /sponsorship-packages/', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'year'           => isset( $s['year'] )           ? $s['year']                 : '',
			'category'       => isset( $s['category'] )       ? $s['category']             : '',
			'show_filter'    => isset( $s['show_filter'] )    ? $s['show_filter']          : 'true',
			'count'          => isset( $s['count'] )          ? (string) $s['count']       : '50',
			'columns'        => isset( $s['columns'] )        ? (string) $s['columns']     : '3',
			'featured_only'  => isset( $s['featured_only'] )  ? $s['featured_only']        : '',
			'eyebrow'        => isset( $s['eyebrow'] )        ? $s['eyebrow']              : '',
			'view_all_label' => isset( $s['view_all_label'] ) ? $s['view_all_label']       : '',
			'view_all_url'   => isset( $s['view_all_url'] )   ? $s['view_all_url']         : '',
		);
	}
}

/* ======================================================================
 *  Sponsorship Packages — Featured Packages (shortcode wrapper)
 * ==================================================================== */

class NPAL_Widget_Spon_Pkg_Featured extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'sponsor_packages';

	public function get_name()     { return 'pal_spon_pkg_featured'; }
	public function get_title()    { return __( 'PAL Featured Packages', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-star'; }
	public function get_keywords() { return array( 'pal', 'sponsorship', 'featured' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Featured Packages', 'nashua-pal-core' ),
		) );

		$this->add_control( 'year', array(
			'label'       => __( 'Year', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. 2026', 'nashua-pal-core' ),
		) );
		$this->add_control( 'count', array(
			'label'   => __( 'Max Packages', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 6,
			'min'     => 1,
			'max'     => 50,
		) );
		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '3',
			'options' => array( '2' => '2', '3' => '3', '4' => '4' ),
		) );
		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'FEATURED PACKAGES',
		) );
		$this->add_control( 'view_all_label', array(
			'label'   => __( 'View All Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'View All Packages',
		) );
		$this->add_control( 'view_all_url', array(
			'label'   => __( 'View All URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '/sponsorship-packages/',
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'year'           => isset( $s['year'] )           ? $s['year']                 : '',
			'count'          => isset( $s['count'] )          ? (string) $s['count']       : '6',
			'columns'        => isset( $s['columns'] )        ? (string) $s['columns']     : '3',
			'featured_only'  => 'true',
			'show_filter'    => 'false',
			'eyebrow'        => isset( $s['eyebrow'] )        ? $s['eyebrow']              : '',
			'view_all_label' => isset( $s['view_all_label'] ) ? $s['view_all_label']       : '',
			'view_all_url'   => isset( $s['view_all_url'] )   ? $s['view_all_url']         : '',
		);
	}
}

/* ======================================================================
 *  Sponsorship Packages — CTA Banner
 * ==================================================================== */

class NPAL_Widget_Spon_Pkg_CTA extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_spon_pkg_cta'; }
	public function get_title()    { return __( 'PAL Sponsorship CTA', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-call-to-action'; }
	public function get_keywords() { return array( 'pal', 'sponsorship', 'cta', 'banner' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'CTA Banner', 'nashua-pal-core' ),
		) );

		$this->add_control( 'bg_image', array(
			'label'   => __( 'Background Image', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Make a Lasting Impact',
		) );
		$this->add_control( 'tagline', array(
			'label'   => __( 'Tagline', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Ready to partner with Nashua PAL? Contact us today to discuss sponsorship opportunities that align with your goals.',
		) );

		$btn_repeater = new \Elementor\Repeater();
		$btn_repeater->add_control( 'label', array( 'label' => __( 'Label', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Button' ) );
		$btn_repeater->add_control( 'url',   array( 'label' => __( 'URL', 'nashua-pal-core' ),   'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#' ) );
		$btn_repeater->add_control( 'style', array(
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
				array( 'label' => 'Become a Sponsor', 'url' => '/contact/', 'style' => 'primary' ),
				array( 'label' => 'Download Guide',   'url' => '#',         'style' => 'outline' ),
			),
			'title_field' => '{{{ label }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$bg_url = ! empty( $s['bg_image']['url'] ) ? $s['bg_image']['url'] : '';
		?>
		<section class="pal-pkg-cta"<?php if ( $bg_url ) : ?> style="background-image:url('<?php echo esc_url( $bg_url ); ?>');"<?php endif; ?>>
			<div class="pal-pkg-cta__inner">
				<?php if ( $s['title'] ) : ?>
					<h2 class="pal-pkg-cta__title"><?php echo esc_html( $s['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $s['tagline'] ) : ?>
					<p class="pal-pkg-cta__tagline"><?php echo esc_html( $s['tagline'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $s['buttons'] ) ) : ?>
					<div class="pal-pkg-cta__buttons">
						<?php foreach ( (array) $s['buttons'] as $btn ) :
							$cls = 'primary' === ( isset( $btn['style'] ) ? $btn['style'] : '' ) ? 'pal-pkg-cta__btn--primary' : 'pal-pkg-cta__btn--outline';
							$btn_label = isset( $btn['label'] ) ? $btn['label'] : ( isset( $btn['btn_label'] ) ? $btn['btn_label'] : '' );
							$btn_url   = isset( $btn['url'] )   ? $btn['url']   : ( isset( $btn['btn_url'] )   ? $btn['btn_url']   : '#' );
							?>
							<a href="<?php echo esc_url( $btn_url ); ?>" class="pal-pkg-cta__btn <?php echo esc_attr( $cls ); ?>"><?php echo esc_html( $btn_label ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Sponsorship Packages — Info Block
 * ==================================================================== */

class NPAL_Widget_Spon_Pkg_Info extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_spon_pkg_info'; }
	public function get_title()    { return __( 'PAL Sponsorship Info', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-text'; }
	public function get_keywords() { return array( 'pal', 'sponsorship', 'info' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Info Block', 'nashua-pal-core' ),
		) );

		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Nashua PAL offers a variety of sponsorship opportunities to fit every budget and goal.',
		) );
		$this->add_control( 'btn_label', array(
			'label'   => __( 'Button Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Download Sponsorship Guide',
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
		// Handle both TEXT (string) and URL (array) control types for backwards compat.
		$btn_url = $s['btn_url'];
		if ( is_array( $btn_url ) ) { $btn_url = ! empty( $btn_url['url'] ) ? $btn_url['url'] : ''; }
		?>
		<section class="pal-pkg-info">
			<div class="pal-pkg-info__inner">
				<?php if ( $s['description'] ) : ?>
					<p class="pal-pkg-info__desc"><?php echo esc_html( $s['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $s['btn_label'] ) && ! empty( $btn_url ) ) : ?>
					<a href="<?php echo esc_url( $btn_url ); ?>" class="pal-pkg-info__btn"><?php echo esc_html( $s['btn_label'] ); ?></a>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Sponsorship Packages — Benefits / Values Grid
 * ==================================================================== */

class NPAL_Widget_Spon_Pkg_Values extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_spon_pkg_values'; }
	public function get_title()    { return __( 'PAL Sponsorship Benefits', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-icon-box'; }
	public function get_keywords() { return array( 'pal', 'sponsorship', 'values', 'benefits' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Sponsorship Benefits', 'nashua-pal-core' ),
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'icon',        array( 'label' => __( 'Icon Class', 'nashua-pal-core' ),  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'fas fa-star' ) );
		$repeater->add_control( 'title',       array( 'label' => __( 'Title', 'nashua-pal-core' ),       'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Benefit' ) );
		$repeater->add_control( 'description', array( 'label' => __( 'Description', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => '' ) );

		$this->add_control( 'items', array(
			'label'       => __( 'Benefits', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(
				array( 'icon' => 'fas fa-bullhorn',  'title' => 'Brand Visibility',  'description' => 'Put your brand in front of thousands of families across Nashua.' ),
				array( 'icon' => 'fas fa-heart',     'title' => 'Community Impact',  'description' => 'Support youth programs that change lives every day.' ),
				array( 'icon' => 'fas fa-handshake', 'title' => 'Networking',        'description' => 'Connect with community leaders and local businesses.' ),
				array( 'icon' => 'fas fa-trophy',    'title' => 'Recognition',       'description' => 'Be recognized as a champion for Nashua\'s youth.' ),
			),
			'title_field' => '{{{ title }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = ! empty( $s['items'] ) ? (array) $s['items'] : array();
		if ( empty( $items ) ) { return; }
		?>
		<section class="pal-pkg-values">
			<div class="pal-pkg-values__inner">
				<?php foreach ( $items as $item ) : ?>
					<div class="pal-pkg-values__item">
						<?php if ( ! empty( $item['icon'] ) ) : ?>
							<div class="pal-pkg-values__icon"><i class="<?php echo esc_attr( $item['icon'] ); ?>"></i></div>
						<?php endif; ?>
						<?php if ( ! empty( $item['title'] ) ) : ?>
							<h3 class="pal-pkg-values__title"><?php echo esc_html( $item['title'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $item['description'] ) ) : ?>
							<p class="pal-pkg-values__desc"><?php echo esc_html( $item['description'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
