<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcodes and template overrides for Impact Reports.
 *
 * [impact_reports year="" show_filter="true" columns="3" count="50" eyebrow=""]
 *
 * @package NashuaPalCore
 * @since   1.34.0
 */
final class NPAL_Impact_Report_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'impact_reports',         array( $this, 'reports' ) );
		add_shortcode( 'impact_report_carousel', array( $this, 'report_carousel' ) );

		add_filter( 'single_template',  array( $this, 'single_template' ) );
		add_filter( 'archive_template', array( $this, 'archive_template' ) );
	}

	/* ================================================================== */
	/*  TEMPLATE OVERRIDES                                                 */
	/* ================================================================== */

	public function single_template( $template ) {
		if ( is_singular( NPAL_CPT_Impact_Report::POST_TYPE ) ) {
			$plugin_tmpl = NPAL_PLUGIN_DIR . 'templates/single-impact-report.php';
			if ( file_exists( $plugin_tmpl ) ) {
				return $plugin_tmpl;
			}
		}
		return $template;
	}

	public function archive_template( $template ) {
		if ( is_post_type_archive( NPAL_CPT_Impact_Report::POST_TYPE ) ) {
			$plugin_tmpl = NPAL_PLUGIN_DIR . 'templates/archive-impact-report.php';
			if ( file_exists( $plugin_tmpl ) ) {
				return $plugin_tmpl;
			}
		}
		return $template;
	}

	/* ================================================================== */
	/*  [impact_reports]                                                  */
	/* ================================================================== */

	public function reports( $atts ) {
		$a = shortcode_atts( array(
			'year'        => '',
			'show_filter' => 'true',
			'columns'     => '3',
			'count'       => '50',
			'eyebrow'     => '',
		), $atts, 'impact_reports' );

		$year        = sanitize_text_field( $a['year'] );
		$show_filter = 'true' === $a['show_filter'];
		$columns     = max( 1, min( 4, (int) $a['columns'] ) );
		$count       = max( 1, (int) $a['count'] );
		$eyebrow     = sanitize_text_field( $a['eyebrow'] );

		$meta_query = array();
		if ( $year ) {
			$meta_query[] = array( 'key' => '_pal_report_year', 'value' => $year );
		}

		$query_args = array(
			'post_type'      => NPAL_CPT_Impact_Report::POST_TYPE,
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'meta_key'       => '_pal_report_order',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		);
		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $query_args );
		if ( ! $query->have_posts() ) {
			return '<p class="pal-report-empty">' . esc_html__( 'No reports found.', 'nashua-pal-core' ) . '</p>';
		}

		$items = array();
		$years = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$pid    = get_the_ID();
			$pdf_id = get_post_meta( $pid, '_pal_report_pdf_id', true );
			$item   = array(
				'id'          => $pid,
				'title'       => get_the_title( $pid ),
				'year'        => get_post_meta( $pid, '_pal_report_year', true ),
				'description' => get_post_meta( $pid, '_pal_report_description', true ),
				'image'       => get_the_post_thumbnail_url( $pid, 'large' ),
				'permalink'   => get_permalink( $pid ),
				'pdf_url'     => $pdf_id ? wp_get_attachment_url( $pdf_id ) : '',
			);
			$items[] = $item;

			if ( $item['year'] && ! in_array( $item['year'], $years, true ) ) {
				$years[] = $item['year'];
			}
		}
		wp_reset_postdata();
		rsort( $years );

		ob_start();

		echo '<div class="pal-report-section">';

		if ( $eyebrow ) {
			echo '<span class="pal-report-section__eyebrow">' . esc_html( $eyebrow ) . '</span>';
		}

		// Year filter tabs.
		if ( $show_filter && count( $years ) > 1 ) {
			echo '<div class="pal-report-filter" role="tablist">';
			echo '<button type="button" class="pal-report-filter__btn is-active" data-report-filter="all">' . esc_html__( 'All Years', 'nashua-pal-core' ) . '</button>';
			foreach ( $years as $yr ) {
				echo '<button type="button" class="pal-report-filter__btn" data-report-filter="' . esc_attr( $yr ) . '">' . esc_html( $yr ) . '</button>';
			}
			echo '</div>';
		}

		echo '<div class="pal-report-grid pal-report-grid--cols-' . esc_attr( $columns ) . '">';
		foreach ( $items as $item ) {
			$this->render_report_card( $item );
		}
		echo '</div>';

		echo '</div>';

		return ob_get_clean();
	}

	/* ------------------------------------------------------------------ */
	/*  Card renderer                                                      */
	/* ------------------------------------------------------------------ */

	/* ================================================================== */
	/*  [impact_report_carousel]                                          */
	/* ================================================================== */

	public function report_carousel( $atts ) {
		$a = shortcode_atts( array(
			'count' => '12',
			'order' => 'ASC',
		), $atts, 'impact_report_carousel' );

		$count = max( 1, (int) $a['count'] );
		$order = strtoupper( $a['order'] ) === 'DESC' ? 'DESC' : 'ASC';

		$query = new WP_Query( array(
			'post_type'      => NPAL_CPT_Impact_Report::POST_TYPE,
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'meta_key'       => '_pal_report_order',
			'orderby'        => 'meta_value_num',
			'order'          => $order,
		) );

		if ( ! $query->have_posts() ) {
			return '<p class="pal-report-empty">' . esc_html__( 'No reports found.', 'nashua-pal-core' ) . '</p>';
		}

		$items = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$pid     = get_the_ID();
			$items[] = array(
				'title'     => get_the_title( $pid ),
				'year'      => get_post_meta( $pid, '_pal_report_year', true ),
				'image'     => get_the_post_thumbnail_url( $pid, 'large' ),
				'permalink' => get_permalink( $pid ),
			);
		}
		wp_reset_postdata();

		$total   = count( $items );
		$uid     = 'pal-irc-' . wp_unique_id();
		$has_nav = $total > 4;

		ob_start();
		?>
		<div class="pal-irc" id="<?php echo esc_attr( $uid ); ?>">
			<?php if ( $has_nav ) : ?>
				<button type="button" class="pal-irc__arrow pal-irc__arrow--prev" aria-label="<?php esc_attr_e( 'Previous', 'nashua-pal-core' ); ?>" style="display:none;">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			<?php endif; ?>

			<div class="pal-irc__viewport">
				<div class="pal-irc__track">
					<?php foreach ( $items as $item ) : ?>
						<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="pal-irc__card">
							<div class="pal-irc__media">
								<?php if ( $item['image'] ) : ?>
									<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy" />
								<?php else : ?>
									<div class="pal-irc__placeholder"><i class="fas fa-file-pdf"></i></div>
								<?php endif; ?>
								<?php if ( $item['year'] ) : ?>
									<span class="pal-report-card__year-badge"><?php echo esc_html( $item['year'] ); ?></span>
								<?php endif; ?>
							</div>
							<span class="pal-irc__title"><?php echo esc_html( $item['title'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $has_nav ) : ?>
				<button type="button" class="pal-irc__arrow pal-irc__arrow--next" aria-label="<?php esc_attr_e( 'Next', 'nashua-pal-core' ); ?>">
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
				<script>
				(function(){
					var root  = document.getElementById('<?php echo esc_js( $uid ); ?>');
					var track = root.querySelector('.pal-irc__track');
					var prev  = root.querySelector('.pal-irc__arrow--prev');
					var next  = root.querySelector('.pal-irc__arrow--next');
					var pos   = 0;
					var total = <?php echo (int) $total; ?>;

					function visibleCount(){
						var w = root.offsetWidth;
						if(w<=480) return 1;
						if(w<=768) return 2;
						if(w<=1024) return 3;
						return 4;
					}
					function maxPos(){ return Math.max(0, total - visibleCount()); }
					function update(){
						var pct = (100 / visibleCount());
						track.style.transform = 'translateX(-' + (pos * pct) + '%)';
						prev.style.display = pos <= 0 ? 'none' : '';
						next.style.display = pos >= maxPos() ? 'none' : '';
					}
					prev.addEventListener('click', function(){ if(pos>0){ pos--; update(); } });
					next.addEventListener('click', function(){ if(pos<maxPos()){ pos++; update(); } });
					window.addEventListener('resize', function(){ if(pos>maxPos()){ pos=maxPos(); } update(); });
					update();
				})();
				</script>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/* ------------------------------------------------------------------ */
	/*  Card renderer                                                      */
	/* ------------------------------------------------------------------ */

	private function render_report_card( $item ) {
		?>
		<div class="pal-report-card" data-report-year="<?php echo esc_attr( $item['year'] ); ?>">
			<div class="pal-report-card__media">
				<?php if ( $item['image'] ) : ?>
					<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" class="pal-report-card__cover" />
				<?php else : ?>
					<div class="pal-report-card__icon">
						<i class="fas fa-file-pdf"></i>
					</div>
				<?php endif; ?>
				<?php if ( $item['year'] ) : ?>
					<span class="pal-report-card__year-badge"><?php echo esc_html( $item['year'] ); ?></span>
				<?php endif; ?>
			</div>
			<div class="pal-report-card__body">
				<h3 class="pal-report-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
				<?php if ( $item['description'] ) : ?>
					<p class="pal-report-card__desc"><?php echo esc_html( wp_trim_words( $item['description'], 20, '&hellip;' ) ); ?></p>
				<?php endif; ?>
				<div class="pal-report-card__actions">
					<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="pal-report-card__btn pal-report-card__btn--view">
						<i class="fas fa-eye"></i> <?php esc_html_e( 'View Report', 'nashua-pal-core' ); ?>
					</a>
					<?php if ( $item['pdf_url'] ) : ?>
						<a href="<?php echo esc_url( $item['pdf_url'] ); ?>" class="pal-report-card__btn pal-report-card__btn--download" download>
							<i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'nashua-pal-core' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
