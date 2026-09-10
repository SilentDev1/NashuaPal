<?php
/**
 * Archive template for Scholarship Recipients.
 *
 * /scholarships/         — shows all recipients grouped by year (current first).
 * /scholarships/?year=2025 — filters to a single year.
 *
 * @package NashuaPalCore
 * @since   1.29.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

// Determine if a single year is requested.
$filter_year = isset( $_GET['scholar_year'] ) ? absint( $_GET['scholar_year'] ) : 0;

// Gather all available years for the dropdown.
global $wpdb;
$years = $wpdb->get_col( $wpdb->prepare(
	"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
	 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
	 WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'
	   AND pm.meta_value != ''
	 ORDER BY pm.meta_value DESC",
	'_pal_scholar_year',
	NPAL_CPT_Scholar::POST_TYPE
) );
?>

<div class="pal-scholar-archive">

	<!-- Hero Banner -->
	<div class="pal-scholar-archive__hero">
		<div class="pal-scholar-archive__hero-inner">
			<p class="pal-scholar-archive__eyebrow"><?php esc_html_e( 'NASHUA POLICE ATHLETIC LEAGUE', 'nashua-pal-core' ); ?></p>
			<h1 class="pal-scholar-archive__title">
				<?php
				if ( $filter_year ) {
					printf( esc_html__( '%s Scholarship Recipients', 'nashua-pal-core' ), esc_html( $filter_year ) );
				} else {
					esc_html_e( 'Scholarship Recipients', 'nashua-pal-core' );
				}
				?>
			</h1>
			<p class="pal-scholar-archive__subtitle"><?php esc_html_e( 'Celebrating the students who represent the best of our community.', 'nashua-pal-core' ); ?></p>
		</div>
	</div>

	<!-- Year Filter -->
	<div class="pal-scholar-archive__filters">
		<div class="pal-scholar-archive__filters-inner">
			<div class="pal-scholar-archive__filter-group">
				<label for="scholar-archive-year"><?php esc_html_e( 'Filter by Year', 'nashua-pal-core' ); ?></label>
				<?php $scholar_archive_url = get_post_type_archive_link( NPAL_CPT_Scholar::POST_TYPE ) ?: home_url( '/scholarships/' ); ?>
				<select id="scholar-archive-year" onchange="if(this.value){location.href='<?php echo esc_url( $scholar_archive_url ); ?>?scholar_year='+this.value}else{location.href='<?php echo esc_url( $scholar_archive_url ); ?>'}">
					<option value=""><?php esc_html_e( 'All Years', 'nashua-pal-core' ); ?></option>
					<?php foreach ( $years as $y ) : ?>
						<option value="<?php echo esc_attr( $y ); ?>" <?php selected( $filter_year, (int) $y ); ?>><?php echo esc_html( $y ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
	</div>

	<!-- Recipients Grid -->
	<div class="pal-scholar-archive__content">
		<?php
		$query_years = $filter_year ? array( (string) $filter_year ) : $years;

		if ( empty( $query_years ) ) :
		?>
			<p class="pal-scholar-empty"><?php esc_html_e( 'No scholarship recipients found.', 'nashua-pal-core' ); ?></p>
		<?php else :
			// Pre-load scholarship programs keyed by year for "About Scholarship" buttons.
			$all_programs = class_exists( 'NPAL_CPT_Scholarship' ) ? get_posts( array(
				'post_type'      => NPAL_CPT_Scholarship::POST_TYPE,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			) ) : array();
			$programs_by_year = array();
			foreach ( $all_programs as $prog ) {
				$py = get_post_meta( $prog->ID, '_pal_scholarship_year', true );
				if ( $py ) { $programs_by_year[ $py ][] = $prog; }
			}

			foreach ( $query_years as $idx => $year ) :
				$query = new WP_Query( array(
					'post_type'      => NPAL_CPT_Scholar::POST_TYPE,
					'posts_per_page' => 100,
					'post_status'    => 'publish',
					'meta_key'       => '_pal_scholar_order',
					'orderby'        => 'meta_value_num',
					'order'          => 'ASC',
					'meta_query'     => array(
						array( 'key' => '_pal_scholar_year', 'value' => $year ),
					),
				) );

				if ( ! $query->have_posts() ) { continue; }

				$alt_class   = ( $idx % 2 === 1 ) ? ' pal-scholar-year--alt' : '';
				$year_progs  = isset( $programs_by_year[ $year ] ) ? $programs_by_year[ $year ] : array();
		?>
				<div class="pal-scholar-year<?php echo esc_attr( $alt_class ); ?>">
					<div class="pal-scholar-year__inner">
						<div class="pal-scholar-year__badge">
							<div class="pal-scholar-year__badge-icon"><i class="fas fa-shield-alt"></i></div>
							<span class="pal-scholar-year__badge-label">SCHOLARSHIP</span>
							<span class="pal-scholar-year__badge-year"><?php echo esc_html( $year ); ?></span>
							<span class="pal-scholar-year__badge-label">RECIPIENTS</span>
							<div class="pal-scholar-year__badge-dots">
								<span></span><span></span><span></span>
							</div>
							<?php foreach ( $year_progs as $prog ) :
								$prog_about  = get_post_meta( $prog->ID, '_pal_scholarship_about', true );
								$prog_amount = get_post_meta( $prog->ID, '_pal_scholarship_amount', true );
								if ( $prog_about ) : ?>
									<div class="pal-scholar-year__badge-programs">
										<button type="button" class="pal-scholar-year__about-btn"
											data-scholarship-name="<?php echo esc_attr( $prog->post_title ); ?>"
											data-scholarship-about="<?php echo esc_attr( $prog_about ); ?>"
											data-scholarship-amount="<?php echo esc_attr( $prog_amount ); ?>"
											data-scholarship-year="<?php echo esc_attr( $year ); ?>">
											<?php esc_html_e( 'About Awardees', 'nashua-pal-core' ); ?>
										</button>
									</div>
								<?php endif;
							endforeach; ?>
						</div>
						<div class="pal-scholar-year__content">
							<div class="pal-scholar-year__grid">
								<?php while ( $query->have_posts() ) : $query->the_post();
									$pid    = get_the_ID();
									$name   = get_the_title( $pid );
									$amount = get_post_meta( $pid, '_pal_scholar_amount', true );
									$school = get_post_meta( $pid, '_pal_scholar_school', true );
									$alumni = get_post_meta( $pid, '_pal_scholar_alumni', true );
									$bio    = get_post_meta( $pid, '_pal_scholar_bio', true );
									$img    = get_the_post_thumbnail_url( $pid, 'medium' );
								?>
									<div class="pal-scholar-card"
										data-winner-name="<?php echo esc_attr( $name ); ?>"
										data-winner-bio="<?php echo esc_attr( $bio ); ?>"
										data-winner-amount="<?php echo esc_attr( $amount ); ?>"
										data-winner-school="<?php echo esc_attr( $school ); ?>"
										data-winner-alumni="<?php echo esc_attr( $alumni ); ?>"
										<?php if ( $img ) : ?>data-winner-photo="<?php echo esc_attr( esc_url( $img ) ); ?>"<?php endif; ?>>
										<div class="pal-scholar-card__photo">
											<?php if ( $img ) : ?>
												<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" />
											<?php else : ?>
												<span class="pal-scholar-card__placeholder"><i class="fas fa-user-graduate"></i></span>
											<?php endif; ?>
										</div>
										<h3 class="pal-scholar-card__name"><?php echo esc_html( $name ); ?></h3>
										<?php if ( $alumni ) : ?>
											<p class="pal-scholar-card__alumni"><?php echo esc_html( $alumni ); ?></p>
										<?php endif; ?>
										<?php if ( $amount ) : ?>
											<span class="pal-scholar-card__amount"><?php echo esc_html( $amount ); ?></span>
										<?php endif; ?>
										<?php if ( $school ) : ?>
											<p class="pal-scholar-card__school"><?php echo esc_html( $school ); ?></p>
										<?php endif; ?>
									</div>
								<?php endwhile; wp_reset_postdata(); ?>
							</div>
						</div>
					</div>
				</div>
		<?php
			endforeach;
		endif;
		?>
	</div>

	<!-- Winner Bio / About Scholarship Modal -->
	<div id="pal-scholarship-modal" class="pal-scholarship-modal" style="display:none;">
		<div class="pal-scholarship-modal__overlay"></div>
		<div class="pal-scholarship-modal__dialog">
			<button type="button" class="pal-scholarship-modal__close">&times;</button>
			<div class="pal-scholarship-modal__photo-wrap"></div>
			<div class="pal-scholarship-modal__header">
				<i class="fas fa-graduation-cap pal-scholarship-modal__icon"></i>
				<h3 class="pal-scholarship-modal__title"></h3>
			</div>
			<div class="pal-scholarship-modal__meta"></div>
			<div class="pal-scholarship-modal__body"></div>
		</div>
	</div>

</div>

<?php get_footer(); ?>
