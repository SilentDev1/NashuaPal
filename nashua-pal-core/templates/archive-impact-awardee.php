<?php
/**
 * Archive template for Impact Awardees.
 *
 * /impact-awardees/ — shows all awardees with year/category/search filters.
 *
 * @package NashuaPalCore
 * @since   1.31.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

// Gather all available years for the dropdown.
global $wpdb;
$years = $wpdb->get_col( $wpdb->prepare(
	"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
	 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	 WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'
	 ORDER BY pm.meta_value DESC",
	'_pal_awardee_year',
	NPAL_CPT_Impact_Awardee::POST_TYPE
) );

// Get categories from taxonomy.
$cat_terms = get_terms( array(
	'taxonomy'   => NPAL_CPT_Impact_Awardee::TAXONOMY,
	'hide_empty' => true,
) );
$categories = array();
if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
	foreach ( $cat_terms as $ct ) {
		$categories[ $ct->slug ] = $ct->name;
	}
}
?>

<div class="pal-awardee-archive">

	<!-- Hero Banner -->
	<div class="pal-awardee-archive__hero">
		<div class="pal-awardee-archive__hero-inner">
			<p class="pal-awardee-archive__eyebrow"><?php esc_html_e( 'NASHUA POLICE ATHLETIC LEAGUE', 'nashua-pal-core' ); ?></p>
			<h1 class="pal-awardee-archive__title"><?php esc_html_e( 'National Recognition', 'nashua-pal-core' ); ?></h1>
			<p class="pal-awardee-archive__subtitle"><?php esc_html_e( 'Celebrating those who have earned impact recognition for their dedication, leadership, and service.', 'nashua-pal-core' ); ?></p>
		</div>
	</div>

	<!-- Filters -->
	<div class="pal-awardee-archive__filters">
		<div class="pal-awardee-archive__filters-inner">
			<div class="pal-awardee-archive__filter-group">
				<label for="awardee-archive-year"><?php esc_html_e( 'Year', 'nashua-pal-core' ); ?></label>
				<select id="awardee-archive-year" data-awardee-archive-filter="year">
					<option value=""><?php esc_html_e( 'All Years', 'nashua-pal-core' ); ?></option>
					<?php foreach ( $years as $y ) : ?>
						<option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="pal-awardee-archive__filter-group">
				<label for="awardee-archive-category"><?php esc_html_e( 'Category', 'nashua-pal-core' ); ?></label>
				<select id="awardee-archive-category" data-awardee-archive-filter="category">
					<option value=""><?php esc_html_e( 'All Categories', 'nashua-pal-core' ); ?></option>
					<?php foreach ( $categories as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="pal-awardee-archive__filter-group pal-awardee-archive__filter-group--search">
				<label for="awardee-archive-search"><?php esc_html_e( 'Search', 'nashua-pal-core' ); ?></label>
				<input type="text" id="awardee-archive-search" data-awardee-archive-filter="search"
					placeholder="<?php esc_attr_e( 'Search by name...', 'nashua-pal-core' ); ?>" />
			</div>
		</div>
	</div>

	<!-- Awardees Grid -->
	<div class="pal-awardee-archive__content">
		<div class="pal-awardee-archive__grid-wrap">
			<?php
			$all_awardees = new WP_Query( array(
				'post_type'      => NPAL_CPT_Impact_Awardee::POST_TYPE,
				'posts_per_page' => 200,
				'post_status'    => 'publish',
				'meta_key'       => '_pal_awardee_order',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			) );

			if ( $all_awardees->have_posts() ) :
			?>
				<div class="pal-awardee-grid pal-awardee-grid--cols-3" id="awardee-archive-grid">
					<?php while ( $all_awardees->have_posts() ) : $all_awardees->the_post();
						$pid         = get_the_ID();
						$name        = get_post_meta( $pid, '_pal_awardee_name', true ) ?: get_the_title();
						$award_title = get_post_meta( $pid, '_pal_awardee_award_title', true );
						$year_val    = get_post_meta( $pid, '_pal_awardee_year', true );
						$cat_term    = NPAL_CPT_Impact_Awardee::get_award_category( $pid );
						$cat_slug    = $cat_term ? $cat_term->slug : '';
						$cat_label   = $cat_term ? $cat_term->name : '';
						$badge_style = NPAL_CPT_Impact_Awardee::get_badge_style( $cat_term );
						$bio         = get_post_meta( $pid, '_pal_awardee_bio', true ) ?: get_the_excerpt();
						$donate_url  = get_post_meta( $pid, '_pal_awardee_donate_url', true );
						$img         = get_the_post_thumbnail_url( $pid, 'large' );
						$img_style   = $img ? 'background-image:url(' . esc_url( $img ) . ')' : '';
					?>
						<div class="pal-awardee-card"
							data-awardee-category="<?php echo esc_attr( $cat_slug ); ?>"
							data-awardee-year="<?php echo esc_attr( $year_val ); ?>"
							data-awardee-name="<?php echo esc_attr( strtolower( $name ) ); ?>">
							<div class="pal-awardee-card__media" style="<?php echo esc_attr( $img_style ); ?>">
								<?php if ( $cat_label ) : ?>
									<span class="pal-awardee-card__badge" <?php if ( $badge_style ) { echo 'style="' . esc_attr( $badge_style ) . '"'; } ?>><?php echo esc_html( $cat_label ); ?></span>
								<?php endif; ?>
							</div>
							<div class="pal-awardee-card__body">
								<h3 class="pal-awardee-card__name"><?php echo esc_html( $name ); ?></h3>
								<?php if ( $award_title ) : ?>
									<p class="pal-awardee-card__award"><?php echo esc_html( $award_title ); ?></p>
								<?php endif; ?>
								<?php if ( $year_val ) : ?>
									<p class="pal-awardee-card__year"><?php echo esc_html( $year_val ); ?></p>
								<?php endif; ?>
								<?php if ( $bio ) : ?>
									<p class="pal-awardee-card__bio"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $bio ), 20, '&hellip;' ) ); ?></p>
								<?php endif; ?>
								<div class="pal-awardee-card__actions">
									<a href="<?php the_permalink(); ?>" class="pal-awardee-card__btn pal-awardee-card__btn--meet">
										<?php esc_html_e( 'Learn More', 'nashua-pal-core' ); ?> <i class="fas fa-arrow-right"></i>
									</a>
									<?php if ( $donate_url ) : ?>
										<a href="<?php echo esc_url( $donate_url ); ?>" class="pal-awardee-card__btn pal-awardee-card__btn--donate" target="_blank" rel="noopener">
											<i class="fas fa-heart"></i>
										</a>
									<?php endif; ?>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
				</div>
				<p class="pal-awardee-archive__no-results" id="awardee-archive-empty" style="display:none;">
					<?php esc_html_e( 'No awardees match your filters.', 'nashua-pal-core' ); ?>
				</p>
			<?php else : ?>
				<p class="pal-awardee-empty"><?php esc_html_e( 'No awardees have been added yet.', 'nashua-pal-core' ); ?></p>
			<?php endif; wp_reset_postdata(); ?>
		</div>
	</div>

</div>

<?php get_footer(); ?>
