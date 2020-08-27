<?php
/**
 * Plugin Name:       Currency Convertor - WP Travel Engine
 * Plugin URI:        https://wordpress.org/plugins/wp-travel-engine/
 * Description:       A Test Project
 * Version:           1.0.0
 * Author:            Prakash Khadka
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wptecc
 * Domain Path:       /languages
 *
 * @package wptecc
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WPTECC_FILE' ) ) {
	define( 'WPTECC_FILE', __FILE__ );
}

if ( ! class_exists( 'WPTECC' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wptecc.php';
}

// Plugin Initialization.
new WPTECC();
