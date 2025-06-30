=== Flex Videos ===
Contributors: rayvillalobos
Tags: youtube, video, grid, responsive, embed
Requires at least: 5.0
Tested up to: 6.5
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin for displaying responsive YouTube video grids with modern flyout overlays and customizable styling.

== Description ==

Flex Videos is a professional WordPress plugin that creates beautiful, responsive video grids using the YouTube Data API. Perfect for showcasing your latest videos with modern flyout overlays and customizable styling options.

= Key Features =

* YouTube Grid Display - Show video thumbnails from any YouTube channel in a responsive grid
* Modern Flyout Overlay - Hover over thumbnails to see an animated overlay with full video details
* Customizable Button Colors - Admin color pickers for channel link button styling
* Responsive Design - Looks great on all devices with configurable column layouts
* Built-in Caching - API responses are cached for optimal performance
* Optimized Thumbnails - Medium resolution for grid performance, high-resolution for overlay quality
* WordPress Compatible - Follows WordPress coding standards and best practices

= Smart Overlay System =

* Fade-in animation with smooth transitions
* Never clipped - automatically repositions at viewport edges
* High-resolution thumbnail preview with full title and description text
* Left-aligned layout for consistent, professional appearance

= Admin-Friendly Customization =

* Color picker controls for button background, hover, and text colors
* Show/hide toggles for grid title, description, and channel link
* Custom text options for grid title, description, and button text
* Cache management and settings reset tools for troubleshooting

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/flex-videos` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Go to Settings > Flex Videos to configure your YouTube API key and channel ID
4. Use the [flex_videos] shortcode to display your video grid

== Frequently Asked Questions ==

= Do I need a YouTube API key? =

Yes, you need a free YouTube Data API v3 key from Google Cloud Console to fetch video information.

= Can I customize the button colors? =

Yes, the plugin includes admin color pickers for button background, hover, and text colors.

= Does it work with any WordPress theme? =

Yes, the plugin is designed to work with any properly coded WordPress theme and follows WordPress standards.

= Can I filter videos by hashtags? =

Yes, you can use the hashtag attribute in the shortcode to filter videos containing specific hashtags.

== Screenshots ==

1. Video grid display with responsive layout
2. Admin settings page with customization options
3. Flyout overlay showing video details

== Changelog ==

= 1.0.1 =
* Fixed WordPress plugin checker compliance issues
* Added proper sanitization to all settings fields
* Replaced direct database queries with WordPress transient functions
* Improved cache clearing system with version-based invalidation
* Enhanced security with proper output escaping
* Removed invalid plugin header fields

= 1.0.0 =
* Initial release
* YouTube Grid Display with responsive layout
* Modern flyout overlay system
* Customizable button colors via admin interface
* Built-in caching system
* Optimized thumbnail loading (medium quality for grid, high quality for overlays)
* WordPress coding standards compliance

== Upgrade Notice ==

= 1.0.1 =
Important security and compliance update. Fixes WordPress plugin checker issues and improves overall plugin security.

= 1.0.0 =
Initial release of Flex Videos plugin.

== Usage ==

Use the shortcode `[flex_videos]` to display a grid of videos from your configured YouTube channel.

Available shortcode attributes:
* `columns` - Number of columns (1-10, default: 3)
* `count` - Number of videos to display (1-50, default: 9)
* `gap` - Space between thumbnails in pixels (default: 15)
* `hashtag` - Filter videos containing hashtag in description

Example: `[flex_videos columns="4" count="12" hashtag="#tutorial"]`

For single video embeds, use: `[flex_video url="https://www.youtube.com/watch?v=VIDEO_ID"]`
