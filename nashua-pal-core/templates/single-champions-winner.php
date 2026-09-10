<?php
/**
 * Single Champions Winner template.
 *
 * Loaded by NPAL_Champions_Shortcodes::single_template() when viewing a
 * single champions_winner post. Uses get_header/get_footer from the
 * active theme (Hello Elementor) so it inherits the site header/footer.
 *
 * @package NashuaPalCore
 * @since   1.15.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

while ( have_posts() ) : the_post();

	$pid          = get_the_ID();
	$name         = get_post_meta( $pid, '_pal_champion_name', true ) ?: get_the_title();
	$award_title  = get_post_meta( $pid, '_pal_champion_award_title', true );
	$year         = get_post_meta( $pid, '_pal_champion_year', true );
	$organization = get_post_meta( $pid, '_pal_champion_organization', true );
	$sport        = get_post_meta( $pid, '_pal_champion_sport', true );
	$video_url    = get_post_meta( $pid, '_pal_champion_video_url', true );
	$ext_url      = get_post_meta( $pid, '_pal_champion_external_url', true );
	$image        = get_the_post_thumbnail_url( $pid, 'large' );

	// Category from taxonomy.
	$cat_term  = NPAL_CPT_Champions_Winner::get_award_category( $pid );
	$cat_slug  = $cat_term ? $cat_term->slug : '';
	$cat_label = $cat_term ? $cat_term->name : '';
?>

<div class="pal-champ-single">

	<!-- Hero Section -->
	<div class="pal-champ-single__hero">
		<div class="pal-champ-single__hero-inner">
			<?php if ( $image ) : ?>
				<div class="pal-champ-single__hero-image">
					<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
				</div>
			<?php endif; ?>
			<div class="pal-champ-single__hero-content">
				<?php if ( $cat_label ) : ?>
					<span class="pal-champ-single__category"><?php echo esc_html( $cat_label ); ?></span>
				<?php endif; ?>
				<h1 class="pal-champ-single__name"><?php echo esc_html( $name ); ?></h1>
				<?php if ( $award_title ) : ?>
					<p class="pal-champ-single__award"><?php echo esc_html( $award_title ); ?></p>
				<?php endif; ?>
				<div class="pal-champ-single__meta">
					<?php if ( $year ) : ?>
						<span class="pal-champ-single__meta-item"><i class="fas fa-calendar-alt"></i> <?php echo esc_html( $year ); ?></span>
					<?php endif; ?>
					<?php if ( $organization ) : ?>
						<span class="pal-champ-single__meta-item"><i class="fas fa-building"></i> <?php echo esc_html( $organization ); ?></span>
					<?php endif; ?>
					<?php if ( $sport ) : ?>
						<span class="pal-champ-single__meta-item"><i class="fas fa-running"></i> <?php echo esc_html( $sport ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>

	<!-- Story Content -->
	<div class="pal-champ-single__content-wrap">
		<div class="pal-champ-single__content">
			<?php the_content(); ?>
		</div>

		<?php if ( $video_url ) :
			// Convert YouTube URL to embed.
			$embed_url = '';
			if ( preg_match( '/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/))([a-zA-Z0-9_-]+)/', $video_url, $m ) ) {
				$embed_url = 'https://www.youtube.com/embed/' . $m[1];
			}
		?>
			<?php if ( $embed_url ) : ?>
				<div class="pal-champ-single__video">
					<div class="pal-champ-single__video-wrap">
						<iframe src="<?php echo esc_url( $embed_url ); ?>" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
					</div>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<?php if ( $ext_url ) : ?>
			<p class="pal-champ-single__ext-link">
				<a href="<?php echo esc_url( $ext_url ); ?>" target="_blank" rel="noopener" class="pal-btn pal-btn--outline">
					<?php esc_html_e( 'Read Full Article', 'nashua-pal-core' ); ?> <i class="fas fa-external-link-alt"></i>
				</a>
			</p>
		<?php endif; ?>

		<!-- Back link -->
		<div class="pal-champ-single__back">
			<?php
			$archive_url = get_post_type_archive_link( NPAL_CPT_Champions_Winner::POST_TYPE );
			?>
			<a href="<?php echo esc_url( $archive_url ); ?>" class="pal-btn pal-btn--primary">
				<i class="fas fa-arrow-left"></i> <?php esc_html_e( 'Back to Champions', 'nashua-pal-core' ); ?>
			</a>
		</div>
	</div>

	<!-- Related Winners -->
	<?php
	$related_args = array(
		'post_type'      => NPAL_CPT_Champions_Winner::POST_TYPE,
		'posts_per_page' => 3,
		'post_status'    => 'publish',
		'post__not_in'   => array( $pid ),
		'meta_key'       => '_pal_champion_order',
		'orderby'        => 'meta_value_num',
		'order'          => 'ASC',
	);
	// Build query conditions for related: same year OR same category.
	$relation_parts = array();
	if ( $year ) {
		$relation_parts[] = array( 'key' => '_pal_champion_year', 'value' => $year );
	}
	if ( $year && ! $cat_slug ) {
		$related_args['meta_query'] = array( $relation_parts[0] );
	} elseif ( $cat_slug && ! $year ) {
		$related_args['tax_query'] = array(
			array( 'taxonomy' => NPAL_CPT_Champions_Winner::TAXONOMY, 'field' => 'slug', 'terms' => $cat_slug ),
		);
	} elseif ( $year && $cat_slug ) {
		// Prefer same year; fall back to same category.
		$related_args['meta_query'] = array(
			array( 'key' => '_pal_champion_year', 'value' => $year ),
		);
	}
	$related = new WP_Query( $related_args );

	if ( $related->have_posts() ) :
	?>
		<div class="pal-champ-single__related">
			<div class="pal-champ-single__related-inner">
				<h2 class="pal-champ-section__title"><?php esc_html_e( 'More Champions', 'nashua-pal-core' ); ?></h2>
				<div class="pal-champ-grid pal-champ-grid--cols-3">
					<?php while ( $related->have_posts() ) : $related->the_post();
						$rid     = get_the_ID();
						$r_name  = get_post_meta( $rid, '_pal_champion_name', true ) ?: get_the_title();
						$r_award = get_post_meta( $rid, '_pal_champion_award_title', true );
						$r_img   = get_the_post_thumbnail_url( $rid, 'large' );
						$r_term  = NPAL_CPT_Champions_Winner::get_award_category( $rid );
						$r_bio   = get_post_meta( $rid, '_pal_champion_bio', true ) ?: get_the_excerpt();
						$img_style = $r_img ? 'background-image:url(' . esc_url( $r_img ) . ')' : '';
					?>
						<div class="pal-champ-card" data-champ-category="<?php echo $r_term ? esc_attr( $r_term->slug ) : ''; ?>">
							<a href="<?php the_permalink(); ?>" class="pal-champ-card__link">
								<div class="pal-champ-card__media" style="<?php echo esc_attr( $img_style ); ?>">
									<?php if ( $r_term ) : ?>
										<span class="pal-champ-card__badge pal-champ-card__badge--<?php echo esc_attr( $r_term->slug ); ?>" style="<?php echo esc_attr( NPAL_CPT_Champions_Winner::get_badge_style( $r_term ) ); ?>"><?php echo esc_html( $r_term->name ); ?></span>
									<?php endif; ?>
								</div>
								<div class="pal-champ-card__body">
									<h3 class="pal-champ-card__name"><?php echo esc_html( $r_name ); ?></h3>
									<?php if ( $r_award ) : ?>
										<p class="pal-champ-card__award"><?php echo esc_html( $r_award ); ?></p>
									<?php endif; ?>
									<?php if ( $r_bio ) : ?>
										<p class="pal-champ-card__bio"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $r_bio ), 20, '&hellip;' ) ); ?></p>
									<?php endif; ?>
									<span class="pal-champ-card__cta"><?php esc_html_e( 'Read Story', 'nashua-pal-core' ); ?> <i class="fas fa-arrow-right"></i></span>
								</div>
							</a>
						</div>
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
