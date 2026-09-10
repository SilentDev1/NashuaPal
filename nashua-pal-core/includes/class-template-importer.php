<?php
/**
 * One-click bulk template importer.
 *
 * Bundles every Nashua PAL Elementor template JSON under
 * `nashua-pal-core/templates/` and provides a "Nashua PAL → Import
 * Templates" admin page with a single "Import All" button that:
 *
 *   1. Creates an `elementor_library` post per JSON.
 *   2. Sets `_elementor_template_type` from the JSON's `type` field
 *      (header / footer / single-post / archive / search-results /
 *      error-404 / page).
 *   3. Sets `_elementor_data` to the JSON's `content` array.
 *   4. Where the file → display-condition mapping is known, sets
 *      `_elementor_conditions` so the template auto-fires on its
 *      intended URL (single Events / single Programs / 404 / etc.).
 *
 * Re-importing the same file updates the existing post by matching on
 * its bundled-filename key in post_meta, so users can hit "Import All"
 * after a plugin update to refresh templates without duplicating.
 *
 * @package NashuaPalCore
 * @since 1.8.58
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

final class NPAL_Template_Importer {

	const PAGE_SLUG       = 'nashua-pal-import-templates';
	const NONCE_KEY       = 'npal_import_templates';
	const META_SOURCE_KEY = '_npal_template_source'; // bundled filename, for re-imports

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) { self::$instance = new self(); }
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu',                                   array( $this, 'menu' ), 30 );
		add_action( 'admin_post_npal_import_all_templates',         array( $this, 'handle_import_all' ) );
		add_action( 'admin_post_npal_import_one_template',          array( $this, 'handle_import_one' ) );
		add_action( 'admin_post_npal_replace_all_templates',        array( $this, 'handle_replace_all' ) );
		add_action( 'admin_post_npal_replace_one_template',         array( $this, 'handle_replace_one' ) );
		add_action( 'admin_post_npal_reset_all_conditions',         array( $this, 'handle_reset_all_conditions' ) );
		add_action( 'admin_post_npal_reset_one_conditions',         array( $this, 'handle_reset_one_conditions' ) );
		add_action( 'admin_post_npal_diagnostic_dump',              array( $this, 'handle_diagnostic_dump' ) );
		add_action( 'admin_post_npal_rebuild_conditions_cache',     array( $this, 'handle_rebuild_conditions_cache' ) );
		add_action( 'admin_post_npal_empty_library_trash',          array( $this, 'handle_empty_library_trash' ) );
	}

	/**
	 * Force a complete rebuild of Elementor Pro's Theme Builder
	 * Conditions_Cache by re-saving every published library post's
	 * conditions through the Conditions_Manager API — the same code path
	 * Elementor's UI hits when a user clicks "Save & Close" in the Display
	 * Conditions dialog. This is necessary because:
	 *
	 *   1. `update_post_meta('_elementor_conditions')` alone does NOT
	 *      register the post in the conditions cache.
	 *   2. `delete_option('elementor_pro_..._cache')` alone does NOT force
	 *      a rebuild — Elementor Pro's `Conditions_Cache::get_cache()`
	 *      lazy-loads from options on next access, and if the cache class
	 *      hasn't been told which post IDs to re-include, the rebuild
	 *      produces an empty cache (no documents match any location).
	 *   3. Verified via diagnostic dump 2026-05-26: published templates
	 *      have correct `_elementor_template_type` + `_elementor_conditions`
	 *      meta, the doctype is registered, the location is registered,
	 *      BUT `Conditions_Manager::get_documents_for_location('single')`
	 *      returns `[]` for a pal_program URL. Smoking-gun proof the cache
	 *      isn't reflecting the meta.
	 *
	 * This handler walks every published `elementor_library` post that
	 * has conditions, calls `Conditions_Cache->add($id, $conditions)` for
	 * each, then `save()` — which writes the rebuilt cache to options
	 * AND triggers `Theme_Builder\Module::clear_cache()` so the new state
	 * is visible on the next page view.
	 *
	 * @since 1.8.79
	 */
	public function handle_rebuild_conditions_cache() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( __( 'Insufficient permissions.', 'nashua-pal-core' ) ); }
		check_admin_referer( self::NONCE_KEY );

		$added = 0;
		$skipped = 0;
		$error   = '';

		if ( ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
			$error = 'Elementor Pro Theme Builder module not loaded.';
		} else {
			try {
				$tb     = \ElementorPro\Modules\ThemeBuilder\Module::instance();
				$mgr    = method_exists( $tb, 'get_conditions_manager' ) ? $tb->get_conditions_manager() : null;
				$cache  = ( $mgr && method_exists( $mgr, 'get_cache' ) ) ? $mgr->get_cache() : null;

				if ( ! $cache ) {
					$error = 'Could not access Conditions_Cache instance.';
				} else {
					// Walk every published library post with conditions and
					// re-register it in the cache. Skip ones without conditions
					// (kit, page-type saved templates) so we don't pollute.
					$q = new WP_Query( array(
						'post_type'      => 'elementor_library',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'no_found_rows'  => true,
					) );

					foreach ( $q->posts as $post_id ) {
						$conds = get_post_meta( $post_id, '_elementor_conditions', true );
						if ( empty( $conds ) || ! is_array( $conds ) ) {
							$skipped++;
							continue;
						}

						// Elementor Pro 4.x: Conditions_Cache::add() takes a
						// Theme_Document instance, NOT a post ID. Earlier 1.8.79
						// passed the ID and crashed with a TypeError. Resolve to
						// the document first, then call add(). The document
						// load must use Plugin::$instance->documents->get() —
						// that's what returns the right subclass (Single,
						// Header, Footer, Single_Post, …) implementing
						// Theme_Document.
						$doc = \Elementor\Plugin::$instance->documents->get( $post_id );
						if ( ! $doc ) { $skipped++; continue; }

						// Theme_Document is the abstract base every Theme
						// Builder doctype extends. Page / Saved Template /
						// Section docs are NOT Theme_Documents and would
						// crash add() with the same TypeError.
						if ( ! ( $doc instanceof \ElementorPro\Modules\ThemeBuilder\Documents\Theme_Document ) ) {
							$skipped++;
							continue;
						}

						if ( method_exists( $cache, 'remove' ) ) {
							$cache->remove( $doc );
						}
						if ( method_exists( $cache, 'add' ) ) {
							$cache->add( $doc, $conds );
							$added++;
						}
					}

					if ( method_exists( $cache, 'save' ) ) {
						$cache->save();
					}

					// Belt-and-suspenders: also nuke the option-level cache + the
					// WP object cache slot for each option key. WP Engine
					// memcached can hold stale values past `delete_option()` if
					// the memcache mu-plugin doesn't pass-through delete invals.
					$this->clear_elementor_conditions_cache();
					$keys = array(
						'elementor_pro_theme_builder_conditions_cache',
						'_elementor_pro_theme_builder_conditions_cache',
						'elementor_pro_conditions_cache',
					);
					foreach ( $keys as $k ) {
						wp_cache_delete( $k, 'options' );
						wp_cache_delete( $k, 'site-options' );
					}
				}
			} catch ( \Throwable $t ) {
				$error = 'Exception: ' . $t->getMessage();
			}
		}

		if ( $error ) {
			set_transient( 'npal_import_errors', array( 'Rebuild cache: ' . $error ), 60 );
		}
		wp_safe_redirect( add_query_arg( array(
			'page'             => self::PAGE_SLUG,
			'cache_rebuilt'    => $added,
			'cache_skipped'    => $skipped,
			'errors'           => $error ? 1 : 0,
		), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Empty the elementor_library trash. Useful after Replace-mode
	 * imports / template rewrites have piled up trashed copies that
	 * still hold `_elementor_conditions` meta and CAN confuse the
	 * Conditions_Cache during rebuild (only documented to filter by
	 * post_status = publish, but observed exceptions).
	 *
	 * @since 1.8.79
	 */
	public function handle_empty_library_trash() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( __( 'Insufficient permissions.', 'nashua-pal-core' ) ); }
		check_admin_referer( self::NONCE_KEY );

		$q = new WP_Query( array(
			'post_type'      => 'elementor_library',
			'post_status'    => 'trash',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		) );
		$deleted = 0;
		foreach ( $q->posts as $post_id ) {
			if ( wp_delete_post( $post_id, true ) ) { $deleted++; }
		}

		wp_safe_redirect( add_query_arg( array(
			'page'           => self::PAGE_SLUG,
			'trash_emptied'  => $deleted,
		), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Stream a JSON diagnostic blob covering every piece of state a Theme
	 * Builder Single template needs to fire. Written to nail down why a
	 * template that's published, has matching conditions, and gets its CSS
	 * enqueued + body class added STILL doesn't replace the rendered
	 * template via Elementor Pro's template_include filter.
	 *
	 * The payload is short on purpose (no _elementor_data dumps — just a
	 * length + hash). Drop the file in the conversation and the assistant
	 * can identify the exact misconfiguration in one round trip.
	 *
	 * @since 1.8.78
	 */
	public function handle_diagnostic_dump() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'Insufficient permissions.', 'nashua-pal-core' ) );
		}
		check_admin_referer( self::NONCE_KEY );

		$out = array(
			'generated_at' => gmdate( 'c' ),
			'site'         => array(
				'home_url'  => home_url( '/' ),
				'site_url'  => site_url( '/' ),
				'wp_ver'    => get_bloginfo( 'version' ),
				'php_ver'   => PHP_VERSION,
				'plugin_ver'=> defined( 'NPAL_VERSION' ) ? NPAL_VERSION : 'unknown',
				'wp_debug'  => defined( 'WP_DEBUG' ) && WP_DEBUG,
				'is_multisite' => is_multisite(),
			),
			'cache'        => array(
				'object_cache_external' => wp_using_ext_object_cache(),
				'object_cache_class'    => function_exists( 'wp_cache_get_stats' ) ? get_class( $GLOBALS['wp_object_cache'] ) : null,
			),
			'theme'        => array(
				'parent_template'   => get_template(),
				'child_stylesheet'  => get_stylesheet(),
				'is_child'          => get_template() !== get_stylesheet(),
			),
			'elementor'    => $this->dump_elementor_state(),
			'cpts'         => $this->dump_cpts(),
			'active_plugins' => $this->dump_active_plugins(),
			'library_posts'  => $this->dump_library_posts(),
			'simulated_route'=> $this->dump_route_simulation(),
		);

		$filename = sprintf( 'npal-diagnostic-%s-%s.json',
			wp_parse_url( home_url(), PHP_URL_HOST ),
			gmdate( 'Ymd-His' )
		);
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		echo wp_json_encode( $out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
	}

	private function dump_elementor_state() {
		$out = array(
			'elementor_active'    => defined( 'ELEMENTOR_VERSION' ),
			'elementor_pro_active'=> defined( 'ELEMENTOR_PRO_VERSION' ),
			'elementor_version'   => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : null,
			'elementor_pro_version'=> defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : null,
			'document_types'      => array(),
			'theme_builder_locations' => array(),
			'conditions_cache_options' => array(),
			'template_include_filters' => $this->dump_filter( 'template_include' ),
			'body_class_filters'       => $this->dump_filter( 'body_class' ),
		);

		// Raw cache option values — the load-bearing diagnostic. After
		// Rebuild Conditions Cache, this option SHOULD contain a map of
		// {post_id: [conditions]} for every published Theme Builder doc.
		// If empty: our save() isn't persisting. If populated but matcher
		// still returns []: the bug is in the matcher, not the cache.
		foreach ( array(
			'elementor_pro_theme_builder_conditions_cache',
			'_elementor_pro_theme_builder_conditions_cache',
			'elementor_pro_conditions_cache',
		) as $opt ) {
			$raw = get_option( $opt, '__npal_missing__' );
			$out['conditions_cache_options'][ $opt ] = ( '__npal_missing__' === $raw )
				? null
				: ( is_array( $raw ) ? $raw : (string) $raw );
		}

		if ( ! class_exists( '\Elementor\Plugin' ) ) { return $out; }
		$plugin = \Elementor\Plugin::$instance;

		// Registered document types (header, footer, single, single-page, single-post, archive, etc.)
		if ( isset( $plugin->documents ) && method_exists( $plugin->documents, 'get_document_types' ) ) {
			foreach ( $plugin->documents->get_document_types() as $type => $class ) {
				$out['document_types'][ $type ] = is_object( $class ) ? get_class( $class ) : (string) $class;
			}
		}

		// Theme Builder location registry (which doctype claims which location)
		if ( class_exists( '\ElementorPro\Modules\ThemeBuilder\Module' ) ) {
			$tb = \ElementorPro\Modules\ThemeBuilder\Module::instance();
			if ( $tb && method_exists( $tb, 'get_locations_manager' ) ) {
				$mgr = $tb->get_locations_manager();
				if ( $mgr && method_exists( $mgr, 'get_locations' ) ) {
					foreach ( $mgr->get_locations() as $name => $loc ) {
						$out['theme_builder_locations'][ $name ] = is_array( $loc ) ? array_keys( $loc ) : (string) gettype( $loc );
					}
				}
			}
		}

		return $out;
	}

	private function dump_filter( $hook ) {
		global $wp_filter;
		$out = array();
		if ( empty( $wp_filter[ $hook ] ) ) { return $out; }
		$wphook = $wp_filter[ $hook ];
		// WP_Hook stores callbacks keyed by priority → cb_id → callback info
		$callbacks = is_object( $wphook ) && isset( $wphook->callbacks ) ? $wphook->callbacks : array();
		if ( ! is_array( $callbacks ) ) { return $out; }
		ksort( $callbacks );
		foreach ( $callbacks as $priority => $cbs ) {
			foreach ( (array) $cbs as $cb_id => $info ) {
				$fn = isset( $info['function'] ) ? $info['function'] : null;
				if ( is_array( $fn ) && count( $fn ) === 2 ) {
					$desc = ( is_object( $fn[0] ) ? get_class( $fn[0] ) : (string) $fn[0] ) . '::' . (string) $fn[1];
				} elseif ( is_string( $fn ) ) {
					$desc = $fn;
				} elseif ( $fn instanceof \Closure ) {
					$desc = 'Closure';
				} else {
					$desc = gettype( $fn );
				}
				$out[] = array( 'priority' => $priority, 'callback' => $desc );
			}
		}
		return $out;
	}

	private function dump_cpts() {
		$out = array();
		foreach ( get_post_types( array(), 'objects' ) as $slug => $obj ) {
			if ( in_array( $slug, array( 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation' ), true ) ) { continue; }
			$out[ $slug ] = array(
				'public'             => (bool) $obj->public,
				'publicly_queryable' => (bool) $obj->publicly_queryable,
				'show_in_rest'       => (bool) $obj->show_in_rest,
				'has_archive'        => $obj->has_archive,
				'hierarchical'       => (bool) $obj->hierarchical,
				'_builtin'           => (bool) $obj->_builtin,
			);
		}
		return $out;
	}

	private function dump_active_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all     = get_plugins();
		$active  = get_option( 'active_plugins', array() );
		$network = is_multisite() ? array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) ) : array();
		$active  = array_unique( array_merge( (array) $active, (array) $network ) );
		$out = array();
		foreach ( $active as $file ) {
			$info = isset( $all[ $file ] ) ? $all[ $file ] : array();
			$out[] = array(
				'file'    => $file,
				'name'    => isset( $info['Name'] ) ? $info['Name'] : $file,
				'version' => isset( $info['Version'] ) ? $info['Version'] : null,
			);
		}
		return $out;
	}

	private function dump_library_posts() {
		$q = new WP_Query( array(
			'post_type'      => 'elementor_library',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'no_found_rows'  => true,
		) );
		$out = array();
		foreach ( $q->posts as $p ) {
			$conds_raw = get_metadata_raw( 'post', $p->ID, '_elementor_conditions', true );
			$conds     = get_post_meta( $p->ID, '_elementor_conditions', true );
			$data_raw  = get_metadata_raw( 'post', $p->ID, '_elementor_data', true );
			$data_len  = is_string( $data_raw ) ? strlen( $data_raw ) : 0;
			$tpl_type  = get_post_meta( $p->ID, '_elementor_template_type', true );
			$doctype_exists = false;
			if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->documents ) {
				$types = \Elementor\Plugin::$instance->documents->get_document_types();
				$doctype_exists = isset( $types[ $tpl_type ] );
			}
			$out[] = array(
				'id'             => (int) $p->ID,
				'title'          => $p->post_title,
				'status'         => $p->post_status,
				'modified_gmt'   => $p->post_modified_gmt,
				'_elementor_template_type'   => $tpl_type,
				'doctype_registered'         => $doctype_exists,
				'_elementor_conditions_raw'  => is_string( $conds_raw ) ? $conds_raw : null,
				'_elementor_conditions'      => $conds,
				'_elementor_data_bytes'      => $data_len,
				'_elementor_data_md5'        => $data_len ? md5( (string) $data_raw ) : null,
				'_wp_page_template'          => get_post_meta( $p->ID, '_wp_page_template', true ),
				'_npal_template_source'      => get_post_meta( $p->ID, self::META_SOURCE_KEY, true ),
			);
		}
		return $out;
	}

	/**
	 * Simulate Elementor Pro's conditions matcher for one fixed CPT URL so
	 * we can see whether ANY published doc claims this URL. If the answer
	 * is "no doc matches" we know it's a condition string problem; if a
	 * doc matches but content still doesn't render, the bug is in
	 * template_include / doctype routing.
	 */
	private function dump_route_simulation() {
		$out = array( 'available' => false, 'matches' => array() );
		if ( ! class_exists( '\ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Manager' ) ) {
			return $out;
		}
		$tb = \ElementorPro\Modules\ThemeBuilder\Module::instance();
		if ( ! $tb || ! method_exists( $tb, 'get_conditions_manager' ) ) { return $out; }
		$mgr = $tb->get_conditions_manager();
		if ( ! $mgr || ! method_exists( $mgr, 'get_documents_for_location' ) ) { return $out; }

		// Find a published pal_program to simulate against.
		$probe = get_posts( array(
			'post_type'      => 'pal_program',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		) );
		if ( empty( $probe ) ) { return $out; }
		$out['available'] = true;
		$out['probe_post_id'] = (int) $probe[0];
		$out['probe_post_url'] = get_permalink( (int) $probe[0] );

		// Spoof the global query so Conditions_Manager sees this URL.
		global $wp_query;
		$saved = $wp_query;
		$wp_query = new WP_Query( array(
			'p'         => (int) $probe[0],
			'post_type' => 'pal_program',
		) );

		foreach ( array( 'single', 'archive' ) as $location ) {
			$docs = $mgr->get_documents_for_location( $location );
			$out['matches'][ $location ] = is_array( $docs ) ? array_keys( $docs ) : array();
		}

		$wp_query = $saved;
		return $out;
	}

	/**
	 * Clear Elementor Pro's Theme Builder conditions cache so newly-applied
	 * `_elementor_conditions` post meta takes effect on the next page view
	 * (without it the cache holds a stale URL → template_id map).
	 *
	 * Tries the canonical Pro API first; falls back to deleting the
	 * underlying option keys for older versions.
	 */
	private function clear_elementor_conditions_cache() {
		// Modern Elementor Pro (3.x): Theme_Builder module has a cache class.
		if ( class_exists( '\\ElementorPro\\Modules\\ThemeBuilder\\Classes\\Conditions_Cache' ) ) {
			try {
				$cache = new \ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Cache();
				if ( method_exists( $cache, 'regenerate' ) ) { $cache->regenerate(); }
			} catch ( \Exception $e ) {
				// fall through to manual delete
			}
		}

		// Backup: nuke the known option keys regardless. Elementor Pro will
		// rebuild on the next request.
		delete_option( 'elementor_pro_theme_builder_conditions_cache' );
		delete_option( '_elementor_pro_theme_builder_conditions_cache' );
		delete_option( 'elementor_pro_conditions_cache' );
		wp_cache_flush();
	}

	public function menu() {
		add_submenu_page(
			'nashua-pal-settings',
			__( 'Import Templates', 'nashua-pal-core' ),
			__( 'Import Templates', 'nashua-pal-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render' )
		);
	}

	private function templates_dir() {
		return NPAL_PLUGIN_DIR . 'templates/';
	}

	/**
	 * Filename → Theme Builder display-condition map.
	 *
	 * Conditions are stored in `_elementor_conditions` meta as a serialized
	 * array of strings parsed by Elementor Pro's `Conditions_Manager::parse_condition()`
	 * as `{type}/{name}/{sub_name}/{sub_id}` (slash-delimited). The second
	 * segment is the `Condition_Base::get_name()` of the registered condition
	 * instance — NOT a category prefix:
	 *   - `Post::get_name()` returns the post-type slug → `include/pal_program`
	 *     for all single `pal_program` posts (NOT `include/post/pal_program`).
	 *   - `Post_Type_Archive::get_name()` returns `{post_type}_archive` →
	 *     `include/pal_program_archive` for the program archive.
	 *   - `General::get_name()` returns `general` → `include/general` is
	 *     "Entire site", `include/general/error_404` is the 404 page.
	 *
	 * 1.8.72 BUG FIX: prior versions used `include/post/{type}` and
	 * `include/post_archive/{type}` which Elementor Pro silently ignored, so
	 * single events/programs/partners fell through to the archive template
	 * when no valid single condition matched the URL.
	 */
	private function conditions_map() {
		return array(
			// Theme Builder Singles — name = post type slug.
			'nashua-pal-single-event-core-compatible-template.json' => array( 'include/pal_event' ),
			'nashua-pal-single-program-themebuilder.json'           => array( 'include/pal_program' ),
			'nashua-pal-single-partner-themebuilder.json'           => array( 'include/pal_partner' ),
			'nashua-pal-single-job-themebuilder.json'               => array( 'include/pal_job' ),
			'nashua-pal-single-post-themebuilder.json'              => array( 'include/post' ),

			// Theme Builder Archives — name = `{post_type}_archive`.
			'nashua-pal-events-archive-themebuilder.json'           => array( 'include/pal_event_archive' ),
			'nashua-pal-programs-archive-themebuilder.json'         => array( 'include/pal_program_archive' ),
			'nashua-pal-partners-archive-themebuilder.json'         => array( 'include/pal_partner_archive' ),
			'nashua-pal-jobs-archive-themebuilder.json'             => array( 'include/pal_job_archive' ),
			'nashua-pal-posts-archive-themebuilder.json'            => array( 'include/post_archive' ),

			// Header / Footer.
			// The **unified** header (1.8.61) bundles the desktop chrome (toolbar,
			// main row with nav menu + donate) AND the mobile chrome (mobile bar,
			// slide-in drawer) into one Theme Builder template, with Elementor's
			// per-section Responsive visibility flags (`hide_desktop` / `hide_tablet`
			// / `hide_mobile`) controlling which sections render at each breakpoint.
			// One "Entire site" condition, no compete with separate mobile-only
			// templates. (The legacy `nashua-pal-header-v2.json` desktop-only +
			// `nashua-pal-header-mobile-v1.json` mobile-only templates were removed
			// from the bundle in 1.8.71 — they're superseded by the unified header.)
			'nashua-pal-header-unified.json'                        => array( 'include/general' ),
			'nashua-pal-footer-v2.json'                             => array( 'include/general' ),

			// Special.
			'nashua-pal-404-themebuilder.json'                      => array( 'include/general/error_404' ),
			'nashua-pal-search-results-themebuilder.json'           => array( 'include/general/search_results' ),
		);
	}

	/**
	 * Scan templates/ and return a manifest of every bundled JSON, with
	 * each entry's parsed type/title + current import status (existing
	 * post ID if previously imported, null otherwise).
	 */
	private function template_list() {
		$dir   = $this->templates_dir();
		$out   = array();
		$conds = $this->conditions_map();

		if ( ! is_dir( $dir ) ) { return $out; }

		$files = glob( $dir . '*.json' );
		if ( ! is_array( $files ) ) { return $out; }

		sort( $files );

		foreach ( $files as $path ) {
			$name = basename( $path );
			$raw  = file_get_contents( $path );
			if ( false === $raw ) { continue; }
			$json = json_decode( $raw, true );
			if ( ! is_array( $json ) || empty( $json['type'] ) ) { continue; }

			$existing = $this->find_existing(
				$name,
				isset( $json['title'] ) ? $json['title'] : '',
				isset( $json['type'] )  ? $json['type']  : ''
			);

			$out[ $name ] = array(
				'file'      => $name,
				'title'     => isset( $json['title'] ) ? $json['title'] : $name,
				'type'      => $json['type'],
				'condition' => isset( $conds[ $name ] ) ? $conds[ $name ] : array(),
				'post_id'   => $existing,
				'path'      => $path,
			);
		}

		return $out;
	}

	private function find_existing_by_source( $filename ) {
		$q = new WP_Query( array(
			'post_type'      => 'elementor_library',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'     => self::META_SOURCE_KEY,
					'value'   => $filename,
					'compare' => '=',
				),
			),
		) );
		return ! empty( $q->posts ) ? (int) $q->posts[0] : null;
	}

	/**
	 * Title + type fallback for templates the user may have imported manually
	 * before this plugin existed. Without this, "Import All" would create
	 * a DUPLICATE of every pre-existing template that lacks our source-tag
	 * meta — and duplicate headers/footers in Theme Builder cause real
	 * problems (multiple "Entire site" conditions compete).
	 *
	 * The `$type` parameter is required: two bundled JSONs can share a title
	 * (e.g. an `archive`-type Theme Builder template vs a `page`-type Saved
	 * Template both named "Nashua PAL Events Archive V2"). Without scoping
	 * to template_type, the second import would overwrite the first one's
	 * `_elementor_template_type`, breaking Theme Builder routing.
	 */
	private function find_existing_by_title_and_type( $title, $type ) {
		$q = new WP_Query( array(
			'post_type'      => 'elementor_library',
			'post_status'    => 'any',
			'title'          => $title,
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
			'meta_query'     => array(
				array(
					'key'     => '_elementor_template_type',
					'value'   => $type,
					'compare' => '=',
				),
			),
		) );
		return ! empty( $q->posts ) ? (int) $q->posts[0] : null;
	}

	/** Combined finder — source meta first, (title + template_type) second. */
	private function find_existing( $filename, $title = '', $type = '' ) {
		$by_source = $this->find_existing_by_source( $filename );
		if ( $by_source ) { return $by_source; }
		return ( $title && $type ) ? $this->find_existing_by_title_and_type( $title, $type ) : null;
	}

	/**
	 * Import one bundled JSON. Returns array with 'post_id' on success or
	 * 'error' on failure (never throws).
	 *
	 * @param array $entry           Manifest entry from template_list().
	 * @param bool  $force_replace   If true, wp_delete the matched existing
	 *                               post (if any) BEFORE creating a fresh one
	 *                               — destroys manual edits + conditions and
	 *                               re-applies the bundled defaults from
	 *                               scratch. If false (default), updates the
	 *                               existing post and preserves its current
	 *                               Display Conditions (the "update" path).
	 */
	private function import_one( $entry, $force_replace = false ) {
		if ( ! is_array( $entry ) || empty( $entry['path'] ) ) {
			return array( 'error' => __( 'Missing entry path.', 'nashua-pal-core' ) );
		}

		$raw = file_get_contents( $entry['path'] );
		if ( false === $raw ) {
			return array( 'error' => __( 'Could not read JSON file.', 'nashua-pal-core' ) );
		}
		$json = json_decode( $raw, true );
		if ( ! is_array( $json ) ) {
			return array( 'error' => __( 'Invalid JSON.', 'nashua-pal-core' ) );
		}

		$type    = isset( $json['type'] )          ? $json['type']          : 'page';
		$title   = isset( $json['title'] )         ? $json['title']         : $entry['file'];
		$content = isset( $json['content'] )       ? $json['content']       : array();
		$pset    = isset( $json['page_settings'] ) ? $json['page_settings'] : array();
		$ver     = isset( $json['version'] )       ? $json['version']       : '0.4';

		// Reuse existing post if we've imported this file before — OR if the
		// user already has a template with this exact title + template_type
		// from a previous manual import. Type-scoped so an archive-type
		// import doesn't get clobbered by a page-type import that happens
		// to share a title (real case: two bundled events-archive JSONs).
		$existing_id = $this->find_existing( $entry['file'], $title, $type );

		// Replace mode: nuke the matched existing post entirely before
		// re-creating, so the bundled version becomes the source of truth
		// — clears manual Elementor edits, conditions, CSS cache.
		if ( $force_replace && $existing_id ) {
			wp_delete_post( $existing_id, true );
			$existing_id = null;
		}

		$postarr = array(
			'post_type'   => 'elementor_library',
			'post_title'  => wp_strip_all_tags( $title ),
			'post_status' => 'publish',
		);
		if ( $existing_id ) {
			$postarr['ID'] = $existing_id;
			// WP 6.9+ validates `_wp_page_template` on every wp_update_post()
			// call, including re-imports, even when page_template is NOT in
			// $postarr. For `elementor_library` posts the meta value
			// `elementor_header_footer` is not a registered page template
			// (Hello Elementor only registers it for the `page` post type),
			// so the validation throws "Invalid page template" and aborts
			// the update. Workaround: clear the meta before the update,
			// re-set it via update_post_meta() further down (which doesn't
			// trigger the validation). Captured here so we can restore on
			// failure too.
			$saved_page_template = get_post_meta( $existing_id, '_wp_page_template', true );
			if ( $saved_page_template ) {
				delete_post_meta( $existing_id, '_wp_page_template' );
			}
			$post_id = wp_update_post( $postarr, true );
			if ( is_wp_error( $post_id ) && $saved_page_template ) {
				// Restore on failure so we don't leave the post in a half-modified state.
				update_post_meta( $existing_id, '_wp_page_template', $saved_page_template );
			}
		} else {
			$post_id = wp_insert_post( $postarr, true );
		}
		if ( is_wp_error( $post_id ) ) {
			return array( 'error' => $post_id->get_error_message() );
		}

		// Substitute `{{NPAL_PLUGIN_URL}}` tokens with the real plugin URL so
		// bundled image references (assets/img/*.jpg) resolve on any install.
		// Done on the JSON string (not the array) so it covers nested values
		// regardless of structure — image URLs inside `photo.url`, `image.url`,
		// background-image styles, etc.
		$content_json = wp_json_encode( $content );
		$content_json = str_replace( '{{NPAL_PLUGIN_URL}}', untrailingslashit( NPAL_PLUGIN_URL ) . '/', $content_json );

		// Wire up Elementor's expected meta. Use slashed JSON for _elementor_data
		// because WP unslashes meta on save; otherwise nested quotes/backslashes
		// in the template content get mangled and Elementor reports "Sorry, the
		// content area was not found in your page."
		update_post_meta( $post_id, '_elementor_template_type', $type );
		update_post_meta( $post_id, '_elementor_edit_mode',     'builder' );
		update_post_meta( $post_id, '_elementor_version',       $ver );
		update_post_meta( $post_id, '_elementor_data',          wp_slash( $content_json ) );
		update_post_meta( $post_id, '_elementor_page_settings', is_array( $pset ) ? $pset : array() );
		update_post_meta( $post_id, self::META_SOURCE_KEY,      $entry['file'] );

		// Force Elementor Full Width layout for Theme Builder templates so the
		// design renders edge-to-edge instead of getting wrapped in Hello
		// Elementor's constrained content container (~1140px). Without this,
		// the template imports correctly but renders narrower than intended.
		// `elementor_header_footer` = full-width content with theme's header
		// + footer (or Theme Builder's). For archives + singles + 404 + search
		// this is always what we want.
		// Force Elementor Full Width layout for every imported template type.
		// Without this the host theme (Hello Elementor / etc.) wraps content
		// in its ~1140px container and edge-to-edge designs render narrower
		// than intended. Theme Builder docs (`single`, `archive`, `header`,
		// `footer`, `error-404`, `search-results`) all benefit. `page`-type
		// library/saved templates: when a user creates a Page and inserts the
		// template, this meta is on the LIBRARY post — the Page itself is
		// separate. We still set it on the library post so the in-editor
		// preview renders full-width, and so users who promote a library
		// template directly to a Page (rare but possible) get the right
		// layout out of the box.
		$full_width_types = array( 'archive', 'single', 'single-post', 'single-page', 'search-results', 'error-404', 'page', 'header', 'footer' );
		if ( in_array( $type, $full_width_types, true ) ) {
			update_post_meta( $post_id, '_wp_page_template', 'elementor_header_footer' );
		}

		// Apply Theme Builder display conditions where we have a mapping
		// AND this is a Theme Builder template type. 1.8.72: always re-apply
		// the bundled conditions (previously skipped on re-import to preserve
		// "manual overrides" — but in practice that just let broken/missing
		// conditions persist, which is exactly what caused the single-event /
		// single-program → archive fallback bug). If a user really wants to
		// keep manual Display Conditions they edited in Theme Builder, they
		// can remove the file from the bundle or fork the conditions_map.
		$tb_types = array( 'header', 'footer', 'single', 'single-post', 'single-page', 'archive', 'search-results', 'error-404', 'product', 'product-archive' );
		if ( in_array( $type, $tb_types, true ) && ! empty( $entry['condition'] ) ) {
			update_post_meta( $post_id, '_elementor_conditions', $entry['condition'] );
		}

		// Clear Elementor's CSS cache for this template so the next page
		// view regenerates assets from the new content.
		delete_post_meta( $post_id, '_elementor_css' );

		return array( 'post_id' => $post_id, 'updated' => (bool) $existing_id );
	}

	/**
	 * Re-apply Display Conditions AND the Theme Builder document type for
	 * one bundled entry's matched existing post — does NOT touch content
	 * or page settings. Use to repair templates whose conditions or type
	 * drifted without nuking edits.
	 *
	 * Updating the type matters because Elementor Pro filters conditions
	 * by the document's location/condition_type. A template stored as
	 * `single-page` (location=page) silently ignores `include/pal_event`
	 * conditions even when the meta string is present. Switching the type
	 * to `single` (CPT-compatible) is required for the condition to fire.
	 */
	private function reset_one_conditions( $entry ) {
		if ( empty( $entry['condition'] ) ) {
			return array( 'error' => __( 'No bundled condition mapping for this template.', 'nashua-pal-core' ) );
		}
		if ( empty( $entry['post_id'] ) ) {
			return array( 'error' => __( 'Template has not been imported yet — nothing to reset.', 'nashua-pal-core' ) );
		}
		update_post_meta( $entry['post_id'], '_elementor_conditions',   $entry['condition'] );
		if ( ! empty( $entry['type'] ) ) {
			update_post_meta( $entry['post_id'], '_elementor_template_type', $entry['type'] );
		}
		return array( 'post_id' => $entry['post_id'], 'reset' => true );
	}

	public function handle_import_all() { $this->run_all( false ); }
	public function handle_replace_all() { $this->run_all( true ); }
	public function handle_import_one() { $this->run_one( false ); }
	public function handle_replace_one() { $this->run_one( true ); }

	public function handle_reset_all_conditions() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Insufficient permissions.', 'nashua-pal-core' ) ); }
		check_admin_referer( self::NONCE_KEY );

		$list = $this->template_list();
		$ok = 0; $skipped = 0; $errors = array();
		foreach ( $list as $entry ) {
			$res = $this->reset_one_conditions( $entry );
			if ( isset( $res['error'] ) ) { $skipped++; continue; }
			$ok++;
		}
		$this->clear_elementor_conditions_cache();

		$args = array(
			'page'             => self::PAGE_SLUG,
			'conditions_reset' => $ok,
		);
		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}

	public function handle_reset_one_conditions() {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Insufficient permissions.', 'nashua-pal-core' ) ); }
		check_admin_referer( self::NONCE_KEY );

		$file = isset( $_GET['file'] ) ? sanitize_file_name( wp_unslash( $_GET['file'] ) ) : '';
		$list = $this->template_list();
		if ( ! $file || ! isset( $list[ $file ] ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => self::PAGE_SLUG, 'errors' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		$res = $this->reset_one_conditions( $list[ $file ] );
		$this->clear_elementor_conditions_cache();

		$args = array(
			'page'             => self::PAGE_SLUG,
			'conditions_reset' => isset( $res['post_id'] ) ? 1 : 0,
			'errors'           => isset( $res['error'] ) ? 1 : 0,
		);
		if ( isset( $res['error'] ) ) {
			set_transient( 'npal_import_errors', array( $file . ': ' . $res['error'] ), 60 );
		}
		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}

	private function run_all( $force_replace ) {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Insufficient permissions.', 'nashua-pal-core' ) ); }
		check_admin_referer( self::NONCE_KEY );

		$list = $this->template_list();
		$ok = 0; $updated = 0; $err = 0; $errors = array();
		foreach ( $list as $entry ) {
			$res = $this->import_one( $entry, $force_replace );
			if ( isset( $res['error'] ) ) {
				$err++;
				$errors[] = $entry['file'] . ': ' . $res['error'];
			} else {
				$ok++;
				if ( ! empty( $res['updated'] ) ) { $updated++; }
			}
		}

		// Cache flush so re-applied conditions take effect immediately.
		$this->clear_elementor_conditions_cache();

		$args = array(
			'page'       => self::PAGE_SLUG,
			'imported'   => $ok,
			'updated'    => $updated,
			'errors'     => $err,
			'replaced'   => $force_replace ? 1 : 0,
		);
		if ( $errors ) {
			set_transient( 'npal_import_errors', $errors, 60 );
		}
		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}

	private function run_one( $force_replace ) {
		if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html__( 'Insufficient permissions.', 'nashua-pal-core' ) ); }
		check_admin_referer( self::NONCE_KEY );

		$file = isset( $_GET['file'] ) ? sanitize_file_name( wp_unslash( $_GET['file'] ) ) : '';
		$list = $this->template_list();
		if ( ! $file || ! isset( $list[ $file ] ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => self::PAGE_SLUG, 'errors' => 1 ), admin_url( 'admin.php' ) ) );
			exit;
		}

		$res = $this->import_one( $list[ $file ], $force_replace );
		$this->clear_elementor_conditions_cache();
		$args = array(
			'page'     => self::PAGE_SLUG,
			'imported' => isset( $res['post_id'] ) ? 1 : 0,
			'updated'  => ! empty( $res['updated'] ) ? 1 : 0,
			'errors'   => isset( $res['error'] ) ? 1 : 0,
			'replaced' => $force_replace ? 1 : 0,
		);
		if ( isset( $res['error'] ) ) {
			set_transient( 'npal_import_errors', array( $file . ': ' . $res['error'] ), 60 );
		}
		wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
		exit;
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) { return; }

		$list      = $this->template_list();
		$imported  = isset( $_GET['imported'] )         ? max( 0, intval( $_GET['imported'] ) )         : 0;
		$updated   = isset( $_GET['updated'] )          ? max( 0, intval( $_GET['updated'] ) )          : 0;
		$err_count = isset( $_GET['errors'] )           ? max( 0, intval( $_GET['errors'] ) )           : 0;
		$replaced  = isset( $_GET['replaced'] )         ? max( 0, intval( $_GET['replaced'] ) )         : 0;
		$reset     = isset( $_GET['conditions_reset'] ) ? max( 0, intval( $_GET['conditions_reset'] ) ) : 0;
		$rebuilt   = isset( $_GET['cache_rebuilt'] )   ? max( 0, intval( $_GET['cache_rebuilt'] ) )   : 0;
		$rebuilt_skipped = isset( $_GET['cache_skipped'] ) ? max( 0, intval( $_GET['cache_skipped'] ) ) : 0;
		$trash_emptied  = isset( $_GET['trash_emptied'] )  ? max( 0, intval( $_GET['trash_emptied'] ) )  : 0;
		$errors    = $err_count ? get_transient( 'npal_import_errors' ) : false;
		if ( $errors ) { delete_transient( 'npal_import_errors' ); }

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Nashua PAL — Import Templates', 'nashua-pal-core' ); ?></h1>
			<p class="description" style="max-width:780px;">
				<?php esc_html_e( 'One-click import of every bundled Elementor template — headers, footers, Theme Builder Singles/Archives, Search/404 templates, and all inner-page starters. Display conditions are auto-applied where possible; re-running is safe (existing templates are updated, not duplicated).', 'nashua-pal-core' ); ?>
			</p>

			<?php if ( $reset ) : ?>
				<div class="notice notice-success" style="margin-top:16px;">
					<p><strong><?php echo (int) $reset; ?></strong> <?php esc_html_e( 'template Display Conditions reset to bundled defaults. Elementor Pro conditions cache flushed.', 'nashua-pal-core' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $rebuilt || $rebuilt_skipped ) : ?>
				<div class="notice notice-success" style="margin-top:16px;">
					<p>
						<?php
						printf(
							/* translators: 1: added, 2: skipped */
							esc_html__( 'Elementor Pro Conditions_Cache rebuilt. %1$d templates re-registered, %2$d skipped (no conditions).', 'nashua-pal-core' ),
							(int) $rebuilt,
							(int) $rebuilt_skipped
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( $trash_emptied ) : ?>
				<div class="notice notice-success" style="margin-top:16px;">
					<p><strong><?php echo (int) $trash_emptied; ?></strong> <?php esc_html_e( 'trashed elementor_library posts permanently deleted.', 'nashua-pal-core' ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( $imported || $err_count ) : ?>
				<div class="notice <?php echo $err_count ? 'notice-warning' : 'notice-success'; ?>" style="margin-top:16px;">
					<p>
						<?php if ( $replaced ) : ?><strong style="color:#b32d2e;"><?php esc_html_e( 'REPLACE MODE:', 'nashua-pal-core' ); ?></strong> <?php endif; ?>
						<strong><?php echo (int) $imported; ?></strong> <?php esc_html_e( 'imported', 'nashua-pal-core' ); ?>
						(<strong><?php echo (int) $updated; ?></strong> <?php esc_html_e( $replaced ? 'replaced' : 'updated', 'nashua-pal-core' ); ?>),
						<strong><?php echo (int) $err_count; ?></strong> <?php esc_html_e( 'errors', 'nashua-pal-core' ); ?>.
					</p>
					<?php if ( is_array( $errors ) && $errors ) : ?>
						<ul style="margin-left:18px;">
							<?php foreach ( $errors as $e ) : ?>
								<li><code><?php echo esc_html( $e ); ?></code></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div style="margin:18px 0;display:flex;gap:14px;align-items:flex-start;flex-wrap:wrap;">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
					<?php wp_nonce_field( self::NONCE_KEY ); ?>
					<input type="hidden" name="action" value="npal_import_all_templates" />
					<button type="submit" class="button button-primary button-hero">
						<?php esc_html_e( '⬇ Import All Templates', 'nashua-pal-core' ); ?>
					</button>
					<p class="description" style="margin:6px 0 0;max-width:260px;font-size:12px;line-height:1.4;">
						<?php esc_html_e( 'Updates existing templates if they exist, preserves your Display Conditions and any Elementor edits.', 'nashua-pal-core' ); ?>
					</p>
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
					<?php wp_nonce_field( self::NONCE_KEY ); ?>
					<input type="hidden" name="action" value="npal_reset_all_conditions" />
					<button type="submit" class="button button-hero" style="background:#F4A51C;color:#071F3F;border-color:#c98a14;font-weight:700;">
						<?php esc_html_e( '🔄 Reset All Conditions', 'nashua-pal-core' ); ?>
					</button>
					<p class="description" style="margin:6px 0 0;max-width:280px;font-size:12px;line-height:1.4;color:#3A4658;">
						<?php esc_html_e( 'Safe — re-applies bundled Display Conditions to every imported template (single posts, archives, 404, search). Content + your Elementor edits are NOT touched. Use this if single program/event pages render the wrong template.', 'nashua-pal-core' ); ?>
					</p>
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;" onsubmit="return confirm('REPLACE ALL TEMPLATES?\n\nThis will DELETE each existing template and re-create it from the bundled file.\n\nAll manual Elementor edits + Display Condition tweaks will be lost.\n\nContinue?');">
					<?php wp_nonce_field( self::NONCE_KEY ); ?>
					<input type="hidden" name="action" value="npal_replace_all_templates" />
					<button type="submit" class="button button-hero" style="background:#b32d2e;color:#fff;border-color:#8a2120;">
						<?php esc_html_e( '⚠ Replace All (Reset)', 'nashua-pal-core' ); ?>
					</button>
					<p class="description" style="margin:6px 0 0;max-width:260px;font-size:12px;line-height:1.4;color:#b32d2e;">
						<?php esc_html_e( 'Destructive — deletes each existing post + re-creates from bundled. Use to fully reset after experiments.', 'nashua-pal-core' ); ?>
					</p>
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
					<?php wp_nonce_field( self::NONCE_KEY ); ?>
					<input type="hidden" name="action" value="npal_rebuild_conditions_cache" />
					<button type="submit" class="button button-hero" style="background:#7d2bdb;color:#fff;border-color:#5a1ea8;font-weight:700;">
						<?php esc_html_e( '🔥 Rebuild Conditions Cache', 'nashua-pal-core' ); ?>
					</button>
					<p class="description" style="margin:6px 0 0;max-width:300px;font-size:12px;line-height:1.4;color:#3A4658;">
						<?php esc_html_e( 'THE main fix for "Theme Builder Single template won\'t fire on a CPT URL." Walks every published library post with conditions and re-registers it in Elementor Pro\'s Conditions_Cache via the same API the Save & Close button uses. Use this if Single Program / Single Event templates render the child-theme fallback instead of the Elementor design even after Reset All Conditions + cache purges.', 'nashua-pal-core' ); ?>
					</p>
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;" onsubmit="return confirm('PERMANENTLY DELETE all trashed Elementor templates?\n\nThis cannot be undone — trashed templates and their meta are wiped.\n\nContinue?');">
					<?php wp_nonce_field( self::NONCE_KEY ); ?>
					<input type="hidden" name="action" value="npal_empty_library_trash" />
					<button type="submit" class="button" style="background:#646970;color:#fff;border-color:#3c434a;">
						<?php esc_html_e( '🗑 Empty Library Trash', 'nashua-pal-core' ); ?>
					</button>
					<p class="description" style="margin:6px 0 0;max-width:240px;font-size:12px;line-height:1.4;color:#3A4658;">
						<?php esc_html_e( 'Permanently deletes all trashed elementor_library posts (clears 28 stale ones still holding dead conditions meta).', 'nashua-pal-core' ); ?>
					</p>
				</form>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0;">
					<?php wp_nonce_field( self::NONCE_KEY ); ?>
					<input type="hidden" name="action" value="npal_diagnostic_dump" />
					<button type="submit" class="button button-hero" style="background:#2271b1;color:#fff;border-color:#135e96;">
						<?php esc_html_e( '📊 Download Diagnostic JSON', 'nashua-pal-core' ); ?>
					</button>
					<p class="description" style="margin:6px 0 0;max-width:280px;font-size:12px;line-height:1.4;color:#3A4658;">
						<?php esc_html_e( 'Dumps Elementor versions, every template\'s conditions/type/doctype-registration status, registered Theme Builder locations + a route-match simulation for a sample CPT URL. Safe — read-only.', 'nashua-pal-core' ); ?>
					</p>
				</form>

				<div style="margin-left:auto;align-self:center;color:#646970;font-size:13px;">
					<?php
					printf(
						/* translators: %d: number of bundled templates */
						esc_html__( '%d bundled templates available.', 'nashua-pal-core' ),
						count( $list )
					);
					?>
				</div>
			</div>

			<table class="widefat striped" style="margin-top:18px;">
				<thead>
					<tr>
						<th style="width:38%;"><?php esc_html_e( 'Template', 'nashua-pal-core' ); ?></th>
						<th style="width:14%;"><?php esc_html_e( 'Type', 'nashua-pal-core' ); ?></th>
						<th style="width:24%;"><?php esc_html_e( 'Display condition', 'nashua-pal-core' ); ?></th>
						<th style="width:10%;"><?php esc_html_e( 'Status', 'nashua-pal-core' ); ?></th>
						<th style="width:14%;"><?php esc_html_e( 'Action', 'nashua-pal-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $list as $entry ) :
						$import_url = wp_nonce_url(
							add_query_arg(
								array(
									'action' => 'npal_import_one_template',
									'file'   => $entry['file'],
								),
								admin_url( 'admin-post.php' )
							),
							self::NONCE_KEY
						);
						$replace_url = wp_nonce_url(
							add_query_arg(
								array(
									'action' => 'npal_replace_one_template',
									'file'   => $entry['file'],
								),
								admin_url( 'admin-post.php' )
							),
							self::NONCE_KEY
						);
						$reset_cond_url = wp_nonce_url(
							add_query_arg(
								array(
									'action' => 'npal_reset_one_conditions',
									'file'   => $entry['file'],
								),
								admin_url( 'admin-post.php' )
							),
							self::NONCE_KEY
						);
						$edit_url = $entry['post_id']
							? add_query_arg( array( 'post' => $entry['post_id'], 'action' => 'elementor' ), admin_url( 'post.php' ) )
							: '';
						$confirm = "javascript:return confirm('Replace this template?\\n\\nThe existing post (#" . (int) $entry['post_id'] . ") will be DELETED and a fresh one created from the bundled file.\\n\\nManual edits + Display Conditions will be lost.\\n\\nContinue?');";
						?>
						<tr>
							<td>
								<strong><?php echo esc_html( $entry['title'] ); ?></strong>
								<br />
								<code style="font-size:11px;color:#646970;"><?php echo esc_html( $entry['file'] ); ?></code>
							</td>
							<td><code><?php echo esc_html( $entry['type'] ); ?></code></td>
							<td><?php echo $entry['condition'] ? '<code>' . esc_html( implode( ', ', $entry['condition'] ) ) . '</code>' : '<span style="color:#a7aaad;">—</span>'; ?></td>
							<td>
								<?php if ( $entry['post_id'] ) : ?>
									<span style="color:#00a32a;">✓ <?php esc_html_e( 'Imported', 'nashua-pal-core' ); ?></span>
									<br />
									<small>#<?php echo (int) $entry['post_id']; ?></small>
								<?php else : ?>
									<span style="color:#a7aaad;">— <?php esc_html_e( 'Not imported', 'nashua-pal-core' ); ?></span>
								<?php endif; ?>
							</td>
							<td>
								<a class="button button-small" href="<?php echo esc_url( $import_url ); ?>">
									<?php echo $entry['post_id'] ? esc_html__( 'Re-import', 'nashua-pal-core' ) : esc_html__( 'Import', 'nashua-pal-core' ); ?>
								</a>
								<?php if ( $entry['post_id'] ) : ?>
									<?php if ( ! empty( $entry['condition'] ) ) : ?>
										<a class="button button-small" href="<?php echo esc_url( $reset_cond_url ); ?>" title="<?php esc_attr_e( 'Re-apply the bundled Display Condition without touching content.', 'nashua-pal-core' ); ?>">
											<?php esc_html_e( 'Reset cond.', 'nashua-pal-core' ); ?>
										</a>
									<?php endif; ?>
									<a class="button button-small" href="<?php echo esc_url( $replace_url ); ?>" onclick="<?php echo esc_attr( $confirm ); ?>" style="color:#b32d2e;border-color:#b32d2e;">
										<?php esc_html_e( 'Replace', 'nashua-pal-core' ); ?>
									</a>
								<?php endif; ?>
								<?php if ( $edit_url ) : ?>
									<br />
									<a href="<?php echo esc_url( $edit_url ); ?>" style="font-size:11px;"><?php esc_html_e( 'Open in Elementor →', 'nashua-pal-core' ); ?></a>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<p class="description" style="margin-top:18px;max-width:780px;">
				<strong><?php esc_html_e( 'Notes:', 'nashua-pal-core' ); ?></strong><br />
				<?php esc_html_e( '• Theme Builder templates auto-apply their Display Conditions on first import only — manual edits to conditions in Theme Builder are preserved on re-import.', 'nashua-pal-core' ); ?><br />
				<?php esc_html_e( '• If a 404 or Search Results template doesn\'t auto-fire on its URL, open the template in Theme Builder and verify the Display Condition (Elementor Pro condition string format varies by version).', 'nashua-pal-core' ); ?><br />
				<?php esc_html_e( '• The Mobile Header template is imported WITHOUT a Display Condition on purpose — it would compete with the Desktop Header (both "Entire site"). Either trash the mobile header and use Elementor\'s per-section Responsive visibility on the desktop header, or wire it up manually if you have a breakpoint-conditions plugin.', 'nashua-pal-core' ); ?><br />
				<?php esc_html_e( '• After importing, visit Settings → Permalinks → Save Changes to re-flush rewrite rules so /events/ and /programs/ resolve cleanly.', 'nashua-pal-core' ); ?>
			</p>
		</div>
		<?php
	}
}
