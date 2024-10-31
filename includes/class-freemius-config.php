<?php

/**
 * Class to load freemius configuration
 */
namespace Fullworks_Preprocess_Dezrez\Includes;

class Freemius_Config
{
    public function init()
    {
        global  $pd_fs ;
        
        if ( !isset( $pd_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/vendor/freemius/wordpress-sdk/start.php';
            $pd_fs = fs_dynamic_init( array(
                'id'             => '1470',
                'slug'           => 'preprocess-dezrez',
                'type'           => 'plugin',
                'public_key'     => 'pk_3219a9b23f3e405eb500db884177d',
                'is_premium'     => false,
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'slug'    => 'Fullworks-Preprocess-Dezrez-settings',
                'support' => false,
                'parent'  => array(
                'slug' => 'options-general.php',
            ),
            ),
                'is_live'        => true,
            ) );
        }
        
        return $pd_fs;
    }

}