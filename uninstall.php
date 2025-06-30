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
$option_keys = [
    'flex_videos_api_key',
    'flex_videos_channel_id',
    'flex_videos_columns',
    'flex_videos_gap',
    'flex_videos_show_grid_title',
    'flex_videos_show_grid_description',
    'flex_videos_num_videos',
    'flex_videos_custom_grid_title',
    'flex_videos_custom_grid_desc',
    'flex_videos_show_channel_link',
    'flex_videos_channel_link_text',
    'flex_videos_button_color',
    'flex_videos_button_hover_color',
    'flex_videos_button_text_color',
    'flex_videos_cache_version',
    'flex_videos_db_version',
    'flex_videos_activated',
];

foreach ($option_keys as $key) {
    delete_option($key);
}

// Delete cached transients
global $wpdb;
$patterns = [
    '_transient_flex_videos_channel_info_%',
    '_transient_timeout_flex_videos_channel_info_%',
    '_transient_flex_videos_search_cache_%',
    '_transient_timeout_flex_videos_search_cache_%',
];
foreach ($patterns as $pattern) {
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $pattern
        )
    );
}

// Clear object cache if used
wp_cache_delete('flex_videos_cache', 'flex_videos');

// Clear any scheduled cron jobs related to this plugin
wp_clear_scheduled_hook('flex_videos_cache_cleanup');

