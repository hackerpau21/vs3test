<?php
/**
 * The template for displaying archive pages
 *
 * @package tripeak-test-seven
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        
        <?php if (have_posts()) : ?>
            
            <!-- Archive Posts Section -->
            <section class="image-card-grid">
                <div class="container">
                    <div class="section-header text-center mb-4">
                        <h2><?php 
                            if (is_category()) {
                                esc_html_e('Blog Posts', 'tripeak-test-seven');
                            } else {
                                esc_html_e('Posts', 'tripeak-test-seven');
                            }
                        ?></h2>
                    </div>

                    <?php
                    // Category Buttons
                    tripeak_test_seven_category_buttons();
                    ?>

                    <div class="grid grid-3 posts-container">
                        <?php
                        // Start the Loop
                        while (have_posts()) :
                            the_post();
                            
                            // Use card format for archive pages
                            get_template_part('template-parts/content', 'card');

                        endwhile;
                        ?>
                    </div>

                    <?php
                    // Pagination
                    the_posts_pagination(array(
                        'mid_size'  => 2,
                        'prev_text' => __('&laquo; Previous', 'tripeak-test-seven'),
                        'next_text' => __('Next &raquo;', 'tripeak-test-seven'),
                        'screen_reader_text' => __('Posts navigation'),
                        'type' => 'list',
                        'class' => 'pagination pagination-unique',
                    ));
                    ?>
                </div>
            </section>

        <?php else : ?>
            
            <?php get_template_part('template-parts/content', 'none'); ?>
            
        <?php endif; ?>

    </div>
</main>

<?php
get_footer(); 