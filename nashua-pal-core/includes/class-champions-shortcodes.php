<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcodes and template overrides for Night of Champions.
 *
 * [champions_winners year="2025" category="" show_filter="true" columns="3" count="50" featured_only="false"]
 * [champions_sponsors year="2025"]
 *
 * @package NashuaPalCore
 * @since   1.15.0
 */
final class NPAL_Champions_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'champions_winners',  array( $this, 'winners' ) );
		add_shortcode( 'champions_sponsors', array( $this, 'sponsors' ) );

		// Template overrides for single + archive.
		add_filter( 'single_template', array( $this, 'single_template' ) );
		add_filter( 'archive_template', array( $this, 'archive_template' ) );
	}

	/* ================================================================== */
	/*  TEMPLATE OVERRIDES                                                 */
	/* ================================================================== */

	public function single_template( $template ) {
		if ( is_singular( NPAL_CPT_Champions_Winner::POST_TYPE ) ) {
			$plugin_tmpl = NPAL_PLUGIN_DIR . 'templates/single-champions-winner.php';
			if ( file_exists( $plugin_tmpl ) ) {
				return $plugin_tmpl;
			}
		}
		return $template;
	}

	public function archive_template( $template ) {
		if ( is_post_type_archive( NPAL_CPT_Champions_Winner::POST_TYPE ) ) {
			$plugin_tmpl = NPAL_PLUGIN_DIR . 'templates/archive-champions-winner.php';
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
		if ( $url ) { return $url; }
		$external = get_post_meta( $post_id, '_thumbnail_external_url', true );
		return $external ? esc_url_raw( $external ) : '';
	}

	private function get_winner_name( $post_id ) {
		$name = get_post_meta( $post_id, '_pal_champion_name', true );
		return $name ? $name : get_the_title( $post_id );
	}

	private function get_winner_bio( $post_id ) {
		$bio = get_post_meta( $post_id, '_pal_champion_bio', true );
		if ( $bio ) { return $bio; }
		$excerpt = get_the_excerpt( $post_id );
		return $excerpt ? $excerpt : '';
	}

	/**
	 * Get category data for a post from taxonomy.
	 * Returns array with slug, label, badge_style keys or empty array.
	 */
	private function get_category_data( $post_id ) {
		$term = NPAL_CPT_Champions_Winner::get_award_category( $post_id );
		if ( ! $term ) { return array( 'slug' => '', 'label' => '', 'badge_style' => '' ); }
		return array(
			'slug'        => $term->slug,
			'label'       => $term->name,
			'badge_style' => NPAL_CPT_Champions_Winner::get_badge_style( $term ),
		);
	}

	/* ================================================================== */
	/*  [champions_winners]                                                */
	/* ================================================================== */

	public function winners( $atts ) {
		$a = shortcode_atts( array(
			'year'               => '',
			'category'           => '',
			'show_filter'        => 'true',
			'columns'            => '3',
			'count'              => '50',
			'featured_only'      => 'false',
			'source'             => 'both', // both | featured | recipients
			'featured_eyebrow'   => '',
			'recipients_eyebrow' => '',
			'view_all_url'       => '',
			'view_all_label'     => '',
			'rotate'             => 'false',
			'rotate_visible'     => '6',
			'rotate_interval'    => '5000',
		), $atts, 'champions_winners' );

		$year               = sanitize_text_field( $a['year'] );
		$category           = sanitize_text_field( $a['category'] );
		$show_filter        = 'true' === $a['show_filter'];
		$columns            = max( 1, min( 4, (int) $a['columns'] ) );
		$count              = max( 1, (int) $a['count'] );
		$featured_only      = 'true' === $a['featured_only'];
		$source             = $a['source'];
		$featured_eyebrow   = sanitize_text_field( $a['featured_eyebrow'] );
		$recipients_eyebrow = sanitize_text_field( $a['recipients_eyebrow'] );
		$view_all_url       = esc_url( $a['view_all_url'] );
		$view_all_label     = sanitize_text_field( $a['view_all_label'] );
		$rotate             = 'true' === $a['rotate'];
		$rotate_visible     = max( 3, (int) $a['rotate_visible'] );
		$rotate_interval    = max( 2000, (int) $a['rotate_interval'] );

		// When rotation is enabled, query all winners so every one gets shown.
		if ( $rotate ) {
			$count = 200;
		}

		// Build query args.
		$meta_query = array();
		$tax_query  = array();
		if ( $year ) {
			$meta_query[] = array( 'key' => '_pal_champion_year', 'value' => $year );
		}
		if ( $category ) {
			$tax_query[] = array(
				'taxonomy' => NPAL_CPT_Champions_Winner::TAXONOMY,
				'field'    => 'slug',
				'terms'    => $category,
			);
		}
		if ( $featured_only ) {
			$meta_query[] = array( 'key' => '_pal_champion_featured', 'value' => '1' );
		}

		$query_args = array(
			'post_type'      => NPAL_CPT_Champions_Winner::POST_TYPE,
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'meta_key'       => '_pal_champion_order',
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
			return '<p class="pal-champ-empty">' . esc_html__( 'No champions found.', 'nashua-pal-core' ) . '</p>';
		}

		// Separate featured vs regular.
		$featured = array();
		$regular  = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$pid     = get_the_ID();
			$catdata = $this->get_category_data( $pid );
			$item    = array(
				'id'           => $pid,
				'name'         => $this->get_winner_name( $pid ),
				'award_title'  => get_post_meta( $pid, '_pal_champion_award_title', true ),
				'year'         => get_post_meta( $pid, '_pal_champion_year', true ),
				'category'     => $catdata['slug'],
				'cat_label'    => $catdata['label'],
				'badge_style'  => $catdata['badge_style'],
				'bio'          => $this->get_winner_bio( $pid ),
				'organization' => get_post_meta( $pid, '_pal_champion_organization', true ),
				'sport'        => get_post_meta( $pid, '_pal_champion_sport', true ),
				'image'        => $this->get_featured_image( $pid ),
				'permalink'    => get_permalink( $pid ),
				'featured'     => '1' === get_post_meta( $pid, '_pal_champion_featured', true ),
				'video_url'    => get_post_meta( $pid, '_pal_champion_video_url', true ),
			);
			if ( $item['featured'] ) {
				$featured[] = $item;
			} else {
				$regular[] = $item;
			}
		}
		wp_reset_postdata();

		// Collect categories for filter tabs from taxonomy.
		$all_items   = array_merge( $featured, $regular );
		$categories  = array();
		foreach ( $all_items as $item ) {
			if ( $item['category'] && ! isset( $categories[ $item['category'] ] ) ) {
				$categories[ $item['category'] ] = $item['cat_label'];
			}
		}

		ob_start();

		$display_year = $year ? $year : '';

		// Featured Champions section.
		if ( 'recipients' !== $source && ! empty( $featured ) ) {
			echo '<div class="pal-champ-section pal-champ-featured-section">';
			if ( $featured_eyebrow ) {
				echo '<span class="pal-champ-section__eyebrow">' . esc_html( $featured_eyebrow ) . '</span>';
			}
			if ( $display_year ) {
				echo '<h2 class="pal-champ-section__title">' . esc_html( $display_year ) . ' ' . esc_html__( 'Honorees', 'nashua-pal-core' ) . '</h2>';
			}
			echo '<div class="pal-champ-featured-grid">';
			foreach ( $featured as $item ) {
				$this->render_featured_card( $item );
			}
			echo '</div>';
			echo '</div>';
		}

		// Award Recipients section.
		if ( 'featured' !== $source && ! empty( $regular ) ) {
			echo '<div class="pal-champ-section pal-champ-recipients-section">';
			if ( $recipients_eyebrow ) {
				echo '<span class="pal-champ-section__eyebrow">' . esc_html( $recipients_eyebrow ) . '</span>';
			}
			if ( $display_year ) {
				echo '<h2 class="pal-champ-section__title">' . esc_html( $display_year ) . ' ' . esc_html__( 'Award Recipients', 'nashua-pal-core' ) . '</h2>';
			}

			// Filter tabs.
			if ( $show_filter && count( $categories ) > 1 ) {
				echo '<div class="pal-champ-filter" role="tablist">';
				echo '<button type="button" class="pal-champ-filter__btn is-active" data-champ-filter="all">' . esc_html__( 'All', 'nashua-pal-core' ) . '</button>';
				foreach ( $categories as $slug => $label ) {
					echo '<button type="button" class="pal-champ-filter__btn" data-champ-filter="' . esc_attr( $slug ) . '">' . esc_html( $label ) . '</button>';
				}
				echo '</div>';
			}

			// Rotation wrapper.
			$rotate_attrs = '';
			if ( $rotate && count( $regular ) > $rotate_visible ) {
				$rotate_attrs = ' data-champ-rotate="' . esc_attr( $rotate_visible ) . '" data-champ-rotate-interval="' . esc_attr( $rotate_interval ) . '"';
			}

			echo '<div class="pal-champ-grid pal-champ-grid--cols-' . esc_attr( $columns ) . '"' . $rotate_attrs . '>';
			foreach ( $regular as $item ) {
				$this->render_winner_card( $item );
			}
			echo '</div>';

			// Page indicator dots (rendered by JS when rotation is active).
			if ( $rotate && count( $regular ) > $rotate_visible ) {
				echo '<div class="pal-champ-rotate-dots"></div>';
			}

			if ( $view_all_url && $view_all_label ) {
				echo '<div class="pal-champ-section__footer"><a href="' . esc_url( $view_all_url ) . '" class="pal-champ-section__view-all">' . esc_html( $view_all_label ) . ' <i class="fas fa-arrow-right"></i></a></div>';
			}
			echo '</div>';
		}

		// If featured_only or source=featured and we only have featured, render those as grid.
		if ( 'featured' === $source && ! empty( $featured ) && empty( $regular ) ) {
			// Already rendered above.
		}

		return ob_get_clean();
	}

	/* ------------------------------------------------------------------ */
	/*  Card renderers                                                     */
	/* ------------------------------------------------------------------ */

	private function render_featured_card( $item ) {
		$img_style = $item['image'] ? 'background-image:url(' . esc_url( $item['image'] ) . ')' : '';
		?>
		<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="pal-champ-featured-card" data-champ-category="<?php echo esc_attr( $item['category'] ); ?>">
			<div class="pal-champ-featured-card__media" style="<?php echo esc_attr( $img_style ); ?>">
				<?php if ( $item['cat_label'] ) : ?>
					<span class="pal-champ-card__badge pal-champ-card__badge--<?php echo esc_attr( $item['category'] ); ?>" <?php if ( $item['badge_style'] ) { echo 'style="' . esc_attr( $item['badge_style'] ) . '"'; } ?>><?php echo esc_html( $item['cat_label'] ); ?></span>
				<?php endif; ?>
			</div>
			<div class="pal-champ-featured-card__body">
				<?php if ( $item['award_title'] ) : ?>
					<p class="pal-champ-featured-card__award"><?php echo esc_html( $item['award_title'] ); ?></p>
				<?php endif; ?>
				<h3 class="pal-champ-featured-card__name"><?php echo esc_html( $item['name'] ); ?></h3>
				<?php if ( $item['organization'] ) : ?>
					<p class="pal-champ-featured-card__org"><?php echo esc_html( $item['organization'] ); ?></p>
				<?php endif; ?>
			</div>
		</a>
		<?php
	}

	private function render_winner_card( $item ) {
		$is_legacy  = empty( $item['image'] ) && empty( $item['bio'] );
		$img_style  = $item['image'] ? 'background-image:url(' . esc_url( $item['image'] ) . ')' : '';
		$card_class = 'pal-champ-card' . ( $is_legacy ? ' pal-champ-card--legacy' : '' );
		$initials   = '';
		if ( $is_legacy && $item['name'] ) {
			$parts    = preg_split( '/[\s]+/', trim( $item['name'] ) );
			$initials = strtoupper( mb_substr( $parts[0], 0, 1 ) );
			if ( count( $parts ) > 1 ) {
				$initials .= strtoupper( mb_substr( end( $parts ), 0, 1 ) );
			}
		}
		?>
		<div class="<?php echo esc_attr( $card_class ); ?>" data-champ-category="<?php echo esc_attr( $item['category'] ); ?>">
			<?php if ( $is_legacy ) : ?>
				<div class="pal-champ-card__link">
					<div class="pal-champ-card__media" style="<?php echo esc_attr( $img_style ); ?>">
						<?php if ( $initials ) : ?>
							<span class="pal-champ-card__initials"><?php echo esc_html( $initials ); ?></span>
						<?php endif; ?>
						<?php if ( $item['cat_label'] ) : ?>
							<span class="pal-champ-card__badge pal-champ-card__badge--<?php echo esc_attr( $item['category'] ); ?>" <?php if ( $item['badge_style'] ) { echo 'style="' . esc_attr( $item['badge_style'] ) . '"'; } ?>><?php echo esc_html( $item['cat_label'] ); ?></span>
						<?php endif; ?>
					</div>
					<div class="pal-champ-card__body">
						<h3 class="pal-champ-card__name"><?php echo esc_html( $item['name'] ); ?></h3>
						<?php if ( $item['award_title'] ) : ?>
							<p class="pal-champ-card__award"><?php echo esc_html( $item['award_title'] ); ?></p>
						<?php endif; ?>
						<?php if ( $item['year'] ) : ?>
							<p class="pal-champ-card__year"><?php echo esc_html( $item['year'] ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php else : ?>
				<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="pal-champ-card__link">
					<div class="pal-champ-card__media" style="<?php echo esc_attr( $img_style ); ?>">
						<?php if ( $item['cat_label'] ) : ?>
							<span class="pal-champ-card__badge pal-champ-card__badge--<?php echo esc_attr( $item['category'] ); ?>" <?php if ( $item['badge_style'] ) { echo 'style="' . esc_attr( $item['badge_style'] ) . '"'; } ?>><?php echo esc_html( $item['cat_label'] ); ?></span>
						<?php endif; ?>
						<?php if ( $item['video_url'] ) : ?>
							<span class="pal-champ-card__play" aria-hidden="true"><i class="fas fa-play"></i></span>
						<?php endif; ?>
					</div>
					<div class="pal-champ-card__body">
						<h3 class="pal-champ-card__name"><?php echo esc_html( $item['name'] ); ?></h3>
						<?php if ( $item['award_title'] ) : ?>
							<p class="pal-champ-card__award"><?php echo esc_html( $item['award_title'] ); ?></p>
						<?php endif; ?>
						<?php if ( $item['bio'] ) : ?>
							<p class="pal-champ-card__bio"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $item['bio'] ), 20, '&hellip;' ) ); ?></p>
						<?php endif; ?>
						<span class="pal-champ-card__cta">Read Story <i class="fas fa-arrow-right"></i></span>
					</div>
				</a>
			<?php endif; ?>
		</div>
		<?php
	}

	/* ================================================================== */
	/*  [champions_sponsors]                                               */
	/* ================================================================== */

	public function sponsors( $atts ) {
		$a = shortcode_atts( array(
			'year'         => '',
			'heading'      => __( 'Thank You to Our Sponsors', 'nashua-pal-core' ),
			'scroll_speed' => '30',
		), $atts, 'champions_sponsors' );

		$year         = sanitize_text_field( $a['year'] );
		$scroll_speed = max( 5, min( 120, (int) $a['scroll_speed'] ) );

		$meta_query = array();
		if ( $year ) {
			$meta_query[] = array( 'key' => '_pal_champ_sponsor_year', 'value' => $year );
		}

		$query_args = array(
			'post_type'      => NPAL_CPT_Champions_Sponsor::POST_TYPE,
			'posts_per_page' => 100,
			'post_status'    => 'publish',
			'meta_key'       => '_pal_champ_sponsor_order',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		);
		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $query_args );
		if ( ! $query->have_posts() ) { return ''; }

		// Get ordered sponsor levels from taxonomy.
		$level_terms = NPAL_CPT_Champions_Sponsor::get_ordered_levels();

		// Build badge style lookup per level.
		$level_badge_styles = array();
		foreach ( $level_terms as $lt ) {
			$level_badge_styles[ $lt->slug ] = NPAL_CPT_Champions_Sponsor::get_level_badge_style( $lt );
		}

		// Group sponsors by level slug.
		$grouped = array();
		foreach ( $level_terms as $lt ) {
			$grouped[ $lt->slug ] = array( 'label' => $lt->name, 'items' => array() );
		}
		// Add a catch-all for sponsors with no level assigned.
		$grouped['_uncategorized'] = array( 'label' => __( 'Sponsors', 'nashua-pal-core' ), 'items' => array() );

		while ( $query->have_posts() ) {
			$query->the_post();
			$pid        = get_the_ID();
			$level_term = NPAL_CPT_Champions_Sponsor::get_sponsor_level( $pid );
			$level_slug = $level_term ? $level_term->slug : '_uncategorized';

			if ( ! isset( $grouped[ $level_slug ] ) ) {
				$grouped[ $level_slug ] = array( 'label' => $level_term->name, 'items' => array() );
			}

			$grouped[ $level_slug ]['items'][] = array(
				'name'        => get_the_title( $pid ),
				'logo'        => $this->get_featured_image( $pid, 'medium' ),
				'url'         => get_post_meta( $pid, '_pal_champ_sponsor_url', true ),
				'level'       => $level_slug,
				'level_label' => $level_term ? $level_term->name : '',
				'badge_style' => isset( $level_badge_styles[ $level_slug ] ) ? $level_badge_styles[ $level_slug ] : '',
			);
		}
		wp_reset_postdata();

		// Flatten all sponsors into one list for a single combined marquee.
		$all_logos = array();
		foreach ( $grouped as $group ) {
			foreach ( $group['items'] as $s ) {
				$all_logos[] = $s;
			}
		}

		ob_start();
		echo '<div class="pal-champ-sponsors" data-sponsor-filter-section>';
		if ( $a['heading'] ) {
			echo '<h2 class="pal-champ-sponsors__heading">' . esc_html( $a['heading'] ) . '</h2>';
		}

		// Single scrolling marquee with every logo + level badge.
		if ( ! empty( $all_logos ) ) {
			echo '<div class="pal-champ-sponsors__tier pal-champ-sponsors__tier--all" data-sponsor-level="all">';
			echo '<div class="pal-champ-sponsors__marquee" style="--pal-sponsor-speed:' . esc_attr( $scroll_speed ) . 's">';
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
