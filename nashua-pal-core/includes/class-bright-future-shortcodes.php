<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Bright_Future_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'pal_bright_futures', array( $this, 'render' ) );
	}

	/**
	 * Extract a YouTube video ID from any common YouTube URL format.
	 */
	private static function extract_youtube_id( $url ) {
		if ( preg_match( '/(?:youtube\.com\/watch\?v=|youtube\.com\/embed\/|youtu\.be\/|youtube\.com\/v\/)([a-zA-Z0-9_-]{11})/', $url, $m ) ) {
			return $m[1];
		}
		return '';
	}

	/**
	 * Get the thumbnail URL — featured image first, YouTube fallback.
	 */
	private function get_thumb( $post_id, $youtube_id ) {
		$thumb = get_the_post_thumbnail_url( $post_id, 'medium_large' );
		if ( $thumb ) { return $thumb; }
		if ( $youtube_id ) {
			return 'https://img.youtube.com/vi/' . $youtube_id . '/hqdefault.jpg';
		}
		return '';
	}

	/**
	 * Get first name from the post title for "Read [Name]'s Story" button.
	 */
	private function first_name( $title ) {
		$parts = explode( ' ', trim( $title ) );
		return $parts[0] ?? $title;
	}

	/**
	 * [pal_bright_futures] — Meet Our Youth & Alumni section.
	 */
	public function render( $atts ) {
		$a = shortcode_atts( array(
			'title'    => __( 'Meet Our Youth & Alumni', 'nashua-pal-core' ),
			'subtitle' => __( 'These young people are living proof that when kids are given support, opportunity, and a place to belong — incredible things can happen.', 'nashua-pal-core' ),
		), $atts, 'pal_bright_futures' );

		// Query all stories, ordered by meta order.
		$q = new WP_Query( array(
			'post_type'      => NPAL_CPT_Bright_Future::POST_TYPE,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				'relation' => 'OR',
				'order_clause' => array(
					'key'     => '_bf_order',
					'compare' => 'EXISTS',
					'type'    => 'NUMERIC',
				),
				array(
					'key'     => '_bf_order',
					'compare' => 'NOT EXISTS',
				),
			),
			'orderby' => array( 'order_clause' => 'ASC', 'title' => 'ASC' ),
			'order'   => 'ASC',
		) );

		if ( ! $q->have_posts() ) { return ''; }

		$featured = array();
		$more     = array();

		while ( $q->have_posts() ) {
			$q->the_post();
			$pid = get_the_ID();
			$story = array(
				'id'       => $pid,
				'name'     => get_the_title(),
				'role'     => get_post_meta( $pid, '_bf_role', true ),
				'youtube'  => get_post_meta( $pid, '_bf_youtube_url', true ),
				'duration' => get_post_meta( $pid, '_bf_video_duration', true ),
				'excerpt'  => get_post_meta( $pid, '_bf_excerpt', true ),
				'quote'    => get_post_meta( $pid, '_bf_quote', true ),
				'bio'      => get_post_meta( $pid, '_bf_bio', true ),
				'is_feat'  => get_post_meta( $pid, '_bf_featured', true ),
			);
			$story['yt_id'] = self::extract_youtube_id( $story['youtube'] );
			$story['thumb'] = $this->get_thumb( $pid, $story['yt_id'] );

			if ( $story['is_feat'] ) {
				$featured[] = $story;
			} else {
				$more[] = $story;
			}
		}
		wp_reset_postdata();

		ob_start();
		?>
		<section class="bf-section" aria-label="<?php echo esc_attr( $a['title'] ); ?>">

			<!-- Section header -->
			<div class="bf-header">
				<span class="bf-header__accent"></span>
				<h2 class="bf-header__title"><?php echo esc_html( $a['title'] ); ?></h2>
				<?php if ( $a['subtitle'] ) : ?>
					<p class="bf-header__subtitle"><?php echo esc_html( $a['subtitle'] ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $featured ) ) : ?>
			<!-- Featured Stories (top row — 3 columns) -->
			<div class="bf-grid bf-grid--featured">
				<?php foreach ( $featured as $s ) : ?>
					<?php $this->render_featured_card( $s ); ?>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<?php if ( ! empty( $more ) ) : ?>
			<!-- Divider -->
			<div class="bf-divider">
				<span class="bf-divider__line"></span>
				<span class="bf-divider__text"><?php esc_html_e( 'More Stories', 'nashua-pal-core' ); ?></span>
				<span class="bf-divider__line"></span>
			</div>

			<!-- More Stories (bottom row — 4 columns) -->
			<div class="bf-grid bf-grid--more">
				<?php foreach ( $more as $s ) : ?>
					<?php $this->render_more_card( $s ); ?>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<!-- Bottom tagline -->
			<div class="bf-divider">
				<span class="bf-divider__line"></span>
				<span class="bf-divider__text"><?php esc_html_e( 'Come back to read more about PAL youth and alums soon!', 'nashua-pal-core' ); ?></span>
				<span class="bf-divider__line"></span>
			</div>

		</section>

		<!-- Bio popup modal (shared, populated via JS) -->
		<div class="bf-modal" id="bf-modal" aria-hidden="true" role="dialog" aria-modal="true">
			<div class="bf-modal__backdrop"></div>
			<div class="bf-modal__dialog">
				<button class="bf-modal__close" aria-label="<?php esc_attr_e( 'Close', 'nashua-pal-core' ); ?>">&times;</button>
				<div class="bf-modal__body">
					<div class="bf-modal__photo"></div>
					<h3 class="bf-modal__name"></h3>
					<span class="bf-modal__role"></span>
					<div class="bf-modal__bio"></div>
				</div>
			</div>
		</div>

		<?php
		// Collect bio data for JS.
		$all = array_merge( $featured, $more );
		$bio_data = array();
		foreach ( $all as $s ) {
			$bio_data[ $s['id'] ] = array(
				'name'  => $s['name'],
				'role'  => $s['role'],
				'thumb' => $s['thumb'],
				'bio'   => wpautop( $s['bio'] ),
			);
		}
		?>
		<script>
		(function(){
			var bfData = <?php echo wp_json_encode( $bio_data ); ?>;
			var modal  = document.getElementById('bf-modal');
			if (!modal) return;

			var backdrop = modal.querySelector('.bf-modal__backdrop');
			var closeBtn = modal.querySelector('.bf-modal__close');
			var nameEl   = modal.querySelector('.bf-modal__name');
			var roleEl   = modal.querySelector('.bf-modal__role');
			var bioEl    = modal.querySelector('.bf-modal__bio');
			var photoEl  = modal.querySelector('.bf-modal__photo');

			function openModal(id) {
				var d = bfData[id];
				if (!d) return;
				nameEl.textContent = d.name;
				roleEl.textContent = d.role;
				bioEl.innerHTML    = d.bio;
				photoEl.innerHTML  = d.thumb ? '<img src="' + d.thumb + '" alt="' + d.name.replace(/"/g,'&quot;') + '" />' : '';
				modal.setAttribute('aria-hidden', 'false');
				modal.classList.add('is-open');
				document.body.style.overflow = 'hidden';
			}
			function closeModal() {
				modal.setAttribute('aria-hidden', 'true');
				modal.classList.remove('is-open');
				document.body.style.overflow = '';
			}

			document.addEventListener('click', function(e) {
				var btn = e.target.closest('[data-bf-bio]');
				if (btn) { e.preventDefault(); openModal(btn.getAttribute('data-bf-bio')); }
			});
			backdrop.addEventListener('click', closeModal);
			closeBtn.addEventListener('click', closeModal);
			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
			});

			/* YouTube play toggle */
			document.addEventListener('click', function(e) {
				var playBtn = e.target.closest('.bf-card__play');
				if (!playBtn) return;
				e.preventDefault();
				var card  = playBtn.closest('.bf-card');
				var ytId  = playBtn.getAttribute('data-yt');
				if (!ytId || !card) return;
				var thumb = card.querySelector('.bf-card__thumb');
				if (!thumb) return;
				thumb.innerHTML = '<iframe src="https://www.youtube-nocookie.com/embed/' + ytId + '?autoplay=1&rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:12px;"></iframe>';
				thumb.classList.add('bf-card__thumb--playing');
			});
		})();
		</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a featured card (large, with excerpt).
	 */
	private function render_featured_card( $s ) {
		$first = $this->first_name( $s['name'] );
		?>
		<div class="bf-card bf-card--featured">
			<div class="bf-card__thumb">
				<?php if ( $s['thumb'] ) : ?>
					<img src="<?php echo esc_url( $s['thumb'] ); ?>" alt="<?php echo esc_attr( $s['name'] ); ?>" loading="lazy" />
				<?php endif; ?>
				<?php if ( $s['yt_id'] ) : ?>
					<button class="bf-card__play" data-yt="<?php echo esc_attr( $s['yt_id'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Play %s video', 'nashua-pal-core' ), $s['name'] ) ); ?>">
						<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="24" cy="24" r="23" fill="rgba(255,255,255,0.9)" stroke="#071F3F" stroke-width="2"/><polygon points="19,15 35,24 19,33" fill="#071F3F"/></svg>
					</button>
				<?php endif; ?>
				<?php if ( $s['duration'] ) : ?>
					<span class="bf-card__duration"><?php echo esc_html( $s['duration'] ); ?></span>
				<?php endif; ?>
			</div>
			<div class="bf-card__body">
				<h3 class="bf-card__name"><?php echo esc_html( $s['name'] ); ?></h3>
				<?php if ( $s['role'] ) : ?>
					<span class="bf-card__role"><?php echo esc_html( $s['role'] ); ?></span>
				<?php endif; ?>
				<?php if ( $s['excerpt'] ) : ?>
					<p class="bf-card__excerpt"><?php echo esc_html( $s['excerpt'] ); ?></p>
				<?php endif; ?>
				<?php if ( $s['bio'] ) : ?>
					<button class="bf-card__read-btn" data-bf-bio="<?php echo (int) $s['id']; ?>">
						<?php echo esc_html( sprintf( __( "Read %s's Story", 'nashua-pal-core' ), $first ) ); ?> &rarr;
					</button>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render a "More Stories" card (smaller, with quote).
	 */
	private function render_more_card( $s ) {
		$first = $this->first_name( $s['name'] );
		?>
		<div class="bf-card bf-card--more">
			<div class="bf-card__thumb">
				<?php if ( $s['thumb'] ) : ?>
					<img src="<?php echo esc_url( $s['thumb'] ); ?>" alt="<?php echo esc_attr( $s['name'] ); ?>" loading="lazy" />
				<?php endif; ?>
				<?php if ( $s['yt_id'] ) : ?>
					<button class="bf-card__play" data-yt="<?php echo esc_attr( $s['yt_id'] ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Play %s video', 'nashua-pal-core' ), $s['name'] ) ); ?>">
						<svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><circle cx="24" cy="24" r="23" fill="rgba(255,255,255,0.9)" stroke="#071F3F" stroke-width="2"/><polygon points="19,15 35,24 19,33" fill="#071F3F"/></svg>
					</button>
				<?php endif; ?>
				<?php if ( $s['duration'] ) : ?>
					<span class="bf-card__duration"><?php echo esc_html( $s['duration'] ); ?></span>
				<?php endif; ?>
			</div>
			<div class="bf-card__body">
				<h3 class="bf-card__name"><?php echo esc_html( $s['name'] ); ?></h3>
				<?php if ( $s['role'] ) : ?>
					<span class="bf-card__role"><?php echo esc_html( $s['role'] ); ?></span>
				<?php endif; ?>
				<?php if ( $s['quote'] ) : ?>
					<p class="bf-card__quote"><?php echo esc_html( $s['quote'] ); ?></p>
				<?php endif; ?>
				<?php if ( $s['bio'] ) : ?>
					<button class="bf-card__read-btn" data-bf-bio="<?php echo (int) $s['id']; ?>">
						<?php echo esc_html( sprintf( __( "Read %s's Story", 'nashua-pal-core' ), $first ) ); ?> &rarr;
					</button>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
