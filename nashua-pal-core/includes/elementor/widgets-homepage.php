<?php
/**
 * Nashua PAL Elementor widgets — Homepage features.
 *
 * Stats, programs, events, news, partners, get-involved. These wrap the
 * dynamic list shortcodes and expose their key attributes (count, view,
 * columns, show_image, order) as Elementor controls.
 *
 * @package NashuaPalCore
 * @since 1.8.56
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/** Helper: Yes/No control values used by show_image. */
function npal_yes_no_choices() {
	return array(
		''    => __( 'Default', 'nashua-pal-core' ),
		'yes' => __( 'Yes',     'nashua-pal-core' ),
		'no'  => __( 'No',      'nashua-pal-core' ),
	);
}

/** Helper: grid/list view choices. */
function npal_view_choices() {
	return array(
		''     => __( 'Default', 'nashua-pal-core' ),
		'grid' => __( 'Grid',    'nashua-pal-core' ),
		'list' => __( 'List',    'nashua-pal-core' ),
	);
}

class NPAL_Widget_Stats_Bar extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_stats_bar';
	public function get_name()     { return 'pal_stats_bar'; }
	public function get_title()    { return __( 'PAL Stats Bar', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-counter'; }
	public function get_keywords() { return array( 'pal', 'stats', 'impact', 'counter', 'numbers' ); }
}

class NPAL_Widget_Single_Stat extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_stat';

	public function get_name()     { return 'pal_stat'; }
	public function get_title()    { return __( 'PAL Single Stat', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-number-field'; }
	public function get_keywords() { return array( 'pal', 'stat', 'number', 'counter' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Stat', 'nashua-pal-core' ) ) );

		$this->add_control( 'number', array(
			'label'   => __( 'Number', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '20+',
		) );
		$this->add_control( 'label', array(
			'label'   => __( 'Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Years of Service', 'nashua-pal-core' ),
		) );
		$this->add_control( 'icon', array(
			'label'       => __( 'Icon', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => 'shield-check',
			'description' => __( 'Built-in icon name (shield-check, users, target, handshake) or a Font Awesome class.', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'number' => isset( $s['number'] ) ? $s['number'] : '',
			'label'  => isset( $s['label'] )  ? $s['label']  : '',
			'icon'   => isset( $s['icon'] )   ? $s['icon']   : '',
		);
	}
}

class NPAL_Widget_Program_Cards extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_program_cards';

	public function get_name()     { return 'pal_program_cards'; }
	public function get_title()    { return __( 'PAL Programs Grid', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-posts-grid'; }
	public function get_keywords() { return array( 'pal', 'programs', 'cards', 'grid' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Query', 'nashua-pal-core' ) ) );

		$this->add_control( 'count', array(
			'label'   => __( 'How many', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 4,
			'min'     => -1,
			'max'     => 50,
			'description' => __( '-1 = show all.', 'nashua-pal-core' ),
		) );
		$this->add_control( 'view', array(
			'label'   => __( 'View', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => npal_view_choices(),
			'default' => 'grid',
		) );
		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 3,
			'min'     => 1,
			'max'     => 4,
		) );
		$this->add_control( 'featured_only', array(
			'label'   => __( 'Show', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'yes' => __( 'Featured programs only (homepage)', 'nashua-pal-core' ),
				'no'  => __( 'All programs (archive)', 'nashua-pal-core' ),
			),
			'default' => 'yes',
			'description' => __( 'Featured = "Show on homepage" toggle on each Program.', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'count'         => isset( $s['count'] )         ? (string) $s['count']   : '',
			'view'          => isset( $s['view'] )          ? $s['view']             : '',
			'columns'       => isset( $s['columns'] )       ? (string) $s['columns'] : '',
			'featured_only' => isset( $s['featured_only'] ) ? $s['featured_only']    : 'yes',
		);
	}
}

class NPAL_Widget_Upcoming_Events extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_upcoming_events';

	public function get_name()     { return 'pal_upcoming_events'; }
	public function get_title()    { return __( 'PAL Upcoming Events', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-calendar'; }
	public function get_keywords() { return array( 'pal', 'events', 'upcoming', 'calendar' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Query', 'nashua-pal-core' ) ) );

		$this->add_control( 'count', array(
			'label'   => __( 'How many', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 3,
			'min'     => 1,
			'max'     => 24,
		) );
		$this->add_control( 'view', array(
			'label'   => __( 'View', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => npal_view_choices(),
			'default' => 'grid',
		) );
		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 3,
			'min'     => 1,
			'max'     => 4,
		) );
		$this->add_control( 'show_image', array(
			'label'   => __( 'Show image', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => npal_yes_no_choices(),
			'default' => 'yes',
		) );
		$this->add_control( 'image_size', array(
			'label'       => __( 'Image size', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => __( 'medium_large', 'nashua-pal-core' ),
			'description' => __( 'WP image size slug (thumbnail, medium, medium_large, large, full).', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'count'      => isset( $s['count'] )      ? (string) $s['count']   : '',
			'view'       => isset( $s['view'] )       ? $s['view']             : '',
			'columns'    => isset( $s['columns'] )    ? (string) $s['columns'] : '',
			'show_image' => isset( $s['show_image'] ) ? $s['show_image']       : '',
			'image_size' => isset( $s['image_size'] ) ? $s['image_size']       : '',
		);
	}
}

class NPAL_Widget_Events extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_events';

	public function get_name()     { return 'pal_events'; }
	public function get_title()    { return __( 'PAL Events (full)', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-calendar'; }
	public function get_keywords() { return array( 'pal', 'events', 'past', 'upcoming', 'archive' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Query', 'nashua-pal-core' ) ) );

		$this->add_control( 'when', array(
			'label'   => __( 'When', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				''         => __( 'All',      'nashua-pal-core' ),
				'upcoming' => __( 'Upcoming', 'nashua-pal-core' ),
				'past'     => __( 'Past',     'nashua-pal-core' ),
			),
			'default' => 'upcoming',
		) );
		$this->add_control( 'count', array(
			'label'   => __( 'How many', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 6,
			'min'     => 1,
			'max'     => 50,
		) );
		$this->add_control( 'view', array(
			'label'   => __( 'View', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => npal_view_choices(),
			'default' => 'grid',
		) );
		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 3,
			'min'     => 1,
			'max'     => 4,
		) );
		$this->add_control( 'show_image', array(
			'label'   => __( 'Show image', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => npal_yes_no_choices(),
			'default' => 'yes',
		) );
		$this->add_control( 'order', array(
			'label'   => __( 'Order', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array( 'ASC' => 'ASC', 'DESC' => 'DESC' ),
			'default' => 'ASC',
		) );
		$this->add_control( 'sources', array(
			'label'       => __( 'Sources', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'description' => __( 'Comma-separated. e.g. "manual,community-calendar".', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'when'       => isset( $s['when'] )       ? $s['when']             : '',
			'count'      => isset( $s['count'] )      ? (string) $s['count']   : '',
			'view'       => isset( $s['view'] )       ? $s['view']             : '',
			'columns'    => isset( $s['columns'] )    ? (string) $s['columns'] : '',
			'show_image' => isset( $s['show_image'] ) ? $s['show_image']       : '',
			'order'      => isset( $s['order'] )      ? $s['order']            : '',
			'sources'    => isset( $s['sources'] )    ? $s['sources']          : '',
		);
	}
}

class NPAL_Widget_Event_Date_Pill extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_event_date_pill';

	public function get_name()     { return 'pal_event_date_pill'; }
	public function get_title()    { return __( 'PAL Event Date Pill', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-clock-o'; }
	public function get_keywords() { return array( 'pal', 'event', 'date', 'pill', 'badge' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Date', 'nashua-pal-core' ) ) );

		$this->add_control( 'date', array(
			'label'       => __( 'Date', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::DATE_TIME,
			'placeholder' => 'YYYY-MM-DD',
			'description' => __( 'When this widget is dropped on a single event page, leave empty to use the event\'s date.', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array( 'date' => isset( $s['date'] ) ? $s['date'] : '' );
	}
}

class NPAL_Widget_Latest_News extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_latest_news';

	public function get_name()     { return 'pal_latest_news'; }
	public function get_title()    { return __( 'PAL Latest News', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-post-list'; }
	public function get_keywords() { return array( 'pal', 'news', 'posts', 'latest', 'blog' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Query', 'nashua-pal-core' ) ) );

		$this->add_control( 'count', array(
			'label'   => __( 'How many', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 3,
			'min'     => 1,
			'max'     => 24,
		) );
		$this->add_control( 'view', array(
			'label'   => __( 'View', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => npal_view_choices(),
			'default' => 'grid',
		) );
		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 3,
			'min'     => 1,
			'max'     => 4,
		) );

		$this->add_control( 'source', array(
			'label'   => __( 'Content source', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				''            => __( 'All (default)', 'nashua-pal-core' ),
				'posts'       => __( 'Blog posts only', 'nashua-pal-core' ),
				'newsletters' => __( 'Newsletters only', 'nashua-pal-core' ),
				'events'      => __( 'Events only', 'nashua-pal-core' ),
			),
			'default' => '',
		) );
		$this->add_control( 'show_filter', array(
			'label'   => __( 'Show filter tabs', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				''     => __( 'No (default)', 'nashua-pal-core' ),
				'true' => __( 'Yes — show News / Newsletter / Events tabs', 'nashua-pal-core' ),
			),
			'default' => '',
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'newsletter_cta', array( 'label' => __( 'Newsletter CTA', 'nashua-pal-core' ) ) );

		$this->add_control( 'show_cta', array(
			'label'   => __( 'Show newsletter CTA', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				''     => __( 'No (default)', 'nashua-pal-core' ),
				'true' => __( 'Yes', 'nashua-pal-core' ),
			),
			'default' => '',
		) );
		$this->add_control( 'cta_label', array(
			'label'   => __( 'Button label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Sign Up for PAL Updates', 'nashua-pal-core' ),
		) );
		$this->add_control( 'cta_url', array(
			'label'       => __( 'Button URL', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::URL,
			'default'     => array( 'url' => 'https://lp.constantcontactpages.com/cu/sfyDpKk/PALnewsletters', 'is_external' => true ),
			'placeholder' => 'https://…',
		) );
		$this->add_control( 'cta_text', array(
			'label'   => __( 'Description', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => __( 'Get the latest news, events, and stories delivered to your inbox.', 'nashua-pal-core' ),
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		$cta_url = '';
		if ( ! empty( $s['cta_url']['url'] ) ) {
			$cta_url = $s['cta_url']['url'];
		} elseif ( is_string( $s['cta_url'] ?? '' ) ) {
			$cta_url = $s['cta_url'];
		}
		return array(
			'count'       => isset( $s['count'] )       ? (string) $s['count']   : '',
			'view'        => isset( $s['view'] )        ? $s['view']             : '',
			'columns'     => isset( $s['columns'] )     ? (string) $s['columns'] : '',
			'source'      => isset( $s['source'] )      ? $s['source']           : '',
			'show_filter' => isset( $s['show_filter'] ) ? $s['show_filter']      : '',
			'show_cta'    => isset( $s['show_cta'] )    ? $s['show_cta']         : '',
			'cta_label'   => isset( $s['cta_label'] )   ? $s['cta_label']        : '',
			'cta_url'     => $cta_url,
			'cta_text'    => isset( $s['cta_text'] )    ? $s['cta_text']         : '',
		);
	}
}

class NPAL_Widget_News_Archive extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_news_archive';

	public function get_name()     { return 'pal_news_archive'; }
	public function get_title()    { return __( 'PAL News Archive', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-archive'; }
	public function get_keywords() { return array( 'pal', 'news', 'archive', 'newsletter', 'all' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Settings', 'nashua-pal-core' ) ) );

		$this->add_control( 'per_page', array(
			'label'   => __( 'Items per page', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 12,
			'min'     => 3,
			'max'     => 48,
		) );
		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 3,
			'min'     => 1,
			'max'     => 4,
		) );

		$this->end_controls_section();
	}

	protected function args_for_shortcode() {
		$s = $this->get_settings_for_display();
		return array(
			'per_page' => isset( $s['per_page'] ) ? (string) $s['per_page'] : '',
			'columns'  => isset( $s['columns'] )  ? (string) $s['columns']  : '',
		);
	}
}

class NPAL_Widget_Partner_Carousel extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_partner_carousel';
	public function get_name()     { return 'pal_partner_carousel'; }
	public function get_title()    { return __( 'PAL Partners Carousel', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-slides'; }
	public function get_keywords() { return array( 'pal', 'partners', 'carousel', 'logos', 'sponsors' ); }
}

class NPAL_Widget_Community_Partners extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_community_partners';
	public function get_name()     { return 'pal_community_partners'; }
	public function get_title()    { return __( 'PAL Community Partners', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-gallery-grid'; }
	public function get_keywords() { return array( 'pal', 'partners', 'community', 'sponsors', 'tiers' ); }
}

class NPAL_Widget_Partner_Single extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_partner_single';
	public function get_name()     { return 'pal_partner_single'; }
	public function get_title()    { return __( 'PAL Partner Profile', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-person'; }
	public function get_keywords() { return array( 'pal', 'partner', 'sponsor', 'profile', 'single' ); }
}

class NPAL_Widget_Get_Involved extends NPAL_Elementor_Widget_Base {
	protected $shortcode = 'pal_get_involved';
	public function get_name()     { return 'pal_get_involved'; }
	public function get_title()    { return __( 'PAL Get Involved Strip', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-handshake'; }
	public function get_keywords() { return array( 'pal', 'volunteer', 'mentor', 'sponsor', 'cta' ); }
}
