<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Partner_Meta {

	const NONCE = 'npal_partner_meta';

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_boxes' ) );
		add_action( 'save_post_' . NPAL_CPT_Partner::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	/* ------------------------------------------------------------------ */
	/*  Tier options                                                       */
	/* ------------------------------------------------------------------ */

	public static function tier_options() {
		return array(
			''          => __( '— Select Tier —', 'nashua-pal-core' ),
			'champion'  => __( 'Champion ($5,000+)', 'nashua-pal-core' ),
			'all_star'  => __( 'All-Star ($2,500+)', 'nashua-pal-core' ),
			'friend'    => __( 'Friend ($500+)', 'nashua-pal-core' ),
			'in_kind'   => __( 'In-Kind Supporter', 'nashua-pal-core' ),
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Meta boxes                                                         */
	/* ------------------------------------------------------------------ */

	public function add_boxes() {
		add_meta_box(
			'pal_partner_details',
			__( 'Partner / Sponsor Details', 'nashua-pal-core' ),
			array( $this, 'render_details' ),
			NPAL_CPT_Partner::POST_TYPE,
			'normal',
			'high'
		);

		add_meta_box(
			'pal_partner_contact',
			__( 'Contact Information', 'nashua-pal-core' ),
			array( $this, 'render_contact' ),
			NPAL_CPT_Partner::POST_TYPE,
			'normal',
			'default'
		);

		add_meta_box(
			'pal_partner_social',
			__( 'Social Media', 'nashua-pal-core' ),
			array( $this, 'render_social' ),
			NPAL_CPT_Partner::POST_TYPE,
			'normal',
			'default'
		);

		add_meta_box(
			'pal_partner_flags',
			__( 'Display Options', 'nashua-pal-core' ),
			array( $this, 'render_flags' ),
			NPAL_CPT_Partner::POST_TYPE,
			'side',
			'default'
		);
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Details (tier, tagline, website URL)                       */
	/* ------------------------------------------------------------------ */

	public function render_details( $post ) {
		wp_nonce_field( self::NONCE, self::NONCE );

		$url     = get_post_meta( $post->ID, '_pal_partner_url', true );
		$tier    = get_post_meta( $post->ID, '_pal_partner_tier', true );
		$tagline = get_post_meta( $post->ID, '_pal_partner_tagline', true );
		?>
		<p style="margin:0 0 10px;color:#555;font-size:13px;">
			<?php esc_html_e( 'The partner logo is set via the standard "Featured Image" box on the right — that single image is reused everywhere this partner appears (sponsor strip on events, homepage partner carousel, Partners archive). Use a transparent PNG or SVG on a light background for best results.', 'nashua-pal-core' ); ?>
		</p>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="pal_partner_tier"><?php esc_html_e( 'Partnership Tier', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<select id="pal_partner_tier" name="pal_partner_tier" style="min-width:240px;">
						<?php foreach ( self::tier_options() as $val => $label ) : ?>
							<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $tier, $val ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Determines which section this partner appears in on the Community Partners page.', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_partner_tagline"><?php esc_html_e( 'Tagline', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_partner_tagline" name="pal_partner_tagline" class="large-text"
						value="<?php echo esc_attr( $tagline ); ?>"
						placeholder="<?php esc_attr_e( 'Short tagline or partnership description', 'nashua-pal-core' ); ?>" />
					<p class="description"><?php esc_html_e( 'Displayed under the partner name on the partners page. Keep it short (one sentence).', 'nashua-pal-core' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="pal_partner_url"><?php esc_html_e( 'Website URL', 'nashua-pal-core' ); ?></label>
				</th>
				<td>
					<input type="text" id="pal_partner_url" name="pal_partner_url" class="regular-text"
						value="<?php echo esc_attr( $url ); ?>"
						placeholder="https://example.com" />
					<p class="description">
						<?php esc_html_e( 'When set, clicking this partner\'s logo opens this URL in a new tab.', 'nashua-pal-core' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Contact Information                                        */
	/* ------------------------------------------------------------------ */

	public function render_contact( $post ) {
		$email   = get_post_meta( $post->ID, '_pal_partner_email', true );
		$phone   = get_post_meta( $post->ID, '_pal_partner_phone', true );
		$contact = get_post_meta( $post->ID, '_pal_partner_contact_name', true );
		?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="pal_partner_contact_name"><?php esc_html_e( 'Contact Person', 'nashua-pal-core' ); ?></label></th>
				<td><input type="text" id="pal_partner_contact_name" name="pal_partner_contact_name" class="regular-text" value="<?php echo esc_attr( $contact ); ?>" placeholder="<?php esc_attr_e( 'Jane Smith', 'nashua-pal-core' ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="pal_partner_email"><?php esc_html_e( 'Email', 'nashua-pal-core' ); ?></label></th>
				<td><input type="email" id="pal_partner_email" name="pal_partner_email" class="regular-text" value="<?php echo esc_attr( $email ); ?>" placeholder="<?php esc_attr_e( 'partner@example.com', 'nashua-pal-core' ); ?>" /></td>
			</tr>
			<tr>
				<th scope="row"><label for="pal_partner_phone"><?php esc_html_e( 'Phone', 'nashua-pal-core' ); ?></label></th>
				<td><input type="tel" id="pal_partner_phone" name="pal_partner_phone" class="regular-text" value="<?php echo esc_attr( $phone ); ?>" placeholder="<?php esc_attr_e( '(603) 555-0199', 'nashua-pal-core' ); ?>" /></td>
			</tr>
		</table>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Social Media                                               */
	/* ------------------------------------------------------------------ */

	public function render_social( $post ) {
		$socials = array(
			'facebook'  => __( 'Facebook URL', 'nashua-pal-core' ),
			'linkedin'  => __( 'LinkedIn URL', 'nashua-pal-core' ),
			'twitter'   => __( 'Twitter / X URL', 'nashua-pal-core' ),
			'instagram' => __( 'Instagram URL', 'nashua-pal-core' ),
		);
		?>
		<table class="form-table" role="presentation">
			<?php foreach ( $socials as $key => $label ) :
				$val = get_post_meta( $post->ID, '_pal_partner_' . $key, true );
				?>
				<tr>
					<th scope="row"><label for="pal_partner_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
					<td><input type="url" id="pal_partner_<?php echo esc_attr( $key ); ?>" name="pal_partner_<?php echo esc_attr( $key ); ?>" class="regular-text" value="<?php echo esc_attr( $val ); ?>" placeholder="https://" /></td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Render: Display Options (sidebar)                                  */
	/* ------------------------------------------------------------------ */

	public function render_flags( $post ) {
		$featured = get_post_meta( $post->ID, '_pal_partner_featured', true );
		$order    = get_post_meta( $post->ID, '_pal_partner_order', true );
		?>
		<p>
			<label>
				<input type="checkbox" name="pal_partner_featured" value="1" <?php checked( $featured, '1' ); ?> />
				<?php esc_html_e( 'Featured Partner', 'nashua-pal-core' ); ?>
			</label>
		</p>
		<p class="description" style="margin:0 0 14px;"><?php esc_html_e( 'Featured partners get highlighted placement and a larger logo on the partners page.', 'nashua-pal-core' ); ?></p>

		<p>
			<label for="pal_partner_order"><?php esc_html_e( 'Display Order', 'nashua-pal-core' ); ?></label><br />
			<input type="number" id="pal_partner_order" name="pal_partner_order" style="width:80px;" value="<?php echo esc_attr( $order ); ?>" min="0" step="1" />
		</p>
		<p class="description"><?php esc_html_e( 'Lower numbers appear first within their tier. Default is 0.', 'nashua-pal-core' ); ?></p>
		<?php
	}

	/* ------------------------------------------------------------------ */
	/*  Save                                                               */
	/* ------------------------------------------------------------------ */

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE ] ) || ! wp_verify_nonce( $_POST[ self::NONCE ], self::NONCE ) ) { return; }
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		/* Website URL */
		$url_raw = isset( $_POST['pal_partner_url'] ) ? wp_unslash( $_POST['pal_partner_url'] ) : '';
		$url     = esc_url_raw( trim( $url_raw ) );
		$this->set_or_delete( $post_id, '_pal_partner_url', $url );

		/* Partnership Tier */
		$tier_raw = isset( $_POST['pal_partner_tier'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_partner_tier'] ) ) : '';
		$tier     = array_key_exists( $tier_raw, self::tier_options() ) ? $tier_raw : '';
		$this->set_or_delete( $post_id, '_pal_partner_tier', $tier );

		/* Tagline */
		$tagline = isset( $_POST['pal_partner_tagline'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_partner_tagline'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_partner_tagline', $tagline );

		/* Contact fields */
		$contact_name = isset( $_POST['pal_partner_contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_partner_contact_name'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_partner_contact_name', $contact_name );

		$email_raw = isset( $_POST['pal_partner_email'] ) ? wp_unslash( $_POST['pal_partner_email'] ) : '';
		$email     = sanitize_email( trim( $email_raw ) );
		$this->set_or_delete( $post_id, '_pal_partner_email', $email );

		$phone = isset( $_POST['pal_partner_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_partner_phone'] ) ) : '';
		$this->set_or_delete( $post_id, '_pal_partner_phone', $phone );

		/* Social media URLs */
		$social_keys = array( 'facebook', 'linkedin', 'twitter', 'instagram' );
		foreach ( $social_keys as $key ) {
			$raw = isset( $_POST[ 'pal_partner_' . $key ] ) ? wp_unslash( $_POST[ 'pal_partner_' . $key ] ) : '';
			$val = esc_url_raw( trim( $raw ) );
			$this->set_or_delete( $post_id, '_pal_partner_' . $key, $val );
		}

		/* Featured flag */
		$featured = ! empty( $_POST['pal_partner_featured'] ) ? '1' : '';
		$this->set_or_delete( $post_id, '_pal_partner_featured', $featured );

		/* Display Order */
		$order = isset( $_POST['pal_partner_order'] ) ? absint( $_POST['pal_partner_order'] ) : 0;
		update_post_meta( $post_id, '_pal_partner_order', $order );
	}

	/* ------------------------------------------------------------------ */
	/*  Helper                                                             */
	/* ------------------------------------------------------------------ */

	private function set_or_delete( $post_id, $key, $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
