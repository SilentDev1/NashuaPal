<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Event_Meta {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_box' ) );
		add_action( 'save_post_' . NPAL_CPT_Event::POST_TYPE, array( $this, 'save' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_media' ) );
	}

	public function enqueue_media( $hook ) {
		// Awardee photo picker uses wp.media — only load on event post edit screens.
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) { return; }
		$screen = get_current_screen();
		if ( ! $screen || NPAL_CPT_Event::POST_TYPE !== $screen->post_type ) { return; }
		wp_enqueue_media();
	}

	/**
	 * All published pal_sponsor posts with context=event, grouped by level.
	 *
	 * @return array [ id => [ name, thumb, level_name, badge_style ] ]
	 */
	private function tiered_sponsor_options() {
		if ( ! class_exists( 'NPAL_CPT_Sponsor' ) ) { return array(); }
		$q = new WP_Query( array(
			'post_type'      => NPAL_CPT_Sponsor::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_key'       => '_pal_sponsor_order',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array(
				array( 'key' => '_pal_sponsor_context', 'value' => 'event' ),
			),
			'no_found_rows'  => true,
		) );
		$out = array();
		foreach ( $q->posts as $p ) {
			$level_term  = NPAL_CPT_Sponsor::get_sponsor_level( $p->ID );
			$badge_style = $level_term ? NPAL_CPT_Sponsor::get_level_badge_style( $level_term ) : '';
			$out[ (int) $p->ID ] = array(
				'name'        => $p->post_title,
				'thumb'       => get_the_post_thumbnail_url( $p->ID, 'thumbnail' ),
				'level_name'  => $level_term ? $level_term->name : '',
				'badge_style' => $badge_style,
			);
		}
		return $out;
	}

	public function add_box() {
		add_meta_box(
			'pal_event_details',
			__( 'Event Details', 'nashua-pal-core' ),
			array( $this, 'render' ),
			NPAL_CPT_Event::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render( $post ) {
		wp_nonce_field( 'pal_event_meta', 'pal_event_meta_nonce' );
		$start    = get_post_meta( $post->ID, '_pal_event_start', true );
		$end      = get_post_meta( $post->ID, '_pal_event_end', true );
		$all_day  = get_post_meta( $post->ID, '_pal_event_all_day', true );
		$location = get_post_meta( $post->ID, '_pal_event_location', true );
		$register = get_post_meta( $post->ID, '_pal_event_register_url', true );
		$reg_status = get_post_meta( $post->ID, '_pal_event_reg_status', true );
		$linked_program = get_post_meta( $post->ID, '_pal_event_program', true );
		// Pre-fill linked program when creating from a Program's "Create Event" button.
		if ( ! $linked_program && isset( $_GET['pal_link_program'] ) ) {
			$linked_program = absint( $_GET['pal_link_program'] );
		}
		$is_fund  = get_post_meta( $post->ID, '_pal_event_is_fundraiser', true );
		$template = get_post_meta( $post->ID, '_pal_event_template', true );
		$embed    = get_post_meta( $post->ID, '_pal_event_embed', true );
		$awardees = get_post_meta( $post->ID, '_pal_event_awardees', true );
		?>
		<style>
			.pal-meta-row { display:grid; grid-template-columns: 180px 1fr; gap:14px 18px; align-items:start; margin:14px 0; }
			.pal-meta-row label { font-weight:600; padding-top:6px; }
			.pal-meta-row input[type=text], .pal-meta-row input[type=url], .pal-meta-row input[type=datetime-local], .pal-meta-row select { width:100%; max-width:480px; padding:8px 10px; }
			.pal-meta-row textarea { width:100%; padding:8px 10px; font-family:Menlo,Consolas,monospace; font-size:12px; }
			.pal-meta-help { color:#646970; font-size:12px; margin-top:4px; }
			.pal-meta-variant-help { background:#F7F9FC; border-left:4px solid #F4A51C; padding:12px 14px; margin:10px 0 16px; font-size:13px; line-height:1.55; }
			.pal-meta-variant-help strong { color:#071F3F; }
			.pal-meta-section-note { background:#FFF4D8; border-left:4px solid #F4A51C; padding:10px 14px; margin:0 0 14px; font-size:12px; color:#5A4A1C; }
			.pal-sponsor-grid { display:flex; flex-wrap:wrap; gap:8px; max-width:760px; max-height:260px; overflow-y:auto; padding:10px; border:1px solid #DCDCDE; border-radius:8px; background:#FAFAFA; }
			.pal-sponsor-card { display:inline-flex; align-items:center; gap:6px; padding:5px 10px 5px 6px; border:1px solid #DCDCDE; border-radius:999px; background:#fff; cursor:pointer; transition:border-color .15s, background .15s; white-space:nowrap; }
			.pal-sponsor-card:hover { border-color:#2271b1; }
			.pal-sponsor-card input[type=checkbox] { margin:0; flex-shrink:0; width:14px; height:14px; }
			.pal-sponsor-card img { width:26px; height:26px; object-fit:contain; border-radius:50%; background:#F0F0F1; flex-shrink:0; }
			.pal-sponsor-card .pal-sponsor-card__placeholder { width:26px; height:26px; border-radius:50%; background:#F0F0F1; display:flex; align-items:center; justify-content:center; color:#A7AAAD; font-size:9px; flex-shrink:0; }
			.pal-sponsor-card span.pal-sponsor-card__name { font-size:12px; flex:1; line-height:1.2; }
			.pal-sponsor-card.is-checked { border-color:#F4A51C; background:#FFFBF0; }
			.pal-sponsor-empty { color:#646970; font-size:13px; }
			.pal-sponsor-empty a { color:#2271b1; }
			.pal-awardees { max-width:760px; }
			.pal-awardee { background:#fff; border:1px solid #DCDCDE; border-radius:8px; padding:14px; margin:0 0 10px; display:grid; grid-template-columns:96px 1fr auto; gap:12px; align-items:start; }
			.pal-awardee__photo { width:96px; height:96px; background:#F0F0F1; border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; color:#A7AAAD; font-size:11px; text-align:center; cursor:pointer; }
			.pal-awardee__photo img { width:100%; height:100%; object-fit:cover; }
			.pal-awardee__photo:hover { background:#E0E0E1; }
			.pal-awardee__fields { display:flex; flex-direction:column; gap:6px; min-width:0; }
			.pal-awardee__fields input, .pal-awardee__fields textarea { width:100%; padding:6px 8px; font-size:13px; }
			.pal-awardee__fields textarea { font-family:inherit; min-height:50px; resize:vertical; }
			.pal-awardee__remove { background:#FEE; border:1px solid #DC3232; color:#B32D2E; padding:4px 10px; cursor:pointer; border-radius:4px; font-size:12px; align-self:start; }
			.pal-awardee__remove:hover { background:#DC3232; color:#fff; }
			.pal-awardees-add { background:#2271b1; color:#fff; border:0; padding:8px 16px; border-radius:4px; cursor:pointer; font-weight:600; margin-top:6px; }
			.pal-awardees-add:hover { background:#135E96; }
		</style>

		<div class="pal-meta-variant-help">
			<strong>Single-event template variant</strong> — choose which layout to render. Each variant uses different fields below; unused fields are ignored.<br>
			<strong>Community</strong>: classic info + body + sidebar (default). <strong>Embedded</strong>: wide BetterUnite/iframe embed at top + sponsors. <strong>Champions</strong>: top embed/link + awardee grid + previous winners + sponsors.
		</div>

		<div class="pal-meta-row">
			<label for="pal_event_template"><?php esc_html_e( 'Template variant', 'nashua-pal-core' ); ?></label>
			<div>
				<select id="pal_event_template" name="pal_event_template">
					<option value=""          <?php selected( $template, '' ); ?>>Community (default)</option>
					<option value="community" <?php selected( $template, 'community' ); ?>>Community — info + body + sidebar</option>
					<option value="embedded"  <?php selected( $template, 'embedded' ); ?>>Embedded fundraiser — wide iframe + sponsors</option>
					<option value="champions" <?php selected( $template, 'champions' ); ?>>Night of Champions — awardees + previous winners</option>
				</select>
			</div>
		</div>

		<div class="pal-meta-row">
			<label for="pal_event_all_day"><?php esc_html_e( 'All-day event?', 'nashua-pal-core' ); ?></label>
			<label><input type="checkbox" id="pal_event_all_day" name="pal_event_all_day" value="1" <?php checked( $all_day, '1' ); ?> /> <?php esc_html_e( 'All day (or multi-day — set Start and End to the first and last day)', 'nashua-pal-core' ); ?></label>
		</div>
		<div class="pal-meta-row">
			<label for="pal_event_start"><?php esc_html_e( 'Start', 'nashua-pal-core' ); ?></label>
			<input type="datetime-local" id="pal_event_start" name="pal_event_start" value="<?php echo esc_attr( $start ); ?>" />
		</div>
		<div class="pal-meta-row">
			<label for="pal_event_end"><?php esc_html_e( 'End (optional)', 'nashua-pal-core' ); ?></label>
			<input type="datetime-local" id="pal_event_end" name="pal_event_end" value="<?php echo esc_attr( $end ); ?>" />
		</div>
		<div class="pal-meta-row">
			<label for="pal_event_location"><?php esc_html_e( 'Location', 'nashua-pal-core' ); ?></label>
			<input type="text" id="pal_event_location" name="pal_event_location" value="<?php echo esc_attr( $location ); ?>" placeholder="<?php esc_attr_e( 'PAL Community Center', 'nashua-pal-core' ); ?>" />
		</div>
		<div class="pal-meta-row">
			<label for="pal_event_register_url"><?php esc_html_e( 'Register / RSVP URL', 'nashua-pal-core' ); ?></label>
			<input type="text" id="pal_event_register_url" name="pal_event_register_url" value="<?php echo esc_attr( $register ); ?>" placeholder="https://… or /relative-path/" />
		</div>
		<div class="pal-meta-row">
			<label for="pal_event_reg_status"><?php esc_html_e( 'Registration status', 'nashua-pal-core' ); ?></label>
			<div>
				<select id="pal_event_reg_status" name="pal_event_reg_status">
					<option value=""         <?php selected( $reg_status, '' ); ?>><?php esc_html_e( '— None (no badge shown)', 'nashua-pal-core' ); ?></option>
					<option value="open"     <?php selected( $reg_status, 'open' ); ?>><?php esc_html_e( 'Open', 'nashua-pal-core' ); ?></option>
					<option value="closing"  <?php selected( $reg_status, 'closing' ); ?>><?php esc_html_e( 'Closing Soon', 'nashua-pal-core' ); ?></option>
					<option value="full"     <?php selected( $reg_status, 'full' ); ?>><?php esc_html_e( 'Full', 'nashua-pal-core' ); ?></option>
					<option value="waitlist" <?php selected( $reg_status, 'waitlist' ); ?>><?php esc_html_e( 'Waitlist', 'nashua-pal-core' ); ?></option>
					<option value="closed"   <?php selected( $reg_status, 'closed' ); ?>><?php esc_html_e( 'Closed', 'nashua-pal-core' ); ?></option>
				</select>
				<span class="pal-meta-help"><?php esc_html_e( 'Shows a colored badge on event listings and the single event page.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_event_program"><?php esc_html_e( 'Linked program', 'nashua-pal-core' ); ?></label>
			<div>
				<select id="pal_event_program" name="pal_event_program">
					<option value=""><?php esc_html_e( '— None', 'nashua-pal-core' ); ?></option>
					<?php
					$programs = get_posts( array( 'post_type' => 'pal_program', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'post_status' => 'publish' ) );
					foreach ( $programs as $prog ) :
						?>
						<option value="<?php echo (int) $prog->ID; ?>" <?php selected( (int) $linked_program, $prog->ID ); ?>><?php echo esc_html( $prog->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="pal-meta-help"><?php esc_html_e( 'Link this event to a program. The program page will show this event automatically.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<div class="pal-meta-row">
			<label for="pal_event_is_fundraiser"><?php esc_html_e( 'Fundraiser?', 'nashua-pal-core' ); ?></label>
			<label><input type="checkbox" id="pal_event_is_fundraiser" name="pal_event_is_fundraiser" value="1" <?php checked( $is_fund, '1' ); ?> /> <?php esc_html_e( 'Mark as a fundraiser event', 'nashua-pal-core' ); ?></label>
		</div>

		<h3 style="margin:30px 0 0;padding-top:18px;border-top:1px solid #DCDCDE;">Embed (Embedded + Champions variants)</h3>
		<p class="pal-meta-section-note"><strong>Heads up:</strong> the Embed field is ignored unless the Template variant at the top is set to <strong>Embedded fundraiser</strong> or <strong>Night of Champions</strong>. Community variant pages don't render the embed.</p>
		<div class="pal-meta-row">
			<label for="pal_event_embed"><?php esc_html_e( 'Embed HTML', 'nashua-pal-core' ); ?></label>
			<div>
				<textarea id="pal_event_embed" name="pal_event_embed" rows="6" placeholder="&lt;div id='bu-checkout'&gt;&lt;/div&gt;&#10;&lt;script src='https://app.betterunite.com/embedjs?h=5050&u=https://app.betterunite.com/your-event'&gt;&lt;/script&gt;"><?php echo esc_textarea( $embed ); ?></textarea>
				<p class="pal-meta-help">Paste full embed HTML (BetterUnite, Bid Beacon, raw iframe — all work). Container auto-stretches to 1280px and adds a branded loading state.</p>
			</div>
		</div>

		<h3 style="margin:30px 0 0;padding-top:18px;border-top:1px solid #DCDCDE;">Sponsors</h3>
		<p class="pal-meta-section-note"><strong>Tip:</strong> Create sponsors under <strong>Events → Sponsors</strong> first (with level badges like Gold, Silver, etc.), then select them here.</p>
		<?php
		$tiered_sponsors  = $this->tiered_sponsor_options();
		$linked_raw       = get_post_meta( $post->ID, '_pal_event_linked_sponsors', true );
		$linked_ids       = is_array( $linked_raw ) ? array_map( 'absint', $linked_raw ) : array();
		$linked_lookup    = array_flip( $linked_ids );
		?>
		<div class="pal-meta-row">
			<label><?php esc_html_e( 'Choose sponsors', 'nashua-pal-core' ); ?></label>
			<div>
				<?php if ( ! empty( $tiered_sponsors ) ) : ?>
					<div class="pal-sponsor-grid" id="pal-tiered-sponsor-grid">
						<?php foreach ( $tiered_sponsors as $sid => $info ) :
							$is_checked = isset( $linked_lookup[ $sid ] );
							?>
							<label class="pal-sponsor-card<?php echo $is_checked ? ' is-checked' : ''; ?>">
								<input type="checkbox" name="pal_event_linked_sponsors[]" value="<?php echo (int) $sid; ?>" <?php checked( $is_checked ); ?> />
								<?php if ( $info['thumb'] ) : ?>
									<img src="<?php echo esc_url( $info['thumb'] ); ?>" alt="" />
								<?php else : ?>
									<span class="pal-sponsor-card__placeholder"><?php esc_html_e( 'No logo', 'nashua-pal-core' ); ?></span>
								<?php endif; ?>
								<span class="pal-sponsor-card__name"><?php echo esc_html( $info['name'] ); ?></span>
								<?php if ( $info['level_name'] ) : ?>
									<span style="font-size:9px;padding:1px 5px;border-radius:3px;margin-left:2px;white-space:nowrap;<?php echo esc_attr( $info['badge_style'] ); ?>"><?php echo esc_html( $info['level_name'] ); ?></span>
								<?php endif; ?>
							</label>
						<?php endforeach; ?>
					</div>
					<p class="pal-meta-help"><?php esc_html_e( 'Select sponsors for this event. They render as a scrolling marquee with level badges on the event page.', 'nashua-pal-core' ); ?></p>
				<?php else : ?>
					<p class="pal-sponsor-empty">
						<?php
						$sponsor_url = admin_url( 'post-new.php?post_type=' . NPAL_CPT_Sponsor::POST_TYPE . '&pal_context=event' );
						printf(
							esc_html__( 'No event sponsors yet. %s to get started.', 'nashua-pal-core' ),
							'<a href="' . esc_url( $sponsor_url ) . '">' . esc_html__( 'Create one', 'nashua-pal-core' ) . '</a>'
						);
						?>
					</p>
				<?php endif; ?>
			</div>
		</div>

		<h3 style="margin:30px 0 0;padding-top:18px;border-top:1px solid #DCDCDE;">Awardees (Champions variant)</h3>
		<?php
		// Parse the pipe-delimited stored value into rows so the repeater can re-render them.
		$awardee_rows = array();
		if ( $awardees ) {
			foreach ( preg_split( "/\r\n|\n|\r/", (string) $awardees ) as $line ) {
				$line = trim( $line );
				if ( '' === $line ) { continue; }
				$parts = array_pad( array_map( 'trim', explode( '|', $line ) ), 5, '' );
				$awardee_rows[] = array(
					'name'  => $parts[0],
					'photo' => $parts[1],
					'grade' => $parts[2],
					'bio'   => $parts[3],
					'link'  => $parts[4],
				);
			}
		}
		?>
		<div class="pal-meta-row">
			<label><?php esc_html_e( 'Youth Awardees', 'nashua-pal-core' ); ?></label>
			<div>
				<p class="pal-meta-help" style="margin:0 0 10px;"><?php esc_html_e( '11–13 awardees recommended. Click the photo box to pick from the Media Library.', 'nashua-pal-core' ); ?></p>
				<div class="pal-awardees" data-pal-awardees>
					<?php foreach ( $awardee_rows as $row ) : ?>
						<?php $this->render_awardee_row( $row ); ?>
					<?php endforeach; ?>
				</div>
				<button type="button" class="pal-awardees-add" data-pal-awardees-add>+ <?php esc_html_e( 'Add Awardee', 'nashua-pal-core' ); ?></button>
				<textarea name="pal_event_awardees" data-pal-awardees-storage hidden><?php echo esc_textarea( $awardees ); ?></textarea>
			</div>
		</div>

		<?php $this->render_awardee_template(); ?>
		<?php $this->print_awardees_script(); ?>

		<?php
	}

	/** Single awardee row (server-side render for existing data). */
	private function render_awardee_row( $row ) {
		?>
		<div class="pal-awardee" data-pal-awardee>
			<div class="pal-awardee__photo" data-pal-awardee-photo>
				<?php if ( ! empty( $row['photo'] ) ) : ?>
					<img src="<?php echo esc_url( $row['photo'] ); ?>" alt="" />
				<?php else : ?>
					<span><?php esc_html_e( 'Click to choose photo', 'nashua-pal-core' ); ?></span>
				<?php endif; ?>
				<input type="hidden" data-pal-awardee-photo-url value="<?php echo esc_attr( $row['photo'] ); ?>" />
			</div>
			<div class="pal-awardee__fields">
				<input type="text" data-pal-awardee-name value="<?php echo esc_attr( $row['name'] ); ?>" placeholder="<?php esc_attr_e( 'Name (required)', 'nashua-pal-core' ); ?>" />
				<input type="text" data-pal-awardee-grade value="<?php echo esc_attr( $row['grade'] ); ?>" placeholder="<?php esc_attr_e( 'Grade / School (e.g. Nashua HS, Grade 11)', 'nashua-pal-core' ); ?>" />
				<textarea data-pal-awardee-bio placeholder="<?php esc_attr_e( 'Short bio', 'nashua-pal-core' ); ?>"><?php echo esc_textarea( $row['bio'] ); ?></textarea>
				<input type="text" data-pal-awardee-link value="<?php echo esc_attr( $row['link'] ); ?>" placeholder="<?php esc_attr_e( 'Optional profile URL', 'nashua-pal-core' ); ?>" />
			</div>
			<button type="button" class="pal-awardee__remove" data-pal-awardee-remove>×</button>
		</div>
		<?php
	}

	/** Hidden template — JS clones this into the awardees list on Add. */
	private function render_awardee_template() {
		echo '<template id="pal-awardee-template">';
		$this->render_awardee_row( array( 'name' => '', 'photo' => '', 'grade' => '', 'bio' => '', 'link' => '' ) );
		echo '</template>';
	}

	/** Inline JS for the awardee repeater + sponsor card visual state. */
	private function print_awardees_script() {
		?>
		<script>
		(function(){
			// Sponsor cards — light-up the .is-checked state when a box flips (both grids).
			document.querySelectorAll('.pal-sponsor-card input[type=checkbox]').forEach(function(cb){
				cb.addEventListener('change', function(){
					cb.closest('.pal-sponsor-card').classList.toggle('is-checked', cb.checked);
				});
			});

			// Awardee repeater.
			var wrap = document.querySelector('[data-pal-awardees]');
			var storage = document.querySelector('[data-pal-awardees-storage]');
			var tpl = document.getElementById('pal-awardee-template');
			var addBtn = document.querySelector('[data-pal-awardees-add]');
			if (!wrap || !storage || !tpl || !addBtn) return;

			function serialize() {
				var lines = [];
				wrap.querySelectorAll('[data-pal-awardee]').forEach(function(row){
					var name  = row.querySelector('[data-pal-awardee-name]').value.trim();
					var photo = row.querySelector('[data-pal-awardee-photo-url]').value.trim();
					var grade = row.querySelector('[data-pal-awardee-grade]').value.trim();
					var bio   = row.querySelector('[data-pal-awardee-bio]').value.trim();
					var link  = row.querySelector('[data-pal-awardee-link]').value.trim();
					if (!name && !photo && !bio) return; // empty row — skip
					lines.push([name, photo, grade, bio, link].join('|'));
				});
				storage.value = lines.join('\n');
			}

			function wirePhotoPicker(box) {
				box.addEventListener('click', function(){
					if (!window.wp || !wp.media) { alert('Media library not loaded — refresh and try again.'); return; }
					var frame = wp.media({ title: 'Pick awardee photo', multiple: false, library: { type: 'image' } });
					frame.on('select', function(){
						var att = frame.state().get('selection').first().toJSON();
						var url = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
						box.innerHTML = '<img src="' + url + '" alt="" /><input type="hidden" data-pal-awardee-photo-url value="' + url + '" />';
						serialize();
					});
					frame.open();
				});
			}

			function wireRow(row) {
				wirePhotoPicker(row.querySelector('[data-pal-awardee-photo]'));
				row.querySelector('[data-pal-awardee-remove]').addEventListener('click', function(){
					row.remove();
					serialize();
				});
				row.querySelectorAll('input, textarea').forEach(function(el){
					el.addEventListener('input', serialize);
				});
			}

			wrap.querySelectorAll('[data-pal-awardee]').forEach(wireRow);

			addBtn.addEventListener('click', function(){
				var clone = tpl.content.firstElementChild.cloneNode(true);
				wrap.appendChild(clone);
				wireRow(clone);
				clone.querySelector('[data-pal-awardee-name]').focus();
			});

			// Belt-and-suspenders: serialize on form submit too in case input events missed something.
			var form = document.getElementById('post');
			if (form) form.addEventListener('submit', serialize);
		})();
		</script>
		<?php
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST['pal_event_meta_nonce'] ) || ! wp_verify_nonce( $_POST['pal_event_meta_nonce'], 'pal_event_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		// Normalize HTML datetime-local ("2026-04-28T15:00") to MySQL DATETIME ("2026-04-28 15:00:00")
		// so meta_query comparisons with type=DATETIME work correctly.
		$start_raw = isset( $_POST['pal_event_start'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_event_start'] ) ) : '';
		$end_raw   = isset( $_POST['pal_event_end'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_event_end'] ) ) : '';
		$start     = $start_raw && ( $ts = strtotime( $start_raw ) ) ? gmdate( 'Y-m-d H:i:s', $ts ) : '';
		$end       = $end_raw && ( $te = strtotime( $end_raw ) ) ? gmdate( 'Y-m-d H:i:s', $te ) : '';

		// Sponsors — pal_sponsor IDs linked to this event.
		$linked_sponsors_raw = isset( $_POST['pal_event_linked_sponsors'] ) ? (array) $_POST['pal_event_linked_sponsors'] : array();
		$linked_sponsors     = array_values( array_unique( array_filter( array_map( 'absint', $linked_sponsors_raw ) ) ) );
		$old_linked          = get_post_meta( $post_id, '_pal_event_linked_sponsors', true );
		$old_linked          = is_array( $old_linked ) ? array_map( 'absint', $old_linked ) : array();

		if ( ! empty( $linked_sponsors ) ) {
			update_post_meta( $post_id, '_pal_event_linked_sponsors', $linked_sponsors );
		} else {
			delete_post_meta( $post_id, '_pal_event_linked_sponsors' );
		}

		// Sync reverse: update each sponsor's _pal_sponsor_linked_posts.
		$removed_sponsors = array_diff( $old_linked, $linked_sponsors );
		foreach ( $removed_sponsors as $sid ) {
			$sp_linked = get_post_meta( $sid, '_pal_sponsor_linked_posts', true );
			if ( is_array( $sp_linked ) ) {
				$sp_linked = array_values( array_diff( $sp_linked, array( $post_id ) ) );
				if ( ! empty( $sp_linked ) ) {
					update_post_meta( $sid, '_pal_sponsor_linked_posts', $sp_linked );
				} else {
					delete_post_meta( $sid, '_pal_sponsor_linked_posts' );
				}
			}
		}
		$added_sponsors = array_diff( $linked_sponsors, $old_linked );
		foreach ( $added_sponsors as $sid ) {
			$sp_linked = get_post_meta( $sid, '_pal_sponsor_linked_posts', true );
			$sp_linked = is_array( $sp_linked ) ? $sp_linked : array();
			if ( ! in_array( $post_id, $sp_linked, true ) ) {
				$sp_linked[] = $post_id;
				update_post_meta( $sid, '_pal_sponsor_linked_posts', array_values( $sp_linked ) );
			}
		}

		// Template variant: validate against whitelist
		$template_raw = isset( $_POST['pal_event_template'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_event_template'] ) ) : '';
		$template     = in_array( $template_raw, array( 'community', 'embedded', 'champions' ), true ) ? $template_raw : '';

		// Embed HTML: allow iframe + standard embed tags
		$embed_raw = isset( $_POST['pal_event_embed'] ) ? wp_unslash( $_POST['pal_event_embed'] ) : '';
		$embed     = wp_kses( $embed_raw, array(
			'iframe' => array( 'src' => array(), 'width' => array(), 'height' => array(), 'frameborder' => array(), 'allowfullscreen' => array(), 'allow' => array(), 'loading' => array(), 'referrerpolicy' => array(), 'title' => array(), 'style' => array(), 'scrolling' => array(), 'class' => array(), 'id' => array() ),
			'script' => array( 'src' => array(), 'async' => array(), 'defer' => array(), 'type' => array(), 'charset' => array(), 'id' => array() ),
			'div'    => array( 'class' => array(), 'id' => array(), 'style' => array(), 'data-*' => array() ),
		) );

		// Awardees: keep raw textarea but strip slashes; rendering layer parses lines
		$awardees_raw = isset( $_POST['pal_event_awardees'] ) ? wp_unslash( $_POST['pal_event_awardees'] ) : '';
		$awardees     = sanitize_textarea_field( $awardees_raw );

		// Registration status: whitelist.
		$reg_status_raw = isset( $_POST['pal_event_reg_status'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_event_reg_status'] ) ) : '';
		$reg_status     = in_array( $reg_status_raw, array( 'open', 'closing', 'full', 'waitlist', 'closed' ), true ) ? $reg_status_raw : '';

		// Linked program.
		$linked_program = isset( $_POST['pal_event_program'] ) ? absint( $_POST['pal_event_program'] ) : 0;

		$fields = array(
			'_pal_event_start'         => $start,
			'_pal_event_end'           => $end,
			'_pal_event_all_day'       => ! empty( $_POST['pal_event_all_day'] ) ? '1' : '0',
			'_pal_event_location'      => isset( $_POST['pal_event_location'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_event_location'] ) ) : '',
			'_pal_event_register_url'  => isset( $_POST['pal_event_register_url'] ) ? esc_url_raw( wp_unslash( $_POST['pal_event_register_url'] ) ) : '',
			'_pal_event_reg_status'    => $reg_status,
			'_pal_event_program'       => $linked_program ? (string) $linked_program : '',
			'_pal_event_is_fundraiser' => ! empty( $_POST['pal_event_is_fundraiser'] ) ? '1' : '0',
			'_pal_event_template'      => $template,
			'_pal_event_embed'         => $embed,
			'_pal_event_awardees'      => $awardees,
		);

		foreach ( $fields as $key => $value ) {
			if ( '' === $value || '0' === $value ) {
				if ( in_array( $key, array( '_pal_event_is_fundraiser', '_pal_event_all_day' ), true ) ) {
					update_post_meta( $post_id, $key, $value );
				} else {
					delete_post_meta( $post_id, $key );
				}
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}
}
