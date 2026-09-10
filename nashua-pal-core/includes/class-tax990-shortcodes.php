<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcodes and template overrides for Tax 990 Documents.
 *
 * [tax990_documents year="" show_filter="true" columns="3" count="50" eyebrow=""]
 *
 * @package NashuaPalCore
 * @since   1.40.1
 */
final class NPAL_Tax990_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'tax990_documents', array( $this, 'documents' ) );

		add_filter( 'single_template',  array( $this, 'single_template' ) );
		add_filter( 'archive_template', array( $this, 'archive_template' ) );
	}

	/* ================================================================== */
	/*  TEMPLATE OVERRIDES                                                 */
	/* ================================================================== */

	public function single_template( $template ) {
		if ( is_singular( NPAL_CPT_Tax990::POST_TYPE ) ) {
			$plugin_tmpl = NPAL_PLUGIN_DIR . 'templates/single-tax990.php';
			if ( file_exists( $plugin_tmpl ) ) {
				return $plugin_tmpl;
			}
		}
		return $template;
	}

	public function archive_template( $template ) {
		if ( is_post_type_archive( NPAL_CPT_Tax990::POST_TYPE ) ) {
			$plugin_tmpl = NPAL_PLUGIN_DIR . 'templates/archive-tax990.php';
			if ( file_exists( $plugin_tmpl ) ) {
				return $plugin_tmpl;
			}
		}
		return $template;
	}

	/* ================================================================== */
	/*  [tax990_documents]                                                */
	/* ================================================================== */

	public function documents( $atts ) {
		$a = shortcode_atts( array(
			'year'        => '',
			'show_filter' => 'true',
			'columns'     => '3',
			'count'       => '50',
			'eyebrow'     => '',
		), $atts, 'tax990_documents' );

		$year        = sanitize_text_field( $a['year'] );
		$show_filter = 'true' === $a['show_filter'];
		$columns     = max( 1, min( 4, (int) $a['columns'] ) );
		$count       = max( 1, (int) $a['count'] );
		$eyebrow     = sanitize_text_field( $a['eyebrow'] );

		$meta_query = array();
		if ( $year ) {
			$meta_query[] = array( 'key' => '_pal_tax990_year', 'value' => $year );
		}

		$query_args = array(
			'post_type'      => NPAL_CPT_Tax990::POST_TYPE,
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'meta_key'       => '_pal_tax990_order',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		);
		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $query_args );
		if ( ! $query->have_posts() ) {
			return '<p class="pal-tax990-empty">' . esc_html__( 'No documents found.', 'nashua-pal-core' ) . '</p>';
		}

		$items = array();
		$years = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$pid    = get_the_ID();
			$pdf_id = get_post_meta( $pid, '_pal_tax990_pdf_id', true );
			$image   = get_the_post_thumbnail_url( $pid, 'large' );
			if ( ! $image && $pdf_id ) {
				$image = wp_get_attachment_image_url( $pdf_id, 'full' );
			}
			$item   = array(
				'id'          => $pid,
				'title'       => get_the_title( $pid ),
				'year'        => get_post_meta( $pid, '_pal_tax990_year', true ),
				'description' => get_post_meta( $pid, '_pal_tax990_description', true ),
				'image'       => $image,
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

		echo '<div class="pal-tax990-section">';

		if ( $eyebrow ) {
			echo '<span class="pal-tax990-section__eyebrow">' . esc_html( $eyebrow ) . '</span>';
		}

		// Year filter tabs.
		if ( $show_filter && count( $years ) > 1 ) {
			echo '<div class="pal-tax990-filter" role="tablist">';
			echo '<button type="button" class="pal-tax990-filter__btn is-active" data-tax990-filter="all">' . esc_html__( 'All Years', 'nashua-pal-core' ) . '</button>';
			foreach ( $years as $yr ) {
				echo '<button type="button" class="pal-tax990-filter__btn" data-tax990-filter="' . esc_attr( $yr ) . '">' . esc_html( $yr ) . '</button>';
			}
			echo '</div>';
		}

		echo '<div class="pal-tax990-grid pal-tax990-grid--cols-' . esc_attr( $columns ) . '">';
		foreach ( $items as $item ) {
			$this->render_card( $item );
		}
		echo '</div>';

		echo '</div>';

		// PDF viewer modal (once per page).
		static $modal_printed = false;
		if ( ! $modal_printed ) {
			$modal_printed = true;
			?>
			<div class="pal-tax990-modal" id="pal-tax990-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Document Viewer', 'nashua-pal-core' ); ?>">
				<div class="pal-tax990-modal__overlay"></div>
				<div class="pal-tax990-modal__container">
					<div class="pal-tax990-modal__header">
						<h3 class="pal-tax990-modal__title"></h3>
						<button type="button" class="pal-tax990-modal__close" aria-label="<?php esc_attr_e( 'Close', 'nashua-pal-core' ); ?>">&times;</button>
					</div>
					<div class="pal-tax990-modal__body">
						<iframe class="pal-tax990-modal__frame" src="about:blank"></iframe>
					</div>
				</div>
			</div>
			<?php
		}

		return ob_get_clean();
	}

	/* ------------------------------------------------------------------ */
	/*  Card renderer                                                      */
	/* ------------------------------------------------------------------ */

	private function render_card( $item ) {
		?>
		<div class="pal-tax990-card" data-tax990-year="<?php echo esc_attr( $item['year'] ); ?>">
			<div class="pal-tax990-card__media">
				<?php if ( $item['image'] ) : ?>
					<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" class="pal-tax990-card__cover" />
				<?php else : ?>
					<div class="pal-tax990-card__icon">
						<i class="fas fa-file-pdf"></i>
					</div>
				<?php endif; ?>
				<?php if ( $item['year'] ) : ?>
					<span class="pal-tax990-card__year-badge"><?php echo esc_html( $item['year'] ); ?></span>
				<?php endif; ?>
			</div>
			<div class="pal-tax990-card__body">
				<h3 class="pal-tax990-card__title"><?php echo esc_html( $item['title'] ); ?></h3>
				<?php if ( $item['description'] ) : ?>
					<p class="pal-tax990-card__desc"><?php echo esc_html( wp_trim_words( $item['description'], 20, '&hellip;' ) ); ?></p>
				<?php endif; ?>
				<div class="pal-tax990-card__actions">
					<?php if ( $item['pdf_url'] ) : ?>
						<button type="button" class="pal-tax990-card__btn pal-tax990-card__btn--view" data-tax990-pdf="<?php echo esc_url( $item['pdf_url'] ); ?>" data-tax990-title="<?php echo esc_attr( $item['title'] ); ?>">
							<i class="fas fa-eye"></i> <?php esc_html_e( 'View Document', 'nashua-pal-core' ); ?>
						</button>
						<a href="<?php echo esc_url( $item['pdf_url'] ); ?>" class="pal-tax990-card__btn pal-tax990-card__btn--download" download>
							<i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'nashua-pal-core' ); ?>
						</a>
					<?php else : ?>
						<a href="<?php echo esc_url( $item['permalink'] ); ?>" class="pal-tax990-card__btn pal-tax990-card__btn--view">
							<i class="fas fa-eye"></i> <?php esc_html_e( 'View Document', 'nashua-pal-core' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}
}
