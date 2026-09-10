<?php
/**
 * Single Job — PHP fallback.
 *
 * Only fires if Elementor Pro's Theme Builder Single doesn't match for
 * `pal_job`. Renders a branded single job page with hero, content, and
 * sidebar details panel — matching the Events/Programs pattern.
 *
 * @package NashuaPalChild
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>

<header class="pal-page-hero pal-page-hero--jobs">
	<div class="pal-container">
		<nav class="pal-breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'nashua-pal-child' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'nashua-pal-child' ); ?></a>
			<span class="pal-breadcrumbs__sep">›</span>
			<a href="<?php echo esc_url( get_post_type_archive_link( 'pal_job' ) ); ?>"><?php esc_html_e( 'Jobs', 'nashua-pal-child' ); ?></a>
			<span class="pal-breadcrumbs__sep">›</span>
			<span><?php the_title(); ?></span>
		</nav>
		<h1><?php the_title(); ?></h1>
		<?php
		$type = get_post_meta( get_the_ID(), '_pal_job_type', true );
		$location = get_post_meta( get_the_ID(), '_pal_job_location', true );
		if ( $type || $location ) :
			$type_labels = array(
				'full_time'  => __( 'Full-Time', 'nashua-pal-child' ),
				'part_time'  => __( 'Part-Time', 'nashua-pal-child' ),
				'seasonal'   => __( 'Seasonal', 'nashua-pal-child' ),
				'volunteer'  => __( 'Volunteer', 'nashua-pal-child' ),
				'internship' => __( 'Internship', 'nashua-pal-child' ),
			);
			?>
			<p class="pal-page-hero__lede">
				<?php if ( $type && isset( $type_labels[ $type ] ) ) echo esc_html( $type_labels[ $type ] ); ?>
				<?php if ( $type && $location ) echo ' · '; ?>
				<?php if ( $location ) echo esc_html( $location ); ?>
			</p>
		<?php endif; ?>
	</div>
</header>

<section class="pal-section pal-job-single-section">
	<div class="pal-container">
		<?php
		if ( shortcode_exists( 'pal_job_single' ) ) {
			echo do_shortcode( '[pal_job_single]' );
		} else {
			// Minimal fallback if shortcode isn't available.
			while ( have_posts() ) : the_post();
				the_content();
			endwhile;
		}
		?>
	</div>
</section>

<!-- Related Jobs -->
<?php
$current_id = get_the_ID();
$related = new WP_Query( array(
	'post_type'      => 'pal_job',
	'posts_per_page' => 3,
	'post_status'    => 'publish',
	'post__not_in'   => array( $current_id ),
	'meta_query'     => array(
		array( 'key' => '_pal_job_status', 'value' => 'open' ),
	),
) );
if ( $related->have_posts() ) : ?>
<section class="pal-section pal-section--gray">
	<div class="pal-container pal-container--wide">
		<h2 style="text-align:center;margin:0 0 var(--pal-space-md);"><?php esc_html_e( 'Other Open Positions', 'nashua-pal-child' ); ?></h2>
		<div class="pal-jobs pal-jobs--grid pal-jobs--cols-3">
			<?php while ( $related->have_posts() ) : $related->the_post();
				$r_id     = get_the_ID();
				$r_type   = get_post_meta( $r_id, '_pal_job_type', true );
				$r_loc    = get_post_meta( $r_id, '_pal_job_location', true );
				$r_thumb  = get_the_post_thumbnail_url( $r_id, 'medium_large' );
				$r_type_labels = array(
					'full_time'  => __( 'Full-Time', 'nashua-pal-child' ),
					'part_time'  => __( 'Part-Time', 'nashua-pal-child' ),
					'seasonal'   => __( 'Seasonal', 'nashua-pal-child' ),
					'volunteer'  => __( 'Volunteer', 'nashua-pal-child' ),
					'internship' => __( 'Internship', 'nashua-pal-child' ),
				);
				?>
				<article class="pal-job-card<?php echo $r_thumb ? '' : ' pal-job-card--no-media'; ?>">
					<?php if ( $r_thumb ) : ?>
						<div class="pal-job-card__media">
							<a href="<?php the_permalink(); ?>">
								<img src="<?php echo esc_url( $r_thumb ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" />
							</a>
						</div>
					<?php endif; ?>
					<div class="pal-job-card__body">
						<?php if ( $r_type ) : ?>
							<div class="pal-job-card__badges">
								<span class="pal-job-card__badge pal-job-card__badge--type"><?php echo esc_html( isset( $r_type_labels[ $r_type ] ) ? $r_type_labels[ $r_type ] : '' ); ?></span>
							</div>
						<?php endif; ?>
						<h3 class="pal-job-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php if ( $r_loc ) : ?>
							<div class="pal-job-card__meta">
								<span class="pal-job-card__location"><?php echo esc_html( $r_loc ); ?></span>
							</div>
						<?php endif; ?>
						<a class="pal-btn pal-btn--primary pal-job-card__apply" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View Details', 'nashua-pal-child' ); ?></a>
					</div>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
