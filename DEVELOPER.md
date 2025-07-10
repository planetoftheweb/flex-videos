# Developer Documentation - Flex Videos Plugin

## Overview
This document provides technical details for developers working on or extending the Flex Videos WordPress plugin.

## Security Features

### Input Validation & Sanitization
- **API Keys**: Sanitized using `sanitize_text_field()`
- **Channel IDs**: Enhanced validation with `flex_videos_validate_channel_id()` function
- **Numeric Values**: Bounds checking with `flex_videos_validate_numeric_range()`
- **Color Values**: WordPress core `sanitize_hex_color()` function
- **Text Areas**: WordPress core `sanitize_textarea_field()` function

### Nonce Verification
All admin form submissions use WordPress nonces:
- Cache clearing: `flex_videos_clear_cache` / `flex_videos_cache_nonce`
- API testing: `flex_videos_test_api` / `flex_videos_test_nonce`  
- Settings reset: `flex_videos_reset_settings` / `flex_videos_reset_nonce`

### XSS Prevention
- All dynamic output uses `esc_html()`, `esc_attr()`, or `esc_url()`
- Admin notices use proper escaping functions
- User input is sanitized before storage and escaped on output

## Code Architecture

### Main Plugin File Structure
```
flex-videos.php (650 lines)
├── Plugin Header & Security Check
├── Settings & Admin Interface (lines 30-400)
├── Shortcode Handlers (lines 400-580)
├── Asset Management (lines 580-650)
└── Hooks & Filters
```

### Key Functions
- `flex_videos_add_admin_menu()` - Admin menu registration
- `flex_videos_settings_init()` - Settings API initialization
- `flex_videos_clear_cache()` - Cache and settings management
- `flex_videos_validate_numeric_range()` - Enhanced numeric validation
- `flex_videos_validate_channel_id()` - YouTube channel ID validation

## Testing Infrastructure

### PHPUnit Tests
Located in `/tests/` directory:
- `test-flex-videos.php` - Main test suite
- `bootstrap.php` - Test environment setup
- `phpunit.xml` - PHPUnit configuration

### Test Coverage
- Plugin initialization
- Shortcode registration  
- Default options validation
- Input sanitization verification
- Color validation
- Cache functionality
- Admin menu registration
- Asset enqueuing

### Running Tests
```bash
# Install test dependencies
composer install

# Run all tests
composer test

# Run coding standards check
composer phpcs

# Fix coding standards
composer phpcbf
```

## Accessibility Features

### Keyboard Navigation
- Video grid items are keyboard focusable (`tabindex="0"`)
- Enter/Space keys show video details overlay
- Escape key hides overlay
- Focus management for better screen reader support

### ARIA Support
- Overlay has `role="dialog"` with descriptive `aria-label`
- Video thumbnails have proper alt text with video titles
- Headings use appropriate `aria-level` attributes
- Interactive elements have descriptive `aria-label` attributes

### Screen Reader Support
- Semantic HTML structure
- Proper heading hierarchy
- Descriptive link text
- Image alt attributes with context

## Performance Optimizations

### Caching Strategy
- YouTube API responses cached with WordPress transients
- Cache keys include channel ID for uniqueness
- Configurable cache duration (default: 1 hour)
- Admin interface for manual cache clearing

### Asset Loading
- CSS/JS only loaded when shortcodes are present
- Minified assets for production
- CSS custom properties for easy theming
- Conditional script loading

### Image Optimization
- Multiple thumbnail resolutions (medium for grid, high for overlay)
- Lazy loading ready markup
- Responsive image sizing

## Development Workflow

### Build Process
```bash
# Install dependencies
npm install
composer install

# Build block assets
npm run build

# Lint PHP code
composer phpcs

# Run tests
composer test
```

### CI/CD Pipeline
GitHub Actions workflow includes:
- Multi-PHP version testing (7.4, 8.0, 8.1, 8.2)
- Coding standards validation
- Security vulnerability scanning
- Build artifact verification
- Automated testing

### Code Standards
- WordPress PHP Coding Standards (WPCS)
- WordPress JavaScript Coding Standards
- WordPress CSS Coding Standards
- PHPDoc documentation for all functions

## API Integration

### YouTube Data API v3
- Requires API key from Google Cloud Console
- Channel data endpoint: `/channels?part=snippet`
- Video data endpoint: `/search?part=snippet`
- Error handling for API failures
- Rate limiting awareness

### Data Structure
```php
// Cached channel data structure
array(
    'title' => 'Channel Name',
    'description' => 'Channel Description',
    'thumbnail' => 'https://...',
    'videos' => array(
        array(
            'id' => 'VIDEO_ID',
            'title' => 'Video Title',
            'description' => 'Video Description',
            'thumbnail' => 'https://...',
            'published' => '2024-01-01T00:00:00Z'
        )
    )
)
```

## Extending the Plugin

### Custom Hooks
```php
// Filter video data before display
apply_filters( 'flex_videos_video_data', $video_data, $video_id );

// Filter grid HTML output
apply_filters( 'flex_videos_grid_html', $html, $atts );

// Action after settings save
do_action( 'flex_videos_settings_saved', $settings );
```

### Custom CSS Properties
```css
:root {
    --flex-videos-button-bg: #ff8c00;
    --flex-videos-button-bg-hover: #e67c00;
    --flex-videos-button-color: #fff;
    --flex-videos-button-shadow: rgba(255, 140, 0, 0.3);
    --flex-videos-button-shadow-hover: rgba(230, 124, 0, 0.4);
}
```

## Troubleshooting

### Common Issues
1. **API Quota Exceeded**: Implement request caching and minimize API calls
2. **Invalid Channel ID**: Use validation function and proper error messaging
3. **Style Conflicts**: Use CSS specificity and custom properties
4. **JavaScript Errors**: Check for jQuery dependencies and DOM ready state

### Debug Mode
Enable WordPress debug logging:
```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Performance Monitoring
- Monitor transient cache hit rates
- Track API response times
- Monitor JavaScript performance with browser dev tools

## Security Best Practices

### Ongoing Maintenance
- Regular security audits of input validation
- Keep dependencies updated
- Monitor for WordPress security updates
- Regular nonce and permission checks
- Sanitize all user inputs
- Escape all outputs
- Validate API responses

### Vulnerability Prevention
- Never trust external API data
- Always validate and sanitize user inputs
- Use WordPress security functions
- Regular code reviews
- Automated security scanning in CI/CD