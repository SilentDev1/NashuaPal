<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Job_Meta {

	private static $instance = null;
	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_box' ) );
		add_action( 'save_post_' . NPAL_CPT_Job::POST_TYPE, array( $this, 'save' ), 10, 2 );
	}

	public function add_box() {
		add_meta_box(
			'pal_job_details',
			__( 'Job Details', 'nashua-pal-core' ),
			array( $this, 'render' ),
			NPAL_CPT_Job::POST_TYPE,
			'normal',
			'high'
		);
	}

	public function render( $post ) {
		wp_nonce_field( 'pal_job_meta', 'pal_job_meta_nonce' );
		$type        = get_post_meta( $post->ID, '_pal_job_type', true );
		$location    = get_post_meta( $post->ID, '_pal_job_location', true );
		$department  = get_post_meta( $post->ID, '_pal_job_department', true );
		$salary      = get_post_meta( $post->ID, '_pal_job_salary', true );
		$apply_url   = get_post_meta( $post->ID, '_pal_job_apply_url', true );
		$apply_email = get_post_meta( $post->ID, '_pal_job_apply_email', true );
		$status      = get_post_meta( $post->ID, '_pal_job_status', true );
		$posted_date = get_post_meta( $post->ID, '_pal_job_posted_date', true );
		$deadline    = get_post_meta( $post->ID, '_pal_job_deadline', true );
		$linked_program = get_post_meta( $post->ID, '_pal_job_program', true );
		?>
		<style>
			.pal-meta-row { display:grid; grid-template-columns: 180px 1fr; gap:14px 18px; align-items:start; margin:14px 0; }
			.pal-meta-row label { font-weight:600; padding-top:6px; }
			.pal-meta-row input[type=text], .pal-meta-row input[type=url], .pal-meta-row input[type=email], .pal-meta-row input[type=date], .pal-meta-row select { width:100%; max-width:480px; padding:8px 10px; }
			.pal-meta-help { color:#646970; font-size:12px; margin-top:4px; }
		</style>

		<div class="pal-meta-row">
			<label for="pal_job_type"><?php esc_html_e( 'Position type', 'nashua-pal-core' ); ?></label>
			<div>
				<select id="pal_job_type" name="pal_job_type">
					<option value=""           <?php selected( $type, '' ); ?>><?php esc_html_e( '— Select —', 'nashua-pal-core' ); ?></option>
					<option value="full_time"  <?php selected( $type, 'full_time' ); ?>><?php esc_html_e( 'Full-Time', 'nashua-pal-core' ); ?></option>
					<option value="part_time"  <?php selected( $type, 'part_time' ); ?>><?php esc_html_e( 'Part-Time', 'nashua-pal-core' ); ?></option>
					<option value="seasonal"   <?php selected( $type, 'seasonal' ); ?>><?php esc_html_e( 'Seasonal', 'nashua-pal-core' ); ?></option>
					<option value="volunteer"  <?php selected( $type, 'volunteer' ); ?>><?php esc_html_e( 'Volunteer', 'nashua-pal-core' ); ?></option>
					<option value="internship" <?php selected( $type, 'internship' ); ?>><?php esc_html_e( 'Internship', 'nashua-pal-core' ); ?></option>
				</select>
			</div>
		</div>

		<div class="pal-meta-row">
			<label for="pal_job_location"><?php esc_html_e( 'Location', 'nashua-pal-core' ); ?></label>
			<input type="text" id="pal_job_location" name="pal_job_location" value="<?php echo esc_attr( $location ); ?>" placeholder="<?php esc_attr_e( 'PAL Community Center, Nashua NH', 'nashua-pal-core' ); ?>" />
		</div>

		<div class="pal-meta-row">
			<label for="pal_job_department"><?php esc_html_e( 'Department', 'nashua-pal-core' ); ?></label>
			<input type="text" id="pal_job_department" name="pal_job_department" value="<?php echo esc_attr( $department ); ?>" placeholder="<?php esc_attr_e( 'e.g. Youth Programs, Athletics', 'nashua-pal-core' ); ?>" />
		</div>

		<div class="pal-meta-row">
			<label for="pal_job_salary"><?php esc_html_e( 'Compensation', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="text" id="pal_job_salary" name="pal_job_salary" value="<?php echo esc_attr( $salary ); ?>" placeholder="<?php esc_attr_e( 'e.g. $15–$20/hr, Competitive, Volunteer', 'nashua-pal-core' ); ?>" />
				<span class="pal-meta-help"><?php esc_html_e( 'Leave blank to hide from the listing.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>

		<div class="pal-meta-row">
			<label for="pal_job_apply_url"><?php esc_html_e( 'Apply URL', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="url" id="pal_job_apply_url" name="pal_job_apply_url" value="<?php echo esc_attr( $apply_url ); ?>" placeholder="https://…" />
				<span class="pal-meta-help"><?php esc_html_e( 'External application form or link. Takes priority over email.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>

		<div class="pal-meta-row">
			<label for="pal_job_apply_email"><?php esc_html_e( 'Apply email', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="email" id="pal_job_apply_email" name="pal_job_apply_email" value="<?php echo esc_attr( $apply_email ); ?>" placeholder="<?php esc_attr_e( 'jobs@nashuapal.com', 'nashua-pal-core' ); ?>" />
				<span class="pal-meta-help"><?php esc_html_e( 'Used only if Apply URL is empty.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>

		<div class="pal-meta-row">
			<label for="pal_job_status"><?php esc_html_e( 'Posting status', 'nashua-pal-core' ); ?></label>
			<div>
				<select id="pal_job_status" name="pal_job_status">
					<option value="open"   <?php selected( $status, 'open' ); ?>><?php esc_html_e( 'Open — accepting applications', 'nashua-pal-core' ); ?></option>
					<option value="closed" <?php selected( $status, 'closed' ); ?>><?php esc_html_e( 'Closed — no longer accepting', 'nashua-pal-core' ); ?></option>
					<option value="filled" <?php selected( $status, 'filled' ); ?>><?php esc_html_e( 'Filled — position has been filled', 'nashua-pal-core' ); ?></option>
				</select>
			</div>
		</div>

		<div class="pal-meta-row">
			<label for="pal_job_posted_date"><?php esc_html_e( 'Date posted', 'nashua-pal-core' ); ?></label>
			<input type="date" id="pal_job_posted_date" name="pal_job_posted_date" value="<?php echo esc_attr( $posted_date ); ?>" />
		</div>

		<div class="pal-meta-row">
			<label for="pal_job_deadline"><?php esc_html_e( 'Application deadline', 'nashua-pal-core' ); ?></label>
			<div>
				<input type="date" id="pal_job_deadline" name="pal_job_deadline" value="<?php echo esc_attr( $deadline ); ?>" />
				<span class="pal-meta-help"><?php esc_html_e( 'Leave blank for open-ended positions.', 'nashua-pal-core' ); ?></span>
			</div>
		</div>

		<div class="pal-meta-row">
			<label for="pal_job_program"><?php esc_html_e( 'Linked program', 'nashua-pal-core' ); ?></label>
			<div>
				<select id="pal_job_program" name="pal_job_program">
					<option value=""><?php esc_html_e( '— None', 'nashua-pal-core' ); ?></option>
					<?php
					$programs = get_posts( array( 'post_type' => 'pal_program', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'post_status' => 'publish' ) );
					foreach ( $programs as $prog ) :
						?>
						<option value="<?php echo (int) $prog->ID; ?>" <?php selected( (int) $linked_program, $prog->ID ); ?>><?php echo esc_html( $prog->post_title ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="pal-meta-help"><?php esc_html_e( 'Link this job to a program (e.g. Basketball, Mentoring).', 'nashua-pal-core' ); ?></span>
			</div>
		</div>
		<?php
	}

	public function save( $post_id, $post ) {
		if ( ! isset( $_POST['pal_job_meta_nonce'] ) || ! wp_verify_nonce( $_POST['pal_job_meta_nonce'], 'pal_job_meta' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
		if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }

		// Position type: whitelist.
		$type_raw = isset( $_POST['pal_job_type'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_job_type'] ) ) : '';
		$type     = in_array( $type_raw, array( 'full_time', 'part_time', 'seasonal', 'volunteer', 'internship' ), true ) ? $type_raw : '';

		// Posting status: whitelist.
		$status_raw = isset( $_POST['pal_job_status'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_job_status'] ) ) : '';
		$status     = in_array( $status_raw, array( 'open', 'closed', 'filled' ), true ) ? $status_raw : 'open';

		// Linked program.
		$linked_program = isset( $_POST['pal_job_program'] ) ? absint( $_POST['pal_job_program'] ) : 0;

		// Dates: validate YYYY-MM-DD format from <input type="date">.
		$posted_raw  = isset( $_POST['pal_job_posted_date'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_job_posted_date'] ) ) : '';
		$posted_date = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $posted_raw ) ? $posted_raw : '';
		$deadline_raw = isset( $_POST['pal_job_deadline'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_job_deadline'] ) ) : '';
		$deadline     = preg_match( '/^\d{4}-\d{2}-\d{2}$/', $deadline_raw ) ? $deadline_raw : '';

		$fields = array(
			'_pal_job_type'        => $type,
			'_pal_job_location'    => isset( $_POST['pal_job_location'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_job_location'] ) ) : '',
			'_pal_job_department'  => isset( $_POST['pal_job_department'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_job_department'] ) ) : '',
			'_pal_job_salary'      => isset( $_POST['pal_job_salary'] ) ? sanitize_text_field( wp_unslash( $_POST['pal_job_salary'] ) ) : '',
			'_pal_job_apply_url'   => isset( $_POST['pal_job_apply_url'] ) ? esc_url_raw( wp_unslash( $_POST['pal_job_apply_url'] ) ) : '',
			'_pal_job_apply_email' => isset( $_POST['pal_job_apply_email'] ) ? sanitize_email( wp_unslash( $_POST['pal_job_apply_email'] ) ) : '',
			'_pal_job_status'      => $status,
			'_pal_job_posted_date' => $posted_date,
			'_pal_job_deadline'    => $deadline,
			'_pal_job_program'     => $linked_program ? (string) $linked_program : '',
		);

		foreach ( $fields as $key => $value ) {
			if ( '' === $value ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, $value );
			}
		}
	}
}
