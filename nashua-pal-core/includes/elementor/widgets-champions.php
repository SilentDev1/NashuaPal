<?php
/**
 * Nashua PAL Elementor widgets — Night of Champions.
 *
 * @package NashuaPalCore
 * @since   1.15.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ======================================================================
 *  Champions Winners Grid
 * ==================================================================== */

class NPAL_Widget_Champions_Winners extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'champions_winners';

	public function get_name()     { return 'pal_champions_winners'; }
	public function get_title()    { return __( 'PAL Champions Winners Grid', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-trophy'; }
	public function get_keywords() { return array( 'pal', 'champions', 'winners', 'awards', 'night' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Champions Winners', 'nashua-pal-core' ),
		) );

		$this->add_control( 'year', array(
			'label'       => __( 'Year', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. 2025 (blank = all)', 'nashua-pal-core' ),
		) );

		$cat_opts = array( '' => __( 'All Categories', 'nashua-pal-core' ) );
		$cat_terms = get_terms( array( 'taxonomy' => NPAL_CPT_Champions_Winner::TAXONOMY, 'hide_empty' => false ) );
		if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
			foreach ( $cat_terms as $ct ) { $cat_opts[ $ct->slug ] = $ct->name; }
		}
		$this->add_control( 'category', array(
			'label'   => __( 'Category', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '',
			'options' => $cat_opts,
		) );

		$this->add_control( 'source', array(
			'label'   => __( 'Show', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'both',
			'options' => array(
				'both'       => __( 'Featured + Recipients', 'nashua-pal-core' ),
				'featured'   => __( 'Featured Only', 'nashua-pal-core' ),
				'recipients' => __( 'Recipients Only', 'nashua-pal-core' ),
			),
		) );

		$this->add_control( 'show_filter', array(
			'label'   => __( 'Show Filters', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'true',
			'options' => array(
				'true'  => __( 'Yes', 'nashua-pal-core' ),
				'false' => __( 'No', 'nashua-pal-core' ),
			),
		) );

		$this->add_control( 'count', array(
			'label'   => __( 'Number of Winners', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 50,
			'min'     => 1,
			'max'     => 200,
		) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns (Desktop)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '3',
			'options' => array(
				'2' => '2',
				'3' => '3',
				'4' => '4',
			),
		) );

		$this->add_control( 'featured_eyebrow', array(
			'label'       => __( 'Featured Section Eyebrow', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. FEATURED CHAMPIONS', 'nashua-pal-core' ),
		) );

		$this->add_control( 'recipients_eyebrow', array(
			'label'       => __( 'Recipients Section Eyebrow', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. OUR WINNERS', 'nashua-pal-core' ),
		) );

		$this->add_control( 'view_all_label', array(
			'label'       => __( 'View All Button Label', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. View All Winners', 'nashua-pal-core' ),
		) );

		$this->add_control( 'view_all_url', array(
			'label'       => __( 'View All Button URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. /champions-winners/', 'nashua-pal-core' ),
		) );

		$this->add_control( 'rotate', array(
			'label'       => __( 'Auto-Rotate Winners', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::SELECT,
			'default'     => 'false',
			'options'     => array(
				'false' => __( 'No', 'nashua-pal-core' ),
				'true'  => __( 'Yes', 'nashua-pal-core' ),
			),
			'description' => __( 'Cycle through all winners in groups, so everyone gets featured.', 'nashua-pal-core' ),
		) );

		$this->add_control( 'rotate_visible', array(
			'label'     => __( 'Visible per Page', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::NUMBER,
			'default'   => 6,
			'min'       => 3,
			'max'       => 12,
			'condition' => array( 'rotate' => 'true' ),
		) );

		$this->add_control( 'rotate_interval', array(
			'label'       => __( 'Rotation Speed (ms)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::NUMBER,
			'default'     => 5000,
			'min'         => 2000,
			'max'         => 15000,
			'step'        => 500,
			'condition'   => array( 'rotate' => 'true' ),
			'description' => __( 'Milliseconds between page transitions.', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'year'               => isset( $s['year'] )               ? $s['year']                 : '',
			'category'           => isset( $s['category'] )           ? $s['category']             : '',
			'source'             => isset( $s['source'] )             ? $s['source']               : 'both',
			'show_filter'        => isset( $s['show_filter'] )        ? $s['show_filter']          : 'true',
			'count'              => isset( $s['count'] )              ? (string) $s['count']       : '50',
			'columns'            => isset( $s['columns'] )            ? (string) $s['columns']     : '3',
			'featured_eyebrow'   => isset( $s['featured_eyebrow'] )   ? $s['featured_eyebrow']     : '',
			'recipients_eyebrow' => isset( $s['recipients_eyebrow'] ) ? $s['recipients_eyebrow']   : '',
			'view_all_label'     => isset( $s['view_all_label'] )     ? $s['view_all_label']       : '',
			'view_all_url'       => isset( $s['view_all_url'] )       ? $s['view_all_url']         : '',
			'rotate'             => isset( $s['rotate'] )             ? $s['rotate']               : 'false',
			'rotate_visible'     => isset( $s['rotate_visible'] )     ? (string) $s['rotate_visible'] : '6',
			'rotate_interval'    => isset( $s['rotate_interval'] )    ? (string) $s['rotate_interval'] : '5000',
		);
	}
}

/* ======================================================================
 *  Champions Sponsors
 * ==================================================================== */

class NPAL_Widget_Champions_Sponsors extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'champions_sponsors';

	public function get_name()     { return 'pal_champions_sponsors'; }
	public function get_title()    { return __( 'PAL Champions Sponsors', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-image-rollover'; }
	public function get_keywords() { return array( 'pal', 'champions', 'sponsors', 'logos', 'night' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Champions Sponsors', 'nashua-pal-core' ),
		) );

		$this->add_control( 'year', array(
			'label'       => __( 'Year', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. 2025', 'nashua-pal-core' ),
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Thank You to Our Sponsors', 'nashua-pal-core' ),
		) );

		$this->add_control( 'show_filter', array(
			'label'        => __( 'Show Level Filter Tabs', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => __( 'Yes', 'nashua-pal-core' ),
			'label_off'    => __( 'No', 'nashua-pal-core' ),
			'return_value' => 'true',
			'default'      => 'true',
		) );

		$this->add_control( 'scroll_speed', array(
			'label'       => __( 'Scroll Speed (seconds)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::NUMBER,
			'default'     => 30,
			'min'         => 5,
			'max'         => 120,
			'step'        => 5,
			'description' => __( 'Duration in seconds for one full loop. Lower = faster.', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'year'         => isset( $s['year'] )         ? $s['year']         : '',
			'heading'      => isset( $s['heading'] )      ? $s['heading']      : '',
			'show_filter'  => isset( $s['show_filter'] )  ? $s['show_filter']  : 'true',
			'scroll_speed' => isset( $s['scroll_speed'] ) ? $s['scroll_speed'] : '30',
		);
	}
}

/* ======================================================================
 *  Night of Champions — Hero Section with Stats Bar
 * ==================================================================== */

class NPAL_Widget_NOC_Hero extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_noc_hero'; }
	public function get_title()    { return __( 'PAL Night of Champions Hero', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-banner'; }
	public function get_keywords() { return array( 'pal', 'champions', 'hero', 'night', 'banner' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'hero_content', array(
			'label' => __( 'Hero Content', 'nashua-pal-core' ),
		) );

		$this->add_control( 'bg_image', array(
			'label'       => __( 'Background Image', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::MEDIA,
			'default'     => array( 'url' => '' ),
			'description' => __( 'Leave empty to use the default Night of Champions background.', 'nashua-pal-core' ),
		) );
		$this->add_responsive_control( 'hero_height', array(
			'label'      => __( 'Hero Height (px)', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 300, 'max' => 1000, 'step' => 10 ) ),
			'default'    => array( 'size' => 520, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .pal-noc-hero' => 'height: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'NASHUA POLICE ATHLETIC LEAGUE',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Annual Night of Champions 2025',
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Celebrating the individuals who make our community stronger through sports, mentorship, and dedicated service to Nashua\xe2\x80\x99s youth.",
		) );

		$btn_repeater = new \Elementor\Repeater();
		$btn_repeater->add_control( 'btn_label', array( 'label' => __( 'Label', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Button' ) );
		$btn_repeater->add_control( 'btn_url',   array( 'label' => __( 'URL', 'nashua-pal-core' ),   'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#' ) );
		$btn_repeater->add_control( 'btn_style', array(
			'label'   => __( 'Style', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'primary' => __( 'Primary (gold filled)', 'nashua-pal-core' ),
				'outline' => __( 'Outline (white border)', 'nashua-pal-core' ),
			),
			'default' => 'primary',
		) );

		$this->add_control( 'buttons', array(
			'label'       => __( 'Buttons', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $btn_repeater->get_controls(),
			'default'     => array(
				array( 'btn_label' => 'View Winners',  'btn_url' => '#winners',                       'btn_style' => 'primary' ),
				array( 'btn_label' => 'View Sponsors', 'btn_url' => '#sponsors',                      'btn_style' => 'outline' ),
				array( 'btn_label' => 'Buy Tickets',   'btn_url' => '/night-of-champions/tickets/',   'btn_style' => 'outline' ),
			),
			'title_field' => '{{{ btn_label }}}',
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
		$this->add_responsive_control( 'stats_width', array(
			'label'      => __( 'Stats Card Max Width (px)', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 400, 'max' => 1200, 'step' => 10 ) ),
			'default'    => array( 'size' => 740, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .pal-noc-stats-card' => 'max-width: {{SIZE}}{{UNIT}};' ),
			'condition'  => array( 'show_stats' => 'yes' ),
		) );
		$this->add_responsive_control( 'stats_padding', array(
			'label'      => __( 'Stats Card Padding', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px' ),
			'default'    => array( 'top' => '20', 'right' => '40', 'bottom' => '20', 'left' => '40', 'unit' => 'px', 'isLinked' => false ),
			'selectors'  => array( '{{WRAPPER}} .pal-noc-stats-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			'condition'  => array( 'show_stats' => 'yes' ),
		) );
		$this->add_responsive_control( 'stats_overlap', array(
			'label'      => __( 'Stats Overlap (px)', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 300, 'step' => 5 ) ),
			'default'    => array( 'size' => 50, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .pal-noc-stats-section' => 'margin-top: -{{SIZE}}{{UNIT}};' ),
			'condition'  => array( 'show_stats' => 'yes' ),
		) );
		$this->add_responsive_control( 'stats_icon_size', array(
			'label'      => __( 'Icon Circle Size (px)', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 24, 'max' => 100, 'step' => 2 ) ),
			'default'    => array( 'size' => 48, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .pal-noc-stats-card__icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}; min-width: {{SIZE}}{{UNIT}};' ),
			'condition'  => array( 'show_stats' => 'yes' ),
		) );
		$this->add_responsive_control( 'stats_number_size', array(
			'label'      => __( 'Number Font Size (px)', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 14, 'max' => 72, 'step' => 1 ) ),
			'default'    => array( 'size' => 26, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .pal-noc-stats-card__number' => 'font-size: {{SIZE}}{{UNIT}};' ),
			'condition'  => array( 'show_stats' => 'yes' ),
		) );
		$this->add_responsive_control( 'stats_label_size', array(
			'label'      => __( 'Label Font Size (px)', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 8, 'max' => 24, 'step' => 1 ) ),
			'default'    => array( 'size' => 12, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .pal-noc-stats-card__label' => 'font-size: {{SIZE}}{{UNIT}};' ),
			'condition'  => array( 'show_stats' => 'yes' ),
		) );

		$stat_repeater = new \Elementor\Repeater();
		$stat_repeater->add_control( 'stat_number', array( 'label' => __( 'Number', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '100+' ) );
		$stat_repeater->add_control( 'stat_label',  array( 'label' => __( 'Label', 'nashua-pal-core' ),  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Stat Label' ) );
		$stat_repeater->add_control( 'stat_icon',   array(
			'label'       => __( 'Icon Class', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => 'fas fa-star',
			'description' => __( 'Font Awesome class, e.g. fas fa-users', 'nashua-pal-core' ),
		) );

		$this->add_control( 'stats', array(
			'label'       => __( 'Stats', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $stat_repeater->get_controls(),
			'default'     => array(
				array( 'stat_number' => '250+', 'stat_label' => 'Guests Celebrated',  'stat_icon' => 'fas fa-users' ),
				array( 'stat_number' => '15',   'stat_label' => 'Award Categories',   'stat_icon' => 'fas fa-trophy' ),
				array( 'stat_number' => '30+',  'stat_label' => 'Years of Excellence', 'stat_icon' => 'fas fa-star' ),
			),
			'title_field' => '{{{ stat_label }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s       = $this->get_settings_for_display();
		$bg_url  = ! empty( $s['bg_image']['url'] ) ? $s['bg_image']['url'] : NPAL_PLUGIN_URL . 'assets/img/noc-hero-bg.png';
		$show_st = 'yes' === $s['show_stats'];
		?>
		<!-- SECTION 1: Hero -->
		<section class="pal-noc-hero" style="background-image:url('<?php echo esc_url( $bg_url ); ?>');">
			<div class="pal-noc-hero__overlay"></div>
			<div class="pal-noc-hero__container">
				<div class="pal-noc-hero__content">
					<?php if ( $s['eyebrow'] ) : ?>
						<span class="pal-noc-hero__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span>
					<?php endif; ?>
					<h1 class="pal-noc-hero__title"><?php echo esc_html( $s['title'] ); ?></h1>
					<?php if ( $s['description'] ) : ?>
						<p class="pal-noc-hero__desc"><?php echo esc_html( $s['description'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $s['buttons'] ) ) : ?>
						<div class="pal-noc-hero__buttons">
							<?php foreach ( (array) $s['buttons'] as $btn ) :
								$cls = 'primary' === $btn['btn_style'] ? 'pal-noc-hero__btn--primary' : 'pal-noc-hero__btn--outline';
								?>
								<a href="<?php echo esc_url( $btn['btn_url'] ); ?>" class="pal-noc-hero__btn <?php echo esc_attr( $cls ); ?>"><?php echo esc_html( $btn['btn_label'] ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</section>

		<?php if ( $show_st && ! empty( $s['stats'] ) ) : ?>
		<!-- SECTION 2: Floating Stats Card (separate from hero) -->
		<section class="pal-noc-stats-section">
			<div class="pal-noc-stats-card">
				<?php foreach ( (array) $s['stats'] as $i => $stat ) : ?>
					<?php if ( $i > 0 ) : ?><div class="pal-noc-stats-card__divider"></div><?php endif; ?>
					<div class="pal-noc-stats-card__item">
						<?php if ( $stat['stat_icon'] ) : ?>
							<div class="pal-noc-stats-card__icon"><i class="<?php echo esc_attr( $stat['stat_icon'] ); ?>"></i></div>
						<?php endif; ?>
						<div class="pal-noc-stats-card__text">
							<span class="pal-noc-stats-card__number"><?php echo esc_html( $stat['stat_number'] ); ?></span>
							<span class="pal-noc-stats-card__label"><?php echo esc_html( $stat['stat_label'] ); ?></span>
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
 *  Night of Champions — About / "Honoring Excellence" Section
 * ==================================================================== */

class NPAL_Widget_NOC_About extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_noc_about'; }
	public function get_title()    { return __( 'PAL NOC About Section', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-circle'; }
	public function get_keywords() { return array( 'pal', 'champions', 'about', 'night', 'honoring' ); }

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
			'default' => 'ABOUT THE EVENT',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Honoring Excellence. Building Community.',
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "The Night of Champions Gala is Nashua PAL\xe2\x80\x99s premier annual event, bringing together community leaders, law enforcement, educators, and families to honor the outstanding individuals who have made a lasting impact on our youth.\n\nFrom young athletes to dedicated officers and volunteer mentors, this evening celebrates the best of Nashua through awards, recognition, and shared commitment to our mission.",
		) );
		$this->add_control( 'btn_label', array(
			'label'   => __( 'Button Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Our Mission',
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
			'default' => 'image-left',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$photo  = ! empty( $s['photo']['url'] ) ? $s['photo']['url'] : '';
		$layout = $s['layout'] ? $s['layout'] : 'image-left';
		$cls    = 'image-right' === $layout ? ' pal-noc-about--reversed' : '';
		?>
		<section class="pal-noc-about<?php echo esc_attr( $cls ); ?>">
			<div class="pal-noc-about__inner">
				<div class="pal-noc-about__media">
					<?php if ( $photo ) : ?>
						<div class="pal-noc-about__image" style="background-image:url('<?php echo esc_url( $photo ); ?>');"></div>
					<?php else : ?>
						<div class="pal-noc-about__image pal-noc-about__image--placeholder"></div>
					<?php endif; ?>
				</div>
				<div class="pal-noc-about__content">
					<?php if ( $s['eyebrow'] ) : ?>
						<span class="pal-noc-about__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span>
					<?php endif; ?>
					<h2 class="pal-noc-about__title"><?php echo esc_html( $s['title'] ); ?></h2>
					<?php if ( $s['description'] ) : ?>
						<?php foreach ( explode( "\n", $s['description'] ) as $para ) :
							$para = trim( $para );
							if ( '' === $para ) { continue; }
							?>
							<p class="pal-noc-about__desc"><?php echo esc_html( $para ); ?></p>
						<?php endforeach; ?>
					<?php endif; ?>
					<?php if ( ! empty( $s['btn_label'] ) && ! empty( $s['btn_url'] ) ) : ?>
						<a href="<?php echo esc_url( $s['btn_url'] ); ?>" class="pal-noc-about__btn"><?php echo esc_html( $s['btn_label'] ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Night of Champions — Past Champions with Year Tabs
 * ==================================================================== */

class NPAL_Widget_NOC_Past_Champions extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_noc_past_champions'; }
	public function get_title()    { return __( 'PAL Past Champions', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-history'; }
	public function get_keywords() { return array( 'pal', 'champions', 'past', 'archive', 'year' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Past Champions', 'nashua-pal-core' ),
		) );

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'LOOKING BACK',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Past Champions',
		) );
		$this->add_control( 'count_per_year', array(
			'label'       => __( 'Visible per Page', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::NUMBER,
			'default'     => 6,
			'min'         => 3,
			'max'         => 20,
			'description' => __( 'Number of winners shown at a time (rotates through all).', 'nashua-pal-core' ),
		) );
		$this->add_control( 'rotate_interval', array(
			'label'       => __( 'Rotation Speed (ms)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::NUMBER,
			'default'     => 5000,
			'min'         => 2000,
			'max'         => 15000,
			'step'        => 500,
			'description' => __( 'Milliseconds between page transitions.', 'nashua-pal-core' ),
		) );
		$this->add_control( 'show_category_filter', array(
			'label'        => __( 'Show Category Filter Tabs', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => __( 'Yes', 'nashua-pal-core' ),
			'label_off'    => __( 'No', 'nashua-pal-core' ),
			'return_value' => 'yes',
			'default'      => 'yes',
		) );
		$this->add_control( 'archive_label', array(
			'label'   => __( 'Archive Button Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Explore Past Winners',
		) );
		$this->add_control( 'archive_url', array(
			'label'   => __( 'Archive URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '/champions-winners/',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s                   = $this->get_settings_for_display();
		$visible_per_page    = max( 3, min( 20, (int) $s['count_per_year'] ) );
		$rotate_interval     = max( 2000, (int) ( isset( $s['rotate_interval'] ) ? $s['rotate_interval'] : 5000 ) );
		$show_category_filter = ! empty( $s['show_category_filter'] ) && 'yes' === $s['show_category_filter'];

		/* -- Get unique years from DB ---------------------------------- */
		global $wpdb;
		$years = $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			 WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'
			 ORDER BY pm.meta_value DESC",
			'_pal_champion_year',
			NPAL_CPT_Champions_Winner::POST_TYPE
		) );

		if ( empty( $years ) ) {
			echo '<div class="pal-noc-past" style="background:var(--pal-champ-blue);padding:48px 20px;"><p class="pal-champ-empty" style="color:#fff;">' . esc_html__( 'No past champions found.', 'nashua-pal-core' ) . '</p></div>';
			return;
		}

		/* -- Query winners for each year ------------------------------- */
		$years_data       = array();
		$years_categories = array(); // categories per year for filter tabs
		foreach ( $years as $year ) {
			$q = new WP_Query( array(
				'post_type'      => NPAL_CPT_Champions_Winner::POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_key'       => '_pal_champion_order',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
				'meta_query'     => array(
					array( 'key' => '_pal_champion_year', 'value' => $year ),
				),
			) );
			$items = array();
			$cats  = array();
			foreach ( $q->posts as $p ) {
				$name      = get_post_meta( $p->ID, '_pal_champion_name', true );
				$term      = NPAL_CPT_Champions_Winner::get_award_category( $p->ID );
				$cat_slug  = $term ? $term->slug : '';
				$cat_label = $term ? $term->name : '';
				$items[]   = array(
					'name'        => $name ? $name : $p->post_title,
					'award_title' => get_post_meta( $p->ID, '_pal_champion_award_title', true ),
					'cat_slug'    => $cat_slug,
					'cat_label'   => $cat_label,
					'badge_style' => NPAL_CPT_Champions_Winner::get_badge_style( $term ),
					'image'       => get_the_post_thumbnail_url( $p->ID, 'large' ),
					'permalink'   => get_permalink( $p->ID ),
				);
				if ( $cat_slug && ! isset( $cats[ $cat_slug ] ) ) {
					$cats[ $cat_slug ] = $cat_label;
				}
			}
			wp_reset_postdata();
			$years_data[ $year ]       = $items;
			$years_categories[ $year ] = $cats;
		}

		$first_year = $years[0];

		// Collect all categories across all years for the dropdown.
		$all_categories = array();
		foreach ( $years_categories as $cats ) {
			foreach ( $cats as $slug => $label ) {
				if ( ! isset( $all_categories[ $slug ] ) ) {
					$all_categories[ $slug ] = $label;
				}
			}
		}
		?>
		<div class="pal-noc-past">
			<div class="pal-noc-past__inner">
				<?php if ( $s['eyebrow'] ) : ?>
					<span class="pal-noc-past__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span>
				<?php endif; ?>
				<?php if ( $s['title'] ) : ?>
					<h2 class="pal-noc-past__title"><?php echo esc_html( $s['title'] ); ?></h2>
				<?php endif; ?>

				<!-- Slim filter bar -->
				<div class="pal-noc-past__filters">
					<div class="pal-noc-past__filter-group">
						<label for="noc-past-year"><?php esc_html_e( 'Year', 'nashua-pal-core' ); ?></label>
						<select id="noc-past-year" data-noc-past-filter="year">
							<?php foreach ( $years as $year ) : ?>
								<option value="<?php echo esc_attr( $year ); ?>"<?php echo $year === $first_year ? ' selected' : ''; ?>><?php echo esc_html( $year ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<?php if ( $show_category_filter && count( $all_categories ) > 1 ) : ?>
						<div class="pal-noc-past__filter-group">
							<label for="noc-past-category"><?php esc_html_e( 'Award Category', 'nashua-pal-core' ); ?></label>
							<select id="noc-past-category" data-noc-past-filter="category">
								<option value=""><?php esc_html_e( 'All Categories', 'nashua-pal-core' ); ?></option>
								<?php foreach ( $all_categories as $slug => $label ) : ?>
									<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					<?php endif; ?>
					<button type="button" class="pal-noc-past__clear-btn" data-noc-past-clear><?php esc_html_e( 'Clear Filters', 'nashua-pal-core' ); ?></button>
				</div>

				<?php foreach ( $years_data as $year => $items ) : ?>
					<div class="pal-noc-past__panel<?php echo (string) $year !== (string) $first_year ? ' is-hidden' : ''; ?>" data-noc-year-panel="<?php echo esc_attr( $year ); ?>">
						<?php if ( empty( $items ) ) : ?>
							<p class="pal-champ-empty" style="color:#C4CCD8;"><?php esc_html_e( 'No champions for this year.', 'nashua-pal-core' ); ?></p>
						<?php else :
							$rotate_attr = count( $items ) > $visible_per_page
								? ' data-champ-rotate="' . esc_attr( $visible_per_page ) . '" data-champ-rotate-interval="' . esc_attr( $rotate_interval ) . '"'
								: '';
							$is_legacy_year = (int) $year < 2021;
							?>
							<div class="pal-champ-grid pal-champ-grid--cols-5 pal-noc-past__grid"<?php echo $rotate_attr; ?>>
								<?php foreach ( $items as $item ) :
									$is_legacy  = empty( $item['image'] );
									$img_style  = $item['image'] ? 'background-image:url(' . esc_url( $item['image'] ) . ')' : '';
									$card_class = 'pal-champ-card pal-noc-past__card' . ( $is_legacy ? ' pal-champ-card--legacy' : '' );
									$initials   = '';
									if ( $is_legacy && $item['name'] ) {
										$parts    = preg_split( '/[\s]+/', trim( $item['name'] ) );
										$initials = strtoupper( mb_substr( $parts[0], 0, 1 ) );
										if ( count( $parts ) > 1 ) {
											$initials .= strtoupper( mb_substr( end( $parts ), 0, 1 ) );
										}
									}
									$show_cta = ! $is_legacy && ! $is_legacy_year;
									?>
									<div class="<?php echo esc_attr( $card_class ); ?>" data-champ-category="<?php echo esc_attr( $item['cat_slug'] ); ?>">
										<?php if ( $is_legacy ) : ?>
											<div class="pal-champ-card__link">
												<div class="pal-champ-card__media">
													<?php if ( $initials ) : ?>
														<span class="pal-champ-card__initials"><?php echo esc_html( $initials ); ?></span>
													<?php endif; ?>
													<?php if ( $item['cat_label'] ) : ?>
														<span class="pal-champ-card__badge pal-champ-card__badge--<?php echo esc_attr( $item['cat_slug'] ); ?>" <?php if ( $item['badge_style'] ) { echo 'style="' . esc_attr( $item['badge_style'] ) . '"'; } ?>><?php echo esc_html( $item['cat_label'] ); ?></span>
													<?php endif; ?>
												</div>
												<div class="pal-champ-card__body">
													<h3 class="pal-champ-card__name"><?php echo esc_html( $item['name'] ); ?></h3>
													<?php if ( $item['award_title'] ) : ?>
														<p class="pal-champ-card__award"><?php echo esc_html( $item['award_title'] ); ?></p>
													<?php endif; ?>
												</div>
											</div>
										<?php else : ?>
											<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="pal-champ-card__link">
												<div class="pal-champ-card__media" style="<?php echo esc_attr( $img_style ); ?>">
													<?php if ( $item['cat_label'] ) : ?>
														<span class="pal-champ-card__badge pal-champ-card__badge--<?php echo esc_attr( $item['cat_slug'] ); ?>" <?php if ( $item['badge_style'] ) { echo 'style="' . esc_attr( $item['badge_style'] ) . '"'; } ?>><?php echo esc_html( $item['cat_label'] ); ?></span>
													<?php endif; ?>
												</div>
												<div class="pal-champ-card__body">
													<h3 class="pal-champ-card__name"><?php echo esc_html( $item['name'] ); ?></h3>
													<?php if ( $item['award_title'] ) : ?>
														<p class="pal-champ-card__award"><?php echo esc_html( $item['award_title'] ); ?></p>
													<?php endif; ?>
													<?php if ( $show_cta ) : ?>
														<span class="pal-champ-card__cta">View Profile <i class="fas fa-arrow-right"></i></span>
													<?php endif; ?>
												</div>
											</a>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
							<?php if ( count( $items ) > $visible_per_page ) : ?>
								<div class="pal-champ-rotate-dots"></div>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>

				<?php if ( $s['archive_label'] && $s['archive_url'] ) : ?>
					<div class="pal-noc-past__footer">
						<a href="<?php echo esc_url( $s['archive_url'] ); ?>" class="pal-noc-past__archive-btn"><?php echo esc_html( $s['archive_label'] ); ?> <i class="fas fa-arrow-right"></i></a>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

/* ======================================================================
 *  Night of Champions — Gala Highlights / Gallery Section
 *  Text-left + photo grid on right (matches mockup layout)
 * ==================================================================== */

class NPAL_Widget_NOC_Gallery extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_noc_gallery'; }
	public function get_title()    { return __( 'PAL NOC Gala Highlights', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-gallery-grid'; }
	public function get_keywords() { return array( 'pal', 'champions', 'gallery', 'gala', 'highlights' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Gala Highlights', 'nashua-pal-core' ),
		) );

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'GALA HIGHLIGHTS',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'A Night to Remember',
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Relive the magic of our annual gala through photos capturing the awards ceremony, community celebration, and the inspiring moments that make Night of Champions special.',
		) );
		$this->add_control( 'btn_label', array(
			'label'   => __( 'Button Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'View Full Gallery',
		) );
		$this->add_control( 'btn_url', array(
			'label'   => __( 'Button URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '#',
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'image', array(
			'label' => __( 'Image', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );

		$this->add_control( 'photos', array(
			'label'       => __( 'Photos', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(
				array( 'image' => array( 'url' => '' ) ),
				array( 'image' => array( 'url' => '' ) ),
				array( 'image' => array( 'url' => '' ) ),
				array( 'image' => array( 'url' => '' ) ),
				array( 'image' => array( 'url' => '' ) ),
				array( 'image' => array( 'url' => '' ) ),
			),
			'title_field' => 'Photo',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$photos = (array) $s['photos'];
		$featured = array_slice( $photos, 0, 6 );
		$all_urls = array();
		foreach ( $photos as $p ) {
			$u = isset( $p['image']['url'] ) ? $p['image']['url'] : '';
			if ( $u ) { $all_urls[] = $u; }
		}
		$uid = 'noc-gallery-' . wp_unique_id();
		?>
		<section class="pal-noc-gallery" id="<?php echo esc_attr( $uid ); ?>">
			<div class="pal-noc-gallery__inner">
				<div class="pal-noc-gallery__text">
					<?php if ( $s['eyebrow'] ) : ?>
						<span class="pal-noc-gallery__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span>
					<?php endif; ?>
					<h2 class="pal-noc-gallery__title"><?php echo esc_html( $s['title'] ); ?></h2>
					<?php if ( $s['description'] ) : ?>
						<p class="pal-noc-gallery__desc"><?php echo esc_html( $s['description'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $s['btn_label'] ) ) : ?>
						<button type="button" class="pal-noc-gallery__btn" data-noc-lightbox-open="<?php echo esc_attr( $uid ); ?>"><?php echo esc_html( $s['btn_label'] ); ?></button>
					<?php endif; ?>
				</div>
				<div class="pal-noc-gallery__grid">
					<?php foreach ( $featured as $idx => $p ) :
						$u = isset( $p['image']['url'] ) ? $p['image']['url'] : '';
						$bg = $u ? "background-image:url('" . esc_url( $u ) . "')" : '';
						?>
						<div class="pal-noc-gallery__photo" data-noc-lightbox-thumb="<?php echo esc_attr( $uid ); ?>" data-index="<?php echo (int) $idx; ?>" style="<?php echo esc_attr( $bg ); ?>"></div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="pal-noc-lightbox" data-noc-lightbox="<?php echo esc_attr( $uid ); ?>" aria-hidden="true">
				<div class="pal-noc-lightbox__overlay"></div>
				<div class="pal-noc-lightbox__wrap">
					<button type="button" class="pal-noc-lightbox__close" aria-label="Close gallery">&times;</button>
					<?php if ( ! empty( $all_urls ) ) : ?>
						<button type="button" class="pal-noc-lightbox__nav pal-noc-lightbox__prev" aria-label="Previous">&lsaquo;</button>
						<button type="button" class="pal-noc-lightbox__nav pal-noc-lightbox__next" aria-label="Next">&rsaquo;</button>
						<div class="pal-noc-lightbox__stage">
							<img class="pal-noc-lightbox__img" src="" alt="">
						</div>
						<div class="pal-noc-lightbox__counter"></div>
						<div class="pal-noc-lightbox__thumbs">
							<?php foreach ( $all_urls as $i => $url ) : ?>
								<div class="pal-noc-lightbox__thumb<?php echo 0 === $i ? ' is-active' : ''; ?>" data-lb-index="<?php echo (int) $i; ?>" style="background-image:url('<?php echo esc_url( $url ); ?>');"></div>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="pal-noc-lightbox__stage">
							<p style="color:#fff;text-align:center;font-size:16px;">Add gallery photos in the Elementor editor to see them here.</p>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<?php if ( ! empty( $all_urls ) ) : ?>
				<script type="application/json" class="pal-noc-lightbox-data" data-for="<?php echo esc_attr( $uid ); ?>"><?php echo wp_json_encode( $all_urls ); ?></script>
			<?php endif; ?>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Night of Champions — CTA (Centered Layout with Watermark)
 * ==================================================================== */

/* ======================================================================
 *  NOC Donation Banner  (inline bar: icon + text + button)
 * ==================================================================== */

class NPAL_Widget_NOC_Donate_Banner extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_noc_donate_banner'; }
	public function get_title()    { return __( 'PAL NOC Donation Banner', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-heart'; }
	public function get_keywords() { return array( 'pal', 'champions', 'donate', 'banner', 'support', 'night' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Donation Banner', 'nashua-pal-core' ),
		) );

		$this->add_control( 'icon', array(
			'label'   => __( 'Icon', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::ICONS,
			'default' => array( 'value' => 'fas fa-heart', 'library' => 'fa-solid' ),
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => "Support this year's Night of Champions",
		) );

		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Your donation helps celebrate local heroes and fund programs that empower Nashua\'s youth.',
			'rows'    => 3,
		) );

		$this->add_control( 'btn_label', array(
			'label'   => __( 'Button Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Donate Today',
		) );

		$this->add_control( 'btn_url', array(
			'label'   => __( 'Button URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => array( 'url' => '/donate/' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$url    = ! empty( $s['btn_url']['url'] ) ? $s['btn_url']['url'] : '/donate/';
		$target = ! empty( $s['btn_url']['is_external'] ) ? ' target="_blank"' : '';
		$rel    = ! empty( $s['btn_url']['nofollow'] ) ? ' rel="nofollow"' : '';
		?>
		<div class="pal-noc-donate-banner">
			<div class="pal-noc-donate-banner__inner">
				<div class="pal-noc-donate-banner__icon">
					<?php \Elementor\Icons_Manager::render_icon( $s['icon'], array( 'aria-hidden' => 'true' ) ); ?>
				</div>
				<div class="pal-noc-donate-banner__text">
					<?php if ( $s['heading'] ) : ?>
						<strong class="pal-noc-donate-banner__heading"><?php echo esc_html( $s['heading'] ); ?></strong>
					<?php endif; ?>
					<?php if ( $s['description'] ) : ?>
						<span class="pal-noc-donate-banner__desc"><?php echo esc_html( $s['description'] ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $s['btn_label'] ) : ?>
					<a href="<?php echo esc_url( $url ); ?>" class="pal-noc-donate-banner__btn"<?php echo $target . $rel; ?>><?php echo esc_html( $s['btn_label'] ); ?> &rarr;</a>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}

/* ======================================================================
 *  NOC CTA Banner (full-width with background image)
 * ==================================================================== */

class NPAL_Widget_NOC_CTA extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_noc_cta'; }
	public function get_title()    { return __( 'PAL NOC CTA Banner', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-call-to-action'; }
	public function get_keywords() { return array( 'pal', 'champions', 'cta', 'donate', 'support' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'CTA Banner', 'nashua-pal-core' ),
		) );

		$this->add_control( 'bg_image', array(
			'label'       => __( 'Background Image', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::MEDIA,
			'default'     => array( 'url' => '' ),
			'description' => __( 'Leave empty to use the default PAL watermark background.', 'nashua-pal-core' ),
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Support the Mission',
		) );
		$this->add_control( 'tagline', array(
			'label'   => __( 'Tagline', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Your generosity helps PAL reach more kids through sports, mentorship, and community programs.',
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
				array( 'btn_label' => 'Donate Now',        'btn_url' => '/donate/',              'btn_style' => 'primary' ),
				array( 'btn_label' => 'Become a Sponsor',  'btn_url' => '/get-involved/partner/', 'btn_style' => 'outline' ),
			),
			'title_field' => '{{{ btn_label }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$bg_url = ! empty( $s['bg_image']['url'] ) ? $s['bg_image']['url'] : NPAL_PLUGIN_URL . 'assets/img/noc-cta-bg.png';
		?>
		<section class="pal-noc-cta" style="background-image:url('<?php echo esc_url( $bg_url ); ?>');">
			<div class="pal-noc-cta__inner">
				<?php if ( $s['title'] ) : ?>
					<h2 class="pal-noc-cta__title"><?php echo esc_html( $s['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $s['tagline'] ) : ?>
					<p class="pal-noc-cta__tagline"><?php echo esc_html( $s['tagline'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $s['buttons'] ) ) : ?>
					<div class="pal-noc-cta__buttons">
						<?php foreach ( (array) $s['buttons'] as $btn ) :
							$cls = 'primary' === $btn['btn_style'] ? 'pal-noc-cta__btn--primary' : 'pal-noc-cta__btn--outline';
							?>
							<a href="<?php echo esc_url( $btn['btn_url'] ); ?>" class="pal-noc-cta__btn <?php echo esc_attr( $cls ); ?>"><?php echo esc_html( $btn['btn_label'] ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
