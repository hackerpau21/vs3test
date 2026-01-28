# VS3 Auto OG Images

A WordPress multisite-aware plugin that automatically generates beautiful 4:3 Open Graph images (1200×900) for posts and pages without featured images.

## Features

- **Automatic Generation**: Creates OG images for posts/pages without featured images (excludes homepage/front page)
- **4:3 Aspect Ratio**: Optimized 1200×900 resolution for social media sharing
- **Beautiful Design**: Combines site logo, post title, and site name
- **Multisite Ready**: Network-activate with per-site overrides
- **Smart Caching**: Regenerates only when needed (title changes, post updates)
- **Meta Tag Injection**: Automatically adds OG and Twitter Card meta tags
- **Customizable Colors**: Configure background, text, and accent colors
- **Homepage Exclusion**: Automatically skips OG image generation for homepage and front page

## Design Layout

Each generated image includes:
- **Site Logo** (top left, max 300×200)
- **Post/Page Title** (large, centered, wrapped to 3 lines max)
- **Site Name** (smaller, bottom)
- **Accent Line** (decorative element)

## Installation

1. Upload the plugin folder to `/wp-content/plugins/`
2. Network activate the plugin (or regular activate for single site)
3. Configure network settings (multisite) or site settings
4. That's it! OG images will be generated automatically

## Multisite Configuration

### Network Settings
1. Go to Network Admin → Settings → Auto OG Images
2. Enable/disable by default for all sites
3. Set default colors (background, text, accent)
4. Individual sites can override these settings

### Per-Site Settings
1. Go to Site Settings → Auto OG Images
2. Override network defaults if needed
3. Customize colors for this specific site
4. Clear cache to regenerate images

## Storage

Images are stored in: `/wp-content/uploads/vs3-og/{POST_ID}-v{VERSION}.png`

- Versioned filenames prevent cache issues
- Old versions automatically cleaned up
- Protected directory with proper permissions

## Cache Management

### Automatic Regeneration
- **Site Title Change**: Bumps version, clears all images
- **Post Update**: Clears single post image
- **Featured Image Added**: Removes OG image (uses featured image instead)

### Manual Cache Clearing
Go to Settings → Auto OG Images → Clear Cache to regenerate all images

## Technical Details

- **Image Format**: PNG (1200×900)
- **Generation**: PHP GD library
- **Buffer Safety**: Cleans output buffers before serving images
- **Font Support**: Uses system fonts (Arial/DejaVu Sans)
- **Performance**: Cached images, served with 1-year expiration headers

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- PHP GD extension
- Write permissions to uploads directory

## URL Structure

OG images are served via clean URLs:
```
https://yoursite.com/vs3-og/{POST_ID}.png
```

## Meta Tags Generated

```html
<meta property="og:image" content="https://yoursite.com/vs3-og/123.png" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="900" />
<meta property="og:image:type" content="image/png" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:image" content="https://yoursite.com/vs3-og/123.png" />
```

## Use Cases

Perfect for:
- News sites without consistent featured images
- Blog networks with varying content types
- Documentation sites
- Portfolio sites
- Any site needing consistent social sharing images

## Troubleshooting

### Images not generating?
- Check that PHP GD extension is installed
- Verify write permissions to uploads directory
- Ensure you have a site logo set (Appearance → Customize → Site Identity)

### Images look wrong?
- Adjust colors in settings
- Clear cache to regenerate
- Check that system fonts are available

### Multisite issues?
- Ensure plugin is network activated
- Check network settings are saved
- Verify per-site overrides if needed

## Credits

Created for sites that need beautiful OG images without manual featured image management.

Inspired by: https://mikekarnj.com/posts/personal-holding-company

## License

GPL v2 or later

