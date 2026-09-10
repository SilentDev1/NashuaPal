<?php
/**
 * Archive template for Impact Reports.
 *
 * @package NashuaPalCore
 * @since   1.34.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

// Collect all published reports for client-side filtering.
$all_reports = new WP_Query( array(
	'post_type'      => NPAL_CPT_Impact_Report::POST_TYPE,
	'posts_per_page' => 200,
	'post_status'    => 'publish',
	'meta_key'       => '_pal_report_year',
	'orderby'        => 'meta_value_num',
	'order'          => 'DESC',
) );

$years = array();
if ( $all_reports->have_posts() ) {
	while ( $all_reports->have_posts() ) {
		$all_reports->the_post();
		$yr = get_post_meta( get_the_ID(), '_pal_report_year', true );
		if ( $yr && ! in_array( $yr, $years, true ) ) {
			$years[] = $yr;
		}
	}
	$all_reports->rewind_posts();
}
rsort( $years );
?>

<div class="pal-report-archive">

	<!-- Hero -->
	<div class="pal-report-archive__hero">
		<div class="pal-report-archive__hero-inner">
			<h1 class="pal-report-archive__title"><?php esc_html_e( 'Impact Reports', 'nashua-pal-core' ); ?></h1>
			<p class="pal-report-archive__subtitle"><?php esc_html_e( 'Browse our annual impact reports showcasing the difference Nashua PAL makes in our community.', 'nashua-pal-core' ); ?></p>
		</div>
	</div>

	<!-- Filters -->
	<div class="pal-report-archive__filters">
		<div class="pal-report-archive__filters-inner">
			<?php if ( count( $years ) > 1 ) : ?>
			<div class="pal-report-archive__filter-group">
				<label for="pal-report-filter-year"><?php esc_html_e( 'Year', 'nashua-pal-core' ); ?></label>
				<select id="pal-report-filter-year">
					<option value=""><?php esc_html_e( 'All Years', 'nashua-pal-core' ); ?></option>
					<?php foreach ( $years as $yr ) : ?>
						<option value="<?php echo esc_attr( $yr ); ?>"><?php echo esc_html( $yr ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>
			<div class="pal-report-archive__filter-group">
				<label for="pal-report-filter-search"><?php esc_html_e( 'Search', 'nashua-pal-core' ); ?></label>
				<input type="text" id="pal-report-filter-search" placeholder="<?php esc_attr_e( 'Search reports...', 'nashua-pal-core' ); ?>" />
			</div>
		</div>
	</div>

	<!-- Grid -->
	<div class="pal-report-archive__grid-wrap">
		<div class="pal-report-archive__grid">
			<?php if ( $all_reports->have_posts() ) : ?>
				<?php while ( $all_reports->have_posts() ) : $all_reports->the_post();
					$pid     = get_the_ID();
					$title   = get_the_title();
					$year    = get_post_meta( $pid, '_pal_report_year', true );
					$desc    = get_post_meta( $pid, '_pal_report_description', true );
					$image   = get_the_post_thumbnail_url( $pid, 'large' );
					$pdf_id  = get_post_meta( $pid, '_pal_report_pdf_id', true );
					$pdf_url = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
				?>
					<div class="pal-report-card" data-report-year="<?php echo esc_attr( $year ); ?>" data-report-title="<?php echo esc_attr( strtolower( $title ) ); ?>">
						<div class="pal-report-card__media">
							<?php if ( $image ) : ?>
								<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="pal-report-card__cover" />
							<?php else : ?>
								<div class="pal-report-card__icon">
									<i class="fas fa-file-pdf"></i>
								</div>
							<?php endif; ?>
							<?php if ( $year ) : ?>
								<span class="pal-report-card__year-badge"><?php echo esc_html( $year ); ?></span>
							<?php endif; ?>
						</div>
						<div class="pal-report-card__body">
							<h3 class="pal-report-card__title"><?php echo esc_html( $title ); ?></h3>
							<?php if ( $desc ) : ?>
								<p class="pal-report-card__desc"><?php echo esc_html( wp_trim_words( $desc, 20, '&hellip;' ) ); ?></p>
							<?php endif; ?>
							<div class="pal-report-card__actions">
								<a href="<?php the_permalink(); ?>" class="pal-report-card__btn pal-report-card__btn--view">
									<i class="fas fa-eye"></i> <?php esc_html_e( 'View Report', 'nashua-pal-core' ); ?>
								</a>
								<?php if ( $pdf_url ) : ?>
									<a href="<?php echo esc_url( $pdf_url ); ?>" class="pal-report-card__btn pal-report-card__btn--download" download>
										<i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'nashua-pal-core' ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endwhile; ?>
			<?php endif; ?>
		</div>

		<p class="pal-report-archive__no-results" style="display:none;">
			<?php esc_html_e( 'No reports match your filters.', 'nashua-pal-core' ); ?>
		</p>
	</div>

</div>

<?php
wp_reset_postdata();
get_footer();
