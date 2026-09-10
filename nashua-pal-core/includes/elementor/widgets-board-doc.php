<?php
/**
 * Nashua PAL Elementor widgets — Board Documents page.
 *
 * Five widgets for composing the password-protected Board Member page:
 *  - Board Page Hero (lock icon + title + subtitle)
 *  - Board Event Materials (wraps [pal_board_event_materials])
 *  - Board Documents Grid (wraps [pal_board_documents])
 *  - Board Meeting Minutes (wraps [pal_board_meeting_minutes])
 *  - Board Doc CTA Banner (gold "Looking for Older Documents?" banner)
 *
 * @package NashuaPalCore
 * @since   1.54.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ============================================================================
   Widget: Board Page Hero
   ========================================================================= */

class NPAL_Widget_Board_Page_Hero extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_board_page_hero'; }
	public function get_title()    { return __( 'PAL Board Page Hero', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-lock-user'; }
	public function get_keywords() { return array( 'pal', 'board', 'hero', 'protected', 'lock' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Hero', 'nashua-pal-core' ) ) );

		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Protected: PAL Board Member Page', 'nashua-pal-core' ),
		) );
		$this->add_control( 'subtitle', array(
			'label'   => __( 'Subtitle', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => __( 'Access important board documents, meeting minutes, and event materials. This page is exclusively for PAL board members.', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="pal-board-doc-hero">
			<div class="pal-board-doc-hero__inner">
				<div class="pal-board-doc-hero__lock">
					<i class="fas fa-lock"></i>
				</div>
				<h1 class="pal-board-doc-hero__title"><?php echo esc_html( $s['title'] ); ?></h1>
				<?php if ( $s['subtitle'] ) : ?>
					<p class="pal-board-doc-hero__subtitle"><?php echo esc_html( $s['subtitle'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ============================================================================
   Widget: Board Event Materials
   ========================================================================= */

class NPAL_Widget_Board_Event_Materials extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'pal_board_event_materials';

	public function get_name()     { return 'pal_board_event_materials'; }
	public function get_title()    { return __( 'PAL Board Event Materials', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-bullet-list'; }
	public function get_keywords() { return array( 'pal', 'board', 'event', 'materials', 'document' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Event Materials', 'nashua-pal-core' ),
		) );

		$this->add_control( 'count', array(
			'label'   => __( 'Number of Documents', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 50,
			'min'     => 1,
			'max'     => 200,
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'count' => isset( $s['count'] ) ? $s['count'] : 50,
		);
	}
}

/* ============================================================================
   Widget: Board Documents Grid
   ========================================================================= */

class NPAL_Widget_Board_Documents extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'pal_board_documents';

	public function get_name()     { return 'pal_board_documents'; }
	public function get_title()    { return __( 'PAL Board Documents Grid', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-gallery-grid'; }
	public function get_keywords() { return array( 'pal', 'board', 'documents', 'grid', 'year' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Board Documents', 'nashua-pal-core' ),
		) );

		$this->add_control( 'year', array(
			'label'       => __( 'Year', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'e.g. 2025 (blank = all)', 'nashua-pal-core' ),
		) );

		$this->add_control( 'show_filter', array(
			'label'   => __( 'Show Year Filter', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'true',
			'options' => array(
				'true'  => __( 'Yes', 'nashua-pal-core' ),
				'false' => __( 'No', 'nashua-pal-core' ),
			),
		) );

		$this->add_control( 'count', array(
			'label'   => __( 'Number of Documents', 'nashua-pal-core' ),
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

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'year'        => isset( $s['year'] ) ? $s['year'] : '',
			'show_filter' => isset( $s['show_filter'] ) ? $s['show_filter'] : 'true',
			'count'       => isset( $s['count'] ) ? $s['count'] : 50,
			'columns'     => isset( $s['columns'] ) ? $s['columns'] : '3',
		);
	}
}

/* ============================================================================
   Widget: Board Meeting Minutes
   ========================================================================= */

class NPAL_Widget_Board_Meeting_Minutes extends NPAL_Elementor_Widget_Base {

	protected $shortcode = 'pal_board_meeting_minutes';

	public function get_name()     { return 'pal_board_meeting_minutes'; }
	public function get_title()    { return __( 'PAL Board Meeting Minutes', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-table'; }
	public function get_keywords() { return array( 'pal', 'board', 'meeting', 'minutes', 'table' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Meeting Minutes', 'nashua-pal-core' ),
		) );

		$this->add_control( 'count', array(
			'label'   => __( 'Number of Documents', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 50,
			'min'     => 1,
			'max'     => 200,
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'count' => isset( $s['count'] ) ? $s['count'] : 50,
		);
	}
}

/* ============================================================================
   Widget: Board Doc CTA Banner
   ========================================================================= */

class NPAL_Widget_Board_Doc_CTA extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_board_doc_cta'; }
	public function get_title()    { return __( 'PAL Board Doc CTA', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-call-to-action'; }
	public function get_keywords() { return array( 'pal', 'board', 'cta', 'banner', 'documents' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'CTA Banner', 'nashua-pal-core' ) ) );

		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Looking for Older Documents?', 'nashua-pal-core' ),
		) );
		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => __( 'Contact the PAL office to request archived board documents, meeting minutes, or other historical records.', 'nashua-pal-core' ),
		) );
		$this->add_control( 'btn_label', array(
			'label'   => __( 'Button Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Contact Us', 'nashua-pal-core' ),
		) );
		$this->add_control( 'btn_url', array(
			'label'   => __( 'Button URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '/contact/',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="pal-board-doc-cta">
			<div class="pal-board-doc-cta__inner">
				<?php if ( $s['title'] ) : ?>
					<h2 class="pal-board-doc-cta__title"><?php echo esc_html( $s['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( $s['description'] ) : ?>
					<p class="pal-board-doc-cta__desc"><?php echo esc_html( $s['description'] ); ?></p>
				<?php endif; ?>
				<?php if ( $s['btn_label'] && $s['btn_url'] ) : ?>
					<a href="<?php echo esc_url( $s['btn_url'] ); ?>" class="pal-board-doc-cta__btn">
						<?php echo esc_html( $s['btn_label'] ); ?> <i class="fas fa-arrow-right"></i>
					</a>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
