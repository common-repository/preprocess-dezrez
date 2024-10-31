<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    PluginName
 * @subpackage PluginName/includes
 */
/**
 * Fired during plugin activation.
 *
 */
namespace Fullworks_Preprocess_Dezrez\Includes;

class Activator
{
    /**
     * Set up cron job.
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        global  $pd_fs ;
        wp_schedule_event( time() - 30, 'hourly', 'preprocess_dezrez_hook' );
    }

}