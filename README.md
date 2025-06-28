# Flex Videos

A professional WordPress plugin for displaying flexible, responsive video embeds with YouTube API integration.

## Features
- 🎥 **YouTube API Integration** - Display latest videos from any channel
- 📱 **Responsive Design** - Mobile-friendly video grids that look great on all devices
- ⚡ **Performance Optimized** - Built-in caching and lazy loading for fast page loads
- 🎨 **Customizable Layout** - Control columns, spacing, and video dimensions
- 🔧 **Easy Configuration** - Simple admin interface for API setup
- 🌐 **Translation Ready** - Full internationalization support
- 📊 **Analytics Integration** - Built-in click tracking for Google Analytics
- 🛡️ **Secure & Standards Compliant** - Follows WordPress coding standards and security best practices

## Installation

### Method 1: WordPress Admin (Recommended)
1. Download the latest release as a ZIP file from the [Releases page](https://github.com/yourusername/flex-videos/releases)
2. In your WordPress admin, go to **Plugins > Add New**
3. Click **Upload Plugin** at the top
4. Select the ZIP file and click **Install Now**
5. Click **Activate Plugin**
6. Proceed to the [Configuration](#configuration) section below

### Method 2: Manual Installation
1. Download and extract the plugin files
2. Upload the entire `flex-videos` folder to `/wp-content/plugins/`
3. Activate the plugin through the WordPress admin **Plugins** menu
4. Proceed to the [Configuration](#configuration) section below

### Method 3: GitHub (Development)
```bash
cd /wp-content/plugins/
git clone https://github.com/yourusername/flex-videos.git
```
Then activate through WordPress admin.

## Configuration

### YouTube API Setup
1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Navigate to **APIs & Services > Library**
4. Search for and enable **YouTube Data API v3**
5. Go to **APIs & Services > Credentials**
6. Click **Create Credentials > API Key**
7. Copy your API key for the next step

### WordPress Configuration
1. In your WordPress admin, go to **Settings > YouTube Videos**
2. Enter your YouTube API Key
3. (Optional) Enter a specific YouTube Channel ID to display videos from
4. Click **Save Changes**
5. Test the configuration with a shortcode

## Usage

### Basic Shortcodes

**Single Video Embed:**
```
[flex_video url="https://www.youtube.com/watch?v=VIDEO_ID"]
```

**Video Grid from Channel:**
```
[flex_videos]
[flex_videos columns="4" width="300px"]
[flex_videos hashtag="#tutorial" columns="3"]
```

### Shortcode Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| `columns` | `3` | Number of columns in the grid (1-5) |
| `width` | `320px` | Maximum width of each video thumbnail |
| `hashtag` | - | Filter videos by hashtag in description |
| `count` | `12` | Maximum number of videos to display |

### Advanced Examples

**Tutorial Videos Grid:**
```
[flex_videos hashtag="#tutorial" columns="2" width="400px" count="6"]
```

**Mobile-Optimized Layout:**
```
[flex_videos columns="1" width="100%"]
```

## Styling & Customization

The plugin includes professional CSS styling that's automatically loaded. You can customize the appearance by adding CSS to your theme or using WordPress Customizer (**Appearance > Customize > Additional CSS**).

### Key CSS Classes

```css
/* Main grid container */
.flex-videos-grid {
  display: grid;
  gap: 20px;
  margin: 20px 0;
}

/* Individual video items */
.flex-video-item {
  position: relative;
  background: #f9f9f9;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease;
}

/* Hide play button overlays for clearer thumbnails */
.flex-video-item .ytp-large-play-button,
.flex-video-item .ytp-play-button {
  display: none !important;
}

/* Hover effects */
.flex-video-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

/* Video titles and descriptions */
.flex-video-title {
  padding: 15px;
  font-size: 16px;
  font-weight: 600;
  color: #333;
}

.flex-video-description {
  padding: 0 15px 15px;
  font-size: 14px;
  color: #666;
}
```

### Custom Color Scheme Example

```css
/* Dark theme example */
.flex-video-item {
  background: #2c2c2c;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.flex-video-title {
  color: #ffffff;
}

.flex-video-description {
  color: #cccccc;
}
```

## Requirements

- **WordPress:** 5.0 or higher
- **PHP:** 7.4 or higher
- **YouTube Data API v3 Key** (free from Google Cloud Console)

## Performance Features

- ⚡ **Caching System** - API responses cached for improved load times
- 🚀 **Lazy Loading** - Videos load only when visible
- 📱 **Responsive Images** - Optimized thumbnails for different screen sizes
- 🔄 **Background Processing** - Non-blocking API requests
- 👁️ **Clean Thumbnails** - Play button overlays hidden for better thumbnail visibility

## Development

### Local Development Setup

```bash
# Clone the repository
git clone https://github.com/yourusername/flex-videos.git
cd flex-videos

# Install PHP dependencies
composer install

# Install Node.js dependencies (if added)
npm install

# Run code quality checks
composer run phpcs
```

### File Structure

```
flex-videos/
├── assets/
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # Plugin assets
├── includes/             # PHP classes and functions
├── languages/            # Translation files
├── templates/            # Template files
├── flex-videos.php       # Main plugin file
├── uninstall.php        # Cleanup script
└── README.md            # This file
```

## Troubleshooting

### Common Issues

**Videos not loading?**
- Verify your YouTube API key is correct
- Check that YouTube Data API v3 is enabled in Google Cloud Console
- Ensure your API key has proper permissions

**Styling issues?**
- Check for theme conflicts
- Verify CSS is loading properly
- Try clearing any caching plugins

**Cache problems?**
- Use the "Reset Cache" button in **Settings > YouTube Videos**
- Clear any WordPress caching plugins

### Debug Mode

Add this to your `wp-config.php` for detailed error logging:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Quick Start for Contributors

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes
4. Run tests: `composer run test`
5. Check code standards: `composer run phpcs`
6. Commit your changes: `git commit -m 'Add amazing feature'`
7. Push to the branch: `git push origin feature/amazing-feature`
8. Open a Pull Request

### Development Commands

```bash
# Check PHP code standards
composer run phpcs

# Fix code standards automatically
composer run phpcbf

# Run tests
composer run test
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a detailed history of changes.

## Support

- 📖 **Documentation:** [GitHub Wiki](https://github.com/yourusername/flex-videos/wiki)
- 🐛 **Bug Reports:** [GitHub Issues](https://github.com/yourusername/flex-videos/issues)
- 💬 **Discussions:** [GitHub Discussions](https://github.com/yourusername/flex-videos/discussions)
- 📧 **Email:** your-email@example.com

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Credits

- **Author:** Ray Villalobos
- **Contributors:** See [Contributors](https://github.com/yourusername/flex-videos/contributors)
- **YouTube Data API:** Google LLC

---

⭐ **If you find this plugin helpful, please star the repository!**
