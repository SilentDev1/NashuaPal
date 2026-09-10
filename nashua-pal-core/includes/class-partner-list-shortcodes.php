<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcode for Partners List recognition page.
 *
 * [pal_partner_list tier="" per_page="100"]
 *
 * Reads the partner-list post for the selected tier, parses the
 * names textarea, and outputs a JSON data blob + HTML structure
 * for client-side JS pagination/search.
 *
 * @package NashuaPalCore
 * @since   1.40.0
 */
final class NPAL_Partner_List_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'pal_partner_list', array( $this, 'partner_list' ) );
	}

	/* ================================================================== */
	/*  [pal_partner_list]                                                 */
	/* ================================================================== */

	public function partner_list( $atts ) {
		$a = shortcode_atts( array(
			'tier'     => '',
			'per_page' => '100',
		), $atts, 'pal_partner_list' );

		$per_page = max( 1, (int) $a['per_page'] );

		// Query ALL partner list posts and group names by tier.
		$query = new WP_Query( array(
			'post_type'      => NPAL_CPT_Partner_List::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );

		$tiers_data  = array(); // slug => array of names
		$total_count = 0;
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$tier_slug = get_post_meta( get_the_ID(), '_pal_partner_list_tier', true );
				$names_raw = get_post_meta( get_the_ID(), '_pal_partner_list_names', true );
				if ( ! $tier_slug || ! $names_raw ) {
					continue;
				}
				if ( ! isset( $tiers_data[ $tier_slug ] ) ) {
					$tiers_data[ $tier_slug ] = array();
				}
				$lines = explode( "\n", $names_raw );
				foreach ( $lines as $line ) {
					$name = trim( $line );
					if ( '' !== $name ) {
						$tiers_data[ $tier_slug ][] = $name;
					}
				}
			}
			wp_reset_postdata();
		}

		// Deduplicate and sort within each tier.
		foreach ( $tiers_data as $slug => $names ) {
			$names = array_values( array_unique( $names ) );
			usort( $names, 'strnatcasecmp' );
			$tiers_data[ $slug ] = $names;
			$total_count += count( $names );
		}

		if ( 0 === $total_count ) {
			return '<div class="pal-partner-list"><p class="pal-partner-list__empty">' . esc_html__( 'No partners have been added yet.', 'nashua-pal-core' ) . '</p></div>';
		}

		// Build ordered tier array using canonical display order.
		$tiers_ordered = array();
		foreach ( NPAL_CPT_Partner_List::TIERS as $slug => $label ) {
			if ( ! empty( $tiers_data[ $slug ] ) ) {
				$tiers_ordered[] = array(
					'label' => $label,
					'names' => $tiers_data[ $slug ],
				);
			}
		}
		// Include any tiers not in the canonical list.
		foreach ( $tiers_data as $slug => $names ) {
			if ( ! isset( NPAL_CPT_Partner_List::TIERS[ $slug ] ) && ! empty( $names ) ) {
				$tiers_ordered[] = array(
					'label' => ucfirst( $slug ),
					'names' => $names,
				);
			}
		}

		// Admin "Add Partner Tier" link.
		$add_url = '';
		if ( current_user_can( 'edit_posts' ) ) {
			$add_url = admin_url( 'post-new.php?post_type=' . NPAL_CPT_Partner_List::POST_TYPE );
		}

		ob_start();
		?>
		<div class="pal-partner-list"
			 data-tiers="<?php echo esc_attr( wp_json_encode( $tiers_ordered ) ); ?>"
			 data-per-page="<?php echo esc_attr( $per_page ); ?>"
			 data-tier="all">

			<div class="pal-partner-list__header">
				<div class="pal-partner-list__tabs">
					<span class="pal-partner-list__tab is-active"><?php esc_html_e( 'All Partners', 'nashua-pal-core' ); ?></span>
					<?php if ( $add_url ) : ?>
						<a href="<?php echo esc_url( $add_url ); ?>" class="pal-partner-list__add-link" target="_blank" rel="noopener">
							<i class="fas fa-plus-circle"></i> <?php esc_html_e( 'Add Partners', 'nashua-pal-core' ); ?>
						</a>
					<?php endif; ?>
				</div>
				<div class="pal-partner-list__search-wrap">
					<i class="fas fa-search pal-partner-list__search-icon"></i>
					<input type="text" class="pal-partner-list__search" placeholder="<?php esc_attr_e( 'Search partners...', 'nashua-pal-core' ); ?>" />
				</div>
			</div>

			<div class="pal-partner-list__count">
				<span class="pal-partner-list__total"><?php echo esc_html( $total_count ); ?></span> <?php esc_html_e( 'partners', 'nashua-pal-core' ); ?>
			</div>

			<div class="pal-partner-list__tiers"></div>

			<div class="pal-partner-list__empty-search" style="display:none;">
				<p><?php esc_html_e( 'No partners match your search.', 'nashua-pal-core' ); ?></p>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
