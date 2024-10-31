<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 */
namespace Fullworks_Preprocess_Dezrez\Admin;

use  DateTimeZone ;
use  DateTime ;
class Admin
{
    /**
     * The ID of this plugin.
     *
     */
    private  $plugin_name ;
    /**
     * The version of this plugin.
     *
     */
    private  $version ;
    /**
     * Initialize the class and set its properties.
     *
     */
    public function __construct( $plugin_name, $version, $freemiusSDK )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->freemiusSDK = $freemiusSDK;
    }
    
    /**
     * Register the JavaScript for the admin area.
     *
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/admin.js',
            array( 'jquery' ),
            $this->version,
            false
        );
    }
    
    public function run_download()
    {
        // called by wpcron hook
        $cronjob = new \Fullworks_Preprocess_Dezrez\Includes\Download_Dezrez( $this->freemiusSDK );
        $cronjob->run();
    }

}