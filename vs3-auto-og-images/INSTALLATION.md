# VS3 Auto OG Images - Installation & Testing Guide

## Quick Start

### For Multisite (Like TriPeak)

1. **Network Activate the Plugin**
   - Go to Network Admin → Plugins
   - Find "VS3 Auto OG Images"
   - Click "Network Activate"

2. **Configure Network Settings**
   - Go to Network Admin → Settings → Auto OG Images
   - Enable by default: ✓
   - Set default colors (or use defaults):
     - Background: `#ffffff` (white)
     - Text: `#000000` (black)
     - Accent: `#0073aa` (WordPress blue)
   - Click "Save Network Settings"

3. **Per-Site Configuration (Optional)**
   - Switch to any site in your network
   - Go to Settings → Auto OG Images
   - Override colors if desired
   - Clear cache if testing

4. **Test It Out**
   - Create or edit a post/page WITHOUT a featured image
   - Publish it
   - View the post
   - Check page source for OG meta tags
   - Test sharing URL on Facebook/Twitter/LinkedIn

### For Single Site

1. **Activate the Plugin**
   - Go to Plugins → Installed Plugins
   - Find "VS3 Auto OG Images"
   - Click "Activate"

2. **Configure Settings**
   - Go to Settings → Auto OG Images
   - Enable automatic OG image generation: ✓
   - Customize colors if desired
   - Click "Save Changes"

3. **Test It Out**
   - Same as multisite step 4 above

## Testing on TriPeak Site

Since you mentioned testing on the TriPeak site:

1. **Network activate** the plugin
2. Switch to the TriPeak site
3. Find a post/page without a featured image
4. View it in browser
5. Right-click → View Page Source
6. Search for `vs3-og` - you should see:
   ```html
   <!-- VS3 Auto OG Images -->
   <meta property="og:image" content="https://tripeak.local/vs3-og/123.png?v=..." />
   <meta property="og:image:width" content="1200" />
   <meta property="og:image:height" content="900" />
   ...
   ```

7. **View the actual image**: Copy the OG image URL and paste in browser
8. **Test with validators**:
   - Facebook: https://developers.facebook.com/tools/debug/
   - Twitter: https://cards-dev.twitter.com/validator
   - LinkedIn: https://www.linkedin.com/post-inspector/

## What Gets Generated?

For each post/page WITHOUT a featured image:

```
Image: 1200×900 PNG
Design:
  ┌─────────────────────────────────┐
  │  [Logo]                         │
  │                                 │
  │                                 │
  │     Post/Page Title Here        │
  │     (Big, Centered, Wrapped)    │
  │                                 │
  │                                 │
  │  ___                            │
  │  Site Name                      │
  └─────────────────────────────────┘
```

## Troubleshooting

### No images generating?
```bash
# Check GD is installed
php -m | grep -i gd

# Check upload directory permissions
ls -la wp-content/uploads/
# Should show writable permissions
```

### Image looks broken?
- Go to Settings → Auto OG Images → Clear Cache
- Regenerate by visiting the post again

### Want to customize more?
- Edit colors in settings
- Add your site logo: Appearance → Customize → Site Identity → Logo
- Change site name: Settings → General → Site Title

### Check if it's working programmatically:
```bash
# From your Local Sites terminal
cd app/public

# Check if rewrite rules are set
wp rewrite list --allow-root | grep vs3

# Test image generation for post ID 123
curl -I "https://yoursite.local/vs3-og/123.png"
# Should return 200 OK with Content-Type: image/png
```

## Files Created by Plugin

```
wp-content/
  uploads/
    vs3-og/               ← Generated images directory
      123-v1234567890.png ← Post ID 123, version timestamp
      456-v1234567890.png ← Post ID 456, version timestamp
      .htaccess           ← Security protection
```

## Cache Behavior

Images are regenerated when:
- ✅ Post/page title changes
- ✅ Post/page content is updated  
- ✅ Site title changes (all images)
- ✅ Site logo changes (manual cache clear needed)
- ✅ Settings colors change
- ✅ Featured image is removed

Images are NOT used when:
- ❌ Post has a featured image (uses that instead)
- ❌ Not a post or page (custom post types not supported by default)
- ❌ Plugin is disabled in settings

## Performance Notes

- Images cached as files (very fast)
- Served with 1-year cache headers
- Only generated once, then reused
- Minimal database queries
- Buffer cleaned before PNG output (prevents corruption)

## Example URL

If you have a post with ID `123`, the OG image will be:
```
https://yoursite.com/vs3-og/123.png?v=1234567890
```

The `?v=` parameter ensures fresh images when cache is cleared.

## Support

Having issues? Check:
1. PHP version ≥ 7.4
2. GD extension installed
3. Uploads directory writable
4. Rewrite rules flushed (Settings → Permalinks → Save)
5. Site logo is set
6. No conflicting OG plugins

## Inspired By

This plugin creates OG images similar to:
https://mikekarnj.com/posts/personal-holding-company

Perfect for sites like TriPeak that need consistent, professional social sharing images!

