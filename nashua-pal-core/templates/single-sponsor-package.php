<?php
/**
 * Single template for Sponsorship Packages.
 *
 * @package NashuaPalCore
 * @since   1.30.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

while ( have_posts() ) : the_post();
	$pid         = get_the_ID();
	$name        = get_the_title();
	$price       = get_post_meta( $pid, '_pal_pkg_price', true );
	$subtitle    = get_post_meta( $pid, '_pal_pkg_subtitle', true );
	$benefits    = get_post_meta( $pid, '_pal_pkg_benefits', true );
	$year        = get_post_meta( $pid, '_pal_pkg_year', true );
	$contact_url = get_post_meta( $pid, '_pal_pkg_contact_url', true );
	$external    = get_post_meta( $pid, '_pal_pkg_external_url', true );
	$featured    = '1' === get_post_meta( $pid, '_pal_pkg_featured', true );
	$cat_term    = NPAL_CPT_Sponsor_Package::get_package_category( $pid );
	$cat_label   = $cat_term ? $cat_term->name : '';
	$badge_style = NPAL_CPT_Sponsor_Package::get_badge_style( $cat_term );
	$img         = get_the_post_thumbnail_url( $pid, 'large' );
	$img_style   = $img ? 'background-image:url(' . esc_url( $img ) . ')' : '';

	$benefit_lines = $benefits ? array_filter( array_map( 'trim', explode( "\n", $benefits ) ) ) : array();
?>

<div class="pal-pkg-single">

	<!-- Hero -->
	<div class="pal-pkg-single__hero">
		<div class="pal-pkg-single__hero-media" style="<?php echo esc_attr( $img_style ); ?>">
			<?php if ( $cat_label ) : ?>
				<span class="pal-pkg-card__badge" <?php if ( $badge_style ) { echo 'style="' . esc_attr( $badge_style ) . '"'; } ?>><?php echo esc_html( $cat_label ); ?></span>
			<?php endif; ?>
			<?php if ( $featured ) : ?>
				<span class="pal-pkg-card__featured-tag"><i class="fas fa-star"></i> <?php esc_html_e( 'Featured', 'nashua-pal-core' ); ?></span>
			<?php endif; ?>
		</div>
		<div class="pal-pkg-single__hero-content">
			<h1 class="pal-pkg-single__name"><?php echo esc_html( $name ); ?></h1>
			<?php if ( $price ) : ?>
				<span class="pal-pkg-single__price"><?php echo esc_html( $price ); ?></span>
			<?php endif; ?>
			<?php if ( $subtitle ) : ?>
				<p class="pal-pkg-single__subtitle"><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
			<div class="pal-pkg-single__meta">
				<?php if ( $year ) : ?>
					<span class="pal-pkg-single__meta-item"><i class="fas fa-calendar-alt"></i> <?php echo esc_html( $year ); ?></span>
				<?php endif; ?>
				<?php if ( $cat_label ) : ?>
					<span class="pal-pkg-single__meta-item"><i class="fas fa-tag"></i> <?php echo esc_html( $cat_label ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Content -->
	<div class="pal-pkg-single__content-wrap">
		<div class="pal-pkg-single__main">

			<?php if ( ! empty( $benefit_lines ) ) : ?>
				<div class="pal-pkg-single__benefits">
					<h2><?php esc_html_e( 'What\'s Included', 'nashua-pal-core' ); ?></h2>
					<ul>
						<?php foreach ( $benefit_lines as $line ) : ?>
							<li><i class="fas fa-check-circle"></i> <?php echo esc_html( $line ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php
			$content = get_the_content();
			if ( $content ) :
			?>
				<div class="pal-pkg-single__description">
					<h2><?php esc_html_e( 'Package Details', 'nashua-pal-core' ); ?></h2>
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<div class="pal-pkg-single__actions">
				<?php if ( $contact_url ) : ?>
					<a href="<?php echo esc_url( $contact_url ); ?>" class="pal-pkg-single__btn pal-pkg-single__btn--primary">
						<i class="fas fa-handshake"></i> <?php esc_html_e( 'Become a Sponsor', 'nashua-pal-core' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( $external ) : ?>
					<a href="<?php echo esc_url( $external ); ?>" class="pal-pkg-single__btn pal-pkg-single__btn--outline" target="_blank" rel="noopener">
						<i class="fas fa-file-pdf"></i> <?php esc_html_e( 'Download Details', 'nashua-pal-core' ); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<!-- Related Packages -->
	<?php
	$related_args = array(
		'post_type'      => NPAL_CPT_Sponsor_Package::POST_TYPE,
		'posts_per_page' => 3,
		'post_status'    => 'publish',
		'post__not_in'   => array( $pid ),
		'meta_key'       => '_pal_pkg_order',
		'orderby'        => 'meta_value_num',
		'order'          => 'ASC',
	);
	if ( $year ) {
		$related_args['meta_query'] = array(
			array( 'key' => '_pal_pkg_year', 'value' => $year ),
		);
	}
	$related = new WP_Query( $related_args );

	if ( $related->have_posts() ) :
	?>
		<div class="pal-pkg-single__related">
			<h2 class="pal-pkg-single__related-title"><?php esc_html_e( 'Other Sponsorship Packages', 'nashua-pal-core' ); ?></h2>
			<div class="pal-pkg-grid pal-pkg-grid--cols-3">
				<?php while ( $related->have_posts() ) : $related->the_post();
					$rpid      = get_the_ID();
					$rname     = get_the_title();
					$rprice    = get_post_meta( $rpid, '_pal_pkg_price', true );
					$rsubtitle = get_post_meta( $rpid, '_pal_pkg_subtitle', true );
					$rbenefits = get_post_meta( $rpid, '_pal_pkg_benefits', true );
					$rimg      = get_the_post_thumbnail_url( $rpid, 'large' );
					$rimg_s    = $rimg ? 'background-image:url(' . esc_url( $rimg ) . ')' : '';
					$rcat      = NPAL_CPT_Sponsor_Package::get_package_category( $rpid );
					$rcat_l    = $rcat ? $rcat->name : '';
					$rbadge    = NPAL_CPT_Sponsor_Package::get_badge_style( $rcat );
					$rfeat     = '1' === get_post_meta( $rpid, '_pal_pkg_featured', true );
					$rblines   = $rbenefits ? array_slice( array_filter( array_map( 'trim', explode( "\n", $rbenefits ) ) ), 0, 3 ) : array();
				?>
					<div class="pal-pkg-card<?php echo $rfeat ? ' pal-pkg-card--featured' : ''; ?>">
						<a href="<?php the_permalink(); ?>" class="pal-pkg-card__link">
							<div class="pal-pkg-card__media" style="<?php echo esc_attr( $rimg_s ); ?>">
								<?php if ( $rcat_l ) : ?>
									<span class="pal-pkg-card__badge" <?php if ( $rbadge ) { echo 'style="' . esc_attr( $rbadge ) . '"'; } ?>><?php echo esc_html( $rcat_l ); ?></span>
								<?php endif; ?>
							</div>
							<div class="pal-pkg-card__body">
								<h3 class="pal-pkg-card__name"><?php echo esc_html( $rname ); ?></h3>
								<?php if ( $rprice ) : ?>
									<span class="pal-pkg-card__price"><?php echo esc_html( $rprice ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $rblines ) ) : ?>
									<ul class="pal-pkg-card__benefits">
										<?php foreach ( $rblines as $bl ) : ?>
											<li><?php echo esc_html( $bl ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
								<span class="pal-pkg-card__cta"><?php esc_html_e( 'View Details', 'nashua-pal-core' ); ?> <i class="fas fa-arrow-right"></i></span>
							</div>
						</a>
					</div>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="pal-pkg-single__back">
		<?php
		$archive_url = get_post_type_archive_link( NPAL_CPT_Sponsor_Package::POST_TYPE ) ?: home_url( '/sponsorship-packages/' );
		?>
		<a href="<?php echo esc_url( $archive_url ); ?>"><i class="fas fa-arrow-left"></i> <?php esc_html_e( 'All Sponsorship Packages', 'nashua-pal-core' ); ?></a>
	</div>

</div>

<?php endwhile; ?>

<?php get_footer(); ?>
