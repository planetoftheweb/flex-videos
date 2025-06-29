# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
