<?php
/**
 * The blog posts page template
 *
 * This template is used when displaying the blog posts page
 * (when Reading Settings > Posts page is set to a specific page)
 *
 * @package tripeak-test-seven
 */

get_header(); ?>

<main id="primary" class="site-main">
    <div class="container">
        
        <!-- Blog Page Header -->
        <header class="page-header text-center">
            <?php
            // Get description from the page set as posts page
            if (is_home() && get_option('page_for_posts')) {
                $posts_page = get_post(get_option('page_for_posts'));
                if ($posts_page && !empty($posts_page->post_content)) {
                    echo '<div class="page-description">' . wp_kses_post(wpautop($posts_page->post_content)) . '</div>';
                }
            }
            ?>
        </header>

        <!-- Blog Posts Section -->
        <section class="blog-posts image-card-grid">
            <div class="container">
                <div class="section-header text-center mb-4">
                    <h2><?php esc_html_e('Blog Posts', 'tripeak-test-seven'); ?></h2>
                </div>

                <?php
                // Category Buttons
                tripeak_test_seven_category_buttons();
                ?>

                <div class="grid grid-3 posts-container">
                    <?php
                    if (have_posts()) :
                        // Use the main loop for blog posts
                        while (have_posts()) :
                            the_post();
                            get_template_part('template-parts/content', 'card');
                        endwhile;
                    else :
                        get_template_part('template-parts/content', 'none');
                    endif;
                    ?>
                </div>
                
                <?php
                // Pagination
                if (have_posts() || is_paged()) :
                    the_posts_pagination(array(
                        'mid_size'  => 2,
                        'prev_text' => __('&laquo; Previous', 'tripeak-test-seven'),
                        'next_text' => __('Next &raquo;', 'tripeak-test-seven'),
                        'screen_reader_text' => __('Posts navigation'),
                        'type' => 'list',
                        'class' => 'pagination pagination-unique',
                    ));
                endif;
                ?>
            </div>
        </section>

    </div>
</main>

<?php
get_footer();
