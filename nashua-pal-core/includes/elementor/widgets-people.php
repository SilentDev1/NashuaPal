<?php
/**
 * Nashua PAL Elementor widgets — Board & Staff / PAL People.
 *
 * Widgets (all render directly — no shortcodes):
 *  - PAL People Hero
 *  - PAL Values Bar
 *  - PAL Board Members Grid
 *  - PAL Team Members Directory
 *  - PAL Volunteer Leadership Grid
 *  - PAL People CTA
 *
 * @package NashuaPalCore
 * @since   1.20.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ======================================================================
 *  Shared helper: query people posts
 * ==================================================================== */

trait NPAL_People_Widget_Trait {

	protected function get_people( $post_type, $count = 50, $sort = 'title' ) {
		$args = array(
			'post_type'      => $post_type,
			'posts_per_page' => intval( $count ),
			'post_status'    => 'publish',
		);

		if ( 'meta_order' === $sort ) {
			$args['meta_key'] = '_pal_people_order';
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'ASC';
		} else {
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
		}

		$people = get_posts( $args );

		if ( 'last_name' === $sort ) {
			usort( $people, function ( $a, $b ) {
				$last_a = trim( substr( $a->post_title, strrpos( $a->post_title, ' ' ) + 1 ) );
				$last_b = trim( substr( $b->post_title, strrpos( $b->post_title, ' ' ) + 1 ) );
				return strcasecmp( $last_a, $last_b );
			} );
		}

		return $people;
	}

	/**
	 * Render circle-photo card grid (Board, Volunteers).
	 */
	protected function render_circle_grid( $people, $columns, $settings = array() ) {
		$show_role    = isset( $settings['show_role'] )    ? $settings['show_role']    : 'yes';
		$show_org     = isset( $settings['show_org'] )     ? $settings['show_org']     : 'no';
		$show_contact = isset( $settings['show_contact'] ) ? $settings['show_contact'] : 'yes';

		echo '<div class="pal-people-grid pal-people-grid--cols-' . esc_attr( $columns ) . '">';

		foreach ( $people as $person ) {
			$pid   = $person->ID;
			$name  = get_the_title( $pid );
			$role  = get_post_meta( $pid, '_pal_people_role', true );
			$org   = get_post_meta( $pid, '_pal_people_organization', true );
			$email = get_post_meta( $pid, '_pal_people_email', true );
			$thumb = get_the_post_thumbnail_url( $pid, 'medium_large' );
			$placeholder = NPAL_PLUGIN_URL . 'assets/img/person-placeholder.svg';
			$first_name  = explode( ' ', $name )[0];

			echo '<div class="pal-people-card pal-people-card--circle">';

			// Circle photo.
			echo '<div class="pal-people-card__photo">';
			if ( $thumb ) {
				echo '<img src="' . esc_url( $thumb ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" />';
			} else {
				echo '<img src="' . esc_url( $placeholder ) . '" alt="' . esc_attr( $name ) . '" class="pal-people-card__placeholder" />';
			}
			echo '</div>';

			// Info.
			echo '<div class="pal-people-card__info">';
			echo '<h3 class="pal-people-card__name">' . esc_html( $name ) . '</h3>';

			if ( 'yes' === $show_role && $role ) {
				echo '<p class="pal-people-card__role">' . esc_html( $role ) . '</p>';
			}
			if ( 'yes' === $show_org && $org ) {
				echo '<p class="pal-people-card__org">' . esc_html( $org ) . '</p>';
			}
			if ( 'yes' === $show_contact && $email ) {
				echo '<p class="pal-people-card__contact-link"><a href="mailto:' . esc_attr( $email ) . '">Contact ' . esc_html( $first_name ) . '</a></p>';
			}

			echo '</div>';
			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Render directory-style list (Team Members — no photos, icon + name + role + contact).
	 */
	protected function render_directory_list( $people, $columns, $settings = array() ) {
		echo '<div class="pal-people-directory pal-people-directory--cols-' . esc_attr( $columns ) . '">';

		foreach ( $people as $person ) {
			$pid   = $person->ID;
			$name  = get_the_title( $pid );
			$role  = get_post_meta( $pid, '_pal_people_role', true );
			$email = get_post_meta( $pid, '_pal_people_email', true );
			$first_name = explode( ' ', $name )[0];

			echo '<div class="pal-people-dir-item">';
			echo '<div class="pal-people-dir-item__icon"><i class="fas fa-user"></i></div>';
			echo '<div class="pal-people-dir-item__info">';
			echo '<h4 class="pal-people-dir-item__name">' . esc_html( $name ) . '</h4>';
			if ( $role ) {
				echo '<p class="pal-people-dir-item__role">' . esc_html( $role ) . '</p>';
			}
			if ( $email ) {
				echo '<p class="pal-people-dir-item__contact"><a href="mailto:' . esc_attr( $email ) . '">Contact ' . esc_html( $first_name ) . '</a></p>';
			}
			echo '</div>';
			echo '</div>';
		}

		echo '</div>';
	}
}

/* ======================================================================
 *  PAL People Hero Banner
 * ==================================================================== */

class NPAL_Widget_People_Hero extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_people_hero'; }
	public function get_title()    { return __( 'PAL People Hero', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-banner'; }
	public function get_keywords() { return array( 'pal', 'people', 'hero', 'banner', 'board', 'staff' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Hero', 'nashua-pal-core' ),
		) );

		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Board & Staff Members',
		) );

		$this->add_control( 'subtitle', array(
			'label'   => __( 'Subtitle', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Meet the dedicated leaders and staff who work together\nto build stronger kids, safer communities, and brighter futures.",
		) );

		$this->add_control( 'bg_image', array(
			'label'   => __( 'Background Image', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );

		$this->add_control( 'overlay_opacity', array(
			'label'   => __( 'Overlay Opacity', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SLIDER,
			'range'   => array( 'px' => array( 'min' => 0, 'max' => 100, 'step' => 5 ) ),
			'default' => array( 'size' => 60, 'unit' => 'px' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$bg = ! empty( $s['bg_image']['url'] ) ? $s['bg_image']['url'] : '';
		$opacity = isset( $s['overlay_opacity']['size'] ) ? $s['overlay_opacity']['size'] / 100 : 0.6;

		echo '<div class="pal-people-hero"';
		if ( $bg ) {
			echo ' style="background-image:url(' . esc_url( $bg ) . ');"';
		}
		echo '>';
		echo '<div class="pal-people-hero__overlay" style="opacity:' . esc_attr( $opacity ) . ';"></div>';
		echo '<div class="pal-people-hero__content">';

		if ( ! empty( $s['title'] ) ) {
			echo '<h1 class="pal-people-hero__title">' . esc_html( $s['title'] ) . '</h1>';
		}
		if ( ! empty( $s['subtitle'] ) ) {
			echo '<p class="pal-people-hero__subtitle">' . nl2br( esc_html( $s['subtitle'] ) ) . '</p>';
		}

		echo '</div>';
		echo '</div>';
	}
}

/* ======================================================================
 *  PAL Values Bar
 * ==================================================================== */

class NPAL_Widget_Values_Bar extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_values_bar'; }
	public function get_title()    { return __( 'PAL Values Bar', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-bar'; }
	public function get_keywords() { return array( 'pal', 'values', 'mission', 'stats' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Values', 'nashua-pal-core' ),
		) );

		$this->add_control( 'items', array(
			'label'       => __( 'Value Items', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => array(
				array(
					'name'    => 'icon',
					'label'   => __( 'Icon Class', 'nashua-pal-core' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'fas fa-star',
				),
				array(
					'name'    => 'title',
					'label'   => __( 'Title', 'nashua-pal-core' ),
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => 'Value',
				),
				array(
					'name'    => 'description',
					'label'   => __( 'Description', 'nashua-pal-core' ),
					'type'    => \Elementor\Controls_Manager::TEXTAREA,
					'default' => 'Short description of this value.',
				),
			),
			'default' => array(
				array( 'icon' => 'fas fa-user-tie',          'title' => 'LEADERSHIP',  'description' => 'Experienced leaders guiding our mission and programs.' ),
				array( 'icon' => 'fas fa-hands-helping',     'title' => 'COMMUNITY',   'description' => 'Deeply connected to Nashua and the greater community.' ),
				array( 'icon' => 'fas fa-shield-alt',        'title' => 'COMMITMENT',  'description' => 'Committed to youth development, safety, and positive change.' ),
				array( 'icon' => 'fas fa-heart',             'title' => 'TOGETHER',    'description' => 'Working together to create opportunities for all.' ),
			),
			'title_field' => '{{{ title }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$items = ! empty( $s['items'] ) ? $s['items'] : array();

		echo '<div class="pal-values-bar">';
		echo '<div class="pal-values-bar__inner">';

		foreach ( $items as $item ) {
			echo '<div class="pal-values-bar__item">';
			if ( ! empty( $item['icon'] ) ) {
				echo '<div class="pal-values-bar__icon"><i class="' . esc_attr( $item['icon'] ) . '"></i></div>';
			}
			echo '<div class="pal-values-bar__text">';
			if ( ! empty( $item['title'] ) ) {
				echo '<strong class="pal-values-bar__title">' . esc_html( $item['title'] ) . '</strong>';
			}
			if ( ! empty( $item['description'] ) ) {
				echo '<p class="pal-values-bar__desc">' . esc_html( $item['description'] ) . '</p>';
			}
			echo '</div>';
			echo '</div>';
		}

		echo '</div>';
		echo '</div>';
	}
}

/* ======================================================================
 *  PAL Board Members Grid
 * ==================================================================== */

class NPAL_Widget_Board_Members extends NPAL_Elementor_Widget_Base {

	use NPAL_People_Widget_Trait;

	public function get_name()     { return 'pal_board_members'; }
	public function get_title()    { return __( 'PAL Board Members Grid', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-person'; }
	public function get_keywords() { return array( 'pal', 'board', 'members', 'directors', 'people' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Board Members', 'nashua-pal-core' ),
		) );

		$this->add_control( 'section_title', array(
			'label'   => __( 'Section Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'NASHUA PAL BOARD',
		) );

		$this->add_control( 'section_subtitle', array(
			'label'   => __( 'Section Subtitle', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Our Board of Directors provides vision, oversight, and governance to ensure that Nashua PAL remains strong, transparent, and focused on its mission.',
		) );

		$this->add_control( 'count', array(
			'label'   => __( 'Number to Show', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 50,
			'min'     => 1,
			'max'     => 100,
		) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns (Desktop)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '5',
			'options' => array( '3' => '3', '4' => '4', '5' => '5' ),
		) );

		$this->add_control( 'show_role', array(
			'label'   => __( 'Show Title/Role', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		) );

		$this->add_control( 'show_org', array(
			'label'   => __( 'Show Organization', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		) );

		$this->add_control( 'show_contact', array(
			'label'   => __( 'Show Contact Link', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => '',
		) );

		$this->add_control( 'btn1_text', array(
			'label'   => __( 'Button 1 Text', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Interested in Joining the Board?',
		) );

		$this->add_control( 'btn1_url', array(
			'label'   => __( 'Button 1 URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '/contact/',
		) );

		$this->add_control( 'btn1_icon', array(
			'label'   => __( 'Button 1 Icon', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'fas fa-handshake',
		) );

		$this->add_control( 'btn2_text', array(
			'label'   => __( 'Button 2 Text', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'PAL Board Resource Page',
		) );

		$this->add_control( 'btn2_url', array(
			'label'   => __( 'Button 2 URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '/board-resources/',
		) );

		$this->add_control( 'btn2_icon', array(
			'label'   => __( 'Button 2 Icon', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'fas fa-file-alt',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$people = $this->get_people( NPAL_CPT_Board_Member::POST_TYPE, $s['count'], 'last_name' );

		echo '<div class="pal-people-section pal-people-section--board">';

		if ( ! empty( $s['section_title'] ) || ! empty( $s['section_subtitle'] ) ) {
			echo '<div class="pal-people-section__header">';
			if ( ! empty( $s['section_title'] ) ) {
				echo '<h2 class="pal-people-section__title">' . esc_html( $s['section_title'] ) . '</h2>';
				echo '<div class="pal-people-section__accent"></div>';
			}
			if ( ! empty( $s['section_subtitle'] ) ) {
				echo '<p class="pal-people-section__subtitle">' . esc_html( $s['section_subtitle'] ) . '</p>';
			}
			echo '</div>';
		}

		if ( ! empty( $people ) ) {
			$this->render_circle_grid( $people, $s['columns'], $s );
		} else {
			echo '<p style="text-align:center;color:#777;">' . esc_html__( 'No board members found. Add them in PAL People > Board Members.', 'nashua-pal-core' ) . '</p>';
		}

		// CTA Buttons.
		$has_btn1 = ! empty( $s['btn1_text'] ) && ! empty( $s['btn1_url'] );
		$has_btn2 = ! empty( $s['btn2_text'] ) && ! empty( $s['btn2_url'] );
		if ( $has_btn1 || $has_btn2 ) {
			echo '<div class="pal-people-section__buttons">';
			if ( $has_btn1 ) {
				echo '<a href="' . esc_url( $s['btn1_url'] ) . '" class="pal-people-section__btn pal-people-section__btn--primary">';
				if ( ! empty( $s['btn1_icon'] ) ) { echo '<i class="' . esc_attr( $s['btn1_icon'] ) . '"></i> '; }
				echo esc_html( $s['btn1_text'] ) . '</a>';
			}
			if ( $has_btn2 ) {
				echo '<a href="' . esc_url( $s['btn2_url'] ) . '" class="pal-people-section__btn pal-people-section__btn--outline">';
				if ( ! empty( $s['btn2_icon'] ) ) { echo '<i class="' . esc_attr( $s['btn2_icon'] ) . '"></i> '; }
				echo esc_html( $s['btn2_text'] ) . '</a>';
			}
			echo '</div>';
		}

		echo '</div>';
	}
}

/* ======================================================================
 *  PAL Team Members Directory
 * ==================================================================== */

class NPAL_Widget_Team_Members extends NPAL_Elementor_Widget_Base {

	use NPAL_People_Widget_Trait;

	public function get_name()     { return 'pal_team_members'; }
	public function get_title()    { return __( 'PAL Team Members Directory', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-user-circle-o'; }
	public function get_keywords() { return array( 'pal', 'team', 'staff', 'members', 'people', 'directory' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Team Members', 'nashua-pal-core' ),
		) );

		$this->add_control( 'section_title', array(
			'label'   => __( 'Section Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'NASHUA PAL TEAM MEMBERS',
		) );

		$this->add_control( 'section_subtitle', array(
			'label'   => __( 'Section Subtitle', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Our team works tirelessly every day to deliver programs, support youth, and strengthen our community.',
		) );

		$this->add_control( 'count', array(
			'label'   => __( 'Number to Show', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 50,
			'min'     => 1,
			'max'     => 100,
		) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns (Desktop)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '3',
			'options' => array( '2' => '2', '3' => '3', '4' => '4' ),
		) );

		$this->add_control( 'general_email', array(
			'label'       => __( 'General Questions Email', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => 'info@nashuapal.org',
			'description' => __( 'Shown at the end of the directory. Leave blank to hide.', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$people = $this->get_people( NPAL_CPT_Team_Member::POST_TYPE, $s['count'], 'meta_order' );

		echo '<div class="pal-people-section pal-people-section--team">';

		if ( ! empty( $s['section_title'] ) || ! empty( $s['section_subtitle'] ) ) {
			echo '<div class="pal-people-section__header">';
			if ( ! empty( $s['section_title'] ) ) {
				echo '<h2 class="pal-people-section__title">' . esc_html( $s['section_title'] ) . '</h2>';
				echo '<div class="pal-people-section__accent"></div>';
			}
			if ( ! empty( $s['section_subtitle'] ) ) {
				echo '<p class="pal-people-section__subtitle">' . esc_html( $s['section_subtitle'] ) . '</p>';
			}
			echo '</div>';
		}

		if ( ! empty( $people ) ) {
			$this->render_directory_list( $people, $s['columns'], $s );
		} else {
			echo '<p style="text-align:center;color:#777;">' . esc_html__( 'No team members found. Add them in PAL People > Team Members.', 'nashua-pal-core' ) . '</p>';
		}

		// General Questions entry.
		if ( ! empty( $s['general_email'] ) ) {
			echo '<div class="pal-people-dir-general">';
			echo '<div class="pal-people-dir-item__icon"><i class="fas fa-envelope"></i></div>';
			echo '<div class="pal-people-dir-item__info">';
			echo '<h4 class="pal-people-dir-item__name">' . esc_html__( 'General Questions', 'nashua-pal-core' ) . '</h4>';
			echo '<p class="pal-people-dir-item__role">' . esc_html( $s['general_email'] ) . '</p>';
			echo '</div>';
			echo '</div>';
		}

		echo '</div>';
	}
}

/* ======================================================================
 *  PAL Volunteer Leadership Grid
 * ==================================================================== */

class NPAL_Widget_Volunteer_Leaders extends NPAL_Elementor_Widget_Base {

	use NPAL_People_Widget_Trait;

	public function get_name()     { return 'pal_volunteer_leaders'; }
	public function get_title()    { return __( 'PAL Volunteer Leadership Grid', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-heart-o'; }
	public function get_keywords() { return array( 'pal', 'volunteer', 'coaches', 'leaders', 'people' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'Volunteer Leaders', 'nashua-pal-core' ),
		) );

		$this->add_control( 'section_title', array(
			'label'   => __( 'Section Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'NASHUA PAL VOLUNTEER LEADERSHIP',
		) );

		$this->add_control( 'section_subtitle', array(
			'label'   => __( 'Section Subtitle', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'These volunteers dedicate their time and expertise to help guide and grow our mission.',
		) );

		$this->add_control( 'count', array(
			'label'   => __( 'Number to Show', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 50,
			'min'     => 1,
			'max'     => 200,
		) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns (Desktop)', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '3',
			'options' => array( '3' => '3', '4' => '4', '5' => '5' ),
		) );

		$this->add_control( 'show_role', array(
			'label'   => __( 'Show Title/Role', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		) );

		$this->add_control( 'show_contact', array(
			'label'   => __( 'Show Contact Link', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$people = $this->get_people( NPAL_CPT_Volunteer_Leader::POST_TYPE, $s['count'] );

		echo '<div class="pal-people-section pal-people-section--volunteers">';

		if ( ! empty( $s['section_title'] ) || ! empty( $s['section_subtitle'] ) ) {
			echo '<div class="pal-people-section__header">';
			if ( ! empty( $s['section_title'] ) ) {
				echo '<h2 class="pal-people-section__title">' . esc_html( $s['section_title'] ) . '</h2>';
				echo '<div class="pal-people-section__accent"></div>';
			}
			if ( ! empty( $s['section_subtitle'] ) ) {
				echo '<p class="pal-people-section__subtitle">' . esc_html( $s['section_subtitle'] ) . '</p>';
			}
			echo '</div>';
		}

		if ( ! empty( $people ) ) {
			$this->render_circle_grid( $people, $s['columns'], $s );
		} else {
			echo '<p style="text-align:center;color:#777;">' . esc_html__( 'No volunteer leaders found. Add them in PAL People > Volunteer Leaders.', 'nashua-pal-core' ) . '</p>';
		}

		echo '</div>';
	}
}

/* ======================================================================
 *  PAL People CTA
 * ==================================================================== */

class NPAL_Widget_People_CTA extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_people_cta'; }
	public function get_title()    { return __( 'PAL People CTA', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-call-to-action'; }
	public function get_keywords() { return array( 'pal', 'cta', 'join', 'people', 'volunteer' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array(
			'label' => __( 'CTA', 'nashua-pal-core' ),
		) );

		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Every Kid Deserves A PAL!',
		) );

		$this->add_control( 'description', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Nashua PAL serves over 2,000 youth each year in 16 engaging and educational programs throughout Greater Nashua!',
		) );

		$this->add_control( 'btn_text', array(
			'label'   => __( 'Button Text', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Click Here to Donate',
		) );

		$this->add_control( 'btn_url', array(
			'label'   => __( 'Button URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '/donate/',
		) );

		$this->add_control( 'bg_image', array(
			'label'   => __( 'Background Image', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s  = $this->get_settings_for_display();
		$bg = ! empty( $s['bg_image']['url'] ) ? $s['bg_image']['url'] : '';

		echo '<div class="pal-people-cta"';
		if ( $bg ) {
			echo ' style="background-image:url(' . esc_url( $bg ) . ');"';
		}
		echo '>';
		echo '<div class="pal-people-cta__overlay"></div>';
		echo '<div class="pal-people-cta__content">';

		if ( ! empty( $s['title'] ) ) {
			echo '<h2 class="pal-people-cta__title">' . esc_html( $s['title'] ) . '</h2>';
		}
		if ( ! empty( $s['description'] ) ) {
			echo '<p class="pal-people-cta__desc">' . esc_html( $s['description'] ) . '</p>';
		}

		if ( ! empty( $s['btn_text'] ) && ! empty( $s['btn_url'] ) ) {
			echo '<div class="pal-people-cta__buttons">';
			echo '<a href="' . esc_url( $s['btn_url'] ) . '" class="pal-people-cta__btn pal-people-cta__btn--primary">' . esc_html( $s['btn_text'] ) . '</a>';
			echo '</div>';
		}

		echo '</div>';
		echo '</div>';
	}
}
