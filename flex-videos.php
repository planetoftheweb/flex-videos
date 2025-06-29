<?php
/**
 * Plugin Name:       Flex Videos
 * Plugin URI:        https://github.com/planetoftheweb/flex-videos
 * Description:       A WordPress plugin for displaying flexible, responsive video embeds with YouTube API integration.
 * Version:           1.0.0
 * Author:            Ray Villalobos
 * Author URI:        https://itsplaitime.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
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
    // API Section
    add_settings_section(
        'flex_videos_api_section',
        'YouTube API Configuration',
        null,
        'flex_videos_settings_group'
    );
    register_setting('flex_videos_settings_group', 'flex_videos_api_key');
    register_setting('flex_videos_settings_group', 'flex_videos_channel_id');
    add_settings_field(
        'flex_videos_api_key',
        'YouTube Data API Key',
        'flex_videos_api_key_field_html',
        'flex_videos_settings_group',
        'flex_videos_api_section'
    );
    add_settings_field(
        'flex_videos_channel_id',
        'YouTube Channel ID',
        'flex_videos_channel_id_field_html',
        'flex_videos_settings_group',
        'flex_videos_api_section'
    );

    // Display Section
    add_settings_section(
        'flex_videos_display_section',
        'Grid Display Options',
        null,
        'flex_videos_settings_group'
    );
    register_setting('flex_videos_settings_group', 'flex_videos_columns');
    register_setting('flex_videos_settings_group', 'flex_videos_gap');
    register_setting('flex_videos_settings_group', 'flex_videos_show_grid_title');
    register_setting('flex_videos_settings_group', 'flex_videos_show_grid_description');
    register_setting('flex_videos_settings_group', 'flex_videos_num_videos');
    register_setting('flex_videos_settings_group', 'flex_videos_custom_grid_title');
    register_setting('flex_videos_settings_group', 'flex_videos_custom_grid_desc');
    add_settings_field(
        'flex_videos_columns',
        'Number of Columns (Grid)',
        'flex_videos_columns_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );
    add_settings_field(
        'flex_videos_gap',
        'Gap Between Thumbnails (px)',
        'flex_videos_gap_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );
    add_settings_field(
        'flex_videos_num_videos',
        'Number of Videos to Show',
        'flex_videos_num_videos_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );
    add_settings_field(
        'flex_videos_show_grid_title',
        'Show Grid Title',
        'flex_videos_show_grid_title_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );
    add_settings_field(
        'flex_videos_custom_grid_title',
        'Custom Grid Title',
        'flex_videos_custom_grid_title_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );
    add_settings_field(
        'flex_videos_show_grid_description',
        'Show Grid Description',
        'flex_videos_show_grid_description_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );
    add_settings_field(
        'flex_videos_custom_grid_desc',
        'Custom Grid Description',
        'flex_videos_custom_grid_desc_field_html',
        'flex_videos_settings_group',
        'flex_videos_display_section'
    );

    // Channel Link Section
    add_settings_section(
        'flex_videos_channel_section',
        'Channel Link Options',
        null,
        'flex_videos_settings_group'
    );
    register_setting('flex_videos_settings_group', 'flex_videos_show_channel_link');
    register_setting('flex_videos_settings_group', 'flex_videos_channel_link_text');
    register_setting('flex_videos_settings_group', 'flex_videos_button_color');
    register_setting('flex_videos_settings_group', 'flex_videos_button_hover_color');
    register_setting('flex_videos_settings_group', 'flex_videos_button_text_color');
    add_settings_field(
        'flex_videos_show_channel_link',
        'Show Channel Link',
        'flex_videos_show_channel_link_field_html',
        'flex_videos_settings_group',
        'flex_videos_channel_section'
    );
    add_settings_field(
        'flex_videos_channel_link_text',
        'Channel Link Text',
        'flex_videos_channel_link_text_field_html',
        'flex_videos_settings_group',
        'flex_videos_channel_section'
    );
    add_settings_field(
        'flex_videos_button_color',
        'Button Background Color',
        'flex_videos_button_color_field_html',
        'flex_videos_settings_group',
        'flex_videos_channel_section'
    );
    add_settings_field(
        'flex_videos_button_hover_color',
        'Button Hover Color',
        'flex_videos_button_hover_color_field_html',
        'flex_videos_settings_group',
        'flex_videos_channel_section'
    );
    add_settings_field(
        'flex_videos_button_text_color',
        'Button Text Color',
        'flex_videos_button_text_color_field_html',
        'flex_videos_settings_group',
        'flex_videos_channel_section'
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
function flex_videos_columns_field_html() {
    $columns = get_option('flex_videos_columns', 3);
    echo '<input type="number" name="flex_videos_columns" value="' . esc_attr($columns) . '" min="1" max="10">';
    echo '<p class="description">Number of columns to display in the grid (default: 3).</p>';
}
function flex_videos_gap_field_html() {
    $gap = get_option('flex_videos_gap', 15);
    echo '<input type="number" name="flex_videos_gap" value="' . esc_attr($gap) . '" min="0" max="100"> px';
    echo '<p class="description">Space between thumbnails in pixels (default: 15).</p>';
}
function flex_videos_num_videos_field_html() {
    $num = get_option('flex_videos_num_videos', 9);
    echo '<input type="number" name="flex_videos_num_videos" value="' . esc_attr($num) . '" min="1" max="50">';
    echo '<p class="description">Number of videos to show in the grid (default: 9).</p>';
}
function flex_videos_custom_grid_title_field_html() {
    $val = get_option('flex_videos_custom_grid_title', '');
    echo '<input type="text" name="flex_videos_custom_grid_title" value="' . esc_attr($val) . '" size="50">';
    echo '<p class="description">Override the grid title with your own text (optional).</p>';
}
function flex_videos_custom_grid_desc_field_html() {
    $val = get_option('flex_videos_custom_grid_desc', '');
    ?>
    <textarea name="flex_videos_custom_grid_desc" rows="3" cols="50"><?php echo esc_textarea($val); ?></textarea>
    <p class="description">Override the grid description with your own text (optional).</p>
    <?php
}
function flex_videos_channel_link_text_field_html() {
    $val = get_option('flex_videos_channel_link_text', 'Visit Channel');
    echo '<input type="text" name="flex_videos_channel_link_text" value="' . esc_attr($val) . '" size="30">';
    echo '<p class="description">Customize the channel link text (default: Visit Channel). You can use {channel} to insert the channel name.</p>';
}
function flex_videos_show_grid_title_field_html() {
    $show = get_option('flex_videos_show_grid_title', '1');
    echo '<input type="checkbox" name="flex_videos_show_grid_title" value="1"' . checked($show, '1', false) . '> Show the grid title above the video grid.';
}
function flex_videos_show_grid_description_field_html() {
    $show = get_option('flex_videos_show_grid_description', '1');
    echo '<input type="checkbox" name="flex_videos_show_grid_description" value="1"' . checked($show, '1', false) . '> Show the grid description below the title.';
}
function flex_videos_button_color_field_html() {
    $color = get_option('flex_videos_button_color', '#ff8c00');
    ?>
    <input type="color" name="flex_videos_button_color" value="<?php echo esc_attr($color); ?>" />
    <p class="description">Choose the background color for the "Visit Channel" button (default: orange).</p>
    <?php
}

function flex_videos_button_hover_color_field_html() {
    $color = get_option('flex_videos_button_hover_color', '#e67c00');
    ?>
    <input type="color" name="flex_videos_button_hover_color" value="<?php echo esc_attr($color); ?>" />
    <p class="description">Choose the background color when hovering over the button (default: darker orange).</p>
    <?php
}

function flex_videos_button_text_color_field_html() {
    $color = get_option('flex_videos_button_text_color', '#ffffff');
    ?>
    <input type="color" name="flex_videos_button_text_color" value="<?php echo esc_attr($color); ?>" />
    <p class="description">Choose the text color for the button (default: white).</p>
    <?php
}

// Add cache clearing functionality
function flex_videos_clear_cache() {
    if (isset($_POST['clear_cache']) && check_admin_referer('flex_videos_clear_cache', 'flex_videos_cache_nonce')) {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_flex_videos_search_cache_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_flex_videos_search_cache_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_flex_videos_channel_info_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_flex_videos_channel_info_%'");
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>YouTube cache cleared successfully!</p></div>';
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
            echo '<div class="notice notice-success is-dismissible"><p>Plugin settings reset successfully! Please reconfigure your display options.</p></div>';
        });
    }
}
add_action('admin_init', 'flex_videos_clear_cache');

function flex_videos_settings_page_html() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
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
        <p>The plugin caches YouTube results for 1 hour to improve performance. You can manually clear this cache using the button below.</p>
        <form method="post" action="">
            <?php wp_nonce_field('flex_videos_clear_cache', 'flex_videos_cache_nonce'); ?>
            <p>
                <input type="submit" name="clear_cache" class="button button-secondary" value="Clear YouTube Cache">
            </p>
        </form>
        
        <hr>
        <h2>Plugin Settings Reset</h2>
        <p><strong>Warning:</strong> This will reset all plugin display settings to default values and force refresh the admin interface. Use this if you're experiencing display issues in the settings page.</p>
        <form method="post" action="">
            <?php wp_nonce_field('flex_videos_reset_settings', 'flex_videos_reset_nonce'); ?>
            <p>
                <input type="submit" name="reset_plugin_settings" class="button button-secondary" value="Reset Plugin Settings" onclick="return confirm('This will reset all your display settings. Are you sure?');">
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
    $channel_link_text = trim(get_option('flex_videos_channel_link_text', 'Visit Channel'));
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
                    'customUrl' => $channel_data['items'][0]['snippet']['customUrl'] ?? '',
                ];
                set_transient('flex_videos_channel_info_' . $channel_id, $channel_info, HOUR_IN_SECONDS);
            }
        }
    }
    $channel_title = $channel_info['title'] ?? 'Latest Videos';
    $channel_description = $channel_info['description'] ?? '';
    $channel_custom_url = $channel_info['customUrl'] ?? '';
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
        $thumbs = $snippet['thumbnails'];
        $thumb_url = isset($thumbs['medium']['url']) ? esc_url($thumbs['medium']['url']) : (isset($thumbs['default']['url']) ? esc_url($thumbs['default']['url']) : '');
        $large_thumb_url = isset($thumbs['high']['url']) ? esc_url($thumbs['high']['url']) : $thumb_url;
        if (!$thumb_url) continue;
        $video_url = 'https://www.youtube.com/watch?v=' . esc_attr($video_id);
        $output_html .= '<div class="flex-videos-item flex-videos-item-has-overlay" tabindex="0" data-title="' . esc_attr($overlay_title) . '" data-desc="' . esc_attr($overlay_desc) . '" data-thumb="' . esc_url($large_thumb_url) . '" data-url="' . esc_url($video_url) . '">';
        $output_html .= '<a href="' . $video_url . '" target="_blank" rel="noopener noreferrer" class="flex-videos-thumb-link">';
        $output_html .= '<img src="' . $thumb_url . '" alt="YouTube Video Thumbnail" class="flex-videos-thumb">';
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

// Enqueue Flex Videos CSS and JS
function flex_videos_enqueue_assets() {
    wp_enqueue_style('flex-videos-css', plugins_url('assets/css/flex-videos.css', __FILE__), [], '1.0.0');
    wp_enqueue_script('flex-videos-flyout', plugins_url('assets/js/flex-videos-flyout.js', __FILE__), [], '1.0.0', true);
    
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
