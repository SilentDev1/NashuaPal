<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcodes and template overrides for Impact Awardees.
 *
 * [impact_awardees year="2026" category="" show_filter="true" columns="4" count="50"]
 *
 * @package NashuaPalCore
 * @since   1.31.0
 */
final class NPAL_Impact_Awardee_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'impact_awardees', array( $this, 'awardees' ) );

		add_filter( 'single_template',  array( $this, 'single_template' ) );
		add_filter( 'archive_template', array( $this, 'archive_template' ) );
	}

	/* ================================================================== */
	/*  TEMPLATE OVERRIDES                                                 */
	/* ================================================================== */

	public function single_template( $template ) {
		if ( is_singular( NPAL_CPT_Impact_Awardee::POST_TYPE ) ) {
			$plugin_tmpl = NPAL_PLUGIN_DIR . 'templates/single-impact-awardee.php';
			if ( file_exists( $plugin_tmpl ) ) {
				return $plugin_tmpl;
			}
		}
		return $template;
	}

	public function archive_template( $template ) {
		if ( is_post_type_archive( NPAL_CPT_Impact_Awardee::POST_TYPE ) ) {
			$plugin_tmpl = NPAL_PLUGIN_DIR . 'templates/archive-impact-awardee.php';
			if ( file_exists( $plugin_tmpl ) ) {
				return $plugin_tmpl;
			}
		}
		return $template;
	}

	/* ================================================================== */
	/*  HELPERS                                                            */
	/* ================================================================== */

	private function get_featured_image( $post_id, $size = 'large' ) {
		$url = get_the_post_thumbnail_url( $post_id, $size );
		return $url ? $url : '';
	}

	private function get_awardee_name( $post_id ) {
		$name = get_post_meta( $post_id, '_pal_awardee_name', true );
		return $name ? $name : get_the_title( $post_id );
	}

	private function get_awardee_bio( $post_id ) {
		$bio = get_post_meta( $post_id, '_pal_awardee_bio', true );
		if ( $bio ) { return $bio; }
		$excerpt = get_the_excerpt( $post_id );
		return $excerpt ? $excerpt : '';
	}

	private function get_category_data( $post_id ) {
		$term = NPAL_CPT_Impact_Awardee::get_award_category( $post_id );
		if ( ! $term ) { return array( 'slug' => '', 'label' => '', 'badge_style' => '' ); }
		return array(
			'slug'        => $term->slug,
			'label'       => $term->name,
			'badge_style' => NPAL_CPT_Impact_Awardee::get_badge_style( $term ),
		);
	}

	/* ================================================================== */
	/*  [impact_awardees]                                                */
	/* ================================================================== */

	public function awardees( $atts ) {
		$a = shortcode_atts( array(
			'year'           => '',
			'category'       => '',
			'show_filter'    => 'true',
			'columns'        => '4',
			'count'          => '50',
			'eyebrow'        => '',
			'view_all_url'   => '',
			'view_all_label' => '',
		), $atts, 'impact_awardees' );

		$year           = sanitize_text_field( $a['year'] );
		$category       = sanitize_text_field( $a['category'] );
		$show_filter    = 'true' === $a['show_filter'];
		$columns        = max( 1, min( 4, (int) $a['columns'] ) );
		$count          = max( 1, (int) $a['count'] );
		$eyebrow        = sanitize_text_field( $a['eyebrow'] );
		$view_all_url   = esc_url( $a['view_all_url'] );
		$view_all_label = sanitize_text_field( $a['view_all_label'] );

		// Build query args.
		$meta_query = array();
		$tax_query  = array();
		if ( $year ) {
			$meta_query[] = array( 'key' => '_pal_awardee_year', 'value' => $year );
		}
		if ( $category ) {
			$tax_query[] = array(
				'taxonomy' => NPAL_CPT_Impact_Awardee::TAXONOMY,
				'field'    => 'slug',
				'terms'    => $category,
			);
		}

		$query_args = array(
			'post_type'      => NPAL_CPT_Impact_Awardee::POST_TYPE,
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'meta_key'       => '_pal_awardee_order',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		);
		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}
		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = $tax_query;
		}

		$query = new WP_Query( $query_args );
		if ( ! $query->have_posts() ) {
			return '<p class="pal-awardee-empty">' . esc_html__( 'No awardees found.', 'nashua-pal-core' ) . '</p>';
		}

		// Build items array.
		$items = array();
		$years = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$pid     = get_the_ID();
			$catdata = $this->get_category_data( $pid );
			$item    = array(
				'id'           => $pid,
				'name'         => $this->get_awardee_name( $pid ),
				'award_title'  => get_post_meta( $pid, '_pal_awardee_award_title', true ),
				'year'         => get_post_meta( $pid, '_pal_awardee_year', true ),
				'category'     => $catdata['slug'],
				'cat_label'    => $catdata['label'],
				'badge_style'  => $catdata['badge_style'],
				'bio'          => $this->get_awardee_bio( $pid ),
				'organization' => get_post_meta( $pid, '_pal_awardee_organization', true ),
				'image'        => $this->get_featured_image( $pid ),
				'permalink'    => get_permalink( $pid ),
				'donate_url'   => get_post_meta( $pid, '_pal_awardee_donate_url', true ),
			);
			$items[] = $item;

			if ( $item['year'] && ! in_array( $item['year'], $years, true ) ) {
				$years[] = $item['year'];
			}
		}
		wp_reset_postdata();
		rsort( $years );

		ob_start();

		echo '<div class="pal-awardee-section">';

		if ( $eyebrow ) {
			echo '<span class="pal-awardee-section__eyebrow">' . esc_html( $eyebrow ) . '</span>';
		}

		// Year filter tabs.
		if ( $show_filter && count( $years ) > 1 ) {
			echo '<div class="pal-awardee-filter" role="tablist">';
			echo '<button type="button" class="pal-awardee-filter__btn is-active" data-awardee-filter="all">' . esc_html__( 'All Years', 'nashua-pal-core' ) . '</button>';
			foreach ( $years as $yr ) {
				echo '<button type="button" class="pal-awardee-filter__btn" data-awardee-filter="' . esc_attr( $yr ) . '">' . esc_html( $yr ) . '</button>';
			}
			echo '</div>';
		}

		echo '<div class="pal-awardee-grid pal-awardee-grid--cols-' . esc_attr( $columns ) . '">';
		foreach ( $items as $item ) {
			$this->render_awardee_card( $item );
		}
		echo '</div>';

		if ( $view_all_url && $view_all_label ) {
			echo '<div class="pal-awardee-section__footer"><a href="' . esc_url( $view_all_url ) . '" class="pal-awardee-section__view-all">' . esc_html( $view_all_label ) . ' <i class="fas fa-arrow-right"></i></a></div>';
		}

		echo '</div>';

		return ob_get_clean();
	}

	/* ------------------------------------------------------------------ */
	/*  Card renderer                                                      */
	/* ------------------------------------------------------------------ */

	private function render_awardee_card( $item ) {
		$img_style = $item['image'] ? 'background-image:url(' . esc_url( $item['image'] ) . ')' : '';
		?>
		<div class="pal-awardee-card" data-awardee-category="<?php echo esc_attr( $item['category'] ); ?>" data-awardee-year="<?php echo esc_attr( $item['year'] ); ?>">
			<div class="pal-awardee-card__media" style="<?php echo esc_attr( $img_style ); ?>">
				<?php if ( $item['cat_label'] ) : ?>
					<span class="pal-awardee-card__badge" <?php if ( $item['badge_style'] ) { echo 'style="' . esc_attr( $item['badge_style'] ) . '"'; } ?>><?php echo esc_html( $item['cat_label'] ); ?></span>
				<?php endif; ?>
			</div>
			<div class="pal-awardee-card__body">
				<h3 class="pal-awardee-card__name"><?php echo esc_html( $item['name'] ); ?></h3>
				<?php if ( $item['award_title'] ) : ?>
					<p class="pal-awardee-card__award"><?php echo esc_html( $item['award_title'] ); ?></p>
				<?php endif; ?>
				<?php if ( $item['bio'] ) : ?>
					<p class="pal-awardee-card__bio"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item['bio'] ), 20, '&hellip;' ) ); ?></p>
				<?php endif; ?>
				<div class="pal-awardee-card__actions">
					<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="pal-awardee-card__btn pal-awardee-card__btn--meet">
						<?php esc_html_e( 'Learn More', 'nashua-pal-core' ); ?> <i class="fas fa-arrow-right"></i>
					</a>
					<?php if ( $item['donate_url'] ) : ?>
						<a href="<?php echo esc_url( $item['donate_url'] ); ?>" class="pal-awardee-card__btn pal-awardee-card__btn--donate" target="_blank" rel="noopener">
							<i class="fas fa-heart"></i>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
