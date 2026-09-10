<?php
/**
 * Events Archive — PHP fallback.
 *
 * Only fires if Elementor Pro's Theme Builder Archive doesn't match for
 * `pal_event`. When it DOES match, Elementor overrides via the
 * `template_include` filter and this file is never executed.
 *
 * Renders a minimal branded layout (navy hero + Upcoming list +
 * Past list) so visitors don't see Hello Elementor's bare default
 * archive even if Theme Builder isn't wired up yet.
 *
 * @package NashuaPalChild
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>

<header class="pal-page-hero">
	<div class="pal-container">
		<p class="pal-page-hero__eyebrow"><?php esc_html_e( 'What\'s Happening', 'nashua-pal-child' ); ?></p>
		<h1><?php esc_html_e( 'Community Events', 'nashua-pal-child' ); ?></h1>
		<p class="pal-page-hero__lede"><?php esc_html_e( 'Upcoming gatherings, programs, and Community Calendar events from Nashua PAL.', 'nashua-pal-child' ); ?></p>
	</div>
</header>

<section class="pal-section">
	<div class="pal-container pal-container--wide">
		<h2 style="text-align:center;margin:0 0 var(--pal-space-md);"><?php esc_html_e( 'Upcoming', 'nashua-pal-child' ); ?></h2>

		<?php
		if ( shortcode_exists( 'pal_upcoming_events' ) ) {
			echo do_shortcode( '[pal_upcoming_events count="-1" view="grid" columns="3" show_image="yes" image_size="medium_large"]' );
		} else {
			echo '<p style="text-align:center;color:var(--pal-gray-400);">' . esc_html__( 'Activate the nashua-pal-core plugin to display events.', 'nashua-pal-child' ) . '</p>';
		}
		?>
	</div>
</section>

<section class="pal-events-past">
	<div class="pal-container pal-container--wide">
		<h2 class="pal-events-past__title"><?php esc_html_e( 'Past Events', 'nashua-pal-child' ); ?></h2>

		<?php
		if ( shortcode_exists( 'pal_events' ) ) {
			echo do_shortcode( '[pal_events when="past" count="-1" view="grid" columns="3" show_image="yes" order="DESC"]' );
		}
		?>
	</div>
</section>

<?php
get_footer();
