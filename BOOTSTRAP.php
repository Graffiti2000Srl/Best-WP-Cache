<?php
/*
Plugin Name: Best WP Cache
Plugin URI:  https://github.com/Graffiti2000Srl/Best-WP-Cache
Description: The ultimate Wordpress plugin to manage the cache
Version:     0.1a
Author:      Graffiti 2000
Author URI:  http://graffiti2000.com
*/

/*
 * This plugin was built on top of WordPress-Plugin-Skeleton by Ian Dunn.
 * See https://github.com/iandunn/WordPress-Plugin-Skeleton for details.
 */

if ( ! defined( 'ABSPATH' ) ) {
    die( 'Access denied.' );
}

define( 'BWPC_NAME',                 'Best WP Cache' );
define( 'BWPC_REQUIRED_PHP_VERSION', '5.3' );                          // because of get_called_class()
define( 'BWPC_REQUIRED_WP_VERSION',  '3.1' );                          // because of esc_textarea()

/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
function bwpc_requirements_met() {
    global $wp_version;

    if ( version_compare( PHP_VERSION, BWPC_REQUIRED_PHP_VERSION, '<' ) ) {
        return false;
    }

    if ( version_compare( $wp_version, BWPC_REQUIRED_WP_VERSION, '<' ) ) {
        return false;
    }

    return true;
}

/**
 * Prints an error that the system requirements weren't met.
 */
function bwpc_requirements_error() {
    global $wp_version;

    require_once( dirname( __FILE__ ) . '/views/requirements-error.php' );
}

/*
 * Check requirements and load main class
 * The main program needs to be in a separate file that only gets loaded if the plugin requirements are met. Otherwise older PHP installations could crash when trying to parse it.
 */
if ( bwpc_requirements_met() ) {
    require_once( __DIR__ . '/classes/wpps-module.php' );
    require_once( __DIR__ . '/classes/wordpress-plugin-skeleton.php' );
    require_once( __DIR__ . '/includes/admin-notice-helper/admin-notice-helper.php' );
    require_once( __DIR__ . '/classes/wpps-custom-post-type.php' );
    require_once( __DIR__ . '/classes/wpps-cpt-example.php' );
    require_once( __DIR__ . '/classes/wpps-settings.php' );
    require_once( __DIR__ . '/classes/wpps-cron.php' );
    require_once( __DIR__ . '/classes/wpps-instance-class.php' );

    if ( class_exists( 'Best_WP_Cache' ) ) {
        $GLOBALS['bwpc'] = Best_WP_Cache::get_instance();
        register_activation_hook(   __FILE__, array( $GLOBALS['bwpc'], 'activate' ) );
        register_deactivation_hook( __FILE__, array( $GLOBALS['bwpc'], 'deactivate' ) );
    }
} else {
    add_action( 'admin_notices', 'bwpc_requirements_error' );
}