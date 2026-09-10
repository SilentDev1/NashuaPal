<?php
/**
 * Nashua PAL Elementor widgets — Inner-page building blocks.
 *
 * These widgets replace the inline-HTML sections used by the older page
 * templates (mission, leadership, volunteer, donate, policies, contact,
 * impact, etc.) with panel-driven Elementor controls. Goal: editors compose
 * every page section by dragging widgets and filling in the right panel —
 * no HTML editing required.
 *
 * Widgets defined here:
 *   - NPAL_Widget_Hero_Banner   (pal_hero_banner)   — small hero: breadcrumb + title + subtitle
 *   - NPAL_Widget_Card_Grid     (pal_card_grid)     — auto-fit grid of cards (image/icon + title + body + button)
 *   - NPAL_Widget_Tier_List     (pal_tier_list)     — pricing / partnership / donation tiers
 *   - NPAL_Widget_Icon_List     (pal_icon_list)     — icon + label/link pairs (horizontal or vertical)
 *   - NPAL_Widget_Content_Box   (pal_content_box)   — single highlighted card (icon + title + body + button)
 *   - NPAL_Widget_Quote         (pal_quote)         — testimonial / pull-quote with author
 *
 * @package NashuaPalCore
 * @since 1.8.72
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
if ( ! class_exists( '\\Elementor\\Widget_Base' ) ) { return; }

/* ============================================================================
   Widget: PAL Hero Banner — smaller hero for inner pages
   ========================================================================= */

class NPAL_Widget_Hero_Banner extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_hero_banner'; }
	public function get_title()    { return __( 'PAL Hero Banner', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-banner'; }
	public function get_keywords() { return array( 'pal', 'hero', 'banner', 'page', 'breadcrumb' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Banner', 'nashua-pal-core' ) ) );

		$this->add_control( 'breadcrumb', array(
			'label'       => __( 'Breadcrumb text', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'Home / About',
			'description' => __( 'Optional. Leave empty to hide.', 'nashua-pal-core' ),
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Page Title',
		) );
		$this->add_control( 'subtitle', array(
			'label' => __( 'Subtitle', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
		) );
		$this->add_control( 'align', array(
			'label'   => __( 'Alignment', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array( 'left' => 'Left', 'center' => 'Center' ),
			'default' => 'center',
		) );
		$this->add_control( 'theme', array(
			'label'   => __( 'Color theme', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'navy'  => __( 'Navy on white', 'nashua-pal-core' ),
				'dark'  => __( 'White on navy', 'nashua-pal-core' ),
				'light' => __( 'Navy on light gray', 'nashua-pal-core' ),
			),
			'default' => 'navy',
		) );
		$this->add_control( 'padding', array(
			'label'      => __( 'Vertical padding', 'nashua-pal-core' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 30, 'max' => 160, 'step' => 5 ) ),
			'default'    => array( 'unit' => 'px', 'size' => 70 ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s        = $this->get_settings_for_display();
		$align    = in_array( $s['align'], array( 'left', 'center' ), true ) ? $s['align'] : 'center';
		$pad      = isset( $s['padding']['size'] ) ? (int) $s['padding']['size'] : 70;
		$themes   = array(
			'navy'  => array( 'bg' => '#fff',     'fg' => '#071F3F', 'sub' => '#3A4658', 'crumb' => '#F4A51C' ),
			'dark'  => array( 'bg' => '#071F3F', 'fg' => '#fff',    'sub' => '#C4CCD8', 'crumb' => '#F4A51C' ),
			'light' => array( 'bg' => '#F4F6FA', 'fg' => '#071F3F', 'sub' => '#3A4658', 'crumb' => '#F4A51C' ),
		);
		$t = isset( $themes[ $s['theme'] ] ) ? $themes[ $s['theme'] ] : $themes['navy'];
		?>
		<section class="pal-hero-banner" style="background:<?php echo esc_attr( $t['bg'] ); ?>;padding:<?php echo (int) $pad; ?>px 24px;text-align:<?php echo esc_attr( $align ); ?>;">
			<div style="max-width:900px;margin:0 auto;">
				<?php if ( ! empty( $s['breadcrumb'] ) ) : ?>
					<div style="color:<?php echo esc_attr( $t['crumb'] ); ?>;font-family:Montserrat,sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:0.14em;font-size:0.78rem;margin-bottom:14px;"><?php echo esc_html( $s['breadcrumb'] ); ?></div>
				<?php endif; ?>
				<?php if ( ! empty( $s['title'] ) ) : ?>
					<h1 style="color:<?php echo esc_attr( $t['fg'] ); ?>;font-family:Montserrat,sans-serif;font-size:clamp(1.8rem,4vw,2.8rem);font-weight:800;margin:0 0 16px;line-height:1.1;"><?php echo esc_html( $s['title'] ); ?></h1>
				<?php endif; ?>
				<?php if ( ! empty( $s['subtitle'] ) ) : ?>
					<p style="color:<?php echo esc_attr( $t['sub'] ); ?>;font-size:1.05rem;max-width:680px;<?php echo 'center' === $align ? 'margin:0 auto;' : 'margin:0;'; ?>line-height:1.6;"><?php echo esc_html( $s['subtitle'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}

/* ============================================================================
   Widget: PAL Card Grid — flexible auto-fit grid of cards
   ========================================================================= */

class NPAL_Widget_Card_Grid extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_card_grid'; }
	public function get_title()    { return __( 'PAL Card Grid', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-posts-grid'; }
	public function get_keywords() { return array( 'pal', 'cards', 'grid', 'features', 'pillars', 'team', 'staff', 'values' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Card Grid', 'nashua-pal-core' ) ) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array( '2' => '2', '3' => '3', '4' => '4', 'auto' => 'Auto-fit (responsive)' ),
			'default' => '3',
		) );
		$this->add_control( 'card_style', array(
			'label'   => __( 'Card style', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'image'      => __( 'Image on top', 'nashua-pal-core' ),
				'icon'       => __( 'Large icon on top', 'nashua-pal-core' ),
				'icon_inline'=> __( 'Inline icon (small, above title)', 'nashua-pal-core' ),
				'text'       => __( 'Text only', 'nashua-pal-core' ),
			),
			'default' => 'icon',
		) );
		$this->add_control( 'bg', array(
			'label'   => __( 'Section background', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'white' => __( 'White', 'nashua-pal-core' ),
				'light' => __( 'Light gray', 'nashua-pal-core' ),
				'navy'  => __( 'Navy', 'nashua-pal-core' ),
			),
			'default' => 'white',
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'image', array(
			'label'   => __( 'Image', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );
		$repeater->add_control( 'icon_class', array(
			'label'       => __( 'Icon class (Font Awesome)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'fas fa-basketball',
		) );
		$repeater->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Card title',
		) );
		$repeater->add_control( 'subtitle', array(
			'label'       => __( 'Subtitle (role / meta)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'Executive Director',
		) );
		$repeater->add_control( 'body', array(
			'label'   => __( 'Body text', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Short description goes here.',
		) );
		$repeater->add_control( 'cta_label', array(
			'label'       => __( 'Button label', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => __( 'Learn More', 'nashua-pal-core' ),
		) );
		$repeater->add_control( 'cta_url', array(
			'label' => __( 'Button URL', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::TEXT,
		) );

		$this->add_control( 'cards', array(
			'label'   => __( 'Cards', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::REPEATER,
			'fields'  => $repeater->get_controls(),
			'default' => array(
				array( 'icon_class' => 'fas fa-heart',      'title' => 'Mentor',     'body' => 'Guide a young person through life’s decisions.' ),
				array( 'icon_class' => 'fas fa-bullseye',   'title' => 'Coach',      'body' => 'Lead practice and games for one of our teams.' ),
				array( 'icon_class' => 'fas fa-hands-helping','title'=> 'Volunteer', 'body' => 'Lend a hand at events and community programs.' ),
			),
			'title_field' => '{{{ title }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$cols  = $s['columns'];
		$style = $s['card_style'];
		$bgmap = array(
			'white' => array( 'bg' => '#fff',     'card' => '#fff',     'fg' => '#071F3F', 'sub' => '#3A4658', 'border' => '#E8EDF5' ),
			'light' => array( 'bg' => '#F4F6FA', 'card' => '#fff',     'fg' => '#071F3F', 'sub' => '#3A4658', 'border' => '#E8EDF5' ),
			'navy'  => array( 'bg' => '#071F3F', 'card' => '#0D2A50', 'fg' => '#fff',    'sub' => '#C4CCD8', 'border' => 'rgba(255,255,255,0.1)' ),
		);
		$bg = isset( $bgmap[ $s['bg'] ] ) ? $bgmap[ $s['bg'] ] : $bgmap['white'];

		$grid_tpl = 'auto' === $cols
			? 'grid-template-columns:repeat(auto-fit,minmax(240px,1fr));'
			: 'grid-template-columns:repeat(' . (int) $cols . ',1fr);';
		?>
		<section class="pal-card-grid" style="background:<?php echo esc_attr( $bg['bg'] ); ?>;padding:60px 24px;">
			<div style="max-width:1200px;margin:0 auto;display:grid;<?php echo esc_attr( $grid_tpl ); ?>gap:24px;">
				<?php foreach ( (array) $s['cards'] as $card ) : ?>
					<article style="background:<?php echo esc_attr( $bg['card'] ); ?>;border:1px solid <?php echo esc_attr( $bg['border'] ); ?>;border-radius:14px;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 6px 18px rgba(11,31,58,0.06);">
						<?php if ( 'image' === $style && ! empty( $card['image']['url'] ) ) : ?>
							<div style="aspect-ratio:4/3;background:url('<?php echo esc_url( $card['image']['url'] ); ?>') center/cover no-repeat;"></div>
						<?php endif; ?>
						<div style="padding:24px 22px;flex:1;display:flex;flex-direction:column;gap:8px;">
							<?php if ( 'icon' === $style && ! empty( $card['icon_class'] ) ) : ?>
								<div style="width:60px;height:60px;border-radius:50%;background:#F4A51C;color:#071F3F;display:flex;align-items:center;justify-content:center;font-size:24px;margin-bottom:6px;"><i class="<?php echo esc_attr( $card['icon_class'] ); ?>"></i></div>
							<?php elseif ( 'icon_inline' === $style && ! empty( $card['icon_class'] ) ) : ?>
								<div style="color:#F4A51C;font-size:22px;margin-bottom:2px;"><i class="<?php echo esc_attr( $card['icon_class'] ); ?>"></i></div>
							<?php endif; ?>
							<h3 style="font-family:Montserrat,sans-serif;font-size:1.15rem;font-weight:700;color:<?php echo esc_attr( $bg['fg'] ); ?>;margin:0;line-height:1.25;"><?php echo esc_html( $card['title'] ); ?></h3>
							<?php if ( ! empty( $card['subtitle'] ) ) : ?>
								<p style="color:#F4A51C;font-family:Montserrat,sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;font-size:0.72rem;margin:0;"><?php echo esc_html( $card['subtitle'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $card['body'] ) ) : ?>
								<p style="color:<?php echo esc_attr( $bg['sub'] ); ?>;font-size:0.95rem;line-height:1.55;margin:6px 0 0;"><?php echo wp_kses_post( wpautop( $card['body'] ) ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $card['cta_label'] ) ) : ?>
								<a href="<?php echo esc_url( $card['cta_url'] ? $card['cta_url'] : '#' ); ?>" style="display:inline-flex;align-items:center;margin-top:14px;color:#F4A51C;font-family:Montserrat,sans-serif;font-weight:700;text-decoration:none;font-size:0.92rem;align-self:flex-start;"><?php echo esc_html( $card['cta_label'] ); ?> →</a>
							<?php endif; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}

/* ============================================================================
   Widget: PAL Tier List — pricing / partnership / donation tiers
   ========================================================================= */

class NPAL_Widget_Tier_List extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_tier_list'; }
	public function get_title()    { return __( 'PAL Tier List', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-price-table'; }
	public function get_keywords() { return array( 'pal', 'tier', 'pricing', 'sponsorship', 'partner', 'donate' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Tiers', 'nashua-pal-core' ) ) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array( '2' => '2', '3' => '3', '4' => '4' ),
			'default' => '3',
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'name', array(
			'label'   => __( 'Tier name', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Champion',
		) );
		$repeater->add_control( 'price', array(
			'label'       => __( 'Amount / price (display)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => '$5,000+ / Year',
		) );
		$repeater->add_control( 'tagline', array(
			'label' => __( 'Short tagline', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
		) );
		$repeater->add_control( 'benefits', array(
			'label'       => __( 'Benefits (one per line)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXTAREA,
			'placeholder' => "Logo placement on website\nName mention in newsletter\nVIP event invitations",
		) );
		$repeater->add_control( 'featured', array(
			'label'        => __( 'Featured (highlight this tier)', 'nashua-pal-core' ),
			'type'         => \Elementor\Controls_Manager::SWITCHER,
			'return_value' => 'yes',
		) );
		$repeater->add_control( 'cta_label', array(
			'label'   => __( 'Button label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Become a Champion',
		) );
		$repeater->add_control( 'cta_url', array(
			'label' => __( 'Button URL', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::TEXT,
		) );

		$this->add_control( 'tiers', array(
			'label'  => __( 'Tiers', 'nashua-pal-core' ),
			'type'   => \Elementor\Controls_Manager::REPEATER,
			'fields' => $repeater->get_controls(),
			'default' => array(
				array( 'name' => 'Friend',    'price' => '$500+',   'tagline' => 'A great place to start.',  'benefits' => "Name on our website\nNewsletter mention", 'cta_label' => 'Become a Friend' ),
				array( 'name' => 'All-Star',  'price' => '$2,500+', 'tagline' => 'Visible community impact.', 'benefits' => "Logo on website\nSocial-media shoutout\nEvent recognition", 'cta_label' => 'Become an All-Star', 'featured' => 'yes' ),
				array( 'name' => 'Champion',  'price' => '$5,000+', 'tagline' => 'Top-tier supporter.',       'benefits' => "Logo placement everywhere\nVIP event invitations\nQuarterly impact briefings", 'cta_label' => 'Become a Champion' ),
			),
			'title_field' => '{{{ name }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$cols = (int) $s['columns'];
		?>
		<section class="pal-tier-list" style="background:#F4F6FA;padding:60px 24px;">
		<div style="max-width:1200px;margin:0 auto;display:grid;grid-template-columns:repeat(<?php echo $cols; ?>,1fr);gap:24px;">
			<?php foreach ( (array) $s['tiers'] as $tier ) :
				$featured = 'yes' === $tier['featured'];
				$bg       = $featured ? '#071F3F' : '#fff';
				$fg       = $featured ? '#fff'    : '#071F3F';
				$sub      = $featured ? '#C4CCD8' : '#3A4658';
				$btn_bg   = $featured ? '#F4A51C' : '#071F3F';
				$btn_fg   = $featured ? '#071F3F' : '#fff';
			?>
				<article style="background:<?php echo esc_attr( $bg ); ?>;color:<?php echo esc_attr( $fg ); ?>;border-radius:14px;padding:32px 26px;box-shadow:0 8px 24px rgba(11,31,58,0.08);<?php echo $featured ? 'transform:scale(1.04);border:2px solid #F4A51C;' : 'border:1px solid #E8EDF5;'; ?>display:flex;flex-direction:column;gap:14px;">
					<?php if ( $featured ) : ?>
						<span style="align-self:flex-start;background:#F4A51C;color:#071F3F;padding:4px 12px;border-radius:6px;font-family:Montserrat,sans-serif;font-weight:700;font-size:0.7rem;letter-spacing:0.12em;text-transform:uppercase;">Most Popular</span>
					<?php endif; ?>
					<h3 style="margin:0;font-family:Montserrat,sans-serif;font-weight:800;font-size:1.5rem;line-height:1.15;"><?php echo esc_html( $tier['name'] ); ?></h3>
					<?php if ( ! empty( $tier['price'] ) ) : ?>
						<div style="font-family:Montserrat,sans-serif;font-weight:800;font-size:1.6rem;color:#F4A51C;line-height:1;"><?php echo esc_html( $tier['price'] ); ?></div>
					<?php endif; ?>
					<?php if ( ! empty( $tier['tagline'] ) ) : ?>
						<p style="color:<?php echo esc_attr( $sub ); ?>;margin:0;font-size:0.95rem;line-height:1.5;"><?php echo esc_html( $tier['tagline'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $tier['benefits'] ) ) :
						$lines = array_filter( array_map( 'trim', preg_split( '/\r?\n/', $tier['benefits'] ) ) );
					?>
						<ul style="list-style:none;padding:0;margin:6px 0 0;display:flex;flex-direction:column;gap:8px;">
							<?php foreach ( $lines as $line ) : ?>
								<li style="color:<?php echo esc_attr( $sub ); ?>;font-size:0.92rem;display:flex;align-items:start;gap:8px;line-height:1.5;"><span style="color:#F4A51C;flex-shrink:0;">✓</span><?php echo esc_html( $line ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if ( ! empty( $tier['cta_label'] ) ) : ?>
						<a href="<?php echo esc_url( $tier['cta_url'] ? $tier['cta_url'] : '#' ); ?>" style="display:inline-block;text-align:center;margin-top:auto;background:<?php echo esc_attr( $btn_bg ); ?>;color:<?php echo esc_attr( $btn_fg ); ?>;padding:12px 22px;border-radius:8px;font-family:Montserrat,sans-serif;font-weight:700;font-size:0.92rem;text-decoration:none;"><?php echo esc_html( $tier['cta_label'] ); ?></a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>
		</section>
		<?php
	}
}

/* ============================================================================
   Widget: PAL Icon List — icon + label/link rows
   ========================================================================= */

class NPAL_Widget_Icon_List extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_icon_list'; }
	public function get_title()    { return __( 'PAL Icon List', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-bullet-list'; }
	public function get_keywords() { return array( 'pal', 'list', 'icon', 'process', 'steps', 'checklist' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Icon List', 'nashua-pal-core' ) ) );

		$this->add_control( 'layout', array(
			'label'   => __( 'Layout', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'rows'    => __( 'Stacked rows (vertical)', 'nashua-pal-core' ),
				'columns' => __( 'Columns (horizontal)',    'nashua-pal-core' ),
				'process' => __( 'Process steps (numbered)', 'nashua-pal-core' ),
			),
			'default' => 'rows',
		) );
		$this->add_control( 'columns_count', array(
			'label'   => __( 'Columns', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array( '2' => '2', '3' => '3', '4' => '4' ),
			'default' => '3',
			'condition' => array( 'layout!' => 'rows' ),
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'icon_class', array(
			'label'       => __( 'Icon class (Font Awesome)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'fas fa-phone',
		) );
		$repeater->add_control( 'label', array(
			'label'   => __( 'Label', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Label',
		) );
		$repeater->add_control( 'sublabel', array(
			'label' => __( 'Sub-label / detail', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
		) );
		$repeater->add_control( 'url', array(
			'label' => __( 'Link URL', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::TEXT,
		) );

		$this->add_control( 'items', array(
			'label'  => __( 'Items', 'nashua-pal-core' ),
			'type'   => \Elementor\Controls_Manager::REPEATER,
			'fields' => $repeater->get_controls(),
			'default' => array(
				array( 'icon_class' => 'fas fa-user-plus',  'label' => 'Apply',     'sublabel' => 'Fill out the application form.' ),
				array( 'icon_class' => 'fas fa-comments',   'label' => 'Interview', 'sublabel' => 'Meet with our team to discuss the role.' ),
				array( 'icon_class' => 'fas fa-handshake',  'label' => 'Match',     'sublabel' => 'Get paired with a youth.' ),
				array( 'icon_class' => 'fas fa-rocket',     'label' => 'Start',     'sublabel' => 'Begin your mentorship journey.' ),
			),
			'title_field' => '{{{ label }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s          = $this->get_settings_for_display();
		$layout     = $s['layout'];
		$cols_count = (int) $s['columns_count'];
		?>
		<section class="pal-icon-list pal-icon-list--<?php echo esc_attr( $layout ); ?>" style="background:#fff;padding:50px 24px;">
			<div style="max-width:1100px;margin:0 auto;<?php
				if ( 'rows' === $layout ) {
					echo 'display:flex;flex-direction:column;gap:18px;';
				} else {
					echo 'display:grid;grid-template-columns:repeat(' . $cols_count . ',1fr);gap:24px;';
				}
			?>">
				<?php $i = 0; foreach ( (array) $s['items'] as $item ) : $i++;
					$tag      = $item['url'] ? 'a' : 'div';
					$href     = $item['url'] ? ' href="' . esc_url( $item['url'] ) . '"' : '';
					$is_proc  = 'process' === $layout;
					$is_rows  = 'rows' === $layout;
				?>
					<<?php echo $tag . $href; ?> style="<?php echo $tag === 'a' ? 'text-decoration:none;' : ''; ?>display:flex;<?php echo $is_rows ? 'flex-direction:row;' : 'flex-direction:column;'; ?>align-items:<?php echo $is_rows ? 'center' : 'flex-start'; ?>;gap:16px;padding:18px;border:1px solid #E8EDF5;border-radius:12px;background:#fff;color:inherit;">
						<div style="<?php
							if ( $is_proc ) {
								echo 'width:48px;height:48px;border-radius:50%;background:#071F3F;color:#fff;display:flex;align-items:center;justify-content:center;font-family:Montserrat,sans-serif;font-weight:800;font-size:1.1rem;flex-shrink:0;';
							} else {
								echo 'width:48px;height:48px;border-radius:12px;background:#F4A51C;color:#071F3F;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0;';
							}
						?>">
							<?php if ( $is_proc ) : ?>
								<?php echo (int) $i; ?>
							<?php elseif ( ! empty( $item['icon_class'] ) ) : ?>
								<i class="<?php echo esc_attr( $item['icon_class'] ); ?>"></i>
							<?php endif; ?>
						</div>
						<div style="flex:1;min-width:0;">
							<h4 style="margin:0 0 4px;font-family:Montserrat,sans-serif;font-weight:700;color:#071F3F;font-size:1.05rem;line-height:1.2;"><?php echo esc_html( $item['label'] ); ?></h4>
							<?php if ( ! empty( $item['sublabel'] ) ) : ?>
								<p style="margin:0;color:#3A4658;font-size:0.92rem;line-height:1.5;"><?php echo esc_html( $item['sublabel'] ); ?></p>
							<?php endif; ?>
						</div>
					</<?php echo $tag; ?>>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}

/* ============================================================================
   Widget: PAL Content Box — single highlighted card
   ========================================================================= */

class NPAL_Widget_Content_Box extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_content_box'; }
	public function get_title()    { return __( 'PAL Content Box', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-info-box'; }
	public function get_keywords() { return array( 'pal', 'box', 'highlight', 'callout', 'feature' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content Box', 'nashua-pal-core' ) ) );

		$this->add_control( 'theme', array(
			'label'   => __( 'Theme', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'gradient' => __( 'Navy → Yellow gradient', 'nashua-pal-core' ),
				'navy'     => __( 'Solid navy', 'nashua-pal-core' ),
				'yellow'   => __( 'Solid yellow',  'nashua-pal-core' ),
				'light'    => __( 'Light gray',  'nashua-pal-core' ),
			),
			'default' => 'navy',
		) );
		$this->add_control( 'icon_class', array(
			'label'       => __( 'Icon class (Font Awesome)', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'fas fa-trophy',
		) );
		$this->add_control( 'eyebrow', array(
			'label' => __( 'Eyebrow', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::TEXT,
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Highlighted message',
		) );
		$this->add_control( 'body', array(
			'label' => __( 'Body', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
		) );
		$this->add_control( 'cta_label', array(
			'label' => __( 'Primary button label', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::TEXT,
		) );
		$this->add_control( 'cta_url', array(
			'label' => __( 'Primary button URL', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::TEXT,
		) );
		$this->add_control( 'cta2_label', array(
			'label' => __( 'Secondary button label', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::TEXT,
		) );
		$this->add_control( 'cta2_url', array(
			'label' => __( 'Secondary button URL', 'nashua-pal-core' ),
			'type'  => \Elementor\Controls_Manager::TEXT,
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$themes = array(
			'gradient' => array( 'bg' => 'linear-gradient(135deg,#071F3F 0%,#F4A51C 100%)', 'fg' => '#fff',    'sub' => '#FFF4D2', 'btn_bg' => '#fff',     'btn_fg' => '#071F3F', 'btn2_bg' => 'transparent', 'btn2_fg' => '#fff',     'btn2_b' => '#fff' ),
			'navy'     => array( 'bg' => '#071F3F',                                       'fg' => '#fff',    'sub' => '#C4CCD8', 'btn_bg' => '#F4A51C', 'btn_fg' => '#071F3F', 'btn2_bg' => 'transparent', 'btn2_fg' => '#fff',     'btn2_b' => '#fff' ),
			'yellow'   => array( 'bg' => '#F4A51C',                                       'fg' => '#071F3F', 'sub' => '#5C3A07', 'btn_bg' => '#071F3F', 'btn_fg' => '#fff',    'btn2_bg' => 'transparent', 'btn2_fg' => '#071F3F', 'btn2_b' => '#071F3F' ),
			'light'    => array( 'bg' => '#F4F6FA',                                       'fg' => '#071F3F', 'sub' => '#3A4658', 'btn_bg' => '#F4A51C', 'btn_fg' => '#071F3F', 'btn2_bg' => 'transparent', 'btn2_fg' => '#071F3F', 'btn2_b' => '#071F3F' ),
		);
		$t = isset( $themes[ $s['theme'] ] ) ? $themes[ $s['theme'] ] : $themes['navy'];
		?>
		<section class="pal-content-box" style="padding:60px 24px;">
		<div style="max-width:1100px;margin:0 auto;background:<?php echo $t['bg']; ?>;color:<?php echo esc_attr( $t['fg'] ); ?>;border-radius:16px;padding:48px 40px;text-align:center;box-shadow:0 12px 30px rgba(11,31,58,0.12);">
			<?php if ( ! empty( $s['icon_class'] ) ) : ?>
				<div style="font-size:56px;margin-bottom:18px;color:<?php echo esc_attr( $t['fg'] ); ?>;"><i class="<?php echo esc_attr( $s['icon_class'] ); ?>"></i></div>
			<?php endif; ?>
			<?php if ( ! empty( $s['eyebrow'] ) ) : ?>
				<span style="display:inline-block;color:<?php echo esc_attr( $t['fg'] ); ?>;opacity:0.85;font-family:Montserrat,sans-serif;font-weight:700;text-transform:uppercase;letter-spacing:0.18em;font-size:0.78rem;margin-bottom:12px;"><?php echo esc_html( $s['eyebrow'] ); ?></span>
			<?php endif; ?>
			<h2 style="margin:0 0 14px;font-family:Montserrat,sans-serif;font-size:clamp(1.5rem,3vw,2.2rem);font-weight:800;line-height:1.15;color:<?php echo esc_attr( $t['fg'] ); ?>;"><?php echo esc_html( $s['title'] ); ?></h2>
			<?php if ( ! empty( $s['body'] ) ) : ?>
				<p style="color:<?php echo esc_attr( $t['sub'] ); ?>;font-size:1.02rem;line-height:1.65;max-width:680px;margin:0 auto 22px;"><?php echo esc_html( $s['body'] ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $s['cta_label'] ) || ! empty( $s['cta2_label'] ) ) : ?>
				<div style="display:flex;gap:10px;flex-wrap:wrap;justify-content:center;">
					<?php if ( ! empty( $s['cta_label'] ) ) : ?>
						<a href="<?php echo esc_url( $s['cta_url'] ? $s['cta_url'] : '#' ); ?>" style="background:<?php echo esc_attr( $t['btn_bg'] ); ?>;color:<?php echo esc_attr( $t['btn_fg'] ); ?>;padding:13px 24px;border-radius:8px;text-decoration:none;font-family:Montserrat,sans-serif;font-weight:700;font-size:0.95rem;display:inline-block;"><?php echo esc_html( $s['cta_label'] ); ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $s['cta2_label'] ) ) : ?>
						<a href="<?php echo esc_url( $s['cta2_url'] ? $s['cta2_url'] : '#' ); ?>" style="background:<?php echo esc_attr( $t['btn2_bg'] ); ?>;color:<?php echo esc_attr( $t['btn2_fg'] ); ?>;border:1px solid <?php echo esc_attr( $t['btn2_b'] ); ?>;padding:13px 24px;border-radius:8px;text-decoration:none;font-family:Montserrat,sans-serif;font-weight:700;font-size:0.95rem;display:inline-block;"><?php echo esc_html( $s['cta2_label'] ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		</section>
		<?php
	}
}

/* ============================================================================
   Widget: PAL Quote — testimonial / pull-quote
   ========================================================================= */

class NPAL_Widget_Quote extends NPAL_Elementor_Widget_Base {

	public function get_name()     { return 'pal_quote'; }
	public function get_title()    { return __( 'PAL Quote', 'nashua-pal-core' ); }
	public function get_icon()     { return 'eicon-testimonial'; }
	public function get_keywords() { return array( 'pal', 'quote', 'testimonial', 'story' ); }

	protected function register_content_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Quote', 'nashua-pal-core' ) ) );

		$this->add_control( 'quote', array(
			'label'   => __( 'Quote text', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'PAL gave me a place to belong when I needed it most.',
		) );
		$this->add_control( 'author', array(
			'label'   => __( 'Author name', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Jordan, age 14',
		) );
		$this->add_control( 'role', array(
			'label'       => __( 'Author role / context', 'nashua-pal-core' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'placeholder' => 'PAL Mentee · 2024',
		) );
		$this->add_control( 'photo', array(
			'label'   => __( 'Author photo', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => '' ),
		) );
		$this->add_control( 'theme', array(
			'label'   => __( 'Theme', 'nashua-pal-core' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'options' => array(
				'light' => __( 'Light background', 'nashua-pal-core' ),
				'navy'  => __( 'Navy background',  'nashua-pal-core' ),
			),
			'default' => 'light',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$is_navy = 'navy' === $s['theme'];
		$bg  = $is_navy ? '#071F3F' : '#F4F6FA';
		$fg  = $is_navy ? '#fff'    : '#071F3F';
		$sub = $is_navy ? '#C4CCD8' : '#3A4658';
		$photo = ! empty( $s['photo']['url'] ) ? $s['photo']['url'] : '';
		?>
		<section class="pal-quote" style="background:<?php echo esc_attr( $bg ); ?>;padding:64px 24px;">
		<figure style="max-width:780px;margin:0 auto;text-align:center;">
			<div style="font-family:Georgia,serif;font-size:5rem;color:#F4A51C;line-height:1;margin-bottom:-10px;">“</div>
			<blockquote style="margin:0 0 22px;color:<?php echo esc_attr( $fg ); ?>;font-family:Montserrat,sans-serif;font-size:clamp(1.1rem,2vw,1.4rem);line-height:1.5;font-weight:500;font-style:italic;"><?php echo esc_html( $s['quote'] ); ?></blockquote>
			<?php if ( $photo ) : ?>
				<img src="<?php echo esc_url( $photo ); ?>" alt="" style="width:64px;height:64px;border-radius:50%;object-fit:cover;margin:0 auto 12px;display:block;">
			<?php endif; ?>
			<figcaption>
				<span style="display:block;color:<?php echo esc_attr( $fg ); ?>;font-family:Montserrat,sans-serif;font-weight:700;font-size:1rem;"><?php echo esc_html( $s['author'] ); ?></span>
				<?php if ( ! empty( $s['role'] ) ) : ?>
					<span style="display:block;color:<?php echo esc_attr( $sub ); ?>;font-size:0.88rem;margin-top:2px;"><?php echo esc_html( $s['role'] ); ?></span>
				<?php endif; ?>
			</figcaption>
		</figure>
		</section>
		<?php
	}
}
