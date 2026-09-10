<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Reusable visual icon picker for any admin screen — Settings → Stats,
 * Programs CPT meta, future use in Partners or Get Involved settings, etc.
 *
 * Usage in any meta box / settings render:
 *   NPAL_Icon_Picker::enqueue();          // call once per screen (in admin_init or render)
 *   NPAL_Icon_Picker::field( $name, $value );   // renders chip + input + Choose button
 *   NPAL_Icon_Picker::modal();            // renders the shared modal once per page
 */
final class NPAL_Icon_Picker {

	private static $modal_rendered = false;
	private static $assets_enqueued = false;

	/**
	 * Enqueue WP media (for custom upload) + Font Awesome (for FA preview).
	 * Idempotent — safe to call from multiple meta boxes.
	 */
	public static function enqueue() {
		if ( self::$assets_enqueued ) { return; }
		self::$assets_enqueued = true;
		wp_enqueue_media();
		wp_enqueue_style( 'pal-admin-fa', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', array(), '6.5.2' );
	}

	/**
	 * Renders the picker FIELD: visual chip + hidden text input + "Choose icon" button.
	 * @param string $name  The form field name (e.g. "_pal_program_icon" or "settings[stat_1_icon]").
	 * @param string $value Current value (icon name, image URL, or FA class).
	 * @param array  $args  Optional: id, label_id, placeholder.
	 */
	public static function field( $name, $value, $args = array() ) {
		$id = isset( $args['id'] ) ? $args['id'] : 'pal_icon_' . md5( $name );
		?>
		<div class="pal-icon-field" data-pal-icon-field>
			<div class="pal-icon-field__preview" data-pal-icon-preview>
				<?php echo NPAL_Icons::render_or_fa( $value ); ?>
			</div>
			<div class="pal-icon-field__controls">
				<input type="text"
					id="<?php echo esc_attr( $id ); ?>"
					name="<?php echo esc_attr( $name ); ?>"
					value="<?php echo esc_attr( $value ); ?>"
					class="regular-text pal-icon-field__input"
					data-pal-icon-input />
				<button type="button" class="button button-secondary pal-icon-field__choose" data-pal-icon-open>
					<?php esc_html_e( 'Choose icon', 'nashua-pal-core' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the shared modal once per page. Subsequent calls are no-ops so multiple
	 * meta boxes / screens can include it without duplication.
	 */
	public static function modal() {
		if ( self::$modal_rendered ) { return; }
		self::$modal_rendered = true;

		$catalog = NPAL_Icons::registry();
		?>
		<style>
			/* Defensive SVG rendering — force outline style to win over any
			   global "svg { fill: currentColor }" rule from WP admin or themes.
			   Without this, fill: currentColor would override our inline fill="none"
			   and the outline icons would render as solid blobs (or invisible). */
			.pal-icon-field svg, .pal-icon-modal svg {
				display: inline-block !important;
				fill: none !important;
				stroke: currentColor !important;
				stroke-width: 2 !important;
				stroke-linecap: round !important;
				stroke-linejoin: round !important;
				vertical-align: middle;
			}

			.pal-icon-field { display:flex; gap:12px; align-items:center; }
			.pal-icon-field__preview {
				width:48px; height:48px; border-radius:8px;
				background:#F7F9FC; border:1px solid #E4EAF2;
				display:inline-flex; align-items:center; justify-content:center;
				color:#F4A51C; flex-shrink:0;
			}
			.pal-icon-field__preview svg { width:28px !important; height:28px !important; color:#F4A51C; }
			.pal-icon-field__preview img { max-width:32px; max-height:32px; }
			.pal-icon-field__preview i { font-size:22px; color:#F4A51C; }
			.pal-icon-field__preview:empty::after { content:"—"; color:#a7aaaf; font-size:18px; }
			.pal-icon-field__controls { display:flex; flex-direction:column; gap:6px; flex:1; }
			.pal-icon-field__input { font-family:Menlo,Consolas,monospace; font-size:12px; }

			.pal-icon-modal { position:fixed; inset:0; z-index:99999; display:none; }
			.pal-icon-modal.is-open { display:block; }
			.pal-icon-modal__overlay { position:absolute; inset:0; background:rgba(7,31,63,0.55); }
			.pal-icon-modal__panel {
				position:relative; max-width:680px; margin:60px auto 0;
				background:#fff; border-radius:10px; box-shadow:0 20px 60px rgba(0,0,0,0.3);
				overflow:hidden;
			}
			.pal-icon-modal__head {
				display:flex; justify-content:space-between; align-items:center;
				padding:16px 20px; border-bottom:1px solid #E4EAF2; background:#071F3F; color:#fff;
			}
			.pal-icon-modal__head h2 { margin:0; color:#F4A51C; font-size:16px; font-weight:700; }
			.pal-icon-modal__close {
				background:transparent; border:0; color:#fff; font-size:22px;
				cursor:pointer; line-height:1; padding:4px 8px;
			}
			.pal-icon-modal__close:hover { color:#F4A51C; }
			.pal-icon-modal__tabs { display:flex; border-bottom:1px solid #E4EAF2; }
			.pal-icon-modal__tabs button {
				flex:1; padding:12px 14px; background:#F7F9FC; border:0;
				border-right:1px solid #E4EAF2; cursor:pointer;
				font-size:13px; font-weight:600; color:#5D6B7A;
			}
			.pal-icon-modal__tabs button:last-child { border-right:0; }
			.pal-icon-modal__tabs button.is-active { background:#fff; color:#071F3F; box-shadow:inset 0 -3px 0 #F4A51C; }
			.pal-icon-modal__body { padding:20px 24px 24px; max-height:60vh; overflow:auto; }
			.pal-icon-modal__panel-builtin .pal-icon-grid {
				display:grid; grid-template-columns:repeat(auto-fill,minmax(96px,1fr)); gap:10px;
			}
			.pal-icon-grid button {
				background:#F7F9FC; border:2px solid transparent; border-radius:8px;
				padding:14px 8px; cursor:pointer; display:flex; flex-direction:column; align-items:center; gap:6px;
				transition:border-color .12s ease, background .12s ease;
			}
			.pal-icon-grid button:hover { background:#fff; border-color:#F4A51C; }
			.pal-icon-grid button.is-selected { border-color:#F4A51C; background:#FFF8E7; }
			.pal-icon-grid button { color:#071F3F; }
			.pal-icon-grid button svg { width:32px !important; height:32px !important; color:#071F3F; }
			.pal-icon-grid button .pal-icon-grid__label {
				font-size:11px; color:#5D6B7A; text-align:center; line-height:1.2;
			}
			.pal-icon-modal__panel-upload { text-align:center; padding:20px 0 8px; }
			.pal-icon-modal__panel-upload p { color:#5D6B7A; font-size:13px; max-width:420px; margin:14px auto 0; }
			.pal-icon-modal__panel-fa label { display:block; font-weight:600; margin:0 0 6px; color:#071F3F; }
			.pal-icon-modal__panel-fa input[type="text"] { width:100%; max-width:420px; padding:8px 10px; font-family:Menlo,Consolas,monospace; }
			.pal-icon-modal__panel-fa .pal-icon-modal__hint { color:#5D6B7A; font-size:12px; margin:8px 0 14px; }
			.pal-icon-modal__panel-fa .pal-fa-preview { display:inline-flex; align-items:center; gap:8px; padding:6px 12px; background:#F7F9FC; border-radius:6px; }
			.pal-icon-modal__panel-fa .pal-fa-preview i { font-size:20px; color:#F4A51C; }
			.pal-icon-fa-howto {
				background: #FFF8E7;
				border: 1px solid #F4A51C;
				border-radius: 6px;
				padding: 10px 14px 4px;
				margin: 0 0 14px;
			}
			.pal-icon-fa-howto ol { margin: 0; padding-left: 22px; color: #5D6B7A; font-size: 13px; line-height: 1.55; }
			.pal-icon-fa-howto ol li { margin: 0 0 6px; }
			.pal-icon-fa-howto a { color: #071F3F; font-weight: 600; }
			.pal-icon-fa-howto a:hover { color: #B07308; }
			.pal-icon-fa-howto code { background: rgba(7,31,63,0.08); color: #071F3F; padding: 1px 5px; border-radius: 3px; font-family: Menlo,Consolas,monospace; font-size: 12px; }
		</style>

		<div class="pal-icon-modal" id="pal-icon-modal" aria-hidden="true">
			<div class="pal-icon-modal__overlay" data-pal-icon-close></div>
			<div class="pal-icon-modal__panel" role="dialog" aria-modal="true" aria-labelledby="pal-icon-modal-title">
				<div class="pal-icon-modal__head">
					<h2 id="pal-icon-modal-title"><?php esc_html_e( 'Choose an icon', 'nashua-pal-core' ); ?></h2>
					<button type="button" class="pal-icon-modal__close" data-pal-icon-close aria-label="<?php esc_attr_e( 'Close', 'nashua-pal-core' ); ?>">×</button>
				</div>
				<div class="pal-icon-modal__tabs" role="tablist">
					<button type="button" class="is-active" data-pal-modal-tab="builtin"><?php esc_html_e( 'Built-in icons', 'nashua-pal-core' ); ?></button>
					<button type="button" data-pal-modal-tab="upload"><?php esc_html_e( 'Upload custom', 'nashua-pal-core' ); ?></button>
					<button type="button" data-pal-modal-tab="fa"><?php esc_html_e( 'Font Awesome', 'nashua-pal-core' ); ?></button>
				</div>
				<div class="pal-icon-modal__body">

					<div class="pal-icon-modal__panel-builtin" data-pal-modal-panel="builtin">
						<div class="pal-icon-grid">
							<?php foreach ( $catalog as $name => $label ) : ?>
								<button type="button" data-pal-icon-name="<?php echo esc_attr( $name ); ?>" title="<?php echo esc_attr( $label ); ?>">
									<?php echo NPAL_Icons::render( $name ); ?>
									<span class="pal-icon-grid__label"><?php echo esc_html( $label ); ?></span>
								</button>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="pal-icon-modal__panel-upload" data-pal-modal-panel="upload" hidden>
						<button type="button" class="button button-primary" data-pal-icon-upload>
							<span class="dashicons dashicons-upload" style="margin-top:3px;"></span>
							<?php esc_html_e( 'Upload from media library', 'nashua-pal-core' ); ?>
						</button>
						<p><?php esc_html_e( 'PNG / SVG / JPG. Square images at 64×64 or 128×128 work best. Once selected, the URL is saved on this field — no other config needed.', 'nashua-pal-core' ); ?></p>
					</div>

					<div class="pal-icon-modal__panel-fa" data-pal-modal-panel="fa" hidden>
						<div class="pal-icon-fa-howto">
							<ol>
								<li>
									<?php
									echo wp_kses(
										sprintf(
											/* translators: %s: Font Awesome icons link */
											__( 'Browse the full Font Awesome 6 Free icon library: %s', 'nashua-pal-core' ),
											'<a href="https://fontawesome.com/search?ic=free&o=r" target="_blank" rel="noopener noreferrer"><strong>fontawesome.com/search</strong> ↗</a>'
										),
										array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ), 'strong' => array() )
									);
									?>
								</li>
								<li>
									<?php esc_html_e( 'Click any icon → copy its CSS class (e.g. ', 'nashua-pal-core' ); ?><code>fas fa-shield-halved</code><?php esc_html_e( ').', 'nashua-pal-core' ); ?>
								</li>
								<li>
									<?php esc_html_e( 'Paste it below — the live preview updates as you type.', 'nashua-pal-core' ); ?>
								</li>
							</ol>
						</div>

						<label for="pal-icon-fa-input"><?php esc_html_e( 'Font Awesome class', 'nashua-pal-core' ); ?></label>
						<input type="text" id="pal-icon-fa-input" placeholder="fas fa-shield-alt" />
						<p class="pal-icon-modal__hint">
							<?php
							echo wp_kses(
								__( 'Free FA 6 examples: <code>fas fa-shield-halved</code>, <code>fas fa-users</code>, <code>fas fa-bullseye</code>, <code>fas fa-handshake</code>, <code>fas fa-basketball</code>, <code>fas fa-calendar</code>, <code>fas fa-heart</code>, <code>fas fa-bullhorn</code>, <code>fas fa-graduation-cap</code>, <code>fas fa-trophy</code>. Brand: <code>fab fa-facebook</code>, <code>fab fa-instagram</code>, <code>fab fa-youtube</code>, <code>fab fa-x-twitter</code>.', 'nashua-pal-core' ),
								array( 'code' => array() )
							);
							?>
						</p>
						<div style="display:flex; gap:12px; align-items:center;">
							<span class="pal-fa-preview"><i id="pal-icon-fa-preview" class="fas fa-shield-alt"></i></span>
							<button type="button" class="button button-primary" data-pal-icon-fa-apply><?php esc_html_e( 'Apply', 'nashua-pal-core' ); ?></button>
							<a href="https://fontawesome.com/search?ic=free&o=r" target="_blank" rel="noopener noreferrer" class="button button-secondary" style="margin-left:auto;">
								<span class="dashicons dashicons-external" style="margin-top:3px;"></span>
								<?php esc_html_e( 'Open Font Awesome library', 'nashua-pal-core' ); ?>
							</a>
						</div>
					</div>

				</div>
			</div>
		</div>

		<script>
		(function(){
			const modal = document.getElementById('pal-icon-modal');
			if (!modal) return;
			let targetField = null;

			function openModal(field) {
				targetField = field;
				modal.classList.add('is-open');
				modal.setAttribute('aria-hidden', 'false');
				document.body.style.overflow = 'hidden';
				highlightCurrentSelection();
				switchModalTab('builtin');
			}
			function closeModal() {
				modal.classList.remove('is-open');
				modal.setAttribute('aria-hidden', 'true');
				document.body.style.overflow = '';
				targetField = null;
			}
			function switchModalTab(name) {
				modal.querySelectorAll('[data-pal-modal-tab]').forEach(b => b.classList.toggle('is-active', b.dataset.palModalTab === name));
				modal.querySelectorAll('[data-pal-modal-panel]').forEach(p => p.hidden = p.dataset.palModalPanel !== name);
			}
			function highlightCurrentSelection() {
				if (!targetField) return;
				const current = targetField.querySelector('[data-pal-icon-input]').value;
				modal.querySelectorAll('.pal-icon-grid button').forEach(b => {
					b.classList.toggle('is-selected', b.dataset.palIconName === current);
				});
				const faInput = document.getElementById('pal-icon-fa-input');
				if (faInput && current && current.indexOf('fa') === 0) faInput.value = current;
			}
			function applyValue(value) {
				if (!targetField) return;
				const input = targetField.querySelector('[data-pal-icon-input]');
				const preview = targetField.querySelector('[data-pal-icon-preview]');
				input.value = value;
				preview.innerHTML = renderPreview(value);
				closeModal();
			}
			function renderPreview(value) {
				if (!value) return '';
				if (/^https?:\/\//i.test(value)) {
					return '<img src="' + value.replace(/"/g, '&quot;') + '" alt="" />';
				}
				const tile = modal.querySelector('.pal-icon-grid button[data-pal-icon-name="' + cssEscape(value) + '"]');
				if (tile) {
					const svg = tile.querySelector('svg');
					if (svg) return svg.outerHTML;
				}
				return '<i class="' + value.replace(/"/g, '') + '"></i>';
			}
			function cssEscape(s) { return (s || '').replace(/(["\\])/g, '\\$1'); }

			// Specific listeners on each button — avoids any chance of a document-wide
			// click handler interfering with form submissions elsewhere on the page.
			document.querySelectorAll('[data-pal-icon-open]').forEach(btn => {
				btn.addEventListener('click', function(e) {
					e.preventDefault();
					openModal(btn.closest('[data-pal-icon-field]'));
				});
			});
			modal.querySelectorAll('[data-pal-icon-close]').forEach(el => {
				el.addEventListener('click', function(e) { e.preventDefault(); closeModal(); });
			});
			modal.querySelectorAll('[data-pal-modal-tab]').forEach(btn => {
				btn.addEventListener('click', function(e) {
					e.preventDefault();
					switchModalTab(btn.dataset.palModalTab);
				});
			});
			modal.querySelectorAll('.pal-icon-grid button[data-pal-icon-name]').forEach(btn => {
				btn.addEventListener('click', function(e) {
					e.preventDefault();
					applyValue(btn.dataset.palIconName);
				});
			});
			document.addEventListener('keydown', function(e) {
				if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
			});

			// Upload custom via WP media
			const uploadBtn = modal.querySelector('[data-pal-icon-upload]');
			let mediaFrame = null;
			if (uploadBtn) {
				uploadBtn.addEventListener('click', () => {
					if (!window.wp || !wp.media) {
						alert('<?php echo esc_js( __( 'Media library is not available on this screen.', 'nashua-pal-core' ) ); ?>');
						return;
					}
					if (!mediaFrame) {
						mediaFrame = wp.media({
							title: '<?php echo esc_js( __( 'Choose a custom icon', 'nashua-pal-core' ) ); ?>',
							button: { text: '<?php echo esc_js( __( 'Use this icon', 'nashua-pal-core' ) ); ?>' },
							library: { type: 'image' },
							multiple: false,
						});
						mediaFrame.on('select', () => {
							const att = mediaFrame.state().get('selection').first().toJSON();
							applyValue(att.url);
						});
					}
					mediaFrame.open();
				});
			}
			// Font Awesome tab live preview + apply
			const faInput = document.getElementById('pal-icon-fa-input');
			const faPreview = document.getElementById('pal-icon-fa-preview');
			const faApply = modal.querySelector('[data-pal-icon-fa-apply]');
			if (faInput && faPreview) {
				faInput.addEventListener('input', () => {
					faPreview.className = faInput.value.trim() || 'fas fa-shield-alt';
				});
			}
			if (faApply) {
				faApply.addEventListener('click', () => {
					const v = (faInput.value || '').trim();
					if (v) applyValue(v);
				});
			}
		})();
		</script>
		<?php
	}
}
