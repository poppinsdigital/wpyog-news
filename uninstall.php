<?php
/**
 * Uninstall WPYog News.
 *
 * Called when the plugin is deleted (not just deactivated) from the WordPress admin.
 * Removes all plugin-related options. Post data is intentionally preserved.
 *
 * @package WPYog_News
 */

// Security: only run when WordPress itself is deleting the plugin.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Remove any plugin options if added in the future.
// delete_option( 'wpyog_news_settings' );

// Note: We intentionally do NOT delete news posts or their meta data on uninstall,
// to prevent accidental data loss. Users can manage their content separately.
