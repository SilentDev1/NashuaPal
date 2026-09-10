<?php
/**
 * Single Impact Awardee template.
 *
 * Compact layout inspired by mockup:
 *   1. Hero (photo + info + bio excerpt + buttons)
 *   2. Quote banner (gold)
 *   3. Media row (video + gallery side-by-side)
 *   4. Story content
 *   5. Related awardees
 *
 * @package NashuaPalCore
 * @since   1.31.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

while ( have_posts() ) : the_post();

	$pid          = get_the_ID();
	$name         = get_post_meta( $pid, '_pal_awardee_name', true ) ?: get_the_title();
	$award_title  = get_post_meta( $pid, '_pal_awardee_award_title', true );
	$year         = get_post_meta( $pid, '_pal_awardee_year', true );
	$organization = get_post_meta( $pid, '_pal_awardee_organization', true );
	$donate_url   = get_post_meta( $pid, '_pal_awardee_donate_url', true );
	$video_url    = get_post_meta( $pid, '_pal_awardee_video_url', true );
	$gallery_raw  = get_post_meta( $pid, '_pal_awardee_gallery', true );
	$gallery_ids  = ! empty( $gallery_raw ) ? array_filter( array_map( 'absint', explode( ',', $gallery_raw ) ) ) : array();
	$image        = get_the_post_thumbnail_url( $pid, 'full' );
	$bio          = get_post_meta( $pid, '_pal_awardee_bio', true );

	// YouTube embed URL.
	$embed_url = '';
	if ( $video_url && preg_match( '/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([a-zA-Z0-9_-]+)/', $video_url, $m ) ) {
		$embed_url = 'https://www.youtube.com/embed/' . $m[1];
	}

	// Gallery image data.
	$gallery_images = array();
	foreach ( $gallery_ids as $gid ) {
		$full  = wp_get_attachment_image_url( $gid, 'full' );
		$thumb = wp_get_attachment_image_url( $gid, 'large' );
		$alt   = get_post_meta( $gid, '_wp_attachment_image_alt', true ) ?: $name;
		if ( $full ) {
			$gallery_images[] = array( 'full' => $full, 'thumb' => $thumb ?: $full, 'alt' => $alt );
		}
	}

	$has_media = $embed_url || ! empty( $gallery_images );

	// Press release.
	$press_release   = get_post_meta( $pid, '_pal_awardee_press_release', true );
	$press_pdf_id    = absint( get_post_meta( $pid, '_pal_awardee_press_pdf', true ) );
	$press_pdf_url   = $press_pdf_id ? wp_get_attachment_url( $press_pdf_id ) : '';
	$has_press       = $press_release || $press_pdf_url;
	$press_btn_label = get_post_meta( $pid, '_pal_awardee_press_btn_label', true ) ?: __( 'Read Press Release', 'nashua-pal-core' );

	// Category from taxonomy.
	$cat_term    = NPAL_CPT_Impact_Awardee::get_award_category( $pid );
	$cat_slug    = $cat_term ? $cat_term->slug : '';
	$cat_label   = $cat_term ? $cat_term->name : '';
	$badge_style = NPAL_CPT_Impact_Awardee::get_badge_style( $cat_term );

	$archive_url = get_post_type_archive_link( NPAL_CPT_Impact_Awardee::POST_TYPE );

	// First name for headings.
	$first_name = explode( ' ', $name )[0];
?>

<div class="pal-awardee-single">

	<!-- ============================================================ -->
	<!-- Hero Section                                                  -->
	<!-- ============================================================ -->
	<div class="pal-awardee-single__hero">
		<div class="pal-awardee-single__hero-inner">
			<?php if ( $image ) : ?>
				<div class="pal-awardee-single__hero-image">
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
				</div>
			<?php endif; ?>
			<div class="pal-awardee-single__hero-content">
				<?php if ( $cat_label ) : ?>
					<span class="pal-awardee-single__category" <?php if ( $badge_style ) { echo 'style="' . esc_attr( $badge_style ) . '"'; } ?>><?php echo esc_html( $cat_label ); ?></span>
				<?php endif; ?>
				<h1 class="pal-awardee-single__name"><?php echo esc_html( $name ); ?></h1>
				<?php if ( $award_title ) : ?>
					<p class="pal-awardee-single__award"><?php echo esc_html( $award_title ); ?></p>
				<?php endif; ?>
				<div class="pal-awardee-single__meta">
					<?php if ( $year ) : ?>
						<span class="pal-awardee-single__meta-item"><i class="fas fa-calendar-alt"></i> <?php echo esc_html( $year ); ?></span>
					<?php endif; ?>
					<?php if ( $organization ) : ?>
						<span class="pal-awardee-single__meta-item"><i class="fas fa-building"></i> <?php echo esc_html( $organization ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( $bio ) : ?>
					<p class="pal-awardee-single__bio"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $bio ), 35, '&hellip;' ) ); ?></p>
				<?php endif; ?>
				<div class="pal-awardee-single__hero-actions">
					<?php if ( $donate_url ) : ?>
						<a href="<?php echo esc_url( $donate_url ); ?>" target="_blank" rel="noopener" class="pal-btn pal-btn--gold">
							<i class="fas fa-heart"></i> <?php esc_html_e( 'Donate', 'nashua-pal-core' ); ?>
						</a>
					<?php endif; ?>
					<?php if ( $has_press ) : ?>
						<button type="button" class="pal-btn pal-btn--outline-light" id="pal-awardee-press-open">
							<i class="fas fa-newspaper"></i> <?php echo esc_html( $press_btn_label ); ?>
						</button>
					<?php endif; ?>
					<a href="<?php echo esc_url( $archive_url ); ?>" class="pal-btn pal-btn--outline-light">
						<i class="fas fa-arrow-left"></i> <?php esc_html_e( 'Back to Impact Awardees', 'nashua-pal-core' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>

	<!-- ============================================================ -->
	<!-- Quote Banner                                                  -->
	<!-- ============================================================ -->
	<?php
	$content_raw = get_the_content();
	$content_text = wp_strip_all_tags( $content_raw );
	if ( trim( $content_text ) ) :
	?>
	<div class="pal-awardee-single__quote-banner">
		<div class="pal-awardee-single__quote-inner">
			<i class="fas fa-quote-left pal-awardee-single__quote-icon"></i>
			<div class="pal-awardee-single__quote-text">
				<?php echo wp_kses_post( wpautop( $content_raw ) ); ?>
			</div>
		</div>
	</div>
	<?php endif; ?>

	<!-- ============================================================ -->
	<!-- Media Row (Video + Photos side-by-side)                       -->
	<!-- ============================================================ -->
	<?php if ( $has_media ) : ?>
	<div class="pal-awardee-single__media-row">
		<div class="pal-awardee-single__media-inner">

			<?php if ( $embed_url ) : ?>
			<div class="pal-awardee-single__media-col <?php echo ! empty( $gallery_images ) ? '' : 'pal-awardee-single__media-col--full'; ?>">
				<h3 class="pal-awardee-single__media-heading"><i class="fas fa-play-circle"></i> <?php esc_html_e( 'Watch the Story', 'nashua-pal-core' ); ?></h3>
				<div class="pal-awardee-single__video-wrap">
					<iframe src="<?php echo esc_url( $embed_url ); ?>" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
				</div>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $gallery_images ) ) : ?>
			<div class="pal-awardee-single__media-col <?php echo ! $embed_url ? 'pal-awardee-single__media-col--full' : ''; ?>">
				<h3 class="pal-awardee-single__media-heading"><i class="fas fa-images"></i> <?php esc_html_e( 'Photos', 'nashua-pal-core' ); ?></h3>
				<div class="pal-awardee-gallery-grid" id="pal-awardee-gallery">
					<?php foreach ( $gallery_images as $idx => $img ) : ?>
						<a href="<?php echo esc_url( $img['full'] ); ?>"
						   class="pal-awardee-gallery-grid__item"
						   data-awardee-lightbox="<?php echo esc_attr( $idx ); ?>"
						   aria-label="<?php printf( esc_attr__( 'View photo %d', 'nashua-pal-core' ), $idx + 1 ); ?>">
							<img src="<?php echo esc_url( $img['thumb'] ); ?>" alt="<?php echo esc_attr( $img['alt'] ); ?>" loading="lazy" />
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- Lightbox data (consumed by JS) -->
			<script type="application/json" id="pal-awardee-gallery-data"><?php echo wp_json_encode( $gallery_images ); ?></script>
			<?php endif; ?>

		</div>
	</div>
	<?php endif; ?>

	<!-- ============================================================ -->
	<!-- Related Awardees                                               -->
	<!-- ============================================================ -->
	<?php
	$related_args = array(
		'post_type'      => NPAL_CPT_Impact_Awardee::POST_TYPE,
		'posts_per_page' => 4,
		'post_status'    => 'publish',
		'post__not_in'   => array( $pid ),
		'meta_key'       => '_pal_awardee_order',
		'orderby'        => 'meta_value_num',
		'order'          => 'ASC',
	);
	// Try same year first; fall back to all if not enough.
	if ( $year ) {
		$year_args = $related_args;
		$year_args['meta_query'] = array(
			array( 'key' => '_pal_awardee_year', 'value' => $year ),
		);
		$related = new WP_Query( $year_args );
		if ( $related->found_posts < 4 ) {
			$related = new WP_Query( $related_args );
		}
	} else {
		$related = new WP_Query( $related_args );
	}

	if ( $related->have_posts() ) :
	?>
		<div class="pal-awardee-single__related">
			<div class="pal-awardee-single__related-inner">
				<h2 class="pal-awardee-single__related-title"><?php esc_html_e( 'More Impact Awardees', 'nashua-pal-core' ); ?></h2>
				<div class="pal-awardee-single__related-grid">
					<?php while ( $related->have_posts() ) : $related->the_post();
						$rid     = get_the_ID();
						$r_name  = get_post_meta( $rid, '_pal_awardee_name', true ) ?: get_the_title();
						$r_award = get_post_meta( $rid, '_pal_awardee_award_title', true );
						$r_img   = get_the_post_thumbnail_url( $rid, 'large' );
					?>
						<a href="<?php the_permalink(); ?>" class="pal-awardee-single__related-card">
							<?php if ( $r_img ) : ?>
								<img src="<?php echo esc_url( $r_img ); ?>" alt="<?php echo esc_attr( $r_name ); ?>" class="pal-awardee-single__related-img" />
							<?php endif; ?>
							<div class="pal-awardee-single__related-info">
								<span class="pal-awardee-single__related-name"><?php echo esc_html( $r_name ); ?></span>
								<?php if ( $r_award ) : ?>
									<span class="pal-awardee-single__related-award"><?php echo esc_html( $r_award ); ?></span>
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

<?php if ( $has_press ) : ?>
<!-- Press Release Modal -->
<div class="pal-awardee-modal <?php echo $press_pdf_url ? 'pal-awardee-modal--has-pdf' : ''; ?>" id="pal-awardee-press-modal" style="display:none;">
	<div class="pal-awardee-modal__overlay"></div>
	<div class="pal-awardee-modal__dialog">
		<div class="pal-awardee-modal__header">
			<h2 class="pal-awardee-modal__title"><i class="fas fa-newspaper"></i> <?php esc_html_e( 'Press Release', 'nashua-pal-core' ); ?></h2>
			<button class="pal-awardee-modal__close" aria-label="<?php esc_attr_e( 'Close', 'nashua-pal-core' ); ?>">&times;</button>
		</div>
		<div class="pal-awardee-modal__body">
			<?php if ( $press_release ) : ?>
				<div class="pal-awardee-modal__text">
					<?php echo nl2br( esc_html( $press_release ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( $press_pdf_url ) : ?>
				<div class="pal-awardee-modal__pdf">
					<div class="pal-awardee-modal__pdf-embed">
						<iframe src="<?php echo esc_url( $press_pdf_url ); ?>#toolbar=1&navpanes=0" type="application/pdf"></iframe>
					</div>
					<div class="pal-awardee-modal__pdf-actions">
						<a href="<?php echo esc_url( $press_pdf_url ); ?>" target="_blank" rel="noopener" class="pal-btn pal-btn--gold pal-btn--sm">
							<i class="fas fa-external-link-alt"></i> <?php esc_html_e( 'Open PDF in New Tab', 'nashua-pal-core' ); ?>
						</a>
						<a href="<?php echo esc_url( $press_pdf_url ); ?>" download class="pal-btn pal-btn--outline-light pal-btn--sm">
							<i class="fas fa-download"></i> <?php esc_html_e( 'Download PDF', 'nashua-pal-core' ); ?>
						</a>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
<?php endif; ?>

<!-- Lightbox overlay -->
<div class="pal-awardee-lightbox" id="pal-awardee-lightbox" style="display:none;">
	<div class="pal-awardee-lightbox__overlay"></div>
	<button class="pal-awardee-lightbox__close" aria-label="<?php esc_attr_e( 'Close', 'nashua-pal-core' ); ?>">&times;</button>
	<button class="pal-awardee-lightbox__prev" aria-label="<?php esc_attr_e( 'Previous', 'nashua-pal-core' ); ?>"><i class="fas fa-chevron-left"></i></button>
	<button class="pal-awardee-lightbox__next" aria-label="<?php esc_attr_e( 'Next', 'nashua-pal-core' ); ?>"><i class="fas fa-chevron-right"></i></button>
	<div class="pal-awardee-lightbox__content">
		<img class="pal-awardee-lightbox__img" src="" alt="" />
		<div class="pal-awardee-lightbox__counter"></div>
	</div>
</div>

<?php endwhile;

get_footer();
