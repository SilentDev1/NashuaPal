<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcodes for Nashua PAL Scholarships.
 *
 * [pal_scholarships max_years="10" count_per_year="20"]
 * [pal_scholarship_year year="2026"]
 *
 * @package NashuaPalCore
 * @since   1.29.0
 */
final class NPAL_Scholar_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'pal_scholarships',     array( $this, 'all_years' ) );
		add_shortcode( 'pal_scholarship_year', array( $this, 'single_year' ) );

		add_filter( 'archive_template', array( $this, 'archive_template' ) );
	}

	/* ================================================================== */
	/*  Archive template override                                          */
	/* ================================================================== */

	public function archive_template( $template ) {
		if ( is_post_type_archive( NPAL_CPT_Scholar::POST_TYPE ) ) {
			$plugin_tmpl = NPAL_PLUGIN_DIR . 'templates/archive-scholar.php';
			if ( file_exists( $plugin_tmpl ) ) {
				return $plugin_tmpl;
			}
		}
		return $template;
	}

	/* ================================================================== */
	/*  Helpers                                                            */
	/* ================================================================== */

	private function get_years() {
		global $wpdb;
		return $wpdb->get_col( $wpdb->prepare(
			"SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			 WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'
			   AND pm.meta_value != ''
			 ORDER BY pm.meta_value DESC",
			'_pal_scholar_year',
			NPAL_CPT_Scholar::POST_TYPE
		) );
	}

	private function query_year( $year, $count = 50 ) {
		return new WP_Query( array(
			'post_type'      => NPAL_CPT_Scholar::POST_TYPE,
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'meta_key'       => '_pal_scholar_order',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array(
				array( 'key' => '_pal_scholar_year', 'value' => $year ),
			),
		) );
	}

	private static $modal_rendered = false;

	private function render_card( $post_id, $program_data = array() ) {
		$name   = get_the_title( $post_id );
		$amount = get_post_meta( $post_id, '_pal_scholar_amount', true );
		$school = get_post_meta( $post_id, '_pal_scholar_school', true );
		$alumni = get_post_meta( $post_id, '_pal_scholar_alumni', true );
		$bio    = get_post_meta( $post_id, '_pal_scholar_bio', true );
		$img    = get_the_post_thumbnail_url( $post_id, 'medium' );
		$card_attrs = '';
		$card_attrs .= ' data-winner-name="'   . esc_attr( $name )   . '"';
		$card_attrs .= ' data-winner-bio="'    . esc_attr( $bio )    . '"';
		$card_attrs .= ' data-winner-amount="' . esc_attr( $amount ) . '"';
		$card_attrs .= ' data-winner-school="' . esc_attr( $school ) . '"';
		$card_attrs .= ' data-winner-alumni="' . esc_attr( $alumni ) . '"';
		if ( $img ) {
			$card_attrs .= ' data-winner-photo="' . esc_attr( esc_url( $img ) ) . '"';
		}
		?>
		<div class="pal-scholar-card"<?php echo $card_attrs; ?>>
			<div class="pal-scholar-card__photo">
				<?php if ( $img ) : ?>
					<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" />
				<?php else : ?>
					<span class="pal-scholar-card__placeholder"><i class="fas fa-user-graduate"></i></span>
				<?php endif; ?>
			</div>
			<h3 class="pal-scholar-card__name"><?php echo esc_html( $name ); ?></h3>
			<?php if ( $alumni ) : ?>
				<p class="pal-scholar-card__alumni"><?php echo esc_html( $alumni ); ?></p>
			<?php endif; ?>
			<?php if ( $amount ) : ?>
				<span class="pal-scholar-card__amount"><?php echo esc_html( $amount ); ?></span>
			<?php endif; ?>
			<?php if ( $school ) : ?>
				<p class="pal-scholar-card__school"><?php echo esc_html( $school ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Get all published scholarship programs, ordered by year desc then title.
	 */
	private function get_all_programs() {
		$programs = get_posts( array(
			'post_type'      => NPAL_CPT_Scholarship::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );

		// Sort: year DESC, then non-Trades before Trades within same year.
		usort( $programs, function ( $a, $b ) {
			$ya = (int) get_post_meta( $a->ID, '_pal_scholarship_year', true );
			$yb = (int) get_post_meta( $b->ID, '_pal_scholarship_year', true );
			if ( $ya !== $yb ) {
				return $yb - $ya; // newest year first
			}
			// Within same year: titles without "Trades" come first.
			$a_is_trades = (int) ( stripos( $a->post_title, 'trades' ) !== false );
			$b_is_trades = (int) ( stripos( $b->post_title, 'trades' ) !== false );
			return $a_is_trades - $b_is_trades;
		} );

		return $programs;
	}

	/**
	 * Query recipients linked to a specific scholarship program.
	 */
	private function query_by_program( $program_id, $count = 50 ) {
		return new WP_Query( array(
			'post_type'      => NPAL_CPT_Scholar::POST_TYPE,
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'meta_key'       => '_pal_scholar_order',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array(
				array( 'key' => '_pal_scholar_scholarship', 'value' => (string) $program_id ),
			),
		) );
	}

	/**
	 * Derive the badge label from a program name by stripping the year number.
	 * e.g. "2026 Scholarship" → "SCHOLARSHIP",
	 *      "2024 Trades Scholarship" → "TRADES SCHOLARSHIP".
	 */
	private function badge_label_from_name( $name, $year ) {
		$label = trim( str_replace( (string) $year, '', $name ) );
		if ( '' === $label ) {
			$label = 'Scholarship';
		}
		return strtoupper( $label );
	}

	private function render_modal() {
		if ( self::$modal_rendered ) { return; }
		self::$modal_rendered = true;
		?>
		<div id="pal-scholarship-modal" class="pal-scholarship-modal" style="display:none;">
			<div class="pal-scholarship-modal__overlay"></div>
			<div class="pal-scholarship-modal__dialog">
				<button type="button" class="pal-scholarship-modal__close">&times;</button>
				<div class="pal-scholarship-modal__photo-wrap"></div>
				<div class="pal-scholarship-modal__header">
					<i class="fas fa-graduation-cap pal-scholarship-modal__icon"></i>
					<h3 class="pal-scholarship-modal__title"></h3>
				</div>
				<div class="pal-scholarship-modal__meta"></div>
				<div class="pal-scholarship-modal__body"></div>
			</div>
		</div>
		<?php
	}

	/* ================================================================== */
	/*  [pal_scholarships] — All years grouped                             */
	/* ================================================================== */

	public function all_years( $atts ) {
		$a = shortcode_atts( array(
			'max_years'      => '10',
			'count_per_year' => '20',
		), $atts, 'pal_scholarships' );

		$count_per_year = max( 1, (int) $a['count_per_year'] );

		// Try program-based grouping first (one section per scholarship program).
		$programs = $this->get_all_programs();

		if ( ! empty( $programs ) ) {
			return $this->render_by_program( $programs, $count_per_year );
		}

		// Fallback: year-based grouping for sites without programs.
		$max_years = max( 1, (int) $a['max_years'] );
		$years     = $this->get_years();
		if ( empty( $years ) ) {
			return '<p class="pal-scholar-empty">' . esc_html__( 'No scholarship recipients found.', 'nashua-pal-core' ) . '</p>';
		}

		$years = array_slice( $years, 0, $max_years );

		ob_start();
		echo '<div class="pal-scholar-years">';

		foreach ( $years as $idx => $year ) {
			$query = $this->query_year( $year, $count_per_year );
			if ( ! $query->have_posts() ) { continue; }

			$alt_class = ( $idx % 2 === 1 ) ? ' pal-scholar-year--alt' : '';
			?>
			<div class="pal-scholar-year<?php echo esc_attr( $alt_class ); ?>">
				<div class="pal-scholar-year__inner">
					<div class="pal-scholar-year__badge">
						<div class="pal-scholar-year__badge-icon"><i class="fas fa-shield-alt"></i></div>
						<span class="pal-scholar-year__badge-label">SCHOLARSHIP</span>
						<span class="pal-scholar-year__badge-year"><?php echo esc_html( $year ); ?></span>
						<span class="pal-scholar-year__badge-label">RECIPIENTS</span>
						<div class="pal-scholar-year__badge-dots">
							<span></span><span></span><span></span>
						</div>
					</div>
					<div class="pal-scholar-year__content">
						<div class="pal-scholar-year__grid">
							<?php
							while ( $query->have_posts() ) {
								$query->the_post();
								$this->render_card( get_the_ID() );
							}
							wp_reset_postdata();
							?>
						</div>
						<div class="pal-scholar-year__footer">
							<?php $archive_url = get_post_type_archive_link( NPAL_CPT_Scholar::POST_TYPE ) ?: home_url( '/scholarships/' ); ?>
							<a href="<?php echo esc_url( $archive_url . '?scholar_year=' . $year ); ?>" class="pal-scholar-year__view-all">
								<?php printf( esc_html__( 'View All %s Recipients', 'nashua-pal-core' ), esc_html( $year ) ); ?> <i class="fas fa-arrow-right"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php
		}

		echo '</div>';
		$this->render_modal();
		return ob_get_clean();
	}

	/* ================================================================== */
	/*  Program-based rendering — one section per scholarship program       */
	/* ================================================================== */

	private function render_by_program( $programs, $count_per_year ) {
		ob_start();
		echo '<div class="pal-scholar-years">';

		$idx = 0;
		foreach ( $programs as $prog ) {
			$prog_year   = get_post_meta( $prog->ID, '_pal_scholarship_year', true );
			$prog_about  = get_post_meta( $prog->ID, '_pal_scholarship_about', true );
			$prog_amount = get_post_meta( $prog->ID, '_pal_scholarship_amount', true );

			$query = $this->query_by_program( $prog->ID, $count_per_year );
			if ( ! $query->have_posts() ) { continue; }

			$badge_label = $this->badge_label_from_name( $prog->post_title, $prog_year );
			$alt_class   = ( $idx % 2 === 1 ) ? ' pal-scholar-year--alt' : '';
			?>
			<div class="pal-scholar-year<?php echo esc_attr( $alt_class ); ?>">
				<div class="pal-scholar-year__inner">
					<div class="pal-scholar-year__badge">
						<div class="pal-scholar-year__badge-icon"><i class="fas fa-shield-alt"></i></div>
						<span class="pal-scholar-year__badge-label"><?php echo esc_html( $badge_label ); ?></span>
						<span class="pal-scholar-year__badge-year"><?php echo esc_html( $prog_year ); ?></span>
						<span class="pal-scholar-year__badge-label">RECIPIENTS</span>
						<div class="pal-scholar-year__badge-dots">
							<span></span><span></span><span></span>
						</div>
						<?php if ( $prog_about ) : ?>
							<div class="pal-scholar-year__badge-programs">
								<button type="button" class="pal-scholar-year__about-btn"
									data-scholarship-name="<?php echo esc_attr( $prog->post_title ); ?>"
									data-scholarship-about="<?php echo esc_attr( $prog_about ); ?>"
									data-scholarship-amount="<?php echo esc_attr( $prog_amount ); ?>"
									data-scholarship-year="<?php echo esc_attr( $prog_year ); ?>">
									<?php esc_html_e( 'About Awardees', 'nashua-pal-core' ); ?>
								</button>
							</div>
						<?php endif; ?>
					</div>
					<div class="pal-scholar-year__content">
						<div class="pal-scholar-year__grid">
							<?php
							while ( $query->have_posts() ) {
								$query->the_post();
								$this->render_card( get_the_ID() );
							}
							wp_reset_postdata();
							?>
						</div>
						<div class="pal-scholar-year__footer">
							<?php $archive_url = get_post_type_archive_link( NPAL_CPT_Scholar::POST_TYPE ) ?: home_url( '/scholarships/' ); ?>
							<a href="<?php echo esc_url( $archive_url . '?scholar_year=' . $prog_year ); ?>" class="pal-scholar-year__view-all">
								<?php printf( esc_html__( 'View All %s Recipients', 'nashua-pal-core' ), esc_html( $prog_year ) ); ?> <i class="fas fa-arrow-right"></i>
							</a>
						</div>
					</div>
				</div>
			</div>
			<?php
			$idx++;
		}

		echo '</div>';
		$this->render_modal();
		return ob_get_clean();
	}

	/* ================================================================== */
	/*  [pal_scholarship_year year="2026"] — Single year grid              */
	/* ================================================================== */

	public function single_year( $atts ) {
		$a = shortcode_atts( array(
			'year' => '',
		), $atts, 'pal_scholarship_year' );

		$year = sanitize_text_field( $a['year'] );
		if ( ! $year ) { $year = gmdate( 'Y' ); }

		$query = $this->query_year( $year, 100 );
		if ( ! $query->have_posts() ) {
			return '<p class="pal-scholar-empty">' . sprintf( esc_html__( 'No scholarship recipients found for %s.', 'nashua-pal-core' ), esc_html( $year ) ) . '</p>';
		}

		ob_start();
		echo '<div class="pal-scholar-single-year">';
		echo '<h2 class="pal-scholar-single-year__title">' . esc_html( $year ) . ' ' . esc_html__( 'Scholarship Recipients', 'nashua-pal-core' ) . '</h2>';
		echo '<div class="pal-scholar-year__grid">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$this->render_card( get_the_ID() );
		}
		wp_reset_postdata();
		echo '</div>';
		echo '</div>';
		$this->render_modal();
		return ob_get_clean();
	}
}
