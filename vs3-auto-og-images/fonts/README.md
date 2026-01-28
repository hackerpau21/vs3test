# Fonts Directory

## Purpose

This directory is used to store TrueType Font (TTF) files for consistent OG image generation across all servers.

## Required Fonts

For best results, add these fonts to this directory:

- **Arial.ttf** (or **arial.ttf**) - Regular weight
- **Arial-Bold.ttf** (or **arial-bold.ttf**) - Bold weight

## Alternative Fonts

You can also use these alternative fonts:

- **Arial-Regular.ttf** - Regular weight
- **ArialBold.ttf** - Bold weight
- **LiberationSans-Regular.ttf** - Open source alternative
- **LiberationSans-Bold.ttf** - Open source alternative (bold)

## How It Works

The plugin will look for fonts in this order:

1. **System fonts** (Arial, Helvetica, DejaVu Sans from OS)
2. **Plugin fonts** (fonts in this directory)
3. **Built-in fonts** (GD library fallback - scaled for larger size)

## Font Diagnostics

To check which font is being used:

1. Go to **Settings → Auto OG Images**
2. Scroll to the **Font Diagnostics** section
3. You'll see which font file is detected and being used

## Obtaining Arial Fonts

### Free Alternative (Recommended for Production)

**Liberation Sans** is a free, open-source alternative to Arial:
- Download from: https://github.com/liberationfonts/liberation-fonts
- Or use package manager on Linux: `apt-get install fonts-liberation`

### From Your Computer

If you have Arial installed on your computer:

**Windows:**
- Copy from: `C:\Windows\Fonts\arial.ttf` and `C:\Windows\Fonts\arialbd.ttf`

**macOS:**
- Copy from: `/System/Library/Fonts/Arial.ttf`

**Note:** Arial is a commercial font by Microsoft. Ensure you have proper licensing for your use case.

## File Permissions

Make sure the fonts directory has proper read permissions:

```bash
chmod 755 fonts/
chmod 644 fonts/*.ttf
```

## Testing

After adding fonts:

1. Go to **Settings → Auto OG Images**
2. Click **Clear All OG Images**
3. Visit a post without a featured image
4. The OG image should now use your TTF fonts

## Current Status

Check your **Font Diagnostics** on the settings page to see if fonts are properly detected!

