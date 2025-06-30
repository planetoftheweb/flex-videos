<?php
/**
 * Plugin Name:       Flex Videos
 * Plugin URI:        https://github.com/planetoftheweb/flex-videos
 * Description:       A WordPress plugin for displaying flexible, responsive video embeds with YouTube API integration.
 * Version:           1.0.1
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

// --- PLUGIN SETTINGS & CACHE HANDLING ---

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
        <h2><?php esc_html_e('Cache Management', 'flex-videos'); ?></h2>
        <p><?php esc_html_e('The plugin caches YouTube results for 1 hour to improve performance. You can manually clear this cache using the button below.', 'flex-videos'); ?></p>
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
    $channel_title = $channel_info['title'] ?? __('Latest Videos', 'flex-videos');
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
                // translators: %s is the error message from the API request
                return sprintf(__('Error: API request failed - %s', 'flex-videos'), $response->get_error_message());
            }
            return '';
        }
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            if (current_user_can('manage_options')) {
                $response_body = wp_remote_retrieve_body($response);
                $error_data = json_decode($response_body, true);
                $error_message = $error_data['error']['message'] ?? __('Unknown API error', 'flex-videos');
                // translators: %1$s is the response code, %2$s is the error message
                return sprintf(__('Error: YouTube API returned %1$s - %2$s', 'flex-videos'), $response_code, $error_message);
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
    } elseif (strpos($url, 'vimeo.com') !== false) {
        if (preg_match('/vimeo.com\/(\d+)/', $url, $matches)) {
            $video_id = $matches[1];
            $embed_url = 'https://player.vimeo.com/video/' . esc_attr($video_id);
        } else {
            return __('Invalid Vimeo URL.', 'flex-videos');
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
    wp_enqueue_style('flex-videos-css', plugins_url('assets/css/flex-videos.min.css', __FILE__), [], '1.0.1');
    wp_enqueue_script('flex-videos-flyout', plugins_url('assets/js/flex-videos-flyout.min.js', __FILE__), [], '1.0.1', true);
    
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
