<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcode for Donors recognition page.
 *
 * [pal_donors year="" per_page="100"]
 *
 * Reads the single donor-year post for the selected fiscal year,
 * parses the names textarea, and outputs a JSON data blob + HTML
 * structure for client-side JS pagination/search.
 *
 * @package NashuaPalCore
 * @since   1.36.0
 */
final class NPAL_Donor_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'pal_donors', array( $this, 'donors' ) );
	}

	/* ================================================================== */
	/*  [pal_donors]                                                       */
	/* ================================================================== */

	public function donors( $atts ) {
		$a = shortcode_atts( array(
			'year'     => '',
			'per_page' => '100',
		), $atts, 'pal_donors' );

		// Determine fiscal year: URL param > shortcode attr > current FY.
		$year = '';
		if ( ! empty( $_GET['donor_year'] ) ) {
			$year = absint( $_GET['donor_year'] );
		} elseif ( ! empty( $a['year'] ) ) {
			$year = absint( $a['year'] );
		}
		if ( ! $year ) {
			$year = NPAL_CPT_Donor::current_fiscal_year();
		}

		$per_page     = max( 1, (int) $a['per_page'] );
		$fiscal_years = NPAL_CPT_Donor::get_fiscal_years();

		// If no donor years exist at all, show a message.
		if ( empty( $fiscal_years ) ) {
			return '<div class="pal-donor-list"><p class="pal-donor-list__empty">' . esc_html__( 'No donors have been added yet.', 'nashua-pal-core' ) . '</p></div>';
		}

		// If selected year has no donors, default to the most recent year.
		if ( ! in_array( (string) $year, $fiscal_years, true ) ) {
			$year = (int) $fiscal_years[0];
		}

		// Find the donor post for this fiscal year.
		$query = new WP_Query( array(
			'post_type'      => NPAL_CPT_Donor::POST_TYPE,
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'   => '_pal_donor_fiscal_year',
					'value' => (string) $year,
				),
			),
		) );

		$donors = array();
		if ( $query->have_posts() ) {
			$query->the_post();
			$names_raw = get_post_meta( get_the_ID(), '_pal_donor_names', true );
			if ( $names_raw ) {
				$lines = explode( "\n", $names_raw );
				foreach ( $lines as $line ) {
					$name = trim( $line );
					if ( '' !== $name ) {
						$donors[] = $name;
					}
				}
			}
			wp_reset_postdata();
		}

		// Sort alphabetically.
		usort( $donors, 'strnatcasecmp' );

		// Admin "Add Donors" link.
		$add_url = '';
		if ( current_user_can( 'edit_posts' ) ) {
			$add_url = admin_url( 'post-new.php?post_type=' . NPAL_CPT_Donor::POST_TYPE );
		}

		ob_start();
		?>
		<div class="pal-donor-list"
			 data-donors="<?php echo esc_attr( wp_json_encode( $donors ) ); ?>"
			 data-per-page="<?php echo esc_attr( $per_page ); ?>"
			 data-year="<?php echo esc_attr( $year ); ?>">

			<div class="pal-donor-list__header">
				<div class="pal-donor-list__tabs">
					<span class="pal-donor-list__tab is-active"><?php esc_html_e( 'All Donors', 'nashua-pal-core' ); ?></span>
					<?php if ( $add_url ) : ?>
						<a href="<?php echo esc_url( $add_url ); ?>" class="pal-donor-list__add-link" target="_blank" rel="noopener">
							<i class="fas fa-plus-circle"></i> <?php esc_html_e( 'Add Donors', 'nashua-pal-core' ); ?>
						</a>
					<?php endif; ?>
				</div>
				<div class="pal-donor-list__search-wrap">
					<i class="fas fa-search pal-donor-list__search-icon"></i>
					<input type="text" class="pal-donor-list__search" placeholder="<?php esc_attr_e( 'Search donors...', 'nashua-pal-core' ); ?>" />
				</div>
			</div>

			<div class="pal-donor-list__count">
				<span class="pal-donor-list__total"><?php echo esc_html( count( $donors ) ); ?></span> <?php esc_html_e( 'donors', 'nashua-pal-core' ); ?>
			</div>

			<div class="pal-donor-list__table">
				<div class="pal-donor-list__col pal-donor-list__col--left"></div>
				<div class="pal-donor-list__col pal-donor-list__col--right"></div>
			</div>

			<div class="pal-donor-list__empty-search" style="display:none;">
				<p><?php esc_html_e( 'No donors match your search.', 'nashua-pal-core' ); ?></p>
			</div>

			<div class="pal-donor-list__pagination"></div>
		</div>
		<?php
		return ob_get_clean();
	}
}
