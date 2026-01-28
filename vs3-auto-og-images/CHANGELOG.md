# Changelog

## Version 1.1.0 (2025-10-30)

### Changed
- **Homepage Exclusion**: Plugin now excludes homepage and front page from OG image generation
- OG images are now only generated for individual posts and pages (not the homepage)

### Technical Details
- Added `is_front_page()` and `is_home()` checks in meta injector
- Added `page_on_front` and `page_for_posts` checks in image generator
- Updated plugin description to reflect homepage exclusion
- Bumped version to 1.1.0

## Version 1.0.0 (Initial Release)

### Features
- ✅ Automatic OG image generation (1200×900 4:3 ratio)
- ✅ Works on posts and pages without featured images
- ✅ Multisite network activation support
- ✅ Network-wide default settings
- ✅ Per-site setting overrides
- ✅ Customizable colors (background, text, accent)
- ✅ Site logo integration (top left)
- ✅ Post/page title (large, centered, word-wrapped)
- ✅ Site name (bottom accent)
- ✅ Meta tag injection (OG + Twitter Cards)
- ✅ Smart caching with versioning
- ✅ Auto-regeneration on content changes
- ✅ Manual cache clearing
- ✅ Clean URL structure (/vs3-og/{POST_ID}.png)
- ✅ Buffer safety (prevents image corruption)
- ✅ Automatic cleanup of old versions
- ✅ System font support (Arial/DejaVu Sans)

### Technical Implementation
- PHP GD library for image generation
- Rewrite rules for clean URLs
- Output buffer cleaning for PNG safety
- Version-based cache busting
- Network and site option storage
- WordPress color picker integration
- Automatic uninstall cleanup

### Storage
- Images stored in: `/wp-content/uploads/vs3-og/`
- Format: `{POST_ID}-v{VERSION}.png`
- Protected directory with .htaccess

### Cache Management
- Bumps version on site title change
- Clears single image on post update
- Removes image when featured image added
- Manual bulk clear available

### Security
- Directory index protection
- Nonce verification on all forms
- Sanitized user inputs
- Capability checks (manage_options, manage_network_options)

### Browser Support
- Works with all modern social platforms
- Facebook Open Graph
- Twitter Cards
- LinkedIn sharing
- Generic OG tag readers

## Roadmap (Future Versions)

### Potential Enhancements
- Custom post type support
- Custom fonts upload
- Template variations
- Image position customization
- Gradient backgrounds
- Pattern overlays
- Dynamic color schemes
- Batch regeneration tool
- Preview before save
- REST API endpoint
- Image optimization
- WebP support
- Custom dimensions per site

