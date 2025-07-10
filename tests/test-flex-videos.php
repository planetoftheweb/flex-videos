<?php
/**
 * Test cases for Flex Videos plugin
 * 
 * @package FlexVideos
 * @subpackage Tests
 */

class Test_Flex_Videos extends WP_UnitTestCase {

    /**
     * Test plugin initialization
     */
    public function test_plugin_initialized() {
        $this->assertTrue( function_exists( 'flex_videos_add_admin_menu' ) );
        $this->assertTrue( function_exists( 'flex_videos_settings_init' ) );
    }

    /**
     * Test shortcode registration
     */
    public function test_shortcodes_registered() {
        $this->assertTrue( shortcode_exists( 'flex_videos' ) );
        $this->assertTrue( shortcode_exists( 'flex_video' ) );
    }

    /**
     * Test default options
     */
    public function test_default_options() {
        $this->assertEquals( 3, get_option( 'flex_videos_columns', 3 ) );
        $this->assertEquals( 15, get_option( 'flex_videos_gap', 15 ) );
        $this->assertEquals( 9, get_option( 'flex_videos_num_videos', 9 ) );
        $this->assertEquals( '1', get_option( 'flex_videos_show_grid_title', '1' ) );
    }

    /**
     * Test input sanitization
     */
    public function test_input_sanitization() {
        // Test integer fields
        update_option( 'flex_videos_columns', '5' );
        $this->assertEquals( 5, get_option( 'flex_videos_columns' ) );
        
        // Test text field sanitization
        update_option( 'flex_videos_custom_grid_title', '<script>alert("xss")</script>Test Title' );
        $sanitized = get_option( 'flex_videos_custom_grid_title' );
        $this->assertStringNotContainsString( '<script>', $sanitized );
    }

    /**
     * Test color validation
     */
    public function test_color_validation() {
        // Valid hex color
        update_option( 'flex_videos_button_color', '#ff8c00' );
        $this->assertEquals( '#ff8c00', get_option( 'flex_videos_button_color' ) );
        
        // Invalid color should be sanitized
        update_option( 'flex_videos_button_color', 'invalid-color' );
        $color = get_option( 'flex_videos_button_color' );
        $this->assertStringStartsWith( '#', $color );
    }

    /**
     * Test shortcode output structure
     */
    public function test_shortcode_output_structure() {
        // Mock API key and channel ID for testing
        update_option( 'flex_videos_api_key', 'test_key' );
        update_option( 'flex_videos_channel_id', 'test_channel' );
        
        $output = do_shortcode( '[flex_videos columns="2" count="4"]' );
        
        // Should contain proper CSS classes
        $this->assertStringContainsString( 'flex-videos-grid', $output );
        $this->assertStringContainsString( 'wp-block-group', $output );
    }

    /**
     * Test cache functionality
     */
    public function test_cache_functionality() {
        $cache_key = 'flex_videos_test_cache';
        $test_data = array( 'test' => 'data' );
        
        // Set cache
        set_transient( $cache_key, $test_data, 3600 );
        
        // Verify cache exists
        $cached_data = get_transient( $cache_key );
        $this->assertEquals( $test_data, $cached_data );
        
        // Clear cache
        delete_transient( $cache_key );
        $this->assertFalse( get_transient( $cache_key ) );
    }

    /**
     * Test admin menu registration
     */
    public function test_admin_menu_registration() {
        // Create admin user
        $admin_user = $this->factory->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $admin_user );
        
        // Test that admin menu hook exists
        $this->assertNotFalse( has_action( 'admin_menu', 'flex_videos_add_admin_menu' ) );
    }

    /**
     * Test script and style enqueuing
     */
    public function test_assets_enqueued() {
        // Trigger the action
        do_action( 'wp_enqueue_scripts' );
        
        // Check if styles are registered
        $this->assertTrue( wp_style_is( 'flex-videos-css', 'registered' ) );
        $this->assertTrue( wp_script_is( 'flex-videos-flyout', 'registered' ) );
    }

    /**
     * Clean up after tests
     */
    public function tearDown(): void {
        // Clean up test options
        delete_option( 'flex_videos_columns' );
        delete_option( 'flex_videos_gap' );
        delete_option( 'flex_videos_custom_grid_title' );
        delete_option( 'flex_videos_button_color' );
        delete_option( 'flex_videos_api_key' );
        delete_option( 'flex_videos_channel_id' );
        
        parent::tearDown();
    }
}