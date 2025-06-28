<?php
/**
 * Plugin Name:       Flex Videos
 * Plugin URI:        https://github.com/rayvillalobos/flex-videos
 * Description:       A professional WordPress plugin for displaying flexible, responsive video embeds with YouTube API integration.
 * Version:           1.0.1
 * Author:            Ray Villalobos
 * Author URI:        https://rayvillalobos.com/
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

function itsplaitime_add_admin_menu() {
    add_options_page('YouTube Videos Settings', 'YouTube Videos', 'manage_options', 'itsplaitime_youtube_videos', 'itsplaitime_settings_page_html');
}
add_action('admin_menu', 'itsplaitime_add_admin_menu');

/**
 * **FIX**: Restored the add_settings_section and add_settings_field calls
 * to make the API key field visible on the settings page.
 */
function itsplaitime_settings_init() {
    register_setting('itsplaitime_youtube_videos_group', 'itsplaitime_yt_api_key');
    register_setting('itsplaitime_youtube_videos_group', 'itsplaitime_yt_channel_id');

    add_settings_section(
        'itsplaitime_settings_section',
        'API Configuration',
        null, // No callback function needed for the section description
        'itsplaitime_youtube_videos_group'
    );

    add_settings_field(
        'itsplaitime_yt_api_key',
        'YouTube Data API Key',
        'itsplaitime_api_key_field_html', // The function that renders the HTML for the field
        'itsplaitime_youtube_videos_group',
        'itsplaitime_settings_section'
    );
    add_settings_field(
        'itsplaitime_yt_channel_id',
        'YouTube Channel ID',
        'itsplaitime_channel_id_field_html',
        'itsplaitime_youtube_videos_group',
        'itsplaitime_settings_section'
    );
}
add_action('admin_init', 'itsplaitime_settings_init');

function itsplaitime_api_key_field_html() {
    $api_key = get_option('itsplaitime_yt_api_key');
    echo '<input type="text" name="itsplaitime_yt_api_key" value="' . esc_attr($api_key) . '" size="50">';
    echo '<p class="description">You can get a free API key from the <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a>.</p>';
}

function itsplaitime_channel_id_field_html() {
    $channel_id = get_option('itsplaitime_yt_channel_id');
    echo '<input type="text" name="itsplaitime_yt_channel_id" value="' . esc_attr($channel_id) . '" size="50">';
    echo '<p class="description">Find your channel ID at <a href="https://www.youtube.com/account_advanced" target="_blank">YouTube Advanced Settings</a>. It looks like UCxxxxxxxxxxxxxxxxxx.</p>';
}

// Add cache clearing functionality
function itsplaitime_clear_cache() {
    if (isset($_POST['clear_cache']) && check_admin_referer('itsplaitime_clear_cache', 'itsplaitime_cache_nonce')) {
        // Clear all transients for this plugin
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_itsplaitime_search_cache_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_itsplaitime_search_cache_%'");
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>Cache cleared successfully!</p></div>';
        });
    }
}
add_action('admin_init', 'itsplaitime_clear_cache');

function itsplaitime_settings_page_html() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p>This plugin uses the YouTube Search API to find the latest videos from your channel.</p>
        <form action="options.php" method="post">
            <?php
            // These functions will now correctly render the section and field we added above.
            settings_fields('itsplaitime_youtube_videos_group');
            do_settings_sections('itsplaitime_youtube_videos_group');
            submit_button('Save Settings');
            ?>
        </form>
        
        <hr>
        <h2>API Testing</h2>
        <p>Test if your API key is working correctly:</p>
        <form method="post" action="">
            <?php wp_nonce_field('itsplaitime_test_api', 'itsplaitime_test_nonce'); ?>
            <p>
                <input type="submit" name="test_api_key" class="button button-secondary" value="Test API Key">
            </p>
        </form>
        
         <hr>
        <h2>Cache Management</h2>
        <p>The plugin caches results for 1 hour to improve performance. You can manually clear this by deactivating and reactivating the plugin.</p>
        <form method="post" action="">
            <?php wp_nonce_field('itsplaitime_clear_cache', 'itsplaitime_cache_nonce'); ?>
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
    $api_key = get_option('itsplaitime_yt_api_key');
    $channel_id = get_option('itsplaitime_yt_channel_id');
    if (empty($api_key) || empty($channel_id)) {
        if (current_user_can('manage_options')) return 'Error: YouTube API Key and Channel ID must be set in settings.';
        return '';
    }
    $attributes = shortcode_atts([
        'count' => 6,
        'hashtag' => '',
        'columns' => 3,
        'width' => '320px',
    ], $atts);
    $max_to_display = intval($attributes['count']);
    $hashtag = sanitize_text_field($attributes['hashtag']);
    $columns = intval($attributes['columns']);
    $width = esc_attr($attributes['width']);
    $transient_key = 'itsplaitime_search_cache_' . md5($hashtag);
    $cached_data = get_transient($transient_key);
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
        if (!empty($hashtag)) {
            return '<p>No videos found with the hashtag: ' . esc_html($hashtag) . '</p>';
        }
        return '<p>No videos found for this channel.</p>';
    }
    $videos_to_display = array_slice($videos, 0, $max_to_display);
    $output_html = '<div class="flex-videos-grid" style="--flex-videos-columns:' . $columns . '; --flex-videos-width:' . $width . ';">';
    foreach ($videos_to_display as $video) {
        $video_id = $video['id']['videoId'];
        $video_title = esc_attr($video['snippet']['title']);
        $embed_url = 'https://www.youtube.com/embed/' . esc_attr($video_id);
        $output_html .= '<div class="flex-videos-item"><iframe src="' . $embed_url . '" title="' . $video_title . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
    }
    $output_html .= '</div>';
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
    // Basic YouTube/Vimeo embed detection
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
        // Extract YouTube video ID
        if (preg_match('/(?:v=|youtu.be\/)([\w-]+)/', $url, $matches)) {
            $video_id = $matches[1];
            $embed_url = 'https://www.youtube.com/embed/' . esc_attr($video_id);
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
    return '<div class="flex-video-single"><iframe src="' . $embed_url . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
}
add_shortcode('flex_video', 'flex_video_single_shortcode');

// Test API key function
function itsplaitime_test_api_key() {
    if (isset($_POST['test_api_key']) && check_admin_referer('itsplaitime_test_api', 'itsplaitime_test_nonce')) {
        $api_key = get_option('itsplaitime_yt_api_key');
        $channel_id = get_option('itsplaitime_yt_channel_id');
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
add_action('admin_init', 'itsplaitime_test_api_key');
