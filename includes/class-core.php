<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 */
namespace Fullworks_Preprocess_Dezrez\Includes;

use  Fullworks_Preprocess_Dezrez\Admin\Admin ;
use  Fullworks_Preprocess_Dezrez\Admin\Settings ;
use  Fullworks_Preprocess_Dezrez\FrontEnd\FrontEnd ;
use  Fullworks_Preprocess_Dezrez\Includes\Loader ;
use  Fullworks_Preprocess_Dezrez\Includes\i18n ;
class Core
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     */
    protected  $loader ;
    /**
     * The unique identifier of this plugin.
     *
     */
    protected  $plugin_name ;
    /**
     * The current version of the plugin.
     *
     */
    protected  $version ;
    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     */
    public function __construct( $freemiusSDK )
    {
        $this->plugin_name = 'Fullworks_Preprocess_Dezrez';
        // @TODO set plugin version
        $this->version = '1.1.1';
        $this->freemiusSDK = $freemiusSDK;
        $this->load_dependencies();
        $this->set_locale();
        $this->settings_pages();
        $this->define_admin_hooks();
    }
    
    /**
     *
     *  Loader. Orchestrates the hooks of the plugin.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        $this->loader = new Loader();
    }
    
    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     */
    private function set_locale()
    {
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        $plugin_i18n = new i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }
    
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     */
    private function settings_pages()
    {
        $this->settings = new Settings( 'Fullworks_Preprocess_Dezrez', $this->freemiusSDK );
    }
    
    private function define_admin_hooks()
    {
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        $plugin_admin = new Admin( $this->get_plugin_name(), $this->get_version(), $this->freemiusSDK );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'preprocess_dezrez_hook', $plugin_admin, 'run_download' );
    }
    
    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }
    
    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
    
    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     */
    public function run()
    {
        $this->loader->run();
    }
    
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     */
    public function get_loader()
    {
        return $this->loader;
    }
    
    /* logger */
    public static function log_it( $message )
    {
        
        if ( is_array( $message ) || is_object( $message ) ) {
            error_log( 'Fullworks_Preprocess_Dezrez:' . print_r( $message, true ) );
        } else {
            error_log( 'Fullworks_Preprocess_Dezrez:' . $message );
        }
    
    }

}