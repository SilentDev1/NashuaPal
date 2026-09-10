<?php
/**
 * PAL Mega Menu — custom Elementor widget.
 *
 * Multi-panel mega menu: each top-level menu item can have its own
 * independently-configurable panel with 1-4 link columns and an
 * optional spotlight card. Desktop opens on hover/focus; mobile uses
 * an accordion. Fully accessible with ARIA attributes and keyboard nav.
 *
 * @package NashuaPalCore
 * @since   1.16.0
 * @since   1.17.0  Multi-panel architecture (independent panel configs).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

class NPAL_Widget_Mega_Menu extends \Elementor\Widget_Base {

	/** @var array|null Cached submenu options for SELECT2. */
	private $submenu_options_cache = null;

	public function get_name()       { return 'pal_mega_menu'; }
	public function get_title()      { return __( 'PAL Mega Menu', 'nashua-pal-core' ); }
	public function get_icon()       { return 'eicon-mega-menu'; }
	public function get_categories() { return array( 'nashua-pal' ); }
	public function get_keywords()   { return array( 'pal', 'mega', 'menu', 'navigation', 'programs', 'header' ); }

	/* ================================================================ */
	/*  CONTROLS                                                        */
	/* ================================================================ */

	protected function register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/* ---------- Content tab ---------- */

	private function register_content_controls() {

		/* ── Menu Items ── */
		$this->start_controls_section( 'sec_menu', array(
			'label' => __( 'Menu Items', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'wp_menu', array(
			'label'       => __( 'WordPress Menu', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::SELECT,
			'options'     => $this->get_wp_menus(),
			'default'     => '',
			'description' => __( 'Select the primary navigation menu.', 'nashua-pal-core' ),
		) );

		$this->add_control( 'mega_parents', array(
			'label'       => __( 'Mega Menu Items (Legacy)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::SELECT2,
			'multiple'    => true,
			'options'     => $this->get_top_level_items(),
			'default'     => array(),
			'description' => __( 'Keep these selected for backward compat. Use the Panel dropdowns below for explicit assignment.', 'nashua-pal-core' ),
		) );

		$top_items = array_merge(
			array( '' => __( '— None —', 'nashua-pal-core' ) ),
			$this->get_top_level_items()
		);

		$this->add_control( 'hr_mega_assign', array(
			'type' => \Elementor\Controls_Manager::HEADING,
			'label' => __( 'Assign Panels to Menu Items', 'nashua-pal-core' ),
			'separator' => 'before',
		) );

		for ( $n = 1; $n <= 4; $n++ ) {
			$this->add_control( "mega_p{$n}", array(
				'label'   => sprintf( __( 'Panel %d → Menu Item', 'nashua-pal-core' ), $n ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $top_items,
				'default' => '',
			) );
		}

		$this->end_controls_section();

		/* ── Panel slots — always register 3, mapped by position to mega_parents ── */
		$panel_defaults = array(
			1 => array(
				'columns' => '3',
				'spot'    => 'yes',
				'cols'    => array(
					1 => array(
						'heading'  => 'Sports',
						'links'    => array(
							array( 'label' => 'Basketball',    'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Football',      'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Boxing',        'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Cheerleading',  'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Cross Country', 'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Track & Field', 'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Street Hockey', 'url' => array( 'url' => '#' ) ),
						),
						'btn_text' => 'View All Sports →',
					),
					2 => array(
						'heading'  => 'Youth Services',
						'links'    => array(
							array( 'label' => 'Afterschool Programs',     'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Mentoring',                'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Family Support',           'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Juvenile Court Diversion', 'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Leadership Development',   'url' => array( 'url' => '#' ) ),
						),
						'btn_text' => 'View All Youth Services →',
					),
					3 => array(
						'heading'  => 'Community Programs',
						'links'    => array(
							array( 'label' => 'Volunteer Opportunities', 'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Scholarships',           'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Events',                 'url' => array( 'url' => '#' ) ),
							array( 'label' => 'Community Outreach',     'url' => array( 'url' => '#' ) ),
							array( 'label' => 'PAL in the Schools',     'url' => array( 'url' => '#' ) ),
						),
						'btn_text' => 'View All Programs →',
					),
					4 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
				),
			),
			2 => array(
				'columns' => '2',
				'spot'    => '',
				'cols'    => array(
					1 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
					2 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
					3 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
					4 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
				),
			),
			3 => array(
				'columns' => '2',
				'spot'    => '',
				'cols'    => array(
					1 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
					2 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
					3 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
					4 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
				),
			),
			4 => array(
				'columns' => '2',
				'spot'    => '',
				'cols'    => array(
					1 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
					2 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
					3 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
					4 => array( 'heading' => '', 'links' => array(), 'btn_text' => '' ),
				),
			),
		);

		/* Panel names default to "Panel 1/2/3/4" here. The editor JS
		   (megamenu-editor.js) dynamically renames them to the actual
		   menu-item titles when the user picks a menu item for each panel. */
		$panel_names = array( 1 => 'Panel 1', 2 => 'Panel 2', 3 => 'Panel 3', 4 => 'Panel 4' );
		for ( $n = 1; $n <= 4; $n++ ) {
			$cfg = isset( $panel_defaults[ $n ] ) ? $panel_defaults[ $n ] : $panel_defaults[2];
			$this->register_panel_controls( $n, $panel_names[ $n ], $cfg );
		}

		/* ── Donate Button ── */
		$this->start_controls_section( 'sec_donate', array(
			'label' => __( 'Donate Button', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( 'donate_text', array(
			'label'   => __( 'Button Text', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'DONATE TODAY',
		) );

		$this->add_control( 'donate_url', array(
			'label'   => __( 'Button URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => array( 'url' => '#' ),
		) );

		$this->end_controls_section();
	}

	/**
	 * Register all controls for one panel slot.
	 *
	 * @param int    $n    Panel number (1-4).
	 * @param string $name Menu item name (e.g. "Programs").
	 * @param array  $cfg  Default values.
	 */
	private function register_panel_controls( $n, $name, $cfg ) {
		$p = "p{$n}";

		/* ── Panel main section ── */
		$this->start_controls_section( "sec_{$p}", array(
			'label' => esc_html( $name ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		) );

		$this->add_control( "{$p}_enable", array(
			'label'        => __( 'Enable Panel', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => __( 'On', 'nashua-pal-core' ),
			'label_off'    => __( 'Off', 'nashua-pal-core' ),
			'return_value' => 'yes',
			'default'      => ( 1 === $n ) ? 'yes' : '',
		) );

		$this->add_control( "{$p}_columns", array(
			'label'     => __( 'Number of Columns', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::SELECT,
			'options'   => array(
				'1' => __( '1 Column', 'nashua-pal-core' ),
				'2' => __( '2 Columns', 'nashua-pal-core' ),
				'3' => __( '3 Columns', 'nashua-pal-core' ),
				'4' => __( '4 Columns', 'nashua-pal-core' ),
			),
			'default'   => $cfg['columns'],
			'condition' => array( "{$p}_enable" => 'yes' ),
		) );

		$this->add_control( "{$p}_spotlight_show", array(
			'label'        => __( 'Show Spotlight Card', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => __( 'Show', 'nashua-pal-core' ),
			'label_off'    => __( 'Hide', 'nashua-pal-core' ),
			'return_value' => 'yes',
			'default'      => ! empty( $cfg['spot'] ) ? $cfg['spot'] : '',
			'condition'    => array( "{$p}_enable" => 'yes' ),
		) );

		$this->end_controls_section();

		/* ── Column sections ── */
		$col_conditions = array(
			1 => array( "{$p}_enable" => 'yes' ),
			2 => array( "{$p}_enable" => 'yes', "{$p}_columns" => array( '2', '3', '4' ) ),
			3 => array( "{$p}_enable" => 'yes', "{$p}_columns" => array( '3', '4' ) ),
			4 => array( "{$p}_enable" => 'yes', "{$p}_columns" => array( '4' ) ),
		);

		for ( $c = 1; $c <= 4; $c++ ) {
			$col_cfg = isset( $cfg['cols'][ $c ] ) ? $cfg['cols'][ $c ] : array( 'heading' => '', 'links' => array(), 'btn_text' => '' );
			$this->register_column_section( $n, $c, $name, $col_conditions[ $c ], $col_cfg );
		}

		/* ── Spotlight section ── */
		$this->register_spotlight_section( $n, $name, $cfg );
	}

	/**
	 * Register one column section's controls.
	 */
	private function register_column_section( $panel, $col, $panel_name, $condition, $cfg ) {
		$prefix = "p{$panel}_c{$col}";

		$this->start_controls_section( "sec_{$prefix}", array(
			'label'     => sprintf( '%s — Column %d', esc_html( $panel_name ), $col ),
			'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
			'condition' => $condition,
		) );

		$this->add_control( "{$prefix}_source", array(
			'label'   => __( 'Link Source', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'manual' => __( 'Manual', 'nashua-pal-core' ),
				'menu'   => __( 'WordPress Menu', 'nashua-pal-core' ),
			),
			'default' => 'manual',
		) );

		$this->add_control( "{$prefix}_menu_items", array(
			'label'       => __( 'Menu Items', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::SELECT2,
			'multiple'    => true,
			'options'     => $this->get_submenu_options( $panel_name ),
			'default'     => array(),
			'description' => __( 'Pick which sub-menu items to show in this column.', 'nashua-pal-core' ),
			'condition'   => array( "{$prefix}_source" => 'menu' ),
		) );

		$this->add_control( "{$prefix}_heading", array(
			'label'       => __( 'Heading', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => isset( $cfg['heading'] ) ? $cfg['heading'] : '',
			'description' => __( 'Leave blank when using WordPress Menu source to auto-use the parent item title.', 'nashua-pal-core' ),
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'label', array(
			'label'   => __( 'Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
		) );
		$repeater->add_control( 'url', array(
			'label'   => __( 'URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => array( 'url' => '#' ),
		) );

		$this->add_control( "{$prefix}_links", array(
			'label'       => __( 'Links', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => isset( $cfg['links'] ) ? $cfg['links'] : array(),
			'title_field' => '{{{ label }}}',
			'condition'   => array( "{$prefix}_source!" => 'menu' ),
		) );

		$this->add_control( "{$prefix}_btn_show", array(
			'label'        => __( 'Show Button', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'label_on'     => __( 'Show', 'nashua-pal-core' ),
			'label_off'    => __( 'Hide', 'nashua-pal-core' ),
			'return_value' => 'yes',
			'default'      => ! empty( $cfg['btn_text'] ) ? 'yes' : '',
		) );

		$this->add_control( "{$prefix}_btn_text", array(
			'label'     => __( 'Button Text', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::TEXT,
			'default'   => isset( $cfg['btn_text'] ) ? $cfg['btn_text'] : '',
			'condition' => array( "{$prefix}_btn_show" => 'yes' ),
		) );

		$this->add_control( "{$prefix}_btn_url", array(
			'label'     => __( 'Button URL', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::URL,
			'default'   => array( 'url' => '#' ),
			'condition' => array( "{$prefix}_btn_show" => 'yes' ),
		) );

		$this->end_controls_section();
	}

	/**
	 * Register spotlight controls for one panel.
	 */
	private function register_spotlight_section( $n, $panel_name, $cfg ) {
		$p        = "p{$n}";
		$is_first = ( 1 === $n );

		$this->start_controls_section( "sec_{$p}_spot", array(
			'label'     => sprintf( '%s — Spotlight', esc_html( $panel_name ) ),
			'tab'       => \Elementor\Controls_Manager::TAB_CONTENT,
			'condition' => array(
				"{$p}_enable"         => 'yes',
				"{$p}_spotlight_show" => 'yes',
			),
		) );

		$this->add_control( "{$p}_spotlight_image", array(
			'label'   => __( 'Image', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );

		$this->add_control( "{$p}_spotlight_label", array(
			'label'   => __( 'Small Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => $is_first ? 'PROGRAM SPOTLIGHT' : '',
		) );

		$this->add_control( "{$p}_spotlight_title", array(
			'label'   => __( 'Headline', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => $is_first ? 'Summer Programs Now Open!' : '',
		) );

		$this->add_control( "{$p}_spotlight_desc", array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => $is_first ? 'Give your child a summer full of fun, confidence, leadership, and new friendships.' : '',
		) );

		$this->add_control( "{$p}_spotlight_btn_text", array(
			'label'   => __( 'Button Text', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => $is_first ? 'Learn More →' : '',
		) );

		$this->add_control( "{$p}_spotlight_btn_url", array(
			'label'   => __( 'Button URL', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => array( 'url' => '#' ),
		) );

		$this->end_controls_section();
	}

	/* ---------- Style tab ---------- */

	private function register_style_controls() {

		/* ---------- Panel ---------- */
		$this->start_controls_section( 'sec_style_panel', array(
			'label' => __( 'Mega Menu Panel', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'panel_bg', array(
			'label'     => __( 'Background', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => array( '{{WRAPPER}} .pal-megamenu__panel' => 'background: {{VALUE}};' ),
		) );

		$this->add_control( 'panel_width', array(
			'label'      => __( 'Width', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 600, 'max' => 1600 ) ),
			'default'    => array( 'size' => 1200, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .pal-megamenu__panel' => 'width: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_control( 'panel_radius', array(
			'label'      => __( 'Border Radius', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::DIMENSIONS,
			'size_units' => array( 'px' ),
			'default'    => array( 'top' => '0', 'right' => '0', 'bottom' => '18', 'left' => '18', 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .pal-megamenu__panel' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
		) );

		$this->end_controls_section();

		/* ---------- Colors ---------- */
		$this->start_controls_section( 'sec_style_colors', array(
			'label' => __( 'Colors & Typography', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'color_primary', array(
			'label'   => __( 'Primary / Navy', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::COLOR,
			'default' => '#11284D',
		) );

		$this->add_control( 'color_accent', array(
			'label'   => __( 'Accent / Gold', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::COLOR,
			'default' => '#F2B134',
		) );

		$this->add_control( 'color_text', array(
			'label'   => __( 'Link Text', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::COLOR,
			'default' => '#4B5563',
		) );

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'heading_typo',
				'label'    => __( 'Column Heading', 'nashua-pal-core' ),
				'selector' => '{{WRAPPER}} .pal-megamenu__col-title',
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'link_typo',
				'label'    => __( 'Link', 'nashua-pal-core' ),
				'selector' => '{{WRAPPER}} .pal-megamenu__link',
			)
		);

		$this->end_controls_section();

		/* ---------- Nav Bar ---------- */
		$this->start_controls_section( 'sec_style_nav', array(
			'label' => __( 'Navigation Bar', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'nav_text_color', array(
			'label'     => __( 'Text Color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#11284D',
			'selectors' => array( '{{WRAPPER}} .pal-megamenu__nav-link' => 'color: {{VALUE}};' ),
		) );

		$this->add_control( 'nav_hover_color', array(
			'label'     => __( 'Hover / Active Color', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#F2B134',
			'selectors' => array(
				'{{WRAPPER}} .pal-megamenu__nav-item:hover > .pal-megamenu__nav-link' => 'color: {{VALUE}};',
				'{{WRAPPER}} .pal-megamenu__nav-item.is-active > .pal-megamenu__nav-link' => 'color: {{VALUE}};',
				'{{WRAPPER}} .pal-megamenu__nav-item:hover > .pal-megamenu__nav-link::after' => 'background: {{VALUE}};',
				'{{WRAPPER}} .pal-megamenu__nav-item.is-active > .pal-megamenu__nav-link::after' => 'background: {{VALUE}};',
			),
		) );

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'nav_typo',
				'label'    => __( 'Typography', 'nashua-pal-core' ),
				'selector' => '{{WRAPPER}} .pal-megamenu__nav-link',
			)
		);

		$this->end_controls_section();

		/* ---------- Buttons ---------- */
		$this->start_controls_section( 'sec_style_buttons', array(
			'label' => __( 'Buttons', 'nashua-pal-core' ),
			'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
		) );

		$this->add_control( 'btn_border_color', array(
			'label'     => __( 'Column Button Border', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#11284D',
			'selectors' => array( '{{WRAPPER}} .pal-megamenu__col-btn' => 'border-color: {{VALUE}}; color: {{VALUE}};' ),
		) );

		$this->add_control( 'btn_hover_bg', array(
			'label'     => __( 'Column Button Hover BG', 'nashua-pal-core' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#11284D',
			'selectors' => array( '{{WRAPPER}} .pal-megamenu__col-btn:hover' => 'background: {{VALUE}}; border-color: {{VALUE}}; color: #fff;' ),
		) );

		$this->end_controls_section();
	}

	/* ================================================================ */
	/*  HELPERS                                                         */
	/* ================================================================ */

	private function get_wp_menus() {
		$menus   = wp_get_nav_menus();
		$options = array( '' => __( '— Select —', 'nashua-pal-core' ) );
		foreach ( $menus as $m ) {
			$options[ $m->slug ] = $m->name;
		}
		return $options;
	}

	/**
	 * Resolve a menu slug to its WP_Term menu object.
	 */
	private function resolve_menu_object( $menu_slug ) {
		if ( ! $menu_slug ) { return null; }
		$menu_obj = wp_get_nav_menu_object( $menu_slug );
		if ( ! $menu_obj ) {
			$locations = get_nav_menu_locations();
			foreach ( $locations as $loc => $id ) {
				$obj = wp_get_nav_menu_object( $id );
				if ( $obj && $obj->slug === $menu_slug ) { return $obj; }
			}
		}
		return $menu_obj ? $menu_obj : null;
	}

	/**
	 * Resolve mega_p{n} IDs against the actual selected menu.
	 *
	 * get_top_level_items() returns items from ALL menus, so the stored
	 * mega_p{n} ID may belong to a different menu than the one selected
	 * in wp_menu. This method title-matches those IDs to the correct
	 * menu item IDs in the selected menu.
	 *
	 * @param array  &$s        Widget settings (modified in place).
	 * @param string $menu_slug The selected menu slug.
	 */
	private function resolve_mega_assignments( &$s, $menu_slug ) {
		$menu_obj = $this->resolve_menu_object( $menu_slug );
		if ( ! $menu_obj ) { return; }

		$items = wp_get_nav_menu_items( $menu_obj->term_id );
		if ( ! $items ) { return; }

		// Build maps from the actual menu's top-level items.
		$actual_ids  = array();
		$title_to_id = array();
		foreach ( $items as $item ) {
			if ( 0 !== (int) $item->menu_item_parent ) { continue; }
			$actual_ids[] = (int) $item->ID;
			$title_to_id[ strtolower( trim( $item->title ) ) ] = (int) $item->ID;
		}

		// Get the full options list to look up titles for stored IDs.
		$all_options = $this->get_top_level_items();

		for ( $n = 1; $n <= 4; $n++ ) {
			$key = "mega_p{$n}";
			if ( empty( $s[ $key ] ) ) { continue; }

			$stored_id = (int) $s[ $key ];

			// If the stored ID already exists in the actual menu, no fix needed.
			if ( in_array( $stored_id, $actual_ids, true ) ) { continue; }

			// Resolve by title: look up the label for this ID, strip the
			// " (menu name)" suffix, and find that title in the actual menu.
			if ( isset( $all_options[ (string) $stored_id ] ) ) {
				$label     = $all_options[ (string) $stored_id ];
				$title     = preg_replace( '/\s*\([^)]*\)$/', '', $label );
				$title_key = strtolower( trim( $title ) );
				if ( isset( $title_to_id[ $title_key ] ) ) {
					$s[ $key ] = (string) $title_to_id[ $title_key ];
				}
			}
		}
	}

	/**
	 * Get top-level menu items for the Mega Menu Items SELECT2.
	 *
	 * Shows items from ALL menus with a label suffix so the user
	 * can identify which menu each item belongs to.
	 */
	private function get_top_level_items() {
		$menus   = wp_get_nav_menus();
		$options = array();
		foreach ( $menus as $menu ) {
			$items = wp_get_nav_menu_items( $menu->term_id );
			if ( ! $items ) { continue; }
			foreach ( $items as $item ) {
				if ( 0 !== (int) $item->menu_item_parent ) { continue; }
				$options[ (string) $item->ID ] = $item->title . ' (' . $menu->name . ')';
			}
		}
		return $options;
	}

	/**
	 * Resolve mega_parents values (may be IDs or legacy title strings)
	 * into an array of menu item IDs.
	 */
	private function resolve_mega_parent_ids( $mega_parents, $menu_slug ) {
		if ( empty( $mega_parents ) || ! is_array( $mega_parents ) ) { return array(); }

		// Check if values are already numeric IDs.
		$first = reset( $mega_parents );
		if ( is_numeric( $first ) ) { return array_map( 'intval', $mega_parents ); }

		// Legacy: values are title strings — resolve to IDs from the menu.
		$menu_obj = $this->resolve_menu_object( $menu_slug );
		if ( ! $menu_obj ) { return array(); }

		$items = wp_get_nav_menu_items( $menu_obj->term_id );
		if ( ! $items ) { return array(); }

		$title_to_id = array();
		foreach ( $items as $item ) {
			if ( 0 !== (int) $item->menu_item_parent ) { continue; }
			$title_to_id[ strtolower( trim( $item->title ) ) ] = (int) $item->ID;
		}

		$ids = array();
		foreach ( $mega_parents as $val ) {
			if ( is_numeric( $val ) ) {
				$ids[] = (int) $val;
			} else {
				$key = strtolower( trim( $val ) );
				if ( isset( $title_to_id[ $key ] ) ) {
					$ids[] = $title_to_id[ $key ];
				}
			}
		}
		return $ids;
	}

	/**
	 * Build SELECT2 options listing child menu items.
	 *
	 * When $parent_name is provided, only children of that parent are returned.
	 * Key: "menu_slug::item_id"
	 */
	private function get_submenu_options( $parent_name = '' ) {
		// Build full cache once.
		if ( null === $this->submenu_options_cache ) {
			$menus = wp_get_nav_menus();
			$all   = array();

			foreach ( $menus as $menu ) {
				$items = wp_get_nav_menu_items( $menu->term_id );
				if ( ! $items ) { continue; }

				// Build a lookup of item ID → title for parent labels.
				$title_map = array();
				foreach ( $items as $item ) {
					$title_map[ (int) $item->ID ] = $item->title;
				}

				// List every child item (items with a parent).
				foreach ( $items as $item ) {
					$pid = (int) $item->menu_item_parent;
					if ( 0 === $pid ) { continue; }

					$parent_title = isset( $title_map[ $pid ] ) ? $title_map[ $pid ] : '';
					$key          = $menu->slug . '::' . (int) $item->ID;
					$all[ $key ]  = array(
						'parent' => $parent_title,
						'child'  => $item->title,
						'menu'   => $menu->name,
					);
				}
			}

			$this->submenu_options_cache = $all;
		}

		// Filter by parent name if provided.
		$parent_lower = strtolower( trim( $parent_name ) );
		$options      = array();

		foreach ( $this->submenu_options_cache as $key => $info ) {
			if ( '' !== $parent_lower && strtolower( $info['parent'] ) !== $parent_lower ) {
				continue;
			}
			$options[ $key ] = $info['child'];
		}

		return $options;
	}

	/**
	 * Resolve selected menu items into link arrays.
	 *
	 * @param array $selected Array of "menu_slug::item_id" keys.
	 * @return array [ 'links' => [...], 'parent_title' => string ]
	 */
	private function resolve_selected_menu_items( $selected ) {
		if ( empty( $selected ) || ! is_array( $selected ) ) {
			return array( 'links' => array(), 'parent_title' => '' );
		}

		// Group selected item IDs by menu slug.
		$by_menu = array();
		foreach ( $selected as $key ) {
			$parts = explode( '::', $key, 2 );
			if ( 2 !== count( $parts ) ) { continue; }
			$by_menu[ $parts[0] ][] = (int) $parts[1];
		}

		$links        = array();
		$parent_title = '';

		foreach ( $by_menu as $menu_slug => $item_ids ) {
			$menu_obj = wp_get_nav_menu_object( $menu_slug );
			if ( ! $menu_obj ) { continue; }

			$items = wp_get_nav_menu_items( $menu_obj->term_id );
			if ( ! $items ) { continue; }

			// Build title map for parent lookup.
			$title_map = array();
			foreach ( $items as $item ) {
				$title_map[ (int) $item->ID ] = $item->title;
			}

			foreach ( $items as $item ) {
				if ( ! in_array( (int) $item->ID, $item_ids, true ) ) { continue; }
				$links[] = array(
					'label' => $item->title,
					'url'   => array( 'url' => $item->url ),
				);
				// Use the first selected item's parent as column heading.
				if ( '' === $parent_title ) {
					$pid = (int) $item->menu_item_parent;
					if ( isset( $title_map[ $pid ] ) ) {
						$parent_title = $title_map[ $pid ];
					}
				}
			}
		}

		return array( 'links' => $links, 'parent_title' => $parent_title );
	}

	private function get_menu_items( $menu_slug, $mega_ids ) {
		if ( ! $menu_slug ) { return array(); }

		$menu_obj = $this->resolve_menu_object( $menu_slug );
		if ( ! $menu_obj ) { return array(); }

		$items = wp_get_nav_menu_items( $menu_obj->term_id );
		if ( ! $items ) { return array(); }

		$children_map = array();
		foreach ( $items as $item ) {
			$pid = (int) $item->menu_item_parent;
			if ( 0 === $pid ) { continue; }
			if ( ! isset( $children_map[ $pid ] ) ) { $children_map[ $pid ] = array(); }
			$children_map[ $pid ][] = array(
				'title'   => $item->title,
				'url'     => $item->url,
				'classes' => implode( ' ', array_filter( (array) $item->classes ) ),
				'current' => in_array( 'current-menu-item', (array) $item->classes, true ),
			);
		}

		$result = array();
		foreach ( $items as $item ) {
			if ( 0 !== (int) $item->menu_item_parent ) { continue; }
			$result[] = array(
				'item_id'  => (int) $item->ID,
				'title'    => $item->title,
				'url'      => $item->url,
				'is_mega'  => in_array( (int) $item->ID, $mega_ids, true ),
				'classes'  => implode( ' ', array_filter( (array) $item->classes ) ),
				'current'  => in_array( 'current-menu-item', (array) $item->classes, true )
						   || in_array( 'current-menu-ancestor', (array) $item->classes, true ),
				'children' => isset( $children_map[ $item->ID ] ) ? $children_map[ $item->ID ] : array(),
			);
		}
		return $result;
	}

	/**
	 * Build a map of menu item ID → panel config.
	 *
	 * Each panel declares which menu item it controls via the
	 * mega_p{n} control in the "Menu Items" section. Falls back
	 * to positional mapping from mega_ids for backward compat.
	 *
	 * @param array $s          Widget settings.
	 * @param array $mega_ids   Mega item IDs (positional fallback).
	 * @param array $menu_items Actual rendered menu items from get_menu_items().
	 * @param array $legacy_ids Resolved legacy mega_parents IDs (correct IDs for positional fallback).
	 */
	private function build_panels_map( $s, $mega_ids = array(), $menu_items = array(), $legacy_ids = array() ) {
		$map = array();

		// Build lookup maps from the actual rendered menu items.
		$actual_ids_set     = array();
		$title_to_actual_id = array();
		$mega_item_ids_ordered = array(); // Mega items in menu display order.
		foreach ( $menu_items as $mi ) {
			$mid = (int) $mi['item_id'];
			if ( 0 === $mid ) { continue; }
			$actual_ids_set[ $mid ] = true;
			$title_to_actual_id[ strtolower( trim( $mi['title'] ) ) ] = $mid;
			if ( ! empty( $mi['is_mega'] ) ) {
				$mega_item_ids_ordered[] = $mid;
			}
		}

		// Option labels from all menus (for title-based ID resolution).
		$all_options = ! empty( $actual_ids_set ) ? $this->get_top_level_items() : array();

		for ( $n = 1; $n <= 4; $n++ ) {
			$p = "p{$n}";
			if ( empty( $s[ "{$p}_enable" ] ) || 'yes' !== $s[ "{$p}_enable" ] ) { continue; }

			$item_id  = 0;
			$mega_key = "mega_p{$n}";

			if ( ! empty( $s[ $mega_key ] ) ) {
				$stored_id = (int) $s[ $mega_key ];

				// 1. Direct match: stored ID exists in the actual menu.
				if ( isset( $actual_ids_set[ $stored_id ] ) ) {
					$item_id = $stored_id;
				} else {
					// 2. Title resolution: look up what title this ID maps
					//    to (from any menu), then find that title in the
					//    actual rendered menu.
					if ( isset( $all_options[ (string) $stored_id ] ) ) {
						$label     = $all_options[ (string) $stored_id ];
						$title     = preg_replace( '/\s*\([^)]*\)$/', '', $label );
						$title_key = strtolower( trim( $title ) );
						if ( isset( $title_to_actual_id[ $title_key ] ) ) {
							$item_id = $title_to_actual_id[ $title_key ];
						}
					}
				}
			}

			// 3. Positional fallback: use the nth mega menu item
			//    in display order (most reliable when stored IDs
			//    are stale/orphaned from a previous migration).
			if ( 0 === $item_id && ! empty( $mega_item_ids_ordered[ $n - 1 ] ) ) {
				$item_id = (int) $mega_item_ids_ordered[ $n - 1 ];
			}
			if ( 0 === $item_id ) { continue; }

			$num_cols = max( 1, min( 4, (int) ( isset( $s[ "{$p}_columns" ] ) ? $s[ "{$p}_columns" ] : 3 ) ) );

			$cols = array();
			for ( $c = 1; $c <= $num_cols; $c++ ) {
				$cp     = "{$p}_c{$c}";
				$source = isset( $s[ "{$cp}_source" ] ) ? $s[ "{$cp}_source" ] : 'manual';

				$heading = isset( $s[ "{$cp}_heading" ] ) ? $s[ "{$cp}_heading" ] : '';
				$links   = isset( $s[ "{$cp}_links" ] ) ? $s[ "{$cp}_links" ] : array();

				// Resolve links from WordPress menu if source is 'menu'.
				if ( 'menu' === $source && ! empty( $s[ "{$cp}_menu_items" ] ) ) {
					$resolved = $this->resolve_selected_menu_items( (array) $s[ "{$cp}_menu_items" ] );
					$links    = $resolved['links'];
					if ( '' === $heading && '' !== $resolved['parent_title'] ) {
						$heading = $resolved['parent_title'];
					}
				}

				$cols[ $c ] = array(
					'heading'  => $heading,
					'links'    => $links,
					'btn_show' => isset( $s[ "{$cp}_btn_show" ] ) ? $s[ "{$cp}_btn_show" ] : '',
					'btn_text' => isset( $s[ "{$cp}_btn_text" ] ) ? $s[ "{$cp}_btn_text" ] : '',
					'btn_url'  => isset( $s[ "{$cp}_btn_url" ] ) ? $s[ "{$cp}_btn_url" ] : array( 'url' => '#' ),
				);
			}

			$map[ $item_id ] = array(
				'num_cols'       => $num_cols,
				'cols'           => $cols,
				'spotlight_show' => isset( $s[ "{$p}_spotlight_show" ] ) ? $s[ "{$p}_spotlight_show" ] : '',
				'spotlight'      => array(
					'image'    => isset( $s[ "{$p}_spotlight_image" ] ) ? $s[ "{$p}_spotlight_image" ] : array( 'url' => '' ),
					'label'    => isset( $s[ "{$p}_spotlight_label" ] ) ? $s[ "{$p}_spotlight_label" ] : '',
					'title'    => isset( $s[ "{$p}_spotlight_title" ] ) ? $s[ "{$p}_spotlight_title" ] : '',
					'desc'     => isset( $s[ "{$p}_spotlight_desc" ] ) ? $s[ "{$p}_spotlight_desc" ] : '',
					'btn_text' => isset( $s[ "{$p}_spotlight_btn_text" ] ) ? $s[ "{$p}_spotlight_btn_text" ] : '',
					'btn_url'  => isset( $s[ "{$p}_spotlight_btn_url" ] ) ? $s[ "{$p}_spotlight_btn_url" ] : array( 'url' => '#' ),
				),
			);
		}

		return $map;
	}

	/* ================================================================ */
	/*  RENDER HELPERS                                                  */
	/* ================================================================ */

	private function render_column( $col_data, $settings ) {
		$primary  = esc_attr( $settings['color_primary'] );
		$accent   = esc_attr( $settings['color_accent'] );
		$text     = esc_attr( $settings['color_text'] );
		$heading  = $col_data['heading'];
		$links    = $col_data['links'];
		$btn_show = $col_data['btn_show'];
		$btn_text = $col_data['btn_text'];
		$btn_url  = $col_data['btn_url'];
		?>
		<div class="pal-megamenu__col">
			<?php if ( $heading ) : ?>
			<h3 class="pal-megamenu__col-title" style="color:<?php echo $primary; ?>;">
				<?php echo esc_html( $heading ); ?>
				<span class="pal-megamenu__col-underline" style="background:<?php echo $accent; ?>;"></span>
			</h3>
			<?php endif; ?>
			<ul class="pal-megamenu__col-links">
				<?php foreach ( $links as $link ) :
					$url      = ! empty( $link['url']['url'] ) ? $link['url']['url'] : '#';
					$target   = ! empty( $link['url']['is_external'] ) ? ' target="_blank"' : '';
					$nofollow = ! empty( $link['url']['nofollow'] ) ? ' rel="nofollow"' : '';
				?>
					<li>
						<a class="pal-megamenu__link" href="<?php echo esc_url( $url ); ?>"<?php echo $target . $nofollow; ?> style="color:<?php echo $text; ?>;">
							<span><?php echo esc_html( $link['label'] ); ?></span>
							<svg class="pal-megamenu__chevron" width="7" height="12" viewBox="0 0 7 12" fill="none" aria-hidden="true"><path d="M1 1l5 5-5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( 'yes' === $btn_show && $btn_text ) :
				$burl = ! empty( $btn_url['url'] ) ? $btn_url['url'] : '#';
			?>
				<a class="pal-megamenu__col-btn" href="<?php echo esc_url( $burl ); ?>"><?php echo esc_html( $btn_text ); ?></a>
			<?php endif; ?>
		</div>
		<?php
	}

	private function render_spotlight( $panel, $settings ) {
		$primary = esc_attr( $settings['color_primary'] );
		$accent  = esc_attr( $settings['color_accent'] );
		$spot    = $panel['spotlight'];
		?>
		<div class="pal-megamenu__spotlight" style="background:<?php echo $primary; ?>;">
			<?php if ( ! empty( $spot['image']['url'] ) ) : ?>
				<img class="pal-megamenu__spotlight-img"
				     src="<?php echo esc_url( $spot['image']['url'] ); ?>"
				     alt="<?php echo esc_attr( $spot['title'] ); ?>"
				     loading="lazy" />
			<?php endif; ?>
			<?php if ( $spot['label'] ) : ?>
				<span class="pal-megamenu__spotlight-label" style="color:<?php echo $accent; ?>;"><?php echo esc_html( $spot['label'] ); ?></span>
			<?php endif; ?>
			<?php if ( $spot['title'] ) : ?>
				<h4 class="pal-megamenu__spotlight-title"><?php echo esc_html( $spot['title'] ); ?></h4>
			<?php endif; ?>
			<?php if ( $spot['desc'] ) : ?>
				<p class="pal-megamenu__spotlight-desc"><?php echo esc_html( $spot['desc'] ); ?></p>
			<?php endif; ?>
			<?php if ( $spot['btn_text'] ) :
				$surl = ! empty( $spot['btn_url']['url'] ) ? $spot['btn_url']['url'] : '#';
			?>
				<a class="pal-megamenu__spotlight-btn" href="<?php echo esc_url( $surl ); ?>" style="background:<?php echo $accent; ?>; color:<?php echo $primary; ?>;">
					<?php echo esc_html( $spot['btn_text'] ); ?>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}

	/* ================================================================ */
	/*  RENDER                                                          */
	/* ================================================================ */

	protected function render() {
		$s = $this->get_settings_for_display();

		$menu_slug = isset( $s['wp_menu'] ) ? $s['wp_menu'] : '';

		/* Resolve mega_p{n} IDs — they may reference items from a
		   different menu than the one selected in wp_menu (because
		   get_top_level_items() returns ALL menus). Title-match
		   them to the correct IDs in the selected menu. */
		$this->resolve_mega_assignments( $s, $menu_slug );

		/* Collect mega item IDs from mega_p1..mega_p4 controls. */
		$mega_ids = array();
		for ( $n = 1; $n <= 4; $n++ ) {
			$key = "mega_p{$n}";
			if ( ! empty( $s[ $key ] ) ) {
				$mega_ids[] = (int) $s[ $key ];
			}
		}

		// Always merge legacy mega_parents for backward compatibility.
		$legacy_raw = isset( $s['mega_parents'] ) ? $s['mega_parents'] : array();
		$legacy_ids = array();
		if ( ! empty( $legacy_raw ) && is_array( $legacy_raw ) ) {
			$legacy_ids = $this->resolve_mega_parent_ids( $legacy_raw, $menu_slug );
			$mega_ids   = array_merge( $mega_ids, $legacy_ids );
		}

		$mega_ids = array_values( array_unique( array_filter( $mega_ids ) ) );

		$menu_items = $this->get_menu_items( $menu_slug, $mega_ids );
		$accent     = esc_attr( $s['color_accent'] );
		$primary    = esc_attr( $s['color_primary'] );
		$uid        = 'pal-mm-' . $this->get_id();
		$mob_id     = $uid . '-mobile';

		/* Build panel map — each panel declares its own menu item
		   via the mega_p{n} controls. Passes $menu_items so
		   build_panels_map can resolve stale IDs by title. */
		$panels_map = $this->build_panels_map( $s, $mega_ids, $menu_items, $legacy_ids );

		/* DEBUG v1.26.9 — remove after confirming fix works. */
		$debug_mega_items = array();
		foreach ( $menu_items as $dm ) {
			if ( ! empty( $dm['is_mega'] ) ) {
				$debug_mega_items[] = $dm['item_id'] . ':' . $dm['title'];
			}
		}
		echo '<!-- MEGAMENU DEBUG v1.26.9'
		   . ' | mega_p1=' . ( isset( $s['mega_p1'] ) ? $s['mega_p1'] : '-' )
		   . ' | mega_p2=' . ( isset( $s['mega_p2'] ) ? $s['mega_p2'] : '-' )
		   . ' | mega_p3=' . ( isset( $s['mega_p3'] ) ? $s['mega_p3'] : '-' )
		   . ' | mega_p4=' . ( isset( $s['mega_p4'] ) ? $s['mega_p4'] : '-' )
		   . ' | mega_items_ordered=' . implode( ',', $debug_mega_items )
		   . ' | panels_map_keys=' . implode( ',', array_keys( $panels_map ) )
		   . ' -->';

		if ( empty( $menu_items ) ) {
			$menu_items = array(
				array( 'item_id' => 0, 'title' => 'Our Work',     'url' => '#', 'is_mega' => false, 'classes' => '', 'current' => false, 'children' => array() ),
				array( 'item_id' => 0, 'title' => 'Programs',     'url' => '#', 'is_mega' => false, 'classes' => '', 'current' => false, 'children' => array() ),
				array( 'item_id' => 0, 'title' => 'Get Involved', 'url' => '#', 'is_mega' => false, 'classes' => '', 'current' => false, 'children' => array() ),
				array( 'item_id' => 0, 'title' => 'Events',       'url' => '#', 'is_mega' => false, 'classes' => '', 'current' => false, 'children' => array() ),
			);
		}
		?>
		<nav class="pal-megamenu" id="<?php echo esc_attr( $uid ); ?>" aria-label="<?php esc_attr_e( 'Main navigation', 'nashua-pal-core' ); ?>">
			<ul class="pal-megamenu__nav" role="menubar">
				<?php foreach ( $menu_items as $item ) : ?>
					<?php
					if ( $item['is_mega'] ) :
						$panel_key = (int) $item['item_id'];
						$panel     = isset( $panels_map[ $panel_key ] ) ? $panels_map[ $panel_key ] : null;
						$panel_id  = $uid . '-panel-' . sanitize_title( $item['title'] );

						$grid_cols = 0;
						if ( $panel ) {
							$grid_cols = (int) $panel['num_cols'];
							if ( 'yes' === $panel['spotlight_show'] ) {
								$grid_cols++;
							}
						}
					?>
						<li class="pal-megamenu__nav-item pal-megamenu__nav-item--mega<?php echo $item['current'] ? ' is-active' : ''; ?>"
						    role="none"
						    data-pal-megamenu-trigger>
							<a class="pal-megamenu__nav-link"
							   href="<?php echo esc_url( $item['url'] ); ?>"
							   role="menuitem"
							   aria-haspopup="true"
							   aria-expanded="false"
							   aria-controls="<?php echo esc_attr( $panel_id ); ?>">
								<?php echo esc_html( $item['title'] ); ?>
								<svg class="pal-megamenu__nav-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</a>

							<?php if ( $panel ) : ?>
							<div class="pal-megamenu__panel" id="<?php echo esc_attr( $panel_id ); ?>" role="menu" aria-label="<?php echo esc_attr( $item['title'] ); ?>">
								<div class="pal-megamenu__grid" style="--mm-cols:<?php echo (int) $grid_cols; ?>;">
									<?php
									foreach ( $panel['cols'] as $col_data ) {
										$this->render_column( $col_data, $s );
									}
									if ( 'yes' === $panel['spotlight_show'] ) {
										$this->render_spotlight( $panel, $s );
									}
									?>
								</div>
							</div>
							<?php endif; ?>
						</li>

					<?php elseif ( ! empty( $item['children'] ) ) : ?>
						<?php $sub_id = $uid . '-sub-' . sanitize_title( $item['title'] ); ?>
						<li class="pal-megamenu__nav-item pal-megamenu__nav-item--has-sub<?php echo $item['current'] ? ' is-active' : ''; ?> <?php echo esc_attr( $item['classes'] ); ?>"
						    role="none"
						    data-pal-megamenu-dropdown>
							<a class="pal-megamenu__nav-link"
							   href="<?php echo esc_url( $item['url'] ); ?>"
							   role="menuitem"
							   aria-haspopup="true"
							   aria-expanded="false"
							   aria-controls="<?php echo esc_attr( $sub_id ); ?>">
								<?php echo esc_html( $item['title'] ); ?>
								<svg class="pal-megamenu__nav-arrow" width="10" height="6" viewBox="0 0 10 6" fill="none" aria-hidden="true"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</a>
							<ul class="pal-megamenu__sub" id="<?php echo esc_attr( $sub_id ); ?>" role="menu">
								<?php foreach ( $item['children'] as $child ) : ?>
									<li role="none">
										<a class="pal-megamenu__sub-link<?php echo $child['current'] ? ' is-current' : ''; ?>"
										   href="<?php echo esc_url( $child['url'] ); ?>"
										   role="menuitem"><?php echo esc_html( $child['title'] ); ?></a>
									</li>
								<?php endforeach; ?>
							</ul>
						</li>

					<?php else : ?>
						<li class="pal-megamenu__nav-item<?php echo $item['current'] ? ' is-active' : ''; ?> <?php echo esc_attr( $item['classes'] ); ?>"
						    role="none">
							<a class="pal-megamenu__nav-link"
							   href="<?php echo esc_url( $item['url'] ); ?>"
							   role="menuitem"><?php echo esc_html( $item['title'] ); ?></a>
						</li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>

			<?php if ( $s['donate_text'] ) :
				$donate_href = ! empty( $s['donate_url']['url'] ) ? $s['donate_url']['url'] : '#';
			?>
				<a class="pal-megamenu__donate" href="<?php echo esc_url( $donate_href ); ?>">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
					<?php echo esc_html( $s['donate_text'] ); ?>
				</a>
			<?php endif; ?>
		</nav>

		<!-- Mobile mega menu accordion -->
		<div class="pal-megamenu-mobile" id="<?php echo esc_attr( $mob_id ); ?>" data-pal-megamenu-mobile>
			<?php
			// Build ID → title lookup for mobile panel headers.
			$id_title_map = array();
			foreach ( $menu_items as $mi ) {
				if ( ! empty( $mi['item_id'] ) ) {
					$id_title_map[ (int) $mi['item_id'] ] = $mi['title'];
				}
			}
			$panel_count = count( $panels_map );
			foreach ( $panels_map as $panel_item_id => $panel ) :
				$panel_title = isset( $id_title_map[ $panel_item_id ] ) ? $id_title_map[ $panel_item_id ] : '';
			?>
				<?php if ( $panel_count > 1 && $panel_title ) : ?>
					<div class="pal-megamenu-mobile__panel-header">
						<?php echo esc_html( $panel_title ); ?>
					</div>
				<?php endif; ?>

				<?php foreach ( $panel['cols'] as $cidx => $col_data ) :
					if ( empty( $col_data['heading'] ) && empty( $col_data['links'] ) ) { continue; }
					$sec_id = $mob_id . '-p' . $panel_item_id . '-c' . $cidx;
				?>
					<div class="pal-megamenu-mobile__group">
						<button class="pal-megamenu-mobile__toggle"
						        aria-expanded="false"
						        aria-controls="<?php echo esc_attr( $sec_id ); ?>"
						        type="button">
							<span><?php echo esc_html( $col_data['heading'] ? $col_data['heading'] : $panel_title ); ?></span>
							<span class="pal-megamenu-mobile__icon" aria-hidden="true">+</span>
						</button>
						<div class="pal-megamenu-mobile__content" id="<?php echo esc_attr( $sec_id ); ?>" role="region" hidden>
							<ul>
								<?php foreach ( $col_data['links'] as $link ) :
									$url = ! empty( $link['url']['url'] ) ? $link['url']['url'] : '#';
								?>
									<li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
								<?php endforeach; ?>
							</ul>
						</div>
					</div>
				<?php endforeach; ?>

				<?php if ( 'yes' === $panel['spotlight_show'] && ! empty( $panel['spotlight']['title'] ) ) :
					$spot = $panel['spotlight'];
				?>
					<div class="pal-megamenu-mobile__spotlight" style="background:<?php echo $primary; ?>;">
						<?php if ( ! empty( $spot['image']['url'] ) ) : ?>
							<img src="<?php echo esc_url( $spot['image']['url'] ); ?>"
							     alt="<?php echo esc_attr( $spot['title'] ); ?>"
							     loading="lazy" />
						<?php endif; ?>
						<?php if ( $spot['label'] ) : ?>
							<span class="pal-megamenu-mobile__spotlight-label" style="color:<?php echo $accent; ?>;"><?php echo esc_html( $spot['label'] ); ?></span>
						<?php endif; ?>
						<h4><?php echo esc_html( $spot['title'] ); ?></h4>
						<?php if ( $spot['desc'] ) : ?>
							<p><?php echo esc_html( $spot['desc'] ); ?></p>
						<?php endif; ?>
						<?php if ( $spot['btn_text'] ) :
							$surl = ! empty( $spot['btn_url']['url'] ) ? $spot['btn_url']['url'] : '#';
						?>
							<a class="pal-megamenu-mobile__spotlight-btn" href="<?php echo esc_url( $surl ); ?>" style="background:<?php echo $accent; ?>; color:<?php echo $primary; ?>;">
								<?php echo esc_html( $spot['btn_text'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

			<?php endforeach; ?>
		</div>
		<?php
	}
}
