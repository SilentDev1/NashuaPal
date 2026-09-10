<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcode for Live Auction packages.
 *
 * [champions_auction year="2026" count="6" columns="3" heading="FEATURED AUCTION PACKAGES"
 *    view_all_url="/live-auction/" view_all_label="VIEW ALL LIVE AUCTION PACKAGES" featured_only="false"]
 *
 * @package NashuaPalCore
 * @since   1.28.0
 */
final class NPAL_Champions_Auction_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'champions_auction', array( $this, 'auction' ) );
	}

	/* ================================================================== */
	/*  [champions_auction]                                                */
	/* ================================================================== */

	public function auction( $atts ) {
		$a = shortcode_atts( array(
			'year'           => '',
			'count'          => '6',
			'columns'        => '3',
			'heading'        => 'FEATURED AUCTION PACKAGES',
			'view_all_url'   => '',
			'view_all_label' => 'VIEW ALL LIVE AUCTION PACKAGES',
			'featured_only'  => 'false',
		), $atts, 'champions_auction' );

		$year          = sanitize_text_field( $a['year'] );
		if ( '' === $year ) {
			$year = gmdate( 'Y' );
		}
		$count         = max( 1, (int) $a['count'] );
		$columns       = max( 1, min( 4, (int) $a['columns'] ) );
		$heading       = sanitize_text_field( $a['heading'] );
		$view_all_url  = esc_url( $a['view_all_url'] );
		$view_all_label = sanitize_text_field( $a['view_all_label'] );
		$featured_only = 'true' === $a['featured_only'];

		// Build query.
		$meta_query = array();
		if ( $year ) {
			$meta_query[] = array( 'key' => '_pal_auction_year', 'value' => $year );
		}
		if ( $featured_only ) {
			$meta_query[] = array( 'key' => '_pal_auction_featured', 'value' => '1' );
		}

		$query_args = array(
			'post_type'      => NPAL_CPT_Champions_Auction::POST_TYPE,
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'meta_key'       => '_pal_auction_order',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
		);
		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $query_args );
		if ( ! $query->have_posts() ) {
			return '<p class="pal-auction__empty">' . esc_html__( 'No auction packages found.', 'nashua-pal-core' ) . '</p>';
		}

		ob_start();
		echo '<div class="pal-auction">';

		if ( $heading ) {
			echo '<h2 class="pal-auction__heading">' . esc_html( $heading ) . '</h2>';
		}

		echo '<div class="pal-auction__grid pal-auction__grid--cols-' . esc_attr( $columns ) . '">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$pid   = get_the_ID();
			$value = get_post_meta( $pid, '_pal_auction_value', true );
			$img   = get_the_post_thumbnail_url( $pid, 'large' );
			$full  = get_the_post_thumbnail_url( $pid, 'full' );
			$desc  = get_the_excerpt( $pid );
			if ( ! $desc ) {
				$desc = wp_trim_words( wp_strip_all_tags( get_the_content( null, false, $pid ) ), 30, '&hellip;' );
			}
			?>
			<div class="pal-auction__card">
				<?php if ( $img ) : ?>
					<a href="<?php echo esc_url( $full ? $full : $img ); ?>" class="pal-auction__card-img-link" data-pal-auction-lightbox>
						<div class="pal-auction__card-img" style="background-image:url(<?php echo esc_url( $img ); ?>)">
							<span class="pal-auction__card-zoom"><i class="fas fa-search-plus"></i></span>
						</div>
					</a>
				<?php else : ?>
					<div class="pal-auction__card-img">
						<span class="pal-auction__card-placeholder"><i class="fas fa-gavel"></i></span>
					</div>
				<?php endif; ?>
				<div class="pal-auction__card-body">
					<h3 class="pal-auction__card-title"><?php echo esc_html( get_the_title() ); ?></h3>
					<?php if ( $value ) : ?>
						<span class="pal-auction__card-value"><?php echo esc_html( $value ); ?></span>
					<?php endif; ?>
					<?php if ( $desc ) : ?>
						<p class="pal-auction__card-desc"><?php echo esc_html( $desc ); ?></p>
					<?php endif; ?>
				</div>
			</div>
			<?php
		}
		wp_reset_postdata();
		echo '</div>';

		if ( $view_all_url && $view_all_label ) {
			echo '<div class="pal-auction__footer">';
			echo '<a href="' . esc_url( $view_all_url ) . '" class="pal-auction__view-all">' . esc_html( $view_all_label ) . '</a>';
			echo '</div>';
		}

		// Lightbox overlay (rendered once).
		echo '<div class="pal-auction-lightbox" data-pal-auction-lb aria-hidden="true">';
		echo '<div class="pal-auction-lightbox__overlay"></div>';
		echo '<div class="pal-auction-lightbox__wrap">';
		echo '<button type="button" class="pal-auction-lightbox__close" aria-label="Close">&times;</button>';
		echo '<img class="pal-auction-lightbox__img" src="" alt="" />';
		echo '</div>';
		echo '</div>';

		echo '</div>';

		// Inline lightbox script (runs once).
		static $script_rendered = false;
		if ( ! $script_rendered ) {
			$script_rendered = true;
			?>
			<script>
			(function(){
				document.addEventListener('click',function(e){
					var link=e.target.closest('[data-pal-auction-lightbox]');
					if(!link)return;
					e.preventDefault();
					var lb=document.querySelector('[data-pal-auction-lb]');
					if(!lb)return;
					var img=lb.querySelector('.pal-auction-lightbox__img');
					img.src=link.href;
					img.alt=link.closest('.pal-auction__card').querySelector('.pal-auction__card-title').textContent||'';
					lb.classList.add('is-open');
					lb.setAttribute('aria-hidden','false');
					document.body.style.overflow='hidden';
				});
				document.addEventListener('click',function(e){
					var lb=document.querySelector('[data-pal-auction-lb].is-open');
					if(!lb)return;
					if(e.target.closest('.pal-auction-lightbox__close')||e.target.closest('.pal-auction-lightbox__overlay')){
						lb.classList.remove('is-open');
						lb.setAttribute('aria-hidden','true');
						document.body.style.overflow='';
					}
				});
				document.addEventListener('keydown',function(e){
					if(e.key==='Escape'){
						var lb=document.querySelector('[data-pal-auction-lb].is-open');
						if(lb){lb.classList.remove('is-open');lb.setAttribute('aria-hidden','true');document.body.style.overflow='';}
					}
				});
			})();
			</script>
			<?php
		}

		return ob_get_clean();
	}
}
