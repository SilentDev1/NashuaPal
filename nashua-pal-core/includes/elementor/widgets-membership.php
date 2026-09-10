<?php
/**
 * Nashua PAL Elementor widgets — Membership Page.
 *
 * @package NashuaPalCore
 * @since   1.35.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ======================================================================
 *  1. Membership Header — title, decorative divider, subtitle, description
 * ==================================================================== */

class NPAL_Widget_Membership_Header extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_membership_header'; }
	public function get_title()    { return __( 'PAL Membership Header', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-heading'; }
	public function get_keywords() { return array( 'pal', 'membership', 'header', 'title' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'header_content', array(
			'label' => __( 'Header Content', 'nashua-pal-core' ),
		) );

		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'PAL Membership',
		) );
		$this->add_control( 'subtitle', array(
			'label'   => __( 'Subtitle (bold)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Nashua PAL has 16 impactful programs focused on Education, Activity, and Leadership.',
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Click below to join an elite group of youth leaders! Registration is available online for the following programs.',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="pal-membership-header">
			<div class="pal-membership-header__inner">
				<?php if ( $s['title'] ) : ?>
					<h1 class="pal-membership-header__title"><?php echo esc_html( $s['title'] ); ?></h1>
				<?php endif; ?>
				<div class="pal-membership-header__divider">
					<span class="pal-membership-header__line"></span>
					<i class="fas fa-star"></i>
					<span class="pal-membership-header__line"></span>
				</div>
				<?php if ( $s['subtitle'] ) : ?>
					<p class="pal-membership-header__subtitle"><?php echo esc_html( $s['subtitle'] ); ?></p>
				<?php endif; ?>
				<?php if ( $s['description'] ) : ?>
					<p class="pal-membership-header__desc"><?php echo esc_html( $s['description'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  2. Program Sign-Up Cards — 2-column cards with program lists + buttons
 * ==================================================================== */

class NPAL_Widget_Membership_Programs extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_membership_programs'; }
	public function get_title()    { return __( 'PAL Membership Programs', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-posts-grid'; }
	public function get_keywords() { return array( 'pal', 'membership', 'programs', 'signup' ); }

	protected function register_content_controls() {
		/* ---- Card 1 ---- */
		$this->start_controls_section( 'card1', array(
			'label' => __( 'Card 1 (Left)', 'nashua-pal-core' ),
		) );
		$this->add_control( 'c1_icon', array( 'label' => __( 'Icon (FA class)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'fas fa-shield-alt' ) );
		$this->add_control( 'c1_title', array( 'label' => __( 'Title', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'YOUTH PROGRAMS' ) );
		$this->add_control( 'c1_subtitle', array( 'label' => __( 'Subtitle', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'For Ages 6–17' ) );
		$this->add_control( 'c1_programs', array( 'label' => __( 'Programs (one per line)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "PAL Basketball – St Pat's Gym (Church)\nBoxing Gym (Youth)\nJuvenile Court Diversion\nYouth Safe Haven Afterschool/Summer" ) );
		$this->add_control( 'c1_btn_label', array( 'label' => __( 'Button Label', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Sign Up for Youth Programs' ) );
		$this->add_control( 'c1_btn_url', array( 'label' => __( 'Button URL', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#' ) );
		$this->end_controls_section();

		/* ---- Card 2 ---- */
		$this->start_controls_section( 'card2', array(
			'label' => __( 'Card 2 (Right)', 'nashua-pal-core' ),
		) );
		$this->add_control( 'c2_icon', array( 'label' => __( 'Icon (FA class)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'fas fa-dumbbell' ) );
		$this->add_control( 'c2_title', array( 'label' => __( 'Title', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'ADULT / ATHLETIC PROGRAMS' ) );
		$this->add_control( 'c2_subtitle', array( 'label' => __( 'Subtitle', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'For Ages 18+' ) );
		$this->add_control( 'c2_programs', array( 'label' => __( 'Programs (one per line)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "PAL Basketball – St Pat's Gym (Adult)\nPAL Boxing\nCross Country & Track & Field\nPAL Storm Football & Cheer" ) );
		$this->add_control( 'c2_btn_label', array( 'label' => __( 'Button Label', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Sign Up for Adult Programs' ) );
		$this->add_control( 'c2_btn_url', array( 'label' => __( 'Button URL', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$cards = array(
			array( 'icon' => $s['c1_icon'], 'title' => $s['c1_title'], 'subtitle' => $s['c1_subtitle'], 'programs' => $s['c1_programs'], 'btn_label' => $s['c1_btn_label'], 'btn_url' => $s['c1_btn_url'] ),
			array( 'icon' => $s['c2_icon'], 'title' => $s['c2_title'], 'subtitle' => $s['c2_subtitle'], 'programs' => $s['c2_programs'], 'btn_label' => $s['c2_btn_label'], 'btn_url' => $s['c2_btn_url'] ),
		);
		?>
		<section class="pal-membership-programs">
			<div class="pal-membership-programs__grid">
				<?php foreach ( $cards as $card ) :
					$items = array_filter( array_map( 'trim', explode( "\n", $card['programs'] ) ) );
				?>
					<div class="pal-membership-program-card">
						<div class="pal-membership-program-card__header">
							<?php if ( $card['icon'] ) : ?><i class="<?php echo esc_attr( $card['icon'] ); ?>"></i><?php endif; ?>
							<h3 class="pal-membership-program-card__title"><?php echo esc_html( $card['title'] ); ?></h3>
							<?php if ( $card['subtitle'] ) : ?>
								<span class="pal-membership-program-card__sub"><?php echo esc_html( $card['subtitle'] ); ?></span>
							<?php endif; ?>
						</div>
						<div class="pal-membership-program-card__body">
							<?php if ( ! empty( $items ) ) : ?>
								<ul class="pal-membership-program-card__list">
									<?php foreach ( $items as $item ) : ?>
										<li><i class="fas fa-check-circle"></i> <?php echo esc_html( $item ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<?php if ( $card['btn_label'] ) : ?>
								<a href="<?php echo esc_url( $card['btn_url'] ); ?>" class="pal-membership-btn pal-membership-btn--gold">
									<i class="fas fa-user-plus"></i> <?php echo esc_html( $card['btn_label'] ); ?>
								</a>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  3. Values Strip — gold background, 4 icon columns
 * ==================================================================== */

class NPAL_Widget_Membership_Values extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_membership_values'; }
	public function get_title()    { return __( 'PAL Membership Values', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-circle-o'; }
	public function get_keywords() { return array( 'pal', 'membership', 'values', 'strip' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'values_content', array(
			'label' => __( 'Values', 'nashua-pal-core' ),
		) );

		$rep = new \Elementor\Repeater();
		$rep->add_control( 'icon',  array( 'label' => __( 'Icon (FA class)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'fas fa-star' ) );
		$rep->add_control( 'title', array( 'label' => __( 'Title', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Value' ) );
		$rep->add_control( 'desc',  array( 'label' => __( 'Description', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => '' ) );

		$this->add_control( 'values', array(
			'label'       => __( 'Values', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'default'     => array(
				array( 'icon' => 'fas fa-shield-alt',     'title' => 'Build Confidence',             'desc' => 'Programs that help youth develop self-esteem and resilience.' ),
				array( 'icon' => 'fas fa-heartbeat',      'title' => 'Stay Active',                  'desc' => 'Athletics and fitness activities for all ages and abilities.' ),
				array( 'icon' => 'fas fa-graduation-cap', 'title' => 'Learn & Grow',                 'desc' => 'Education and mentorship programs for personal development.' ),
				array( 'icon' => 'fas fa-users',          'title' => 'Be Part of Something Bigger',  'desc' => 'Join a community dedicated to making a difference.' ),
			),
			'title_field' => '{{{ title }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="pal-membership-values">
			<div class="pal-membership-values__inner">
				<?php if ( ! empty( $s['values'] ) ) : ?>
					<?php foreach ( $s['values'] as $val ) : ?>
						<div class="pal-membership-value">
							<?php if ( $val['icon'] ) : ?>
								<div class="pal-membership-value__icon"><i class="<?php echo esc_attr( $val['icon'] ); ?>"></i></div>
							<?php endif; ?>
							<?php if ( $val['title'] ) : ?>
								<h4 class="pal-membership-value__title"><?php echo esc_html( $val['title'] ); ?></h4>
							<?php endif; ?>
							<?php if ( $val['desc'] ) : ?>
								<p class="pal-membership-value__desc"><?php echo esc_html( $val['desc'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  4. Story Section — photo left, cursive + heading + text right
 * ==================================================================== */

class NPAL_Widget_Membership_Story extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_membership_story'; }
	public function get_title()    { return __( 'PAL Membership Story', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-image-box'; }
	public function get_keywords() { return array( 'pal', 'membership', 'story', 'hero' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'story_content', array(
			'label' => __( 'Story Section', 'nashua-pal-core' ),
		) );

		$this->add_control( 'image', array(
			'label'   => __( 'Photo', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );
		$this->add_control( 'script_text', array(
			'label'   => __( 'Script Line (cursive)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'You Are',
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'the Hero of Your Own Story',
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'At Nashua PAL, every child has the power to achieve their dreams. We provide a safe, supportive environment where kids can learn, grow, and discover their potential through sports, education, and community.',
		) );
		$this->add_control( 'closing', array(
			'label'   => __( 'Closing (bold)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Together, we build brighter futures.',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$img = ! empty( $s['image']['url'] ) ? $s['image']['url'] : '';
		?>
		<section class="pal-membership-story">
			<div class="pal-membership-story__inner">
				<?php if ( $img ) : ?>
					<div class="pal-membership-story__photo">
						<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $s['title'] ); ?>" />
					</div>
				<?php endif; ?>
				<div class="pal-membership-story__text">
					<?php if ( $s['script_text'] ) : ?>
						<span class="pal-membership-story__script"><?php echo esc_html( $s['script_text'] ); ?></span>
					<?php endif; ?>
					<?php if ( $s['title'] ) : ?>
						<h2 class="pal-membership-story__title"><?php echo esc_html( $s['title'] ); ?></h2>
					<?php endif; ?>
					<?php if ( $s['description'] ) : ?>
						<p class="pal-membership-story__desc"><?php echo esc_html( $s['description'] ); ?></p>
					<?php endif; ?>
					<?php if ( $s['closing'] ) : ?>
						<p class="pal-membership-story__closing"><?php echo esc_html( $s['closing'] ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  5. Notice Banner — bilingual dismissible notice with bell icon
 * ==================================================================== */

class NPAL_Widget_Membership_Notice extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_membership_notice'; }
	public function get_title()    { return __( 'PAL Membership Notice', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-alert'; }
	public function get_keywords() { return array( 'pal', 'membership', 'notice', 'alert' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'notice_content', array(
			'label' => __( 'Notice', 'nashua-pal-core' ),
		) );

		$this->add_control( 'icon', array( 'label' => __( 'Icon (FA class)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'fas fa-bell' ) );
		$this->add_control( 'text_left', array(
			'label'   => __( 'Left Text (English)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Please note, PAL\'s Youth Safe Haven hours may change due to holidays. Monday – Thursday, 2–8pm Friday, 2–5pm',
		) );
		$this->add_control( 'text_right', array(
			'label'   => __( 'Right Text (Spanish)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Tenga en cuenta que el horario de Youth Safe Haven puede cambiar debido a los días festivos. De Lunes a Jueves de 2 a 8pm, Viernes, 2–5pm',
		) );
		$this->add_control( 'dismissible', array(
			'label'   => __( 'Show Close Button', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$uid = 'notice-' . $this->get_id();
		?>
		<section class="pal-membership-notice" id="<?php echo esc_attr( $uid ); ?>">
			<div class="pal-membership-notice__inner">
				<?php if ( $s['icon'] ) : ?>
					<div class="pal-membership-notice__icon"><i class="<?php echo esc_attr( $s['icon'] ); ?>"></i></div>
				<?php endif; ?>
				<div class="pal-membership-notice__cols">
					<?php if ( $s['text_left'] ) : ?>
						<div class="pal-membership-notice__col"><?php echo wp_kses_post( nl2br( esc_html( $s['text_left'] ) ) ); ?></div>
					<?php endif; ?>
					<?php if ( $s['text_right'] ) : ?>
						<div class="pal-membership-notice__col"><?php echo wp_kses_post( nl2br( esc_html( $s['text_right'] ) ) ); ?></div>
					<?php endif; ?>
				</div>
				<?php if ( 'yes' === $s['dismissible'] ) : ?>
					<button type="button" class="pal-membership-notice__close" onclick="document.getElementById('<?php echo esc_js( $uid ); ?>').style.display='none'" aria-label="Close"><i class="fas fa-times"></i></button>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  6. Info Cards — 2-column bilingual info cards (schedule, application, etc.)
 * ==================================================================== */

class NPAL_Widget_Membership_Info_Cards extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_membership_info_cards'; }
	public function get_title()    { return __( 'PAL Membership Info Cards', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-box'; }
	public function get_keywords() { return array( 'pal', 'membership', 'info', 'schedule', 'cards' ); }

	protected function register_content_controls() {
		/* ---- Card 1 ---- */
		$this->start_controls_section( 'card1', array(
			'label' => __( 'Card 1 (Left)', 'nashua-pal-core' ),
		) );
		$this->add_control( 'c1_icon', array( 'label' => __( 'Icon (FA class)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'fas fa-calendar-alt' ) );
		$this->add_control( 'c1_title', array( 'label' => __( 'Title', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'PAL Schedule Update!' ) );
		$this->add_control( 'c1_text', array( 'label' => __( 'Text', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Please note the updated PAL Youth Safe Haven hours:' ) );
		$this->add_control( 'c1_highlight', array( 'label' => __( 'Highlighted Text', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "Monday – Thursday, 2–8pm\nFriday, 2–5pm" ) );
		$this->add_control( 'c1_btn_label', array( 'label' => __( 'Button Label (optional)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'c1_btn_url', array( 'label' => __( 'Button URL', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#' ) );
		$this->end_controls_section();

		/* ---- Card 2 ---- */
		$this->start_controls_section( 'card2', array(
			'label' => __( 'Card 2 (Right)', 'nashua-pal-core' ),
		) );
		$this->add_control( 'c2_icon', array( 'label' => __( 'Icon (FA class)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'fas fa-clock' ) );
		$this->add_control( 'c2_title', array( 'label' => __( 'Title', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'PAL Cambios de Horario!' ) );
		$this->add_control( 'c2_text', array( 'label' => __( 'Text', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Tenga en cuenta las horas actualizadas de PAL Youth Safe Haven:' ) );
		$this->add_control( 'c2_highlight', array( 'label' => __( 'Highlighted Text', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => "De Lunes a Jueves, 2–8pm\nViernes, 2–5pm" ) );
		$this->add_control( 'c2_btn_label', array( 'label' => __( 'Button Label (optional)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'c2_btn_url', array( 'label' => __( 'Button URL', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$cards = array(
			array( 'icon' => $s['c1_icon'], 'title' => $s['c1_title'], 'text' => $s['c1_text'], 'highlight' => $s['c1_highlight'], 'btn_label' => $s['c1_btn_label'], 'btn_url' => $s['c1_btn_url'] ),
			array( 'icon' => $s['c2_icon'], 'title' => $s['c2_title'], 'text' => $s['c2_text'], 'highlight' => $s['c2_highlight'], 'btn_label' => $s['c2_btn_label'], 'btn_url' => $s['c2_btn_url'] ),
		);
		?>
		<section class="pal-membership-info-cards">
			<div class="pal-membership-info-cards__grid">
				<?php foreach ( $cards as $card ) : ?>
					<div class="pal-membership-info-card">
						<div class="pal-membership-info-card__top">
							<?php if ( $card['icon'] ) : ?><div class="pal-membership-info-card__icon"><i class="<?php echo esc_attr( $card['icon'] ); ?>"></i></div><?php endif; ?>
							<?php if ( $card['title'] ) : ?><h3 class="pal-membership-info-card__title"><?php echo esc_html( $card['title'] ); ?></h3><?php endif; ?>
						</div>
						<?php if ( $card['text'] ) : ?>
							<p class="pal-membership-info-card__text"><?php echo esc_html( $card['text'] ); ?></p>
						<?php endif; ?>
						<?php if ( $card['highlight'] ) : ?>
							<div class="pal-membership-info-card__highlight"><?php echo wp_kses_post( nl2br( esc_html( $card['highlight'] ) ) ); ?></div>
						<?php endif; ?>
						<?php if ( $card['btn_label'] ) : ?>
							<a href="<?php echo esc_url( $card['btn_url'] ); ?>" class="pal-membership-btn pal-membership-btn--gold"><?php echo esc_html( $card['btn_label'] ); ?></a>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}

/* ======================================================================
 *  7. Application Cards — 2-column bilingual application link cards
 * ==================================================================== */

class NPAL_Widget_Membership_Application extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_membership_application'; }
	public function get_title()    { return __( 'PAL Membership Application', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-form-horizontal'; }
	public function get_keywords() { return array( 'pal', 'membership', 'application', 'form' ); }

	protected function register_content_controls() {
		/* ---- Card 1 ---- */
		$this->start_controls_section( 'card1', array(
			'label' => __( 'Card 1 (Left / English)', 'nashua-pal-core' ),
		) );
		$this->add_control( 'c1_icon', array( 'label' => __( 'Icon (FA class)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'fas fa-phone-alt' ) );
		$this->add_control( 'c1_title', array( 'label' => __( 'Title', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Please make sure your phone number and email are correct!' ) );
		$this->add_control( 'c1_text', array( 'label' => __( 'Text', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Keep your contact info up to date so you receive important PAL updates and announcements.' ) );
		$this->add_control( 'c1_btn_label', array( 'label' => __( 'Button Label', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'PAL Youth Safe Haven Membership Application' ) );
		$this->add_control( 'c1_btn_url', array( 'label' => __( 'Button URL', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#' ) );
		$this->end_controls_section();

		/* ---- Card 2 ---- */
		$this->start_controls_section( 'card2', array(
			'label' => __( 'Card 2 (Right / Spanish)', 'nashua-pal-core' ),
		) );
		$this->add_control( 'c2_icon', array( 'label' => __( 'Icon (FA class)', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'fas fa-globe-americas' ) );
		$this->add_control( 'c2_title', array( 'label' => __( 'Title', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Por favor asegúrese de que su número de teléfono y correo sean correctos!' ) );
		$this->add_control( 'c2_text', array( 'label' => __( 'Text', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Mantenga su información de contacto actualizada para recibir actualizaciones y anuncios importantes de PAL.' ) );
		$this->add_control( 'c2_btn_label', array( 'label' => __( 'Button Label', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Formulario de Membresía de PAL Youth Safe Haven' ) );
		$this->add_control( 'c2_btn_url', array( 'label' => __( 'Button URL', 'nashua-pal-core' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '#' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$cards = array(
			array( 'icon' => $s['c1_icon'], 'title' => $s['c1_title'], 'text' => $s['c1_text'], 'btn_label' => $s['c1_btn_label'], 'btn_url' => $s['c1_btn_url'] ),
			array( 'icon' => $s['c2_icon'], 'title' => $s['c2_title'], 'text' => $s['c2_text'], 'btn_label' => $s['c2_btn_label'], 'btn_url' => $s['c2_btn_url'] ),
		);
		?>
		<section class="pal-membership-application">
			<div class="pal-membership-application__grid">
				<?php foreach ( $cards as $card ) : ?>
					<div class="pal-membership-app-card">
						<div class="pal-membership-app-card__top">
							<?php if ( $card['icon'] ) : ?><div class="pal-membership-app-card__icon"><i class="<?php echo esc_attr( $card['icon'] ); ?>"></i></div><?php endif; ?>
							<?php if ( $card['title'] ) : ?><h3 class="pal-membership-app-card__title"><?php echo esc_html( $card['title'] ); ?></h3><?php endif; ?>
						</div>
						<?php if ( $card['text'] ) : ?>
							<p class="pal-membership-app-card__text"><?php echo esc_html( $card['text'] ); ?></p>
						<?php endif; ?>
						<?php if ( $card['btn_label'] ) : ?>
							<a href="<?php echo esc_url( $card['btn_url'] ); ?>" class="pal-membership-link">
								<i class="fas fa-arrow-right"></i> <?php echo esc_html( $card['btn_label'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
