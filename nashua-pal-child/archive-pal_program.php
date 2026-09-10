<?php
/**
 * Programs Archive — PHP fallback. Only fires if Elementor Pro's Theme
 * Builder Archive doesn't match for `pal_program`.
 *
 * @package NashuaPalChild
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>

<header class="pal-page-hero">
	<div class="pal-container">
		<p class="pal-page-hero__eyebrow"><?php esc_html_e( 'What We Offer', 'nashua-pal-child' ); ?></p>
		<h1><?php esc_html_e( 'Our Programs', 'nashua-pal-child' ); ?></h1>
		<p class="pal-page-hero__lede"><?php esc_html_e( 'Sports, mentoring, scholarship and community programs serving Nashua youth — find one that fits.', 'nashua-pal-child' ); ?></p>
	</div>
</header>

<section class="pal-section">
	<div class="pal-container pal-container--wide">
		<?php
		if ( shortcode_exists( 'pal_program_cards' ) ) {
			echo do_shortcode( '[pal_program_cards count="-1" view="grid" columns="3"]' );
		} else {
			echo '<p style="text-align:center;color:var(--pal-gray-400);">' . esc_html__( 'Activate the nashua-pal-core plugin to display programs.', 'nashua-pal-child' ) . '</p>';
		}
		?>
	</div>
</section>

<?php
get_footer();
