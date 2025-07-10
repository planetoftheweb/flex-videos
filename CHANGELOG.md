# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] - Development Improvements

### Added
- Comprehensive PHPUnit test suite with automated testing
- Enhanced CI/CD workflow with multi-PHP version testing and security checks
- Improved input validation with bounds checking for numeric fields
- Enhanced YouTube Channel ID validation with format checking
- Accessibility improvements: keyboard navigation, ARIA labels, screen reader support
- Developer documentation (DEVELOPER.md) with security and architecture details
- Accessibility documentation (ACCESSIBILITY.md) with WCAG 2.1 compliance details
- Development setup script (dev-setup.sh) for easy environment configuration

### Enhanced
- JavaScript flyout overlay with keyboard navigation support (Enter, Space, Escape keys)
- Focus management for better accessibility
- ARIA roles and labels for screen reader compatibility
- Image alt text with contextual video information
- PHPDoc documentation throughout codebase

### Security
- Enhanced nonce verification validation in CI pipeline
- Input sanitization security checks
- Dangerous function detection in automated testing
- Improved validation functions with proper error handling

### Development
- Multi-environment testing (PHP 7.4, 8.0, 8.1, 8.2)
- Automated code quality checks with PHPCS
- Security vulnerability scanning
- Build artifact verification
- Comprehensive test coverage for core functionality

## [1.0.2] - 2025-07-01

### Added
- Gutenberg block for inserting video grids with customizable options
- Enhanced block editor integration with sidebar controls
- Proper WordPress script loading compliance

### Fixed
- WordPress Plugin Checker script registration warnings
- Explicit `$in_footer` parameter for all script registrations
- Enhanced block editor compatibility

### Changed
- Refactored uninstall process to better clean transients and options
- Minified CSS/JS and optimized selectors

### Removed
- GitHub update checker (for WordPress.org compliance)
- Obsolete `includes/class-flex-videos.php` and Composer autoload configuration

## [1.0.1] - 2025-06-30

### Fixed
- WordPress plugin checker compliance issues
- Added proper sanitization callbacks to all register_setting() calls
- Replaced direct database queries with WordPress transient functions
- Improved cache clearing system using version-based invalidation
- Added proper output escaping for security compliance
- Removed invalid "Network" header from plugin file

### Security
- Enhanced input sanitization for all settings fields
- Improved output escaping throughout the plugin
- Replaced direct SQL queries with WordPress API functions

## [1.0.0] - 2025-06-29

### Added
- Modern flyout overlay on hover with full video details (large thumbnail, title, description)
- Cropped thumbnails to remove black bars from YouTube
- Orange-yellow "Visit Channel" button with bold, sans-serif font
- Tighter layout with reduced space above/below grid and overlays
- All output wrapped in a WordPress block group for block compatibility
- Overlay content is always left-aligned and never truncated
- CSS/JS fully external and improved for maintainability
- Button and overlay styles are easy to customize

### Changed
- Main grid descriptions are trimmed more aggressively
- Removed unnecessary inner container divs for cleaner markup
- Overlay content is never clipped and always visible at viewport edges

### Fixed
- Thumbnail alignment for non-standard aspect ratios
- Overlay positioning and boundary checks
- Class name inconsistencies and duplicate CSS
