<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcode for rendering Event / Program sponsors.
 *
 * [pal_sponsors context="event" year="2026" heading="Thank You to Our Sponsors"]
 *
 * Produces a scrolling marquee with level badges — same visual pattern as
 * the Night of Champions [champions_sponsors] shortcode.
 *
 * @package NashuaPalCore
 * @since   1.23.0
 */
final class NPAL_Sponsor_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'pal_sponsors', array( $this, 'sponsors' ) );
	}

	/* ================================================================== */
	/*  [pal_sponsors]                                                     */
	/* ================================================================== */

	public function sponsors( $atts ) {
		$a = shortcode_atts( array(
			'context' => 'event',
			'year'    => '',
			'post_id' => '',
			'heading' => __( 'Thank You to Our Sponsors', 'nashua-pal-core' ),
		), $atts, 'pal_sponsors' );

		$context = sanitize_key( $a['context'] );
		if ( ! in_array( $context, array( 'event', 'program' ), true ) ) {
			$context = 'event';
		}

		$year = sanitize_text_field( $a['year'] );

		// Resolve post_id — "auto" detects the current single event/program.
		$post_id = $a['post_id'];
		if ( 'auto' === $post_id ) {
			if ( is_singular( NPAL_CPT_Event::POST_TYPE ) || is_singular( NPAL_CPT_Program::POST_TYPE ) ) {
				$post_id = get_the_ID();
			} else {
				$post_id = '';
			}
		}
		$post_id = absint( $post_id );

		// If a specific post_id is given, read linked sponsors from that post.
		if ( $post_id ) {
			$meta_key    = 'event' === $context ? '_pal_event_linked_sponsors' : '_pal_program_linked_sponsors';
			$linked_raw  = get_post_meta( $post_id, $meta_key, true );
			$linked_ids  = is_array( $linked_raw ) ? array_filter( array_map( 'absint', $linked_raw ) ) : array();

			if ( empty( $linked_ids ) ) { return ''; }

			$query_args = array(
				'post_type'      => NPAL_CPT_Sponsor::POST_TYPE,
				'posts_per_page' => 100,
				'post_status'    => 'publish',
				'post__in'       => $linked_ids,
				'meta_key'       => '_pal_sponsor_order',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
			);
		} else {
			// Fallback: query all sponsors by context + optional year.
			$meta_query = array(
				array( 'key' => '_pal_sponsor_context', 'value' => $context ),
			);
			if ( $year ) {
				$meta_query[] = array( 'key' => '_pal_sponsor_year', 'value' => $year );
			}

			$query_args = array(
				'post_type'      => NPAL_CPT_Sponsor::POST_TYPE,
				'posts_per_page' => 100,
				'post_status'    => 'publish',
				'meta_key'       => '_pal_sponsor_order',
				'orderby'        => 'meta_value_num',
				'order'          => 'ASC',
				'meta_query'     => $meta_query,
			);
		}

		$query = new WP_Query( $query_args );

		if ( ! $query->have_posts() ) { return ''; }

		// Get ordered sponsor levels.
		$level_terms = NPAL_CPT_Sponsor::get_ordered_levels();

		// Build badge style lookup per level.
		$level_badge_styles = array();
		foreach ( $level_terms as $lt ) {
			$level_badge_styles[ $lt->slug ] = NPAL_CPT_Sponsor::get_level_badge_style( $lt );
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
			$level_term = NPAL_CPT_Sponsor::get_sponsor_level( $pid );
			$level_slug = $level_term ? $level_term->slug : '_uncategorized';

			if ( ! isset( $grouped[ $level_slug ] ) ) {
				$grouped[ $level_slug ] = array( 'label' => $level_term->name, 'items' => array() );
			}

			$grouped[ $level_slug ]['items'][] = array(
				'name'        => get_the_title( $pid ),
				'logo'        => $this->get_featured_image( $pid, 'medium' ),
				'url'         => get_post_meta( $pid, '_pal_sponsor_url', true ),
				'level_label' => $level_term ? $level_term->name : '',
				'badge_style' => isset( $level_badge_styles[ $level_slug ] ) ? $level_badge_styles[ $level_slug ] : '',
			);
		}
		wp_reset_postdata();

		// Flatten all sponsors into one list for the combined marquee.
		$all_logos = array();
		foreach ( $grouped as $group ) {
			foreach ( $group['items'] as $s ) {
				$all_logos[] = $s;
			}
		}

		ob_start();
		echo '<div class="pal-sponsors" data-sponsor-filter-section>';
		if ( $a['heading'] ) {
			echo '<h2 class="pal-sponsors__heading">' . esc_html( $a['heading'] ) . '</h2>';
		}

		// Single scrolling marquee with every logo + level badge.
		if ( ! empty( $all_logos ) ) {
			echo '<div class="pal-sponsors__tier pal-sponsors__tier--all" data-sponsor-level="all">';
			echo '<div class="pal-sponsors__marquee">';
			echo '<div class="pal-sponsors__track">';
			for ( $dup = 0; $dup < 2; $dup++ ) {
				foreach ( $all_logos as $s ) {
					$tag   = $s['url'] ? 'a' : 'span';
					$href  = $s['url'] ? ' href="' . esc_url( $s['url'] ) . '" target="_blank" rel="noopener"' : '';
					$img   = $s['logo']
						? '<img src="' . esc_url( $s['logo'] ) . '" alt="' . esc_attr( $s['name'] ) . '" loading="lazy" />'
						: '<span class="pal-sponsors__name-text">' . esc_html( $s['name'] ) . '</span>';
					$badge = '';
					if ( ! empty( $s['level_label'] ) ) {
						$badge = '<span class="pal-sponsors__level-badge"';
						if ( ! empty( $s['badge_style'] ) ) {
							$badge .= ' style="' . esc_attr( $s['badge_style'] ) . '"';
						}
						$badge .= '>' . esc_html( $s['level_label'] ) . '</span>';
					}
					echo '<' . $tag . ' class="pal-sponsors__logo pal-sponsors__logo--badged"' . $href . '>' . $img . $badge . '</' . $tag . '>';
				}
			}
			echo '</div></div>';
			echo '</div>';
		}

		echo '</div>';
		return ob_get_clean();
	}

	/* ================================================================== */
	/*  Helper                                                             */
	/* ================================================================== */

	private function get_featured_image( $post_id, $size = 'large' ) {
		$url = get_the_post_thumbnail_url( $post_id, $size );
		if ( $url ) { return $url; }
		$thumb_id = get_post_thumbnail_id( $post_id );
		if ( $thumb_id ) {
			$src = wp_get_attachment_image_src( $thumb_id, $size );
			return $src ? $src[0] : '';
		}
		return '';
	}
}
