=== Nashua PAL Core ===
Contributors: caotech
Tags: custom post types, nashua pal, elementor
Requires at least: 6.4
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.55.39
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Custom post types, settings, and shortcodes that power the Nashua PAL website.

== Description ==

Nashua PAL Core registers Events, Programs, and Partners as custom post types so staff can edit homepage content from clean WP forms instead of Elementor. It also adds a Settings screen for promo bars, alerts, contact info, and impact stats, plus shortcodes the Elementor templates use to render dynamic sections.

Pair this plugin with the five Elementor templates shipped alongside it.

== Changelog ==

= 1.15.17 =
* Add Elementor controls for hero height, stats card width, padding, overlap, icon size, number/label font sizes — all responsive.

= 1.15.16 =
* UI: Shorter hero (520px), gradient overlay (dark left, transparent right), horizontal stats card layout matching mockup.

= 1.15.15 =
* UI: Scale down stats card for better proportions — smaller icons, numbers, labels, and overall card size.

= 1.15.7 =
* Fix: Past Champions cards now use `large` thumbnail size instead of `medium` to prevent pixelation.

= 1.15.6 =
* Fix: "View Full Gallery" button now uses filled navy style matching the site's button design (was thin outline).
* Fix: Sponsor logos significantly larger — Presenting 140px, Gold 110px, Silver 90px, Community 80px (were 80/60/48/40px).

= 1.15.5 =
* Fix: Archive page title and single winner name now explicitly white — theme h1 styles were overriding inherited color on navy backgrounds.

= 1.15.4 =
* Fix: "View Full Gallery" button now always visible (was hidden when photos had no URLs).
* Fix: Winner card images now display from top (`background-position: top center`) so faces aren't cut off.
* Fix: Auto-flush rewrite rules on version change so archive (`/champions-winners/`) and single winner pages work after plugin update without manual permalink save.
* Enhancement: Gallery lightbox works with empty state — shows placeholder message until photos are uploaded.

= 1.15.3 =
* Fix: Stats bar now uses white background with horizontal icon+text layout matching the mockup (dark navy numbers, gray labels, gold icons).
* New: Gallery lightbox — "View Full Gallery" button opens a fullscreen popup with all photos, prev/next nav, thumbnail strip, and keyboard support.
* Enhancement: Clicking individual gallery photos opens the lightbox at that photo.
* Enhancement: Gallery repeater supports unlimited photos — first 6 display as featured in the grid, all photos available in the lightbox.
* Fix: Stats labels updated to match mockup (Guests, Awards Presented, Sponsors).

= 1.15.2 =
* New: `pal_noc_gallery` Elementor widget — "Gala Highlights" section with text-left + photo grid-right layout matching mockup.
* New: `pal_noc_cta` Elementor widget — centered CTA banner with PAL watermark background, configurable buttons.
* Enhancement: Champions Winners shortcode now supports `featured_eyebrow`, `recipients_eyebrow`, `view_all_label`, `view_all_url` attributes.
* Fix: About section background changed to white with dark text to match mockup.
* Fix: Hero title and button text now display in uppercase.
* Fix: Rebuilt page template (v6) using all dedicated NOC widgets.

= 1.15.1 =
* Fix: Rebuilt Night of Champions page template using dedicated widgets (NOC Hero, NOC About, NOC Past Champions) for exact mockup match.
* New: `pal_noc_hero` Elementor widget — full-bleed hero with background image, left-aligned text, configurable buttons, and floating gold-bordered stats bar.
* New: `pal_noc_about` Elementor widget — two-column layout with image-left/text-right (reversible) for the "Honoring Excellence" section.
* New: `pal_noc_past_champions` Elementor widget — auto-detects years from DB, year tab pills, winner cards per year, archive button.
* Enhancement: All Night of Champions sections fully editable via Elementor panel controls.

= 1.15.0 =
* New: Night of Champions system — `champions_winner` CPT with meta boxes for award details, category, year, featured toggle, video URL, and display order.
* New: `champions_sponsor` CPT for event sponsors with tier levels (Presenting, Gold, Silver, Community) and year filtering.
* New: `[champions_winners]` shortcode with featured honorees section, award recipients grid, category filter tabs, and responsive card layout.
* New: `[champions_sponsors]` shortcode renders sponsor logos grouped by tier level.
* New: Single winner template with hero, full story, video embed, related winners section.
* New: Archive page at `/champions-winners/` with year/category dropdowns and name search, all client-side filtered.
* New: PAL Champions Winners Grid and PAL Champions Sponsors Elementor widgets with full control panels.
* Admin list columns for both CPTs: sortable by year, category, featured status, and display order.

= 1.14.0 =
* New: Constant Contact API integration — auto-imports sent email campaigns as Newsletter CPT posts.
* Settings → Newsletter tab now includes CC API fields: API Key, Secret, Access Token, Refresh Token, and an enable toggle.
* OAuth2 token auto-refresh: when the access token expires, the plugin refreshes it automatically using the stored refresh token.
* Sync status indicator on the Settings page shows connection health and last sync time.
* 12-hour transient cache prevents excessive API calls; cache clears on credential change.
* Auto-imported newsletters get `_pal_newsletter_cc_campaign_id` meta to prevent duplicates; manually added newsletters are never touched.

= 1.13.0 =
* New: Newsletter CPT (`pal_newsletter`) — staff can add newsletter entries with title, publication date, external URL, and optional thumbnail/excerpt.
* Enhanced: `[pal_latest_news]` shortcode now merges blog posts and newsletters into a unified date-sorted feed with "News" / "Newsletter" badges on each card.
* New: `show_filter="true"` attribute on `[pal_latest_news]` renders filter tabs for All / News / Newsletter.
* New: `source` attribute on `[pal_latest_news]` to restrict to `posts` or `newsletters` only.
* Newsletter cards open the external Constant Contact URL in a new tab.
* PAL Latest News Elementor widget updated with "Content source" and "Show filter tabs" controls.

= 1.12.19 =
* Added newsletter CTA to `[pal_latest_news]` shortcode — optional `cta_label`, `cta_url`, `cta_text` attributes render a "Sign Up for PAL Updates" button below news cards.
* PAL Latest News Elementor widget now has a "Newsletter CTA" section with controls for button label, URL, and description.
* Homepage template updated with Constant Contact newsletter sign-up link.

= 1.12.18 =
* Fixed "Become a Partner" CTA button on `/partners/` — replaced non-existent `pal-btn--yellow` class with `pal-btn--primary` so the button matches the site-wide design system.

= 1.8.96 =
* **`[pal_program_cards]` featured filter is now opt-out instead of opt-in.** The check changed from `if ( 'yes' === featured_only )` to `if ( 'no' !== featured_only )`. Anything except an explicit `featured_only="no"` (yes, empty string, missing key) keeps the filter ON. This protects legacy widget instances that stored an empty `featured_only` key from accidentally reading as "show all programs."
* The archive's bundled template still passes `featured_only="no"` explicitly so it shows everything; homepage defaults to featured-only with no settings needed.

= 1.8.95 =
* **★ Featured column** added to Programs admin list (Programs → All Programs) with one-click toggle: click the star to feature/unfeature a program from the homepage without opening it. Outlined ☆ = not featured, filled gold ★ = featured. Column is sortable so you can group all featured at the top.
* Bundled homepage template (`nashua-pal-homepage-v2.json`) now explicitly sets `count="4"` + `featured_only="yes"` on the PAL Programs Grid widget (matching the intended 4-card row), so re-import gives a clean baseline. The control was added in 1.8.94.
* Together with 1.8.94: re-import both bundled templates (homepage + Programs Archive) after upgrading to push the new widget settings into existing posts; or open the live pages in Elementor and set the new "Show" dropdown manually.

= 1.8.94 =
* **Fix: "Show on homepage" featured toggle on Programs wasn't taking effect because the Elementor `PAL Programs Grid` widget never passed `featured_only` to the shortcode.** Default in the shortcode was `"yes"` — so both the homepage AND the Programs archive were silently filtering to featured. The archive should show all programs regardless of featured status.
* **Widget control added: "Show" dropdown** with two options — "Featured programs only (homepage)" and "All programs (archive)." Default stays "featured only" so the homepage keeps its intended behavior. Bundled Programs Archive template (`nashua-pal-programs-archive-themebuilder.json`) updated to `featured_only="no"` so it shows every published Program.
* Existing widget instances default to "featured only" on first render after upgrade, matching prior implicit behavior — so the homepage doesn't need a touch. If your archive page is custom (not the bundled template) and you want all programs, open it in Elementor → edit the PAL Programs Grid widget → set Show = "All programs."

= 1.8.93 =
* **Fix: `count="-1"` on `[pal_upcoming_events]` was silently dropping the latest upcoming event.** The shortcode used `array_slice($combined, 0, $count)` directly; PHP's `array_slice` with a negative length returns "all but the last N" elements — so passing -1 (the bundled Events Archive template's default for "show everything") chopped off the farthest-out event. Now: `count` of 0 or less means "no cap," matching the WP_Query convention every other PAL shortcode uses.
* Same fix applied to `[pal_events]` (`when="past"` / `when="upcoming"` / `when="all"`). Previously `max(1, count)` collapsed -1 to 1, showing only a single past event. Now negative/zero counts → no cap.

= 1.8.92 =
* **`[pal_event_sponsors]` auto-switches to a marquee slider when sponsor count > 6.** Reuses the existing `.pal-partners__viewport / __track / __item` chrome from `[pal_partner_carousel]` so logos infinite-scroll with the same animation speed, hover-pause, edge-fade mask, and responsive `--pal-visible` breakpoints (6 desktop → 4 ≤1200px → 3 ≤768px → 2 ≤480px; respects `prefers-reduced-motion`). At ≤ 6 sponsors the previous static grid still renders.
* New shortcode attrs: `slider_threshold` (default 6 — count above which the slider activates), `slider_visible` (default 6 — desktop slot count), `slider_speed` (default 30 — seconds per loop). Past behavior is the default.
* Click-through links from `_pal_partner_url` are preserved in both layouts.

= 1.8.91 =
* **Partner / Sponsor admin: new "Partner / Sponsor Details" meta box** with a single "Website URL" field that saves to `_pal_partner_url` meta. When set, the partner's logo on event sponsor strips AND in the homepage `[pal_partner_carousel]` now wraps in `<a href="…" target="_blank">` — clicking the logo opens the sponsor's site in a new tab. Leaving the URL blank keeps the logo non-clickable, same as before.
* The meta box also surfaces inline copy reminding staff that the Featured Image box (right rail) IS the sponsor logo — a single transparent PNG/SVG is reused everywhere the partner appears (event sponsor strip, homepage carousel, Partners archive).
* `[pal_partner_carousel]` now also reads `_pal_partner_url` and wraps each logo in `<a>` when set; logos without a URL render as plain `<img>` (existing behavior preserved). Hover-pointer style picks up the existing `.pal-partners__item img` rules; no extra CSS shipped.
* `[pal_event_sponsors]` already wrapped logos in `<a>` since 1.8.54 — this release just gives admins the UI to actually populate `_pal_partner_url`. Existing events with sponsor IDs continue to work and pick up links automatically once each Partner gets a URL.

= 1.8.90 =
* **Event meta box — Sponsors are now a checkbox-card grid of Partner posts** with logos and names, instead of typing comma-separated post IDs. Each card has a thumbnail (from the Partner's featured image) + the partner name, highlights yellow when selected. Storage format unchanged (CSV of IDs) so the rendering layer and existing data both keep working.
* **Awardees repeater UI** — replaced the pipe-delimited textarea with a JS-driven repeater. Each awardee is its own row with separate fields (name, school/grade, bio textarea, optional profile URL) and a 96px photo box that opens the Media Library when clicked. "+ Add Awardee" button at the bottom inserts a new row; each row has an × remove button. Data still serializes to the same pipe-delimited format on save so the front-end renderer doesn't need changes, AND existing pipe-delimited data parses back into rows on edit.
* Photo picker uses `wp.media()` (the standard WP Media Library modal) — enqueued via `wp_enqueue_media()` on event-post edit screens only.
* Added a yellow notice above the Embed HTML field clarifying that it only renders on Embedded fundraiser + Night of Champions variants. Community variant ignores it. Closes the support thread "embed is not showing up" on community-variant events.
* Empty rows are skipped on save so accidentally clicking Add Awardee and not filling anything in doesn't pollute the saved data with empty `|||` lines.

= 1.8.89 =
* **`[pal_event_embed]` upgraded to a "branded surface" so visitors can't tell it's a third-party iframe.** New wrapper structure: full-width subtle-gradient band, white rounded card with soft shadow, small uppercase eyebrow ("Secure donation form" with a tiny yellow lock icon, customizable via `eyebrow=""`), and an animated loading skeleton that fades out the moment the iframe is injected into the placeholder.
* Works for both raw `<iframe src=…>` paste-ins AND JS-injected embeds like BetterUnite's `<div id='bu-checkout'></div><script src='embedjs?…'></script>` pattern. The new JS uses a MutationObserver on the embed placeholder to catch the script-injected iframe; falls back to a 12s timeout so the skeleton never sticks around forever.
* Markup: `.pal-embed-frame > .pal-embed-frame__inner > .pal-embed-frame__card > .pal-embed-frame__skeleton + .pal-embed-frame__embed`. The `is-loaded` class on the outer section is what triggers the cross-fade. Iframe inside the embed div is forced to `width:100%; border:0; background:transparent` so it inherits the card chrome.
* Sanitizer in `class-event-meta.php` (line 143-147) already allows `iframe + script + div` so existing BetterUnite paste-ins keep working — no migration needed. Re-saving an event post is enough to pick up the new rendering on the next page load.
* **Child theme 1.1.13** ships a slug-mismatch fix that was silently breaking the existing embed-wider override since 1.1.0 — the helper plugin uses variant slug `embedded`, but the child theme's `pal-child-embedded-widen` `<style>` block was scoped to a `--betterunite` body class that the plugin never emits, so the 1280px override never fired. Renamed the slug across all 4 references (`body_class` filter + the `if/style id/two body selectors` block) so the widening now actually applies on `_pal_event_template=embedded` posts.

= 1.8.88 =
* **Promo bar dismiss × button removed.** The yellow bar above the header is now always visible while `promo_enabled` is on in Settings — no per-visitor dismiss, no localStorage hash key, no "dismiss → reappear when copy changes" behavior. If admins want it hidden, they uncheck "Display the yellow bar above the header" in Settings → Promo Bar (which has been the kill-switch since 1.8.x; nothing changes about how that toggle works). Mobile padding-right reserve for the close button is also dropped since the slot no longer exists, giving the centered text more horizontal room on phones.
* Touches three files: `class-shortcodes.php` (removed the `<button>` markup + dismiss aria-label), `assets/js/nashua-pal.js` (removed `initPromoDismiss()` + its call), `assets/css/nashua-pal.css` (removed the `.pal-promo__close` desktop + mobile rules and the `padding-right: 36px` mobile reserve).

= 1.8.87 =
* **Footer middle columns (Quick Links / Get Involved / Stay Connected) converted from raw HTML widgets to native Elementor widgets.** Click any of the column headings in the footer template editor and you get a friendly editor instead of a textarea of hand-written HTML. Specifically: each column's H4 title is now an Elementor **Heading** widget (Montserrat / 800 / uppercase / 2.5px tracking / brand yellow); Quick Links and Get Involved use Elementor's **Nav Menu** widget pointed at two new menu locations; Stay Connected uses Heading + **Text Editor** + a shortcode widget for `[pal_newsletter]`.
* **Two new menu locations** registered: "Nashua PAL Footer — Quick Links" and "Nashua PAL Footer — Get Involved". Admins manage menu items via the standard WP Admin → Appearance → Menus screen — same UX as the existing Primary and Mobile menus. No more editing inline HTML to add/rename a link.
* **One-shot seed** on first admin page load: if either footer menu location isn't yet assigned, the plugin creates a starter menu with the previous hardcoded items (About PAL / Programs / Events / Impact / News / Contact and Volunteer / Mentor / Coach / Partner / Community Partners / Donate Now) so the footer doesn't render empty out of the box. Idempotent — protected by the `npal_footer_menus_seeded` option flag; runs exactly once. If an admin has hand-built a menu and assigned it to the location already, that menu wins and the seeder leaves it alone.
* Re-import the footer template via Nashua PAL → Import Templates after upload so the new widget structure replaces the old HTML widgets in #4415.

= 1.8.86 =
* **Footer Bottom Links are now editable from Settings.** Added 4 label/URL pairs in Nashua PAL → Settings → General → "Footer Bottom Links" section: each slot takes a label (e.g. "Privacy", "Terms") and a URL (absolute or `/relative-path/`). Defaults: Privacy / Terms / Policies / Contact pointing at `/privacy/` `/terms/` `/policies/` `/contact/`.
* `[pal_footer_legal]` updated to read the 4 link slots from settings — was previously hardcoded. A slot whose label OR URL is blank is omitted from the rendered list, so admins can drop a column without needing a separate toggle.
* URL fields run through `esc_url_raw()` on save (same path as the existing Donate / Register URL fields, which means relative paths like `/privacy/` work alongside full https:// links).

= 1.8.85 =
* **Hotfix: `[pal_footer_legal]` rendered empty in editor preview.** Symptom: Elementor template editor showed the legal-bar widget with link bullets but no link text, no copyright line, no "Powered by Cao-Tech LLC". Root cause: `NPAL_Plugin::get_setting()` reads `get_option(NPAL_OPTION_KEY)` directly and returns the per-key default ONLY if the key is missing from the saved option. The new `footer_copyright` / `footer_legal_sub` keys added in 1.8.83 are in `NPAL_Settings::defaults()`, but the saved option on the live site doesn't have them yet — it predates the field being added — so `get_setting()` returned empty string and the shortcode rendered empty `<div>`s. Fixed by merging `NPAL_Settings::defaults()` into the saved option inside `get_setting()` so any default defined in the settings class resolves correctly even before the user has clicked Save on the new field.
* Child theme 1.1.9 ships matching CSS-scope fix — `.pal-footer-legal` selectors no longer require a `footer.elementor-location-footer` ancestor, so styles apply inside Elementor's template-edit preview iframe (which doesn't render the `<footer>` wrapper) AND on the real front-end footer. Same visual output in both places.

= 1.8.84 =
* **Hotfix: "Invalid page template" error on Re-import.** Symptom: clicking Re-import next to any template returned `0 imported (0 updated), 1 errors. nashua-pal-footer-v2.json: Invalid page template.` Root cause: WordPress 6.9+ now validates the `_wp_page_template` post meta on every `wp_update_post()` call — even when `page_template` is NOT passed in `$postarr`. For `elementor_library` posts the meta value `elementor_header_footer` is not a registered page template (Hello Elementor only registers it for the `page` post type), so the validation throws `invalid_page_template` and the update aborts before our `_elementor_data` write ever runs. Fixed in `import_one()` by clearing the meta with `delete_post_meta()` immediately before `wp_update_post()` and re-setting it via `update_post_meta()` after the update completes (which does NOT trigger page-template validation). Includes a restore-on-failure branch so a failed update doesn't leave the post with no page template at all.

= 1.8.83 =
* **Footer legal bar moved from raw HTML widget to settings-driven shortcode.** Staff can now edit the copyright + sub-line text in WP Admin → Nashua PAL → Settings → General → "Footer Legal Line" instead of opening the footer template and editing inline HTML in an Elementor HTML widget. New `[pal_footer_legal]` shortcode renders the entire bottom-of-footer strip (copyright text + sub-line + Privacy/Terms/Policies/Contact link list).
* Two new settings fields: `footer_copyright` (defaults to "© {year} Nashua Police Athletic League. All rights reserved.") and `footer_legal_sub` (defaults to "501(c)(3) nonprofit · Powered by <a href=…>Cao-Tech LLC</a>"). Both accept inline HTML through `wp_kses_post`, so editors can wrap any word in a link without leaving the field.
* `{year}` token is replaced server-side at render time — no embedded `<script>` block in the footer markup anymore.
* Footer JSON updated: widget `w000010` (the legal-bar widget) switched from `html` widgetType to `shortcode` widgetType holding `[pal_footer_legal]`. Re-import the footer template via Nashua PAL → Import Templates to apply.
* Child theme 1.1.8 ships matching `.pal-footer-legal` styles (border-top hairline, flex layout, hover colors) so the visual output is identical to the old HTML widget.

= 1.8.82 =
* Footer JSON edits: column widths rebalanced from 30/18/22/30 → 26/20/22/32 (col 1 narrower so logo+address doesn't dominate, col 4 wider so newsletter form has room), section gap "no" → "extended" so "QUICK LINKS" / "GET INVOLVED" stop visually butting against each other, legal-line text "EIN 00-0000000" replaced with a dotted-underline "Powered by Cao-Tech LLC" link. Re-import the footer template via Nashua PAL → Import Templates to apply.
* Footer JSON also synced to nashua-pal-elementor-v2-production-templates/ so a fresh-site install picks up the new copy.

= 1.8.81 =
* **Deeper diagnostic for the Theme Builder Single routing bug.** Even after 1.8.80's successful cache rebuild (no errors), the Single Program template still doesn't fire. The CSS regenerates, the `elementor-page-{id}` body class still adds, but `<div data-elementor-type="single">` never appears. The cache API succeeded — yet the matcher still returns nothing OR template_include skips the swap for a different reason. Diagnostic dump now includes three new fields to nail down which: (a) raw values of `elementor_pro_theme_builder_conditions_cache` and 2 sibling option keys so we can see exactly what got written by `Conditions_Cache::save()`, (b) full list of `template_include` filter callbacks at all priorities — answers whether Elementor Pro's filter is even hooked and at what priority, vs Hello Elementor's, vs anything else fighting for the slot, (c) `body_class` filter callbacks for comparison since body_class IS adding `elementor-page-4433` so its hook chain is working.
* Same "📊 Download Diagnostic JSON" button — no UI changes. Re-running now produces a much richer file.

= 1.8.80 =
* **Hotfix to 1.8.79:** Rebuild Conditions Cache crashed with `TypeError: Conditions_Cache::add(): Argument #1 ($document) must be of type Theme_Document, int given`. Elementor Pro 4.x changed the cache API — `add()` and `remove()` now take a `Theme_Document` instance rather than a raw post ID (probably so each handler can read the doc's location off the doctype class instead of looking it up). Fixed by resolving each post ID through `Plugin::$instance->documents->get($id)` and skipping non-Theme_Documents (page-type Saved Templates, Sections, Containers don't qualify). Defensive `instanceof Theme_Document` check before `add()` to avoid silent skips becoming silent crashes on future Elementor Pro versions.

= 1.8.79 =
* **THE fix for Theme Builder Singles not firing on CPT URLs.** The diagnostic dump from 1.8.78 nailed the root cause: `Conditions_Manager::get_documents_for_location('single')` returned an EMPTY array for a probed `pal_program` URL, even though the published templates had correct `_elementor_template_type='single'`, correct `_elementor_conditions` ('include/pal_event' / 'include/pal_partner' / 'include/singular/pal_program/1002'), all doctypes registered, all locations registered, and matching content. Smoking-gun proof that Elementor Pro's **Conditions_Cache wasn't being rebuilt** — our Reset only wrote `_elementor_conditions` post meta and deleted the option-level cache keys, but never called the actual `Conditions_Cache::add($post_id, $conditions)` API. Without that registration step, the cache rebuild on next page view produces an empty index (the matcher walks an empty cache and finds nothing), so `template_include` never swaps the template — explaining the asymmetry where Elementor's CSS hooks fire (they use a looser check at the per-post level) but `template_include` does not (it requires the cache).
* New **"🔥 Rebuild Conditions Cache"** button on Nashua PAL → Import Templates. Walks every published library post with conditions, calls `Conditions_Cache->remove()` then `add($id, $conditions)` (same path Theme Builder's "Save & Close" hits), then `save()`. Also `wp_cache_delete()`s each known cache option key on both the `options` and `site-options` cache groups — covers WP Engine memcached pass-through cases. **Run this once after upload and Single Program / Single Event templates should immediately route correctly.**
* New **"🗑 Empty Library Trash"** button. Permanently deletes all trashed elementor_library posts. Useful after Replace-mode imports have piled up trashed copies that still hold `_elementor_conditions` meta (the live site has 28 such ghosts).
* Both new actions emit success notices on redirect (re-registered count, skipped count, trash deleted count).

= 1.8.78 =
* **Diagnostic Dump button on Import Templates page.** Investigating why Theme Builder Single templates on CPTs (pal_event, pal_program, pal_partner) refuse to render even though Elementor enqueues their CSS, adds `elementor-page-{id}` to the body class, AND the user has set explicit display conditions in the Theme Builder UI. Symptom: header (4417) and footer (4415) render correctly with their `data-elementor-type`/`elementor-location-*` wrappers, but `single` doctype templates produce no `<div data-elementor-type="single">` wrapper between them — WP falls back to the child theme's `single-pal_program.php`. The disconnect between "conditions match for CSS/body class" and "conditions don't match for template_include" pointed to a deeper state issue we couldn't diagnose by trial-and-error.
* New "📊 Download Diagnostic JSON" button streams a single JSON file containing: Elementor + Elementor Pro versions, WP/PHP/object-cache backend, every registered Elementor document type (so we can see whether `single` is actually a live doctype in this Elementor Pro release), Theme Builder location registry, every CPT's `public`/`publicly_queryable`/`show_in_rest` flags, every `elementor_library` post (any status) with its `_elementor_template_type` + `_elementor_conditions` + `_elementor_data` byte size + `doctype_registered` flag + bundled-source-file marker, full active-plugins list, and a route-match simulation that runs Elementor Pro's own `Conditions_Manager::get_documents_for_location('single')` against a probed `pal_program` URL — answers definitively whether ANY published doc claims that URL.
* Safe and read-only — does not modify any meta or options. Output is JSON download, no UI side-effects.

= 1.8.77 =
* **Fix: unchecking a checkbox in Settings didn't stick** — every checkbox (Show promo bar / Show alert / Alert dismissible / Show search / Show language switcher) silently reverted to checked on save. Classic WordPress checkbox + sanitize gotcha: unchecked checkboxes don't submit ANY value in form data, so `isset($input[$key])` returned false. The sanitize callback's "missing input → use default" branch then used `defaults()` (which has these all set to `'1'` = enabled). Result: the unchecked state was thrown away and the default '1' was saved back. Symptom matches user report — Save Changes shows "Settings saved." but the checkbox is still checked on the next page load.
* Fix: in `sanitize()`, missing input for any boolean/checkbox key (`promo_enabled`, `alert_enabled`, `alert_dismissible`, `gtranslate_enabled`, `search_enabled`) now resolves to `'0'` instead of the default. Checked state still works the same (submits `'1'`, sanitized to `'1'`).

= 1.8.76 =
* **Fix: "Save Changes does nothing" on Nashua PAL — Site Settings.** Page reloaded after every submit but no green "Settings saved" notice ever appeared. Root cause: WordPress' Settings API auto-renders `settings_errors()` ONLY on sub-pages under the built-in Settings menu. Our settings live under a top-level `add_menu_page()` admin page, which means `settings_errors()` must be called manually — and we never did. The save itself WAS persisting; the user just had no visual confirmation, so it looked broken. Fixed by calling `settings_errors()` right after the page title.
* Defensive backup: also render a manual `<div class="notice notice-success is-dismissible">Settings saved.</div>` when `?settings-updated=true` is in the URL AND `settings_errors()` didn't already emit one. Covers the case where an object-cache plugin / Cloudflare page rule / etc. drops the transient between options.php's redirect and our render — without this fallback the user would still see no confirmation under those conditions even though the save was successful.

= 1.8.75 =
* **Fix: footer was reverted to the ugly widget-based version a second time** when 1.8.74 re-ran the page composer to fix the cta_banner schema. Root cause: `/tmp/pal_compose_templates.py` still had a `register("nashua-pal-footer-v2.json", ...)` block defining the widget-based footer. The footer's separate composer at `/tmp/pal_compose_footer.py` (which builds the correct inline-HTML 4-column design) ran first in 1.8.73 — but then the page composer ran in 1.8.74 and clobbered the footer JSON with widgets again. Footer registration is now permanently removed from the page composer so this can't happen again. Footer regenerated via the footer-only composer: inline-HTML 4-column layout (Brand+contact / Quick Links / Get Involved / Newsletter) + bottom bar with copyright + EIN + Privacy/Terms/Policies/Contact links, navy bg, yellow uppercase column headers.
* **New: every section in every bundled template is now full-width** — `layout: "full_width"`, `stretch_section: "section-stretched"`, `gap: "no"` are now defaults set by the composer's `section()` helper. All 149 sections across 26 templates patched with these settings (verified `not full-width: 0`). Section backgrounds now reach the viewport edges instead of getting clipped by the host theme's content container. Widgets inside the sections still cap their own inner content max-width (~1100–1200px), so visual padding is preserved.
* **Importer: `$full_width_types` extended** to include `page`, `header`, `footer` so every imported template post — Theme Builder doc AND library/saved template — gets `_wp_page_template = elementor_header_footer`. This makes Elementor render the template without the host theme's wrapper container, end-to-end.
* **On the live site:** after upload, hit **Nashua PAL → Import Templates → Re-import All** (preserves your edits where possible) OR **🔄 Reset All Conditions** (which now also resyncs `_wp_page_template`). Existing posts that had the theme's default `default_template` will switch to `elementor_header_footer` and pages render full-width on the next visit.

= 1.8.74 =
* **Fatal fix:** Elementor editor crashed with `Controls_Stack::sanitize_settings(): Argument #1 ($settings) must be of type array, null given` on every page template the 1.8.72 composer wrote. Root cause: my composer fed `pal_cta_banner` widget the wrong setting keys (`label / url / style` instead of `btn_label / btn_url / btn_style`) AND emitted `null` button entries in the `buttons` array when no second button was passed. Both fixed in the composer; every page template regenerated with the correct widget schema. Editor opens cleanly again on all rebuilt templates.
* **Critical Theme Builder routing fix:** Single Program / Single Event / Single Partner templates were stored with template type `single-page` (and `single-post` for Partner). Per Elementor Pro source (`modules/theme-builder/documents/single-page.php`), `single-page` has `get_sub_type() = 'page'` — it routes ONLY to the WordPress `page` post type. For CPT singles (`pal_event`, `pal_program`, `pal_partner`) the correct type is `single` (multipurpose). Without this match Elementor Pro silently rejected `include/pal_event` conditions on the event template — exactly the "Instances: No instances" symptom the user reported.
  * `nashua-pal-single-event-core-compatible-template.json` type → `single`
  * `nashua-pal-single-program-themebuilder.json` type → `single`
  * `nashua-pal-single-partner-themebuilder.json` type → `single`
  * `nashua-pal-single-post-themebuilder.json` stays `single-post` (default WP post)
  * Importer's `$tb_types` + `$full_width_types` arrays updated to include the `single` type.
* **Reset Conditions now also updates the template type** in `_elementor_template_type` meta — type drift is part of the same routing class as condition drift, so the surgical-repair button must fix both. Run the **🔄 Reset All Conditions** button after upload to flip existing live posts to the correct `single` type without losing any of your Elementor content edits.
* **Fix: `[pal_program]` shortcode rendered empty inside Theme Builder Single templates.** Symptom: `/programs/{slug}/` showed the hero + Related Programs but no description / locations / schedule / CTA. Root cause: the shortcode's "current post" auto-detect used `is_singular()` + `get_the_ID()`, which only resolve once WP's main loop sets up `$post`. Inside Elementor Pro's Theme Builder document render path the shortcode fires earlier — before the loop. Switched to `get_queried_object_id()` + `get_queried_object()->post_type` (set by `WP_Query` during request parsing, way ahead of the loop). Falls back to the old method for non-Elementor contexts.
* **How to fix existing live site:**
  1. Upload `nashua-pal-core.zip` 1.8.74 → Plugins → Activate.
  2. Nashua PAL → Import Templates → click **🔄 Reset All Conditions** — re-applies the corrected condition strings AND the corrected template types to every imported template, then flushes Elementor Pro's conditions cache. Your Elementor edits are NOT touched.
  3. If single Event still shows "No instances": you likely have a competing Theme Builder template (the screenshot showed an "Elementor Single Page #4379" sitting next to ours) — open WP Admin → Templates → Theme Builder → trash any duplicate / legacy single Event entries you don't want, leaving only "Nashua PAL Single Event - Core Compatible".
  4. Hard-refresh the live `/programs/basketball/` and `/events/{slug}/` pages — body content should now render and the right Theme Builder template should fire.

= 1.8.73 =
* **Fix: single Program / single Event pages were rendering the archive template instead of the single template.** Root cause: the bulk-importer's `conditions_map()` used the wrong Elementor Pro condition-string format — `include/post/pal_program` and `include/post_archive/pal_program`. Verified against the Elementor Pro source: `Conditions_Manager::parse_condition()` reads `{type}/{name}/{sub_name}/{sub_id}`, where the second segment is the registered condition's `get_name()`. `Post::get_name()` returns the post-type slug (NOT `post`), and `Post_Type_Archive::get_name()` returns `{post_type}_archive`. So the strings now resolve correctly:
  * Single Event → `include/pal_event`
  * Single Program → `include/pal_program`
  * Single Partner → `include/pal_partner`
  * Single Post → `include/post`
  * Events archive → `include/pal_event_archive`
  * Programs archive → `include/pal_program_archive`
  * Posts archive → `include/post_archive`
  * 404 → `include/general/error_404` (unchanged — was already correct)
  * Search → `include/general/search_results` (unchanged)
  * Header/Footer → `include/general` (unchanged)
* Fix: importer used to **skip Display Conditions on re-import** to "preserve manual overrides". In practice that just let broken/missing conditions persist (the actual bug behind the single→archive fallback). Re-imports now ALWAYS re-apply the bundled conditions. If you genuinely need a custom condition, remove the template from the bundle or fork `conditions_map()`.
* New: **🔄 Reset All Conditions** button (yellow, next to "Import All"). Re-applies the bundled Display Conditions to every imported template's `_elementor_conditions` post meta WITHOUT touching content, page settings, or any of your Elementor edits. Plus a flush of Elementor Pro's conditions cache so the change takes effect on the next page view. Use this when the importer ran fine but pages are routing to the wrong template.
* New: per-template **Reset cond.** action button (in the template list). Same behavior, one template at a time.
* New: every import / replace / reset action calls `clear_elementor_conditions_cache()` — tries `ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Cache::regenerate()` first, falls back to deleting the known option keys. Without this flush Elementor Pro can serve stale URL→template mappings even after the post meta changes.
* Fix: footer template was rebuilt in 1.8.72 with widget-only design that didn't match the navy/yellow UI. Restored to the original-style 4-column inline-HTML footer: brand column (logo + tagline + address/phone/email + socials), Quick Links column (About / Programs / Events / Impact / News / Contact), Get Involved column (Volunteer / Mentor / Coach / Partner / Community Partners / Donate Now), Newsletter column (heading + lede + form), plus bottom bar with copyright + EIN + Privacy/Terms/Policies/Contact links. Auto-updates the copyright year via tiny inline script.
* **Standing rule for templates going forward:** the footer keeps its inline-HTML chrome (PAL widgets via shortcode for dynamic data only); the rest of the templates remain widget-based per 1.8.72.
* **How to fix existing live-site templates:** upload 1.8.73 → Nashua PAL → Import Templates → click "🔄 Reset All Conditions". That's all you need — your existing template posts get the corrected condition strings, the cache flushes, and the next visit to /programs/{slug}/ or /events/{slug}/ renders the single template.

= 1.8.72 =
* **Every page template is now widget-based — no more inline HTML.** Rebuilt 21 templates (About family, Get Involved family, all Theme Builder utility pages, homepage, inner-page starter, contact, footer) to compose every section from real Elementor widgets with panel-driven controls. Editors can now click any section in Elementor and edit its content + style from the right panel, no HTML knowledge required.
* New: **6 inner-page building-block widgets** in `includes/elementor/widgets-pages.php`:
  * **PAL Hero Banner** — small page hero with breadcrumb + title + subtitle, 3 color themes (white/navy/light), configurable padding + alignment. Used as the top section on every inner page.
  * **PAL Card Grid** — flexible auto-fit grid of cards. Each card has image OR icon (4 styles: image-top / large-icon / inline-icon / text-only), title, subtitle, body, optional CTA. Repeater control for cards; 2/3/4/auto-fit column options. Powers "Our Pillars" / "Core Values" / "Staff Grid" / "Partner Tiers" / "Donation Impact" / "Policies" / "Quick Links" across every page.
  * **PAL Tier List** — pricing / partnership / donation tiers with name, price/amount, tagline, benefits list (one per line), featured flag, CTA button. Featured tier gets a "Most Popular" badge + navy background + yellow border. Powers Donate, Partner, and Community Partners tier sections.
  * **PAL Icon List** — repeater of icon + label/sublabel/link rows. Three layouts: stacked rows, horizontal columns, or numbered process steps (auto 1/2/3/4 badges). Powers Contact info, "How It Works" process flows, history timelines, footer link columns.
  * **PAL Content Box** — single highlighted callout box with icon + eyebrow + title + body + 2 buttons. Four themes: navy / gradient / yellow / light. Used for "Night of Champions" feature, "Other Ways to Give", mission-statement highlights.
  * **PAL Quote** — testimonial / pull-quote with text, author, role, optional photo, 2 themes. Used on Impact + Get Involved hub for community stories.
* Templates rebuilt (`templates/` + `nashua-pal-elementor-v2-production-templates/`): `nashua-pal-our-work-v2.json`, `nashua-pal-mission-v2.json`, `nashua-pal-leadership-v2.json`, `nashua-pal-policies-v2.json`, `nashua-pal-community-partners-v2.json`, `nashua-pal-impact-v2.json`, `nashua-pal-get-involved-hub-v2.json`, `nashua-pal-volunteer-v2.json`, `nashua-pal-mentor-v2.json`, `nashua-pal-coach-v2.json`, `nashua-pal-partner-v2.json`, `nashua-pal-donate-v2.json`, `nashua-pal-404-themebuilder.json`, `nashua-pal-search-results-themebuilder.json`, `nashua-pal-single-partner-themebuilder.json`, `nashua-pal-single-post-themebuilder.json`, `nashua-pal-posts-archive-themebuilder.json`, `nashua-pal-homepage-v2.json`, `nashua-pal-inner-page-template-v2.json`, `nashua-pal-contact-page-v2.json`, `nashua-pal-footer-v2.json`. Each one's `widgetType: "html"` count is now ZERO.
* On import, every section is configurable via Elementor's right panel — no need to crack open the HTML widget to change text, images, links, icons, or layout.
* Loader registers the 6 new widgets under the existing "Nashua PAL" category. All include the baseline Style tab (typography + colors + bg + radius) from `NPAL_Elementor_Widget_Base`.
* Re-import the affected templates via Nashua PAL → Import Templates ("⚠ Replace All (Reset)" for a clean slate, or per-template Replace to swap individual ones) to get the widget-based versions live.

= 1.8.71 =
* Cleanup: **Removed 5 legacy/superseded templates** from the bundle and importer — `nashua-pal-header-v2.json` (desktop-only header), `nashua-pal-header-mobile-v1.json` (mobile-only header), `nashua-pal-events-archive-v2.json` (page-type archive), `nashua-pal-programs-archive-v2.json` (page-type archive), `nashua-pal-single-program-v2.json` (legacy single program). Each had been superseded by a newer widget-based equivalent (Unified Header / Widgets v4 archives / Widgets v4 single program). Bundle is now 26 templates (down from 31). Importer admin page is correspondingly less cluttered.
* Importer's `conditions_map()` cleaned up — removed entries for the deleted files. Re-imports continue to work normally for all remaining templates.
* Note: the LIVE site's existing `elementor_library` posts for these legacy templates are NOT auto-deleted — the plugin can't safely auto-trash user content. To finish cleanup on your site: WP Admin → Templates → Theme Builder + Saved Templates → manually trash any "Nashua PAL Header V2", "Nashua PAL Header — Mobile V1", "Nashua PAL Events Archive V2", "Nashua PAL Programs Archive V2", or legacy single program entries you don't want.

= 1.8.70 =
* Fix: **Single Program template hero section** was rendering broken (title overlapping the site header, no navy background, featured image missing) because the bundled JSON had `structure="10"` (1-col) declared on a 2-column section payload. Elementor couldn't reconcile the col-count mismatch and the entire hero section's bg color + padding + child widgets failed to render correctly. Fixed: hero now correctly declares `structure="20"` (2-col 50/50) matching its two columns.

= 1.8.69 =
* New: **Widget-based Single Program Theme Builder template** (`nashua-pal-single-program-themebuilder.json`, title "Nashua PAL Single Program (Widgets v4)") — replaces the minimal legacy v2. 5 sections: (1) navy hero, 2-col with program title + featured image via Elementor's built-in dynamic theme-post-title + theme-post-featured-image widgets; (2) program detail body via `[pal_program]` shortcode (renders description, locations table, age range, schedule, CTA from the meta box); (3) "PAL In Action" Video Gallery (user pastes YouTube URLs per program); (4) Related Programs 3-col grid; (5) Together CTA Banner. Importer's `conditions_map()` updated to set `include/post/pal_program` on the new file and dormant the legacy v2.

= 1.8.68 =
* **PAL Video Gallery widget — proper YouTube support.** Paste any YouTube URL (watch / youtu.be / shorts / embed format) into the main video URL or any thumbnail URL field. Clicking the main play button OR any thumbnail now swaps the player to an inline auto-playing YouTube iframe in the same spot (no more "opens in a new tab" — videos play right on the page). Side thumbnails act as a playlist — click any to swap the main player to that video.
* Auto-fetches YouTube poster thumbnails (`https://img.youtube.com/vi/{ID}/maxresdefault.jpg`) when the user doesn't upload a custom thumbnail — saves the user the step of grabbing screenshots from YouTube.
* New helper: `npal_youtube_id( $url )` extracts the 11-char video ID from all common YouTube URL formats.
* Vanilla-JS click handler registered once per page (guarded by `window.__palvgInit`) so multiple video galleries on the same page each work correctly without duplicating listeners.

= 1.8.67 =
* Fix: Theme Builder templates (archives, singles, search results, 404) imported correctly but rendered narrower than designed — Hello Elementor's parent theme wraps them in its constrained ~1140px content container by default. Importer now sets `_wp_page_template = elementor_header_footer` post meta on every Theme Builder template type (archive / single-post / single-page / search-results / error-404), forcing **Elementor Full Width** layout so the design renders edge-to-edge with only the Theme Builder Header/Footer above/below.

= 1.8.66 =
* Fix: **Featured Event widget** was querying the wrong meta key (`_pal_event_date`) — the actual meta key the plugin writes on save is `_pal_event_start` (matches the `[pal_upcoming_events]` shortcode). Result was "No upcoming events found" displaying even when upcoming events existed. Fixed: widget now queries `_pal_event_start` and filters in PHP via `strtotime()` since the meta value is a strtotime-parseable string in an arbitrary format (not always YYYY-MM-DD). Same fix path as the existing shortcode.
* Featured Event date/time strings now formatted from the parsed timestamp (`l, F j, Y` and `g:i a`) instead of pulling raw meta — consistent with how event date pills format in the existing cards.

= 1.8.65 =
* New: **6 content-design widgets** (PAL Page Hero / PAL Section Heading / PAL Featured Event / PAL Video Gallery / PAL Photo Gallery / PAL CTA Banner) — every section of the Events + Programs archive is now a real Elementor widget with panel-driven controls (eyebrow, title, lede, photos, video URLs, button repeater, etc.). NO inline HTML widgets anywhere in the archive templates.
* **PAL Featured Event widget** auto-pulls the next upcoming event from the merged `pal_event` CPT + Google Calendar source (same query logic the existing `[pal_upcoming_events]` shortcode uses) — featured card content stays in sync with whatever event is actually next without any manual wiring. Override fields available for fixed/curated featured event.
* Archive templates rebuilt to **list ALL events / programs** (`count="-1"`) — no "View All" button since the archive IS the listing.
* **13 PAL-themed stock photos bundled** in `assets/img/` (hero-events, hero-programs, featured-event, featured-program, video-main, video-thumb-1/2/3, photo-1 through photo-5) — enhanced from raw multi-MB originals via `sips` to 100-500KB JPEGs at 80-82% quality, max 600/800/1200/1600px dimensions per role. Total ~3.4MB. Archive widgets reference them via `{{NPAL_PLUGIN_URL}}assets/img/...` placeholder.
* **Importer token substitution**: `import_one()` replaces `{{NPAL_PLUGIN_URL}}` with the real `NPAL_PLUGIN_URL` value before writing `_elementor_data`, so bundled image references resolve correctly on any WordPress install.

= 1.8.64 =
* Redesign: **Events Archive + Programs Archive** rebuilt from scratch to match the new "Community Events" mockup. 7-section layout (Events) / 6-section layout (Programs): (1) navy hero with side photo + yellow eyebrow + lede, (2) full-width Featured Event/Program card with side photo + meta row + dual buttons, (3) Upcoming Events / All Programs section with eyebrow + heading + Grid/Calendar view toggle pill buttons + 4-column card grid + "View All" CTA, (4) "PAL In Action" navy video gallery — large main video + 3 side thumbnails + "Watch More on YouTube" button, (5) Photo Highlights 5-column gallery row + "View Full Gallery" button, (6) Past Events grid with date pills + play-overlay thumbnails + "View Recap" links (Events only), (7) "Together, We Build Stronger Futures" navy CTA strip with hands icon + 3 buttons (Donate / Volunteer / Become a Sponsor). All cards use inline-styled HTML widgets with Montserrat headings, navy/yellow brand palette, soft shadows + rounded corners.
* Cards use placeholder photos from `picsum.photos` with stable seeds — user replaces them in Elementor when ready. Static placeholder cards in the Upcoming + Past sections preserve the design even before any events are posted; once events have featured images and the user wires `[pal_upcoming_events]` in, the design has a natural upgrade path.
* New titles disambiguate from prior versions: "Nashua PAL Events Archive (Community Events v3)" / "Nashua PAL Programs Archive (Our Programs v3)". Combined with the 1.8.62 dedup-by-(title+type) fix, these import cleanly side-by-side with the legacy v2 page templates.

= 1.8.63 =
* New: **"Replace" import mode** in the bulk template importer — destructive variant that `wp_delete_post`'s the matched existing template entirely and re-creates it from the bundled JSON, so manual Elementor edits AND Display Condition customizations get fully reset to the bundled defaults. Useful when the user has experimented their way into a broken state and wants a clean slate. Exposed as: a top-level **⚠ Replace All (Reset)** red button (with confirm dialog) next to the regular **⬇ Import All Templates** button, AND a per-template **Replace** action (red, with confirm) shown only for already-imported templates. Regular Import / Re-import is unchanged — still updates in place and preserves your Display Conditions.
* Architecture: handler refactored to a single `run_all($force_replace)` / `run_one($force_replace)` pair instead of duplicating the handler logic. `import_one()` grew a `$force_replace` flag; when true, the matched existing post is deleted before insert so the new post starts fresh with all bundled defaults (including conditions, which the regular update path preserves).

= 1.8.62 =
* Redesign: **Events Archive** and **Programs Archive** Theme Builder templates. Tighter padding throughout (45px hero vs 70px previously), consistent 3-column grid layout (was list view for upcoming + grid for calendar — inconsistent rhythm), new filter chips strip ("Upcoming" / "Past" with anchor scroll, "All Programs / Sports / Mentoring / Community" on programs), section divider lines between major regions, narrower newsletter CTA at the bottom. Section count dropped from 3-4 to 4-5 sections each with much better visual hierarchy. New titles: "Nashua PAL Events Archive (Redesigned)" / "Nashua PAL Programs Archive (Redesigned)" — distinct from the legacy v2 titles so the dedup logic doesn't collide.
* Fix: Bulk-import dedup-by-title was too aggressive — two bundled JSONs sharing a title (e.g. archive-type `nashua-pal-events-archive-themebuilder.json` and page-type `nashua-pal-events-archive-v2.json`, both titled "Nashua PAL Events Archive V2") would collapse to ONE post because the second import found the first by title alone and overwrote `_elementor_template_type` from `archive` to `page`. Dedup now matches by `(title AND template_type)` so different-typed templates with the same title coexist as separate posts (Theme Builder Archive shows one, Saved Templates shows the other).

= 1.8.61 =
* New: **Unified responsive header** (`nashua-pal-header-unified.json`) — single Theme Builder header template that fully replaces the legacy desktop + mobile pair. Combines 6 sections with Elementor's per-section Responsive visibility flags (`hide_desktop` / `hide_tablet` / `hide_mobile`): promo bar (all), site alert (all), desktop toolbar (desktop only), desktop main row with logo + nav menu + donate (desktop only), mobile nav bar with logo + search/language/hamburger (tablet + mobile), mobile menu drawer (tablet + mobile). One template, one "Entire site" condition — no more Theme Builder priority conflicts between two headers.
* The two legacy headers (`nashua-pal-header-v2.json` desktop-only + `nashua-pal-header-mobile-v1.json` mobile-only) remain bundled but the importer no longer attaches any Display Condition to them — they sit dormant in Theme Builder, safe to trash. The unified header is the recommended single-source-of-truth.

= 1.8.60 =
* Fix: Bulk importer was creating DUPLICATE templates when the user had previously imported the same template manually (e.g. through Elementor's native Theme Builder import). The importer only matched existing posts by its own `_npal_template_source` meta tag — pre-existing templates lacking that tag were bypassed and re-created as new posts. This produced multiple competing headers/footers in Theme Builder, breaking the front-end render. Now matches by source-meta first AND falls back to exact-title match in `elementor_library`, so pre-existing templates get updated in place instead of duplicated.
* Fix: Events grid view — when an event has no featured image, the absolutely-positioned date pill was overlapping the title text (in Community Calendar + Past Events sections). Body now has `padding-left: 80px; min-height: 88px` when card lacks `.pal-event--with-image`, plus a soft gradient background + yellow top accent stripe so no-image cards read as intentional rather than broken. `z-index: 2` added to the date pill so it always sits above the body.

= 1.8.59 =
* Fix: Bulk importer was auto-conditioning BOTH the desktop header (`nashua-pal-header-v2.json`) AND the mobile header (`nashua-pal-header-mobile-v1.json`) to `include/general` (Entire site). Elementor Pro's Display Conditions have no native "mobile breakpoint" option, so two headers matching "Entire site" compete and Elementor picks one — usually the mobile, producing a broken desktop view. Mobile header is now imported WITHOUT any Display Condition; the desktop header keeps its "Entire site" condition unchanged. Users either trash the mobile header (recommended; use the desktop header's per-section Responsive visibility on mobile) or wire it up manually via a third-party breakpoint plugin.
* Note in admin: clarifies the mobile-header behavior so future users don't hit the same trap.

= 1.8.58 =
* New: **One-click bulk template importer** — Nashua PAL → Import Templates. Every Elementor template JSON is now bundled inside the plugin (`templates/` folder, 29 files). Hit "Import All Templates" and the plugin creates an `elementor_library` post per JSON with the right `_elementor_template_type` (header / footer / single-post / archive / search-results / error-404 / page), populates `_elementor_data` from the JSON content, and applies Theme Builder Display Conditions where the file → condition mapping is known (singles → `include/post/{cpt}`, archives → `include/post_archive/{cpt}`, 404 → `include/general/error_404`, search → `include/general/search_results`, header/footer → `include/general` for entire site).
* Re-importing is safe and non-destructive: each imported post is tagged with a `_npal_template_source` meta key pointing back to the bundled filename, so subsequent imports update the existing post rather than duplicating it. Any manual Display Condition edits the user made in Theme Builder are preserved on re-import (conditions are only set on FIRST import).
* The admin page lists every bundled template with its title, type, suggested Display Condition, and import status (imported/not imported + post ID + direct "Open in Elementor" link). Per-template Import / Re-import buttons let users refresh selectively.

= 1.8.57 =
* New: **Every Nashua PAL widget now has a Style tab** in Elementor — Typography (group control: font family, size, weight, line-height, letter-spacing), Text color, Link / accent color, Background color, Border radius. Selectors target the wrapper + common inner elements (a, h1-h5, p, span, li) so the control affects the right text. Per-widget Style overrides can extend the baseline.
* New: **"Nashua PAL" widget category is pinned to the TOP of the Elementor widget panel.** Implemented via reflection on Elementor's private `categories` property — wrapped in try/catch so a future Elementor refactor silently falls back to default ordering rather than fatal-ing.
* Refactor: split `register_controls()` in NPAL_Elementor_Widget_Base into `register_content_controls()` + `register_style_controls()` so subclasses only override the half they care about. Every widget subclass renamed accordingly. (No external behavior change beyond the new Style tab + Content controls now coexisting cleanly.)

= 1.8.56 =
* New: **28 Elementor widgets** under a dedicated "Nashua PAL" category — every front-end `[pal_*]` shortcode is now draggable from the Elementor widget panel, so site editors no longer have to paste raw HTML + shortcode into HTML widgets. Buttons: PAL Donate Button. Header tools: PAL Logo / Search / Language Switcher / Social Icons / Promo Bar / Site Alert / Mobile Menu Button / Mobile Menu Drawer. Homepage features: PAL Stats Bar / Single Stat / Programs Grid / Upcoming Events / Events (full) / Event Date Pill / Latest News / Partners Carousel / Get Involved Strip. Contact + inline: PAL Phone / Email / Address / Newsletter Form / Underline Highlight. Event variants: PAL Awardees Grid / Event Sponsors / Event Embed / Previous Champions / Single Program.
* New: Each widget exposes its underlying shortcode's most-used attributes as Elementor controls (count, view, columns, show_image, image_size, when, order, etc.) — including `[pal_donate_button]` URL + label override and `[pal_underline]` enclosed-content text field.
* Each widget gets a sensible Elementor icon (eicon-heart, eicon-site-logo, eicon-calendar, eicon-medal, etc.) plus keywords so search in the widget panel finds them quickly.
* Architecture: shared `NPAL_Elementor_Widget_Base` abstract centralizes the render protocol — subclasses declare a `$shortcode` + return an `args_for_shortcode()` map; the base composes `[tag attr="val"]` and pipes through `do_shortcode()`. Each widget is 15–60 lines instead of duplicating boilerplate 28 times.
* Loader auto-registers under the `nashua-pal` widget category on `elementor/elements/categories_registered` and `elementor/widgets/register`. Widgets only load when Elementor is active — no error on sites without Elementor.

= 1.8.55 =
* New: **Theme tab** in Nashua PAL → Settings — re-skin the site without touching code. Controls: 6 brand color pickers (Primary navy, Primary gradient stop, Accent yellow, Accent hover, Light background, Body text), button vertical/horizontal padding + radius + font size, section padding, heading + body font (curated 10-font Google Fonts list: Montserrat, Open Sans, Inter, Roboto, Lato, Poppins, Playfair Display, Merriweather, Source Sans 3, Nunito), heading weight (400–900), base font size.
* New: **NPAL_Theme_Vars** class emits a `<style id="pal-theme-vars">` block at `wp_head` priority 999 with `:root` CSS variable overrides plus targeted rule overrides for `.pal-btn`, `.pal-section`, `body`, and headings. Because :root has equal specificity to other :root declarations, the LAST one wins — so this beats both the plugin's stylesheet and the active theme's style.css.
* New: chosen heading + body Google Fonts are bundled into a single CDN request (deduped if heading and body pick the same family).
* The Theme tab settings live on the existing single-option-key sanitize loop with bespoke branches: `sanitize_hex_color` for colors (falls back to default on invalid), `intval` clamped with `min`/`max` for pixel sizes, `floatval` for rem sizes, allowlist check for font keys, allowlist `(400,500,…,900)` for heading weight.
* WP's built-in `wp-color-picker` script/style is enqueued on the Settings screen so color swatches render natively.

= 1.8.54 =
* New: **Event template variants** — each Event post now has a "Template variant" dropdown in Event Details: Community (info+body+sidebar, default), Embedded fundraiser (wide BetterUnite/iframe + sponsors), or Night of Champions (awardees grid + previous winners + sponsors). The companion `nashua-pal-event-layout-helper` plugin (bumped to 2.0.0) reads the meta and renders the right variant from a single Theme Builder Single template.
* New: **Event meta extensions** — added Embed HTML (textarea, wp_kses iframe/script-safe), Sponsor Partner IDs (comma-separated), and Youth Awardees (pipe-delimited textarea: `Name|PhotoURL|Grade/School|Bio|OptionalProfileURL`, one per line).
* New: **[pal_awardees]** — renders an awardee card grid from the current event's awardees meta. Supports `columns` (2-4), responsive down to 1 column on mobile.
* New: **[pal_event_sponsors]** — renders a sponsor logo grid from the current event's selected Partner IDs. Supports `columns` (2-6) and `heading`. Uses Partner CPT featured images.
* New: **[pal_event_embed]** — renders the event's embed HTML inside a wide 1400px container so BetterUnite forms have room.
* New: **[pal_previous_champions]** — queries past `pal_event` posts with `_pal_event_template = 'champions'` and renders cross-link cards (year + title + thumbnail). Auto-excludes the current event.
* Templates shipped: Single Program, Programs Archive, Events Archive, Our Work, Mission, Leadership, Policies, Community Partners, Impact, Get Involved hub, Volunteer, Mentor, Coach, Partner, Donate — all in `nashua-pal-elementor-v2-production-templates/` as importable Elementor JSONs.

= 1.8.53 =
* Fix: PAL logo appeared pixelated/soft when displayed above ~125 px because the bundled `Pal-Logo.png` is only 250×250 — fine for thumbnails, blurry on retina displays at hero sizes. Two changes:
  * **New "Site Logo" setting** — Settings → General → Site Logo (high-res). Upload a 1024×1024+ PNG (or an SVG, which scales infinitely). Used everywhere `[pal_logo]` renders + the mobile menu drawer + the mobile header template. Falls back to the bundled 250×250 logo if empty.
  * **Sharpness CSS hints** — added `image-rendering: -webkit-optimize-contrast` + GPU-accelerated layer (`transform: translateZ(0)` + `backface-visibility: hidden`) on every logo `<img>`. Browsers now request the best available downscale filter, killing visible moiré on detailed marks like the PAL badge.
* Recommended: re-export the PAL logo from Illustrator / Canva at 1024×1024 PNG (or larger / SVG) and upload via Settings → General. The bundled file stays as a safety fallback for first-time installs.

= 1.8.52 =
* New: Site Alert popup gets a **"Single uploaded graphic"** layout mode for users who design the entire popup in Canva / Photoshop / etc. and just want to drop in one image. Settings → Site Alert → top of the tab → new "Layout mode" radio toggle:
  * **Designed layout** (default) — existing structured left + right panel built from individual fields
  * **Single uploaded graphic** — upload one image (recommended 800×800 to 1200×1200) plus an optional click URL that turns the whole popup into a link. Skips the field-by-field builder entirely.
* Image-only popup renders the graphic at the popup's natural width (max 640px) with rounded corners, no background panel chrome around it. The close × button floats over the top-right corner with a translucent navy disc + white border so it stays visible regardless of how dark the uploaded graphic is. ESC / overlay click / × button all dismiss as usual; the dismiss key is hashed off the image URL so swapping the graphic re-shows it to previously-dismissed visitors.
* New: `[pal_program]` shortcode renders a single program at full detail — featured-image hero + icon badge + title + short description + post content + "Where it's offered" locations grid + Learn More CTA. Use `id="N"` or `slug="..."`, OR drop unparametrized inside a Program single-post template (auto-detects current post). Three display toggles: `show_image`, `show_locations`, `show_cta` (all "yes" by default).
* Docs: `[pal_program]` added to the Shortcodes reference page with attribute table + 3 examples.

= 1.8.51 =
* Icon picker — Font Awesome tab redesigned: added a yellow how-to box at the top with a 3-step flow ("Browse fontawesome.com/search → click any icon → copy its CSS class → paste below"), an inline link to the **Font Awesome 6 Free** library (filtered to free-only, ranked by relevance), and a secondary **"Open Font Awesome library"** button next to Apply. Same library Elementor's icon widget pulls from.
* Hint text expanded with copy-paste-ready examples covering common nonprofit / event use cases (`fas fa-shield-halved`, `fas fa-users`, `fas fa-bullseye`, `fas fa-handshake`, `fas fa-basketball`, `fas fa-calendar`, `fas fa-heart`, `fas fa-bullhorn`, `fas fa-graduation-cap`, `fas fa-trophy`) and brand classes (`fab fa-facebook`, `fab fa-instagram`, `fab fa-youtube`, `fab fa-x-twitter`).
* No new icons bundled — keeps plugin lean. Font Awesome 6 is already loaded on the front end and on the icon picker modal, so any class admins paste renders immediately in the live preview.

= 1.8.50 =
* Fix: URL fields rejected relative paths like `/activity-programs/` because they used `<input type="url">` which forces browser-level full-URL validation. Switched all admin URL fields to `<input type="text">` with a placeholder hint (`https://… or /relative-path/`). Server-side `esc_url_raw()` already preserves relative paths, so no other change needed. Affected fields:
  * Programs CPT meta — Learn-more URL + Locations → URL (existing rows + new dynamically-added rows)
  * Events CPT meta — Register / RSVP URL
  * All Settings page URL fields routed through `text_row( …, 'url' )` (Donate URL, Register URL, Promo button URL, Alert primary/secondary button URLs, social URLs, Newsletter form action) — central helper now downgrades type=url → type=text + adds the same placeholder hint
* Existing values stored with full URLs continue to work unchanged.

= 1.8.49 =
* Fix: language dropdown not opening when tapped on mobile. Root cause: per-widget inline `<script>` tags binding `addEventListener('click')` directly on `.pal-language__btn` were unreliable inside Elementor HTML widgets in some setups (script execution order vs. shortcode-rendered DOM). Added a global delegation handler in `nashua-pal.js` (`initLanguageDropdowns()`) that listens at the document level and works regardless of inline-script timing. Handles open/close toggle, click-outside-to-close, ESC-to-close, and language item selection (cookie + reload). Inline scripts kept in place as primary binding (still set the visible label from the existing cookie on page load); the delegation kicks in if they fail. Both paths use `stopPropagation` so they don't double-fire.

= 1.8.48 =
* UX: nav menu item Icon field is now a **visual icon picker** instead of a text input. Reuses the same `NPAL_Icon_Picker` modal that powers Stats / Programs / Site Alert — click **Choose icon** under any menu item to open a tabbed modal:
  * **Built-in icons** — visual grid of every brand-styled SVG icon (shield, basketball, users, calendar, heart, megaphone, etc.) — click to select.
  * **Upload custom** — pick any image from the WP media library.
  * **Font Awesome** — paste any FA 6 class (`fas fa-bell`, `fab fa-x-twitter`, etc.) with a live preview tile.
* The icon picker thumbnail (48×48 rounded square) sits inline in each menu item with the chosen icon visible, so admins see what they picked without leaving the menu screen. Save the menu and the icon renders before the label in the mobile drawer.
* Picker assets (icon picker CSS, WP media library, Font Awesome 6 stylesheet) auto-enqueue on `nav-menus.php`. The shared modal renders once in `admin_footer-nav-menus.php` so all per-item pickers reference the same lightbox.

= 1.8.47 =
* New: per-item icons for the WP nav menu — admins now get an "Icon" field on each menu item under Appearance → Menus → expand item (just below the Navigation Label / URL fields). Type a Font Awesome 6 class (`fas fa-shield-halved`, `fas fa-basketball`, `fas fa-users`, `fas fa-calendar`, `fas fa-heart`, etc.) or a built-in `NPAL_Icons` slug (`shield-check`, `basketball`, `users`, `calendar`, `heart`). Live preview tile next to the input shows the rendered icon as you type. Saved as `_pal_menu_icon` post meta on each `nav_menu_item` post via `wp_update_nav_menu_item`. New `NPAL_Menu_Icons` class registered in the bootstrap.
* `NPAL_Mobile_Menu_Walker` now reads the new meta first, falling back to the legacy `pal-mi-<name>` CSS-class detection from 1.8.45 — so existing menus that already use the CSS-class route keep working untouched. The walker is also used as the default for `wp_nav_menu( 'pal-mobile' )` so all icons render in the drawer automatically.
* Mobile logo overflow strengthened: bumped the logo to **130px tall** (was 110) with `margin-bottom: -65px` (was -54) so a bigger portion spills onto the hero photo, matching the mockup proportions. Replaced the `:has()`-only overflow rule with a dual-selector pattern: `:has()` for modern browsers AND a broader `.elementor-element-populated > .elementor-widget-shortcode` rule that works on iOS Safari < 15.4. `.pal-logo` itself gets `overflow: visible` + `z-index: 20` so the badge floats above adjacent content cleanly.
* Note on caching: if changes don't appear after upload, clear (a) WP Engine page cache (User menu → WP Engine → Caching → Purge All) and (b) any CDN sitting in front (Cloudflare → Caching → Purge Everything), then hard-refresh (Cmd-Shift-R / Ctrl-Shift-R) on the device. The plugin's CSS / JS auto-bust via the version number, but server-side page cache often serves the old HTML referencing the previous version.

= 1.8.46 =
* Mobile menu drawer redesign:
  * **Slides in from the LEFT** now (was right). Panel pinned to the left edge with `margin-right: auto`, enters via `transform: translateX(-100%) → translateX(0)`. Box-shadow flipped to fall on the right edge (`10px 0 40px` instead of `-10px 0`). Matches the mobile design mockup.
  * **No internal scrolling on standard mobile viewports** — tightened content density so everything fits within a typical phone screen height (iPhone 13 / Pixel size). Padding 28/24/32 → 20/18/22, gap between widget rows 22 → 14, logo 80px → 64px, menu item padding 16 → 12 + font 15 → 14, button padding 14 → 11 + font 13 → 12, tools/socials top-padding 14 → 10. Drawer still has `overflow-y: auto` as a safety net for very short screens or accessibility text-zoom users.
  * **iOS-friendly viewport height** — switched the `.pal-mobile-menu` container from `inset: 0` to explicit `top/left/right/bottom: 0` plus `height: 100dvh` (dynamic viewport height) and `max-height: 100dvh` on the panel. Handles iOS Safari's URL-bar resize cleanly so the drawer is always exactly the visible-area tall, no clipped logo at the top.
  * **Removed duplicate is-open transform rule** that was redundantly setting `translateX(0)` from the previous right-slide implementation.

= 1.8.45 =
* Fix: promo bar text/icon cluster was left-aligned on desktop after 1.8.42 added the dismiss × button. Cause: `margin-left: auto` on `.pal-promo__close` consumed a flex slot on the right, which pushed the centered content cluster left. Fix: switched the close button to `position: absolute; right: 14px; top: 50%; transform: translateY(-50%)` so it sits at the far right without affecting the centered icon + text + button layout. Mobile keeps the same absolute positioning at `right: 8px`, with `padding-right: 36px` on the bar to reserve space so the text never slides under the close button.
* Mobile menu drawer fixes to match the mockup:
  * **Width**: drawer no longer fills the entire screen — now `width: 80vw` on phones (`88%` on tablets ≥480px), max 420px. Dim backdrop visible to the left so the drawer feels like a slide-in panel rather than a takeover.
  * **Logo size**: capped at 80px tall via `!important` overrides. Theme/Elementor `img { height: auto }` rules can no longer balloon it.
  * **Close ×**: bigger 44×44 button (was 36×36) with a 26px SVG and explicit `stroke: #fff !important` so the X is always visible against the navy panel regardless of theme button styling.
  * **Menu item icons**: walker now scans each menu item's CSS classes for a `pal-mi-<name>` prefix (e.g., `pal-mi-shield-check`, `pal-mi-basketball`, `pal-mi-users`, `pal-mi-calendar`, `pal-mi-heart`) and renders the matching `NPAL_Icons` icon before the link text. Add the class under Appearance → Menus → expand item → "CSS Classes" (advanced field). Also accepts Font Awesome class slugs via `pal-mi-fas-fa-bell` syntax. The fallback menu (shown when no nav menu has been assigned) now ships with icons pre-wired for each of the 5 default items.
* Mobile header logo overflow: rewrote the selector to use `:has(.pal-logo)` so any Elementor section containing the `[pal_logo]` shortcode gets the overflow treatment, regardless of whether the section has the `.pal-mheader` or `.pal-header-main` class. Applies `overflow: visible !important` to every wrapper level (section → container → column → element-populated → widget-wrap → widget-shortcode → widget-container) so Elementor's default `overflow: hidden` can't clip the oversized logo. Drawer logos are scoped out so the effect doesn't double-apply inside the slide-in panel.

= 1.8.44 =
* Promo Bar settings: new "Icon" + "Icon position" controls under Settings → Promo Bar.
  * **Icon picker** — uses the same `NPAL_Icon_Picker` modal that drives Stats / Programs / Site Alert pickers (built-in branded SVG icons + Font Awesome 6 class input + custom image upload via WP media library). Defaults to `megaphone` (the existing built-in icon) so the rendered bar looks identical for users who don't explicitly pick.
  * **Icon position** — select with three options: "Before message" (default), "After message", "No icon". Saved as `promo_icon_position` and validated against the allowed set on save.
* Render: `[pal_promo_bar]` now reads both new settings and emits the chosen icon at the chosen side, falling back to the registered megaphone SVG. Builds a `pal-promo--icon-<position>` modifier class on the wrapper for further per-side styling. The leading-emoji-strip regex from 1.8.42 is preserved so existing emoji-prefixed text keeps working without an edit.

= 1.8.43 =
* Mobile logo overflow: implemented the mockup's "logo extends past the header bar into the hero" effect. CSS bumps the logo image to 110px tall with a `margin-bottom: -54px` so the bottom half of the badge spills out the bottom of the navy header band onto the hero photo, with a soft drop-shadow for separation. Targets both the dedicated `.pal-mheader` (1.8.41 mobile header template) AND the legacy `.pal-header-main` columns of the v2 desktop header so the effect applies regardless of which header template is active on mobile.
* Forced `overflow: visible !important` on every Elementor wrapper level around the logo (section, container, column, element-populated, widget-wrap) since Elementor defaults to `overflow: hidden` on sections in many setups, which would clip the overflowing logo. Logo container gets `position: relative; z-index: 10; line-height: 0` so it floats above the hero photo cleanly.

= 1.8.42 =
* Promo bar redesign to match the mockup:
  * Replaced the literal megaphone emoji (📣 / 📢 / 🔊 — rendered as the platform's blue gradient emoji on iOS / many Android devices) with a custom inline navy SVG megaphone in the brand color, separate from the text. Old saved promo text that still has a leading emoji gets it stripped server-side via a Unicode-codepoint regex so existing settings keep working without an edit.
  * Added a dismiss × button on the right that hides the bar and persists the choice in localStorage (keyed off a hash of the visible text — change the promo copy and previously-dismissed visitors see the new one).
  * Mobile (≤768px): forced single-row layout with `flex-wrap: nowrap`, smaller font (12px), text auto-truncates with ellipsis if too long for the viewport, and the optional CTA button (`Register Today →`) is **hidden** to keep the bar compact. Desktop still shows the button when configured.
  * Default promo text now `SUMMER PROGRAM REGISTRATION IS NOW OPEN!` with no emoji prefix (existing saved values are unchanged).
* Mobile header tools polish (inside `.pal-mheader__tools`):
  * Search collapses to a 36×36 icon-only round button by default. Tapping the icon expands the form inline into a translucent rounded pill with the input field; tapping outside collapses it back if empty. ESC clears + collapses.
  * Language button hides its "Select Language" label and chevron on mobile — globe icon only, sized 20×20, button itself 36×36 round. Existing dropdown still opens on tap.
  * Hamburger bars white by default, yellow on hover, on the navy header background.
* Now matches the mockup's navy header row: PAL logo (left) + 3 white icon buttons (search · globe · hamburger) cleanly aligned to the right.

= 1.8.41 =
* New: ships `nashua-pal-header-mobile-v1.json` — a dedicated Mobile Header template for Elementor Theme Builder. Three sections only: promo bar (full width), navy header bar with the logo on the left + search/language/hamburger on the right (`.pal-mheader` + `.pal-mheader__tools` classes), and the `[pal_mobile_menu]` drawer container. Imports cleanly into Theme Builder → Header. Pair with Display Conditions to scope it to mobile-width visitors only.
* CSS: added `.pal-mheader` + `.pal-mheader__tools` styles so the new header renders consistently on import — navy background, single-row layout regardless of Elementor's responsive column behavior, white-on-navy treatment for the search field placeholder, language switcher, and hamburger bars. Logo capped at 56px tall.

= 1.8.40 =
* Fix: hero photo not appearing on mobile. Root cause: in the homepage v2 template the hero photo is set as the column's **background-image** — Elementor stores column backgrounds on the inner `.elementor-element-populated` wrap, NOT on `.elementor-column` itself. My 1.8.39 mobile rule applied `min-height: 380px` to the outer column, but the inner populated wrap (which holds the bg image) had no widgets inside on the image-only column, so it collapsed to 0px and the bg had nothing to fill. Fix: applied `min-height` to BOTH the column AND the populated/widget-wrap inner div, plus explicit `background-size: cover; background-position: center; background-repeat: no-repeat` overrides on the wrap so Elementor's mobile responsive bg controls (which may not be set in the v2 template) don't matter.
* Mobile header tune-up to match the mockup: forced `.pal-header-main` columns into a horizontal row regardless of Elementor's default mobile stacking, with the logo column on the left (auto-width) and the right column flex-aligned to the end so search / language / hamburger sit cleanly together. Hid the desktop WP nav-menu column on mobile via `:has(.elementor-widget-nav-menu)` since the drawer-based mobile menu (`[pal_mobile_menu]`) handles navigation. Tighter section padding (8px/14px) so the header doesn't eat 30% of the viewport. Logo image capped at 56px tall on mobile.

How to use:
1. Install / activate 1.8.40, hard-refresh on mobile.
2. Edit your header in Elementor and add `[pal_mobile_menu_button]` to the rightmost column of `pal-header-main` if you haven't already.
3. Drop `[pal_mobile_menu]` once anywhere in the header (it's `position: fixed`, DOM placement doesn't matter).
4. Appearance → Menus → assign your menu to the "Nashua PAL Mobile Menu" location.

= 1.8.39 =
* Mobile hero layout: rewrote the ≤768px media query for `.pal-hero-split` to match the mobile design mockup. Changes:
  * Photo column now stacks ABOVE the text column (was: text first, photo below). Implemented via `flex-direction: column !important` on the container + `order: -1` on `.pal-hero-image`.
  * Photo column is taller on mobile (380px min, 56vh max) so the kid photo gets proper visual weight matching the mockup.
  * Photo overlay gradient adjusted: subtle top dim + heavier bottom blend (rgba navy 0% → 0% → 60%) so the photo eases into the navy text column below instead of competing with it.
  * Curved swoosh repositioned for mobile — now bridges photo → text column instead of photo → next section, with rounded edges and no skew (removes the desktop-tuned diagonal that looked off when stacked).
  * Text column padding tightened to 38/22/36px and `gap: 14px` between widgets so the title, paragraph, and button row sit cleanly without huge gaps.
  * Headings scaled: H1 38px → 36px, H2 32px, paragraph 14px / line-height 1.5.
  * Hero buttons row: forced side-by-side via `flex: 1 1 calc(50% - 5px)` on Elementor button widgets and `.pal-btn` anchors. Each button gets centered text and tightened padding (12px 14px) so they fit two-up at narrow widths instead of stacking.

= 1.8.38 =
* New: mobile menu drawer system matching the mobile design mockup. Two new shortcodes:
  * `[pal_mobile_menu_button]` — animated hamburger button (3 white bars that morph into an X when the drawer opens). Drop it into the header next to the search/language icons; visible only at ≤1024px viewport via CSS media query.
  * `[pal_mobile_menu]` — full-screen navy panel that slides in from the right when the hamburger is tapped. Includes the PAL logo + close button, the WP nav menu (with chevron toggles for items that have submenus, expand-in-place without leaving the drawer), Donate Today / Register Now CTAs (URLs from Settings → Donate URL / Register URL), an inline search field, language switcher, and social icons row. Each section is toggle-able via shortcode attrs (`show_donate`, `show_register`, `show_search`, `show_language`, `show_socials` — all default "yes").
* Plugin now registers a new WP nav menu location: **Appearance → Menus → Display location → "Nashua PAL Mobile Menu"**. Assign your existing main menu (or a separate mobile-only one) there. Falls back to a placeholder list (Our Work / Programs / Get Involved / Events / Donate) when no menu is assigned, with an admin-only hint linking back to the menus screen.
* Custom `NPAL_Mobile_Menu_Walker` adds a chevron toggle button next to top-level menu items that have children, so users can expand the submenu inline without leaving the drawer. JS handles open/close, ESC-to-close, backdrop-click-to-close, body scroll lock, hamburger-to-X morph animation, and submenu rotate-chevron + slide-down.
* Reference page (Nashua PAL → Shortcodes) lists both new shortcodes under "Header & Site-wide Tools" with attribute tables and copy-to-clipboard.

How to use:
1. Edit your Elementor header template; add `[pal_mobile_menu_button]` to the right side (next to your existing search / language toggles).
2. Anywhere on the same template (or in the footer), add `[pal_mobile_menu]` once.
3. Appearance → Menus → assign your main menu to "Nashua PAL Mobile Menu" location.

= 1.8.37 =
* New: entire event card is now clickable in `[pal_upcoming_events]` and `[pal_events]` — image, date pill, body, excerpt, anywhere — clicking lands on the event detail page (or external Google Calendar URL for gcal events). Implemented via the "stretched-link" CSS pattern: the title's `<a>` grows a transparent `::after` pseudo-element that covers the entire `.pal-event` parent (which is now `position: relative`), so the click hit-area expands to the whole card without nesting links or duplicating anchors.
* Markup: removed the redundant `<a class="pal-event__media">` image wrapper (replaced with `<span>`) since the whole card is now clickable. Title link gets a new `.pal-event__link` class for the stretched-link CSS hook. New `.pal-event--clickable` modifier added when the event has a real URL (skipped on Google Calendar events that came back without one), so cursor: pointer / hover styles only apply when there's actually somewhere to navigate.
* CSS: title text on hover now turns the brand yellow regardless of whether the user's cursor is over the title text or anywhere else on the card. External (gcal) events still open in a new tab via `target="_blank" rel="noopener"` on the underlying anchor.

= 1.8.36 =
* New: `[pal_events]` shortcode — full events listing including both past and upcoming events, intended for a dedicated /events archive page or any "all events" section. Different from `[pal_upcoming_events]` which only surfaces future dates. Pulls from the Events CPT and any Google Calendar feeds, merged and sorted.
* Filtering attributes: `when` ("all" / "upcoming" / "past"), `sources` ("all" / "cpt" / "gcal"), `count`, `order` ("auto" picks asc for upcoming and desc for past/all, or override with "asc" / "desc"), `from` and `to` (any strtotime-parseable date — "2026-01-01", "January 2026", etc., with bare dates in `to` interpreted as end-of-day so the day itself is included).
* Display attributes: `view` (grid/list), `columns` (1-4), `show_image` (default "yes"), `image_size`, `show_date` (toggle the yellow date pill), `show_location`, `show_excerpt` (28-word trim from the post excerpt), `show_status` (renders an "Upcoming" / "Past" badge above the title).
* Visual: past events auto-render at 78% opacity with slightly desaturated images, brightening to 100% on hover so they don't visually compete with upcoming events. Status badges use yellow-on-navy for "Upcoming" and gray for "Past". CSS reuses the existing `.pal-events--grid` / `.pal-events--list` framework so the look stays consistent with `[pal_upcoming_events]`.
* Docs: Nashua PAL → Shortcodes reference page lists `[pal_events]` with all 13 attributes documented and 6 example variations covering common use cases (default, past archive, upcoming list with no images, 4-up grid with status, date-range, CPT-only).

= 1.8.35 =
* Docs: Nashua PAL → Shortcodes reference page now documents every attribute added across 1.8.32–1.8.34. Updated entries:
  * `[pal_upcoming_events]` — added `view`, `columns`, `show_image`, `image_size` rows + 6 example variations covering list / list-with-thumbnails / grid / grid-with-images / different column counts.
  * `[pal_program_cards]` — added `view`, `columns` rows + 4 examples covering default / column count / list view.
  * `[pal_latest_news]` — added `view`, `columns` rows + 4 examples covering default / column count / list view.
  * `[pal_alert]` — clarified that with the popup-modal redesign (1.8.17+) the alert is auto-injected on every front-end page when enabled in Settings, so the shortcode itself is rarely needed for placement.

= 1.8.34 =
* New: `[pal_program_cards]` and `[pal_latest_news]` accept the same `view="grid|list"` and `columns="N"` (1-4) attributes added to `[pal_upcoming_events]` in 1.8.33. Defaults preserved: programs default to `view="grid" columns="4"`, news defaults to `view="grid" columns="3"` — every existing placement renders identically.
* List view turns each card into a horizontal row — image left, body right (240px image column for programs, 220px for news, full-width image stacked on top below 640px). Grid view uses CSS variables (`--pal-prog-cols`, `--pal-news-cols`) keyed off the `--cols-N` modifier so column counts are easy to extend later.
* Responsive: grid auto-collapses to 2 columns at ≤1024px (programs) / ≤900px (news), and to single column at ≤600/560px regardless of the configured count.

Usage examples:
* `[pal_program_cards]` — original 4-up grid (default)
* `[pal_program_cards columns="3"]` — 3-up grid
* `[pal_program_cards view="list" count="6"]` — 6 programs as horizontal list rows
* `[pal_latest_news]` — original 3-up grid (default)
* `[pal_latest_news view="list" count="5"]` — 5 news posts as list rows
* `[pal_latest_news columns="4" count="8"]` — 4-up grid of 8 posts

= 1.8.33 =
* New: `[pal_upcoming_events]` accepts `view="grid"` to render each event as a card (image on top, date pill overlaid in the top-left, title + meta below) instead of the default horizontal list row. The `columns="N"` attribute (1-4) controls how many columns the grid uses; defaults to 3 in grid mode. The grid auto-collapses to 2 columns at ≤900px and 1 column at ≤560px so it stays readable on smaller screens regardless of the configured column count.
* Each grid card has a hover lift (translateY + bigger box-shadow) for tactility, and the date pill is positioned absolutely over the image with a yellow chip + drop-shadow for contrast — looks the same whether or not the row has a featured image (no-image cards just don't show the image area).
* List view (`view="list"`, the default) is unchanged. Pair `view="grid"` with `show_image="yes"` for the full card design with featured images.

Usage examples:
* `[pal_upcoming_events]` — list, 3 events, no images (original behavior)
* `[pal_upcoming_events show_image="yes"]` — list with thumbnails (1.8.32)
* `[pal_upcoming_events view="grid" columns="3" show_image="yes"]` — 3-column grid of event cards
* `[pal_upcoming_events view="grid" columns="4" count="8"]` — 4-up grid of 8 events
* `[pal_upcoming_events view="grid" columns="2" count="4" show_image="yes" image_size="large"]` — 2-up large-image grid

= 1.8.32 =
* New: `[pal_upcoming_events]` accepts a `show_image="yes"` attribute that adds the event's featured image as the first column of each row, before the date pill. Image size is configurable via `image_size="medium"` (any registered WP image size — `thumbnail`, `medium`, `large`, etc.). Falls back to the `_thumbnail_external_url` post meta when no native attachment is set, matching the same fallback used elsewhere in the plugin (program cards, news cards). When the image is wrapped in the event's link, clicking it navigates to the event detail page; falls back to a non-clickable `<span>` when no URL is available. Google Calendar feed events don't expose images, so those rows render with an empty first column to keep the date/title columns aligned.
* CSS: added `.pal-events--with-images` and `.pal-event__media` styles — 80×80 rounded thumbnails with `background-size: cover`, downsizing to 64×64 below 520px. Existing two-column `[pal_upcoming_events]` (without `show_image`) is unchanged.

Usage:
* `[pal_upcoming_events]` — original behavior (date + title only)
* `[pal_upcoming_events show_image="yes"]` — adds featured image column, default `medium` size
* `[pal_upcoming_events show_image="yes" image_size="large" count="6"]` — six events with large featured images

= 1.8.31 =
* Fix: close (×) button was floating at the top-right of the SCREEN instead of the popup card itself, leaving a big visual gap between the X and the popup. Cause: 1.8.20 moved the button outside `.pal-alert-modal__panel` and into the modal container to avoid the button disappearing into the navy left panel. That made positioning relative to the full-screen modal. Fix: button moved BACK inside the panel, positioned at `top: 12px; right: 12px` relative to the panel. Restyled as a smaller solid-navy disc with a white × — reads cleanly on the white right-panel background where the corner lands in the default two-column layout.
* Mobile / stacked layout: the panel's top-right corner sits over the navy left panel on mobile (≤759px), where a navy disc would disappear. Added a media-query swap to a white disc with a navy × in that case so the close button is always clearly visible.

= 1.8.30 =
* New: Script size picker for the alert popup's cursive script line. 4 preset sizes — Small (28/36 px mobile/desktop), Medium (40/54), Large (56/72, default), Extra Large (72/96). Lives directly under the Script font picker in Settings → Site Alert → Left panel — structured. Each card shows an "Aa" sample at the chosen size + the px pair so admins can pick by visual scale. Live-updates the preview on click via CSS variables (`--pal-script-size`, `--pal-script-size-desktop`) applied as inline style on the script element.
* Render: removed the hard-coded `font-size: 56px / 72px` from the script CSS in favor of `font-size: var(--pal-script-size, 56px)` so the chosen size flows in automatically. Also added `overflow-wrap: break-word` and `max-width: 100%` to the script element so very long strings (e.g., "1 CUP CAN HELP HIM WORK FASTER") wrap inside the panel instead of overflowing into the right column. Pair this with selecting a smaller size from the new picker for long script lines.

= 1.8.29 =
* New: visual font picker for the alert popup's "script line" (the cursive yellow text on the structured left panel). 10 Google Font choices — Allura (default), Pinyon Script, Great Vibes, Sacramento, Pacifico, Dancing Script, Lobster, Satisfy, Kaushan Script, Tangerine — each rendered as a clickable card showing the word "Champions" in its own face so admins pick by appearance, not by font name. Live-applies to the preview's script element on click. The chosen font is the only one loaded on the front end (via `fonts.googleapis.com/css2?family=…&display=swap`); all 10 are loaded on the settings page so the picker cards render correctly.
* Settings: new `Script font` row appears in Site Alert tab → Left panel — structured, directly under the "Script line" text input. Stored as `alert_left_script_font` (slug like `allura` / `pinyon-script` / etc.), validated against the registered choice list on save so an unknown value falls back to `allura`.
* Render: the popup's `<p class="pal-alert-modal__left-script">` element now carries an inline `font-family:<stack>;` style derived from the saved choice (via new `NPAL_Settings::script_font_family()` helper), with a `'Brush Script MT' → cursive` fallback chain so the script renders in *some* cursive face even if Google Fonts is blocked.

= 1.8.28 =
* Fix: alert popup rendered three flex columns (simple-mode image + structured left + right content) instead of two when both simple-mode and structured fields had values, causing the right content to shrink to ~8% width and wrap text vertically. Root cause: my CSS rules `.pal-alert-modal__image { display: none; }` / `.pal-alert-modal--with-image .pal-alert-modal__image { display: block; }` (specificity 0,0,2,0) and `.pal-alert-modal__left { display: flex; }` (0,0,1,0) were equal-or-higher specificity than the browser default `[hidden] { display: none; }` (0,0,1,0) — so when JS added the `hidden` attribute to one of them, the class rule won and both stayed visible. Fix: added a defensive `.pal-alert-modal [hidden], .pal-alert-preview-frame [hidden] { display: none !important; }` rule scoped to the modal/preview, so the JS toggle works correctly and only one left element renders at a time. The popup is now always two columns (or one when no left content is set).

= 1.8.27 =
* Fix (CRITICAL): "There has been a critical error on this website" fatal introduced by 1.8.26. When I removed the `if ($image) elseif ($has_structured_left)` wrapper around the left panel to make both elements always render, I left the closing `<?php endif; ?>` behind on line 226 — a fatal PHP parse error any time the alert shortcode ran (which now also auto-injects via `wp_body_open` / `wp_footer` on every front-end page request, so every page broke). Removed the stray endif. Verified all PHP control-flow tags in `alert()` are now balanced.

= 1.8.26 =
* Fix: structured-left-panel text wasn't appearing in the live preview when a simple-mode "Designed image" was also set. Root cause: alert shortcode used `if ($image) elseif ($has_structured_left)` — mutually exclusive at PHP render time, so structured fields never even entered the DOM when a simple-mode image was present. Reworked to ALWAYS emit both `[data-pal-live-image]` (the simple-mode overlay) AND `[data-pal-live-structured-left]` (the structured panel) and let JS toggle which is visible based on the current input state. Net effect: clear the simple-mode image and the structured panel appears instantly with whatever you've typed; type into the simple-mode field and structured hides under the overlay.
* New: simple-mode image input is now live-mirrored — typing/picking a Designed image immediately renders it as the overlay (no Save round-trip). Same for the structured panel's Background image (live `background-image`) and Logo image (live `<img src>`). Image picker `setValue()` now dispatches an `input` event on programmatic value changes, so picking from the WP media library propagates to the live preview JS.
* Auto-hide: the entire left panel now hides itself when neither the simple-mode image nor any structured-left field has content, AND the modal's `--with-image` class (which controls the desktop two-column layout) is toggled live. So a popup with only right-panel content renders single-column without needing a Save.
* Same auto-hide applies to feature/detail slot containers: pick or type to reveal a slot, clear it to re-hide; the parent `<ul>` follows. No more empty rows or empty containers anywhere.

= 1.8.25 =
* Fix: feature icon picker thumbnails appeared in the form but the matching feature row never showed in the live preview, because (1) the `<ul class="pal-alert-modal__features">` was server-rendered only when at least one feature slot already had data saved in the DB — picking an icon in the picker just updates the form input client-side, no DB write — and (2) icon SVGs were never live-mirrored, only re-rendered on Save Changes. Same issue applied to the structured left-panel detail rows.
* New: alert shortcode now ALWAYS emits all 3 feature slots and all 3 detail slots, with the `hidden` attribute on each empty `<li>` and on the parent `<ul>` until at least one slot has content. Live JS now (a) toggles slot/container `hidden` as you type labels or pick icons, and (b) **mirrors the icon picker's rendered SVG into the matching preview slot in real time** via a `MutationObserver` on the icon picker's thumbnail node — when the picker JS swaps the thumbnail's `innerHTML` after an icon pick, the preview slot copies the same markup. No save round-trip needed for icons either.
* Each preview icon slot is tagged with `data-pal-live-icon="alert_feature_N_icon"` / `alert_left_detail_N_icon`; each `<li>` with `data-pal-live-feature-slot="N"` / `data-pal-live-detail-slot="N"` so JS can find and update them. The `<ul>` containers carry `data-pal-live-features` / `data-pal-live-details` for the auto-show/hide behavior.
* Net effect: pick an icon — see it in the preview instantly. Type a label — see it instantly. The "must Save Changes" caveat now applies only to image uploads (designed image, background image, logo image), since those still require server-side media library handling.

= 1.8.24 =
* Fix: live-preview text updates were silently no-op'ing in 1.8.23. Root cause: the inline `<script>` block was placed BEFORE the form fields below it in the DOM, so when the IIFE ran during HTML parsing, `fieldsRoot.querySelector('[name="…"]')` for every input returned `null` (the inputs hadn't been parsed yet). No event listeners were ever attached. Fix: deferred initialization to `DOMContentLoaded` (or run-immediately if the document is already past loading) so the inputs exist when wiring runs.
* Hardening: replaced the CSS attribute-selector lookup `[name="nashua_pal_settings[<key>]"]` (brackets-in-quotes works in spec but is finicky across edge-case parsers) with a one-pass `inputMap` built from `querySelectorAll('input[name], textarea[name], select[name]')`. Now the JS works regardless of how the browser handles bracket characters in attribute selectors.
* Reminder: text fields (eyebrow, title, message, structured-left fields, feature labels, button labels, button URLs, footer line) update **live as you type**. Images and icons still require Save Changes — they need the server-side media library / SVG icon renderer to produce the markup. After Save, the preview reloads with everything (icons + images + text) populated.

= 1.8.23 =
* Admin UX: Site Alert tab redesigned — live preview now sits at the **top** of the tab at full width (was: sticky right column), and all editing fields live below it. Lets admins see the entire popup at proper desktop dimensions (max 940px wide, two-column layout when there's a left panel) instead of a cramped scaled-down sidebar.
* New: text inputs now update the preview **as you type** — eyebrow, title, message body, structured-left fields (subtitle / title / script / year / strip / detail texts), feature labels, button labels, button URLs, footer line. No save-and-refresh loop for text content. Each preview element is wired via `data-pal-live="<setting_key>"` attributes; on `input` events, the JS finds the matching form field and updates `textContent` plus toggles the `hidden` attribute. Multi-line text (Message field) reconstructs paragraph breaks the same way wpautop does.
* Buttons: hide automatically when their label OR url is empty; show when both are filled. URL input updates the `href` live too. The actions container hides itself when no buttons are visible.
* Footer: visibility tied to the footer-text input.
* Render: removed the "all fields empty → return ''" early bail in the alert shortcode. The structural skeleton now always emits when the alert is enabled (with `hidden` attributes on individual empty elements) so the admin Live Preview has DOM nodes to populate. Front-end side effect: enabling the alert with every field empty will show an empty popup card — but that's an obvious misconfiguration the admin will catch in the preview before anyone visits the site.
* Note on icons + images: still update on Save Changes (server-side SVG / picker rendering). The hint in the preview header now reads "Text updates as you type · images and icons update on Save Changes" so admins know what to expect.

= 1.8.22 =
* Admin UX: Site Alert moved from a sub-section of the Top Bars tab into its own dedicated tab — "Site Alert" with a warning-icon (`dashicons-warning`) in the sidebar nav. The Top Bars tab is now just the Promo Bar (renamed accordingly) so the two are no longer competing for space in one panel. Alert tab uses a 2-column edit grid: form fields on the left, a sticky **live preview** pane on the right that re-renders the actual popup at scaled-down size using the values currently saved in the database. No more guessing what the popup will look like — every Save Changes redraws the preview. Falls back to single-column on screens narrower than 1100px.
* Admin UX: replaced the always-open `.pal-stat-block` accordions in the Alert tab with proper collapsible `<details>` groups (Left simple-mode, Left structured, Right content, Right features, Buttons & footer). Each group has a brand-colored summary header with a rotating chevron, opens/closes with a click, no JS required. By default only the most-edited groups (simple mode, right content, buttons & footer) are open — significantly reduces vertical scrolling.
* Admin UX: top-level alert toggles (Show alert, Dismissible) now sit in their own compact panel at the top of the tab so the user immediately sees whether the popup is on/off without scrolling through the form.
* Fix: Front-end popup now hooks to BOTH `wp_body_open` (primary, modern themes) AND `wp_footer` (fallback for themes that don't call `wp_body_open()` in header.php). The popup is `position: fixed` so DOM placement is visually identical either way; the shortcode's static `$alert_rendered` flag keeps both hooks from double-rendering on the same request. This fixes "popup not appearing on front end" on themes that omit `wp_body_open()` — including some Elementor Hello variants and many older custom themes.
* Internals: added `NPAL_Shortcodes::reset_alert_render_flag()` so the admin Live Preview can call `do_shortcode( '[pal_alert]' )` without colliding with the front-end auto-injection guard.

= 1.8.21 =
* Admin UX: added a visual wireframe at the top of Settings → Top Bars → Site Alert showing where every field lands in the rendered popup. Brand-styled mini-mockup (navy left panel + white right panel + dim backdrop) with each region labeled with the exact setting name (`Subtitle`, `Title`, `Script line`, `Year`, `Detail 1/2/3`, `Yellow footer strip`, `Eyebrow`, `Message`, `Feature 1/2/3`, `Primary button`, `Secondary button`, `Footer line`, etc.) so admins can scan from the diagram down to the form and know what they're editing without trial-and-error save/reload cycles. Also shows the close-button placement and labels it "Always shown when Dismissible". Pure HTML/CSS — no images bundled, scales responsively, stays in sync with the actual popup layout.

= 1.8.20 =
* Fix: Site Alert popup close (×) button rendered "blue on blue" — visible only as a navy disc with no X — when the structured-mode left panel pushed the navy area into the corner where the close button was sitting. Two stacked causes: (1) the button was positioned absolutely inside `.pal-alert-modal__panel`, so on mobile (left panel stacks on top) and during the brief two-column overlap on narrow desktops the navy circle background landed directly on the navy left panel, erasing the disc edge; (2) the X SVG used `stroke="currentColor"` and could be repainted by aggressive theme button styles. Fix: moved the close button OUT of the panel and into the modal container itself (top-right of the dim backdrop area) so it always sits over the dark overlay regardless of layout. Restyled with a translucent dark fill, white border ring, and an explicit `stroke: #fff !important` on the SVG so no theme override can disappear it. Position bumped to top: 18px / right: 18px (was top: 12px / right: 12px), size to 40×40 (was 36×36), with a soft drop-shadow for clearer separation from busy backdrops.

= 1.8.19 =
* Polish: Site Alert popup's structured-mode "script line" (the cursive yellow "Champions"-style word) now uses **Allura** from Google Fonts — an elegant, refined script that matches the design mockup. Loaded via `fonts.googleapis.com/css2?family=Allura&display=swap`, enqueued conditionally only when the alert is enabled (so non-alert pages don't pay the request). `display=swap` keeps text visible in the system-cursive fallback (`Brush Script MT` → `Lucida Handwriting` → `cursive`) until Allura loads, so no FOIT. Script line rendering bumped from 44px → 56px (mobile/baseline) and 56px → 72px (desktop ≥760px) to better match the proportions in the mockup.

= 1.8.18 =
* New: Site Alert popup is now fully editable section-by-section in Settings → Top Bars → Site Alert. Every visible piece of the design is a real field — no Photoshop required. Two left-panel modes: (a) **Simple** — upload one designed graphic to fill the entire left panel; (b) **Structured** — used when the simple-mode image is empty, builds the panel from individual fields with brand styling. Structured left panel fields: background image (decorative), logo image, subtitle (small caps), title (block), script line (cursive yellow, "Champions"-style), year, three icon+text detail rows (date / time / location, picked via the same icon picker used by Stats), and a yellow footer strip. Right panel adds a 3-circle feature icon row (icon + label + style: navy or yellow circle) under the message — matches the "Awards & Recognition / Dinner & Entertainment / Support Youth Programs" callouts in the design mockup.
* Settings: alert section reorganized into grouped sub-blocks — General, Left panel (simple mode), Left panel (structured), Right panel content, Right panel features, Buttons & footer. Field count grew significantly but each block is visually scoped with the existing `.pal-stat-block` styling so it scans cleanly.
* Render: cache key now hashes every popup field via `wp_json_encode` so editing any of them re-shows the popup to previously-dismissed visitors. Structured left panel renders with cursive script font (Brush Script MT fallback chain), yellow-on-navy detail-icon chips, and a brand-colored yellow strip — all CSS-driven, no extra HTTP requests for fonts. Feature circles enforce navy↔yellow contrast even when the icon picker returns an SVG with `currentColor`.

= 1.8.17 =
* Redesign: Site Alert is now a centered popup modal (was: top banner) with a dimmed backdrop, fade-in animation, and ESC / overlay-click / X-button dismissal. When an Alert image is set, the popup uses a two-column layout — designed image fills the **left panel** (intended for an event poster e.g. 600×800), structured fields render on the **right panel**: eyebrow label, title, message, primary button, secondary button, footer line. Falls back to a single-column popup when no image is set. Stacks vertically on mobile (image on top). Modal locks background scroll while open. Dismiss state still saved in localStorage; the cache key is now derived from all popup fields, so updating any field re-shows the popup to previously-dismissed visitors.
* Settings: new fields under Top Bars → Site Alert — `Eyebrow label` (small caps tagline above title), `Title` (big bold heading), `Secondary button label` + `Secondary button URL`, `Footer line` (e.g. "Proceeds support Nashua PAL youth programs"). Existing fields (`Message`, `Primary button label/URL`, `Dismissible`, `Alert image`) kept and now flow into the new layout. Section heading renamed "popup banner" → "popup modal" to match new behavior.
* Removed: old banner-style markup and CSS (`.pal-alert`, `.pal-alert-icon`, `.pal-alert-btn`, `.pal-alert-close`). Banner-specific Elementor section neutralizer rules above `.pal-alert` are now no-ops since the class doesn't render — left in place harmlessly. JS `initAlertDismiss()` replaced by `initAlertModal()` with overlay/ESC/X-button close, scroll-lock on body, and DOM removal post-close.

= 1.8.16 =
* Fix: Site Alert (Settings → Top Bars → Site Alert) was enabled but never appeared on the front end because the implementation only rendered when the `[pal_alert]` shortcode was placed manually — the settings UI labels it "site-wide", so users expected automatic rendering. Auto-injected via `wp_body_open` so an enabled alert now shows on every front-end page without requiring shortcode placement. The shortcode still works for ad-hoc placement; an internal `$alert_rendered` static flag prevents double-rendering when both paths are present. Skipped in WP admin and Elementor editor/preview contexts.
* New: `[pal_alert]` and the auto-injected banner now support an optional alert image. Settings → Top Bars → Site Alert → "Alert image" field with a media-library picker (Choose / Remove buttons + thumbnail preview + URL input for direct paste). When set, the image replaces the warning ⚠ icon on the left of the alert. Recommended ratio: 1:1 (e.g., 64×64). Renders at 40×40 on mobile, 48×48 on desktop with rounded corners and `object-fit: cover`.

= 1.8.15 =
* Fix: Elementor editor hung on the "LOADING" splash with a "Can't Edit / Enable Safe Mode" prompt. Real cause (1.8.14 missed it): `class-program-meta.php` was hooking `NPAL_Icon_Picker::modal()` unconditionally onto `admin_footer-post.php` and `admin_footer-post-new.php`, which dumped ~320 lines of HTML + inline CSS (with SVG `!important` rules) + an IIFE into the admin footer of every post-edit screen — including Elementor's editor, which loads at `post.php?action=elementor`. The injected modal stomped Elementor's editor DOM and JS bootstrap. Fix: replaced the bare hooks with a guarded callback that bails if (a) we're not on a `program` post-type screen, or (b) Elementor's editor/preview context is detected. Front-end output and the Programs icon picker are unchanged; the modal now only renders where it's actually needed.

= 1.8.14 =
* Fix: Elementor editor hung on "Editor successfully loaded? / Can't Edit / Enable Safe Mode" whenever a page using `[pal_language]` (header, mobile menu) was opened in the editor. Cause: the Google Translate widget loaded by the language switcher mutates the DOM (wraps text nodes in `<font>` tags, sets cookies) inside Elementor's preview iframe, racing Elementor's preview handshake. Fix: `[pal_language]` now early-returns an empty string when rendered inside Elementor's editor or preview context (detected via `\Elementor\Plugin::$instance->editor->is_edit_mode()`, `preview->is_preview_mode()`, and the `elementor-preview` / `action=elementor` query args). Front-end output is unchanged.

= 1.8.13 =
* Fix: `[pal_language]` dropdown items went white-on-white (invisible) once a non-English language was active. Two stacked causes: (1) Google Translate was translating the dropdown items themselves and wrapping the text in nested `<font>` tags whose color inheritance broke through our specificity bump from 1.8.10; (2) standard language-switcher UX says language names should always render in their own native form anyway — a Vietnamese speaker needs to be able to spot "Tiếng Việt" even when the page is currently translated to Spanish. Fixed by tagging the entire dropdown with `class="notranslate"` and `translate="no"` so GT skips it entirely, AND hardening the menu-item color rule with `!important` (covering the `<li>` plus any descendant in case a future GT build ignores the notranslate marker).

= 1.8.12 =
* Fix: `[pal_language]` now actually translates the page. Threw out the synthetic-event-on-hidden-select approach (1.8.9–1.8.11) — fragile across Google Translate widget versions and silently failed when the widget did not finish mounting. Replaced with the **cookie + reload** pattern every production GT integration uses: clicking a language sets the `googtrans` cookie on three scopes (path-only / host / dot-host, to cover CDN edge cases), then `window.location.reload()`. On the next page load Google Translate reads the cookie during its own init and translates the DOM. The visible label also reads the cookie on page load so it reflects the active language across navigations. Picking "English" clears the cookie and reloads back to the original page text. Trade-off: page reloads on language change (instead of in-place translate) — but it is rock-solid across browsers, ad-blockers, and GT versions.

= 1.8.11 =
* Fix: `[pal_language]` selecting a language now actually translates the page. Two issues stacked: (1) the synthetic `change` event we dispatched on `select.goog-te-combo` was non-bubbling — Google's internal listener is attached on a parent element of the select, not the select itself, so it never saw the event and the page was never translated. Now dispatching both `input` and `change` with `bubbles: true`. (2) The hidden Google Translate engine mount was sized 1×1px which some browsers refuse to mount widgets into at all — bumped to 240×40 (still off-screen via `left/top: -9999px` + `visibility: hidden`). Also added a max-attempts cap (~4s) on the "wait for combo to exist" retry loop with a console.warn when it gives up, so we no longer poll indefinitely if the GT script is blocked by CSP / ad-blocker / network failure.

= 1.8.10 =
* Fix: `[pal_language]` dropdown items were invisible (white text on white background) until hovered. Root cause was a specificity collision — the dark-toolbar rule `.pal-toolbar--dark .pal-language * { color:#fff; }` (0,2,0) was beating the menu's `.pal-language__menu li { color:#071F3F; }` (0,1,1), so the inherited white from the toolbar leaked onto the menu items. Hover state survived because `:hover` pushed it to (0,2,1). Scoped the menu-item color rules with the parent `.pal-language` selector to put them at (0,2,1) — navy text now wins regardless of which toolbar variant the shortcode lives inside.

= 1.8.9 =
* Fix: `[pal_language]` Select Language click now actually opens a dropdown. The previous implementation used an "invisible Google Translate select overlaid on a styled label" trick — but Google injects its widget into a `<div>` it sizes to its own content, so the underlying `<select>` rarely covered the full clickable area, and clicks landed on empty parent space (nothing happened). Replaced with a proper custom dropdown UI: a button that opens a styled `<ul>` of languages (English, Español, Português, Français, Tiếng Việt, 中文); selecting one sets `select.goog-te-combo.value` and dispatches `change` so Google Translate handles the actual translation behind the scenes. Multiple shortcode instances per page (e.g. desktop header + mobile menu) now share a single GT engine instance to avoid duplicate-ID collisions; only the first instance emits the loader script. Keyboard accessible (Esc closes, focus-visible outline, ARIA roles on listbox/option). Google's auto-injected top banner is also suppressed so it cannot push the page layout down when a language is active.

= 1.8.8 =
* New shortcode `[pal_underline]` — wraps any word or phrase in the same animated yellow underline used on the homepage hero ("COMMUNITY"). Drop it inside Elementor headings, paragraphs, button labels, etc. Both `[pal_underline]COMMUNITY[/pal_underline]` (enclosing) and `[pal_underline text="COMMUNITY"]` (self-closing) work. Optional `class` attribute stacks extra CSS classes. Cataloged under Nashua PAL → Shortcodes → Inline Utilities. The underlying `.pal-hero-underline` CSS already shipped in 1.8.x — this just exposes it as a portable shortcode so editors don't have to hand-write `<span class="pal-hero-underline">…</span>` in every Elementor HTML widget.

= 1.8.7 =
* Donate row equal-height: the donate "Your Support Changes Lives" card was visibly shorter than the side Events / Get Involved cards because it had less natural content. Added flex `align-items: stretch` on the row container plus `height: 100%` on `.pal-donate-card` so all three cards now match the tallest column. The Thank You photo column also stretches to fill the new height — it no longer caps at the 400px min-height when the side cards are taller.

= 1.8.6 =
* Donate row layout: "Your Support Changes Lives" middle column is now visibly wider than the side cards. Final widths: Upcoming Events 24% / Donate 52% / Get Involved 24% — donate is more than 2x each side column, reading as the clear centerpiece. Added a CSS lock (`.elementor-section:has(> .elementor-container > .pal-donate-col)`) so Elementor's `structure: 33` equal-column preset can't override the per-column widths after import. Mobile (≤1024px) collapses all three to full-width as before.
* Homepage v2 template JSON updated to match.

= 1.8.5 =
* Hero swoosh fix: the curved white swoosh under the hero photo was sitting INSIDE the image column (bottom:-5px, height:90px), which made the white fill bleed across the bottom of the cop-and-kids photo. Repositioned to bottom:-81px / height:113px so the swoosh now sits BELOW the image — the curve becomes the transition into the navy stats bar instead of overlapping the photo. Mobile values adjusted to match (height 60px, bottom -40px).
* Hero column: added defensive navy gradient on the left text column so it never falls back to white if Elementor's gradient setting fails to load. `.pal-hero-split` and its container are now hard-locked to `#071F3F` with `!important`.
* Donate "Your Support Changes Lives" card: enlarged to be the visual centerpiece of the homepage 3-column row. Middle column widened from 33% → 44% (events/get-involved trimmed to 28% each). Inside the card, photo column expanded from 40% → 50% so the kid-with-Thank-You image now reads as a proper hero photo, not a thumbnail. Photo min-height bumped 320px → 400px (200 → 240 mobile). Heading bumped 24px → 30px.
* Homepage v2 template JSON updated to match.

= 1.8.4 =
* Language selector: now shows a clear "🌐 Select Language ▾" label so visitors know the dropdown is for translation. Google Translate's native UI is layered invisibly on top so clicks still open its dropdown — but visitors see our clean label, not Google's branding.
* Hover state: globe icon, label, and chevron all turn yellow on hover for clear interactivity feedback.

= 1.8.3 =
* Hero underline: now animates continuously — draws left → right (1.4s), holds at full (1s), erases left → right (1.4s), then repeats. Loop cycle is 4.5s. Uses clip-path so the line really appears to be drawn (and erased) rather than scaled. Honors prefers-reduced-motion.
* Hero swoosh: white fill restored under the curved gold border so the bottom of the photo column shows white (matches the mockup composition). The transparent version was over-corrected in 1.7.4.

= 1.8.2 =
* New: Animated underline draws beneath "COMMUNITY" in the hero title on page load. Yellow line scales from 0 → 100% width over 1.2s with an ease-in-out curve, after a 0.5s delay so the title settles first. Honors prefers-reduced-motion.
* Reusable: any text wrapped in `<span class="pal-hero-underline">` gets the same effect — drop it into other headings if you want to highlight a word elsewhere.

= 1.8.1 =
* Fix: Outline SVG icons now render correctly in the picker modal and on the front end. Some WP admin/theme stylesheets apply `svg { fill: currentColor }` globally, which (per SVG spec) overrides the inline `fill="none"` attribute and makes outline icons render as solid blobs or appear invisible. Added defensive `!important` rules forcing fill:none + stroke:currentColor on all PAL icon containers.
* Bumped picker grid icon size from 28px to 32px so they're more visible.

= 1.8.0 =
* New icon system: replaced all 12 filled SVGs with 22 outline-style icons (stroke-width 2, rounded caps) — modern minimal aesthetic that matches the mockup's design language.
* Existing icons updated to outline variants: shield-check, users, target, handshake, basketball, book-open, sun, hands-helping, whistle, graduation-cap, megaphone, heart.
* New icons added (now appear in the picker grid): mentoring (with star sparkle), arrow-right, calendar, clock, location-pin, search, language (globe), mail, phone, newspaper.
* Mentoring program now uses dedicated mentoring icon (star sparkle) instead of generic users icon.
* Get Involved → Volunteer now uses helping-hand icon instead of users.
* All icons are 24×24, currentColor stroke — they automatically pick up the parent's color (yellow on navy stats, white on navy program cards, gold on get-involved tiles).

= 1.7.6 =
* Fix (Save Changes finally works!): Added `novalidate` to the Settings form. Browser HTML5 validation was rejecting submission because url-type inputs on hidden tabs (e.g. promo_button_url="/programs", donate_url="/donate") used relative paths. Chrome wouldn't focus the "invalid" field because it was on a display:none tab and silently aborted the submit with a console warning. Server-side sanitization (esc_url_raw) still validates URLs.

= 1.7.5 =
* Fix: Save Changes on the Settings page wasn't responding. Replaced the icon picker's document-wide click listener with specific button listeners so it can never interfere with form submissions elsewhere on the page.
* Fix: Header toolbar (search field, language switcher, social icons) now renders correctly on the navy background regardless of whether the v1.3.x+ header was re-imported. Search input shows white-on-translucent, Google Translate dropdown renders white text on transparent, social icons are circle pills with hover state.
* Suppressed Google Translate's default white branding bar inside the toolbar.

= 1.7.4 =
* Design: Hero swoosh is now just a thin curved yellow line over the photo (matches mockup) instead of a tall white wave with a gold border-top. Transparent body, 5px gold border, slight skew. The photo now extends straight down to the next section without the white band cutting into it.

= 1.7.3 =
* Fix: Hero photo and "Thank You" donate card photo now auto-fit responsively whether the user sets the photo as the column background OR drops in an Elementor Image widget. Image widget images get object-fit: cover, 100% width/height, and clip overflow — so the photo always fills its column cleanly on every screen size without overflowing.

= 1.7.2 =
* Plugin URI updated to https://cao-tech.com/ — "Visit plugin site" link in the WP Plugins list now opens cao-tech.com.

= 1.7.1 =
* Refactor: Icon picker extracted into a reusable NPAL_Icon_Picker class so any meta box / settings screen can drop it in with two calls (NPAL_Icon_Picker::field() + NPAL_Icon_Picker::modal()).
* New: Programs CPT — the "Icon" field on each program now uses the same visual picker as Settings → Impact Stats. Same three tabs (built-in icons, upload custom, Font Awesome class).
* Code cleanup: Removed 230 lines of duplicated picker code from class-settings.php now that it's centralized.

= 1.7.0 =
* New: Visual icon picker for the Impact Stats. Each stat's icon field now shows the current icon as a chip with a "Choose icon" button. Clicking opens a modal with three tabs:
  – Built-in icons: grid of all 12 SVG icons (shield-check, users, target, handshake, whistle, basketball, book-open, sun, graduation-cap, hands-helping, megaphone, heart). Click any to apply.
  – Upload custom: opens the WordPress media library to pick a PNG/SVG/JPG. The image URL is saved on the stat and rendered as <img>.
  – Font Awesome: paste a free FA class with a live preview, click Apply.
* Backward compat: existing values (FA classes, SVG names, image URLs) all keep working — the picker just gives editors a visual way to set them.
* Front end: the stats shortcode now renders an <img> when the value is a URL (custom upload), an inline SVG for built-in names, or an <i> for Font Awesome — same field, three render paths.

= 1.6.0 =
* Hero: rebuilt as a true 50/50 split — navy gradient column on the left, photo column on the right. Mockup-faithful approach borrowed from the GPT pixel-perfect reference build.
* Hero: white curved swoosh with gold border-top at the bottom of the photo column (CSS :after with border-radius + skewY) — the signature curve from the design.
* Hero: gradient blend on the image's left edge (navy 0% → 65% at 12% → transparent at 38%) so the photo fades cleanly into the navy column.
* Stats: number color now matches mockup (yellow #F4A51C, weight 900, 34px). Labels are pure white and slightly bolder. Divider opacity bumped to 25%.

= 1.5.0 =
* Settings UI: rebuilt as a tabbed interface — General / Top Bars / Impact Stats / Social / Newsletter / Calendar. Each section has its own panel, no more long scroll. Active tab is remembered between visits via localStorage and reflected in the URL hash so editors can bookmark a tab.
* Settings UI: navy left-rail with yellow active state; mobile collapses to a horizontal pill row.
* Settings UI: one shared Save button — switching tabs preserves all unsaved fields, so editors can edit multiple sections before saving.
* Settings UI: each Stat in the Impact Stats tab gets its own labelled card so the four stats are easy to scan and edit.

= 1.4.1 =
* Hero: rebuilt as full-bleed section with photo background + linear gradient overlay (navy 0% → transparent 70%, 90deg). Mockup-accurate. Added yellow diagonal swoosh accent at the bottom-right corner.
* Stats: vertical dividers between stats bumped from 12% to 22% white opacity for the visible separator the mockup shows.
* Program cards: media min-height 220px, border-radius 14px, shadow 0 10px 30px rgba(7,31,63,0.10), lift on hover. Bigger and bolder.
* News cards: media 200px tall, padding bumped to 22-24px, hover lift + bigger shadow, title 17px.

= 1.4.0 =
* New: Inline SVG icon library (NPAL_Icons class). Stats, Get Involved, and Program cards now use SVGs that match the design mockup pixel-for-pixel — including a custom whistle for Coach/Mentor (FA Free has no whistle) and a shield-with-check for Years of Service.
* New: Partner section is now an auto-scrolling slider (not a static row). Shows 6 logos at a time on desktop, 4 on tablet, 3 on mobile. Logos auto-resize via object-fit:contain so any logo aspect ratio fits cleanly. Pauses on hover. Edges fade out via CSS mask. Configurable via [pal_partner_carousel visible="6" speed="30"].
* Design: PAL logo bumped from 140px to 200px and now overflows 80px below the header — matches the brand-anchor placement in the mockup.
* Design: Stats / Promo / Alert / Partner sections use ultra-aggressive Elementor wrapper overrides (:has selectors) to kill every possible source of white gap above and below.
* Backward compat: Settings still accepts Font Awesome class strings (e.g. "fas fa-shield-alt") — the icon helper renders SVG when it recognizes a built-in name, falls back to <i class> otherwise.
* Sample data: WXR program icons updated from FA classes to SVG names (users / basketball / book-open / sun) so re-imports get the mockup-perfect icons automatically.

= 1.3.1 =
* Design: Donate card text now centered (heading, body, button, divider, sponsor link) to match mockup composition.
* Design: Stats bar icons enlarged (32px) and forced inline-flex sizing so Font Awesome glyphs render at full size on the navy bar.
* Design: Get Involved icons updated — Volunteer = users, Coach/Mentor = bullhorn (whistle isn't in FA Free), Partner = handshake, Tutor = graduation-cap. All free icons available out of the box.
* Design: Default Stat 2 icon swapped from "user-friends" to "users" to match the group icon shown in the mockup.
* Templates: Promo bar / alert sections explicitly set gap=no, margin=0, layout=full_width to remove Elementor default gaps above and below.
* CSS: Aggressive overrides applied to .elementor-widget-container wrapping .pal-promo / .pal-alert / .pal-stats / .pal-partners so they render flush regardless of theme defaults.
* Templates: Donate, events, get-involved, latest-news columns rebuilt with native Elementor heading + button + divider widgets (instead of HTML widgets) so editors can click each text/button/icon and edit visually without touching code.
* Donate card heading uses native Heading widget; body uses Text Editor; "Become a Sponsor" is a styled Button widget — all editable in the Elementor sidebar.

= 1.3.0 =
* Design: Top toolbar (search / language / socials) now renders on a navy background to match the mockup. Includes dark-mode styling for the search input, language switcher, and social icons.
* Design: PAL logo upgraded from 90px to 140px and styled to overflow downward 52px into the hero section (matches the brand-anchor placement in the mockup).
* Design: Hero rebuilt as a 55/45 split — navy text column on the left, photo column on the right, with a yellow diagonal swoosh at the bottom-right of the photo (matches mockup composition).
* Design: "Your Support Changes Lives" donate card now uses a 50/50 split — navy text + button on the left, "Thank You" photo on the right — instead of a flat navy block.
* Design: Stats / promo / alert / partner bars use viewport-width breakouts to ensure they always render edge-to-edge regardless of section layout.

= 1.2.0 =
* New: Nashua PAL → Shortcodes admin page. Documents every [pal_*] shortcode the plugin provides — name, description, attributes table, copy-to-clipboard buttons, and usage examples. Grouped by purpose (Header, Homepage, CTA, Contact). Visible to anyone with edit_posts capability so editors can self-serve.
* Cleanup: First submenu under "Nashua PAL" now reads "Settings" instead of duplicating the parent label.

= 1.1.0 =
* New: Google Calendar feed support. Paste public Google Calendar iCal URLs (one per line) under Nashua PAL → Settings → Google Calendar Feed. Events from those calendars now appear in [pal_upcoming_events] alongside any events created manually under Events → Add Event, sorted by date.
* New: [pal_upcoming_events sources="all|cpt|gcal"] attribute lets you choose the source. Default is "all" (merge both).
* New: Includes a dependency-free iCalendar (RFC5545) parser that handles UTC, TZID, date-only, and basic escape sequences. Recurring events (RRULE) render only their first occurrence.
* Performance: Calendar feeds cached for 15 minutes via WP transients. Cache auto-clears whenever the URLs are saved.

= 1.0.6 =
* Fix: Stats bar, promo bar, alert banner, and partner carousel now stretch edge-to-edge regardless of the Elementor section's layout (boxed vs full_width). Previously these full-bleed sections rendered as cards inside the boxed content area.
* Templates: Stats section in the homepage marked as full_width / stretched at the section level so the bar matches the design out of the box.

= 1.0.5 =
* Templates: Removed duplicate "Proudly Partnered With" section from the homepage. Partners now render once, sourced from the footer template (visible on every page).
* Templates: Hero section now uses a real Unsplash photo as the background, with a left-to-right gradient overlay so the headline stays readable on the left while the photo shows through on the right (matches mockup composition).

= 1.0.4 =
* New: External-URL featured image fallback. Programs / news / partners can use a `_thumbnail_external_url` post meta to render a featured image without requiring an attachment in the media library — makes the WXR sample data import work cleanly.
* New: Partner carousel now renders a styled text placeholder when a partner has no logo image, so the section never appears empty during a mockup.

= 1.0.3 =
* Fix: Font Awesome 6 is now force-enqueued so all icons render (stats, get involved, social, donate heart, contact). Previously icons were invisible because Elementor only loads FA when an Elementor icon widget is on the page.
* Fix: PAL logo bundled inside the plugin and served via new [pal_logo] shortcode — no more broken cao-tech.com 404s in the header / footer.
* Fix: Header rebuilt to use Elementor Pro nav-menu's built-in mobile dropdown instead of a custom mobile column. Removes the duplicate "Donate Today" button that was showing on desktop.
* Fix: Program cards without a featured image now show a navy gradient panel with the icon centered, instead of an empty gray box.
* Improve: Homepage hero and "Your Support Changes Lives" card no longer reference broken external image URLs — both display cleanly with just the navy background until you upload your own photos in Elementor.
* New shortcode: [pal_logo width="90"] — renders the bundled PAL logo with a link to home.

= 1.0.2 =
* Fix: Events saved via the datetime-local input now save in MySQL DATETIME format so the homepage query actually finds them. Previously events stayed hidden because the format mismatch broke the comparison.
* Fix: Upcoming-events query rewritten to filter in PHP — handles both legacy and new datetime formats; existing test events show without re-saving.
* Design: Program card icon is now navy with a white border ring (matches mockup).
* Design: Stats bar uses horizontal layout with vertical dividers and inline icon (matches mockup).
* Design: Get Involved list now has 4 rows (Volunteer / Coach·Mentor / Partner / Tutor) per design.

= 1.0.1 =
* Fix: Elementor HTML widgets now run shortcodes — sections that mixed HTML + [pal_*] shortcodes were rendering as plain text.
* Fix: Program cards fall back to "show all" when no programs are flagged "Show on homepage".
* Fix: New programs default to "Show on homepage" checked so the homepage populates without extra clicks.
* Improve: Empty-state messages on homepage sections now tell logged-in editors exactly which admin screen to use.

= 1.0.0 =
* Initial release.
* CPTs: Events, Programs (with locations repeater), Partners.
* Settings: promo bar, alert banner, donate URL, contact info, social links, impact stats, newsletter, header tools.
* Shortcodes: pal_promo_bar, pal_alert, pal_stats_bar, pal_program_cards, pal_upcoming_events, pal_latest_news, pal_partner_carousel, pal_get_involved, pal_donate_button, pal_search, pal_language, pal_socials, pal_phone, pal_email, pal_address, pal_newsletter.
* Front-end: animated stat counters, dismissible alert, sticky TOC active state.
