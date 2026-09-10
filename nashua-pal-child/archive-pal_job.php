<?php
/**
 * Jobs Archive — PHP fallback.
 *
 * Only fires if Elementor Pro's Theme Builder Archive doesn't match for
 * `pal_job`. Renders a branded Jobs page matching the approved mockup:
 *   1. Navy hero with title + lede
 *   2. 4 feature icon cards (Make an Impact, Grow Your Career, etc.)
 *   3. Organization intro paragraph
 *   4. Open job listings via [pal_job_listings]
 *   5. Bottom CTA band
 *
 * @package NashuaPalChild
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
?>

<header class="pal-page-hero pal-page-hero--jobs">
	<div class="pal-container" style="text-align:center;">
		<h1><?php esc_html_e( 'Jobs', 'nashua-pal-child' ); ?></h1>
		<p class="pal-page-hero__lede"><?php esc_html_e( 'Join our team and help build stronger kids, safer communities, and brighter futures every day.', 'nashua-pal-child' ); ?></p>
	</div>
</header>

<!-- Feature Cards -->
<section class="pal-section pal-section--tight pal-jobs-features">
	<div class="pal-container">
		<div class="pal-jobs-features__grid">
			<div class="pal-jobs-feature">
				<div class="pal-jobs-feature__icon">
					<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18z"/><path d="M7 13l3 3 7-7"/></svg>
				</div>
				<h4 class="pal-jobs-feature__title"><?php esc_html_e( 'Make an Impact', 'nashua-pal-child' ); ?></h4>
				<p class="pal-jobs-feature__desc"><?php esc_html_e( 'Help shape the future of Nashua youth through mentoring, coaching, and community programs.', 'nashua-pal-child' ); ?></p>
			</div>
			<div class="pal-jobs-feature">
				<div class="pal-jobs-feature__icon">
					<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
				</div>
				<h4 class="pal-jobs-feature__title"><?php esc_html_e( 'Grow Your Career', 'nashua-pal-child' ); ?></h4>
				<p class="pal-jobs-feature__desc"><?php esc_html_e( 'Develop leadership skills and advance your career while serving the community.', 'nashua-pal-child' ); ?></p>
			</div>
			<div class="pal-jobs-feature">
				<div class="pal-jobs-feature__icon">
					<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
				</div>
				<h4 class="pal-jobs-feature__title"><?php esc_html_e( 'Support Youth', 'nashua-pal-child' ); ?></h4>
				<p class="pal-jobs-feature__desc"><?php esc_html_e( 'Create safe, supportive environments where young people can learn, play, and thrive.', 'nashua-pal-child' ); ?></p>
			</div>
			<div class="pal-jobs-feature">
				<div class="pal-jobs-feature__icon">
					<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
				</div>
				<h4 class="pal-jobs-feature__title"><?php esc_html_e( 'Flexible Opportunities', 'nashua-pal-child' ); ?></h4>
				<p class="pal-jobs-feature__desc"><?php esc_html_e( 'Full-time, part-time, and seasonal positions available to fit your schedule.', 'nashua-pal-child' ); ?></p>
			</div>
		</div>
	</div>
</section>

<!-- Organization Intro -->
<section class="pal-section pal-section--gray pal-jobs-intro">
	<div class="pal-container" style="text-align:center;max-width:860px;">
		<h2 class="pal-jobs-intro__title"><?php esc_html_e( 'Nashua Police Athletic League', 'nashua-pal-child' ); ?></h2>
		<p class="pal-jobs-intro__text"><?php esc_html_e( 'We prepare Nashua youth (ages 5–18) for success in adulthood with a focus on Education, Activity, and Leadership. We seek those that love to strengthen the community.', 'nashua-pal-child' ); ?></p>
		<p class="pal-jobs-intro__note"><?php esc_html_e( 'Nashua PAL is a 501(c)(3) nonprofit organization serving the greater Nashua, NH community.', 'nashua-pal-child' ); ?></p>
	</div>
</section>

<!-- Job Listings -->
<section class="pal-section pal-jobs-listing-section">
	<div class="pal-container pal-container--wide">
		<?php
		if ( shortcode_exists( 'pal_job_listings' ) ) {
			echo do_shortcode( '[pal_job_listings count="-1" status="open" view="list" show_image="yes"]' );
		} else {
			echo '<p style="text-align:center;color:var(--pal-gray-400);">' . esc_html__( 'Activate the nashua-pal-core plugin to display job listings.', 'nashua-pal-child' ) . '</p>';
		}
		?>
	</div>
</section>

<!-- Bottom CTA -->
<section class="pal-section--navy pal-jobs-cta">
	<div class="pal-container" style="text-align:center;padding:var(--pal-space-xl) var(--pal-space-sm);">
		<h2 style="color:var(--pal-white);margin:0 0 var(--pal-space-xs);"><?php esc_html_e( 'Be Part of Something Bigger', 'nashua-pal-child' ); ?></h2>
		<p style="color:rgba(255,255,255,0.85);max-width:600px;margin:0 auto var(--pal-space-md);"><?php esc_html_e( 'Join a dedicated team making a real difference in the lives of Nashua youth.', 'nashua-pal-child' ); ?></p>
		<a class="pal-btn pal-btn--primary" href="/get-involved/"><?php esc_html_e( 'Get Involved', 'nashua-pal-child' ); ?></a>
	</div>
</section>

<?php
get_footer();
