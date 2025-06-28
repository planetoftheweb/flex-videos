<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package FlexVideos
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('itsplaitime_yt_api_key');
delete_option('itsplaitime_yt_channel_id');

// Delete any cached data
wp_cache_delete('flex_videos_cache', 'flex_videos');

// Clean up any custom database tables if you had any
// (Your current plugin doesn't seem to create custom tables, but this is where you'd clean them up)

// Clear any scheduled cron jobs related to this plugin
wp_clear_scheduled_hook('flex_videos_cache_cleanup');
