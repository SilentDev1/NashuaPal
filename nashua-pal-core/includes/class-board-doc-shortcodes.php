<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Shortcodes for Board Documents.
 *
 * [pal_board_event_materials count="50"]
 * [pal_board_documents count="50"]
 * [pal_board_meeting_minutes count="50"]
 *
 * @package NashuaPalCore
 * @since   1.54.0
 */
final class NPAL_Board_Doc_Shortcodes {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'pal_board_event_materials',  array( $this, 'event_materials' ) );
		add_shortcode( 'pal_board_documents',        array( $this, 'board_documents' ) );
		add_shortcode( 'pal_board_meeting_minutes',  array( $this, 'meeting_minutes' ) );

		// Render popup overlay once in the footer.
		add_action( 'wp_footer', array( $this, 'render_overlay' ) );
	}

	/* ================================================================== */
	/*  Helper: query board docs by section                                */
	/* ================================================================== */

	private function query_docs( $section, $count = 50 ) {
		return new WP_Query( array(
			'post_type'      => NPAL_CPT_Board_Doc::POST_TYPE,
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'meta_key'       => '_board_doc_order',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array(
				array( 'key' => '_board_doc_section', 'value' => $section ),
			),
		) );
	}

	/* ------------------------------------------------------------------ */
	/*  Helper: file-type icon class                                       */
	/* ------------------------------------------------------------------ */

	private function file_icon( $file_id ) {
		if ( ! $file_id ) { return 'fas fa-file'; }
		$path = get_attached_file( $file_id );
		$ext  = $path ? strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) : '';
		switch ( $ext ) {
			case 'pdf':  return 'fas fa-file-pdf';
			case 'doc':
			case 'docx': return 'fas fa-file-word';
			case 'xls':
			case 'xlsx': return 'fas fa-file-excel';
			case 'ppt':
			case 'pptx': return 'fas fa-file-powerpoint';
			case 'jpg':
			case 'jpeg':
			case 'png':
			case 'gif':  return 'fas fa-file-image';
			default:     return 'fas fa-file-alt';
		}
	}

	private function file_icon_color( $file_id ) {
		if ( ! $file_id ) { return '#666'; }
		$path = get_attached_file( $file_id );
		$ext  = $path ? strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) : '';
		switch ( $ext ) {
			case 'pdf':  return '#E53935';
			case 'doc':
			case 'docx': return '#1565C0';
			case 'xls':
			case 'xlsx': return '#2E7D32';
			case 'ppt':
			case 'pptx': return '#E65100';
			default:     return '#F4A51C';
		}
	}

	/* ------------------------------------------------------------------ */
	/*  Helper: sort groups by category display order                      */
	/* ------------------------------------------------------------------ */

	private function sort_groups_by_category( $groups, $section ) {
		$ordered_names = NPAL_Board_Doc_Categories::get_names( $section );
		if ( empty( $ordered_names ) ) { return $groups; }

		$sorted = array();
		// First add groups that appear in the category order.
		foreach ( $ordered_names as $name ) {
			if ( isset( $groups[ $name ] ) ) {
				$sorted[ $name ] = $groups[ $name ];
				unset( $groups[ $name ] );
			}
		}
		// Append any remaining groups not in the category list.
		foreach ( $groups as $name => $docs ) {
			$sorted[ $name ] = $docs;
		}
		return $sorted;
	}

	/* ------------------------------------------------------------------ */
	/*  Helper: View + Download button pair                                */
	/* ------------------------------------------------------------------ */

	private function render_buttons( $file_url, $css_prefix = 'pal-board-doc-events' ) {
		if ( ! $file_url ) { return; }
		?>
		<div class="<?php echo esc_attr( $css_prefix ); ?>__actions">
			<button type="button" class="<?php echo esc_attr( $css_prefix ); ?>__btn <?php echo esc_attr( $css_prefix ); ?>__btn--view js-board-doc-view"
				data-board-doc-url="<?php echo esc_url( $file_url ); ?>">
				<i class="fas fa-eye"></i> <?php esc_html_e( 'View', 'nashua-pal-core' ); ?>
			</button>
			<a href="<?php echo esc_url( $file_url ); ?>" class="<?php echo esc_attr( $css_prefix ); ?>__btn <?php echo esc_attr( $css_prefix ); ?>__btn--download"
				download>
				<i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'nashua-pal-core' ); ?>
			</a>
		</div>
		<?php
	}

	/* ================================================================== */
	/*  [pal_board_event_materials] — tabbed file list                      */
	/* ================================================================== */

	public function event_materials( $atts ) {
		$a = shortcode_atts( array( 'count' => '50' ), $atts, 'pal_board_event_materials' );
		$count = max( 1, (int) $a['count'] );
		$query = $this->query_docs( 'event_materials', $count );

		if ( ! $query->have_posts() ) {
			return '<p class="pal-board-doc-empty">' . esc_html__( 'No event materials found.', 'nashua-pal-core' ) . '</p>';
		}

		// Collect docs grouped by _board_doc_group.
		$groups = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$pid     = get_the_ID();
			$grp     = get_post_meta( $pid, '_board_doc_group', true );
			if ( ! $grp ) { $grp = __( 'Event Materials', 'nashua-pal-core' ); }
			$file_id = get_post_meta( $pid, '_board_doc_file_id', true );
			$groups[ $grp ][] = array(
				'title'       => get_the_title(),
				'description' => get_post_meta( $pid, '_board_doc_description', true ),
				'file_id'     => $file_id,
				'file_url'    => $file_id ? wp_get_attachment_url( $file_id ) : '',
				'icon_class'  => $this->file_icon( $file_id ),
				'icon_color'  => $this->file_icon_color( $file_id ),
			);
		}
		wp_reset_postdata();

		// Sort groups by category display order.
		$groups = $this->sort_groups_by_category( $groups, 'event_materials' );
		$group_keys = array_keys( $groups );
		$uid        = 'evt-' . wp_rand( 1000, 9999 );

		ob_start();
		?>
		<div class="pal-board-doc-events" id="<?php echo esc_attr( $uid ); ?>">
			<!-- Tabs -->
			<?php if ( count( $group_keys ) > 1 ) : ?>
			<div class="pal-board-doc-events__tabs" role="tablist">
				<?php foreach ( $group_keys as $i => $gk ) : ?>
					<button type="button" class="pal-board-doc-events__tab<?php echo 0 === $i ? ' is-active' : ''; ?>"
						data-board-doc-tab="<?php echo esc_attr( sanitize_title( $gk ) ); ?>"><?php echo esc_html( strtoupper( $gk ) ); ?></button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<!-- Panels -->
			<?php foreach ( $groups as $gk => $docs ) : ?>
			<div class="pal-board-doc-events__panel<?php echo $gk === $group_keys[0] ? ' is-active' : ''; ?>"
				data-board-doc-panel="<?php echo esc_attr( sanitize_title( $gk ) ); ?>">
				<?php foreach ( $docs as $doc ) : ?>
				<div class="pal-board-doc-events__row">
					<div class="pal-board-doc-events__icon" style="color:<?php echo esc_attr( $doc['icon_color'] ); ?>">
						<i class="<?php echo esc_attr( $doc['icon_class'] ); ?>"></i>
					</div>
					<div class="pal-board-doc-events__info">
						<span class="pal-board-doc-events__title"><?php echo esc_html( $doc['title'] ); ?></span>
						<?php if ( $doc['description'] ) : ?>
							<span class="pal-board-doc-events__desc"><?php echo esc_html( $doc['description'] ); ?></span>
						<?php endif; ?>
					</div>
					<?php $this->render_buttons( $doc['file_url'], 'pal-board-doc-events' ); ?>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/* ================================================================== */
	/*  [pal_board_documents] — grouped by Document Type, expandable cards  */
	/* ================================================================== */

	public function board_documents( $atts ) {
		$a = shortcode_atts( array( 'count' => '50' ), $atts, 'pal_board_documents' );
		$count = max( 1, (int) $a['count'] );
		$query = $this->query_docs( 'board_documents', $count );

		if ( ! $query->have_posts() ) {
			return '<p class="pal-board-doc-empty">' . esc_html__( 'No board documents found.', 'nashua-pal-core' ) . '</p>';
		}

		// Collect docs grouped by Document Type (group), then by year within type.
		$type_groups = array();
		$ungrouped   = array();
		while ( $query->have_posts() ) {
			$query->the_post();
			$pid     = get_the_ID();
			$grp     = get_post_meta( $pid, '_board_doc_group', true );
			$year    = get_post_meta( $pid, '_board_doc_year', true );
			$file_id = get_post_meta( $pid, '_board_doc_file_id', true );
			$desc    = get_post_meta( $pid, '_board_doc_description', true );
			$item = array(
				'title'       => get_the_title(),
				'year'        => $year,
				'file_url'    => $file_id ? wp_get_attachment_url( $file_id ) : '',
				'file_id'     => $file_id,
				'description' => $desc,
			);
			if ( $grp ) {
				$type_groups[ $grp ][] = $item;
			} else {
				$ungrouped[] = $item;
			}
		}
		wp_reset_postdata();

		// Sort type groups by category display order.
		$type_groups = $this->sort_groups_by_category( $type_groups, 'board_documents' );

		ob_start();
		$card_index = 0;
		?>
		<div class="pal-board-doc-docs">
			<?php foreach ( $type_groups as $type_name => $docs ) :
				$doc_count  = count( $docs );
				$is_first   = ( 0 === $card_index );
				$card_index++;

				// Get unique years for sub-tabs within this type.
				$years = array();
				foreach ( $docs as $d ) {
					if ( $d['year'] && ! in_array( $d['year'], $years, true ) ) { $years[] = $d['year']; }
				}
				rsort( $years ); // newest first
			?>
			<div class="pal-board-doc-docs__type-card">
				<div class="pal-board-doc-docs__type-header">
					<span class="pal-board-doc-docs__type-name"><?php echo esc_html( $type_name ); ?></span>
					<?php if ( count( $years ) > 1 ) : ?>
					<div class="pal-board-doc-docs__type-tabs" role="tablist">
						<button type="button" class="pal-board-doc-docs__tab is-active"
							data-board-doc-type-tab="all"><?php esc_html_e( 'All', 'nashua-pal-core' ); ?></button>
						<?php foreach ( $years as $yr ) : ?>
							<button type="button" class="pal-board-doc-docs__tab"
								data-board-doc-type-tab="<?php echo esc_attr( $yr ); ?>"><?php echo esc_html( $yr ); ?></button>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
					<div class="pal-board-doc-docs__type-meta">
						<span class="pal-board-doc-docs__count"><?php echo esc_html( $doc_count . ' ' . _n( 'Document', 'Documents', $doc_count, 'nashua-pal-core' ) ); ?></span>
						<button type="button" class="pal-board-doc-docs__toggle<?php echo $is_first ? '' : ' is-collapsed'; ?>" aria-label="<?php esc_attr_e( 'Toggle', 'nashua-pal-core' ); ?>">
							<i class="fas fa-chevron-down"></i>
						</button>
					</div>
				</div>
				<div class="pal-board-doc-docs__type-body<?php echo $is_first ? '' : ' is-collapsed'; ?>">
					<?php foreach ( $docs as $d ) :
						$has_content = ! empty( $d['description'] );
						$has_file    = ! empty( $d['file_url'] );
					?>
					<div class="pal-board-doc-docs__item" data-board-doc-file-year="<?php echo esc_attr( $d['year'] ); ?>">
						<?php if ( $has_file || ! $has_content ) : ?>
						<div class="pal-board-doc-docs__file-row">
							<div class="pal-board-doc-docs__file-icon" style="color:<?php echo esc_attr( $this->file_icon_color( $d['file_id'] ) ); ?>">
								<i class="<?php echo esc_attr( $this->file_icon( $d['file_id'] ) ); ?>"></i>
							</div>
							<span class="pal-board-doc-docs__file-title"><?php echo esc_html( $d['title'] ); ?></span>
							<?php if ( $d['year'] ) : ?>
								<span class="pal-board-doc-docs__file-year"><?php echo esc_html( $d['year'] ); ?></span>
							<?php endif; ?>
							<?php if ( $has_content ) : ?>
								<button type="button" class="pal-board-doc-docs__btn pal-board-doc-docs__btn--view js-board-doc-view-text"
									data-board-doc-title="<?php echo esc_attr( $d['title'] ); ?>">
									<i class="fas fa-eye"></i> <?php esc_html_e( 'View', 'nashua-pal-core' ); ?>
								</button>
							<?php endif; ?>
							<?php $this->render_buttons( $d['file_url'], 'pal-board-doc-docs' ); ?>
						</div>
						<?php endif; ?>
						<?php if ( $has_content && ! $has_file ) : ?>
						<div class="pal-board-doc-docs__file-row">
							<div class="pal-board-doc-docs__file-icon" style="color:var(--pal-navy, #071F3F);">
								<i class="fas fa-file-alt"></i>
							</div>
							<span class="pal-board-doc-docs__file-title"><?php echo esc_html( $d['title'] ); ?></span>
							<?php if ( $d['year'] ) : ?>
								<span class="pal-board-doc-docs__file-year"><?php echo esc_html( $d['year'] ); ?></span>
							<?php endif; ?>
							<button type="button" class="pal-board-doc-docs__btn pal-board-doc-docs__btn--view js-board-doc-view-text"
								data-board-doc-title="<?php echo esc_attr( $d['title'] ); ?>">
								<i class="fas fa-eye"></i> <?php esc_html_e( 'View', 'nashua-pal-core' ); ?>
							</button>
						</div>
						<?php endif; ?>
						<?php if ( $has_content ) : ?>
						<div class="pal-board-doc-docs__content" style="display:none;">
							<?php echo wp_kses_post( $d['description'] ); ?>
						</div>
						<?php endif; ?>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endforeach; ?>

			<?php // Ungrouped docs (no type) ?>
			<?php foreach ( $ungrouped as $d ) : ?>
			<div class="pal-board-doc-docs__standalone-row">
				<div class="pal-board-doc-docs__file-icon" style="color:<?php echo esc_attr( $this->file_icon_color( $d['file_id'] ) ); ?>">
					<i class="<?php echo esc_attr( $this->file_icon( $d['file_id'] ) ); ?>"></i>
				</div>
				<span class="pal-board-doc-docs__file-title"><?php echo esc_html( $d['title'] ); ?></span>
				<?php if ( $d['year'] ) : ?>
					<span class="pal-board-doc-docs__file-year"><?php echo esc_html( $d['year'] ); ?></span>
				<?php endif; ?>
				<?php $this->render_buttons( $d['file_url'], 'pal-board-doc-docs' ); ?>
			</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/* ================================================================== */
	/*  [pal_board_meeting_minutes] — table with date + file links          */
	/* ================================================================== */

	public function meeting_minutes( $atts ) {
		$a = shortcode_atts( array( 'count' => '200', 'per_page' => '10' ), $atts, 'pal_board_meeting_minutes' );
		$count    = max( 1, (int) $a['count'] );
		$per_page = max( 1, (int) $a['per_page'] );

		$query = new WP_Query( array(
			'post_type'      => NPAL_CPT_Board_Doc::POST_TYPE,
			'posts_per_page' => $count,
			'post_status'    => 'publish',
			'meta_key'       => '_board_doc_date',
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
			'meta_query'     => array(
				array( 'key' => '_board_doc_section', 'value' => 'meeting_minutes' ),
			),
		) );

		if ( ! $query->have_posts() ) {
			return '<p class="pal-board-doc-empty">' . esc_html__( 'No meeting minutes found.', 'nashua-pal-core' ) . '</p>';
		}

		// Collect unique years for the filter dropdown.
		$years = array();
		$uid   = 'mm-' . wp_rand( 1000, 9999 );

		ob_start();
		?>
		<div class="pal-board-doc-minutes" id="<?php echo esc_attr( $uid ); ?>" data-per-page="<?php echo (int) $per_page; ?>">

			<!-- Year filter -->
			<div class="pal-board-doc-minutes__toolbar">
				<label for="<?php echo esc_attr( $uid ); ?>-filter" class="pal-board-doc-minutes__filter-label"><?php esc_html_e( 'Filter by year:', 'nashua-pal-core' ); ?></label>
				<select id="<?php echo esc_attr( $uid ); ?>-filter" class="pal-board-doc-minutes__filter js-minutes-year-filter">
					<option value=""><?php esc_html_e( 'All Years', 'nashua-pal-core' ); ?></option>
				</select>
			</div>

			<table class="pal-board-doc-minutes__table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'nashua-pal-core' ); ?></th>
						<th><?php esc_html_e( 'Meeting Minutes', 'nashua-pal-core' ); ?></th>
						<th><?php esc_html_e( 'Financial Report', 'nashua-pal-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php while ( $query->have_posts() ) : $query->the_post();
					$pid       = get_the_ID();
					$date      = get_post_meta( $pid, '_board_doc_date', true );
					$file_id   = get_post_meta( $pid, '_board_doc_file_id', true );
					$file2_id  = get_post_meta( $pid, '_board_doc_file2_id', true );
					$file3_id  = get_post_meta( $pid, '_board_doc_file3_id', true );
					$file4_id  = get_post_meta( $pid, '_board_doc_file4_id', true );
					$file_url  = $file_id  ? wp_get_attachment_url( $file_id )  : '';
					$file2_url = $file2_id ? wp_get_attachment_url( $file2_id ) : '';
					$file3_url = $file3_id ? wp_get_attachment_url( $file3_id ) : '';
					$file4_url = $file4_id ? wp_get_attachment_url( $file4_id ) : '';
					$formatted = $date ? wp_date( 'F j, Y', strtotime( $date ) ) : '';
					$desc      = get_post_meta( $pid, '_board_doc_description', true );
					$has_text  = ! empty( $desc );
					$row_year  = $date ? gmdate( 'Y', strtotime( $date ) ) : '';
					if ( $row_year && ! in_array( $row_year, $years, true ) ) { $years[] = $row_year; }
				?>
				<tr data-year="<?php echo esc_attr( $row_year ); ?>">
					<td data-label="<?php esc_attr_e( 'Date', 'nashua-pal-core' ); ?>">
						<em><?php echo esc_html( $formatted ); ?></em>
					</td>
					<td data-label="<?php esc_attr_e( 'Meeting Minutes', 'nashua-pal-core' ); ?>">
						<?php if ( $has_text ) : ?>
							<button type="button" class="pal-board-doc-minutes__link js-board-doc-view-text"
								data-board-doc-title="<?php echo esc_attr( $formatted . ' — ' . __( 'Meeting Minutes', 'nashua-pal-core' ) ); ?>">
								<i class="fas fa-file-alt"></i> <?php esc_html_e( 'View Minutes', 'nashua-pal-core' ); ?>
							</button>
							<div class="pal-board-doc-docs__content" style="display:none;">
								<?php echo wp_kses_post( $desc ); ?>
							</div>
						<?php elseif ( $file_url ) : ?>
							<button type="button" class="pal-board-doc-minutes__link js-board-doc-view"
								data-board-doc-url="<?php echo esc_url( $file_url ); ?>">
								<i class="fas fa-file-alt"></i> <?php esc_html_e( 'View Minutes', 'nashua-pal-core' ); ?>
							</button>
						<?php else : ?>
							<span class="pal-board-doc-minutes__empty">&mdash;</span>
						<?php endif; ?>
					</td>
					<td data-label="<?php esc_attr_e( 'Financial Report', 'nashua-pal-core' ); ?>">
						<?php if ( $file2_url || $file3_url || $file4_url ) : ?>
							<?php if ( $file2_url ) : ?>
								<button type="button" class="pal-board-doc-minutes__link pal-board-doc-minutes__link--report js-board-doc-view"
									data-board-doc-url="<?php echo esc_url( $file2_url ); ?>">
									<i class="<?php echo esc_attr( $this->file_icon( $file2_id ) ); ?>"></i> <?php esc_html_e( 'View Report', 'nashua-pal-core' ); ?>
								</button>
							<?php endif; ?>
							<?php if ( $file3_url ) : ?>
								<button type="button" class="pal-board-doc-minutes__link pal-board-doc-minutes__link--report js-board-doc-view"
									data-board-doc-url="<?php echo esc_url( $file3_url ); ?>"
									style="margin-top:6px;">
									<i class="<?php echo esc_attr( $this->file_icon( $file3_id ) ); ?>"></i> <?php esc_html_e( 'Revised Report', 'nashua-pal-core' ); ?>
								</button>
							<?php endif; ?>
							<?php if ( $file4_url ) : ?>
								<button type="button" class="pal-board-doc-minutes__link pal-board-doc-minutes__link--report js-board-doc-view"
									data-board-doc-url="<?php echo esc_url( $file4_url ); ?>"
									style="margin-top:6px;">
									<i class="<?php echo esc_attr( $this->file_icon( $file4_id ) ); ?>"></i> <?php esc_html_e( 'Additional Report', 'nashua-pal-core' ); ?>
								</button>
							<?php endif; ?>
						<?php else : ?>
							<span class="pal-board-doc-minutes__empty">&mdash;</span>
						<?php endif; ?>
					</td>
				</tr>
				<?php endwhile; ?>
				</tbody>
			</table>

			<!-- Pagination -->
			<div class="pal-board-doc-minutes__pagination js-minutes-pagination"></div>
		</div>

		<script>
		(function(){
			var wrap   = document.getElementById('<?php echo esc_js( $uid ); ?>');
			if (!wrap) return;
			var perPage = parseInt(wrap.getAttribute('data-per-page'),10) || 10;
			var filter  = wrap.querySelector('.js-minutes-year-filter');
			var tbody   = wrap.querySelector('tbody');
			var allRows = Array.prototype.slice.call(tbody.querySelectorAll('tr[data-year]'));
			var pager   = wrap.querySelector('.js-minutes-pagination');

			// Populate year dropdown from collected years.
			var yearSet = {};
			allRows.forEach(function(r){ var y=r.getAttribute('data-year'); if(y) yearSet[y]=true; });
			var years = Object.keys(yearSet).sort().reverse();
			years.forEach(function(y){
				var o = document.createElement('option');
				o.value = y; o.textContent = y;
				filter.appendChild(o);
			});

			var currentPage = 1;

			function getVisible(){
				var sel = filter.value;
				return allRows.filter(function(r){
					return !sel || r.getAttribute('data-year') === sel;
				});
			}

			function render(){
				var visible  = getVisible();
				var total    = visible.length;
				var pages    = Math.ceil(total / perPage);
				if (currentPage > pages) currentPage = pages || 1;
				var start    = (currentPage - 1) * perPage;
				var end      = start + perPage;
				var pageRows = visible.slice(start, end);

				// Hide all, show only current page rows.
				allRows.forEach(function(r){ r.style.display = 'none'; });
				pageRows.forEach(function(r){ r.style.display = ''; });

				// Build pagination.
				pager.innerHTML = '';
				if (pages <= 1) return;

				var info = document.createElement('span');
				info.className = 'pal-board-doc-minutes__page-info';
				info.textContent = 'Page ' + currentPage + ' of ' + pages + ' (' + total + ' records)';
				pager.appendChild(info);

				var nav = document.createElement('div');
				nav.className = 'pal-board-doc-minutes__page-btns';

				// Prev
				var prev = document.createElement('button');
				prev.type = 'button';
				prev.className = 'pal-board-doc-minutes__page-btn';
				prev.innerHTML = '&laquo; Prev';
				prev.disabled = currentPage <= 1;
				prev.addEventListener('click', function(){ currentPage--; render(); wrap.scrollIntoView({behavior:'smooth',block:'start'}); });
				nav.appendChild(prev);

				// Page numbers
				for (var i = 1; i <= pages; i++) {
					var btn = document.createElement('button');
					btn.type = 'button';
					btn.className = 'pal-board-doc-minutes__page-btn' + (i === currentPage ? ' is-active' : '');
					btn.textContent = i;
					btn.setAttribute('data-page', i);
					btn.addEventListener('click', function(){ currentPage = parseInt(this.getAttribute('data-page'),10); render(); wrap.scrollIntoView({behavior:'smooth',block:'start'}); });
					nav.appendChild(btn);
				}

				// Next
				var next = document.createElement('button');
				next.type = 'button';
				next.className = 'pal-board-doc-minutes__page-btn';
				next.innerHTML = 'Next &raquo;';
				next.disabled = currentPage >= pages;
				next.addEventListener('click', function(){ currentPage++; render(); wrap.scrollIntoView({behavior:'smooth',block:'start'}); });
				nav.appendChild(next);

				pager.appendChild(nav);
			}

			filter.addEventListener('change', function(){ currentPage = 1; render(); });
			render();
		})();
		</script>
		<?php
		wp_reset_postdata();
		return ob_get_clean();
	}

	/* ================================================================== */
	/*  Popup overlay (rendered once in wp_footer)                          */
	/* ================================================================== */

	public function render_overlay() {
		?>
		<div class="pal-board-doc-overlay" id="pal-board-doc-overlay" aria-hidden="true">
			<div class="pal-board-doc-overlay__inner">
				<div class="pal-board-doc-overlay__header">
					<h3 class="pal-board-doc-overlay__title"></h3>
					<a href="#" class="pal-board-doc-overlay__dl" download>
						<i class="fas fa-download"></i> <?php esc_html_e( 'Download', 'nashua-pal-core' ); ?>
					</a>
					<button type="button" class="pal-board-doc-overlay__close" aria-label="<?php esc_attr_e( 'Close', 'nashua-pal-core' ); ?>">
						<i class="fas fa-times"></i>
					</button>
				</div>
				<div class="pal-board-doc-overlay__body">
					<iframe title="<?php esc_attr_e( 'Document Viewer', 'nashua-pal-core' ); ?>"></iframe>
				</div>
			</div>
		</div>
		<?php
	}
}
