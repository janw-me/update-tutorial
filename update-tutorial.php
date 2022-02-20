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
 * Version:           1.0.0
 * Update URI:        plugins.janw.me
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'class-external-updater.php';

add_action( 'init', function () {
	$plugin_slug   = 'update-tutorial';
	$update_domain = 'plugins.janw.me';
	new \External_Updater( $plugin_slug, $update_domain );
} );
