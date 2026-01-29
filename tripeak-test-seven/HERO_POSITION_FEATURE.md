# Hero Text Position Feature

## Overview
Added a customizer option to control the vertical positioning of the hero section title and subtitle text.

## Feature Details

### Customizer Option
**Location:** Appearance → Customize → Hero Section → Hero Text Position

**Options:**
1. **Bottom Center (Default)** - Text appears centered horizontally at the bottom of the hero section
2. **Center Center** - Text appears centered both horizontally and vertically in the hero section

### Implementation Details

#### Files Modified

1. **functions.php**
   - Added `hero_text_position` customizer setting with select dropdown
   - Added `tripeak_test_seven_sanitize_hero_position()` sanitization callback
   - Default value: `bottom-center`

2. **front-page.php**
   - Retrieves hero position setting: `get_theme_mod('hero_text_position', 'bottom-center')`
   - Applies dynamic class to hero section: `hero-position-{position}`

3. **index.php**
   - Same implementation as front-page.php for consistency

4. **style.css**
   - Added `.hero-position-bottom-center` class (align-items: flex-end)
   - Added `.hero-position-center-center` class (align-items: center)
   - Removes bottom margin on centered text for proper centering

### CSS Classes Applied

#### Bottom Center (Default)
```css
.hero-section.hero-position-bottom-center {
    align-items: flex-end;
}
```

#### Center Center
```css
.hero-section.hero-position-center-center {
    align-items: center;
}

.hero-section.hero-position-center-center .hero-content {
    margin-bottom: 0;
}
```

### How to Use

1. Go to **Appearance → Customize**
2. Navigate to **Hero Section**
3. Find **Hero Text Position** dropdown
4. Select your preferred position:
   - **Bottom Center (Default)** - Keep text at bottom
   - **Center Center** - Move text to vertical center
5. Click **Publish** to save changes

### Technical Notes

- The position is stored as a theme mod: `hero_text_position`
- Valid values are sanitized to prevent invalid inputs
- The feature uses flexbox alignment for responsive positioning
- Works on both front-page.php and index.php templates
- Compatible with all existing hero section customizations (colors, background, text)

### Backwards Compatibility

- Default position is `bottom-center` (current behavior)
- Existing sites will automatically use the default position
- No breaking changes to existing functionality

