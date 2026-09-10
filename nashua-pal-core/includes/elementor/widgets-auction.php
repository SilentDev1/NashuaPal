<?php
/**
 * Nashua PAL Elementor widgets — Live Auction.
 *
 * @package NashuaPalCore
 * @since   1.28.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ======================================================================
 *  Auction Hero
 * ==================================================================== */

class NPAL_Widget_Auction_Hero extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_auction_hero'; }
	public function get_title()    { return __( 'PAL Auction Hero', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-banner'; }
	public function get_keywords() { return array( 'pal', 'auction', 'hero', 'gala', 'champions' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'hero_content', array(
			'label' => __( 'Hero Content', 'nashua-pal-core' ),
		) );

		$this->add_control( 'bg_image', array(
			'label'       => __( 'Background Image', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::MEDIA,
			'default'     => array( 'url' => '' ),
		) );
		$this->add_responsive_control( 'hero_height', array(
			'label'      => __( 'Hero Height (px)', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 300, 'max' => 900, 'step' => 10 ) ),
			'default'    => array( 'size' => 500, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .pal-auction-hero' => 'min-height: {{SIZE}}{{UNIT}};' ),
		) );
		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'NIGHT OF CHAMPIONS GALA',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Live Auction',
		) );
		$this->add_control( 'subtitle', array(
			'label'       => __( 'Subtitle (cursive)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => 'Extraordinary Experiences, Lasting Impact',
			'description' => __( 'Displayed in a script/cursive font.', 'nashua-pal-core' ),
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Our live auction features exclusive, once-in-a-lifetime experiences and luxury items carefully curated for tonight\xe2\x80\x99s guests. Every bid directly supports Nashua PAL\xe2\x80\x99s youth programs.",
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$bg_url = ! empty( $s['bg_image']['url'] ) ? $s['bg_image']['url'] : '';
		$has_bg = (bool) $bg_url;
		$bg_css = $has_bg ? "background-image:url('" . esc_url( $bg_url ) . "');" : '';
		$cls    = 'pal-auction-hero' . ( $has_bg ? ' pal-auction-hero--has-bg' : '' );
		?>
		<section class="<?php echo esc_attr( $cls ); ?>" style="<?php echo esc_attr( $bg_css ); ?>">
			<div class="pal-auction-hero__overlay"></div>
			<div class="pal-auction-hero__container">
				<div class="pal-auction-hero__content">
					<?php if ( $s['eyebrow'] ) : ?>
						<span class="pal-auction-hero__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span>
					<?php endif; ?>
					<?php if ( $s['title'] ) : ?>
						<h1 class="pal-auction-hero__title"><?php echo esc_html( $s['title'] ); ?></h1>
					<?php endif; ?>
					<?php if ( $s['subtitle'] ) : ?>
						<p class="pal-auction-hero__subtitle"><?php echo esc_html( $s['subtitle'] ); ?></p>
					<?php endif; ?>
					<?php if ( $s['description'] ) : ?>
						<p class="pal-auction-hero__desc"><?php echo esc_html( $s['description'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Auction Packages Grid (wraps [champions_auction] shortcode)
 * ==================================================================== */

class NPAL_Widget_Auction_Packages extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'champions_auction';

	public function get_name()     { return 'pal_auction_packages'; }
	public function get_title()    { return __( 'PAL Auction Packages', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-gallery-grid'; }
	public function get_keywords() { return array( 'pal', 'auction', 'packages', 'grid', 'champions' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Auction Packages', 'nashua-pal-core' ),
		) );

		$this->add_control( 'year', array(
			'label'       => __( 'Year', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. 2026 (blank = all)', 'nashua-pal-core' ),
		) );
		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'FEATURED AUCTION PACKAGES',
		) );
		$this->add_control( 'count', array(
			'label'   => __( 'Number of Packages', 'nashua-pal-core' ),
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
		$this->add_control( 'featured_only', array(
			'label'   => __( 'Featured Only', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'false',
			'options' => array( 'false' => __( 'No', 'nashua-pal-core' ), 'true' => __( 'Yes', 'nashua-pal-core' ) ),
		) );
		$this->add_control( 'view_all_label', array(
			'label'   => __( 'View All Button Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'VIEW ALL LIVE AUCTION PACKAGES',
		) );
		$this->add_control( 'view_all_url', array(
			'label'       => __( 'View All Button URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => '/live-auction/',
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'year'           => isset( $s['year'] )           ? $s['year']                 : '',
			'heading'        => isset( $s['heading'] )        ? $s['heading']               : '',
			'count'          => isset( $s['count'] )          ? (string) $s['count']        : '6',
			'columns'        => isset( $s['columns'] )        ? (string) $s['columns']      : '3',
			'featured_only'  => isset( $s['featured_only'] )  ? $s['featured_only']         : 'false',
			'view_all_label' => isset( $s['view_all_label'] ) ? $s['view_all_label']        : '',
			'view_all_url'   => isset( $s['view_all_url'] )   ? $s['view_all_url']          : '',
		);
	}
}

/* ======================================================================
 *  Auction Info Strip
 * ==================================================================== */

class NPAL_Widget_Auction_Info_Strip extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_auction_info_strip'; }
	public function get_title()    { return __( 'PAL Auction Info Strip', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-box'; }
	public function get_keywords() { return array( 'pal', 'auction', 'info', 'strip', 'icons' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Info Strip', 'nashua-pal-core' ),
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'icon', array(
			'label'       => __( 'Icon Class', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => 'fas fa-heart',
			'description' => __( 'Font Awesome class, e.g. fas fa-gavel', 'nashua-pal-core' ),
		) );
		$repeater->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Info Item',
		) );
		$repeater->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Description text here.',
		) );

		$this->add_control( 'items', array(
			'label'       => __( 'Info Items', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(
				array(
					'icon'        => 'fas fa-heart',
					'title'       => 'Every Bid Makes a Difference',
					'description' => '100% of auction proceeds go directly to funding Nashua PAL youth programs, sports leagues, and mentorship initiatives.',
				),
				array(
					'icon'        => 'fas fa-star',
					'title'       => 'Exclusive to In-Person Guests',
					'description' => 'These premium auction items are available only to tonight\'s gala attendees. Don\'t miss your chance to bid on something extraordinary.',
				),
				array(
					'icon'        => 'fas fa-gavel',
					'title'       => 'Don\'t Miss Out!',
					'description' => 'Our live auctioneer will guide you through each package. Raise your paddle high and take home an unforgettable experience!',
				),
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
		<section class="pal-auction-info">
			<div class="pal-auction-info__inner">
				<?php foreach ( $items as $item ) : ?>
					<div class="pal-auction-info__item">
						<?php if ( ! empty( $item['icon'] ) ) : ?>
							<div class="pal-auction-info__icon"><i class="<?php echo esc_attr( $item['icon'] ); ?>"></i></div>
						<?php endif; ?>
						<?php if ( ! empty( $item['title'] ) ) : ?>
							<h3 class="pal-auction-info__title"><?php echo esc_html( $item['title'] ); ?></h3>
						<?php endif; ?>
						<?php if ( ! empty( $item['description'] ) ) : ?>
							<p class="pal-auction-info__desc"><?php echo esc_html( $item['description'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
