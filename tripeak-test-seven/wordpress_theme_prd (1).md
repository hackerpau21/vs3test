# MVP WordPress Theme PRD - Maria Orozova Inspired

## Project Overview

**Project Name:** Timeless Blog Theme  
**Target:** Blog-style websites with professional, elegant aesthetic  
**Development Approach:** Vibe coding with modern, clean design principles  
**Design Inspiration:** Maria Orozova website (mariaorozova.com)

## Design Philosophy

- **Timeless**: Clean, minimalist design that won't feel dated
- **Professional**: Sophisticated aesthetic suitable for personal brands, consultants, and thought leaders
- **Elegant**: Refined typography and spacious layouts
- **Responsive**: Mobile-first approach with seamless experience across devices

## Core Features & Components

### 1. Header & Hero Section

**Header Navigation:**
- Clean, minimal header with logo placement on the left
- Horizontal navigation menu (right-aligned)
- Social media icons (LinkedIn, Instagram, etc.) in header
- Sticky header behavior on scroll
- Mobile hamburger menu

**Hero Section:**
- Full-viewport height background image
- Overlay text with tagline/description
- Typography: Large, elegant serif or clean sans-serif
- Text positioning: Centered or left-aligned over image
- Subtle overlay for text readability
- Responsive image scaling

### 2. Content Sections

**Image Card Grid (Gutenberg Block):**
- 3-column grid layout (responsive to 1 column on mobile)
- Each card contains:
  - Featured image with subtle hover effects
  - Category/tag label
  - Heading (H3)
  - Body text excerpt
  - "Learn More" or "Read More" button
- Consistent spacing and alignment
- Clean borders or subtle shadows

**Additional Content Blocks:**
- Rich text blocks with elegant typography
- Quote blocks with distinctive styling
- Gallery blocks with lightbox functionality
- Call-to-action sections

### 3. Blog Functionality

**Blog Post Layout:**
- Clean, readable post format
- Featured image at top
- Author bio section
- Related posts suggestions
- Social sharing buttons
- Comment system integration

**Blog Index:**
- Card-based layout similar to homepage
- Pagination or infinite scroll
- Category filtering
- Search functionality

## Technical Requirements

### WordPress Integration
- **Version:** WordPress 6.0+
- **Gutenberg:** Full block editor support
- **Customizer:** Theme options panel
- **Custom Post Types:** Ready for CPT plugins

### Gutenberg Blocks (Custom)
1. **Hero Section Block**
   - Background image upload
   - Text overlay editor
   - Alignment options
   - Color controls

2. **Image Card Grid Block**
   - Repeater for multiple cards
   - Image upload for each card
   - Text fields (title, description, button)
   - Link/URL options
   - Grid layout options (2-col, 3-col, 4-col)

3. **Quote Block (Enhanced)**
   - Author attribution
   - Styling options
   - Large quote marks

### Design Specifications

**Color Palette:**
- Primary: Deep charcoal (#2D2D2D)
- Secondary: Warm red/coral (#E74C3C or similar)
- Accent: Light blue/teal (#3498DB)
- Neutral: Light gray (#F8F9FA)
- Text: Dark gray (#333333)

**Typography:**
- **Headers:** Elegant serif (like Playfair Display) or clean sans-serif (like Montserrat)
- **Body:** Readable sans-serif (like Open Sans or Source Sans Pro)
- **Hierarchy:** Clear H1-H6 styling with appropriate spacing

**Spacing & Layout:**
- Consistent grid system (12-column)
- Generous white space
- Maximum content width: 1200px
- Responsive breakpoints: 768px, 1024px, 1440px

## User Experience Features

### Navigation
- Smooth scroll to sections
- Breadcrumb navigation
- Search functionality
- Category/tag navigation

### Performance
- Optimized images with lazy loading
- Minified CSS/JS
- Fast loading times (<3 seconds)
- SEO-friendly markup

### Accessibility
- WCAG 2.1 AA compliance
- Keyboard navigation
- Screen reader compatible
- Color contrast compliance

## Content Management

### Theme Options (Customizer)
- Logo upload
- Color scheme customization
- Typography choices
- Social media links
- Footer content
- Contact information

### Widget Areas
- Header widget area
- Footer widget areas (3-4 columns)
- Sidebar widget area
- Post sidebar

## Development Phases

### Phase 1: Core Structure (MVP)
- [ ] Basic header with navigation
- [ ] Hero section with background image
- [ ] Image card grid block
- [ ] Basic blog post layout
- [ ] Mobile responsiveness

### Phase 2: Enhanced Features
- [ ] Advanced Gutenberg blocks
- [ ] Theme customization options
- [ ] Performance optimizations
- [ ] SEO enhancements

### Phase 3: Polish & Extras
- [ ] Advanced animations
- [ ] Additional layout options
- [ ] Advanced customizer options
- [ ] Plugin integrations

## File Structure

```
timeless-blog-theme/
├── style.css
├── index.php
├── functions.php
├── header.php
├── footer.php
├── single.php
├── page.php
├── archive.php
├── search.php
├── 404.php
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── inc/
│   ├── customizer.php
│   ├── theme-options.php
│   └── gutenberg-blocks.php
├── template-parts/
│   ├── header/
│   ├── footer/
│   └── content/
└── blocks/
    ├── hero-section/
    ├── image-card-grid/
    └── enhanced-quote/
```

## Success Metrics

- **Loading Speed:** <3 seconds on mobile
- **Mobile Score:** 90+ on Google PageSpeed
- **Accessibility:** WCAG 2.1 AA compliance
- **SEO:** Structured data implementation
- **User Experience:** Intuitive navigation and content discovery

## Design References

- **Primary Inspiration:** Maria Orozova website
- **Typography:** Modern, professional styling
- **Layout:** Clean, spacious design with strategic use of white space
- **Color Scheme:** Professional palette with strategic accent colors
- **Imagery:** High-quality, impactful visuals with proper aspect ratios

## Future Enhancements

- Dark mode toggle
- Advanced portfolio layouts
- Integration with popular plugins (Contact Form 7, Yoast SEO)
- Additional Gutenberg blocks
- Multi-language support
- Advanced customization options

---

*This PRD serves as the foundation for developing a timeless, professional WordPress theme suitable for blog-style websites with a focus on elegant design and user experience.*