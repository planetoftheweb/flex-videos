<?php
/**
 * Registers the Flex Videos block.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function flex_videos_register_block() {
    register_block_type( __DIR__ . '/flex-videos-grid', [
        'render_callback' => 'flex_videos_render_block',
    ] );
}
add_action( 'init', 'flex_videos_register_block' );

function flex_videos_render_block( $attributes, $content ) {
    return do_shortcode( '[flex_videos]' );
}
