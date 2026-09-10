<?php
/**
 * Archive template for Sponsorship Packages.
 *
 * /sponsorship-packages/ — shows all packages with year/category/search filters.
 *
 * @package NashuaPalCore
 * @since   1.30.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

// Gather all available years.
global $wpdb;
$years = $wpdb->get_col( $wpdb->prepare(
	"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
	 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	 WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'
	   AND pm.meta_value != ''
	 ORDER BY pm.meta_value DESC",
	'_pal_pkg_year',
	NPAL_CPT_Sponsor_Package::POST_TYPE
) );

// Get categories from taxonomy.
$cat_terms  = get_terms( array(
	'taxonomy'   => NPAL_CPT_Sponsor_Package::TAXONOMY,
	'hide_empty' => true,
) );
$categories = array();
if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
	foreach ( $cat_terms as $ct ) {
		$categories[ $ct->slug ] = $ct->name;
	}
}
?>

<div class="pal-pkg-archive">

	<!-- Hero Banner -->
	<div class="pal-pkg-archive__hero">
		<div class="pal-pkg-archive__hero-inner">
			<p class="pal-pkg-archive__eyebrow"><?php esc_html_e( 'NASHUA POLICE ATHLETIC LEAGUE', 'nashua-pal-core' ); ?></p>
			<h1 class="pal-pkg-archive__title"><?php esc_html_e( 'Sponsorship Packages', 'nashua-pal-core' ); ?></h1>
			<p class="pal-pkg-archive__subtitle"><?php esc_html_e( 'Partner with Nashua PAL and make a lasting impact on our community\'s youth.', 'nashua-pal-core' ); ?></p>
		</div>
	</div>

	<!-- Filters -->
	<div class="pal-pkg-archive__filters">
		<div class="pal-pkg-archive__filters-inner">
			<div class="pal-pkg-archive__filter-group">
				<label for="pkg-archive-year"><?php esc_html_e( 'Year', 'nashua-pal-core' ); ?></label>
				<select id="pkg-archive-year" data-pkg-archive-filter="year">
					<option value=""><?php esc_html_e( 'All Years', 'nashua-pal-core' ); ?></option>
					<?php foreach ( $years as $y ) : ?>
						<option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="pal-pkg-archive__filter-group">
				<label for="pkg-archive-category"><?php esc_html_e( 'Category', 'nashua-pal-core' ); ?></label>
				<select id="pkg-archive-category" data-pkg-archive-filter="category">
					<option value=""><?php esc_html_e( 'All Categories', 'nashua-pal-core' ); ?></option>
					<?php foreach ( $categories as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="pal-pkg-archive__filter-group pal-pkg-archive__filter-group--search">
				<label for="pkg-archive-search"><?php esc_html_e( 'Search', 'nashua-pal-core' ); ?></label>
				<input type="text" id="pkg-archive-search" data-pkg-archive-filter="search"
					placeholder="<?php esc_attr_e( 'Search packages...', 'nashua-pal-core' ); ?>" />
			</div>
		</div>
	</div>

	<!-- Packages Grid -->
	<div class="pal-pkg-archive__content">
		<div class="pal-pkg-archive__grid-wrap">
			<?php
			$all_packages = new WP_Query( array(
				'post_type'      => NPAL_CPT_Sponsor_Package::POST_TYPE,
				'posts_per_page' => 200,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			) );

			if ( $all_packages->have_posts() ) :
			?>
				<div class="pal-pkg-grid pal-pkg-grid--cols-3" id="pkg-archive-grid">
					<?php while ( $all_packages->have_posts() ) : $all_packages->the_post();
						$pid      = get_the_ID();
						$name     = get_the_title();
						$year_val = get_post_meta( $pid, '_pal_pkg_year', true );
						$cat_term = NPAL_CPT_Sponsor_Package::get_package_category( $pid );
						$cat_slug = $cat_term ? $cat_term->slug : '';
						$cat_lbl  = $cat_term ? $cat_term->name : '';
						$badge_st = NPAL_CPT_Sponsor_Package::get_badge_style( $cat_term );
						$img      = get_the_post_thumbnail_url( $pid, 'medium_large' );
						$pdf_url  = NPAL_Sponsor_Package_Meta::get_pdf_url( $pid );
						$gallery  = NPAL_Sponsor_Package_Meta::get_gallery_urls( $pid );
						$gallery_json = ! empty( $gallery ) ? wp_json_encode( $gallery ) : '';
					?>
						<div class="pal-pkg-card"
							data-pkg-category="<?php echo esc_attr( $cat_slug ); ?>"
							data-pkg-year="<?php echo esc_attr( $year_val ); ?>"
							data-pkg-name="<?php echo esc_attr( strtolower( $name ) ); ?>">
							<div class="pal-pkg-card__inner">
								<div class="pal-pkg-card__media">
									<?php if ( $img ) : ?>
										<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" />
									<?php else : ?>
										<span class="pal-pkg-card__placeholder"><i class="fas fa-handshake"></i></span>
									<?php endif; ?>
									<?php if ( $cat_lbl ) : ?>
										<span class="pal-pkg-card__badge" <?php if ( $badge_st ) { echo 'style="' . esc_attr( $badge_st ) . '"'; } ?>><?php echo esc_html( $cat_lbl ); ?></span>
									<?php endif; ?>
								</div>
								<div class="pal-pkg-card__body">
									<h3 class="pal-pkg-card__name"><?php echo esc_html( $name ); ?></h3>
									<?php if ( $pdf_url ) : ?>
										<a href="<?php echo esc_url( $pdf_url ); ?>" class="pal-pkg-card__cta" target="_blank" rel="noopener"><?php esc_html_e( 'View PDF', 'nashua-pal-core' ); ?> <i class="fas fa-file-pdf"></i></a>
									<?php elseif ( $gallery_json ) : ?>
										<button type="button" class="pal-pkg-card__cta" data-pkg-gallery='<?php echo esc_attr( $gallery_json ); ?>'><?php esc_html_e( 'View Details', 'nashua-pal-core' ); ?> <i class="fas fa-search-plus"></i></button>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
				</div>
				<p class="pal-pkg-archive__no-results" id="pkg-archive-empty" style="display:none;">
					<?php esc_html_e( 'No packages match your filters.', 'nashua-pal-core' ); ?>
				</p>
			<?php else : ?>
				<p class="pal-pkg-empty"><?php esc_html_e( 'No sponsorship packages have been added yet.', 'nashua-pal-core' ); ?></p>
			<?php endif; wp_reset_postdata(); ?>
		</div>
	</div>

</div>

<?php get_footer(); ?>
