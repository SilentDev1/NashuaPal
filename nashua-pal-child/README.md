# Nashua PAL Child Theme

Brand foundation + per-page CSS for the Nashua PAL 2026 site. Locks in colors / typography across every page (Layout, Programs, Get Involved, Events, Partners) and provides shared CSS hooks for the three event template variants.

## Parent theme

Child of **Hello Elementor**. Hello Elementor must be installed at `wp-content/themes/hello-elementor/`.

## Install

1. Confirm **Hello Elementor** is installed (does not need to be active — it just needs to live in `wp-content/themes/`).
2. WP Admin → Appearance → Themes → **Add New** → **Upload Theme** → upload `nashua-pal-child.zip`.
3. **Activate** the Nashua PAL Child theme.

## What it ships

| File | Loaded when |
|---|---|
| `style.css` | Every page — brand CSS variables, typography, button + card + sponsor primitives |
| `assets/css/pages-layout.css` | Our Work, Mission, Leadership, Impact, Policies, Privacy, Whistleblower, 990 |
| `assets/css/pages-programs.css` | Programs hub + 14 single `pal_program` pages |
| `assets/css/pages-get-involved.css` | Get Involved hub + Volunteer, Mentor, Coach, Partner, Donate |
| `assets/css/pages-partners.css` | Community Partners page + Partners CPT pages |
| `assets/css/pages-events.css` | Events archive — upcoming, past, community calendar |
| `assets/css/event-variants.css` | Single event pages — branched by `_pal_event_template` meta |

`functions.php` resolves the page type (post type, archive, or page slug), adds a `pal-page--{type}` body class, and enqueues **only** the matching per-page CSS — every other page stays light.

## Page-type body classes

| Class | Pages it covers |
|---|---|
| `pal-page--layout` | Our Work / Mission / Leadership / Impact / Policies (and 990, Privacy, Whistleblower) |
| `pal-page--programs` | Programs hub + every single `pal_program` post |
| `pal-page--get-involved` | Get Involved hub + Volunteer / Mentor / Coach / Partner / Donate |
| `pal-page--partners` | Community Partners + single `pal_partner` pages |
| `pal-page--events` | Events archive |
| `pal-page--event-variant` | Any single `pal_event` (all variants) |
| `pal-event-variant--betterunite` | Variant 1 — embedded BetterUnite + sponsors (palgolf style) |
| `pal-event-variant--community` | Variant 2 — info + outside register link + sponsors (conewithacop style) |
| `pal-event-variant--champions` | Variant 3 — awardees + previous winners (2025champions style) |

Elementor templates and the `nashua-pal-core` shortcodes can scope styles using these classes.

## The 3 event variants

The variant is driven by the **Event Template** dropdown on the Event meta box (added by `nashua-pal-core` 1.8.54), which writes `_pal_event_template`. The companion `nashua-pal-event-layout-helper` plugin reads it and dispatches one of three renderers. This child theme styles all three outputs.

### Variant 1 — BetterUnite Embed (palgolf)

- `_pal_event_template = betterunite`
- `[pal_event_embed]` (from core 1.8.54) renders the iframe
- **Wider embed (user-requested):** the child widens the content area to **1280px** on this variant only, and forces the iframe to `min-height: 760px` — so the BetterUnite form doesn't collapse into a tall narrow column
- `[pal_event_sponsors ids="…"]` renders the sponsor strip below

### Variant 2 — Community Event (conewithacop)

- `_pal_event_template = community`
- 2-column layout (main body + sticky sidebar) — collapses to single column below 880px
- Sidebar holds the outside register/RSVP link, address, time, contact
- `[pal_event_sponsors]` renders below the body

### Variant 3 — Champions / Awardees (2025champions)

- `_pal_event_template = champions`
- Optional yellow fundraiser banner at the very top with a link out to the BetterUnite page
- `[pal_awardees]` renders the 11–13 awardee cards from the pipe-delimited Youth Awardees field
- `[pal_previous_champions]` renders the chip-style list of prior-year links
- `[pal_event_sponsors]` renders the sponsor strip

## Customizing

- Brand colors live in `style.css` under `:root` (the `--pal-*` vars). Override there or scope inside a per-page CSS file.
- To pull a new page slug into one of the existing page-type stylesheets, add it to the lookup array in `npal_child_resolve_page_type()` in `functions.php` and bump `NPAL_CHILD_VERSION`.

## PHP template hierarchy

These render automatically when the matching post type / archive view is requested. Elementor Pro's Theme Builder Single/Archive templates (when display conditions match) override these — so they act as a branded **safety net**, never a competitor to Theme Builder.

| File | Fires for |
|---|---|
| `single-pal_event.php` | Single event — routes through `[pal_single_event_layout]` (variant dispatcher) |
| `single-pal_program.php` | Single program — uses `[pal_program]` + Related Programs strip |
| `archive-pal_event.php` | `/events/` archive — Upcoming + Community Calendar + Past |
| `archive-pal_program.php` | `/programs/` archive — all 14 program cards + bottom CTA |

## User-selectable page template

| Template Name | File | What it does |
|---|---|---|
| **PAL Flexible Page** | `template-flexible.php` | Navy hero with the page title + excerpt (lede), optional featured image banner, then the standard content area. Pick from Page Attributes → Template when creating any custom page that doesn't have a purpose-built Elementor layout. Works without Elementor — paste/write content in the standard editor and you get a branded page out of the box. Set Custom Field `_pal_page_eyebrow` to display a small uppercase eyebrow above the title. |

## Version

1.1.0 — added PHP template hierarchy fallbacks (single + archive for event/program), the "PAL Flexible Page" user-selectable template, and theme screenshot.

1.0.0 — initial scaffold: brand foundation, per-page CSS, event variant styling, BetterUnite widener.
