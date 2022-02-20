<?php
/**
 * Plugin Name:       Plugin update tutorial
 * Plugin URI:        PLUGIN SITE HERE
 * Description:       This is the plugin for the tutorial about how to update WordPress plugins.
 * Author:            janw.oostendorp
 * Author URI:        https://janw.me
 * Text Domain:       update-tutorial
 * Domain Path:       /languages
 * Requires at least: 5.8
 * Requires PHP:      7.3
 * Version:           0.9.9
 *
 * @package         Update\Tutorial
 */

namespace Update\Tutorial;

define( 'UPDATE_TUTORIAL_VERSION', '0.9.9' );
define( 'UPDATE_TUTORIAL_DIR', plugin_dir_path( __FILE__ ) ); // Full path with trailing slash.
define( 'UPDATE_TUTORIAL_URL', plugin_dir_url( __FILE__ ) ); // With trailing slash.
define( 'UPDATE_TUTORIAL_SLUG', basename( __DIR__ ) ); // update-tutorial.

/**
 * Autoload internal classes.
 */
spl_autoload_register( function ( $class_name ) { //phpcs:ignore PEAR.Functions.FunctionCallSignature
	if ( strpos( $class_name, __NAMESPACE__ . '\App' ) !== 0 ) {
		return; // Not in the plugin namespace, don't check.
	}
	if ( strpos( $class_name, __NAMESPACE__ . '\App\Vendor' ) === 0 ) {
		return; // 3rd party, prefixed class.
	}
	$transform  = str_replace( __NAMESPACE__ . '\\', '', $class_name );                   // Remove NAMESPACE and it's "/".
	$transform  = str_replace( '_', '-', $transform );                                    // Replace "_" with "-".
	$transform  = preg_replace( '%\\\\((?:.(?!\\\\))+$)%', '\class-$1.php', $transform ); // Set correct classname.
	$transform  = str_replace( '\\', DIRECTORY_SEPARATOR, $transform );                   // Replace NS separator with dir separator.
	$class_path = UPDATE_TUTORIAL_DIR . strtolower( $transform );
	if ( ! file_exists( $class_path ) ) {
		wp_die( "<h1>Can't find class</h1><pre><code>Class: {$class_name}<br/>Path:  {$class_path}</code></pre>" ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	require_once $class_path;
} );//phpcs:ignore PEAR.Functions.FunctionCallSignature

/**
 * Hook everything.
 */

// Plugin (de)activation & uninstall.
register_activation_hook( __FILE__, array( '\Update\Tutorial\App\Admin', 'activate' ) );
register_deactivation_hook( __FILE__, array( '\Update\Tutorial\App\Admin', 'deactivate' ) );
register_uninstall_hook( __FILE__, array( '\Update\Tutorial\App\Admin', 'uninstall' ) );

// Adds a link to the settings page on the plugin overview.
add_filter( 'plugin_action_links', array( '\Update\Tutorial\App\Admin', 'settings_link' ), 10, 2 );

// Add the rest of the hooks & filters.
