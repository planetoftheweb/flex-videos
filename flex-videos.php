<?php
/**
 * Plugin Name:       Flex Videos
 * Plugin URI:        https://github.com/rayvillalobos/flex-videos
 * Description:       A professional WordPress plugin for displaying flexible, responsive video embeds with YouTube API integration.
 * Version:           1.0.1
 * Author:            Ray Villalobos
 * Author URI:        https://itsplaitime.com/
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       flex-videos
 * Requires at least: 5.0
 * Tested up to:      6.5
 * Requires PHP:      7.4
 * Network:           false
 */

// Block direct access to the file.
if (!defined('ABSPATH')) {
    exit;
}

// --- PLUGIN SETTINGS & CACHE HANDLING ---

function flex_videos_add_admin_menu() {
    add_options_page('Flex Videos Settings', 'Flex Videos', 'manage_options', 'flex_videos_settings', 'flex_videos_settings_page_html');
}
add_action('admin_menu', 'flex_videos_add_admin_menu');

function flex_videos_settings_init() {
    register_setting('flex_videos_settings_group', 'flex_videos_api_key');
    register_setting('flex_videos_settings_group', 'flex_videos_channel_id');
    register_setting('flex_videos_settings_group', 'flex_videos_show_channel_link');
    register_setting('flex_videos_settings_group', 'flex_videos_rows');
    register_setting('flex_videos_settings_group', 'flex_videos_columns');
    register_setting('flex_videos_settings_group', 'flex_videos_gap');
    register_setting('flex_videos_settings_group', 'flex_videos_show_grid_title');
    register_setting('flex_videos_settings_group', 'flex_videos_show_grid_description');

    add_settings_section(
        'flex_videos_settings_section',
        'API & Display Configuration',
        null,
        'flex_videos_settings_group'
    );

    add_settings_field(
        'flex_videos_api_key',
        'YouTube Data API Key',
        'flex_videos_api_key_field_html',
        'flex_videos_settings_group',
        'flex_videos_settings_section'
    );
    add_settings_field(
        'flex_videos_channel_id',
        'YouTube Channel ID',
        'flex_videos_channel_id_field_html',
        'flex_videos_settings_group',
        'flex_videos_settings_section'
    );
    add_settings_field(
        'flex_videos_show_channel_link',
        'Show Channel Link',
        'flex_videos_show_channel_link_field_html',
        'flex_videos_settings_group',
        'flex_videos_settings_section'
    );
    add_settings_field(
        'flex_videos_rows',
        'Number of Rows (Grid)',
        'flex_videos_rows_field_html',
        'flex_videos_settings_group',
        'flex_videos_settings_section'
    );
    add_settings_field(
        'flex_videos_columns',
        'Number of Columns (Grid)',
        'flex_videos_columns_field_html',
        'flex_videos_settings_group',
        'flex_videos_settings_section'
    );
    add_settings_field(
        'flex_videos_gap',
        'Gap Between Thumbnails (px)',
        'flex_videos_gap_field_html',
        'flex_videos_settings_group',
        'flex_videos_settings_section'
    );
    add_settings_field(
        'flex_videos_show_grid_title',
        'Show Grid Title',
        'flex_videos_show_grid_title_field_html',
        'flex_videos_settings_group',
        'flex_videos_settings_section'
    );
    add_settings_field(
        'flex_videos_show_grid_description',
        'Show Grid Description',
        'flex_videos_show_grid_description_field_html',
        'flex_videos_settings_group',
        'flex_videos_settings_section'
    );
}
add_action('admin_init', 'flex_videos_settings_init');

function flex_videos_api_key_field_html() {
    $api_key = get_option('flex_videos_api_key');
    echo '<input type="text" name="flex_videos_api_key" value="' . esc_attr($api_key) . '" size="50">';
    echo '<p class="description">You can get a free API key from the <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>.</p>';
}
function flex_videos_channel_id_field_html() {
    $channel_id = get_option('flex_videos_channel_id');
    echo '<input type="text" name="flex_videos_channel_id" value="' . esc_attr($channel_id) . '" size="50">';
    echo '<p class="description">Find your channel ID at <a href="https://www.youtube.com/account_advanced" target="_blank">YouTube Advanced Settings</a>. It looks like UCxxxxxxxxxxxxxxxxxx.</p>';
}
function flex_videos_show_channel_link_field_html() {
    $show = get_option('flex_videos_show_channel_link', '1');
    echo '<input type="checkbox" name="flex_videos_show_channel_link" value="1"' . checked($show, '1', false) . '> Show a link to the YouTube channel below the grid.';
}
function flex_videos_rows_field_html() {
    $rows = get_option('flex_videos_rows', 3);
    echo '<input type="number" name="flex_videos_rows" value="' . esc_attr($rows) . '" min="1" max="10">';
    echo '<p class="description">Number of rows to display in the grid (default: 3).</p>';
}
function flex_videos_columns_field_html() {
    $columns = get_option('flex_videos_columns', 3);
    echo '<input type="number" name="flex_videos_columns" value="' . esc_attr($columns) . '" min="1" max="10">';
    echo '<p class="description">Number of columns to display in the grid (default: 3).</p>';
}
function flex_videos_gap_field_html() {
    $gap = get_option('flex_videos_gap', 10);
    echo '<input type="number" name="flex_videos_gap" value="' . esc_attr($gap) . '" min="0" max="100"> px';
    echo '<p class="description">Space between thumbnails in pixels (default: 10).</p>';
}
function flex_videos_show_grid_title_field_html() {
    $show = get_option('flex_videos_show_grid_title', '1');
    echo '<input type="checkbox" name="flex_videos_show_grid_title" value="1"' . checked($show, '1', false) . '> Show the grid title above the thumbnails.';
}
function flex_videos_show_grid_description_field_html() {
    $show = get_option('flex_videos_show_grid_description', '1');
    echo '<input type="checkbox" name="flex_videos_show_grid_description" value="1"' . checked($show, '1', false) . '> Show the grid description below the thumbnails.';
}

// Add cache clearing functionality
function flex_videos_clear_cache() {
    if (isset($_POST['clear_cache']) && check_admin_referer('flex_videos_clear_cache', 'flex_videos_cache_nonce')) {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_flex_videos_search_cache_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_flex_videos_search_cache_%'");
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Cache cleared successfully!</p></div>';
        });
    }
}
add_action('admin_init', 'flex_videos_clear_cache');

function flex_videos_settings_page_html() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p>This plugin uses the YouTube Data API to find and display the latest videos from your channel.</p>
        <form action="options.php" method="post">
            <?php
            settings_fields('flex_videos_settings_group');
            do_settings_sections('flex_videos_settings_group');
            submit_button('Save Settings');
            ?>
        </form>
        <hr>
        <h2>API Testing</h2>
        <p>Test if your API key is working correctly:</p>
        <form method="post" action="">
            <?php wp_nonce_field('flex_videos_test_api', 'flex_videos_test_nonce'); ?>
            <p>
                <input type="submit" name="test_api_key" class="button button-secondary" value="Test API Key">
            </p>
        </form>
        <hr>
        <h2>Cache Management</h2>
        <p>The plugin caches results for 1 hour to improve performance. You can manually clear this by deactivating and reactivating the plugin or using the button below.</p>
        <form method="post" action="">
            <?php wp_nonce_field('flex_videos_clear_cache', 'flex_videos_cache_nonce'); ?>
            <p>
                <input type="submit" name="clear_cache" class="button button-primary" value="Clear Cache">
            </p>
        </form>
    </div>
    <?php
}

// --- SHORTCODE AND DISPLAY LOGIC ---

// Enqueue Flex Videos CSS
function flex_videos_enqueue_css() {
    $css = '
    .flex-videos-grid {
      display: grid;
      grid-template-columns: repeat(var(--flex-videos-columns, 3), 1fr);
      gap: 20px;
      padding: 20px 0;
    }
    .flex-videos-item {
      aspect-ratio: 16/9;
      max-width: var(--flex-videos-width, 320px);
      margin: 0 auto;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,.1);
      background: #000;
      transition: transform .3s, box-shadow .3s;
    }
    .flex-videos-item:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0,0,0,.15);
    }
    .flex-videos-item iframe {
      width: 100%;
      height: 100%;
      border: 0;
      display: block;
    }
    ';
    echo '<style id="flex-videos-css">' . $css . '</style>';
}
add_action('wp_head', 'flex_videos_enqueue_css');

// New [flex_videos] shortcode for grid display
function flex_videos_grid_shortcode($atts) {
    $api_key = get_option('flex_videos_api_key');
    $channel_id = get_option('flex_videos_channel_id');
    $show_channel_link = get_option('flex_videos_show_channel_link', '1');
    $rows = intval(get_option('flex_videos_rows', 3));
    $columns = intval(get_option('flex_videos_columns', 3));
    $gap = intval(get_option('flex_videos_gap', 10));
    $show_grid_title = get_option('flex_videos_show_grid_title', '1');
    $show_grid_description = get_option('flex_videos_show_grid_description', '1');
    $attributes = shortcode_atts([
        'count' => 12,
        'hashtag' => '',
        'columns' => $columns,
        'width' => '320px',
    ], $atts);
    $columns = intval($attributes['columns']);
    $width = esc_attr($attributes['width']);
    $max_to_display = $columns * $rows;
    $hashtag = sanitize_text_field($attributes['hashtag']);
    $transient_key = 'flex_videos_search_cache_' . md5($hashtag);
    $cached_data = get_transient($transient_key);
    // Fetch channel info (name and description)
    $channel_info = get_transient('flex_videos_channel_info_' . $channel_id);
    if ($channel_info === false && $api_key && $channel_id) {
        $channel_api_url = sprintf(
            'https://www.googleapis.com/youtube/v3/channels?part=snippet&id=%s&key=%s',
            $channel_id,
            $api_key
        );
        $channel_response = wp_remote_get($channel_api_url);
        if (!is_wp_error($channel_response) && wp_remote_retrieve_response_code($channel_response) === 200) {
            $channel_data = json_decode(wp_remote_retrieve_body($channel_response), true);
            if (!empty($channel_data['items'][0]['snippet'])) {
                $channel_info = [
                    'title' => $channel_data['items'][0]['snippet']['title'],
                    'description' => $channel_data['items'][0]['snippet']['description'],
                ];
                set_transient('flex_videos_channel_info_' . $channel_id, $channel_info, HOUR_IN_SECONDS);
            }
        }
    }
    $channel_title = $channel_info['title'] ?? 'Latest Videos';
    $channel_description = $channel_info['description'] ?? '';
    if (false === $cached_data) {
        if (empty($hashtag)) {
            $api_url = sprintf(
                'https://www.googleapis.com/youtube/v3/search?part=snippet&channelId=%s&order=date&type=video&maxResults=50&key=%s',
                $channel_id,
                $api_key
            );
        } else {
            $search_query = urlencode($hashtag);
            $api_url = sprintf(
                'https://www.googleapis.com/youtube/v3/search?part=snippet&channelId=%s&q=%s&order=date&type=video&maxResults=50&key=%s',
                $channel_id,
                $search_query,
                $api_key
            );
        }
        $response = wp_remote_get($api_url);
        if (is_wp_error($response)) {
            if (current_user_can('manage_options')) {
                return 'Error: API request failed - ' . $response->get_error_message();
            }
            return '';
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            if (current_user_can('manage_options')) {
                $response_body = wp_remote_retrieve_body($response);
                $error_data = json_decode($response_body, true);
                $error_message = $error_data['error']['message'] ?? 'Unknown API error';
                return 'Error: YouTube API returned ' . $response_code . ' - ' . $error_message;
            }
            return '';
        }
        $api_data = json_decode(wp_remote_retrieve_body($response), true);
        set_transient($transient_key, $api_data, HOUR_IN_SECONDS);
        $videos = $api_data['items'] ?? [];
    } else {
        $videos = $cached_data['items'] ?? [];
    }
    if (empty($videos)) {
        return '<p>No videos found for this channel.</p>';
    }
    $videos_to_display = array_slice($videos, 0, $max_to_display);
    $output_html = '';
    if ($show_grid_title === '1') {
        $output_html .= '<h2 class="wp-block-heading">' . esc_html($channel_title) . '</h2>';
    }
    $output_html .= '<div class="flex-videos-grid" style="--flex-videos-columns:' . $columns . '; --flex-videos-width:' . $width . '; gap:' . $gap . 'px;">';
    foreach ($videos_to_display as $video) {
        if (!isset($video['id']['videoId'])) continue;
        $video_id = $video['id']['videoId'];
        // Use the best available thumbnail
        $thumbs = $video['snippet']['thumbnails'];
        $thumb_url = isset($thumbs['medium']['url']) ? esc_url($thumbs['medium']['url']) : (isset($thumbs['default']['url']) ? esc_url($thumbs['default']['url']) : '');
        if (!$thumb_url) continue;
        $video_url = 'https://www.youtube.com/watch?v=' . esc_attr($video_id);
        $output_html .= '<div class="flex-videos-item">';
        $output_html .= '<a href="' . $video_url . '" target="_blank" rel="noopener noreferrer">';
        $output_html .= '<img src="' . $thumb_url . '" alt="YouTube Video Thumbnail" style="width:100%;display:block;border-radius:8px;">';
        $output_html .= '</a>';
        $output_html .= '</div>';
    }
    $output_html .= '</div>';
    if ($show_grid_description === '1' && $channel_description) {
        $output_html .= '<div class="flex-videos-grid-description" style="margin-top:10px;">' . esc_html($channel_description) . '</div>';
    }
    if ($show_channel_link === '1') {
        $output_html .= '<div class="flex-videos-channel-link" style="margin-top:10px;text-align:right;">';
        $output_html .= '<a href="https://www.youtube.com/channel/' . esc_attr($channel_id) . '" target="_blank" rel="noopener noreferrer">Visit YouTube Channel</a>';
        $output_html .= '</div>';
    }
    return $output_html;
}
add_shortcode('flex_videos', 'flex_videos_grid_shortcode');

// Responsive CSS for single video embeds
function flex_video_single_css() {
    $css = '
    .flex-video-single {
      position: relative;
      width: 100%;
      max-width: 800px;
      margin: 0 auto 2em auto;
      aspect-ratio: 16/9;
      background: #000;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,.1);
    }
    .flex-video-single iframe {
      position: absolute;
      top: 0; left: 0;
      width: 100%;
      height: 100%;
      border: 0;
      display: block;
    }
    ';
    echo '<style id="flex-video-single-css">' . $css . '</style>';
}
add_action('wp_head', 'flex_video_single_css');

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
            return 'Invalid YouTube URL.';
        }
    } elseif (strpos($url, 'vimeo.com') !== false) {
        if (preg_match('/vimeo.com\/(\d+)/', $url, $matches)) {
            $video_id = $matches[1];
            $embed_url = 'https://player.vimeo.com/video/' . esc_attr($video_id);
        } else {
            return 'Invalid Vimeo URL.';
        }
    } else {
        return 'Unsupported video URL.';
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
                echo '<div class="notice notice-error is-dismissible"><p>Please set your API key and channel ID first!</p></div>';
            });
            return;
        }
        $test_url = sprintf(
            'https://www.googleapis.com/youtube/v3/channels?part=snippet&id=%s&key=%s',
            $channel_id,
            $api_key
        );
        $response = wp_remote_get($test_url);
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code === 200) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (!empty($data['items'])) {
                $channel_title = $data['items'][0]['snippet']['title'];
                add_action('admin_notices', function() use ($channel_title) {
                    echo '<div class="notice notice-success is-dismissible"><p>API key is working! Found channel: ' . esc_html($channel_title) . '</p></div>';
                });
            } else {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-error is-dismissible"><p>Channel not found with this ID.</p></div>';
                });
            }
        } else {
            add_action('admin_notices', function() use ($response_code) {
                echo '<div class="notice notice-error is-dismissible"><p>API Error: ' . $response_code . '</p></div>';
            });
        }
    }
}
add_action('admin_init', 'flex_videos_test_api_key');
