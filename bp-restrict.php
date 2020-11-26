<?php
/**
 *  Restrictions for BuddyPress
 *
 * Restrict BuddyPress pages or content
 *
 * @package   bp_restrict
 * @author    SeventhQueen <plugins@seventhqueen.com>
 * @license   GPL-2.0+
 * @link      http://wordpress.org/plugins
 * @copyright SeventhQueen
 *
 * @wordpress-plugin
 * Plugin Name:       BP Restrict
 * Plugin URI:        http://wordpress.org/plugins
 * Description:       Restrict BuddyPress pages or content
 * Version:           1.4.1
 * Author:            SeventhQueen
 * Author URI:        https://seventhqueen.com
 * Text Domain:       bp-restrict
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/seventhqueen/bp-restrict
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if (! defined('BP_RESTRICT_DIR')) {
	define('BP_RESTRICT_DIR', plugin_dir_path( __FILE__ ));
}

if (! defined('BP_RESTRICT_VERSION')) {
	define('BP_RESTRICT_VERSION', '1.4.1');
}


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-bp-restrict-activator.php
 */
function activate_bp_restrict() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bp-restrict-activator.php';
	bp_restrict_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-bp-restrict-deactivator.php
 */
function deactivate_bp_restrict() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-bp-restrict-deactivator.php';
	bp_restrict_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_bp_restrict' );
register_deactivation_hook( __FILE__, 'deactivate_bp_restrict' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-bp-restrict.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 * @return BP_Restrict
 * @since    1.0.0
 */
function bp_restrict() {
	return BP_Restrict::getInstance();
}
bp_restrict()->run();
