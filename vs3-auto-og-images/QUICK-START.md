# Quick Start Guide - VS3 Auto OG Images

## 🚀 Installation (30 seconds)

### For Multisite (TriPeak, etc.)
1. Go to **Network Admin → Plugins**
2. Find **VS3 Auto OG Images**
3. Click **Network Activate**
4. Done! ✅

### For Single Site
1. Go to **Plugins → Installed Plugins**
2. Find **VS3 Auto OG Images**
3. Click **Activate**
4. Done! ✅

## ⚙️ Configuration (Optional - Works out of box!)

### Network Defaults (Multisite Only)
**Network Admin → Settings → Auto OG Images**
- ✅ Enable by default
- 🎨 Set default colors
- 💾 Save settings

### Per-Site Settings
**Settings → Auto OG Images**
- 🔌 Enable/disable for this site
- 🎨 Override colors
- 🗑️ Clear cache

## 🎨 Default Colors
- **Background**: White (`#ffffff`)
- **Text**: Black (`#000000`)
- **Accent**: WordPress Blue (`#0073aa`)

## ✅ Testing

1. **Create a post/page WITHOUT a featured image**
2. **Publish it**
3. **View the post**
4. **Right-click → View Source**
5. **Search for**: `vs3-og`

You should see:
```html
<meta property="og:image" content="https://yoursite.com/vs3-og/123.png" />
```

## 🖼️ View Generated Image

Copy the OG image URL from source and paste in browser:
```
https://yoursite.com/vs3-og/123.png
```

## 🔍 Test Social Sharing

**Facebook Debugger**: https://developers.facebook.com/tools/debug/
1. Paste your post URL
2. Click "Scrape Again"
3. See your generated OG image!

**Twitter Card Validator**: https://cards-dev.twitter.com/validator
**LinkedIn Inspector**: https://www.linkedin.com/post-inspector/

## 📁 Where Are Images Stored?

```
/wp-content/uploads/vs3-og/
  ├── 123-v1696700000.png
  ├── 456-v1696700000.png
  └── .htaccess
```

## 🎯 What Gets Generated?

**Image Size**: 1200×900 (4:3 ratio - perfect for social!)

**Design**:
- 📷 Your site logo (top left)
- 📝 Post/Page title (big, centered)
- 🏷️ Site name (bottom)
- 🎨 Accent line (decorative)

## 🔄 When Are Images Regenerated?

**Automatically**:
- ✏️ Post title changes
- 📝 Post content updates
- 🏷️ Site title changes (all images)
- 🎨 Color settings change
- 🖼️ Featured image removed

**Manually**:
- Settings → Auto OG Images → Clear Cache

## ⚡ Pro Tips

1. **Set your site logo first**: Appearance → Customize → Site Identity → Logo
2. **Test with no featured image posts**: Plugin only works when no featured image exists
3. **Use for consistency**: Perfect for news/blog sites with varying content
4. **Network flexibility**: Each site can have different colors in multisite
5. **Clear cache after logo change**: Settings → Auto OG Images → Clear Cache

## 🛠️ Troubleshooting

### Images not showing?
```bash
# Check PHP GD extension
php -m | grep -i gd
```

### Permissions issue?
```bash
# Make uploads writable
chmod 755 /path/to/wp-content/uploads/
```

### Rewrite rules not working?
- Go to Settings → Permalinks
- Click "Save Changes" (flushes rules)

## 📊 Example Post Flow

```
1. Create Post
   ↓
2. No Featured Image?
   ↓ YES
3. Plugin Generates OG Image (1200×900)
   ↓
4. Saves to /vs3-og/POST_ID.png
   ↓
5. Injects <meta property="og:image">
   ↓
6. Share on Social Media!
   ↓
7. Beautiful Preview Image! ✨
```

## 🎯 Perfect For

- ✅ News sites
- ✅ Blog networks
- ✅ Documentation sites
- ✅ Portfolio sites
- ✅ Multisite installations
- ✅ Sites without consistent featured images
- ✅ **Your TriPeak site!**

## 📚 More Info

- **Full Documentation**: See `README.md`
- **Installation Guide**: See `INSTALLATION.md`
- **Changelog**: See `CHANGELOG.md`

## 🎉 That's It!

Your posts now have beautiful, auto-generated OG images for social sharing!

**Inspired by**: https://mikekarnj.com/posts/personal-holding-company

