<?php
/**
 * Archive template for Tax 990 Documents.
 *
 * @package NashuaPalCore
 * @since   1.40.1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

// Collect all published documents for client-side filtering.
$all_docs = new WP_Query( array(
	'post_type'      => NPAL_CPT_Tax990::POST_TYPE,
	'posts_per_page' => 200,
	'post_status'    => 'publish',
	'meta_key'       => '_pal_tax990_year',
	'orderby'        => 'meta_value_num',
	'order'          => 'DESC',
) );

$years = array();
if ( $all_docs->have_posts() ) {
	while ( $all_docs->have_posts() ) {
		$all_docs->the_post();
		$yr = get_post_meta( get_the_ID(), '_pal_tax990_year', true );
		if ( $yr && ! in_array( $yr, $years, true ) ) {
			$years[] = $yr;
		}
	}
	$all_docs->rewind_posts();
}
rsort( $years );
?>

<div class="pal-tax990-archive">

	<!-- Hero -->
	<div class="pal-tax990-archive__hero">
		<div class="pal-tax990-archive__hero-inner">
			<h1 class="pal-tax990-archive__title"><?php esc_html_e( 'Tax 990 Documents', 'nashua-pal-core' ); ?></h1>
			<p class="pal-tax990-archive__subtitle"><?php esc_html_e( 'Browse our annual IRS Form 990 filings demonstrating Nashua PAL\'s commitment to financial transparency.', 'nashua-pal-core' ); ?></p>
		</div>
	</div>

	<!-- Filters -->
	<div class="pal-tax990-archive__filters">
		<div class="pal-tax990-archive__filters-inner">
			<?php if ( count( $years ) > 1 ) : ?>
			<div class="pal-tax990-archive__filter-group">
				<label for="pal-tax990-filter-year"><?php esc_html_e( 'Year', 'nashua-pal-core' ); ?></label>
				<select id="pal-tax990-filter-year">
					<option value=""><?php esc_html_e( 'All Years', 'nashua-pal-core' ); ?></option>
					<?php foreach ( $years as $yr ) : ?>
						<option value="<?php echo esc_attr( $yr ); ?>"><?php echo esc_html( $yr ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>
			<div class="pal-tax990-archive__filter-group">
				<label for="pal-tax990-filter-search"><?php esc_html_e( 'Search', 'nashua-pal-core' ); ?></label>
				<input type="text" id="pal-tax990-filter-search" placeholder="<?php esc_attr_e( 'Search documents...', 'nashua-pal-core' ); ?>" />
			</div>
		</div>
	</div>

	<!-- Grid -->
	<div class="pal-tax990-archive__grid-wrap">
		<div class="pal-tax990-archive__grid">
			<?php if ( $all_docs->have_posts() ) : ?>
				<?php while ( $all_docs->have_posts() ) : $all_docs->the_post();
					$pid     = get_the_ID();
					$title   = get_the_title();
					$year    = get_post_meta( $pid, '_pal_tax990_year', true );
					$desc    = get_post_meta( $pid, '_pal_tax990_description', true );
					$pdf_id  = get_post_meta( $pid, '_pal_tax990_pdf_id', true );
					$image   = get_the_post_thumbnail_url( $pid, 'large' );
					if ( ! $image && $pdf_id ) {
						$image = wp_get_attachment_image_url( $pdf_id, 'full' );
					}
					$pdf_url = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
				?>
					<div class="pal-tax990-card" data-tax990-year="<?php echo esc_attr( $year ); ?>" data-tax990-title="<?php echo esc_attr( strtolower( $title ) ); ?>">
						<div class="pal-tax990-card__media">
							<?php if ( $image ) : ?>
								<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="pal-tax990-card__cover" />
							<?php else : ?>
								<div class="pal-tax990-card__icon">
									<i class="fas fa-file-pdf"></i>
								</div>
							<?php endif; ?>
							<?php if ( $year ) : ?>
								<span class="pal-tax990-card__year-badge"><?php echo esc_html( $year ); ?></span>
							<?php endif; ?>
						</div>
						<div class="pal-tax990-card__body">
							<h3 class="pal-tax990-card__title"><?php echo esc_html( $title ); ?></h3>
							<?php if ( $desc ) : ?>
								<p class="pal-tax990-card__desc"><?php echo esc_html( wp_trim_words( $desc, 20, '&hellip;' ) ); ?></p>
							<?php endif; ?>
							<div class="pal-tax990-card__actions">
								<?php if ( $pdf_url ) : ?>
									<button type="button" class="pal-tax990-card__btn pal-tax990-card__btn--view" data-tax990-pdf="<?php echo esc_url( $pdf_url ); ?>" data-tax990-title="<?php echo esc_attr( $title ); ?>">
										<i class="fas fa-eye"></i> <?php esc_html_e( 'View Document', 'nashua-pal-core' ); ?>
									</button>
									<a href="<?php echo esc_url( $pdf_url ); ?>" class="pal-tax990-card__btn pal-tax990-card__btn--download" download>
										<i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'nashua-pal-core' ); ?>
									</a>
								<?php else : ?>
									<a href="<?php the_permalink(); ?>" class="pal-tax990-card__btn pal-tax990-card__btn--view">
										<i class="fas fa-eye"></i> <?php esc_html_e( 'View Document', 'nashua-pal-core' ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endwhile; ?>
			<?php endif; ?>
		</div>

		<p class="pal-tax990-archive__no-results" style="display:none;">
			<?php esc_html_e( 'No documents match your filters.', 'nashua-pal-core' ); ?>
		</p>
	</div>

</div>

<!-- PDF viewer modal -->
<div class="pal-tax990-modal" id="pal-tax990-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Document Viewer', 'nashua-pal-core' ); ?>">
	<div class="pal-tax990-modal__overlay"></div>
	<div class="pal-tax990-modal__container">
		<div class="pal-tax990-modal__header">
			<h3 class="pal-tax990-modal__title"></h3>
			<button type="button" class="pal-tax990-modal__close" aria-label="<?php esc_attr_e( 'Close', 'nashua-pal-core' ); ?>">&times;</button>
		</div>
		<div class="pal-tax990-modal__body">
			<iframe class="pal-tax990-modal__frame" src="about:blank"></iframe>
		</div>
	</div>
</div>

<?php
wp_reset_postdata();
get_footer();
