<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcodes and template overrides for Sponsorship Packages.
 *
 * [sponsor_packages year="" category="" show_filter="true" columns="3" count="50"
 *                   eyebrow="" view_all_url="" view_all_label=""]
 *
 * @package NashuaPalCore
 * @since   1.30.0
 */
final class NPAL_Sponsor_Package_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'sponsor_packages', array( $this, 'packages' ) );

		add_filter( 'single_template',  array( $this, 'single_template' ) );
		add_filter( 'archive_template', array( $this, 'archive_template' ) );
	}

	/* ================================================================== */
	/*  Template overrides                                                 */
	/* ================================================================== */

	public function single_template( $template ) {
		if ( is_singular( NPAL_CPT_Sponsor_Package::POST_TYPE ) ) {
			$plugin_tmpl = NPAL_PLUGIN_DIR . 'templates/single-sponsor-package.php';
			if ( file_exists( $plugin_tmpl ) ) {
				return $plugin_tmpl;
			}
		}
		return $template;
	}

	public function archive_template( $template ) {
		if ( is_post_type_archive( NPAL_CPT_Sponsor_Package::POST_TYPE ) ) {
			$plugin_tmpl = NPAL_PLUGIN_DIR . 'templates/archive-sponsor-package.php';
			if ( file_exists( $plugin_tmpl ) ) {
				return $plugin_tmpl;
			}
		}
		return $template;
	}

	/* ================================================================== */
	/*  Helpers                                                            */
	/* ================================================================== */

	private function get_category_data( $post_id ) {
		$term = NPAL_CPT_Sponsor_Package::get_package_category( $post_id );
		if ( ! $term ) { return array( 'slug' => '', 'label' => '', 'badge_style' => '' ); }
		return array(
			'slug'        => $term->slug,
			'label'       => $term->name,
			'badge_style' => NPAL_CPT_Sponsor_Package::get_badge_style( $term ),
		);
	}

	/* ================================================================== */
	/*  [sponsor_packages]                                                 */
	/* ================================================================== */

	public function packages( $atts ) {
		$a = shortcode_atts( array(
			'year'           => '',
			'category'       => '',
			'show_filter'    => 'true',
			'columns'        => '3',
			'count'          => '50',
			'eyebrow'        => '',
			'view_all_url'   => '',
			'view_all_label' => '',
		), $atts, 'sponsor_packages' );

		$year          = sanitize_text_field( $a['year'] );
		$category      = sanitize_text_field( $a['category'] );
		$show_filter   = 'true' === $a['show_filter'];
		$columns       = max( 1, min( 4, (int) $a['columns'] ) );
		$count         = max( 1, (int) $a['count'] );
		$eyebrow       = sanitize_text_field( $a['eyebrow'] );
		$view_all_url  = esc_url( $a['view_all_url'] );
		$view_all_label = sanitize_text_field( $a['view_all_label'] );

		// Build query args.
		$meta_query = array();
		$tax_query  = array();
		if ( $year ) {
			$meta_query[] = array( 'key' => '_pal_pkg_year', 'value' => $year );
		}
		if ( $category ) {
			$tax_query[] = array(
				'taxonomy' => NPAL_CPT_Sponsor_Package::TAXONOMY,
				'field'    => 'slug',
				'terms'    => $category,
			);
		}

		$query_args = array(
			'post_type'      => NPAL_CPT_Sponsor_Package::POST_TYPE,
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}
		if ( ! empty( $tax_query ) ) {
			$query_args['tax_query'] = $tax_query;
		}

		$query = new WP_Query( $query_args );
		if ( ! $query->have_posts() ) {
			return '<p class="pal-pkg-empty">' . esc_html__( 'No sponsorship packages found.', 'nashua-pal-core' ) . '</p>';
		}

		// Collect items.
		$items      = array();
		$categories = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$pid     = get_the_ID();
			$catdata = $this->get_category_data( $pid );

			$items[] = array(
				'id'           => $pid,
				'name'         => get_the_title( $pid ),
				'year'         => get_post_meta( $pid, '_pal_pkg_year', true ),
				'category'     => $catdata['slug'],
				'cat_label'    => $catdata['label'],
				'badge_style'  => $catdata['badge_style'],
				'image'        => get_the_post_thumbnail_url( $pid, 'medium_large' ),
				'gallery'      => NPAL_Sponsor_Package_Meta::get_gallery_urls( $pid ),
				'pdf'          => NPAL_Sponsor_Package_Meta::get_pdf_url( $pid ),
			);

			if ( $catdata['slug'] && ! isset( $categories[ $catdata['slug'] ] ) ) {
				$categories[ $catdata['slug'] ] = $catdata['label'];
			}
		}
		wp_reset_postdata();

		ob_start();
		echo '<div class="pal-pkg-section">';

		if ( $eyebrow ) {
			echo '<span class="pal-pkg-section__eyebrow">' . esc_html( $eyebrow ) . '</span>';
		}

		// Filter tabs.
		if ( $show_filter && count( $categories ) > 1 ) {
			echo '<div class="pal-pkg-filter" role="tablist">';
			echo '<button type="button" class="pal-pkg-filter__btn is-active" data-pkg-filter="all">' . esc_html__( 'All', 'nashua-pal-core' ) . '</button>';
			foreach ( $categories as $slug => $label ) {
				echo '<button type="button" class="pal-pkg-filter__btn" data-pkg-filter="' . esc_attr( $slug ) . '">' . esc_html( $label ) . '</button>';
			}
			echo '</div>';
		}

		echo '<div class="pal-pkg-grid pal-pkg-grid--cols-' . esc_attr( $columns ) . '">';
		foreach ( $items as $item ) {
			$this->render_card( $item );
		}
		echo '</div>';

		if ( $view_all_url && $view_all_label ) {
			echo '<div class="pal-pkg-section__footer"><a href="' . esc_url( $view_all_url ) . '" class="pal-pkg-section__view-all">' . esc_html( $view_all_label ) . ' <i class="fas fa-arrow-right"></i></a></div>';
		}

		echo '</div>';
		return ob_get_clean();
	}

	/* ------------------------------------------------------------------ */
	/*  Card renderer                                                      */
	/* ------------------------------------------------------------------ */

	private function render_card( $item ) {
		$pdf_url      = ! empty( $item['pdf'] ) ? $item['pdf'] : '';
		$gallery      = ! empty( $item['gallery'] ) ? $item['gallery'] : array();
		$gallery_json = ! empty( $gallery ) ? wp_json_encode( $gallery ) : '';
		$has_cta      = $pdf_url || $gallery_json;
		?>
		<div class="pal-pkg-card" data-pkg-category="<?php echo esc_attr( $item['category'] ); ?>">
			<div class="pal-pkg-card__inner">
				<div class="pal-pkg-card__media">
					<?php if ( $item['image'] ) : ?>
						<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy" />
					<?php else : ?>
						<span class="pal-pkg-card__placeholder"><i class="fas fa-handshake"></i></span>
					<?php endif; ?>
					<?php if ( $item['cat_label'] ) : ?>
						<span class="pal-pkg-card__badge" <?php if ( $item['badge_style'] ) { echo 'style="' . esc_attr( $item['badge_style'] ) . '"'; } ?>><?php echo esc_html( $item['cat_label'] ); ?></span>
					<?php endif; ?>
				</div>
				<div class="pal-pkg-card__body">
					<h3 class="pal-pkg-card__name"><?php echo esc_html( $item['name'] ); ?></h3>
					<?php if ( $pdf_url ) : ?>
						<a href="<?php echo esc_url( $pdf_url ); ?>" class="pal-pkg-card__cta" target="_blank" rel="noopener"><?php esc_html_e( 'View PDF', 'nashua-pal-core' ); ?> <i class="fas fa-file-pdf"></i></a>
					<?php elseif ( $gallery_json ) : ?>
						<button type="button" class="pal-pkg-card__cta" data-pkg-gallery='<?php echo esc_attr( $gallery_json ); ?>'><?php esc_html_e( 'View Details', 'nashua-pal-core' ); ?> <i class="fas fa-search-plus"></i></button>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
