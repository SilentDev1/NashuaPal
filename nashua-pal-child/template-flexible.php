<?php
/**
 * Template Name: PAL Flexible Page
 * Template Post Type: page
 *
 * User-selectable page template for any custom page that doesn't have a
 * purpose-built Elementor layout. Renders a navy branded hero with the
 * page title (and the page excerpt as the lede if set), followed by the
 * standard content area. Works without Elementor — paste/write content
 * in the standard editor and you get a branded page out of the box.
 *
 * To use: Page Attributes → Template → "PAL Flexible Page".
 *
 * @package NashuaPalChild
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article id="page-<?php the_ID(); ?>" <?php post_class( 'pal-flexible-page' ); ?>>

		<header class="pal-page-hero">
			<div class="pal-container">
				<?php
				/**
				 * Optional eyebrow above the title. Set via Custom Field
				 * `_pal_page_eyebrow` on any page using this template.
				 */
				$pal_eyebrow = get_post_meta( get_the_ID(), '_pal_page_eyebrow', true );
				if ( $pal_eyebrow ) {
					echo '<p class="pal-page-hero__eyebrow">' . esc_html( $pal_eyebrow ) . '</p>';
				}
				?>
				<h1><?php the_title(); ?></h1>

				<?php
				// Use the page excerpt as the hero lede if the user filled it in.
				$pal_excerpt = has_excerpt() ? wp_strip_all_tags( get_the_excerpt() ) : '';
				if ( $pal_excerpt ) {
					echo '<p class="pal-page-hero__lede">' . esc_html( $pal_excerpt ) . '</p>';
				}
				?>
			</div>
		</header>

		<?php
		// If the page has a featured image, display it as a full-width banner
		// below the hero — gives the user a hero-photo option without Elementor.
		if ( has_post_thumbnail() ) :
			?>
			<div class="pal-flexible-page__featured">
				<?php the_post_thumbnail( 'full', array( 'loading' => 'lazy' ) ); ?>
			</div>
			<?php
		endif;
		?>

		<section class="pal-section">
			<div class="pal-container pal-content">
				<?php the_content(); ?>

				<?php
				wp_link_pages( array(
					'before'      => '<nav class="pal-page-pagination">',
					'after'       => '</nav>',
					'link_before' => '<span class="pal-page-pagination__link">',
					'link_after'  => '</span>',
				) );
				?>
			</div>
		</section>

	</article>
	<?php
endwhile;

get_footer();
