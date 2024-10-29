<?php

/**
 * Plugin Name:     Automizy Gravity Forms
 * Description:     Automizy connector for Gravity Forms
 * Author:          Chris Bibby
 * Author URI:      https://chrisbibby.com.au
 * Text Domain:     automizy-gravity-forms
 * Domain Path:     /languages
 * Version:         1.0.3
 *
 *
 * @package         Automizy_Gravity_Forms
 */

if ( !function_exists( 'agf_fs' ) ) {
    // Create a helper function for easy SDK access.
    function agf_fs()
    {
        global  $agf_fs ;
        
        if ( !isset( $agf_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $agf_fs = fs_dynamic_init( array(
                'id'             => '7500',
                'slug'           => 'automizy-gravity-forms',
                'type'           => 'plugin',
                'public_key'     => 'pk_4ef5aec6e493c84a07130c5a38b5f',
                'is_premium'     => false,
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'first-path' => 'admin.php?page=gf_settings&subview=automizy-gravity-forms',
                'support'    => false,
            ),
                'is_live'        => true,
            ) );
        }
        
        return $agf_fs;
    }
    
    // Init Freemius .
    // agf_fs();
    // Signal that SDK was initiated .
    do_action( 'agf_fs_loaded' );
}

define( 'GF_AUTOMIZY_VERSION', '1.0' );
class GF_Automizy_Plugin
{
    /**
     * File path of the main feed class to include
     *
     * @var string
     */
    protected  $feed_class_file = '' ;
    protected  $feed_class_name = '' ;
    /**
     *
     * Fire it up
     */
    public function __construct()
    {
        $this->feed_class_file = 'inc/class-gf-automizy-free.php';
        $this->feed_class_name = 'GF_Automizy_Feed_Free';
        add_action( 'plugins_loaded', array( $this, 'check_gf_exists' ) );
        add_action( 'gform_loaded', array( $this, 'load_addon_framework' ), 5 );
    }
    
    /**
     *
     * Check to see if Gravity is active. If not show a notice and deactivate this plugin.
     *
     * @return void
     */
    public function check_gf_exists()
    {
        if ( !class_exists( 'GFForms' ) ) {
            add_action( 'admin_notices', function () {
                $class = esc_attr( 'error notice' );
                $message = esc_html__( 'Automizy Gravity Forms requires Gravity Forms to be active.', 'automizy-gravity-forms' );
                printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );
            } );
        }
    }
    
    /**
     *
     * Load and register the GF Addon class
     *
     * @return void
     */
    public function load_addon_framework()
    {
        if ( !method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
            return;
        }
        GFForms::include_feed_addon_framework();
        require_once plugin_dir_path( __FILE__ ) . 'inc/class-automizy-api.php';
        require_once plugin_dir_path( __FILE__ ) . 'inc/class-gf-automizy.php';
        require_once plugin_dir_path( __FILE__ ) . $this->feed_class_file;
        GFAddOn::register( $this->feed_class_name );
    }

}
/**
 * Instantiate the plugin
 */
new GF_Automizy_Plugin();