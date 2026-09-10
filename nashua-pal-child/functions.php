<?php
/**
 * Nashua PAL Child Theme — bootstrap.
 *
 * Locks in brand styling across every page on the Nashua PAL 2026 site and
 * provides shared CSS hooks for the three event template variants (handled
 * by the companion nashua-pal-event-layout-helper plugin).
 *
 * @package NashuaPalChild
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'NPAL_CHILD_VERSION', '1.1.24' );
define( 'NPAL_CHILD_DIR', get_stylesheet_directory() );
define( 'NPAL_CHILD_URL', get_stylesheet_directory_uri() );

/**
 * Enqueue parent (Hello Elementor) + child + per-page-type CSS.
 *
 * Priority 20 runs after the parent theme's own wp_enqueue_scripts so the
 * child stylesheet always wins specificity ties on equal selectors.
 */
add_action( 'wp_enqueue_scripts', function () {

	// Parent theme — Hello Elementor's handle is 'hello-elementor'.
	if ( wp_style_is( 'hello-elementor', 'registered' ) ) {
		wp_enqueue_style( 'hello-elementor' );
	}

	// Child base — brand vars, typography, primitives.
	wp_enqueue_style(
		'nashua-pal-child',
		get_stylesheet_uri(),
		array( 'hello-elementor' ),
		NPAL_CHILD_VERSION
	);

	// Global Elementor button brand override — loads on every page.
	wp_enqueue_style(
		'nashua-pal-child-elementor-buttons',
		NPAL_CHILD_URL . '/assets/css/elementor-buttons.css',
		array( 'nashua-pal-child' ),
		NPAL_CHILD_VERSION
	);

	// Per-page-type CSS — only load what this page needs.
	$type = npal_child_resolve_page_type();
	if ( $type ) {
		$map = array(
			'layout'        => 'pages-layout.css',
			'programs'      => 'pages-programs.css',
			'get-involved'  => 'pages-get-involved.css',
			'partners'      => 'pages-partners.css',
			'events'        => 'pages-events.css',
			'event-variant' => 'event-variants.css',
			'jobs'          => 'pages-jobs.css',
		);
		if ( isset( $map[ $type ] ) ) {
			wp_enqueue_style(
				'nashua-pal-child-' . $type,
				NPAL_CHILD_URL . '/assets/css/' . $map[ $type ],
				array( 'nashua-pal-child' ),
				NPAL_CHILD_VERSION
			);
		}
	}
}, 20 );

/**
 * Resolve the current request to a page-type slug.
 *
 * Returns null for pages we don't recognize (so per-page CSS isn't enqueued).
 *
 * @return string|null
 */
function npal_child_resolve_page_type() {

	// Single event — handled by helper plugin's variant dispatcher.
	if ( is_singular( 'pal_event' ) ) {
		return 'event-variant';
	}

	// Programs CPT — single or archive.
	if ( is_singular( 'pal_program' ) || is_post_type_archive( 'pal_program' ) ) {
		return 'programs';
	}

	// Events archive (upcoming + past + community calendar).
	if ( is_post_type_archive( 'pal_event' ) ) {
		return 'events';
	}

	// Jobs CPT — single or archive.
	if ( is_singular( 'pal_job' ) || is_post_type_archive( 'pal_job' ) ) {
		return 'jobs';
	}

	// Partners CPT — single or archive.
	if ( is_singular( 'pal_partner' ) || is_post_type_archive( 'pal_partner' ) ) {
		return 'partners';
	}

	// Map standard WP pages by slug.
	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );

		// "Layout" pages — Our Work, Mission, Leadership, Impact, Policies sub-set.
		$layout_slugs = array(
			'our-work', 'mission', 'leadership', 'impact',
			'policies', 'privacy', 'whistleblower', '990',
		);
		if ( in_array( $slug, $layout_slugs, true ) ) {
			return 'layout';
		}

		// Get Involved hub + sub-pages.
		$get_involved_slugs = array(
			'get-involved', 'volunteer', 'mentor', 'coach', 'partner', 'donate',
		);
		if ( in_array( $slug, $get_involved_slugs, true ) ) {
			return 'get-involved';
		}

		if ( in_array( $slug, array( 'community-partners', 'partners' ), true ) ) {
			return 'partners';
		}

		if ( 'events' === $slug ) {
			return 'events';
		}

		if ( 'programs' === $slug ) {
			return 'programs';
		}

		if ( 'jobs' === $slug ) {
			return 'jobs';
		}
	}

	return null;
}

/**
 * Add body classes for page-type targeting so Elementor templates and
 * sub-CSS files can scope styles cleanly.
 */
add_filter( 'body_class', function ( $classes ) {

	$type = npal_child_resolve_page_type();
	if ( $type ) {
		$classes[] = 'pal-page--' . $type;
	}

	// For event variants, expose the specific variant slug
	// (set by the Event Template dropdown in the nashua-pal-core meta box).
	if ( is_singular( 'pal_event' ) ) {
		$variant = get_post_meta( get_the_ID(), '_pal_event_template', true );
		if ( in_array( $variant, array( 'embedded', 'community', 'champions' ), true ) ) {
			$classes[] = 'pal-event-variant--' . $variant;
		}
	}

	return $classes;
} );

/**
 * Widen the content column on BetterUnite-variant event pages so the
 * embedded donation form doesn't collapse into a tall narrow column.
 * (Direct user request: "can we make it wider so it doesn't have to be
 * so long?")
 *
 * Emitted inline in <head> so it overrides Elementor's per-page section
 * widths without a stylesheet hop. Scoped to body class so no other
 * pages are affected.
 */
add_action( 'wp_head', function () {

	if ( ! is_singular( 'pal_event' ) ) {
		return;
	}

	$variant = get_post_meta( get_the_ID(), '_pal_event_template', true );
	if ( 'embedded' !== $variant ) {
		return;
	}

	?>
	<style id="pal-child-embedded-widen">
		body.pal-event-variant--embedded .elementor-section.elementor-section-boxed > .elementor-container,
		body.pal-event-variant--embedded .e-con,
		body.pal-event-variant--embedded .pal-event-embed { max-width: 1280px; }
		body.pal-event-variant--embedded .pal-event-embed iframe { width: 100%; min-height: 760px; border: 0; }
	</style>
	<?php
} );

/**
 * Basic theme support — Hello Elementor handles most, but be explicit.
 */
add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
} );
