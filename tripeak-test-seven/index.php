<?php
/**
 * The main template file
 *
 * @package tripeak-test-seven
 */

get_header(); ?>

<main id="primary" class="site-main">
    
    <?php if (is_front_page() && !is_paged()) : ?>
        <!-- Hero Section -->
        <?php 
        $hero_data = tripeak_test_seven_get_hero_data();
        $background_image = $hero_data['background_image'];
        $hero_title = $hero_data['title'];
        $hero_description = $hero_data['description'];
        $hero_position = get_theme_mod('hero_text_position', 'bottom-center');
        
        $hero_style = '';
        if ($background_image) {
            $hero_style = 'style="background-image: url(\'' . esc_url($background_image) . '\');"';
        }
        ?>
        
        <section class="hero-section hero-position-<?php echo esc_attr($hero_position); ?>" <?php echo $hero_style; ?>>
            <div class="hero-content">
                <h1><?php echo tripeak_test_seven_format_text_with_breaks($hero_title); ?></h1>
                <p><?php echo tripeak_test_seven_format_text_with_breaks($hero_description); ?></p>
            </div>
        </section>
    <?php endif; ?>

    <!-- Main Content Area -->
    <div class="container">
        <?php if (is_front_page() && !is_paged()) : ?>
            <!-- Featured Posts Section -->
            <section class="image-card-grid">
                <div class="container">
                    <div class="section-header text-center mb-4">
                        <h2><?php esc_html_e('Featured Stories', 'tripeak-test-seven'); ?></h2>
                        <p><?php esc_html_e('Discover our latest insights and perspectives', 'tripeak-test-seven'); ?></p>
                    </div>
                    
                    <div class="grid grid-3">
                        <?php
                        // Query for featured posts or recent posts
                        $featured_posts = new WP_Query(array(
                            'posts_per_page' => 6,
                            'post_status' => 'publish',
                            'meta_query' => array(
                                array(
                                    'key' => '_featured_post',
                                    'value' => '1',
                                    'compare' => '='
                                )
                            )
                        ));
                        
                        // If no featured posts, show recent posts
                        if (!$featured_posts->have_posts()) {
                            $featured_posts = new WP_Query(array(
                                'posts_per_page' => 6,
                                'post_status' => 'publish'
                            ));
                        }
                        
                        if ($featured_posts->have_posts()) :
                            while ($featured_posts->have_posts()) : $featured_posts->the_post();
                                get_template_part('template-parts/content', 'card');
                            endwhile;
                            wp_reset_postdata();
                        else :
                            // Show placeholder cards if no posts
                            for ($i = 1; $i <= 3; $i++) :
                        ?>
                                <article class="card">
                                    <div class="card-image-placeholder" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
                                    </div>
                                    <div class="card-content">
                                        <span class="card-category"><?php esc_html_e('Sample', 'tripeak-test-seven'); ?></span>
                                        <h3><?php printf(esc_html__('Sample Post %d', 'tripeak-test-seven'), $i); ?></h3>
                                        <p><?php esc_html_e('This is a sample post to demonstrate the card layout. Create your first post to see your content here.', 'tripeak-test-seven'); ?></p>
                                        <a href="#" class="btn"><?php esc_html_e('Read More', 'tripeak-test-seven'); ?></a>
                                    </div>
                                </article>
                        <?php
                            endfor;
                        endif;
                        ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Latest Posts Section (only show if not front page or if paged) -->
        <?php if (!is_front_page() || is_paged()) : ?>
        <section class="image-card-grid">
            <div class="container">
                <div class="section-header text-center mb-4">
                    <h2><?php 
                        if (is_front_page()) {
                            esc_html_e('More Posts', 'tripeak-test-seven');
                        } elseif (is_home()) {
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
                    if (have_posts()) :
                        // Use the main loop for all cases
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
        <?php endif; ?>
    </div>

</main>

<?php
get_footer(); 