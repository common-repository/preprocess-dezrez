<?php

/**
 *
 *
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 *
 * Plugin Name:       PreProcess DezRez
 * Plugin URI:        http://fullworks.net/wordpress-plugins/preprocess-dezrez/
 * Description:       PreProcess Dezrez to download images of updated properties
 * Version:           1.1.2
 * Author:            Fullworks
 * Author URI:        http://fullworks.net/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       preprocess-dezrez
 * Domain Path:       /languages
 *
 *
 *
 */

namespace Fullworks_Preprocess_Dezrez;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
define( 'FULLWORKS_PREPROCESS_DEZREZ_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include the autoloader so we can dynamically include the classes.
require_once( FULLWORKS_PREPROCESS_DEZREZ_PLUGIN_DIR . 'includes/autoloader.php' );


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 */
function run_plugin_name() {
	/**
	 *  Load freemius SDK
	 */
	$freemius = new \Fullworks_Preprocess_Dezrez\Includes\Freemius_Config();
	$freemiusSDK = $freemius->init();
    // Signal that SDK was initiated.

	do_action( 'pd_fs_loaded' );

	/**
	 * The code that runs during plugin activation.
	 * This action is documented in includes/class-activator.php
	 */
	register_activation_hook( __FILE__, array('\Fullworks_Preprocess_Dezrez\Includes\Activator', 'activate') );
	/**
	 * The code that runs during plugin deactivation.
	 * This action is documented in includes/class-deactivator.php
	 */
	register_deactivation_hook( __FILE__, array('\Fullworks_Preprocess_Dezrez\Includes\Deactivator', 'deactivate') );


	$freemiusSDK->add_action('after_uninstall', array( '\Fullworks_Preprocess_Dezrez\Includes\Uninstall', 'uninstall' ));

	/**
	 * The core plugin class that is used to define internationalization,
	 * admin-specific hooks, and public-facing site hooks.
	 */
	$plugin = new \Fullworks_Preprocess_Dezrez\Includes\Core($freemiusSDK);
	$plugin->run();

}
run_plugin_name();
