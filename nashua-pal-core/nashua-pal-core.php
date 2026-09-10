<?php
/**
 * Plugin Name:       Nashua PAL Core
 * Plugin URI:        https://cao-tech.com/
 * Description:       Custom post types, settings, and shortcodes that power the Nashua PAL website. Lets staff edit events, programs, partners, alerts, and site-wide settings without touching Elementor.
 * Version:           1.55.39
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Cao-Tech
 * Author URI:        https://cao-tech.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nashua-pal-core
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NPAL_VERSION', '1.55.39' );
define( 'NPAL_PLUGIN_FILE', __FILE__ );
define( 'NPAL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NPAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NPAL_OPTION_KEY', 'nashua_pal_settings' );

require_once NPAL_PLUGIN_DIR . 'includes/class-plugin.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-theme-vars.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-settings.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-assets.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-mobile-menu-walker.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-menu-icons.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-event.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-program.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-partner.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-event-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-program-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-program-category-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-partner-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-newsletter.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-newsletter-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-google-calendar.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-constant-contact.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-champions-winner.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-champions-sponsor.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-champions-winner-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-champions-sponsor-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-champions-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-legacy-winners-migration.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-champions-auction.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-champions-auction-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-champions-auction-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-cwac-sponsor.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-cwac-sponsor-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-cwac-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-board-member.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-team-member.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-volunteer-leader.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-board-member-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-team-member-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-volunteer-leader-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-people-templates.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-job.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-job-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-sponsor.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-sponsor-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-sponsor-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-admin-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-scholar.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-scholarship.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-scholar-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-scholarship-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-scholar-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-sponsor-package.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-sponsor-package-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-sponsor-package-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-impact-awardee.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-impact-awardee-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-impact-awardee-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-impact-report.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-impact-report-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-impact-report-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-donor.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-donor-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-donor-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-partner-list.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-partner-list-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-partner-list-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-tax990.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-tax990-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-tax990-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-bright-future.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-bright-future-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-bright-future-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/cpt/class-cpt-board-doc.php';
require_once NPAL_PLUGIN_DIR . 'includes/meta/class-board-doc-meta.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-board-doc-shortcodes.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-board-doc-categories.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-password-form.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-icons.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-icon-picker.php';
require_once NPAL_PLUGIN_DIR . 'includes/class-template-importer.php';
require_once NPAL_PLUGIN_DIR . 'includes/elementor/class-loader.php';

register_activation_hook( __FILE__, array( 'NPAL_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'NPAL_Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'NPAL_Plugin', 'instance' ) );
