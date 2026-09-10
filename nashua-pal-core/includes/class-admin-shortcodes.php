<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Renders an admin reference page documenting every [pal_*] shortcode
 * the plugin provides. Lives under Nashua PAL → Shortcodes.
 */
final class NPAL_Admin_Shortcodes {

	const PAGE_SLUG = 'nashua-pal-shortcodes';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ), 11 );
	}

	public function menu() {
		add_submenu_page(
			NPAL_Settings::PAGE_SLUG,
			__( 'Shortcodes', 'nashua-pal-core' ),
			__( 'Shortcodes', 'nashua-pal-core' ),
			'edit_posts',
			self::PAGE_SLUG,
			array( $this, 'render' )
		);
	}

	/**
	 * Single source of truth for shortcode docs. Add new shortcodes here
	 * to have them appear on the reference page automatically.
	 */
	public static function definitions() {
		return array(

			array(
				'group' => 'header',
				'tag'   => 'pal_logo',
				'desc'  => 'Bundled Nashua PAL logo, linked to the home page. Always loads — uses the file shipped with the plugin.',
				'atts'  => array(
					'width' => 'Logo width in px (default 90)',
					'href'  => 'Override the link target (default = site home)',
					'class' => 'Custom CSS class for the link wrapper',
				),
				'examples' => array( '[pal_logo]', '[pal_logo width="120"]' ),
			),
			array(
				'group' => 'header',
				'tag'   => 'pal_search',
				'desc'  => 'Search field for the top toolbar. Submits to the site\'s built-in WP search. Hidden if "Show search" is unchecked in Settings.',
				'atts'  => array(),
				'examples' => array( '[pal_search]' ),
			),
			array(
				'group' => 'header',
				'tag'   => 'pal_language',
				'desc'  => 'Google Translate dropdown for the top toolbar. Hidden if "Show language switcher" is unchecked in Settings.',
				'atts'  => array(),
				'examples' => array( '[pal_language]' ),
			),
			array(
				'group' => 'header',
				'tag'   => 'pal_socials',
				'desc'  => 'Row of social icons (Facebook, Instagram, YouTube, X). Hides any icon whose URL is left blank in Settings → Social Links.',
				'atts'  => array(),
				'examples' => array( '[pal_socials]' ),
			),
			array(
				'group' => 'header',
				'tag'   => 'pal_promo_bar',
				'desc'  => 'Yellow promo bar that sits above the header. Text, button label, button URL, and on/off toggle all live in Settings → Top Promo Bar.',
				'atts'  => array(),
				'examples' => array( '[pal_promo_bar]' ),
			),
			array(
				'group' => 'header',
				'tag'   => 'pal_mobile_menu_button',
				'desc'  => 'Hamburger button for the mobile header. Three white bars that morph into an X when the drawer opens. Pair with [pal_mobile_menu] anywhere on the page (the button toggles the drawer).',
				'atts'  => array(
					'label' => 'Aria-label for screen readers (default "Open menu")',
					'class' => 'Optional extra CSS class on the button',
				),
				'examples' => array( '[pal_mobile_menu_button]' ),
			),
			array(
				'group' => 'header',
				'tag'   => 'pal_mobile_menu',
				'desc'  => 'Mobile menu drawer (slides in from the LEFT when the hamburger is tapped). Auto-loads the WP nav menu assigned to "Nashua PAL Mobile Menu" under Appearance → Menus, and includes Donate/Register buttons, search, language switcher, and social icons. Drop it once near the bottom of your header template (or anywhere — it positions fixed). Falls back to a placeholder menu if no nav menu has been assigned. **Per-item icons:** add a Font Awesome class (e.g. `fas fa-shield-halved`) or a built-in icon slug (e.g. `shield-check`) in the **"Icon"** field on each menu item under Appearance → Menus → expand item.',
				'atts'  => array(
					'show_donate'   => '"yes" (default) / "no" — Donate Today button',
					'show_register' => '"yes" (default) / "no" — Register Now button',
					'show_search'   => '"yes" (default) / "no" — search field row',
					'show_language' => '"yes" (default) / "no" — language switcher',
					'show_socials'  => '"yes" (default) / "no" — social icons row at the bottom',
				),
				'examples' => array(
					'[pal_mobile_menu]',
					'[pal_mobile_menu show_search="no" show_language="no"]',
				),
			),
			array(
				'group' => 'header',
				'tag'   => 'pal_alert',
				'desc'  => 'Site-wide alert popup (closures, weather, urgent events). Off by default — turn on under Settings → Site Alert and design every region (left panel image / structured fields, right panel content, feature icons, buttons, footer). When enabled, the popup is auto-injected on every front-end page (no shortcode placement needed); the shortcode itself only matters if you want to force-render the popup at a specific spot in your template. Visitors can dismiss it; their choice is remembered in their browser per popup-content hash.',
				'atts'  => array(),
				'examples' => array( '[pal_alert]' ),
			),

			array(
				'group' => 'homepage',
				'tag'   => 'pal_stats_bar',
				'desc'  => 'Navy bar with 4 animated impact stats. Numbers, labels, and icons are edited in Settings → Impact Stats.',
				'atts'  => array(),
				'examples' => array( '[pal_stats_bar]' ),
			),
			array(
				'group' => 'homepage',
				'tag'   => 'pal_stat',
				'desc'  => 'A single animated stat (number + label + icon). Useful when you want a one-off stat outside of the standard 4-stat bar.',
				'atts'  => array(
					'number' => 'The big number, e.g. "20+" or "1,000"',
					'label'  => 'Caption below the number',
					'icon'   => 'Font Awesome class, e.g. "fas fa-shield-alt"',
				),
				'examples' => array( '[pal_stat number="500" label="Volunteers" icon="fas fa-heart"]' ),
			),
			array(
				'group' => 'homepage',
				'tag'   => 'pal_program_cards',
				'desc'  => 'Programs from the Programs CPT, rendered as cards or list rows. Each item uses the program\'s featured image, title, short description, icon, and "Learn more" URL.',
				'atts'  => array(
					'count'         => 'Max number of programs to show (default 4)',
					'featured_only' => '"yes" (default) shows only programs ticked "Show on homepage"; "no" shows all programs',
					'view'          => '"grid" (default) renders cards in a grid; "list" renders horizontal rows (image left, body right)',
					'columns'       => 'Grid columns: 1, 2, 3, or 4 (default 4). Ignored when view="list". Auto-collapses to 2 columns on tablets and 1 column on phones.',
				),
				'examples' => array(
					'[pal_program_cards]',
					'[pal_program_cards count="6" featured_only="no"]',
					'[pal_program_cards columns="3"]',
					'[pal_program_cards view="list" count="6"]',
				),
			),
			array(
				'group' => 'homepage',
				'tag'   => 'pal_program',
				'desc'  => 'Single program detail view (full hero image + title + description + locations grid + CTA). For a dedicated program page or to spotlight one specific program anywhere on the site. Pulls from the Programs CPT.',
				'atts'  => array(
					'id'             => 'Program post ID (numeric). Use this OR `slug`.',
					'slug'           => 'Program post slug — e.g. "mentoring". Use this OR `id`.',
					'show_image'     => '"yes" (default) shows the featured-image hero · "no" hides it',
					'show_locations' => '"yes" (default) shows the "Where it\'s offered" grid · "no" hides it',
					'show_cta'       => '"yes" (default) shows the Learn More button · "no" hides it',
				),
				'examples' => array(
					'[pal_program slug="mentoring"]',
					'[pal_program id="123"]',
					'[pal_program slug="sports" show_locations="no"]',
				),
			),
			array(
				'group' => 'homepage',
				'tag'   => 'pal_upcoming_events',
				'desc'  => 'Upcoming events with date pills. Pulls from the Events CPT and any Google Calendars configured under Settings → Google Calendar Feed, merged and sorted by start time. Renders as a horizontal list row (default) or a card grid with the date pill overlaid on the image.',
				'atts'  => array(
					'count'      => 'Max number of events to show (default 3)',
					'sources'    => '"all" (default) merges both sources · "cpt" only the manually-entered events · "gcal" only Google Calendar feeds',
					'view'       => '"list" (default) renders date + title rows; "grid" renders cards with image on top and date pill overlaid in the corner',
					'columns'    => 'Grid columns: 1, 2, 3, or 4 (default 3). Ignored when view="list". Auto-collapses to 2 columns at ≤900px and 1 column at ≤560px.',
					'show_image' => '"yes" adds the event\'s featured image. In list view → 80×80 thumbnail before the date pill. In grid view → image fills the top of the card. (default "no")',
					'image_size' => 'Any registered WP image size (default "medium"). Try "thumbnail", "medium_large", "large", or "full" for bigger / smaller renders.',
				),
				'examples' => array(
					'[pal_upcoming_events]',
					'[pal_upcoming_events count="6" sources="gcal"]',
					'[pal_upcoming_events show_image="yes"]',
					'[pal_upcoming_events view="grid" columns="3" show_image="yes"]',
					'[pal_upcoming_events view="grid" columns="4" count="8"]',
					'[pal_upcoming_events view="grid" columns="2" count="4" show_image="yes" image_size="large"]',
				),
			),
			array(
				'group' => 'homepage',
				'tag'   => 'pal_events',
				'desc'  => 'Full events listing — includes BOTH past and upcoming events. For a dedicated /events page, an "Event Archive" section, or anywhere you want to surface every event regardless of date. Pulls from the Events CPT and any Google Calendars. Different from [pal_upcoming_events] which only shows future events.',
				'atts'  => array(
					'when'          => '"all" (default) shows everything · "upcoming" only future events · "past" only finished events',
					'sources'       => '"all" (default) merges both · "cpt" only manually-entered events · "gcal" only Google Calendar feeds',
					'count'         => 'Max events to render (default 12)',
					'order'         => '"auto" (default — desc for all/past, asc for upcoming) · "asc" oldest first · "desc" newest first',
					'from'          => 'Optional start date filter — "2026-01-01" or "January 2026". Only events on or after this date.',
					'to'            => 'Optional end date filter — "2026-12-31". Only events on or before this date (interpreted as end-of-day for date-only values).',
					'view'          => '"grid" (default) renders cards in a grid · "list" renders horizontal rows',
					'columns'       => 'Grid columns: 1, 2, 3 (default), or 4. Auto-collapses to 2 columns on tablets and 1 on phones.',
					'show_image'    => '"yes" (default) shows the event\'s featured image · "no" hides it',
					'image_size'    => 'Any registered WP image size (default "medium"). Try "large" for bigger thumbnails.',
					'show_date'     => '"yes" (default) shows the yellow date pill · "no" hides it (useful when you want a cleaner card-only look)',
					'show_location' => '"yes" (default) shows the location after the date · "no" hides it',
					'show_excerpt'  => '"yes" shows a 28-word excerpt below the meta · "no" (default) hides it',
					'show_status'   => '"yes" shows an "Upcoming" / "Past" badge above the title · "no" (default) hides it. Past events also auto-render at 78% opacity.',
				),
				'examples' => array(
					'[pal_events]',
					'[pal_events when="past" count="20"]',
					'[pal_events when="upcoming" view="list" show_image="no"]',
					'[pal_events columns="4" count="12" show_status="yes"]',
					'[pal_events from="2026-01-01" to="2026-12-31" show_excerpt="yes"]',
					'[pal_events sources="cpt" view="list" count="50" order="desc"]',
				),
			),
			array(
				'group' => 'homepage',
				'tag'   => 'pal_event_date_pill',
				'desc'  => 'Standalone date pill (yellow box with month + day). Useful when displaying a single specific event somewhere outside the upcoming-events list.',
				'atts'  => array(
					'date' => 'Any PHP-parseable date — "2026-05-18", "May 18 2026", etc.',
				),
				'examples' => array( '[pal_event_date_pill date="2026-05-18"]' ),
			),
			array(
				'group' => 'homepage',
				'tag'   => 'pal_get_involved',
				'desc'  => 'Get Involved list (Volunteer / Coach / Partner / Tutor) with icons and short descriptions.',
				'atts'  => array(),
				'examples' => array( '[pal_get_involved]' ),
			),
			array(
				'group' => 'homepage',
				'tag'   => 'pal_latest_news',
				'desc'  => 'Most recent published Posts (image + title + date), rendered as cards in a grid or as horizontal list rows. Edit posts under Posts → Add New.',
				'atts'  => array(
					'count'   => 'Max number of posts to show (default 3)',
					'view'    => '"grid" (default) renders cards in a grid; "list" renders horizontal rows (image left, title + date right)',
					'columns' => 'Grid columns: 1, 2, 3, or 4 (default 3). Ignored when view="list". Auto-collapses to 2 columns at ≤900px and 1 column at ≤560px.',
				),
				'examples' => array(
					'[pal_latest_news]',
					'[pal_latest_news count="6"]',
					'[pal_latest_news columns="4" count="8"]',
					'[pal_latest_news view="list" count="5"]',
				),
			),
			array(
				'group' => 'homepage',
				'tag'   => 'pal_partner_carousel',
				'desc'  => 'Logo row of the Partners CPT, sorted by Order. Renders text placeholders for partners whose Featured Image isn\'t uploaded yet.',
				'atts'  => array(
					'title' => 'Heading text above the logos (default "Proudly Partnered With")',
				),
				'examples' => array( '[pal_partner_carousel]', '[pal_partner_carousel title="Our Sponsors"]' ),
			),

			array(
				'group' => 'cta',
				'tag'   => 'pal_donate_button',
				'desc'  => 'Yellow donate button. URL comes from Settings → Donate URL — change it once and every donate button site-wide updates.',
				'atts'  => array(
					'label' => 'Button text (default "Donate Today")',
					'class' => 'CSS class (default "pal-btn pal-btn-yellow")',
				),
				'examples' => array( '[pal_donate_button]', '[pal_donate_button label="Give Now"]', '[pal_donate_button label="Donate Now" class="pal-btn pal-btn-outline"]' ),
			),
			array(
				'group' => 'contact',
				'tag'   => 'pal_phone',
				'desc'  => 'Linked phone number with icon. Source: Settings → Phone.',
				'atts'  => array(),
				'examples' => array( '[pal_phone]' ),
			),
			array(
				'group' => 'contact',
				'tag'   => 'pal_email',
				'desc'  => 'Linked email with icon. Source: Settings → Email.',
				'atts'  => array(),
				'examples' => array( '[pal_email]' ),
			),
			array(
				'group' => 'contact',
				'tag'   => 'pal_address',
				'desc'  => 'Mailing address with map-pin icon. Source: Settings → Mailing address.',
				'atts'  => array(),
				'examples' => array( '[pal_address]' ),
			),
			array(
				'group' => 'contact',
				'tag'   => 'pal_newsletter',
				'desc'  => 'Newsletter signup form. Heading, button label, and form action URL come from Settings → Newsletter (point this at Mailchimp / Constant Contact / etc.).',
				'atts'  => array(),
				'examples' => array( '[pal_newsletter]' ),
			),

			array(
				'group' => 'utility',
				'tag'   => 'pal_underline',
				'desc'  => 'Wraps a word or phrase in the same animated yellow underline used on the homepage hero (the "COMMUNITY" effect). Drop it inside any heading, paragraph, or button label to draw the eye to one word. Both enclosing and self-closing forms work.',
				'atts'  => array(
					'text'  => 'Word or phrase to underline (alternative to wrapping content)',
					'class' => 'Optional extra CSS class to stack on top of pal-hero-underline',
				),
				'examples' => array(
					'[pal_underline]COMMUNITY[/pal_underline]',
					'[pal_underline text="STRONGER KIDS"]',
				),
			),
		);
	}

	public function render() {
		if ( ! current_user_can( 'edit_posts' ) ) { return; }
		$defs   = self::definitions();
		$groups = array(
			'header'   => __( 'Header & Site-wide Tools', 'nashua-pal-core' ),
			'homepage' => __( 'Homepage Sections', 'nashua-pal-core' ),
			'cta'      => __( 'CTA Buttons', 'nashua-pal-core' ),
			'contact'  => __( 'Contact Info & Forms', 'nashua-pal-core' ),
			'utility'  => __( 'Inline Utilities', 'nashua-pal-core' ),
		);
		?>
		<div class="wrap pal-shortcodes-wrap">
			<h1><?php esc_html_e( 'Nashua PAL — Shortcodes Reference', 'nashua-pal-core' ); ?></h1>
			<p class="description" style="font-size:14px;max-width:780px;">
				<?php esc_html_e( 'Drop these shortcodes into any page, post, Elementor widget, or sidebar to render dynamic content. Most pull live data from your settings or CPTs, so updating Settings or adding a new Event / Program / Partner / Post automatically updates wherever the shortcode appears.', 'nashua-pal-core' ); ?>
			</p>

			<style>
				.pal-shortcodes-wrap .pal-sc-toc { display:flex; flex-wrap:wrap; gap:8px; margin:18px 0 28px; }
				.pal-shortcodes-wrap .pal-sc-toc a { background:#fff; border:1px solid #c3c4c7; padding:6px 12px; border-radius:4px; text-decoration:none; color:#1d2327; font-weight:600; font-size:13px; }
				.pal-shortcodes-wrap .pal-sc-toc a:hover { background:#F4A51C; border-color:#F4A51C; color:#071F3F; }
				.pal-shortcodes-wrap .pal-sc-group { background:#fff; border:1px solid #c3c4c7; border-radius:6px; margin:0 0 22px; overflow:hidden; }
				.pal-shortcodes-wrap .pal-sc-group h2 { background:#071F3F; color:#fff; padding:14px 18px; margin:0; font-size:15px; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; }
				.pal-shortcodes-wrap .pal-sc-row { padding:18px 18px; border-bottom:1px solid #e5e7eb; display:grid; grid-template-columns: 280px 1fr; gap:18px; }
				.pal-shortcodes-wrap .pal-sc-row:last-child { border-bottom:0; }
				.pal-shortcodes-wrap .pal-sc-tag { display:inline-flex; align-items:center; gap:6px; font-family:Menlo,Consolas,monospace; font-size:13px; font-weight:600; color:#071F3F; background:#FEF6E7; border:1px solid #F4A51C; padding:4px 8px; border-radius:4px; word-break:break-all; }
				.pal-shortcodes-wrap .pal-sc-copy { background:transparent; border:0; cursor:pointer; color:#646970; font-size:12px; padding:2px 4px; }
				.pal-shortcodes-wrap .pal-sc-copy:hover { color:#F4A51C; }
				.pal-shortcodes-wrap .pal-sc-copy.is-copied { color:#16a34a; }
				.pal-shortcodes-wrap .pal-sc-desc { color:#1d2327; font-size:14px; line-height:1.5; margin:0 0 10px; }
				.pal-shortcodes-wrap .pal-sc-attrs { margin:8px 0; }
				.pal-shortcodes-wrap .pal-sc-attrs table { width:100%; max-width:640px; border-collapse:collapse; font-size:13px; }
				.pal-shortcodes-wrap .pal-sc-attrs th { text-align:left; background:#F7F9FC; padding:6px 10px; font-weight:600; color:#646970; border-bottom:1px solid #e5e7eb; }
				.pal-shortcodes-wrap .pal-sc-attrs td { padding:6px 10px; border-bottom:1px solid #f0f0f1; vertical-align:top; }
				.pal-shortcodes-wrap .pal-sc-attrs td code { font-family:Menlo,Consolas,monospace; font-size:12px; background:#f7f9fc; padding:1px 5px; border-radius:3px; }
				.pal-shortcodes-wrap .pal-sc-examples { margin-top:8px; }
				.pal-shortcodes-wrap .pal-sc-examples code { display:inline-block; font-family:Menlo,Consolas,monospace; font-size:12px; background:#0e1c2e; color:#F4A51C; padding:6px 10px; border-radius:4px; margin:0 4px 4px 0; }
				.pal-shortcodes-wrap .pal-sc-no-attrs { color:#646970; font-size:12px; font-style:italic; }
				@media (max-width: 780px) {
					.pal-shortcodes-wrap .pal-sc-row { grid-template-columns: 1fr; }
				}
			</style>

			<div class="pal-sc-toc">
				<?php foreach ( $groups as $key => $label ) : ?>
					<a href="#group-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</div>

			<?php foreach ( $groups as $group_key => $group_label ) : ?>
				<section id="group-<?php echo esc_attr( $group_key ); ?>" class="pal-sc-group">
					<h2><?php echo esc_html( $group_label ); ?></h2>
					<?php foreach ( $defs as $def ) : if ( $def['group'] !== $group_key ) continue; ?>
						<div class="pal-sc-row">
							<div>
								<span class="pal-sc-tag" data-pal-copy="[<?php echo esc_attr( $def['tag'] ); ?>]">
									[<?php echo esc_html( $def['tag'] ); ?>]
									<button type="button" class="pal-sc-copy" aria-label="<?php esc_attr_e( 'Copy shortcode', 'nashua-pal-core' ); ?>" title="<?php esc_attr_e( 'Copy', 'nashua-pal-core' ); ?>">📋</button>
								</span>
							</div>
							<div>
								<p class="pal-sc-desc"><?php echo esc_html( $def['desc'] ); ?></p>

								<?php if ( ! empty( $def['atts'] ) ) : ?>
									<div class="pal-sc-attrs">
										<table>
											<thead>
												<tr><th style="width:160px"><?php esc_html_e( 'Attribute', 'nashua-pal-core' ); ?></th><th><?php esc_html_e( 'What it does', 'nashua-pal-core' ); ?></th></tr>
											</thead>
											<tbody>
												<?php foreach ( $def['atts'] as $attr_name => $attr_desc ) : ?>
													<tr><td><code><?php echo esc_html( $attr_name ); ?></code></td><td><?php echo esc_html( $attr_desc ); ?></td></tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								<?php else : ?>
									<p class="pal-sc-no-attrs"><?php esc_html_e( 'No attributes — drop it in as-is.', 'nashua-pal-core' ); ?></p>
								<?php endif; ?>

								<?php if ( ! empty( $def['examples'] ) ) : ?>
									<div class="pal-sc-examples">
										<strong style="font-size:12px;color:#646970;display:block;margin-bottom:6px;"><?php esc_html_e( 'Examples', 'nashua-pal-core' ); ?></strong>
										<?php foreach ( $def['examples'] as $example ) : ?>
											<code><?php echo esc_html( $example ); ?></code>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</section>
			<?php endforeach; ?>

			<p style="margin-top:24px;color:#646970;font-size:13px;">
				<?php esc_html_e( 'Tip: in Elementor, the cleanest place to drop a shortcode is the "Shortcode" widget. The "HTML" widget also runs shortcodes since v1.0.1.', 'nashua-pal-core' ); ?>
			</p>

			<script>
			(function(){
				document.querySelectorAll('.pal-sc-copy').forEach(function(btn){
					btn.addEventListener('click', function(){
						var wrapper = btn.closest('[data-pal-copy]');
						if (!wrapper) return;
						var code = wrapper.getAttribute('data-pal-copy');
						(navigator.clipboard ? navigator.clipboard.writeText(code) : Promise.reject())
							.catch(function(){
								var ta = document.createElement('textarea');
								ta.value = code; document.body.appendChild(ta);
								ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
							})
							.finally(function(){
								btn.classList.add('is-copied');
								var orig = btn.textContent;
								btn.textContent = '✓';
								setTimeout(function(){ btn.classList.remove('is-copied'); btn.textContent = orig; }, 1200);
							});
					});
				});
			})();
			</script>
		</div>
		<?php
	}
}
