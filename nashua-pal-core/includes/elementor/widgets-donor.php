<?php
/**
 * Nashua PAL Elementor widgets — Donors.
 *
 * @package NashuaPalCore
 * @since   1.36.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ======================================================================
 *  Donor Hero
 * ==================================================================== */

class NPAL_Widget_Donor_Hero extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_donor_hero'; }
	public function get_title()    { return __( 'PAL Donor Hero', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-banner'; }
	public function get_keywords() { return array( 'pal', 'donor', 'hero', 'thank', 'banner' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'hero_content', array(
			'label' => __( 'Hero Content', 'nashua-pal-core' ),
		) );

		$this->add_control( 'cursive_line', array(
			'label'   => __( 'Cursive Line', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Thank You to',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => "Nashua PAL\xe2\x80\x99s Donors",
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Your generous contributions help us provide life-changing programs for Nashua\xe2\x80\x99s youth. Every donation makes a difference in building stronger, safer communities.",
		) );
		$this->add_control( 'show_year_selector', array(
			'label'   => __( 'Show Fiscal Year Selector', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'true',
			'options' => array(
				'true'  => __( 'Yes', 'nashua-pal-core' ),
				'false' => __( 'No', 'nashua-pal-core' ),
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$show_year = 'true' === $s['show_year_selector'];

		// Determine selected year.
		$current_year = NPAL_CPT_Donor::current_fiscal_year();
		$selected     = ! empty( $_GET['donor_year'] ) ? absint( $_GET['donor_year'] ) : $current_year;
		$fiscal_years = NPAL_CPT_Donor::get_fiscal_years();
		if ( empty( $fiscal_years ) ) {
			$fiscal_years = array( (string) $current_year );
		}
		if ( ! in_array( (string) $selected, $fiscal_years, true ) ) {
			$selected = (int) $fiscal_years[0];
		}
		?>
		<section class="pal-donor-hero">
			<div class="pal-donor-hero__pattern"></div>
			<div class="pal-donor-hero__container">
				<div class="pal-donor-hero__content">
					<?php if ( $s['cursive_line'] ) : ?>
						<span class="pal-donor-hero__cursive"><?php echo esc_html( $s['cursive_line'] ); ?></span>
					<?php endif; ?>
					<?php if ( $s['title'] ) : ?>
						<h1 class="pal-donor-hero__title"><?php echo esc_html( $s['title'] ); ?></h1>
					<?php endif; ?>
					<?php if ( $s['description'] ) : ?>
						<p class="pal-donor-hero__desc"><?php echo esc_html( $s['description'] ); ?></p>
					<?php endif; ?>
					<?php if ( $show_year && count( $fiscal_years ) > 0 ) : ?>
						<div class="pal-donor-hero__years" role="tablist">
							<?php foreach ( $fiscal_years as $fy ) :
								$is_active = (int) $fy === $selected;
								$fy_label  = 'FY ' . esc_html( $fy );
								?>
								<button type="button"
									class="pal-donor-hero__year-pill<?php echo $is_active ? ' is-active' : ''; ?>"
									data-donor-year="<?php echo esc_attr( $fy ); ?>"
									<?php echo $is_active ? 'aria-selected="true"' : ''; ?>
								><?php echo $fy_label; ?></button>
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
 *  Donor Info Banner
 * ==================================================================== */

class NPAL_Widget_Donor_Info_Banner extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_donor_info_banner'; }
	public function get_title()    { return __( 'PAL Donor Info Banner', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-circle'; }
	public function get_keywords() { return array( 'pal', 'donor', 'info', 'banner', 'gratitude' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Info Banner', 'nashua-pal-core' ),
		) );

		$this->add_control( 'icon', array(
			'label'   => __( 'Icon Class', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'fas fa-hand-holding-heart',
		) );
		$this->add_control( 'text', array(
			'label'   => __( 'Banner Text', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "We are deeply grateful for the generosity of our donors. Your support makes it possible for Nashua PAL to serve over 5,000 youth each year through athletics, education, and mentorship programs.",
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="pal-donor-info-banner">
			<div class="pal-donor-info-banner__inner">
				<?php if ( $s['icon'] ) : ?>
					<div class="pal-donor-info-banner__icon"><i class="<?php echo esc_attr( $s['icon'] ); ?>"></i></div>
				<?php endif; ?>
				<?php if ( $s['text'] ) : ?>
					<p class="pal-donor-info-banner__text"><?php echo esc_html( $s['text'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Donor List (wraps [pal_donors])
 * ==================================================================== */

class NPAL_Widget_Donor_List extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'pal_donors';

	public function get_name()     { return 'pal_donor_list'; }
	public function get_title()    { return __( 'PAL Donor List', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-editor-list-ul'; }
	public function get_keywords() { return array( 'pal', 'donor', 'list', 'table', 'names' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Donor List', 'nashua-pal-core' ),
		) );

		$this->add_control( 'year', array(
			'label'       => __( 'Fiscal Year', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Blank = auto-detect from URL or current FY', 'nashua-pal-core' ),
		) );
		$this->add_control( 'per_page', array(
			'label'   => __( 'Names per Page', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 100,
			'min'     => 10,
			'max'     => 500,
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'year'     => isset( $s['year'] ) ? $s['year'] : '',
			'per_page' => isset( $s['per_page'] ) ? $s['per_page'] : 100,
		);
	}
}

/* ======================================================================
 *  Donor CTA
 * ==================================================================== */

class NPAL_Widget_Donor_CTA extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_donor_cta'; }
	public function get_title()    { return __( 'PAL Donor CTA', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-call-to-action'; }
	public function get_keywords() { return array( 'pal', 'donor', 'cta', 'donate', 'difference' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'CTA Bar', 'nashua-pal-core' ),
		) );

		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Together, We Make a Difference',
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Join our community of supporters and help Nashua PAL continue providing life-changing programs for local youth.",
		) );
		$this->add_control( 'btn_label', array(
			'label'   => __( 'Button Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Become a Donor Today',
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
		<section class="pal-donor-cta">
			<div class="pal-donor-cta__inner">
				<?php if ( $s['title'] ) : ?>
					<h2 class="pal-donor-cta__title"><?php echo esc_html( $s['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $s['description'] ) : ?>
					<p class="pal-donor-cta__desc"><?php echo esc_html( $s['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $s['btn_label'] ) && ! empty( $s['btn_url'] ) ) : ?>
					<a href="<?php echo esc_url( $s['btn_url'] ); ?>" class="pal-donor-cta__btn"><?php echo esc_html( $s['btn_label'] ); ?></a>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
