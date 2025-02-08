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
 * Description: Generate custom WordPress® block patterns with AI.
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
        filemtime( plugin_dir_path( __FILE__ ) . 'build/index.js' ),
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
            'nonce'   => wp_create_nonce( 'pattern_pal_nonce' ),
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        ]
    );
}
add_action( 'enqueue_block_editor_assets', 'pattern_pal_enqueue_editor_assets' );
