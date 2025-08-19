<?php
/**
 * Plugin Name:       Flex Videos
 * Plugin URI:        https://github.com/planetoftheweb/flex-videos
 * Description:       A WordPress plugin for displaying flexible, responsive video embeds with YouTube API integration.
 * Version:           1.0.2
 * Author:            Ray Villalobos
 * Author URI:        https://itsplaitime.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       flex-videos
 * Requires at least: 5.0
 * Tested up to:      6.8
 * Requires PHP:      7.4
 */

// Block direct access to the file.
if (!defined('ABSPATH')) {
    exit;
}

// Load Composer dependencies if available.
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Register Gutenberg block.
require_once plugin_dir_path( __FILE__ ) . 'blocks/register-block.php';

// --- CACHE CONFIGURATION CONSTANTS ---

// Different cache durations based on data volatility
if (!defined('FLEX_VIDEOS_CHANNEL_CACHE_DURATION')) {
    define('FLEX_VIDEOS_CHANNEL_CACHE_DURATION', 12 * HOUR_IN_SECONDS); // 12 hours - channels rarely change
}
if (!defined('FLEX_VIDEOS_VIDEO_CACHE_DURATION')) {
    define('FLEX_VIDEOS_VIDEO_CACHE_DURATION', 2 * HOUR_IN_SECONDS); // 2 hours - videos change more frequently
}
if (!defined('FLEX_VIDEOS_SEARCH_CACHE_DURATION')) {
    define('FLEX_VIDEOS_SEARCH_CACHE_DURATION', 30 * MINUTE_IN_SECONDS); // 30 minutes - hashtag searches change frequently
}
if (!defined('FLEX_VIDEOS_MAX_API_CALLS_PER_HOUR')) {
    define('FLEX_VIDEOS_MAX_API_CALLS_PER_HOUR', 1000); // Conservative limit to prevent quota exhaustion
}

// --- PLUGIN SETTINGS & CACHE HANDLING ---

/**
 * Check if API quota allows for another request
 * 
 * @return bool True if quota allows request, false otherwise
 */
function flex_videos_check_api_quota() {
    $quota_key = 'flex_videos_api_calls_' . date('YmdH');
    $current_calls = get_transient($quota_key) ?: 0;
    
    if ($current_calls >= FLEX_VIDEOS_MAX_API_CALLS_PER_HOUR) {
        error_log('Flex Videos: API quota limit reached for hour ' . date('YmdH'));
        return false;
    }
    
    return true;
}

/**
 * Increment API call counter
 */
function flex_videos_increment_api_calls() {
    $quota_key = 'flex_videos_api_calls_' . date('YmdH');
    $current_calls = get_transient($quota_key) ?: 0;
    set_transient($quota_key, $current_calls + 1, HOUR_IN_SECONDS);
}

/**
 * Get current API usage statistics
 * 
 * @return array API usage data
 */
function flex_videos_get_api_usage() {
    $quota_key = 'flex_videos_api_calls_' . date('YmdH');
    $current_calls = get_transient($quota_key) ?: 0;
    
    return [
        'current_hour_calls' => $current_calls,
        'max_calls_per_hour' => FLEX_VIDEOS_MAX_API_CALLS_PER_HOUR,
        'remaining_calls' => FLEX_VIDEOS_MAX_API_CALLS_PER_HOUR - $current_calls,
        'quota_percentage' => round(($current_calls / FLEX_VIDEOS_MAX_API_CALLS_PER_HOUR) * 100, 1)
    ];
}

/**
 * Enhanced API request with quota tracking and error handling
 * 
 * @param string $url API endpoint URL
 * @param array $args Additional request arguments
 * @return array|WP_Error Response data or error
 */
function flex_videos_api_request($url, $args = []) {
    // Check quota before making request
    if (!flex_videos_check_api_quota()) {
        return new WP_Error(
            'quota_exceeded', 
            __('API quota limit reached. Please try again later.', 'flex-videos')
        );
    }
    
    // Check for stored ETag to make conditional request
    $etag_key = 'flex_videos_etag_' . md5($url);
    $stored_etag = get_transient($etag_key);
    
    if ($stored_etag) {
        $args['headers'] = array_merge(
            $args['headers'] ?? [],
            ['If-None-Match' => $stored_etag]
        );
    }
    
    // Make the API request
    $response = wp_remote_get($url, $args);
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    
    // Handle 304 Not Modified response
    if ($response_code === 304) {
        // Data unchanged, return special indicator to use cached data
        return new WP_Error('not_modified', __('Data not modified, use cached version.', 'flex-videos'));
    }
    
    // Store new ETag for future requests
    $new_etag = wp_remote_retrieve_header($response, 'etag');
    if ($new_etag) {
        set_transient($etag_key, $new_etag, DAY_IN_SECONDS);
    }
    
    // Increment counter for successful requests only
    if ($response_code === 200) {
        flex_videos_increment_api_calls();
    }
    
    return $response;
}

/**
 * Get data with fallback to stale cache on API failure
 * 
 * @param string $cache_key Primary cache key
 * @param callable $api_callback Function to call API
 * @param int $cache_duration Primary cache duration
 * @return mixed Cached data, fresh data, or error
 */
function flex_videos_get_with_fallback($cache_key, $api_callback, $cache_duration = HOUR_IN_SECONDS) {
    // Try to get fresh cached data first
    $cached_data = get_transient($cache_key);
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // Cache miss - try API call
    $api_result = $api_callback();
    
    if (is_wp_error($api_result)) {
        // Check if it's a 304 Not Modified response
        if ($api_result->get_error_code() === 'not_modified') {
            // Data hasn't changed, extend the current cache
            $cached_data = get_transient($cache_key);
            if ($cached_data !== false) {
                // Extend cache duration
                set_transient($cache_key, $cached_data, $cache_duration);
                return $cached_data;
            }
        }
        
        // API failed - check for stale cache as fallback
        $stale_key = $cache_key . '_stale';
        $stale_data = get_transient($stale_key);
        if ($stale_data !== false) {
            error_log('Flex Videos: Using stale cache due to API failure: ' . $api_result->get_error_message());
            return $stale_data;
        }
        
        // No fallback available
        return $api_result;
    }
    
    // Success - store fresh data and stale backup
    $response_code = wp_remote_retrieve_response_code($api_result);
    if ($response_code === 200) {
        $response_body = wp_remote_retrieve_body($api_result);
        $data = json_decode($response_body, true);
        
        if ($data) {
            // Store primary cache
            set_transient($cache_key, $data, $cache_duration);
            // Store stale backup with longer duration
            set_transient($cache_key . '_stale', $data, WEEK_IN_SECONDS);
            return $data;
        }
    }
    
    return new WP_Error('api_error', __('Failed to parse API response.', 'flex-videos'));
}

/**
 * Background cache warming functionality
 */
function flex_videos_schedule_cache_warming() {
    if (!wp_next_scheduled('flex_videos_warm_cache')) {
        wp_schedule_event(time(), 'hourly', 'flex_videos_warm_cache');
    }
}
add_action('init', 'flex_videos_schedule_cache_warming');

/**
 * Warm critical cache data in the background
 */
function flex_videos_warm_cache() {
    $api_key = get_option('flex_videos_api_key');
    $channel_id = get_option('flex_videos_channel_id');
    
    if (!$api_key || !$channel_id) {
        return;
    }
    
    // Check if we're approaching quota limits
    $api_usage = flex_videos_get_api_usage();
    if ($api_usage['quota_percentage'] > 90) {
        error_log('Flex Videos: Skipping cache warming due to high API usage');
        return;
    }
    
    // Warm channel info cache if it's about to expire
    $channel_cache_key = 'flex_videos_channel_info_' . $channel_id;
    $channel_ttl = flex_videos_get_cache_ttl($channel_cache_key);
    
    if ($channel_ttl !== false && $channel_ttl < HOUR_IN_SECONDS) {
        // Channel cache expires soon, refresh it
        $channel_api_url = sprintf(
            'https://www.googleapis.com/youtube/v3/channels?part=snippet&fields=items(snippet(title,description,customUrl))&id=%s&key=%s',
            $channel_id,
            $api_key
        );
        
        $response = flex_videos_api_request($channel_api_url);
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $channel_data = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($channel_data['items'][0]['snippet'])) {
                $channel_info = [
                    'title' => $channel_data['items'][0]['snippet']['title'],
                    'description' => $channel_data['items'][0]['snippet']['description'],
                    'customUrl' => $channel_data['items'][0]['snippet']['customUrl'] ?? '',
                ];
                set_transient($channel_cache_key, $channel_info, FLEX_VIDEOS_CHANNEL_CACHE_DURATION);
                set_transient($channel_cache_key . '_stale', $channel_info, WEEK_IN_SECONDS);
                error_log('Flex Videos: Successfully warmed channel cache');
            }
        }
    }
    
    // Warm video cache for default grid
    $cache_version = get_option('flex_videos_cache_version', 1);
    $video_cache_key = 'flex_videos_search_cache_' . md5('_v' . $cache_version);
    $video_ttl = flex_videos_get_cache_ttl($video_cache_key);
    
    if ($video_ttl !== false && $video_ttl < HOUR_IN_SECONDS) {
        // Video cache expires soon, refresh it
        $api_url = sprintf(
            'https://www.googleapis.com/youtube/v3/search?part=snippet&fields=items(id/videoId,snippet(title,description,thumbnails))&channelId=%s&order=date&type=video&maxResults=15&key=%s',
            $channel_id,
            $api_key
        );
        
        $response = flex_videos_api_request($api_url);
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $api_data = json_decode(wp_remote_retrieve_body($response), true);
            if ($api_data) {
                set_transient($video_cache_key, $api_data, FLEX_VIDEOS_VIDEO_CACHE_DURATION);
                set_transient($video_cache_key . '_stale', $api_data, WEEK_IN_SECONDS);
                error_log('Flex Videos: Successfully warmed video cache');
            }
        }
    }
}
add_action('flex_videos_warm_cache', 'flex_videos_warm_cache');

/**
 * Get remaining TTL for a transient
 * 
 * @param string $transient_key Transient key
 * @return int|false TTL in seconds or false if not found
 */
function flex_videos_get_cache_ttl($transient_key) {
    global $wpdb;
    
    $transient_timeout = '_transient_timeout_' . $transient_key;
    $timeout = $wpdb->get_var($wpdb->prepare(
        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s",
        $transient_timeout
    ));
    
    if ($timeout === null) {
        return false;
    }
    
    $ttl = $timeout - time();
    return max(0, $ttl);
}

/**
 * Clear cache warming on plugin deactivation
 */
function flex_videos_clear_scheduled_events() {
    wp_clear_scheduled_hook('flex_videos_warm_cache');
}
register_deactivation_hook(__FILE__, 'flex_videos_clear_scheduled_events');

function flex_videos_add_admin_menu() {
    add_options_page(
        __('Flex Videos Settings', 'flex-videos'),
        __('Flex Videos', 'flex-videos'),
        'manage_options',
        'flex_videos_settings',
        'flex_videos_settings_page_html'
    );
}
add_action('admin_menu', 'flex_videos_add_admin_menu');

function flex_videos_settings_init() {
    // API Section
    add_settings_section(
        'flex_videos_api_section',
        __('YouTube API Configuration', 'flex-videos'),
        null,
        'flex_videos_settings_group'
    );
    register_setting('flex_videos_settings_group', 'flex_videos_api_key', 'sanitize_text_field');
    register_setting('flex_videos_settings_group', 'flex_videos_channel_id', 'sanitize_text_field');
    add_settings_field(
        'flex_videos_api_key',
        __('YouTube Data API Key', 'flex-videos'),
        'flex_videos_api_key_field_html',
        'flex_videos_settings_group',
        'flex_videos_api_section'
    );
    add_settings_field(
        'flex_videos_channel_id',
        __('YouTube Channel ID', 'flex-videos'),
        'flex_videos_channel_id_field_html',
        'flex_videos_settings_group',
        'flex_videos_api_section'
    );

    // Display Section
    add_settings_section(
        'flex_videos_display_section',
        __('Grid Display Options', 'flex-videos'),
        null,
        'flex_videos_settings_group'
    );
    register_setting('flex_videos_settings_group', 'flex_videos_columns', 'intval');
    register_setting('flex_videos_settings_group', 'flex_videos_gap', 'intval');
    register_setting('flex_videos_settings_group', 'flex_videos_show_grid_title', 'sanitize_text_field');
    register_setting('flex_videos_settings_group', 'flex_videos_show_grid_description', 'sanitize_text_field');
    register_setting('flex_videos_settings_group', 'flex_videos_num_videos', 'intval');
    register_setting('flex_videos_settings_group', 'flex_videos_custom_grid_title', 'sanitize_text_field');
    register_setting('flex_videos_settings_group', 'flex_videos_custom_grid_desc', 'sanitize_textarea_field');
    add_settings_field(
        'flex_videos_columns',
        __('Number of Columns (Grid)', 'flex-videos'),
        'flex_videos_columns_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );
    add_settings_field(
        'flex_videos_gap',
        __('Gap Between Thumbnails (px)', 'flex-videos'),
        'flex_videos_gap_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );
    add_settings_field(
        'flex_videos_num_videos',
        __('Number of Videos to Show', 'flex-videos'),
        'flex_videos_num_videos_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );
    add_settings_field(
        'flex_videos_show_grid_title',
        __('Show Grid Title', 'flex-videos'),
        'flex_videos_show_grid_title_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );
    add_settings_field(
        'flex_videos_custom_grid_title',
        __('Custom Grid Title', 'flex-videos'),
        'flex_videos_custom_grid_title_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );
    add_settings_field(
        'flex_videos_show_grid_description',
        __('Show Grid Description', 'flex-videos'),
        'flex_videos_show_grid_description_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );
    add_settings_field(
        'flex_videos_custom_grid_desc',
        __('Custom Grid Description', 'flex-videos'),
        'flex_videos_custom_grid_desc_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );

    // Channel Link Section
    add_settings_section(
        'flex_videos_channel_section',
        __('Channel Link Options', 'flex-videos'),
        null,
        'flex_videos_settings_group'
    );
    register_setting('flex_videos_settings_group', 'flex_videos_show_channel_link', 'sanitize_text_field');
    register_setting('flex_videos_settings_group', 'flex_videos_channel_link_text', 'sanitize_text_field');
    register_setting('flex_videos_settings_group', 'flex_videos_button_color', 'sanitize_hex_color');
    register_setting('flex_videos_settings_group', 'flex_videos_button_hover_color', 'sanitize_hex_color');
    register_setting('flex_videos_settings_group', 'flex_videos_button_text_color', 'sanitize_hex_color');
    add_settings_field(
        'flex_videos_show_channel_link',
        __('Show Channel Link', 'flex-videos'),
        'flex_videos_show_channel_link_field_html',
        'flex_videos_settings_group',
        'flex_videos_channel_section'
    );
    add_settings_field(
        'flex_videos_channel_link_text',
        __('Channel Link Text', 'flex-videos'),
        'flex_videos_channel_link_text_field_html',
        'flex_videos_settings_group',
        'flex_videos_channel_section'
    );
    add_settings_field(
        'flex_videos_button_color',
        __('Button Background Color', 'flex-videos'),
        'flex_videos_button_color_field_html',
        'flex_videos_settings_group',
        'flex_videos_channel_section'
    );
    add_settings_field(
        'flex_videos_button_hover_color',
        __('Button Hover Color', 'flex-videos'),
        'flex_videos_button_hover_color_field_html',
        'flex_videos_settings_group',
        'flex_videos_channel_section'
    );
    add_settings_field(
        'flex_videos_button_text_color',
        __('Button Text Color', 'flex-videos'),
        'flex_videos_button_text_color_field_html',
        'flex_videos_settings_group',
        'flex_videos_channel_section'
    );
}
add_action('admin_init', 'flex_videos_settings_init');

function flex_videos_api_key_field_html() {
    $api_key = get_option('flex_videos_api_key');
    echo '<input type="text" name="flex_videos_api_key" value="' . esc_attr($api_key) . '" size="50">';
    echo '<p class="description">' . wp_kses(__('You can get a free API key from the <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>.', 'flex-videos'), array('a' => array('href' => array(), 'target' => array()))) . '</p>';
}
function flex_videos_channel_id_field_html() {
    $channel_id = get_option('flex_videos_channel_id');
    echo '<input type="text" name="flex_videos_channel_id" value="' . esc_attr($channel_id) . '" size="50">';
    echo '<p class="description">' . wp_kses(__('Find your channel ID at <a href="https://www.youtube.com/account_advanced" target="_blank">YouTube Advanced Settings</a>. It looks like UCxxxxxxxxxxxxxxxxxx.', 'flex-videos'), array('a' => array('href' => array(), 'target' => array()))) . '</p>';
}
function flex_videos_show_channel_link_field_html() {
    $show = get_option('flex_videos_show_channel_link', '1');
    echo '<input type="checkbox" name="flex_videos_show_channel_link" value="1"' . checked($show, '1', false) . '> ' . esc_html__('Show a link to the YouTube channel below the grid.', 'flex-videos');
}
function flex_videos_columns_field_html() {
    $columns = get_option('flex_videos_columns', 3);
    echo '<input type="number" name="flex_videos_columns" value="' . esc_attr($columns) . '" min="1" max="10">';
    echo '<p class="description">' . esc_html__('Number of columns to display in the grid (default: 3).', 'flex-videos') . '</p>';
}
function flex_videos_gap_field_html() {
    $gap = get_option('flex_videos_gap', 15);
    echo '<input type="number" name="flex_videos_gap" value="' . esc_attr($gap) . '" min="0" max="100"> px';
    echo '<p class="description">' . esc_html__('Space between thumbnails in pixels (default: 15).', 'flex-videos') . '</p>';
}
function flex_videos_num_videos_field_html() {
    $num = get_option('flex_videos_num_videos', 9);
    echo '<input type="number" name="flex_videos_num_videos" value="' . esc_attr($num) . '" min="1" max="50">';
    echo '<p class="description">' . esc_html__('Number of videos to show in the grid (default: 9).', 'flex-videos') . '</p>';
}
function flex_videos_custom_grid_title_field_html() {
    $val = get_option('flex_videos_custom_grid_title', '');
    echo '<input type="text" name="flex_videos_custom_grid_title" value="' . esc_attr($val) . '" size="50">';
    echo '<p class="description">' . esc_html__('Override the grid title with your own text (optional).', 'flex-videos') . '</p>';
}
function flex_videos_custom_grid_desc_field_html() {
    $val = get_option('flex_videos_custom_grid_desc', '');
    ?>
    <textarea name="flex_videos_custom_grid_desc" rows="3" cols="50"><?php echo esc_textarea($val); ?></textarea>
    <p class="description"><?php echo esc_html__('Override the grid description with your own text (optional).', 'flex-videos'); ?></p>
    <?php
}
function flex_videos_channel_link_text_field_html() {
    $val = get_option('flex_videos_channel_link_text', __('Visit Channel', 'flex-videos'));
    echo '<input type="text" name="flex_videos_channel_link_text" value="' . esc_attr($val) . '" size="30">';
    echo '<p class="description">' . esc_html__('Customize the channel link text (default: Visit Channel). You can use {channel} to insert the channel name.', 'flex-videos') . '</p>';
}
function flex_videos_show_grid_title_field_html() {
    $show = get_option('flex_videos_show_grid_title', '1');
    echo '<input type="checkbox" name="flex_videos_show_grid_title" value="1"' . checked($show, '1', false) . '> ' . esc_html__('Show the grid title above the video grid.', 'flex-videos');
}
function flex_videos_show_grid_description_field_html() {
    $show = get_option('flex_videos_show_grid_description', '1');
    echo '<input type="checkbox" name="flex_videos_show_grid_description" value="1"' . checked($show, '1', false) . '> ' . esc_html__('Show the grid description below the title.', 'flex-videos');
}
function flex_videos_button_color_field_html() {
    $color = get_option('flex_videos_button_color', '#ff8c00');
    ?>
    <input type="color" name="flex_videos_button_color" value="<?php echo esc_attr($color); ?>" />
    <p class="description"><?php echo esc_html__('Choose the background color for the "Visit Channel" button (default: orange).', 'flex-videos'); ?></p>
    <?php
}

function flex_videos_button_hover_color_field_html() {
    $color = get_option('flex_videos_button_hover_color', '#e67e00');
    ?>
    <input type="color" name="flex_videos_button_hover_color" value="<?php echo esc_attr($color); ?>" />
    <p class="description"><?php echo esc_html__('Choose the background color when hovering over the button (default: darker orange).', 'flex-videos'); ?></p>
    <?php
}

function flex_videos_button_text_color_field_html() {
    $color = get_option('flex_videos_button_text_color', '#ffffff');
    ?>
    <input type="color" name="flex_videos_button_text_color" value="<?php echo esc_attr($color); ?>" />
    <p class="description"><?php echo esc_html__('Choose the text color for the button (default: white).', 'flex-videos'); ?></p>
    <?php
}

// Add cache clearing functionality
function flex_videos_clear_cache() {
    if (isset($_POST['clear_cache']) && check_admin_referer('flex_videos_clear_cache', 'flex_videos_cache_nonce')) {
        // Clear all flex_videos transients using WordPress functions
        $transients_to_clear = array();
        
        // Get all transient keys with our prefix
        $api_key = get_option('flex_videos_api_key');
        $channel_id = get_option('flex_videos_channel_id');
        
        // Clear common cache keys
        if ($channel_id) {
            delete_transient('flex_videos_channel_info_' . $channel_id);
        }
        
        // Clear search cache (we'll use a different approach since we can't easily enumerate all hashtag combinations)
        // Instead, we'll increment a cache version to invalidate all old caches
        $cache_version = get_option('flex_videos_cache_version', 1);
        update_option('flex_videos_cache_version', $cache_version + 1);
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('YouTube cache cleared successfully!', 'flex-videos') . '</p></div>';
        });
    }
    
    if (isset($_POST['reset_plugin_settings']) && check_admin_referer('flex_videos_reset_settings', 'flex_videos_reset_nonce')) {
        // Clear all plugin options to force fresh state
        delete_option('flex_videos_show_grid_title');
        delete_option('flex_videos_show_grid_description');
        delete_option('flex_videos_custom_grid_title');
        delete_option('flex_videos_custom_grid_desc');
        
        // Re-register settings to ensure fresh state
        do_action('admin_init');
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Plugin settings reset successfully! Please reconfigure your display options.', 'flex-videos') . '</p></div>';
        });
    }
}
add_action('admin_init', 'flex_videos_clear_cache');

/**
 * AJAX handler to reset the plugin cache.
 */
function flex_videos_reset_cache() {
    check_ajax_referer('flex_videos_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(__('Error resetting cache.', 'flex-videos'));
    }

    $channel_id = get_option('flex_videos_channel_id');
    if ($channel_id) {
        delete_transient('flex_videos_channel_info_' . $channel_id);
    }

    $cache_version = get_option('flex_videos_cache_version', 1);
    update_option('flex_videos_cache_version', $cache_version + 1);

    wp_send_json_success(__('Cache has been reset successfully.', 'flex-videos'));
}
add_action('wp_ajax_flex_videos_reset_cache', 'flex_videos_reset_cache');

function flex_videos_settings_page_html() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap flex-videos-admin">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('flex_videos_settings_group');
            do_settings_sections('flex_videos_settings_group');
            submit_button(__('Save Settings', 'flex-videos'));
            ?>
        </form>
        <hr>
        <h2><?php esc_html_e('API Testing', 'flex-videos'); ?></h2>
        <p><?php esc_html_e('Test if your API key is working correctly:', 'flex-videos'); ?></p>
        <form method="post" action="">
            <?php wp_nonce_field('flex_videos_test_api', 'flex_videos_test_nonce'); ?>
            <p>
                <input type="submit" name="test_api_key" class="button button-secondary" value="<?php echo esc_attr(__('Test API Key', 'flex-videos')); ?>">
            </p>
        </form>
        <hr>
        <h2><?php esc_html_e('Cache Management & API Usage', 'flex-videos'); ?></h2>
        <p><?php esc_html_e('The plugin uses optimized caching with different durations: Channel info (12 hours), Videos (2 hours), Search results (30 minutes).', 'flex-videos'); ?></p>
        
        <?php 
        // Display API usage statistics
        $api_usage = flex_videos_get_api_usage();
        ?>
        <div style="background: #f9f9f9; padding: 15px; border-left: 4px solid #0073aa; margin: 15px 0;">
            <h4 style="margin-top: 0;"><?php esc_html_e('Current Hour API Usage', 'flex-videos'); ?></h4>
            <p>
                <strong><?php esc_html_e('API Calls:', 'flex-videos'); ?></strong> 
                <?php echo esc_html($api_usage['current_hour_calls']); ?> / <?php echo esc_html($api_usage['max_calls_per_hour']); ?> 
                (<?php echo esc_html($api_usage['quota_percentage']); ?>%)
            </p>
            <p>
                <strong><?php esc_html_e('Remaining:', 'flex-videos'); ?></strong> 
                <?php echo esc_html($api_usage['remaining_calls']); ?> calls
            </p>
            <?php if ($api_usage['quota_percentage'] > 80): ?>
                <p style="color: #d63384;">
                    <strong><?php esc_html_e('Warning:', 'flex-videos'); ?></strong>
                    <?php esc_html_e('High API usage detected. Consider clearing cache or reducing requests.', 'flex-videos'); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <form method="post" action="">
            <?php wp_nonce_field('flex_videos_clear_cache', 'flex_videos_cache_nonce'); ?>
            <p>
                <input type="submit" name="clear_cache" class="button button-secondary" value="<?php echo esc_attr(__('Clear YouTube Cache', 'flex-videos')); ?>">
            </p>
        </form>
        
        <hr>
        <h2><?php esc_html_e('Plugin Settings Reset', 'flex-videos'); ?></h2>
        <p><strong><?php esc_html_e('Warning:', 'flex-videos'); ?></strong> <?php esc_html_e("This will reset all plugin display settings to default values and force refresh the admin interface. Use this if you're experiencing display issues in the settings page.", 'flex-videos'); ?></p>
        <form method="post" action="">
            <?php wp_nonce_field('flex_videos_reset_settings', 'flex_videos_reset_nonce'); ?>
            <p>
                <input type="submit" name="reset_plugin_settings" class="button button-secondary" value="<?php echo esc_attr(__('Reset Plugin Settings', 'flex-videos')); ?>" onclick="return confirm('<?php echo esc_js(__('This will reset all your display settings. Are you sure?', 'flex-videos')); ?>');">
            </p>
        </form>
    </div>
    <?php
}

// --- SHORTCODE AND DISPLAY LOGIC ---

// New [flex_videos] shortcode for grid display
function flex_videos_grid_shortcode($atts) {
    $api_key = get_option('flex_videos_api_key');
    $channel_id = get_option('flex_videos_channel_id');
    $show_channel_link = get_option('flex_videos_show_channel_link', '1');
    $columns = intval(get_option('flex_videos_columns', 3));
    $gap = intval(get_option('flex_videos_gap', 15));
    $show_grid_title = get_option('flex_videos_show_grid_title', '1');
    $show_grid_description = get_option('flex_videos_show_grid_description', '1');
    $num_videos = intval(get_option('flex_videos_num_videos', 9));
    $custom_grid_title = trim(get_option('flex_videos_custom_grid_title', ''));
    $custom_grid_desc = trim(get_option('flex_videos_custom_grid_desc', ''));
    $channel_link_text = trim(get_option('flex_videos_channel_link_text', __('Visit Channel', 'flex-videos')));
    $attributes = shortcode_atts([
        'count' => $num_videos,
        'hashtag' => '',
        'columns' => $columns,
        'width' => '320px',
        'gap' => $gap,
    ], $atts);
    $columns = intval($attributes['columns']);
    $width = esc_attr($attributes['width']);
    $gap = intval($attributes['gap']);
    $max_to_display = intval($attributes['count']);
    $hashtag = sanitize_text_field($attributes['hashtag']);
    $cache_version = get_option('flex_videos_cache_version', 1);
    $transient_key = 'flex_videos_search_cache_' . md5($hashtag . '_v' . $cache_version);
    $cached_data = get_transient($transient_key);
    // Fetch channel info (name and description) with fallback support
    $channel_cache_key = 'flex_videos_channel_info_' . $channel_id;
    $channel_info = flex_videos_get_with_fallback(
        $channel_cache_key,
        function() use ($channel_id, $api_key) {
            if (!$api_key || !$channel_id) {
                return new WP_Error('missing_credentials', __('API key or channel ID missing.', 'flex-videos'));
            }
            
            $channel_api_url = sprintf(
                'https://www.googleapis.com/youtube/v3/channels?part=snippet&fields=items(snippet(title,description,customUrl))&id=%s&key=%s',
                $channel_id,
                $api_key
            );
            return flex_videos_api_request($channel_api_url);
        },
        FLEX_VIDEOS_CHANNEL_CACHE_DURATION
    );
    
    // Parse channel info from API response or use cached data
    if (is_array($channel_info) && !is_wp_error($channel_info)) {
        // Data is already parsed (from cache)
        if (isset($channel_info['title'])) {
            // Already processed channel info
        } else if (isset($channel_info['items'][0]['snippet'])) {
            // Raw API response, parse it
            $snippet = $channel_info['items'][0]['snippet'];
            $channel_info = [
                'title' => $snippet['title'],
                'description' => $snippet['description'],
                'customUrl' => $snippet['customUrl'] ?? '',
            ];
            // Update cache with parsed data
            set_transient($channel_cache_key, $channel_info, FLEX_VIDEOS_CHANNEL_CACHE_DURATION);
            set_transient($channel_cache_key . '_stale', $channel_info, WEEK_IN_SECONDS);
        }
    } else {
        // API failed and no fallback available
        $channel_info = [];
    }
    $channel_title = $channel_info['title'] ?? __('Latest Videos', 'flex-videos');
    $channel_description = $channel_info['description'] ?? '';
    $channel_custom_url = $channel_info['customUrl'] ?? '';
    // Fetch video data with fallback support
    $video_data = flex_videos_get_with_fallback(
        $transient_key,
        function() use ($hashtag, $channel_id, $api_key, $max_to_display) {
            if (!$api_key || !$channel_id) {
                return new WP_Error('missing_credentials', __('API key or channel ID missing.', 'flex-videos'));
            }
            
            // Calculate optimal maxResults based on actual needs plus small buffer
            $optimal_max_results = min($max_to_display + 5, 50);
            
            if (empty($hashtag)) {
                // Optimized API URL with selective fields and dynamic maxResults
                $api_url = sprintf(
                    'https://www.googleapis.com/youtube/v3/search?part=snippet&fields=items(id/videoId,snippet(title,description,thumbnails))&channelId=%s&order=date&type=video&maxResults=%d&key=%s',
                    $channel_id,
                    $optimal_max_results,
                    $api_key
                );
            } else {
                $search_query = urlencode($hashtag);
                $api_url = sprintf(
                    'https://www.googleapis.com/youtube/v3/search?part=snippet&fields=items(id/videoId,snippet(title,description,thumbnails))&channelId=%s&q=%s&order=date&type=video&maxResults=%d&key=%s',
                    $channel_id,
                    $search_query,
                    $optimal_max_results,
                    $api_key
                );
            }
            return flex_videos_api_request($api_url);
        },
        empty($hashtag) ? FLEX_VIDEOS_VIDEO_CACHE_DURATION : FLEX_VIDEOS_SEARCH_CACHE_DURATION
    );
    
    // Extract videos from response or cached data
    if (is_wp_error($video_data)) {
        if (current_user_can('manage_options')) {
            // translators: %s is the error message from the API request
            return sprintf(__('Error: API request failed - %s', 'flex-videos'), $video_data->get_error_message());
        }
        return '';
    }
    
    $videos = $video_data['items'] ?? [];
    if (empty($videos)) {
        return '<p>' . __('No videos found for this channel.', 'flex-videos') . '</p>';
    }
    $videos_to_display = array_slice($videos, 0, $max_to_display);
    $output_html = '<div class="wp-block-group flex-videos-wrapper">';
    if ($show_grid_title === '1') {
        $title = $custom_grid_title !== '' ? $custom_grid_title : $channel_title;
        $output_html .= '<h2 class="wp-block-heading">' . esc_html($title) . '</h2>';
    }
    $output_html .= '<div class="flex-videos-grid" style="--flex-videos-columns:' . $columns . '; --flex-videos-width:' . $width . '; gap:' . $gap . 'px;">';
    foreach ($videos_to_display as $video) {
        if (!isset($video['id']['videoId'])) continue;
        $video_id = $video['id']['videoId'];
        $snippet = $video['snippet'];
        $title = isset($snippet['title']) ? $snippet['title'] : '';
        $desc = isset($snippet['description']) ? $snippet['description'] : '';
        $desc_max = 90; // Increased from 50 for less truncation
        $title_max = 60;
        if (mb_strlen($desc) > $desc_max) {
            $desc = mb_substr($desc, 0, $desc_max) . '…';
        }
        if (mb_strlen($title) > $title_max) {
            $title = mb_substr($title, 0, $title_max) . '…';
        }
        // For overlay, don't truncate
        $overlay_title = isset($snippet['title']) ? esc_html($snippet['title']) : '';
        $overlay_desc = isset($snippet['description']) ? esc_html($snippet['description']) : '';
        $title = esc_html($title);
        $desc = esc_html($desc);
        // Use optimized thumbnail URLs: medium quality for grid, max quality for overlay
        $grid_thumb_url = 'https://img.youtube.com/vi/' . $video_id . '/mqdefault.jpg';
        $overlay_thumb_url = 'https://img.youtube.com/vi/' . $video_id . '/maxresdefault.jpg';
        if (!$grid_thumb_url) continue;
        $video_url = 'https://www.youtube.com/watch?v=' . esc_attr($video_id);
        $output_html .= '<div class="flex-videos-item flex-videos-item-has-overlay" tabindex="0" data-title="' . esc_attr($overlay_title) . '" data-desc="' . esc_attr($overlay_desc) . '" data-thumb="' . esc_url($overlay_thumb_url) . '" data-url="' . esc_url($video_url) . '">';
        $output_html .= '<a href="' . esc_url($video_url) . '" target="_blank" rel="noopener noreferrer" class="flex-videos-thumb-link">';
        // Use WordPress-style image attributes for external YouTube thumbnail
        $img_attrs = array(
            'src' => esc_url($grid_thumb_url),
            /* translators: %s is the video title */
            'alt' => esc_attr(sprintf(__('YouTube video thumbnail: %s', 'flex-videos'), $title)),
            'class' => 'flex-videos-thumb',
            'loading' => 'lazy',
            'decoding' => 'async'
        );
        $output_html .= '<img';
        foreach ($img_attrs as $attr => $value) {
            $output_html .= ' ' . $attr . '="' . $value . '"';
        }
        $output_html .= '>';
        $output_html .= '</a>';
        $output_html .= '</div>';
    }
    $output_html .= '</div>';
    // Overlay container (single, outside grid)
    $output_html .= '<div id="flex-videos-flyout-overlay" style="display:none;position:fixed;z-index:99999;"></div>';
    if ($show_grid_description === '1') {
        $desc = $custom_grid_desc !== '' ? $custom_grid_desc : $channel_description;
        $max_length = 200;
        if (mb_strlen($desc) > $max_length) {
            $desc = mb_substr($desc, 0, $max_length) . '…';
        }
        $output_html .= '<div class="flex-videos-grid-description" style="margin-top:10px;">' . esc_html($desc) . '</div>';
    }
    if ($show_channel_link === '1') {
        $output_html .= '<div class="flex-videos-channel-link" style="margin-top:10px;text-align:right;">';
        $link_text = str_replace('{channel}', $channel_title, $channel_link_text);
        $channel_url = $channel_custom_url ? 'https://www.youtube.com/c/' . esc_attr($channel_custom_url) : 'https://www.youtube.com/channel/' . esc_attr($channel_id);
        $output_html .= '<a href="' . esc_url($channel_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($link_text) . '</a>';
        $output_html .= '</div>';
    }
    $output_html .= '</div>'; // Close wp-block-group wrapper
    return $output_html;
}
add_shortcode('flex_videos', 'flex_videos_grid_shortcode');

// [flex_video] shortcode for single responsive video
function flex_video_single_shortcode($atts) {
    $attributes = shortcode_atts([
        'url' => '',
    ], $atts);
    $url = esc_url($attributes['url']);
    if (empty($url)) return '';
    $api_key = get_option('flex_videos_api_key');
    $video_title = '';
    $video_desc = '';
    $embed_url = '';
    $video_id = '';
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
        if (preg_match('/(?:v=|youtu.be\/)([\w-]+)/', $url, $matches)) {
            $video_id = $matches[1];
            $embed_url = 'https://www.youtube.com/embed/' . esc_attr($video_id);
            // Fetch title/desc from YouTube API
            if ($api_key && $video_id) {
                $api_url = 'https://www.googleapis.com/youtube/v3/videos?part=snippet&id=' . $video_id . '&key=' . $api_key;
                $response = wp_remote_get($api_url);
                if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                    $data = json_decode(wp_remote_retrieve_body($response), true);
                    if (!empty($data['items'][0]['snippet'])) {
                        $video_title = esc_html($data['items'][0]['snippet']['title']);
                        $video_desc = esc_html($data['items'][0]['snippet']['description']);
                    }
                }
            }
        } else {
            return __('Invalid YouTube URL.', 'flex-videos');
        }
    } else {
        return __('Unsupported video URL.', 'flex-videos');
    }
    $title = $video_title ?: '';
    $desc = $video_desc ?: '';
    $output = '<div class="flex-video-single">';
    $output .= '<iframe src="' . $embed_url . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>';
    if ($title) {
        $output .= '<div class="flex-video-title" style="margin-top:1em;">' . $title . '</div>';
    }
    if ($desc) {
        $output .= '<div class="flex-video-description">' . $desc . '</div>';
    }
    $output .= '</div>';
    return $output;
}
add_shortcode('flex_video', 'flex_video_single_shortcode');

// Test API key function
function flex_videos_test_api_key() {
    if (isset($_POST['test_api_key']) && check_admin_referer('flex_videos_test_api', 'flex_videos_test_nonce')) {
        $api_key = get_option('flex_videos_api_key');
        $channel_id = get_option('flex_videos_channel_id');
        if (empty($api_key) || empty($channel_id)) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Please set your API key and channel ID first!', 'flex-videos') . '</p></div>';
            });
            return;
        }
        $test_url = sprintf(
            'https://www.googleapis.com/youtube/v3/channels?part=snippet&fields=items(snippet(title))&id=%s&key=%s',
            $channel_id,
            $api_key
        );
        $response = flex_videos_api_request($test_url);
        if (is_wp_error($response)) {
            add_action('admin_notices', function() use ($response) {
                echo '<div class="notice notice-error is-dismissible"><p>' . sprintf(esc_html__('API Error: %s', 'flex-videos'), esc_html($response->get_error_message())) . '</p></div>';
            });
            return;
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code === 200) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($data['items'])) {
                $channel_title = $data['items'][0]['snippet']['title'];
                add_action('admin_notices', function() use ($channel_title) {
                    // translators: %s is the channel name
                    echo '<div class="notice notice-success is-dismissible"><p>' . sprintf(esc_html__('API key is working! Found channel: %s', 'flex-videos'), esc_html($channel_title)) . '</p></div>';
                });
            } else {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__('Channel not found with this ID.', 'flex-videos') . '</p></div>';
                });
            }
        } else {
            add_action('admin_notices', function() use ($response_code) {
                // translators: %s is the API error response code
                echo '<div class="notice notice-error is-dismissible"><p>' . sprintf(esc_html__('API Error: %s', 'flex-videos'), esc_html($response_code)) . '</p></div>';
            });
        }
    }
}
add_action('admin_init', 'flex_videos_test_api_key');

// Enqueue Flex Videos CSS and JS
function flex_videos_enqueue_assets() {
    wp_enqueue_style('flex-videos-css', plugins_url('assets/css/flex-videos.min.css', __FILE__), [], '1.0.2');
    wp_enqueue_script('flex-videos-flyout', plugins_url('assets/js/flex-videos-flyout.min.js', __FILE__), [], '1.0.2', true);
    
    // Add custom button colors
    $button_color = get_option('flex_videos_button_color', '#ff8c00');
    $button_hover_color = get_option('flex_videos_button_hover_color', '#e67c00');
    $button_text_color = get_option('flex_videos_button_text_color', '#ffffff');
    
    $custom_css = "
    :root {
        --flex-videos-button-bg: {$button_color};
        --flex-videos-button-bg-hover: {$button_hover_color};
        --flex-videos-button-color: {$button_text_color};
        --flex-videos-button-shadow: " . $button_color . "50;
        --flex-videos-button-shadow-hover: " . $button_hover_color . "66;
    }";
    
    wp_add_inline_style('flex-videos-css', $custom_css);
}
add_action('wp_enqueue_scripts', 'flex_videos_enqueue_assets');
