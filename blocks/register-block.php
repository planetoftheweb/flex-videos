<?php
/**
 * Registers the Flex Videos block.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function flex_videos_register_block() {
    // Register the block script
    wp_register_script(
        'flex-videos-block-editor',
        plugins_url('../build/index.js', __FILE__),
        array(
            'wp-blocks',
            'wp-i18n',
            'wp-element',
            'wp-editor',
            'wp-block-editor',
            'wp-components'
        ),
        '1.0.1',
        false // Load in header for admin/editor scripts
    );

    // Register the block type
    register_block_type(__DIR__ . '/flex-videos-grid', array(
        'editor_script' => 'flex-videos-block-editor',
        'render_callback' => 'flex_videos_render_block',
    ));
}
add_action('init', 'flex_videos_register_block');

function flex_videos_render_block( $attributes, $content ) {
    // Extract attributes with defaults
    $count = isset($attributes['count']) ? intval($attributes['count']) : 9;
    $hashtag = isset($attributes['hashtag']) ? sanitize_text_field($attributes['hashtag']) : '';
    $columns = isset($attributes['columns']) ? intval($attributes['columns']) : 3;
    
    // Build shortcode attributes
    $shortcode_atts = array(
        'count' => $count,
        'columns' => $columns,
    );
    
    if (!empty($hashtag)) {
        $shortcode_atts['hashtag'] = $hashtag;
    }
    
    // Convert array to shortcode attribute string
    $atts_string = '';
    foreach ($shortcode_atts as $key => $value) {
        $atts_string .= ' ' . $key . '="' . esc_attr($value) . '"';
    }
    
    return do_shortcode('[flex_videos' . $atts_string . ']');
}
