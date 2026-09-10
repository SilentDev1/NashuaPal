<?php
/**
 * Nashua PAL Elementor widgets — Partner List.
 *
 * @package NashuaPalCore
 * @since   1.40.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ======================================================================
 *  Partner List Hero
 * ==================================================================== */

class NPAL_Widget_Partner_List_Hero extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_partner_list_hero'; }
	public function get_title()    { return __( 'PAL Partner List Hero', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-banner'; }
	public function get_keywords() { return array( 'pal', 'partner', 'list', 'hero', 'tier', 'banner' ); }

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
			'default' => "Nashua PAL\xe2\x80\x99s Partners",
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Our partners play a vital role in helping Nashua PAL provide life-changing programs for youth. Their commitment strengthens our community every day.",
		) );
		$this->add_control( 'show_tier_selector', array(
			'label'   => __( 'Show Tier Selector', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'false',
			'options' => array(
				'true'  => __( 'Yes', 'nashua-pal-core' ),
				'false' => __( 'No', 'nashua-pal-core' ),
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$show_tier = 'true' === $s['show_tier_selector'];

		// Determine selected tier.
		$tiers    = NPAL_CPT_Partner_List::get_tiers();
		$selected = ! empty( $_GET['partner_tier'] ) ? sanitize_key( $_GET['partner_tier'] ) : '';

		if ( empty( $tiers ) ) {
			$tiers = array( array_key_first( NPAL_CPT_Partner_List::TIERS ) );
		}
		if ( ! $selected || ! in_array( $selected, $tiers, true ) ) {
			$selected = $tiers[0];
		}

		$all_tier_labels = NPAL_CPT_Partner_List::TIERS;
		?>
		<section class="pal-partner-list-hero">
			<div class="pal-partner-list-hero__pattern"></div>
			<div class="pal-partner-list-hero__container">
				<div class="pal-partner-list-hero__content">
					<?php if ( $s['cursive_line'] ) : ?>
						<span class="pal-partner-list-hero__cursive"><?php echo esc_html( $s['cursive_line'] ); ?></span>
					<?php endif; ?>
					<?php if ( $s['title'] ) : ?>
						<h1 class="pal-partner-list-hero__title"><?php echo esc_html( $s['title'] ); ?></h1>
					<?php endif; ?>
					<?php if ( $s['description'] ) : ?>
						<p class="pal-partner-list-hero__desc"><?php echo esc_html( $s['description'] ); ?></p>
					<?php endif; ?>
					<?php if ( $show_tier && count( $tiers ) > 0 ) : ?>
						<div class="pal-partner-list-hero__tiers" role="tablist">
							<?php foreach ( $tiers as $t ) :
								$is_active  = $t === $selected;
								$tier_label = isset( $all_tier_labels[ $t ] ) ? $all_tier_labels[ $t ] : $t;
								?>
								<button type="button"
									class="pal-partner-list-hero__tier-pill<?php echo $is_active ? ' is-active' : ''; ?>"
									data-partner-tier="<?php echo esc_attr( $t ); ?>"
									<?php echo $is_active ? 'aria-selected="true"' : ''; ?>
								><?php echo esc_html( $tier_label ); ?></button>
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
 *  Partner List Info Banner
 * ==================================================================== */

class NPAL_Widget_Partner_List_Info_Banner extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_partner_list_info_banner'; }
	public function get_title()    { return __( 'PAL Partner List Info Banner', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-circle'; }
	public function get_keywords() { return array( 'pal', 'partner', 'list', 'info', 'banner' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Info Banner', 'nashua-pal-core' ),
		) );

		$this->add_control( 'icon', array(
			'label'   => __( 'Icon Class', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'fas fa-handshake',
		) );
		$this->add_control( 'text', array(
			'label'   => __( 'Banner Text', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "We are grateful for the commitment of our partners. Your support makes it possible for Nashua PAL to serve over 5,000 youth each year through athletics, education, and mentorship programs.",
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="pal-partner-list-info-banner">
			<div class="pal-partner-list-info-banner__inner">
				<?php if ( $s['icon'] ) : ?>
					<div class="pal-partner-list-info-banner__icon"><i class="<?php echo esc_attr( $s['icon'] ); ?>"></i></div>
				<?php endif; ?>
				<?php if ( $s['text'] ) : ?>
					<p class="pal-partner-list-info-banner__text"><?php echo esc_html( $s['text'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Partner List (wraps [pal_partner_list])
 * ==================================================================== */

class NPAL_Widget_Partner_List extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'pal_partner_list';

	public function get_name()     { return 'pal_partner_list_widget'; }
	public function get_title()    { return __( 'PAL Partner List', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-editor-list-ul'; }
	public function get_keywords() { return array( 'pal', 'partner', 'list', 'table', 'names', 'tier' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Partner List', 'nashua-pal-core' ),
		) );

		$tier_options = array( '' => __( 'Auto-detect from URL or first tier', 'nashua-pal-core' ) );
		foreach ( NPAL_CPT_Partner_List::TIERS as $slug => $label ) {
			$tier_options[ $slug ] = $label;
		}

		$this->add_control( 'tier', array(
			'label'   => __( 'Partner Tier', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '',
			'options' => $tier_options,
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
			'tier'     => isset( $s['tier'] ) ? $s['tier'] : '',
			'per_page' => isset( $s['per_page'] ) ? $s['per_page'] : 100,
		);
	}
}

/* ======================================================================
 *  Partner List CTA
 * ==================================================================== */

class NPAL_Widget_Partner_List_CTA extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_partner_list_cta'; }
	public function get_title()    { return __( 'PAL Partner List CTA', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-call-to-action'; }
	public function get_keywords() { return array( 'pal', 'partner', 'list', 'cta', 'join' ); }

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
			'default' => "Join our community of partners and help Nashua PAL continue providing life-changing programs for local youth.",
		) );
		$this->add_control( 'btn_label', array(
			'label'   => __( 'Button Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Become a Partner Today',
		) );
		$this->add_control( 'btn_url', array(
			'label'   => __( 'Button URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '/partner/',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="pal-partner-list-cta">
			<div class="pal-partner-list-cta__inner">
				<?php if ( $s['title'] ) : ?>
					<h2 class="pal-partner-list-cta__title"><?php echo esc_html( $s['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $s['description'] ) : ?>
					<p class="pal-partner-list-cta__desc"><?php echo esc_html( $s['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( ! empty( $s['btn_label'] ) && ! empty( $s['btn_url'] ) ) : ?>
					<a href="<?php echo esc_url( $s['btn_url'] ); ?>" class="pal-partner-list-cta__btn"><?php echo esc_html( $s['btn_label'] ); ?></a>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  Featured Partners (logo grid)
 * ==================================================================== */

class NPAL_Widget_Featured_Partners extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_featured_partners'; }
	public function get_title()    { return __( 'PAL Featured Partners', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-logo'; }
	public function get_keywords() { return array( 'pal', 'partner', 'featured', 'logo', 'sponsor' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Featured Partners', 'nashua-pal-core' ),
		) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Heading', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Our Featured Partners',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s        = $this->get_settings_for_display();
		$partners = get_option( 'npal_featured_partners', array() );

		if ( empty( $partners ) || ! is_array( $partners ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				echo '<p style="text-align:center;padding:20px;color:#888;">'
					. esc_html__( 'No featured partners configured. Go to Partners → Featured Partners to add some.', 'nashua-pal-core' )
					. '</p>';
			}
			return;
		}
		?>
		<section class="pal-featured-partners">
			<?php if ( ! empty( $s['heading'] ) ) : ?>
				<h2 class="pal-featured-partners__heading"><?php echo esc_html( $s['heading'] ); ?></h2>
			<?php endif; ?>
			<div class="pal-featured-partners__grid">
				<?php foreach ( $partners as $p ) :
					$has_url = ! empty( $p['url'] );
					$tag_open  = $has_url ? '<a href="' . esc_url( $p['url'] ) . '" target="_blank" rel="noopener noreferrer" class="pal-featured-partners__link">' : '<div class="pal-featured-partners__link">';
					$tag_close = $has_url ? '</a>' : '</div>';
					?>
					<div class="pal-featured-partners__item">
						<?php echo $tag_open; ?>
							<?php if ( ! empty( $p['logo_url'] ) ) : ?>
								<img src="<?php echo esc_url( $p['logo_url'] ); ?>" alt="<?php echo esc_attr( $p['name'] ); ?>" class="pal-featured-partners__logo" loading="lazy">
							<?php endif; ?>
							<?php if ( ! empty( $p['name'] ) ) : ?>
								<span class="pal-featured-partners__name"><?php echo esc_html( $p['name'] ); ?></span>
							<?php endif; ?>
						<?php echo $tag_close; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
