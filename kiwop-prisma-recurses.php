<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://kiwop.com
 * @since             1.0.0
 * @package           Kiwop_Prisma_Recurses
 *
 * @wordpress-plugin
 * Plugin Name:       Kiwop - Prisma Recursos Educatius
 * Plugin URI:        https://kiwop.com
 * Description:       Scrapejador de recursos educatius, cercador de recursos escrapejats i assignació massiva de post_type
 * Version:           1.0.0
 * Author:            Antonio Sanchez (kiwop)
 * Author URI:        https://kiwop.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       kiwop-prisma-recurses
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

include('vendor/autoload.php');

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'KIWOP_PRISMA_RECURSES_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-kiwop-prisma-recurses-activator.php
 */
function activate_kiwop_prisma_recurses() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-kiwop-prisma-recurses-activator.php';
	Kiwop_Prisma_Recurses_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-kiwop-prisma-recurses-deactivator.php
 */
function deactivate_kiwop_prisma_recurses() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-kiwop-prisma-recurses-deactivator.php';
	Kiwop_Prisma_Recurses_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_kiwop_prisma_recurses' );
register_deactivation_hook( __FILE__, 'deactivate_kiwop_prisma_recurses' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-kiwop-prisma-recurses.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_kiwop_prisma_recurses() {

	$plugin = new Kiwop_Prisma_Recurses();
	$plugin->run();

}
run_kiwop_prisma_recurses();
