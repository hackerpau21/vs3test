<?php
/**
 * Test Theme 7 - Timeless Blog Style functions and definitions
 *
 * @package tripeak-test-seven
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function tripeak_test_seven_setup() {
    // Add default posts and comments RSS feed links to head
    add_theme_support('automatic-feed-links');

    // Let WordPress manage the document title
    add_theme_support('title-tag');

    // Enable support for Post Thumbnails on posts and pages
    add_theme_support('post-thumbnails');

    // Add theme support for selective refresh for widgets
    add_theme_support('customize-selective-refresh-widgets');

    // Add support for core custom logo
    add_theme_support('custom-logo', array(
        'height'      => 250,
        'width'       => 250,
        'flex-width'  => true,
        'flex-height' => true,
    ));

    // Add support for HTML5 markup
    add_theme_support('html5', array(
        'search-form',
        'gallery',
        'caption',
        'style',
        'script',
    ));

    // Add support for responsive embedded content
    add_theme_support('responsive-embeds');

    // Add support for Gutenberg wide and full alignment
    add_theme_support('align-wide');

    // Add support for editor styles
    add_theme_support('editor-styles');
    
    // Add editor style
    add_editor_style('assets/css/editor-style.css');

    // Add support for Gutenberg color palette
    add_theme_support('editor-color-palette', array(
        array(
            'name'  => __('Primary Dark', 'tripeak-test-seven'),
            'slug'  => 'primary-dark',
            'color' => '#2D2D2D',
        ),
        array(
            'name'  => __('Coral Red', 'tripeak-test-seven'),
            'slug'  => 'coral-red',
            'color' => '#E74C3C',
        ),
        array(
            'name'  => __('Light Blue', 'tripeak-test-seven'),
            'slug'  => 'light-blue',
            'color' => '#3498DB',
        ),
        array(
            'name'  => __('Light Gray', 'tripeak-test-seven'),
            'slug'  => 'light-gray',
            'color' => '#F8F9FA',
        ),
        array(
            'name'  => __('Text Gray', 'tripeak-test-seven'),
            'slug'  => 'text-gray',
            'color' => '#333333',
        ),
    ));

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'tripeak-test-seven'),
        'footer'  => __('Footer Menu', 'tripeak-test-seven'),
    ));

    // Set content width
    if (!isset($content_width)) {
        $content_width = 800;
    }
}
add_action('after_setup_theme', 'tripeak_test_seven_setup');

/**
 * Display category buttons as clickable links
 */
function tripeak_test_seven_category_buttons() {
    $show_category_buttons = get_theme_mod('posts_page_category_filter_enable', true);
    
    if (!$show_category_buttons || (!is_home() && !is_archive())) {
        return;
    }
    
    $categories = get_categories(array('hide_empty' => true));
    
    if (empty($categories)) {
        return;
    }
    
    // Get current category ID if on category archive
    $current_category_id = is_category() ? get_queried_object_id() : 0;
    
    // Get blog page URL for "All" button
    $posts_page_id = get_option('page_for_posts');
    if ($posts_page_id) {
        $blog_url = get_permalink($posts_page_id);
    } else {
        $blog_url = home_url('/');
    }
    
    echo '<div class="category-filter-wrapper">';
    echo '<div class="category-filter-buttons">';
    
    // "All" button - active when not on a category archive
    $all_active = !is_category() ? 'active' : '';
    echo '<a href="' . esc_url($blog_url) . '" class="category-filter-btn ' . esc_attr($all_active) . '">';
    echo esc_html__('All', 'tripeak-test-seven');
    echo '</a>';
    
    // Category buttons
    foreach ($categories as $category) {
        $category_url = get_category_link($category->term_id);
        $is_active = ($current_category_id == $category->term_id) ? 'active' : '';
        
        echo '<a href="' . esc_url($category_url) . '" class="category-filter-btn ' . esc_attr($is_active) . '">';
        echo esc_html($category->name);
        echo '</a>';
    }
    
    echo '</div>';
    echo '</div>';
}

/**
 * Disable comments globally
 */
function tripeak_test_seven_disable_comments() {
    // Close comments on all post types
    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
}
add_action('admin_init', 'tripeak_test_seven_disable_comments');

// Remove comments page from admin menu
function tripeak_test_seven_remove_comments_menu() {
    remove_menu_page('edit-comments.php');
}
add_action('admin_menu', 'tripeak_test_seven_remove_comments_menu');

// Remove comments from admin bar
function tripeak_test_seven_remove_comments_admin_bar($wp_admin_bar) {
    $wp_admin_bar->remove_node('comments');
}
add_action('admin_bar_menu', 'tripeak_test_seven_remove_comments_admin_bar', 999);

// Disable comments feed
add_filter('feed_links_show_comments_feed', '__return_false');

// Remove comment support from theme
function tripeak_test_seven_remove_comment_support() {
    remove_theme_support('comments');
    remove_theme_support('trackbacks');
}
add_action('after_setup_theme', 'tripeak_test_seven_remove_comment_support', 100);

/**
 * Enqueue scripts and styles with performance optimizations
 */
function tripeak_test_seven_scripts() {
    // Get current version for cache busting
    $theme_version = wp_get_theme()->get('Version') ?: '1.0.0';
    
    // Enqueue main stylesheet (critical CSS is inlined)
    wp_enqueue_style('tripeak-test-seven-style', get_stylesheet_uri(), array(), $theme_version);

    // Fonts - deferred loading via preload in head
    wp_enqueue_style('tripeak-test-seven-fonts', 'https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Source+Sans+Pro:wght@300;400;500;600&display=swap', array(), null);

    // Font Awesome - deferred loading
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');

    // Enqueue main JavaScript with defer
    wp_enqueue_script('tripeak-test-seven-script', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), $theme_version, true);

    // Defer button enhancements JavaScript (non-critical)
    wp_enqueue_script('tripeak-test-seven-button-enhancements', get_template_directory_uri() . '/assets/js/button-enhancements.js', array(), $theme_version, true);

    // Comments disabled - no comment-reply script needed
    
    // Add performance-related script variables
    wp_localize_script('tripeak-test-seven-script', 'triPeakPerf', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tripeak_perf_nonce'),
        'isTouch' => wp_is_mobile() ? 'true' : 'false',
        'webpSupport' => function_exists('imagewebp') ? 'true' : 'false'
    ));
}
add_action('wp_enqueue_scripts', 'tripeak_test_seven_scripts');

/**
 * Register widget areas
 */
function tripeak_test_seven_widgets_init() {
    register_sidebar(array(
        'name'          => __('Header Widget Area', 'tripeak-test-seven'),
        'id'            => 'header-widget-area',
        'description'   => __('Add widgets here to appear in your header.', 'tripeak-test-seven'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widget Area 1', 'tripeak-test-seven'),
        'id'            => 'footer-1',
        'description'   => __('Add widgets here to appear in your footer.', 'tripeak-test-seven'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widget Area 2', 'tripeak-test-seven'),
        'id'            => 'footer-2',
        'description'   => __('Add widgets here to appear in your footer.', 'tripeak-test-seven'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Widget Area 3', 'tripeak-test-seven'),
        'id'            => 'footer-3',
        'description'   => __('Add widgets here to appear in your footer.', 'tripeak-test-seven'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('Sidebar', 'tripeak-test-seven'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here to appear in your sidebar.', 'tripeak-test-seven'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'tripeak_test_seven_widgets_init');

/**
 * Customizer additions
 */
function tripeak_test_seven_customize_register($wp_customize) {
    // Add Theme Colors section
    $wp_customize->add_section('theme_colors', array(
        'title'    => __('Theme Colors', 'tripeak-test-seven'),
        'priority' => 20,
    ));

    // Primary Colors
    $wp_customize->add_setting('primary_color', array(
        'default'           => '#E74C3C',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'primary_color', array(
        'label'   => __('Primary Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Main brand color used for buttons, links, and highlights', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('primary_hover_color', array(
        'default'           => '#C0392B',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'primary_hover_color', array(
        'label'   => __('Primary Hover Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for primary elements on hover', 'tripeak-test-seven'),
    )));

    // Secondary Colors
    $wp_customize->add_setting('secondary_color', array(
        'default'           => '#3498DB',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'secondary_color', array(
        'label'   => __('Secondary Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Secondary brand color for accents and highlights', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('secondary_hover_color', array(
        'default'           => '#2980B9',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'secondary_hover_color', array(
        'label'   => __('Secondary Hover Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for secondary elements on hover', 'tripeak-test-seven'),
    )));

    // Text Colors
    $wp_customize->add_setting('heading_color', array(
        'default'           => '#2D2D2D',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'heading_color', array(
        'label'   => __('Heading Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for all headings (h1, h2, h3, etc.)', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('body_text_color', array(
        'default'           => '#2D2D2D',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'body_text_color', array(
        'label'   => __('Body Text Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for body text and paragraphs', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('text_light_color', array(
        'default'           => '#666',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'text_light_color', array(
        'label'   => __('Light Text Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Secondary text color for meta information and descriptions', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('text_white_color', array(
        'default'           => '#fff',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'text_white_color', array(
        'label'   => __('White Text Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Text color for light backgrounds', 'tripeak-test-seven'),
    )));

    // Hero Section Colors
    $wp_customize->add_setting('hero_heading_color', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'hero_heading_color', array(
        'label'   => __('Hero Heading Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for headings in the hero section', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('hero_text_color', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'hero_text_color', array(
        'label'   => __('Hero Text Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for text in the hero section', 'tripeak-test-seven'),
    )));

    // Footer Colors
    $wp_customize->add_setting('footer_heading_color', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_heading_color', array(
        'label'   => __('Footer Heading Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for headings in the footer', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('footer_text_color', array(
        'default'           => '#cccccc',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_text_color', array(
        'label'   => __('Footer Text Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for text in the footer', 'tripeak-test-seven'),
    )));

    // Background Colors
    $wp_customize->add_setting('bg_light_color', array(
        'default'           => '#F8F9FA',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'bg_light_color', array(
        'label'   => __('Light Background Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Light background color for sections and cards', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('bg_white_color', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'bg_white_color', array(
        'label'   => __('White Background Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Main background color for content areas', 'tripeak-test-seven'),
    )));

    // Border Colors
    $wp_customize->add_setting('border_color', array(
        'default'           => '#E5E5E5',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'border_color', array(
        'label'   => __('Border Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for borders and dividers', 'tripeak-test-seven'),
    )));

    // Footer Colors
    $wp_customize->add_setting('footer_bg_color', array(
        'default'           => '#2D2D2D',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_bg_color', array(
        'label'   => __('Footer Background Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Background color for the footer section', 'tripeak-test-seven'),
    )));

    // Button Colors
    $wp_customize->add_setting('button_primary_bg', array(
        'default'           => '#E74C3C',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'button_primary_bg', array(
        'label'   => __('Primary Button Background', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Background color for primary buttons', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('button_primary_hover_bg', array(
        'default'           => '#C0392B',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'button_primary_hover_bg', array(
        'label'   => __('Primary Button Hover Background', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Background color for primary buttons on hover', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('button_secondary_bg', array(
        'default'           => '#3498DB',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'button_secondary_bg', array(
        'label'   => __('Secondary Button Background', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Background color for secondary buttons', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('button_secondary_hover_bg', array(
        'default'           => '#2980B9',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'button_secondary_hover_bg', array(
        'label'   => __('Secondary Button Hover Background', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Background color for secondary buttons on hover', 'tripeak-test-seven'),
    )));

    // Card Colors
    $wp_customize->add_setting('card_bg_color', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'card_bg_color', array(
        'label'   => __('Card Background Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Background color for card elements', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('card_hover_bg_color', array(
        'default'           => '#f8f9fa',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'card_hover_bg_color', array(
        'label'   => __('Card Hover Background Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Background color for cards on hover', 'tripeak-test-seven'),
    )));

    // Header Colors
    $wp_customize->add_setting('header_bg_color', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_bg_color', array(
        'label'   => __('Header Background Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Background color for the header', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('header_text_color', array(
        'default'           => '#2D2D2D',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_text_color', array(
        'label'   => __('Header Text Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Text color for header elements', 'tripeak-test-seven'),
    )));

    // Navigation Colors
    $wp_customize->add_setting('nav_link_color', array(
        'default'           => '#2D2D2D',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_link_color', array(
        'label'   => __('Navigation Link Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for navigation menu links', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('nav_link_hover_color', array(
        'default'           => '#E74C3C',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'nav_link_hover_color', array(
        'label'   => __('Navigation Link Hover Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for navigation menu links on hover', 'tripeak-test-seven'),
    )));

    // Social Links Colors
    $wp_customize->add_setting('social_link_color', array(
        'default'           => '#2D2D2D',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'social_link_color', array(
        'label'   => __('Social Link Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for social media links', 'tripeak-test-seven'),
    )));

    $wp_customize->add_setting('social_link_hover_color', array(
        'default'           => '#E74C3C',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'social_link_hover_color', array(
        'label'   => __('Social Link Hover Color', 'tripeak-test-seven'),
        'section' => 'theme_colors',
        'description' => __('Color for social media links on hover', 'tripeak-test-seven'),
    )));

    // Add social media section
    $wp_customize->add_section('social_media', array(
        'title'    => __('Social Media Links', 'tripeak-test-seven'),
        'priority' => 30,
    ));

    // Add social media settings
    $social_media = array(
        'linkedin' => 'LinkedIn',
        'instagram' => 'Instagram',
        'twitter' => 'Twitter',
        'facebook' => 'Facebook',
        'youtube' => 'YouTube',
        'tiktok' => 'TikTok',
        'patronview' => 'PatronView',
        'email' => 'Email',
    );

    foreach ($social_media as $platform => $label) {
        $wp_customize->add_setting("social_media_{$platform}", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ));

        $wp_customize->add_control("social_media_{$platform}", array(
            'label'   => $label . ' URL',
            'section' => 'social_media',
            'type'    => 'url',
        ));
    }

    // Add hero section
    $wp_customize->add_section('hero_section', array(
        'title'    => __('Hero Section', 'tripeak-test-seven'),
        'priority' => 25,
    ));

    // Hero background image
    $wp_customize->add_setting('hero_background_image', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hero_background_image', array(
        'label'   => __('Hero Background Image', 'tripeak-test-seven'),
        'section' => 'hero_section',
    )));

    // Hero title
    $wp_customize->add_setting('hero_title', array(
        'default' => __('Welcome to Our Blog', 'tripeak-test-seven'),
        'sanitize_callback' => 'wp_kses_post',
    ));

    $wp_customize->add_control('hero_title', array(
        'label'       => __('Hero Title', 'tripeak-test-seven'),
        'section'     => 'hero_section',
        'type'        => 'textarea',
        'description' => __('You can use line breaks and basic HTML formatting (strong, em, etc.)', 'tripeak-test-seven'),
    ));

    // Hero description
    $wp_customize->add_setting('hero_description', array(
        'default' => __('Timeless stories and insights for the modern world', 'tripeak-test-seven'),
        'sanitize_callback' => 'wp_kses_post',
    ));

    $wp_customize->add_control('hero_description', array(
        'label'       => __('Hero Description', 'tripeak-test-seven'),
        'section'     => 'hero_section',
        'type'        => 'textarea',
        'description' => __('You can use line breaks and basic HTML formatting (strong, em, etc.)', 'tripeak-test-seven'),
    ));

    // Hero text position
    $wp_customize->add_setting('hero_text_position', array(
        'default' => 'bottom-center',
        'sanitize_callback' => 'tripeak_test_seven_sanitize_hero_position',
    ));

    $wp_customize->add_control('hero_text_position', array(
        'label'       => __('Hero Text Position', 'tripeak-test-seven'),
        'section'     => 'hero_section',
        'type'        => 'select',
        'choices'     => array(
            'bottom-center' => __('Bottom Center (Default)', 'tripeak-test-seven'),
            'center-center' => __('Center Center', 'tripeak-test-seven'),
        ),
        'description' => __('Choose where to display the hero title and subtitle', 'tripeak-test-seven'),
    ));

    // Homepage Article Loop (Featured Stories) Section
    $wp_customize->add_section('homepage_article_loop', array(
        'title'    => __('Homepage Article Loop', 'tripeak-test-seven'),
        'priority' => 35,
    ));

    // Section Header Text
    $wp_customize->add_setting('homepage_article_loop_header', array(
        'default'           => __('Featured Stories', 'tripeak-test-seven'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('homepage_article_loop_header', array(
        'label'   => __('Section Header Text', 'tripeak-test-seven'),
        'section' => 'homepage_article_loop',
        'type'    => 'text',
    ));

    // Section Body Text
    $wp_customize->add_setting('homepage_article_loop_body', array(
        'default'           => __('Discover our latest insights and perspectives', 'tripeak-test-seven'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('homepage_article_loop_body', array(
        'label'   => __('Section Body Text', 'tripeak-test-seven'),
        'section' => 'homepage_article_loop',
        'type'    => 'text',
    ));

    // Show/Hide Card Images
    $wp_customize->add_setting('homepage_article_loop_show_images', array(
        'default'           => true,
        'sanitize_callback' => 'tripeak_test_seven_sanitize_checkbox',
    ));
    $wp_customize->add_control('homepage_article_loop_show_images', array(
        'label'   => __('Show Card Images', 'tripeak-test-seven'),
        'section' => 'homepage_article_loop',
        'type'    => 'checkbox',
    ));

    // Number of Posts to Show
    $wp_customize->add_setting('homepage_article_loop_num_posts', array(
        'default'           => 6,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('homepage_article_loop_num_posts', array(
        'label'   => __('Number of Posts to Show', 'tripeak-test-seven'),
        'section' => 'homepage_article_loop',
        'type'    => 'number',
        'input_attrs' => array('min' => 1, 'max' => 12),
    ));

    // Category Selection for Homepage Article Loop
    $wp_customize->add_setting('homepage_article_loop_category', array(
        'default'           => 0,
        'sanitize_callback' => 'absint',
    ));
    
    // Get all categories for dropdown
    $categories = get_categories(array('hide_empty' => false));
    $category_options = array(0 => __('All Categories', 'tripeak-test-seven'));
    foreach ($categories as $category) {
        $category_options[$category->term_id] = $category->name . ' (' . $category->count . ')';
    }
    
    $wp_customize->add_control('homepage_article_loop_category', array(
        'label'   => __('Filter by Category', 'tripeak-test-seven'),
        'section' => 'homepage_article_loop',
        'type'    => 'select',
        'choices' => $category_options,
        'description' => __('Select a category to show only posts from that category. Select "All Categories" to show all posts.', 'tripeak-test-seven'),
    ));

    // Posts Page Category Filter Section
    $wp_customize->add_section('posts_page_category_filter', array(
        'title'    => __('Posts Page Category Filter', 'tripeak-test-seven'),
        'priority' => 36,
    ));

    // Enable/Disable Category Filter Buttons
    $wp_customize->add_setting('posts_page_category_filter_enable', array(
        'default'           => true,
        'sanitize_callback' => 'tripeak_test_seven_sanitize_checkbox',
    ));
    $wp_customize->add_control('posts_page_category_filter_enable', array(
        'label'   => __('Enable Category Filter Buttons', 'tripeak-test-seven'),
        'section' => 'posts_page_category_filter',
        'type'    => 'checkbox',
        'description' => __('Show category filter buttons above posts on the blog/posts page', 'tripeak-test-seven'),
    ));

    // Footer Section
    $wp_customize->add_section('footer_section', array(
        'title'    => __('Footer', 'tripeak-test-seven'),
        'priority' => 40,
    ));

    // Footer Menu Header
    $wp_customize->add_setting('footer_menu_header', array(
        'default'           => __('Quick Links', 'tripeak-test-seven'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('footer_menu_header', array(
        'label'   => __('Footer Menu Header', 'tripeak-test-seven'),
        'section' => 'footer_section',
        'type'    => 'text',
    ));

    // Footer Recent Posts Toggle
    $wp_customize->add_setting('footer_recent_posts_enable', array(
        'default'           => true,
        'sanitize_callback' => 'tripeak_test_seven_sanitize_checkbox',
    ));
    $wp_customize->add_control('footer_recent_posts_enable', array(
        'label'   => __('Show Recent Posts', 'tripeak-test-seven'),
        'section' => 'footer_section',
        'type'    => 'checkbox',
    ));

    // Footer Recent Posts Header
    $wp_customize->add_setting('footer_recent_posts_header', array(
        'default'           => __('Recent Posts', 'tripeak-test-seven'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('footer_recent_posts_header', array(
        'label'   => __('Recent Posts Header', 'tripeak-test-seven'),
        'section' => 'footer_section',
        'type'    => 'text',
    ));

    // Footer Recent Posts Number
    $wp_customize->add_setting('footer_recent_posts_number', array(
        'default'           => 3,
        'sanitize_callback' => 'absint',
    ));
    $wp_customize->add_control('footer_recent_posts_number', array(
        'label'   => __('Number of Recent Posts', 'tripeak-test-seven'),
        'section' => 'footer_section',
        'type'    => 'number',
        'input_attrs' => array('min' => 1, 'max' => 6),
    ));

    // Footer Social Links Toggle
    $wp_customize->add_setting('footer_social_links_enable', array(
        'default'           => true,
        'sanitize_callback' => 'tripeak_test_seven_sanitize_checkbox',
    ));
    $wp_customize->add_control('footer_social_links_enable', array(
        'label'   => __('Show Social Links', 'tripeak-test-seven'),
        'section' => 'footer_section',
        'type'    => 'checkbox',
    ));

    // Footer Social Links Header
    $wp_customize->add_setting('footer_social_links_header', array(
        'default'           => __('Connect', 'tripeak-test-seven'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('footer_social_links_header', array(
        'label'   => __('Social Links Header', 'tripeak-test-seven'),
        'section' => 'footer_section',
        'type'    => 'text',
    ));

    // Footer Powered By Text Toggle
    $wp_customize->add_setting('footer_powered_by_enable', array(
        'default'           => true,
        'sanitize_callback' => 'tripeak_test_seven_sanitize_checkbox',
    ));
    $wp_customize->add_control('footer_powered_by_enable', array(
        'label'       => __('Show "Powered by PersonalWebsites.org" Text', 'tripeak-test-seven'),
        'section'     => 'footer_section',
        'type'        => 'checkbox',
        'description' => __('Display powered by text after custom footer text', 'tripeak-test-seven'),
    ));
}
add_action('customize_register', 'tripeak_test_seven_customize_register');

// Checkbox sanitization callback
function tripeak_test_seven_sanitize_checkbox($checked) {
    return (isset($checked) && true == $checked) ? true : false;
}

// Hero position sanitization callback
function tripeak_test_seven_sanitize_hero_position($position) {
    $valid_positions = array('bottom-center', 'center-center');
    return in_array($position, $valid_positions) ? $position : 'bottom-center';
}

/**
 * Add custom image sizes
 */
function tripeak_test_seven_image_sizes() {
    add_image_size('card-image', 400, 300, true);
    add_image_size('hero-image', 1920, 1080, true);
}
add_action('after_setup_theme', 'tripeak_test_seven_image_sizes');

/**
 * Custom excerpt length
 */
function tripeak_test_seven_excerpt_length($length) {
    return 25;
}
add_filter('excerpt_length', 'tripeak_test_seven_excerpt_length');

/**
 * Custom excerpt more
 */
function tripeak_test_seven_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'tripeak_test_seven_excerpt_more');

/**
 * Template functions
 */

/**
 * Get social media links
 * 
 * @param bool $show_names Whether to display platform names alongside icons
 */
function tripeak_test_seven_get_social_links($show_names = false) {
    $social_media = array(
        'linkedin' => array('icon' => 'fab fa-linkedin', 'name' => 'LinkedIn'),
        'github' => array('icon' => 'fab fa-github', 'name' => 'GitHub'),
        'instagram' => array('icon' => 'fab fa-instagram', 'name' => 'Instagram'),
        'twitter' => array('icon' => 'x-custom-svg', 'name' => 'X'), // Use custom SVG for X
        'facebook' => array('icon' => 'fab fa-facebook', 'name' => 'Facebook'),
        'youtube' => array('icon' => 'fab fa-youtube', 'name' => 'YouTube'),
        'tiktok' => array('icon' => 'fab fa-tiktok', 'name' => 'TikTok'),
        'patronview' => array('icon' => 'patronview-custom-logo', 'name' => 'Patron View'), // Use custom logo for PatronView
        'email' => array('icon' => 'fas fa-envelope', 'name' => 'Email'),
    );

    $social_links = '';
    foreach ($social_media as $platform => $data) {
        $url = get_theme_mod("social_media_{$platform}");
        if ($url) {
            if ($platform === 'twitter') {
                // Inline SVG for X (Twitter), sized to match icon fonts
                $svg = '<svg viewBox="0 0 120 120" width="1em" height="1em" fill="currentColor" aria-hidden="true" focusable="false" style="display:inline-block;vertical-align:middle;"><path d="M93.6 20H110L74.6 58.6L116 110H83.2L57.8 78.2L29.6 110H13.8L51.2 68.2L12 20H45.2L68.2 49.2L93.6 20ZM87.6 101.2H97.2L39.2 28.2H29.2L87.6 101.2Z"></path></svg>';
                $social_links .= sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer" aria-label="X">%s%s</a>',
                    esc_url($url),
                    $svg,
                    $show_names ? ' <span class="social-name">' . esc_html($data['name']) . '</span>' : ''
                );
            } elseif ($platform === 'patronview') {
                // Custom SVG for PatronView
                $svg_url = get_template_directory_uri() . '/pv.svg';
                $social_links .= sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer" aria-label="PatronView"><img src="%s" alt="' . get_bloginfo('name') . ' on Patron View donor profile" title="Visit my Patron View profile" style="width: 1em; height: 1em; vertical-align: middle; display: inline-block;">%s</a>',
                    esc_url($url),
                    esc_url($svg_url),
                    $show_names ? ' <span class="social-name">' . esc_html($data['name']) . '</span>' : ''
                );
            } elseif ($platform === 'email') {
                // Email link with mailto: prefix if not already present
                $email_url = $url;
                if (strpos($email_url, 'mailto:') !== 0) {
                    $email_url = 'mailto:' . $email_url;
                }
                $social_links .= sprintf(
                    '<a href="%s" aria-label="Email"><i class="%s"></i>%s</a>',
                    esc_attr($email_url),
                    esc_attr($data['icon']),
                    $show_names ? ' <span class="social-name">' . esc_html($data['name']) . '</span>' : ''
                );
            } else {
                $social_links .= sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s"><i class="%s"></i>%s</a>',
                    esc_url($url),
                    esc_attr($data['name']),
                    esc_attr($data['icon']),
                    $show_names ? ' <span class="social-name">' . esc_html($data['name']) . '</span>' : ''
                );
            }
        }
    }

    return $social_links;
}

/**
 * Get hero section data
 */
function tripeak_test_seven_get_hero_data() {
    return array(
        'background_image' => get_theme_mod('hero_background_image', ''),
        'title' => get_theme_mod('hero_title', __('Welcome to Our Blog', 'tripeak-test-seven')),
        'description' => get_theme_mod('hero_description', __('Timeless stories and insights for the modern world', 'tripeak-test-seven')),
    );
}

/**
 * Format text with line breaks
 * Converts newlines to <br> tags and allows safe HTML
 */
function tripeak_test_seven_format_text_with_breaks($text) {
    // Allow basic HTML tags for formatting
    $allowed_html = array(
        'br' => array(),
        'strong' => array(),
        'b' => array(),
        'em' => array(),
        'i' => array(),
        'span' => array(
            'class' => array(),
        ),
    );
    
    // Clean the text with allowed HTML
    $text = wp_kses($text, $allowed_html);
    
    // Convert newlines to <br> tags
    $text = nl2br($text);
    
    return $text;
}

/**
 * Include custom Gutenberg blocks
 */
function tripeak_test_seven_register_blocks() {
    // Register Card Grid block
    register_block_type('tripeak/card-grid', array(
        'editor_script' => 'tripeak-card-grid-block',
        'editor_style' => 'tripeak-card-grid-editor',
        'style' => 'tripeak-card-grid-frontend'
    ));
}
add_action('init', 'tripeak_test_seven_register_blocks');

/**
 * Enqueue Gutenberg block scripts
 */
function tripeak_test_seven_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'tripeak-card-grid-block',
        get_template_directory_uri() . '/assets/js/card-grid-block.js',
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
        filemtime(get_template_directory() . '/assets/js/card-grid-block.js'),
        false
    );
}
add_action('enqueue_block_editor_assets', 'tripeak_test_seven_enqueue_block_editor_assets');

/**
 * Add body classes
 */
function tripeak_test_seven_body_classes($classes) {
    // Add class of hfeed to non-singular pages
    if (!is_singular()) {
        $classes[] = 'hfeed';
    }

    // Add class if header image is set
    if (get_header_image()) {
        $classes[] = 'has-header-image';
    }

    // Add front-page class for easier targeting
    if (is_front_page()) {
        $classes[] = 'front-page';
    }

    // Add blog class for blog page
    if (is_home() && !is_front_page()) {
        $classes[] = 'blog';
    }

    return $classes;
}
add_filter('body_class', 'tripeak_test_seven_body_classes'); 

// Force blog page to show 18 posts per page
add_action('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query() && (is_home() || is_archive() || is_search())) {
        $query->set('posts_per_page', 18);
    }
}); 

/**
 * Output custom CSS for theme colors
 */
function tripeak_test_seven_custom_css() {
    $primary_color = get_theme_mod('primary_color', '#E74C3C');
    $primary_hover_color = get_theme_mod('primary_hover_color', '#C0392B');
    $secondary_color = get_theme_mod('secondary_color', '#3498DB');
    $secondary_hover_color = get_theme_mod('secondary_hover_color', '#2980B9');
    $heading_color = get_theme_mod('heading_color', '#2D2D2D');
    $body_text_color = get_theme_mod('body_text_color', '#2D2D2D');
    $text_light_color = get_theme_mod('text_light_color', '#666');
    $text_white_color = get_theme_mod('text_white_color', '#fff');
    $hero_heading_color = get_theme_mod('hero_heading_color', '#ffffff');
    $hero_text_color = get_theme_mod('hero_text_color', '#ffffff');
    $footer_heading_color = get_theme_mod('footer_heading_color', '#ffffff');
    $footer_text_color = get_theme_mod('footer_text_color', '#cccccc');
    $bg_light_color = get_theme_mod('bg_light_color', '#F8F9FA');
    $bg_white_color = get_theme_mod('bg_white_color', '#ffffff');
    $border_color = get_theme_mod('border_color', '#E5E5E5');
    $footer_bg_color = get_theme_mod('footer_bg_color', '#2D2D2D');
    $button_primary_bg = get_theme_mod('button_primary_bg', '#E74C3C');
    $button_primary_hover_bg = get_theme_mod('button_primary_hover_bg', '#C0392B');
    $button_secondary_bg = get_theme_mod('button_secondary_bg', '#3498DB');
    $button_secondary_hover_bg = get_theme_mod('button_secondary_hover_bg', '#2980B9');
    $card_bg_color = get_theme_mod('card_bg_color', '#ffffff');
    $card_hover_bg_color = get_theme_mod('card_hover_bg_color', '#f8f9fa');
    $header_bg_color = get_theme_mod('header_bg_color', '#ffffff');
    $header_text_color = get_theme_mod('header_text_color', '#2D2D2D');
    $nav_link_color = get_theme_mod('nav_link_color', '#2D2D2D');
    $nav_link_hover_color = get_theme_mod('nav_link_hover_color', '#E74C3C');
    $social_link_color = get_theme_mod('social_link_color', '#2D2D2D');
    $social_link_hover_color = get_theme_mod('social_link_hover_color', '#E74C3C');

    $custom_css = "
    :root {
        /* Colors */
        --primary-color: {$primary_color};
        --primary-hover: {$primary_hover_color};
        --secondary-color: {$secondary_color};
        --secondary-hover: {$secondary_hover_color};
        --heading-color: {$heading_color};
        --body-text-color: {$body_text_color};
        --text-light: {$text_light_color};
        --text-white: {$text_white_color};
        --bg-light: {$bg_light_color};
        --bg-white: {$bg_white_color};
        --border-color: {$border_color};
        --footer-bg: {$footer_bg_color};
        
        /* Button System Colors */
        --button-primary-bg: linear-gradient(135deg, {$button_primary_bg} 0%, " . tripeak_test_seven_adjust_brightness($button_primary_bg, -10) . " 100%);
        --button-primary-hover-bg: linear-gradient(135deg, {$button_primary_hover_bg} 0%, " . tripeak_test_seven_adjust_brightness($button_primary_hover_bg, -10) . " 100%);
        --button-secondary-bg: linear-gradient(135deg, {$button_secondary_bg} 0%, " . tripeak_test_seven_adjust_brightness($button_secondary_bg, -10) . " 100%);
        --button-secondary-hover-bg: linear-gradient(135deg, {$button_secondary_hover_bg} 0%, " . tripeak_test_seven_adjust_brightness($button_secondary_hover_bg, -10) . " 100%);
        --button-shadow: 0 4px 12px " . tripeak_test_seven_hex_to_rgba($button_primary_bg, 0.25) . ";
        --button-shadow-hover: 0 8px 25px " . tripeak_test_seven_hex_to_rgba($button_primary_bg, 0.35) . ";
    }

    /* Header Colors */
    .site-header {
        background: {$header_bg_color} !important;
    }

    .site-title a {
        color: {$header_text_color} !important;
    }

    .site-title a:hover {
        color: {$primary_color} !important;
    }

    /* Navigation Colors */
    .main-navigation a {
        color: {$nav_link_color} !important;
    }

    .main-navigation a:hover {
        color: {$nav_link_hover_color} !important;
    }

    /* Social Links Colors */
    .social-links a {
        color: {$social_link_color} !important;
    }

    .social-links a:hover {
        color: {$social_link_hover_color} !important;
    }

    /* Card Colors */
    .card {
        background: {$card_bg_color} !important;
    }

    .card:hover {
        background: {$card_hover_bg_color} !important;
    }

    /* Footer Colors */
    .site-footer {
        background: {$footer_bg_color} !important;
    }

    /* Button Colors */
    .btn {
        background: {$button_primary_bg} !important;
        color: {$text_white_color} !important;
        border: none !important;
        border-radius: var(--button-border-radius) !important;
        box-shadow: var(--button-shadow) !important;
    }

    .btn:hover {
        background: {$button_primary_hover_bg} !important;
        color: {$text_white_color} !important;
        transform: translateY(-2px) !important;
        box-shadow: var(--button-shadow-hover) !important;
    }

    .wp-block-button__link {
        background: var(--button-primary-bg) !important;
    }

    .wp-block-button__link:hover {
        background: var(--button-primary-hover-bg) !important;
    }

    .wp-block-button.is-style-secondary .wp-block-button__link {
        background: var(--button-secondary-bg) !important;
    }

    .wp-block-button.is-style-secondary .wp-block-button__link:hover {
        background: var(--button-secondary-hover-bg) !important;
    }

    /* Hero Section Colors */
    .hero-content h1 {
        color: {$hero_heading_color} !important;
    }

    .hero-content p {
        color: {$hero_text_color} !important;
    }

    /* Front page specific colors */
    .front-page .site-title a {
        color: {$text_white_color} !important;
    }

    .front-page .main-navigation a {
        color: {$text_white_color} !important;
    }

    .front-page .social-links a {
        color: {$text_white_color} !important;
    }

    /* Footer Colors */
    .site-footer h3 {
        color: {$footer_heading_color} !important;
    }

    .site-footer p,
    .site-footer .footer-text {
        color: {$footer_text_color} !important;
    }

    .site-footer a {
        color: {$footer_text_color} !important;
    }

    .site-footer a:hover {
        color: {$primary_color} !important;
    }

    /* Text Colors */
    body {
        color: {$body_text_color} !important;
    }

    h1, h2, h3, h4, h5, h6 {
        color: {$heading_color} !important;
    }

    .card h3,
    .card-title,
    .card h3 a {
        color: {$heading_color} !important;
    }

    .entry-content a {
        color: {$secondary_color} !important;
    }

    .entry-content a:hover {
        color: {$primary_color} !important;
    }

    /* Background Colors */
    body {
        background-color: {$bg_white_color} !important;
    }

    .image-card-grid {
        background: {$bg_light_color} !important;
    }

    /* Border Colors */
    .site-header {
        border-bottom: 1px solid " . tripeak_test_seven_hex_to_rgba($border_color, 0.1) . " !important;
    }

    .card {
        border: 1px solid {$border_color} !important;
    }

    .image-card-grid .card {
        border: 1px solid {$border_color} !important;
    }
    ";

    wp_add_inline_style('tripeak-test-seven-style', $custom_css);
}
add_action('wp_enqueue_scripts', 'tripeak_test_seven_custom_css');

/**
 * Helper function to adjust brightness of a hex color
 */
function tripeak_test_seven_adjust_brightness($hex, $steps) {
    $hex = str_replace('#', '', $hex);
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    $r = max(0, min(255, $r + $steps));
    $g = max(0, min(255, $g + $steps));
    $b = max(0, min(255, $b + $steps));

    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

/**
 * Helper function to convert hex to rgba
 */
function tripeak_test_seven_hex_to_rgba($hex, $alpha) {
    $hex = str_replace('#', '', $hex);
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    return "rgba($r, $g, $b, $alpha)";
}

/**
 * Performance Optimization Functions
 */

/**
 * Optimize image loading with lazy loading and modern formats
 */
function tripeak_test_seven_optimize_images() {
    // Enable WebP support
    add_filter('wp_image_editors', 'tripeak_test_seven_enable_webp_support');
    
    // Add lazy loading attributes to images
    add_filter('wp_get_attachment_image_attributes', 'tripeak_test_seven_add_lazy_loading', 10, 3);
    
    // Add responsive image sizes
    add_action('after_setup_theme', 'tripeak_test_seven_add_responsive_image_sizes');
}
add_action('init', 'tripeak_test_seven_optimize_images');

/**
 * Enable WebP support for image editor
 */
function tripeak_test_seven_enable_webp_support($editors) {
    array_unshift($editors, 'WP_Image_Editor_GD');
    return $editors;
}

/**
 * Add responsive image sizes for better performance
 */
function tripeak_test_seven_add_responsive_image_sizes() {
    // Hero images - multiple sizes for responsive loading
    add_image_size('hero-mobile', 768, 576, true);
    add_image_size('hero-tablet', 1024, 768, true);
    add_image_size('hero-desktop', 1920, 1080, true);
    add_image_size('hero-large', 2560, 1440, true);
    
    // Card images - optimized sizes
    add_image_size('card-small', 300, 225, true);
    add_image_size('card-medium', 400, 300, true);
    add_image_size('card-large', 600, 450, true);
}

/**
 * Get optimized hero image data with responsive sizes
 */
function tripeak_test_seven_get_optimized_hero_data() {
    $hero_image_id = attachment_url_to_postid(get_theme_mod('hero_background_image', ''));
    $hero_data = array(
        'background_image' => get_theme_mod('hero_background_image', ''),
        'title' => get_theme_mod('hero_title', __('Welcome to Our Blog', 'tripeak-test-seven')),
        'description' => get_theme_mod('hero_description', __('Timeless stories and insights for the modern world', 'tripeak-test-seven')),
        'responsive_images' => array()
    );
    
    if ($hero_image_id) {
        // Generate responsive image URLs
        $hero_data['responsive_images'] = array(
            'mobile' => wp_get_attachment_image_url($hero_image_id, 'hero-mobile'),
            'tablet' => wp_get_attachment_image_url($hero_image_id, 'hero-tablet'),
            'desktop' => wp_get_attachment_image_url($hero_image_id, 'hero-desktop'),
            'large' => wp_get_attachment_image_url($hero_image_id, 'hero-large'),
            'original' => wp_get_attachment_image_url($hero_image_id, 'full')
        );
    }
    
    return $hero_data;
}

/**
 * Preload critical resources
 */
function tripeak_test_seven_preload_critical_resources() {
    // Preconnect to external domains (highest priority)
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    echo '<link rel="preconnect" href="https://cdnjs.cloudflare.com">' . "\n";
    
    // Preload LCP hero image on front page
    if (is_front_page()) {
        $hero_data = tripeak_test_seven_get_optimized_hero_data();
        if (!empty($hero_data['responsive_images']['desktop'])) {
            echo '<link rel="preload" as="image" href="' . esc_url($hero_data['responsive_images']['desktop']) . '" fetchpriority="high" media="(min-width: 1024px)">' . "\n";
            echo '<link rel="preload" as="image" href="' . esc_url($hero_data['responsive_images']['tablet']) . '" fetchpriority="high" media="(min-width: 769px) and (max-width: 1023px)">' . "\n";
            echo '<link rel="preload" as="image" href="' . esc_url($hero_data['responsive_images']['mobile']) . '" fetchpriority="high" media="(max-width: 768px)">' . "\n";
        }
    }
    
    // Preload critical fonts
    echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Source+Sans+Pro:wght@300;400;500;600&display=swap" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
    echo '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Source+Sans+Pro:wght@300;400;500;600&display=swap"></noscript>' . "\n";
}
add_action('wp_head', 'tripeak_test_seven_preload_critical_resources', 1);

/**
 * Optimize script and style loading
 */
function tripeak_test_seven_optimize_assets() {
    // Defer non-critical JavaScript
    add_filter('script_loader_tag', 'tripeak_test_seven_defer_scripts', 10, 3);
    
    // Optimize CSS delivery
    add_filter('style_loader_tag', 'tripeak_test_seven_optimize_css_delivery', 10, 4);
}
add_action('wp_enqueue_scripts', 'tripeak_test_seven_optimize_assets', 20);

/**
 * Defer non-critical JavaScript
 */
function tripeak_test_seven_defer_scripts($tag, $handle, $src) {
    // Scripts to defer (non-critical)
    $defer_scripts = array(
        'tripeak-test-seven-button-enhancements',
        'tripeak-test-seven-script'
    );
    
    // Don't defer jQuery as other scripts depend on it
    if ($handle === 'jquery' || $handle === 'jquery-core') {
        return $tag;
    }
    
    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }
    
    return $tag;
}

/**
 * Optimize CSS delivery
 */
function tripeak_test_seven_optimize_css_delivery($html, $handle, $href, $media) {
    // Non-critical CSS that can be deferred
    $defer_styles = array(
        'font-awesome',
        'tripeak-test-seven-fonts'
    );
    
    if (in_array($handle, $defer_styles)) {
        // Use preload with fallback for non-critical CSS
        return '<link rel="preload" href="' . $href . '" as="style" onload="this.onload=null;this.rel=\'stylesheet\'" media="' . esc_attr($media) . '" />' . 
               '<noscript><link rel="stylesheet" href="' . $href . '" media="' . esc_attr($media) . '"></noscript>';
    }
    
    return $html;
}

/**
 * Add critical CSS inline for above-the-fold content
 */
function tripeak_test_seven_inline_critical_css() {
    if (is_front_page()) {
        ?>
        <style id="critical-css">
        /* Critical CSS for above-the-fold content */
        .site-header { 
            background: var(--header-bg-color, #ffffff); 
            position: fixed; 
            top: 0; 
            width: 100%; 
            z-index: 999; 
        }
        .hero-section { 
            height: 100vh; 
            min-height: 600px; 
            display: flex; 
            align-items: flex-end; 
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .hero-content { 
            text-align: center; 
            color: white; 
            width: 100%; 
            padding: 2rem; 
            margin-bottom: 4rem; 
        }
        .hero-content h1 { 
            font-size: 3.5rem; 
            margin-bottom: 1.5rem; 
            font-family: 'Playfair Display', serif; 
        }
        </style>
        <?php
    }
}
add_action('wp_head', 'tripeak_test_seven_inline_critical_css', 5);

/**
 * Add image optimization and lazy loading support
 */
function tripeak_test_seven_add_image_optimization() {
    // Add support for WebP images
    add_filter('wp_check_filetype_and_ext', 'tripeak_test_seven_webp_support', 10, 4);
    
    // Add image compression - optimized quality
    add_filter('jpeg_quality', function() { return 82; });
    add_filter('wp_editor_set_quality', function() { return 82; });
}
add_action('init', 'tripeak_test_seven_add_image_optimization');

/**
 * Add WebP support
 */
function tripeak_test_seven_webp_support($data, $file, $filename, $mimes) {
    $webp_ext = substr($filename, -5);
    if (strlen($webp_ext) >= 5 && strtolower($webp_ext) === '.webp') {
        $data['ext'] = 'webp';
        $data['type'] = 'image/webp';
    }
    return $data;
}

/**
 * Generate WebP versions of uploaded images
 */
function tripeak_test_seven_generate_webp_on_upload($metadata, $attachment_id) {
    if (!isset($metadata['file'])) {
        return $metadata;
    }
    
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/' . $metadata['file'];
    
    // Check if file is an image and WebP is supported
    if (function_exists('imagewebp') && in_array($metadata['mime-type'], ['image/jpeg', 'image/png'])) {
        tripeak_test_seven_create_webp_image($file_path);
        
        // Create WebP versions for all sizes
        if (isset($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size => $size_data) {
                $size_file_path = $upload_dir['basedir'] . '/' . dirname($metadata['file']) . '/' . $size_data['file'];
                tripeak_test_seven_create_webp_image($size_file_path);
            }
        }
    }
    
    return $metadata;
}
add_filter('wp_generate_attachment_metadata', 'tripeak_test_seven_generate_webp_on_upload', 10, 2);

/**
 * Create WebP version of an image
 */
function tripeak_test_seven_create_webp_image($file_path) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $file_info = pathinfo($file_path);
    $webp_path = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';
    
    // Don't recreate if WebP already exists
    if (file_exists($webp_path)) {
        return true;
    }
    
    $mime_type = wp_check_filetype($file_path)['type'];
    
    switch ($mime_type) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($file_path);
            break;
        case 'image/png':
            $image = imagecreatefrompng($file_path);
            break;
        default:
            return false;
    }
    
    if ($image) {
        imagewebp($image, $webp_path, 85);
        imagedestroy($image);
        return true;
    }
    
    return false;
}

/**
 * Get WebP image URL if available, fallback to original
 */
function tripeak_test_seven_get_webp_image_url($attachment_id, $size = 'full') {
    $image_url = wp_get_attachment_image_url($attachment_id, $size);
    
    if (!$image_url) {
        return false;
    }
    
    // Check if browser supports WebP
    $supports_webp = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
    
    if ($supports_webp) {
        $upload_dir = wp_upload_dir();
        $image_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image_url);
        $file_info = pathinfo($image_path);
        $webp_path = $file_info['dirname'] . '/' . $file_info['filename'] . '.webp';
        
        if (file_exists($webp_path)) {
            return str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $webp_path);
        }
    }
    
    return $image_url;
}

/**
 * Enhanced image optimization with WebP support
 */
function tripeak_test_seven_get_optimized_image_data($attachment_id, $sizes = array('small', 'medium', 'large')) {
    if (!$attachment_id) {
        return false;
    }
    
    $image_data = array();
    $size_map = array(
        'small' => 'card-small',
        'medium' => 'card-medium', 
        'large' => 'card-large',
        'thumbnail' => 'thumbnail'
    );
    
    foreach ($sizes as $size) {
        $wp_size = isset($size_map[$size]) ? $size_map[$size] : $size;
        
        // Get both regular and WebP versions
        $regular_url = wp_get_attachment_image_url($attachment_id, $wp_size);
        $webp_url = tripeak_test_seven_get_webp_image_url($attachment_id, $wp_size);
        
        $image_data[$size] = array(
            'url' => $regular_url,
            'webp' => $webp_url !== $regular_url ? $webp_url : null,
            'width' => wp_get_attachment_image_src($attachment_id, $wp_size)[1] ?? null,
            'height' => wp_get_attachment_image_src($attachment_id, $wp_size)[2] ?? null
        );
    }
    
    return $image_data;
}

/**
 * Add native lazy loading to images
 */
function tripeak_test_seven_add_lazy_loading($attr, $attachment, $size) {
    // Add loading="lazy" to all images except those in the hero/featured area
    if (!is_admin()) {
        $attr['loading'] = 'lazy';
        
        // Add decoding="async" for better performance
        $attr['decoding'] = 'async';
    }
    
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'tripeak_test_seven_add_lazy_loading', 10, 3);

/**
 * Add lazy loading to content images
 */
function tripeak_test_seven_lazy_load_content_images($content) {
    // Add loading="lazy" to all img tags in content
    if (!is_admin() && !is_feed()) {
        $content = preg_replace('/<img(.*?)>/i', '<img$1 loading="lazy" decoding="async">', $content);
    }
    
    return $content;
}
add_filter('the_content', 'tripeak_test_seven_lazy_load_content_images', 20);

/**
 * Exclude featured images from lazy loading for better LCP
 */
function tripeak_test_seven_exclude_featured_from_lazy($attr, $attachment, $size) {
    // Check if this is a featured/hero image (usually larger sizes)
    if (in_array($size, array('full', 'large', 'hero'))) {
        global $post;
        $featured_id = get_post_thumbnail_id($post);
        
        // If this is the featured image at the top of a post/page, prioritize loading
        if ($attachment && $attachment->ID == $featured_id && (is_single() || is_page())) {
            unset($attr['loading']); // Remove lazy loading
            $attr['fetchpriority'] = 'high'; // Prioritize loading
        }
    }
    
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'tripeak_test_seven_exclude_featured_from_lazy', 20, 3);

/**
 * Add resource hints for faster font loading
 */
function tripeak_test_seven_add_resource_hints() {
    // Preconnect to Google Fonts for faster loading
    echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    
    // DNS prefetch for external resources
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
    echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . "\n";
    echo '<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">' . "\n";
}
add_action('wp_head', 'tripeak_test_seven_add_resource_hints', 1);

/**
 * Add performance-focused meta tags
 */
function tripeak_test_seven_performance_meta_tags() {
    // Resource hints for better performance
    echo '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">' . "\n";
    
    // Prevent unnecessary prefetching on slow connections
    if (isset($_SERVER['HTTP_SAVE_DATA']) || isset($_SERVER['HTTP_DOWNLINK']) && $_SERVER['HTTP_DOWNLINK'] < 1) {
        echo '<meta http-equiv="Save-Data" content="on">' . "\n";
    }
    
    // Performance timing hints
    echo '<meta name="color-scheme" content="light dark">' . "\n";
    
    // For PWA support (future enhancement)
    echo '<meta name="theme-color" content="#E74C3C">' . "\n";
}
add_action('wp_head', 'tripeak_test_seven_performance_meta_tags', 2);

/**
 * Optimize database queries for better performance
 */
function tripeak_test_seven_optimize_queries() {
    // Optimize post queries
    add_filter('posts_clauses', 'tripeak_test_seven_optimize_post_queries', 10, 2);
    
    // Optimize comment queries
    add_filter('comments_clauses', 'tripeak_test_seven_optimize_comment_queries', 10, 2);
}
add_action('init', 'tripeak_test_seven_optimize_queries');

/**
 * Optimize post queries
 */
function tripeak_test_seven_optimize_post_queries($clauses, $query) {
    // Disabled: narrowing selected fields can break WP_Query post hydration.
    // Keep default clauses to ensure the main loop works on blog/archives.
    return $clauses;
}

/**
 * Optimize comment queries
 */
function tripeak_test_seven_optimize_comment_queries($clauses, $query) {
    // Optimize comment queries by limiting fields when not needed
    if (!is_admin()) {
        $clauses['fields'] = "comment_ID, comment_post_ID, comment_author, comment_date, comment_content, comment_approved, comment_parent";
    }
    
    return $clauses;
}

/**
 * Cache optimization for theme
 */
function tripeak_test_seven_cache_optimizations() {
    // Object cache for expensive operations
    add_filter('pre_get_posts', 'tripeak_test_seven_cache_expensive_queries');
}
add_action('init', 'tripeak_test_seven_cache_optimizations');

/**
 * Cache expensive queries
 */
function tripeak_test_seven_cache_expensive_queries($query) {
    if (!is_admin() && $query->is_main_query()) {
        // Cache featured posts query
        if (is_front_page()) {
            $cache_key = 'tripeak_featured_posts_' . md5(serialize($query->query_vars));
            $cached_posts = wp_cache_get($cache_key, 'tripeak_posts');
            
            if ($cached_posts === false) {
                // Cache will be set after query runs
                add_action('wp', function() use ($cache_key) {
                    if (have_posts()) {
                        global $wp_query;
                        wp_cache_set($cache_key, $wp_query->posts, 'tripeak_posts', 300); // 5 minutes
                    }
                });
            }
        }
    }
    
    return $query;
}

/**
 * Set Permalink Structure for All Sites (Multisite) - Fixes Schema Pro
 */
function tripeak_test_seven_set_permalink_structure_for_all_sites() {
    if (is_multisite()) {
        $permalinks_set = get_option('tripeak_permalinks_set', false);
        
        if (!$permalinks_set) {
            $sites = get_sites();
            foreach ($sites as $site) {
                switch_to_blog($site->blog_id);
                update_option('permalink_structure', '/%postname%/');
                flush_rewrite_rules();
                restore_current_blog();
            }
            update_option('tripeak_permalinks_set', true);
        }
    }
}
add_action('init', 'tripeak_test_seven_set_permalink_structure_for_all_sites', 999);

/**
 * Set Permalink on New Site
 */
function tripeak_test_seven_set_permalink_on_new_site($blog_id) {
    switch_to_blog($blog_id);
    update_option('permalink_structure', '/%postname%/');
    flush_rewrite_rules();
    restore_current_blog();
}
add_action('wpmu_new_blog', 'tripeak_test_seven_set_permalink_on_new_site'); 
/**
 * Custom Breadcrumb Function for Single Posts
 */
function tripeak_test_seven_breadcrumb() {
    // Only show on single posts
    if (!is_single()) {
        return '';
    }

    $breadcrumb = '<nav class="breadcrumb" aria-label="Breadcrumb">';
    $breadcrumb .= '<ol class="breadcrumb-list">';

    // Home link
    $breadcrumb .= '<li class="breadcrumb-item">';
    $breadcrumb .= '<a href="' . esc_url(home_url('/')) . '" class="breadcrumb-link">' . esc_html__('Home', 'tripeak-test-seven') . '</a>';
    $breadcrumb .= '</li>';

    // Get the primary category
    $categories = get_the_category();
    if (!empty($categories)) {
        $category = $categories[0];

        $breadcrumb .= '<li class="breadcrumb-separator" aria-hidden="true">/</li>';
        $breadcrumb .= '<li class="breadcrumb-item breadcrumb-item-current">';
        $breadcrumb .= '<a href="' . esc_url(get_category_link($category->term_id)) . '" class="breadcrumb-link breadcrumb-link-current">' . esc_html($category->name) . '</a>';
        $breadcrumb .= '</li>';
    }

    $breadcrumb .= '</ol>';
    $breadcrumb .= '</nav>';

    return $breadcrumb;
}
