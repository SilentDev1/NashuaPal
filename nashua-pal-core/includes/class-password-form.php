<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Custom password-protection form for WordPress pages.
 *
 * Replaces the default WP password form with a branded PAL layout
 * featuring a lock illustration, "PROTECTED CONTENT" badge, and
 * styled password input — matching the Nashua PAL design language.
 *
 * @since 1.55.5
 */
final class NPAL_Password_Form {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter( 'the_password_form', array( $this, 'custom_password_form' ), 10, 2 );
	}

	/**
	 * Build the branded password form HTML.
	 *
	 * @param string  $output Default WP password form HTML.
	 * @param WP_Post $post   The password-protected post.
	 * @return string
	 */
	public function custom_password_form( $output, $post = null ) {
		$label  = 'pwbox-' . ( $post ? $post->ID : wp_rand() );
		$action = esc_url( site_url( 'wp-login.php?action=postpass', 'login_post' ) );
		$title  = $post ? esc_html( get_the_title( $post ) ) : esc_html__( 'Protected Content', 'nashua-pal-core' );

		ob_start();
		?>
		<div class="pal-pw-gate">
			<div class="pal-pw-gate__inner">

				<!-- Lock illustration -->
				<div class="pal-pw-gate__illustration">
					<svg class="pal-pw-gate__lock-svg" viewBox="0 0 120 150" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
						<rect x="10" y="60" width="100" height="80" rx="12" fill="#E2E8F0" stroke="#CBD5E1" stroke-width="2"/>
						<path d="M35 60V42C35 23.222 50.222 8 69 8H51C69.778 8 85 23.222 85 42V60" stroke="#CBD5E1" stroke-width="8" stroke-linecap="round" fill="none"/>
						<circle cx="60" cy="95" r="10" fill="#94A3B8"/>
						<rect x="56" y="102" width="8" height="16" rx="4" fill="#94A3B8"/>
					</svg>
				</div>

				<!-- Form content -->
				<div class="pal-pw-gate__content">
					<div class="pal-pw-gate__badge">
						<i class="fas fa-lock" aria-hidden="true"></i>
						<span><?php esc_html_e( 'PROTECTED CONTENT', 'nashua-pal-core' ); ?></span>
					</div>

					<h1 class="pal-pw-gate__title"><?php echo $title; ?></h1>

					<p class="pal-pw-gate__desc">
						<?php esc_html_e( 'This content is password-protected. To view it, please enter the password below.', 'nashua-pal-core' ); ?>
					</p>

					<form action="<?php echo $action; ?>" method="post" class="pal-pw-gate__form">
						<div class="pal-pw-gate__field-row">
							<input
								name="post_password"
								id="<?php echo esc_attr( $label ); ?>"
								type="password"
								class="pal-pw-gate__input"
								placeholder="<?php esc_attr_e( 'Enter password…', 'nashua-pal-core' ); ?>"
								spellcheck="false"
								required
							/>
							<button type="submit" class="pal-pw-gate__btn">
								<?php esc_html_e( 'Enter', 'nashua-pal-core' ); ?>
							</button>
						</div>
					</form>

					<div class="pal-pw-gate__notice">
						<i class="fas fa-shield-halved" aria-hidden="true"></i>
						<span>
							<?php esc_html_e( 'This area is for authorized PAL board members only. If you need access, please contact your administrator.', 'nashua-pal-core' ); ?>
						</span>
					</div>
				</div>

			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
