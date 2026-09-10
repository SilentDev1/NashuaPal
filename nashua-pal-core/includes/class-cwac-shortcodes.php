<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcodes for Cone with a Cop event page.
 *
 * [cwac_sponsors year="2026" heading="Thank You to Our Sponsors"]
 *
 * @package NashuaPalCore
 * @since   1.26.0
 */
final class NPAL_CWAC_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'cwac_sponsors', array( $this, 'sponsors' ) );
	}

	/* ================================================================== */
	/*  HELPERS                                                            */
	/* ================================================================== */

	private function get_featured_image( $post_id, $size = 'medium' ) {
		$url = get_the_post_thumbnail_url( $post_id, $size );
		return $url ? $url : '';
	}

	/* ================================================================== */
	/*  [cwac_sponsors]                                                    */
	/* ================================================================== */

	public function sponsors( $atts ) {
		$a = shortcode_atts( array(
			'year'    => '',
			'heading' => __( 'Thank You to Our Sweet Sponsors!', 'nashua-pal-core' ),
		), $atts, 'cwac_sponsors' );

		$year = sanitize_text_field( $a['year'] );

		$meta_query = array();
		if ( $year ) {
			$meta_query[] = array( 'key' => '_pal_cwac_sponsor_year', 'value' => $year );
		}

		$query_args = array(
			'post_type'      => NPAL_CPT_CWAC_Sponsor::POST_TYPE,
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'meta_key'       => '_pal_cwac_sponsor_order',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		);
		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $query_args );
		if ( ! $query->have_posts() ) { return ''; }

		// Get ordered sponsor levels from taxonomy.
		$level_terms = NPAL_CPT_CWAC_Sponsor::get_ordered_levels();

		// Build badge style lookup per level.
		$level_badge_styles = array();
		foreach ( $level_terms as $lt ) {
			$level_badge_styles[ $lt->slug ] = NPAL_CPT_CWAC_Sponsor::get_level_badge_style( $lt );
		}

		// Group sponsors by level slug.
		$grouped = array();
		foreach ( $level_terms as $lt ) {
			$grouped[ $lt->slug ] = array( 'label' => $lt->name, 'items' => array() );
		}
		$grouped['_uncategorized'] = array( 'label' => __( 'Sponsors', 'nashua-pal-core' ), 'items' => array() );

		while ( $query->have_posts() ) {
			$query->the_post();
			$pid        = get_the_ID();
			$level_term = NPAL_CPT_CWAC_Sponsor::get_sponsor_level( $pid );
			$level_slug = $level_term ? $level_term->slug : '_uncategorized';

			if ( ! isset( $grouped[ $level_slug ] ) ) {
				$grouped[ $level_slug ] = array( 'label' => $level_term->name, 'items' => array() );
			}

			$grouped[ $level_slug ]['items'][] = array(
				'name'        => get_the_title( $pid ),
				'logo'        => $this->get_featured_image( $pid, 'medium' ),
				'url'         => get_post_meta( $pid, '_pal_cwac_sponsor_url', true ),
				'level'       => $level_slug,
				'level_label' => $level_term ? $level_term->name : '',
				'badge_style' => isset( $level_badge_styles[ $level_slug ] ) ? $level_badge_styles[ $level_slug ] : '',
			);
		}
		wp_reset_postdata();

		// Flatten all sponsors into one list for the marquee.
		$all_logos = array();
		foreach ( $grouped as $group ) {
			foreach ( $group['items'] as $s ) {
				$all_logos[] = $s;
			}
		}

		ob_start();
		// Reuse the same CSS classes as Champions sponsors for consistent styling.
		echo '<div class="pal-champ-sponsors" data-sponsor-filter-section>';
		if ( $a['heading'] ) {
			echo '<h2 class="pal-champ-sponsors__heading">' . esc_html( $a['heading'] ) . '</h2>';
		}

		if ( ! empty( $all_logos ) ) {
			echo '<div class="pal-champ-sponsors__tier pal-champ-sponsors__tier--all" data-sponsor-level="all">';
			echo '<div class="pal-champ-sponsors__marquee">';
			echo '<div class="pal-champ-sponsors__track">';
			for ( $dup = 0; $dup < 2; $dup++ ) {
				foreach ( $all_logos as $s ) {
					$tag   = $s['url'] ? 'a' : 'span';
					$href  = $s['url'] ? ' href="' . esc_url( $s['url'] ) . '" target="_blank" rel="noopener"' : '';
					$img   = $s['logo']
						? '<img src="' . esc_url( $s['logo'] ) . '" alt="' . esc_attr( $s['name'] ) . '" loading="lazy" />'
						: '<span class="pal-champ-sponsors__name-text">' . esc_html( $s['name'] ) . '</span>';
					$badge = '';
					if ( ! empty( $s['level_label'] ) ) {
						$badge = '<span class="pal-champ-sponsors__level-badge"';
						if ( ! empty( $s['badge_style'] ) ) {
							$badge .= ' style="' . esc_attr( $s['badge_style'] ) . '"';
						}
						$badge .= '>' . esc_html( $s['level_label'] ) . '</span>';
					}
					echo '<' . $tag . ' class="pal-champ-sponsors__logo pal-champ-sponsors__logo--badged"' . $href . '>' . $img . $badge . '</' . $tag . '>';
				}
			}
			echo '</div></div>';
			echo '</div>';
		}

		echo '</div>';
		return ob_get_clean();
	}
}
