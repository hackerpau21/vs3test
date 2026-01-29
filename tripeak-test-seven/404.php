<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package tripeak-test-seven
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        
        <section class="error-404 not-found text-center">
            <header class="page-header">
                <h1 class="page-title"><?php esc_html_e('Oops! That page can&rsquo;t be found.', 'tripeak-test-seven'); ?></h1>
            </header>

            <div class="page-content">
                <div class="error-404-content">
                    <div class="error-number">404</div>
                    
                    <p><?php esc_html_e('It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'tripeak-test-seven'); ?></p>

                    <!-- Search Form -->
                    <div class="search-form-container">
                        <h3><?php esc_html_e('Search our site:', 'tripeak-test-seven'); ?></h3>
                        <?php get_search_form(); ?>
                    </div>

                    <!-- Recent Posts -->
                    <div class="recent-posts-section">
                        <h3><?php esc_html_e('Recent Posts:', 'tripeak-test-seven'); ?></h3>
                        <?php
                        $recent_posts = wp_get_recent_posts(array(
                            'numberposts' => 6,
                            'post_status' => 'publish'
                        ));
                        
                        if ($recent_posts) :
                        ?>
                            <div class="grid grid-3">
                                <?php
                                foreach ($recent_posts as $post) :
                                    setup_postdata($GLOBALS['post'] =& $post);
                                    get_template_part('template-parts/content', 'card');
                                endforeach;
                                wp_reset_postdata();
                                ?>
                            </div>
                        <?php else : ?>
                            <p><?php esc_html_e('No recent posts found.', 'tripeak-test-seven'); ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Categories -->
                    <?php
                    $categories = get_categories(array(
                        'orderby' => 'count',
                        'order'   => 'DESC',
                        'number'  => 5,
                    ));
                    
                    if ($categories) :
                    ?>
                        <div class="categories-section">
                            <h3><?php esc_html_e('Browse by Category:', 'tripeak-test-seven'); ?></h3>
                            <ul class="categories-list">
                                <?php foreach ($categories as $category) : ?>
                                    <li>
                                        <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>">
                                            <?php echo esc_html($category->name); ?>
                                            <span class="post-count">(<?php echo $category->count; ?>)</span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Navigation Links -->
                    <div class="navigation-links">
                        <h3><?php esc_html_e('Quick Navigation:', 'tripeak-test-seven'); ?></h3>
                        <div class="nav-buttons">
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="btn">
                                <i class="fas fa-home"></i>
                                <?php esc_html_e('Back to Homepage', 'tripeak-test-seven'); ?>
                            </a>
                            
                            <?php if (get_option('page_for_posts')) : ?>
                                <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" class="btn">
                                    <i class="fas fa-blog"></i>
                                    <?php esc_html_e('View Blog', 'tripeak-test-seven'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </div>
</main>

<?php
get_footer(); 