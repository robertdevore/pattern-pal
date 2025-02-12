<?php

/**
 * The plugin bootstrap file.
 *
 * @link              https://pluginpal.app/
 * @since             1.0.0
 * @package           Pattern_Pal
 *
 * @wordpress-plugin
 *
 * Plugin Name: Pattern Pal
 * Description: Generate custom WordPress® block patterns with AI
 * Plugin URI:  https://github.com/robertdevore/pattern-pal/
 * Version:     1.0.0
 * Author:      Plugin Pal
 * Author URI:  https://pluginpal.app/
 * License:     GPL-3.0+
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: pattern-pal
 * Domain Path: /languages
 * Update URI:  https://github.com/robertdevore/pattern-pal/
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Set the current version.
define( 'PATTERN_PAL_VERSION', '1.0.0' );

require 'vendor/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/robertdevore/pattern-pal/',
	__FILE__,
	'pattern-pal'
);

// Set the branch that contains the stable release.
$myUpdateChecker->setBranch( 'main' );

// Check if Composer's autoloader is already registered globally.
if ( ! class_exists( 'RobertDevore\WPComCheck\WPComPluginHandler' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use RobertDevore\WPComCheck\WPComPluginHandler;

new WPComPluginHandler( plugin_basename( __FILE__ ), 'https://robertdevore.com/why-this-plugin-doesnt-support-wordpress-com-hosting/' );

// Load dependencies
require_once plugin_dir_path( __FILE__ ) . 'includes/admin-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/api-handler.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/block-inserter.php';

/**
 * Load plugin text domain for localization.
 *
 * @since  1.0.0
 * @return void
 */
function pattern_pal_load_textdomain() {
    load_plugin_textdomain( 'pattern-pal', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'pattern_pal_load_textdomain' );

/**
 * Enqueues editor assets for the block editor.
 *
 * @since  1.0.0
 * @return void
 */
function pattern_pal_enqueue_editor_assets() {
    // Ensure we're in the block editor.
    if ( ! is_admin() ) {
        return;
    }

    wp_enqueue_script(
        'pattern-pal-block-editor',
        plugin_dir_url( __FILE__ ) . 'build/index.js',
        [ 'wp-blocks', 'wp-editor', 'wp-components', 'wp-data', 'wp-element' ],
        PATTERN_PAL_VERSION,
        true
    );

    // Debugging: Check if this is running.
    if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
        error_log( 'pattern_pal_enqueue_editor_assets() is running.' );
    }

    // Pass AJAX URL and nonce securely.
    wp_localize_script(
        'pattern-pal-block-editor',
        'patternpalNonce',
        [
            'nonce'       => wp_create_nonce( 'pattern_pal_nonce' ),
            'ajaxurl'     => admin_url( 'admin-ajax.php' ),
            'settingsUrl' => admin_url( 'options-general.php?page=pattern-pal-settings' ),
        ]
    );
}
add_action( 'enqueue_block_editor_assets', 'pattern_pal_enqueue_editor_assets' );

/**
 * Enqueue admin styles.
 * 
 * @since  1.0.0
 * @return void
 */
function pattern_pal_enqueue_admin_styles( $hook ) {
    if ( $hook !== 'settings_page_pattern-pal-settings' ) {
        return;
    }
    wp_enqueue_style( 'pattern-pal-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin-settings.css', [], PATTERN_PAL_VERSION );
}
add_action( 'admin_enqueue_scripts', 'pattern_pal_enqueue_admin_styles' );

/**
 * Runs on plugin activation.
 *
 * @since  1.1.0
 * @return void
 */
function pattern_pal_activation() {
    // Set a transient flag to trigger the redirect.
    set_transient( 'pattern_pal_activation_redirect', true, 30 );
}
register_activation_hook( __FILE__, 'pattern_pal_activation' );

/**
 * Redirects to the settings page on first load after activation.
 *
 * @since  1.1.0
 * @return void
 */
function pattern_pal_redirect_after_activation() {
    // Check if our redirect transient is set.
    if ( get_transient( 'pattern_pal_activation_redirect' ) ) {
        // Delete the transient so the redirect only happens once.
        delete_transient( 'pattern_pal_activation_redirect' );
        // Do not redirect on bulk activation.
        if ( ! isset( $_GET['activate-multi'] ) ) {
            wp_redirect( admin_url( 'options-general.php?page=pattern-pal-settings' ) );
            exit;
        }
    }
}
add_action( 'admin_init', 'pattern_pal_redirect_after_activation' );
