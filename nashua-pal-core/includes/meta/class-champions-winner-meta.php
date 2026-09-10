<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Champions_Winner_Meta {

	const NONCE = 'npal_champion_winner_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Champions_Winner::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	/* ------------------------------------------------------------------ */
	/*  Category helpers (taxonomy-driven)                                 */
	/* ------------------------------------------------------------------ */

	/**
	 * Get all award category options as slug => label.
	 * Used by archive template dropdowns, Elementor widget controls, etc.
	 */
	public static function category_options() {
		$options = array( '' => __( '— Select Category —', 'nashua-pal-core' ) );
		$terms   = get_terms( array(
			'taxonomy'   => NPAL_CPT_Champions_Winner::TAXONOMY,
			'hide_empty' => false,
		) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->slug ] = $term->name;
			}
		}
		return $options;
	}

	/**
	 * Get category label by slug.
	 */
	public static function category_label( $slug ) {
		if ( ! $slug ) { return ''; }
		$term = get_term_by( 'slug', $slug, NPAL_CPT_Champions_Winner::TAXONOMY );
		return $term ? $term->name : '';
	}

	/* ------------------------------------------------------------------ */
	/*  Meta boxes                                                         */
	/* ------------------------------------------------------------------ */

	public function add_boxes() {
		$pt = NPAL_CPT_Champions_Winner::POST_TYPE;

		add_meta_box(
			'pal_champion_details',
			__( 'Winner Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			$pt, 'normal', 'high'
		);

		add_meta_box(
			'pal_champion_award',
			__( 'Award Information', 'nashua-pal-core' ),
			array( $this, 'render_award' ),
			$pt, 'normal', 'high'
		);

		add_meta_box(
			'pal_champion_media',
			__( 'Media & Links', 'nashua-pal-core' ),
			array( $this, 'render_media' ),
			$pt, 'normal', 'default'
		);

		add_meta_box(
			'pal_champion_display',
			__( 'Display Settings', 'nashua-pal-core' ),
			array( $this, 'render_display' ),
			$pt, 'side', 'default'
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Winner Details                                             */
	/* ------------------------------------------------------------------ */

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$name = get_post_meta( $post->ID, '_pal_champion_name', true );
		$bio  = get_post_meta( $post->ID, '_pal_champion_bio', true );
		$org  = get_post_meta( $post->ID, '_pal_champion_organization', true );
		$sport = get_post_meta( $post->ID, '_pal_champion_sport', true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_champion_name"><?php esc_html_e( 'Winner Name', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_champion_name" name="pal_champion_name" class="large-text"
						value="<?php echo esc_attr( $name ); ?>"
						placeholder="<?php esc_attr_e( 'Full name of the winner', 'nashua-pal-core' ); ?>" />
					<p class="description"><?php esc_html_e( 'If different from the post title. Leave blank to use the post title.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_champion_organization"><?php esc_html_e( 'School or Organization', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_champion_organization" name="pal_champion_organization" class="regular-text"
						value="<?php echo esc_attr( $org ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. Nashua High School North', 'nashua-pal-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_champion_sport"><?php esc_html_e( 'Sport / Program', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_champion_sport" name="pal_champion_sport" class="regular-text"
						value="<?php echo esc_attr( $sport ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. Basketball, Mentorship Program', 'nashua-pal-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_champion_bio"><?php esc_html_e( 'Short Bio / Story', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<textarea id="pal_champion_bio" name="pal_champion_bio" class="large-text" rows="4"
						placeholder="<?php esc_attr_e( 'Brief description shown on the card. Use the main editor for the full story.', 'nashua-pal-core' ); ?>"
					><?php echo esc_textarea( $bio ); ?></textarea>
					<p class="description"><?php esc_html_e( 'Displayed as the card excerpt. If blank, the post excerpt is used instead.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Award Information                                          */
	/* ------------------------------------------------------------------ */

	public function render_award( $post ) {
		$award_title = get_post_meta( $post->ID, '_pal_champion_award_title', true );
		$year        = get_post_meta( $post->ID, '_pal_champion_year', true );

		// Get current category from taxonomy.
		$current_term = NPAL_CPT_Champions_Winner::get_award_category( $post->ID );
		$current_slug = $current_term ? $current_term->slug : '';

		$cat_options = self::category_options();
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_champion_award_title"><?php esc_html_e( 'Award Title', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_champion_award_title" name="pal_champion_award_title" class="large-text"
						value="<?php echo esc_attr( $award_title ); ?>"
						placeholder="<?php esc_attr_e( 'e.g. Officer of the Year, Youth Athlete of the Year', 'nashua-pal-core' ); ?>" />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_champion_year"><?php esc_html_e( 'Award Year', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="number" id="pal_champion_year" name="pal_champion_year" style="width:100px;"
						value="<?php echo esc_attr( $year ); ?>"
						min="2000" max="2099" step="1"
						placeholder="<?php echo esc_attr( gmdate( 'Y' ) ); ?>" />
					<p class="description"><?php esc_html_e( 'The year this award was given. Used for filtering and grouping.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_champion_category"><?php esc_html_e( 'Award Category', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<select id="pal_champion_category" name="pal_champion_category" style="min-width:240px;">
						<?php foreach ( $cat_options as $val => $label ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_slug, $val ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php
						$tax_url = admin_url( 'edit-tags.php?taxonomy=' . NPAL_CPT_Champions_Winner::TAXONOMY . '&post_type=' . NPAL_CPT_Champions_Winner::POST_TYPE );
						printf(
							/* translators: %s = link to manage categories */
							esc_html__( 'Select a category or %s.', 'nashua-pal-core' ),
							'<a href="' . esc_url( $tax_url ) . '">' . esc_html__( 'manage categories', 'nashua-pal-core' ) . '</a>'
						);
						?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Media & Links                                              */
	/* ------------------------------------------------------------------ */

	public function render_media( $post ) {
		$url   = get_post_meta( $post->ID, '_pal_champion_external_url', true );
		$video = get_post_meta( $post->ID, '_pal_champion_video_url', true );
		?>
		<p style="margin:0 0 10px;color:#555;font-size:13px;">
			<?php esc_html_e( 'The winner photo is set via the standard "Featured Image" box on the right.', 'nashua-pal-core' ); ?>
		</p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_champion_external_url"><?php esc_html_e( 'External Link', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="url" id="pal_champion_external_url" name="pal_champion_external_url" class="regular-text"
						value="<?php echo esc_attr( $url ); ?>"
						placeholder="https://" />
					<p class="description"><?php esc_html_e( 'Optional link to an external article or page about this winner.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_champion_video_url"><?php esc_html_e( 'YouTube / Video URL', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="url" id="pal_champion_video_url" name="pal_champion_video_url" class="regular-text"
						value="<?php echo esc_attr( $video ); ?>"
						placeholder="https://www.youtube.com/watch?v=..." />
					<p class="description"><?php esc_html_e( 'Optional video link. Displayed on the single winner page.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Display Settings (sidebar)                                 */
	/* ------------------------------------------------------------------ */

	public function render_display( $post ) {
		$featured = get_post_meta( $post->ID, '_pal_champion_featured', true );
		$order    = get_post_meta( $post->ID, '_pal_champion_order', true );
		?>
		<p>
			<label>
				<input type="checkbox" name="pal_champion_featured" value="1" <?php checked( $featured, '1' ); ?> />
				<?php esc_html_e( 'Featured Champion', 'nashua-pal-core' ); ?>
			</label>
		</p>
		<p class="description" style="margin:0 0 14px;"><?php esc_html_e( 'Featured champions are displayed in a larger hero section at the top of the page.', 'nashua-pal-core' ); ?></p>

		<p>
			<label for="pal_champion_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label><br />
			<input type="number" id="pal_champion_order" name="pal_champion_order" style="width:80px;"
				value="<?php echo esc_attr( $order ); ?>" min="0" step="1" />
		</p>
		<p class="description"><?php esc_html_e( 'Lower numbers appear first. Default is 0.', 'nashua-pal-core' ); ?></p>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Save                                                               */
	/* ------------------------------------------------------------------ */

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( $_POST[ self::NONCE ], self::NONCE ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		// Winner Details.
		$this->save_text( $post_id, 'pal_champion_name', '_pal_champion_name' );
		$this->save_text( $post_id, 'pal_champion_organization', '_pal_champion_organization' );
		$this->save_text( $post_id, 'pal_champion_sport', '_pal_champion_sport' );

		// Bio — allow basic HTML via wp_kses_post.
		$bio = isset( $_POST['pal_champion_bio'] ) ? wp_kses_post( wp_unslash( $_POST['pal_champion_bio'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_champion_bio', $bio );

		// Award Information.
		$this->save_text( $post_id, 'pal_champion_award_title', '_pal_champion_award_title' );

		$year = isset( $_POST['pal_champion_year'] ) ? absint( $_POST['pal_champion_year'] ) : 0;
		$this->set_or_delete( $post_id, '_pal_champion_year', $year ? (string) $year : '' );

		// Category — set taxonomy term.
		$cat_slug = isset( $_POST['pal_champion_category'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_champion_category'] ) ) : '';
		if ( $cat_slug ) {
			wp_set_object_terms( $post_id, $cat_slug, NPAL_CPT_Champions_Winner::TAXONOMY );
		} else {
			wp_set_object_terms( $post_id, array(), NPAL_CPT_Champions_Winner::TAXONOMY );
		}

		// Media & Links.
		$this->save_url( $post_id, 'pal_champion_external_url', '_pal_champion_external_url' );
		$this->save_url( $post_id, 'pal_champion_video_url', '_pal_champion_video_url' );

		// Display Settings.
		$featured = ! empty( $_POST['pal_champion_featured'] ) ? '1' : '';
		$this->set_or_delete( $post_id, '_pal_champion_featured', $featured );

		$order = isset( $_POST['pal_champion_order'] ) ? absint( $_POST['pal_champion_order'] ) : 0;
		update_post_meta( $post_id, '_pal_champion_order', $order );
	}

	/* ------------------------------------------------------------------ */
	/*  Helpers                                                            */
	/* ------------------------------------------------------------------ */

	private function save_text( $post_id, $field, $meta_key ) {
		$val = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
		$this->set_or_delete( $post_id, $meta_key, $val );
	}

	private function save_url( $post_id, $field, $meta_key ) {
		$raw = isset( $_POST[ $field ] ) ? wp_unslash( $_POST[ $field ] ) : '';
		$val = esc_url_raw( trim( $raw ) );
		$this->set_or_delete( $post_id, $meta_key, $val );
	}

	private function set_or_delete( $post_id, $key, $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
