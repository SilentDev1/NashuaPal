<?php
/**
 * Single Tax 990 Document template.
 *
 * Layout:
 *   1. Hero (cover image + title + year + action buttons)
 *   2. PDF Viewer (embedded iframe)
 *   3. Related Documents
 *
 * @package NashuaPalCore
 * @since   1.40.1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

while ( have_posts() ) : the_post();

	$pid         = get_the_ID();
	$title       = get_the_title();
	$year        = get_post_meta( $pid, '_pal_tax990_year', true );
	$pdf_id      = get_post_meta( $pid, '_pal_tax990_pdf_id', true );
	$description = get_post_meta( $pid, '_pal_tax990_description', true );
	$image       = get_the_post_thumbnail_url( $pid, 'full' );
	$pdf_url     = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
	$archive_url = get_post_type_archive_link( NPAL_CPT_Tax990::POST_TYPE );
?>

<div class="pal-tax990-single">

	<!-- ============================================================ -->
	<!-- Hero Section                                                  -->
	<!-- ============================================================ -->
	<div class="pal-tax990-single__hero">
		<div class="pal-tax990-single__hero-inner">
			<div class="pal-tax990-single__hero-image">
				<?php if ( $image ) : ?>
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
				<?php else : ?>
					<div class="pal-tax990-single__hero-icon">
						<i class="fas fa-file-pdf"></i>
					</div>
				<?php endif; ?>
			</div>
			<div class="pal-tax990-single__hero-content">
				<?php if ( $year ) : ?>
					<span class="pal-tax990-single__year-badge"><?php echo esc_html( $year ); ?></span>
				<?php endif; ?>
				<h1 class="pal-tax990-single__title"><?php echo esc_html( $title ); ?></h1>
				<?php if ( $description ) : ?>
					<p class="pal-tax990-single__desc"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>
				<div class="pal-tax990-single__hero-actions">
					<?php if ( $pdf_url ) : ?>
						<a href="<?php echo esc_url( $pdf_url ); ?>" download class="pal-btn pal-btn--gold">
							<i class="fas fa-download"></i> <?php esc_html_e( 'Download PDF', 'nashua-pal-core' ); ?>
						</a>
					<?php endif; ?>
					<a href="<?php echo esc_url( $archive_url ); ?>" class="pal-btn pal-btn--outline-light">
						<i class="fas fa-arrow-left"></i> <?php esc_html_e( 'All Tax 990 Documents', 'nashua-pal-core' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>

	<!-- ============================================================ -->
	<!-- PDF Viewer                                                    -->
	<!-- ============================================================ -->
	<?php if ( $pdf_url ) : ?>
	<div class="pal-tax990-single__viewer">
		<div class="pal-tax990-single__viewer-inner">
			<h2 class="pal-tax990-single__viewer-heading">
				<i class="fas fa-file-pdf"></i> <?php echo esc_html( $title ); ?>
			</h2>
			<div class="pal-tax990-single__viewer-wrap">
				<iframe src="<?php echo esc_url( $pdf_url ); ?>#toolbar=1&navpanes=0" class="pal-tax990-single__pdf-frame" title="<?php echo esc_attr( $title ); ?>"></iframe>
			</div>
			<div class="pal-tax990-single__viewer-footer">
				<a href="<?php echo esc_url( $pdf_url ); ?>" download class="pal-btn pal-btn--gold">
					<i class="fas fa-download"></i> <?php esc_html_e( 'Download PDF', 'nashua-pal-core' ); ?>
				</a>
				<a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener" class="pal-btn pal-btn--outline-navy">
					<i class="fas fa-external-link-alt"></i> <?php esc_html_e( 'Open in New Tab', 'nashua-pal-core' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- ============================================================ -->
	<!-- Related Documents                                              -->
	<!-- ============================================================ -->
	<?php
	$related = new WP_Query( array(
		'post_type'      => NPAL_CPT_Tax990::POST_TYPE,
		'posts_per_page' => 3,
		'post_status'    => 'publish',
		'post__not_in'   => array( $pid ),
		'meta_key'       => '_pal_tax990_year',
		'orderby'        => 'meta_value_num',
		'order'          => 'DESC',
	) );

	if ( $related->have_posts() ) :
	?>
		<div class="pal-tax990-single__related">
			<div class="pal-tax990-single__related-inner">
				<h2 class="pal-tax990-single__related-title"><?php esc_html_e( 'More Tax 990 Documents', 'nashua-pal-core' ); ?></h2>
				<div class="pal-tax990-single__related-grid">
					<?php while ( $related->have_posts() ) : $related->the_post();
						$rid     = get_the_ID();
						$r_title = get_the_title();
						$r_year  = get_post_meta( $rid, '_pal_tax990_year', true );
						$r_img   = get_the_post_thumbnail_url( $rid, 'large' );
					?>
						<a href="<?php the_permalink(); ?>" class="pal-tax990-single__related-card">
							<?php if ( $r_img ) : ?>
								<img src="<?php echo esc_url( $r_img ); ?>" alt="<?php echo esc_attr( $r_title ); ?>" class="pal-tax990-single__related-img" />
							<?php else : ?>
								<div class="pal-tax990-single__related-icon"><i class="fas fa-file-pdf"></i></div>
							<?php endif; ?>
							<div class="pal-tax990-single__related-info">
								<span class="pal-tax990-single__related-name"><?php echo esc_html( $r_title ); ?></span>
								<?php if ( $r_year ) : ?>
									<span class="pal-tax990-single__related-year"><?php echo esc_html( $r_year ); ?></span>
								<?php endif; ?>
							</div>
						</a>
					<?php endwhile; ?>
				</div>
			</div>
		</div>
	<?php
		wp_reset_postdata();
	endif;
	?>

</div>

<?php endwhile;

get_footer();
