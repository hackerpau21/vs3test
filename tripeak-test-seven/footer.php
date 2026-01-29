    <footer id="colophon" class="site-footer">
        <div class="container">
            <div class="footer-content footer-grid">
                <!-- Footer Menu Column -->
                <div class="footer-widget-area footer-1">
                    <h3><?php echo esc_html(get_theme_mod('footer_menu_header', __('Quick Links', 'tripeak-test-seven'))); ?></h3>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'menu_id'        => 'footer-menu',
                        'container'      => false,
                        'fallback_cb'    => 'tripeak_test_seven_footer_fallback_menu',
                    ));
                    ?>
                </div>

                <!-- Recent Posts Column -->
                <?php if (get_theme_mod('footer_recent_posts_enable', true)) : ?>
                <div class="footer-widget-area footer-2">
                    <h3><?php echo esc_html(get_theme_mod('footer_recent_posts_header', __('Recent Posts', 'tripeak-test-seven'))); ?></h3>
                    <?php
                    $recent_posts = wp_get_recent_posts(array(
                        'numberposts' => absint(get_theme_mod('footer_recent_posts_number', 3)),
                        'post_status' => 'publish'
                    ));
                    if ($recent_posts) {
                        echo '<ul class="recent-posts-list">';
                        foreach ($recent_posts as $post) {
                            echo '<li><a href="' . esc_url(get_permalink($post['ID'])) . '">' . esc_html($post['post_title']) . '</a></li>';
                        }
                        echo '</ul>';
                        wp_reset_postdata();
                    }
                    ?>
                </div>
                <?php endif; ?>

                <!-- Best Posts Column -->
                <?php if (get_theme_mod('footer_best_posts_enable', true)) : ?>
                <div class="footer-widget-area footer-3">
                    <h3><?php echo esc_html(get_theme_mod('footer_best_posts_header', __('Best Posts', 'tripeak-test-seven'))); ?></h3>
                    <?php
                    // First try to get posts from 'best-posts' category
                    $best_posts_query = new WP_Query(array(
                        'category_name' => 'best-posts',
                        'posts_per_page' => absint(get_theme_mod('footer_best_posts_number', 3)),
                        'post_status' => 'publish'
                    ));
                    
                    // If no posts in 'best-posts' category, get oldest posts as fallback
                    if (!$best_posts_query->have_posts()) {
                        $best_posts_query = new WP_Query(array(
                            'posts_per_page' => absint(get_theme_mod('footer_best_posts_number', 3)),
                            'post_status' => 'publish',
                            'orderby' => 'date',
                            'order' => 'ASC'
                        ));
                    }
                    
                    if ($best_posts_query->have_posts()) {
                        echo '<ul class="best-posts-list">';
                        while ($best_posts_query->have_posts()) {
                            $best_posts_query->the_post();
                            echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
                        }
                        echo '</ul>';
                        wp_reset_postdata();
                    }
                    ?>
                </div>
                <?php endif; ?>

                <!-- Social Links Column -->
                <?php if (get_theme_mod('footer_social_links_enable', true)) : ?>
                <div class="footer-widget-area footer-4">
                    <h3><?php echo esc_html(get_theme_mod('footer_social_links_header', __('Connect', 'tripeak-test-seven'))); ?></h3>
                    <div class="footer-social-links spaced-icons">
                        <?php echo tripeak_test_seven_get_social_links(true); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Powered By Section -->
            <?php 
            $show_powered_by = get_theme_mod('footer_powered_by_enable', true);
            
            if ($show_powered_by) : ?>
            <div class="footer-custom-text">
                <p>
                    <?php 
                    // Get the current site domain
                    $site_domain = parse_url(get_site_url(), PHP_URL_HOST);
                    $profile_url = 'https://personalwebsites.org/site/' . $site_domain;
                    ?>
                    Powered by <a href="https://personalwebsites.org" target="_blank" rel="noopener">PersonalWebsites.org</a>
                    | <a href="<?php echo esc_url($profile_url); ?>" target="_blank" rel="noopener">See my profile on Personal Websites List</a>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </footer>
</div><!-- #page -->

<?php wp_footer(); ?>

<!-- Mobile Menu JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuCheckbox = document.querySelector('#mobile-menu-toggle');
    const menuToggle = document.querySelector('.menu-toggle');
    const mainNavigation = document.querySelector('.main-navigation');
    
    if (mobileMenuCheckbox && menuToggle && mainNavigation) {
        // Handle checkbox change
        mobileMenuCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            
            if (isChecked) {
                // Menu is opening
                menuToggle.setAttribute('aria-expanded', 'true');
                document.body.classList.add('menu-open');
            } else {
                // Menu is closing
                menuToggle.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('menu-open');
            }
        });
        
        // Handle submenu toggling and menu item clicks
        const navMenu = mainNavigation.querySelector('.primary-menu');
        if (navMenu) {
            navMenu.addEventListener('click', function(e) {
                const target = e.target;
                
                // Only handle on mobile
                if (window.innerWidth <= 768) {
                    // Handle submenu toggling
                    if (target.tagName === 'A' && target.parentElement.classList.contains('menu-item-has-children')) {
                        const parentLi = target.parentElement;
                        const submenu = parentLi.querySelector('ul');
                        
                        if (submenu) {
                            // If submenu is not open, prevent navigation and open it
                            if (!parentLi.classList.contains('submenu-open')) {
                                e.preventDefault();
                                
                                // Close other open submenus
                                navMenu.querySelectorAll('.submenu-open').forEach(function(openItem) {
                                    if (openItem !== parentLi) {
                                        openItem.classList.remove('submenu-open');
                                    }
                                });
                                
                                // Open this submenu
                                parentLi.classList.add('submenu-open');
                                target.setAttribute('aria-expanded', 'true');
                            }
                        }
                    } else if (target.tagName === 'A' && !target.parentElement.classList.contains('menu-item-has-children')) {
                        // Close menu when clicking on regular menu items
                        mobileMenuCheckbox.checked = false;
                        menuToggle.setAttribute('aria-expanded', 'false');
                        document.body.classList.remove('menu-open');
                    }
                }
            });
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 768) {
                if (!mainNavigation.contains(event.target) && mobileMenuCheckbox.checked) {
                    mobileMenuCheckbox.checked = false;
                    menuToggle.setAttribute('aria-expanded', 'false');
                    document.body.classList.remove('menu-open');
                }
            }
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', function(e) {
            if (e.keyCode === 27 && mobileMenuCheckbox.checked) {
                mobileMenuCheckbox.checked = false;
                menuToggle.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('menu-open');
            }
        });
        
        // Close menu on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && mobileMenuCheckbox.checked) {
                mobileMenuCheckbox.checked = false;
                menuToggle.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('menu-open');
                
                // Close all submenus
                const openSubmenus = document.querySelectorAll('.submenu-open');
                openSubmenus.forEach(function(item) {
                    item.classList.remove('submenu-open');
                });
            }
        });
    }
});
</script>

</body>
</html>

<?php
/**
 * Fallback menu for footer when no footer menu is set
 */
function tripeak_test_seven_footer_fallback_menu() {
    echo '<ul id="footer-menu" class="menu">';
    echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Home', 'tripeak-test-seven') . '</a></li>';
    echo '<li><a href="' . esc_url(get_privacy_policy_url()) . '">' . esc_html__('Privacy Policy', 'tripeak-test-seven') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/contact')) . '">' . esc_html__('Contact', 'tripeak-test-seven') . '</a></li>';
    echo '</ul>';
}
?> 