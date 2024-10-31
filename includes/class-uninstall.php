<?php


/**
 * Fired during plugin uninstall.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 */

namespace Fullworks_Preprocess_Dezrez\Includes;

class Uninstall {

	/**
	 * Uninstall specific code
	 */
	public static function uninstall() {
		delete_option( 'Fullworks_Preprocess_Dezrez' . '_settings' );
		delete_transient( 'fpd-process-last-run' );
		delete_transient('fpd_all_import_running');
// @todo delete cache directory
	}

}
