<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    PluginName
 * @subpackage PluginName/includes
 */
/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 */
namespace Fullworks_Preprocess_Dezrez\Includes;

class Deactivator
{
    /**
     * Remove schedule hook
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate()
    {
        global  $pd_fs ;
        wp_clear_scheduled_hook( 'preprocess_dezrez_hook' );
    }

}