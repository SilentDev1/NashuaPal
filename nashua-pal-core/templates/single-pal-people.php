<?php
/**
 * Single PAL People template (Board Member, Team Member, Volunteer Leader).
 *
 * Uses get_header/get_footer from the active theme (Hello Elementor) so
 * it inherits the site header/footer.
 *
 * @package NashuaPalCore
 * @since   1.20.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

while ( have_posts() ) : the_post();

	$pid   = get_the_ID();
	$name  = get_the_title();
	$role  = get_post_meta( $pid, '_pal_people_role', true );
	$org   = get_post_meta( $pid, '_pal_people_organization', true );
	$email = get_post_meta( $pid, '_pal_people_email', true );
	$phone = get_post_meta( $pid, '_pal_people_phone', true );
	$bio   = get_post_meta( $pid, '_pal_people_bio', true );
	$image = get_the_post_thumbnail_url( $pid, 'large' );
	$placeholder = NPAL_PLUGIN_URL . 'assets/img/person-placeholder.svg';

	// Determine the back link based on post type.
	$post_type  = get_post_type();
	$type_label = '';
	switch ( $post_type ) {
		case NPAL_CPT_Board_Member::POST_TYPE:
			$type_label = __( 'Board & Staff', 'nashua-pal-core' );
			break;
		case NPAL_CPT_Team_Member::POST_TYPE:
			$type_label = __( 'Board & Staff', 'nashua-pal-core' );
			break;
		case NPAL_CPT_Volunteer_Leader::POST_TYPE:
			$type_label = __( 'Board & Staff', 'nashua-pal-core' );
			break;
	}
?>

<div class="pal-people-profile">
	<div class="pal-people-profile__header">
		<div class="pal-people-profile__photo">
			<?php if ( $image ) : ?>
				<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
			<?php else : ?>
				<img src="<?php echo esc_url( $placeholder ); ?>" alt="<?php echo esc_attr( $name ); ?>" />
			<?php endif; ?>
		</div>
		<div class="pal-people-profile__details">
			<h1 class="pal-people-profile__name"><?php echo esc_html( $name ); ?></h1>
			<?php if ( $role ) : ?>
				<p class="pal-people-profile__role"><?php echo esc_html( $role ); ?></p>
			<?php endif; ?>
			<?php if ( $org ) : ?>
				<p class="pal-people-profile__org"><?php echo esc_html( $org ); ?></p>
			<?php endif; ?>
			<?php if ( $email || $phone ) : ?>
				<div class="pal-people-profile__contact">
					<?php if ( $email ) : ?>
						<a href="mailto:<?php echo esc_attr( $email ); ?>"><i class="fas fa-envelope"></i> <?php echo esc_html( $email ); ?></a>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><i class="fas fa-phone"></i> <?php echo esc_html( $phone ); ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $bio ) : ?>
		<div class="pal-people-profile__bio">
			<?php echo wp_kses_post( wpautop( $bio ) ); ?>
		</div>
	<?php endif; ?>

	<?php
	// Show editor content if present.
	$content = get_the_content();
	if ( ! empty( trim( $content ) ) ) : ?>
		<div class="pal-people-profile__bio">
			<?php the_content(); ?>
		</div>
	<?php endif; ?>

	<div class="pal-people-profile__back">
		<a href="<?php echo esc_url( home_url( '/board-staff/' ) ); ?>">
			<i class="fas fa-arrow-left"></i> <?php printf( esc_html__( 'Back to %s', 'nashua-pal-core' ), esc_html( $type_label ) ); ?>
		</a>
	</div>
</div>

<?php endwhile;

get_footer();
