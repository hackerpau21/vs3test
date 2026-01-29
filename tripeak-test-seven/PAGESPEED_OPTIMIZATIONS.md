# PageSpeed Insights Optimizations

This document outlines all the performance optimizations made to fix PageSpeed Insights issues.

## Issues Fixed

### 1. ✅ LCP (Largest Contentful Paint) - Hero Image Discovery

**Problem:** Hero background image was lazy-loaded via JavaScript, making it not discoverable from HTML.

**Solution:**
- Changed hero section from CSS background-image to HTML `<picture>` element with `<img>`
- Added `fetchpriority="high"` attribute to hero image
- Added `decoding="sync"` for immediate rendering
- Added preload links in `<head>` for responsive hero images
- Removed JavaScript-based background image loading

**Files Modified:**
- `front-page.php` - Changed hero section structure
- `style.css` - Added CSS for `.hero-bg-image` and `.hero-bg-picture`
- `functions.php` - Added hero image preload in `tripeak_test_seven_preload_critical_resources()`
- `assets/js/main.js` - Removed `initResponsiveHeroBackground()` function

### 2. ✅ Render Blocking Resources Optimization

**Problem:** CSS and JavaScript files were blocking page rendering.

**Solution:**
- Deferred non-critical CSS (Font Awesome, Google Fonts) using preload technique
- Deferred JavaScript files with `defer` attribute
- Kept jQuery loading normally (required by other scripts)
- Added noscript fallbacks for CSS

**Files Modified:**
- `functions.php`:
  - Updated `tripeak_test_seven_scripts()` to properly enqueue resources
  - Modified `tripeak_test_seven_defer_scripts()` to defer main JS and button enhancements
  - Updated `tripeak_test_seven_optimize_css_delivery()` to defer fonts and Font Awesome

### 3. ✅ Image Delivery Optimization

**Problem:** Images not using modern formats (WebP), missing responsive attributes, or improperly sized.

**Solution:**
- Implemented `<picture>` element with WebP support for all images
- Added proper `srcset` and `sizes` attributes for responsive images
- Set explicit `width` and `height` attributes to prevent layout shifts
- Used `loading="lazy"` for below-the-fold images
- Used `fetchpriority="high"` for above-the-fold images (LCP elements)
- Reduced JPEG quality from 85 to 82 for better compression

**Files Modified:**
- `template-parts/content-card.php` - Card images with WebP and responsive support
- `template-parts/content.php` - Featured images with conditional lazy loading
- `page.php` - Page featured images with WebP support
- `functions.php` - Reduced JPEG compression quality to 82
- `style.css` - Added CSS for proper picture element display

## Technical Implementation Details

### Image Optimization Strategy

1. **WebP Support**: Uses existing `tripeak_test_seven_get_webp_image_url()` function to serve WebP when available
2. **Responsive Images**: Multiple sizes (small: 300w, medium: 400w, large: 600w)
3. **Lazy Loading Strategy**:
   - Hero images: `fetchpriority="high"`, `decoding="sync"` (LCP optimization)
   - Single post featured images: `fetchpriority="high"`, `decoding="sync"` (LCP optimization)
   - Card images: `loading="lazy"`, `decoding="async"` (below-the-fold)
   - Archive featured images: `loading="lazy"`, `decoding="async"` (below-the-fold)

### Resource Loading Strategy

1. **Critical Resources** (loaded immediately):
   - Main stylesheet
   - Preconnect links for external domains
   - Hero image preload on front page

2. **Deferred Resources** (non-blocking):
   - Google Fonts (preload + async)
   - Font Awesome (preload + async)
   - JavaScript files (defer attribute)

### Picture Element Structure

```html
<picture>
    <source type="image/webp" 
            srcset="image-300.webp 300w, image-400.webp 400w, image-600.webp 600w"
            sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw">
    <source type="image/jpeg" 
            srcset="image-300.jpg 300w, image-400.jpg 400w, image-600.jpg 600w"
            sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw">
    <img src="image-400.jpg" 
         alt="Description"
         loading="lazy"
         decoding="async"
         width="400"
         height="300" />
</picture>
```

## Expected Performance Improvements

1. **LCP (Largest Contentful Paint)**: Significantly improved by making hero image discoverable and adding fetchpriority
2. **Image Delivery**: ~20-30% file size reduction with WebP format
3. **Render Blocking**: Eliminated render-blocking CSS/JS for faster initial paint
4. **Layout Stability**: Proper width/height attributes prevent CLS (Cumulative Layout Shift)

## Testing Recommendations

1. Test on PageSpeed Insights: https://pagespeed.web.dev/
2. Clear all caches (browser, WordPress, CDN if applicable)
3. Test on both mobile and desktop
4. Verify WebP images are being served (check Network tab in DevTools)
5. Verify hero image loads with high priority (check Network tab, Priority column)

## Compatibility Notes

- All changes maintain existing design (no visual changes)
- WebP fallback to JPEG/PNG for older browsers
- No JavaScript required for image display (progressive enhancement)
- Fully backward compatible with WordPress core functions

## Cache Considerations

**Note**: Cache headers were NOT modified per user request. For maximum performance gains, consider implementing cache headers at the server or CDN level:
- Static assets (images, CSS, JS): 1 year cache
- HTML pages: 1 hour cache with revalidation

## Future Enhancements

Consider these additional optimizations:
1. Implement critical CSS inlining for above-the-fold content
2. Add service worker for offline caching
3. Implement image CDN for automatic optimization
4. Consider lazy loading for iframes and videos
5. Implement font subsetting for Google Fonts

