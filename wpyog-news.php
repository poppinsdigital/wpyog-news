<?php
/**
 * Plugin Name: WPYog News
 * Plugin URI:  https://popswidgets.com/wpyog-news/
 * Description: A clean and lightweight News plugin with List and Card layouts. Supports External URLs, Source details, Gutenberg block, and all major page builders.
 * Version:     1.1.1
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * Author:      popswidgets.com
 * Author URI:  https://popswidgets.com/
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpyog-news
 * Domain Path: /languages
 *
 * @package WPYog_News
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Plugin version
if ( ! defined( 'WPYOG_NEWS_VERSION' ) ) {
	define( 'WPYOG_NEWS_VERSION', '1.1.1' );
}

// Plugin directory path
if ( ! defined( 'WPYOG_NEWS_DIR' ) ) {
	define( 'WPYOG_NEWS_DIR', plugin_dir_path( __FILE__ ) );
}

// Plugin directory URL
if ( ! defined( 'WPYOG_NEWS_URL' ) ) {
	define( 'WPYOG_NEWS_URL', plugin_dir_url( __FILE__ ) );
}

// Post type slug
if ( ! defined( 'WPYOG_NEWS_POST_TYPE' ) ) {
	define( 'WPYOG_NEWS_POST_TYPE', 'wpyog_news' );
}

// Taxonomy slug
if ( ! defined( 'WPYOG_NEWS_CAT' ) ) {
	define( 'WPYOG_NEWS_CAT', 'wpyog_news_cat' );
}

/**
 * Plugin activation hook.
 *
 * @since 1.0.0
 */
function wpyog_news_activate() {
	// Register post type so rewrite rules flush properly.
	require_once WPYOG_NEWS_DIR . 'includes/class-wpyog-post-types.php';
	WPYOG_Post_Types::register_post_type();
	WPYOG_Post_Types::register_taxonomy();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wpyog_news_activate' );

/**
 * Plugin deactivation hook.
 *
 * @since 1.0.0
 */
function wpyog_news_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wpyog_news_deactivate' );

/**
 * Bootstrap the plugin.
 *
 * @since 1.0.0
 */
function wpyog_news_init() {

	// Core helpers
	require_once WPYOG_NEWS_DIR . 'includes/class-wpyog-functions.php';

	// Post types & taxonomies
	require_once WPYOG_NEWS_DIR . 'includes/class-wpyog-post-types.php';
	new WPYOG_Post_Types();

	// Scripts & styles
	require_once WPYOG_NEWS_DIR . 'includes/class-wpyog-scripts.php';
	new WPYOG_Scripts();

	// Metaboxes (External URL + Source Details)
	require_once WPYOG_NEWS_DIR . 'includes/admin/class-wpyog-metabox.php';
	new WPYOG_Metabox();

	// Admin menus & settings
	if ( is_admin() ) {
		require_once WPYOG_NEWS_DIR . 'includes/admin/class-wpyog-admin.php';
		new WPYOG_Admin();
	}

	// Shortcode
	require_once WPYOG_NEWS_DIR . 'includes/shortcode/class-wpyog-shortcode.php';
	new WPYOG_Shortcode();

	// Gutenberg block
	if ( function_exists( 'register_block_type' ) ) {
		require_once WPYOG_NEWS_DIR . 'includes/blocks/class-wpyog-block.php';
		new WPYOG_Block();
	}

	// Elementor integration (free + Pro Theme Builder)
	if ( did_action( 'elementor/loaded' ) || defined( 'ELEMENTOR_VERSION' ) ) {
		require_once WPYOG_NEWS_DIR . 'includes/class-wpyog-elementor.php';
		new WPYOG_Elementor();
	}
}
add_action( 'plugins_loaded', 'wpyog_news_init' );

/**
 * Load the plugin's single post template when the active theme does not
 * supply its own single-wpyog_news.php.
 *
 * Priority: theme's single-wpyog_news.php → plugin template → theme's single.php
 *
 * @param string $template Full path to the resolved template file.
 * @return string
 */
function wpyog_news_single_template( $template ) {

	if ( ! is_singular( WPYOG_NEWS_POST_TYPE ) ) {
		return $template;
	}

	// If the active theme already has a specific template, respect it.
	$theme_template = locate_template( array( 'single-' . WPYOG_NEWS_POST_TYPE . '.php' ) );
	if ( $theme_template ) {
		return $theme_template;
	}

	// Fall back to the plugin-bundled template.
	$plugin_template = WPYOG_NEWS_DIR . 'templates/single-wpyog_news.php';
	if ( file_exists( $plugin_template ) ) {
		return $plugin_template;
	}

	return $template;
}
add_filter( 'single_template', 'wpyog_news_single_template' );
