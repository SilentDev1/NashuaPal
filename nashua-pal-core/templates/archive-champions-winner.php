<?php
/**
 * Archive template for Champions Winners.
 *
 * /champions-winners/ — shows all winners with year/category/search filters.
 *
 * @package NashuaPalCore
 * @since   1.15.0
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
	'_pal_champion_year',
	NPAL_CPT_Champions_Winner::POST_TYPE
) );

// Get categories from taxonomy.
$cat_terms = get_terms( array(
	'taxonomy'   => NPAL_CPT_Champions_Winner::TAXONOMY,
	'hide_empty' => true,
) );
$categories = array();
if ( ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ) {
	foreach ( $cat_terms as $ct ) {
		$categories[ $ct->slug ] = $ct->name;
	}
}
?>

<div class="pal-champ-archive">

	<!-- Hero Banner -->
	<div class="pal-champ-archive__hero">
		<div class="pal-champ-archive__hero-inner">
			<p class="pal-champ-archive__eyebrow"><?php esc_html_e( 'NASHUA POLICE ATHLETIC LEAGUE', 'nashua-pal-core' ); ?></p>
			<h1 class="pal-champ-archive__title"><?php esc_html_e( 'Night of Champions', 'nashua-pal-core' ); ?></h1>
			<p class="pal-champ-archive__subtitle"><?php esc_html_e( 'Honoring the individuals who make our community stronger through sports, mentorship, and service.', 'nashua-pal-core' ); ?></p>
		</div>
	</div>

	<!-- Filters -->
	<div class="pal-champ-archive__filters">
		<div class="pal-champ-archive__filters-inner">
			<div class="pal-champ-archive__filter-group">
				<label for="champ-archive-year"><?php esc_html_e( 'Year', 'nashua-pal-core' ); ?></label>
				<select id="champ-archive-year" data-champ-archive-filter="year">
					<option value=""><?php esc_html_e( 'All Years', 'nashua-pal-core' ); ?></option>
					<?php foreach ( $years as $y ) : ?>
						<option value="<?php echo esc_attr( $y ); ?>"><?php echo esc_html( $y ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="pal-champ-archive__filter-group">
				<label for="champ-archive-category"><?php esc_html_e( 'Category', 'nashua-pal-core' ); ?></label>
				<select id="champ-archive-category" data-champ-archive-filter="category">
					<option value=""><?php esc_html_e( 'All Categories', 'nashua-pal-core' ); ?></option>
					<?php foreach ( $categories as $slug => $label ) : ?>
						<option value="<?php echo esc_attr( $slug ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<div class="pal-champ-archive__filter-group pal-champ-archive__filter-group--search">
				<label for="champ-archive-search"><?php esc_html_e( 'Search', 'nashua-pal-core' ); ?></label>
				<input type="text" id="champ-archive-search" data-champ-archive-filter="search"
					placeholder="<?php esc_attr_e( 'Search by name...', 'nashua-pal-core' ); ?>" />
			</div>
		</div>
	</div>

	<!-- Winners Grid -->
	<div class="pal-champ-archive__content">
		<div class="pal-champ-archive__grid-wrap">
			<?php
			$all_winners = new WP_Query( array(
				'post_type'      => NPAL_CPT_Champions_Winner::POST_TYPE,
				'posts_per_page' => 500,
				'post_status'    => 'publish',
				'meta_key'       => '_pal_champion_order',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			) );

			if ( $all_winners->have_posts() ) :
			?>
				<div class="pal-champ-grid pal-champ-grid--cols-3" id="champ-archive-grid">
					<?php while ( $all_winners->have_posts() ) : $all_winners->the_post();
						$pid         = get_the_ID();
						$name        = get_post_meta( $pid, '_pal_champion_name', true ) ?: get_the_title();
						$award_title = get_post_meta( $pid, '_pal_champion_award_title', true );
						$year_val    = get_post_meta( $pid, '_pal_champion_year', true );
						$cat_term    = NPAL_CPT_Champions_Winner::get_award_category( $pid );
						$cat_slug    = $cat_term ? $cat_term->slug : '';
						$cat_label   = $cat_term ? $cat_term->name : '';
						$badge_style = NPAL_CPT_Champions_Winner::get_badge_style( $cat_term );
						$bio         = get_post_meta( $pid, '_pal_champion_bio', true ) ?: get_the_excerpt();
						$video       = get_post_meta( $pid, '_pal_champion_video_url', true );
						$img         = get_the_post_thumbnail_url( $pid, 'large' );
						$img_style   = $img ? 'background-image:url(' . esc_url( $img ) . ')' : '';
						$is_legacy   = empty( $img ) && empty( $bio );
						$card_class  = 'pal-champ-card' . ( $is_legacy ? ' pal-champ-card--legacy' : '' );
						$initials    = '';
						if ( $is_legacy && $name ) {
							$parts    = preg_split( '/[\s]+/', trim( $name ) );
							$initials = strtoupper( mb_substr( $parts[0], 0, 1 ) );
							if ( count( $parts ) > 1 ) {
								$initials .= strtoupper( mb_substr( end( $parts ), 0, 1 ) );
							}
						}
					?>
						<div class="<?php echo esc_attr( $card_class ); ?>"
							data-champ-category="<?php echo esc_attr( $cat_slug ); ?>"
							data-champ-year="<?php echo esc_attr( $year_val ); ?>"
							data-champ-name="<?php echo esc_attr( strtolower( $name ) ); ?>">
							<?php if ( $is_legacy ) : ?>
								<div class="pal-champ-card__link">
									<div class="pal-champ-card__media">
										<?php if ( $initials ) : ?>
											<span class="pal-champ-card__initials"><?php echo esc_html( $initials ); ?></span>
										<?php endif; ?>
										<?php if ( $cat_label ) : ?>
											<span class="pal-champ-card__badge pal-champ-card__badge--<?php echo esc_attr( $cat_slug ); ?>" <?php if ( $badge_style ) { echo 'style="' . esc_attr( $badge_style ) . '"'; } ?>><?php echo esc_html( $cat_label ); ?></span>
										<?php endif; ?>
									</div>
									<div class="pal-champ-card__body">
										<h3 class="pal-champ-card__name"><?php echo esc_html( $name ); ?></h3>
										<?php if ( $award_title ) : ?>
											<p class="pal-champ-card__award"><?php echo esc_html( $award_title ); ?></p>
										<?php endif; ?>
										<?php if ( $year_val ) : ?>
											<p class="pal-champ-card__year"><?php echo esc_html( $year_val ); ?></p>
										<?php endif; ?>
									</div>
								</div>
							<?php else : ?>
								<a href="<?php the_permalink(); ?>" class="pal-champ-card__link">
									<div class="pal-champ-card__media" style="<?php echo esc_attr( $img_style ); ?>">
										<?php if ( $cat_label ) : ?>
											<span class="pal-champ-card__badge pal-champ-card__badge--<?php echo esc_attr( $cat_slug ); ?>" <?php if ( $badge_style ) { echo 'style="' . esc_attr( $badge_style ) . '"'; } ?>><?php echo esc_html( $cat_label ); ?></span>
										<?php endif; ?>
										<?php if ( $video ) : ?>
											<span class="pal-champ-card__play" aria-hidden="true"><i class="fas fa-play"></i></span>
										<?php endif; ?>
									</div>
									<div class="pal-champ-card__body">
										<h3 class="pal-champ-card__name"><?php echo esc_html( $name ); ?></h3>
										<?php if ( $award_title ) : ?>
											<p class="pal-champ-card__award"><?php echo esc_html( $award_title ); ?></p>
										<?php endif; ?>
										<?php if ( $year_val ) : ?>
											<p class="pal-champ-card__year"><?php echo esc_html( $year_val ); ?></p>
										<?php endif; ?>
										<?php if ( $bio ) : ?>
											<p class="pal-champ-card__bio"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $bio ), 20, '&hellip;' ) ); ?></p>
										<?php endif; ?>
										<span class="pal-champ-card__cta"><?php esc_html_e( 'Read Story', 'nashua-pal-core' ); ?> <i class="fas fa-arrow-right"></i></span>
									</div>
								</a>
							<?php endif; ?>
						</div>
					<?php endwhile; ?>
				</div>
				<p class="pal-champ-archive__no-results" id="champ-archive-empty" style="display:none;">
					<?php esc_html_e( 'No champions match your filters.', 'nashua-pal-core' ); ?>
				</p>
			<?php else : ?>
				<p class="pal-champ-empty"><?php esc_html_e( 'No champions have been added yet.', 'nashua-pal-core' ); ?></p>
			<?php endif; wp_reset_postdata(); ?>
		</div>
	</div>

</div>

<?php get_footer(); ?>
