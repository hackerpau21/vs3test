<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'tripeak-test-seven'); ?></a>

    <header id="masthead" class="site-header">
        <div class="container">
            <div class="header-content">
                <!-- Logo/Site Title -->
                <div class="site-branding">
                    <?php if (has_custom_logo()) : ?>
                        <div class="site-logo">
                            <?php the_custom_logo(); ?>
                        </div>
                    <?php else : ?>
                        <div class="site-title">
                            <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                                <?php 
                                $site_name = get_bloginfo('name');
                                $words = explode(' ', $site_name);
                                if (count($words) >= 2) {
                                    echo esc_html($words[0]) . '<br>' . esc_html(implode(' ', array_slice($words, 1)));
                                } else {
                                    echo esc_html($site_name);
                                }
                                ?>
                            </a>
                        </div>
                        <?php 
                        $description = get_bloginfo('description', 'display');
                        if ($description || is_customize_preview()) : ?>
                            <p class="site-description"><?php echo $description; ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Main Navigation with Hamburger Menu -->
                <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e('Primary Menu', 'tripeak-test-seven'); ?>">
                    <input type="checkbox" id="mobile-menu-toggle" class="mobile-menu-checkbox">
                    <label for="mobile-menu-toggle" class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                        <span class="sr-only"><?php esc_html_e('Menu', 'tripeak-test-seven'); ?></span>
                        <span class="menu-icon">☰</span>
                    </label>
                    
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'menu_class'     => 'primary-menu',
                        'container'      => false,
                        'fallback_cb'    => 'tripeak_test_seven_fallback_menu',
                    ));
                    ?>
                </nav>

                <!-- Social Media Links -->
                <div class="social-links">
                    <?php echo tripeak_test_seven_get_social_links(); ?>
                </div>
            </div>
        </div>
    </header>

<?php
/**
 * Fallback menu for when no primary menu is set
 */
function tripeak_test_seven_fallback_menu() {
    echo '<ul id="primary-menu" class="menu">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'tripeak-test-seven') . '</a></li>';
    
    // Add sample menu items
    $pages = get_pages(array('number' => 5));
    foreach ($pages as $page) {
        echo '<li><a href="' . esc_url(get_permalink($page->ID)) . '">' . esc_html($page->post_title) . '</a></li>';
    }
    
    echo '<li><a href="' . esc_url(get_permalink(get_option('page_for_posts'))) . '">' . esc_html__('Blog', 'tripeak-test-seven') . '</a></li>';
    echo '</ul>';
}
?> 