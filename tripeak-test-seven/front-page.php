<?php
/**
 * The front page template file
 *
 * @package tripeak-test-seven
 */

get_header(); ?>

<main id="primary" class="site-main">
    
    <!-- Hero Section -->
    <?php 
    $hero_data = tripeak_test_seven_get_optimized_hero_data();
    $background_image = $hero_data['background_image'];
    $hero_title = $hero_data['title'];
    $hero_description = $hero_data['description'];
    $responsive_images = $hero_data['responsive_images'];
    $hero_position = get_theme_mod('hero_text_position', 'bottom-center');
    ?>
    
    <section class="hero-section hero-position-<?php echo esc_attr($hero_position); ?>">
        <?php if ($background_image) : ?>
            <?php if (!empty($responsive_images)) : ?>
                <!-- Hero background image with fetchpriority for LCP optimization -->
                <picture class="hero-bg-picture">
                    <source media="(min-width: 2560px)" srcset="<?php echo esc_url($responsive_images['large']); ?>">
                    <source media="(min-width: 1024px)" srcset="<?php echo esc_url($responsive_images['desktop']); ?>">
                    <source media="(min-width: 769px)" srcset="<?php echo esc_url($responsive_images['tablet']); ?>">
                    <source media="(max-width: 768px)" srcset="<?php echo esc_url($responsive_images['mobile']); ?>">
                    <img src="<?php echo esc_url($responsive_images['desktop']); ?>" 
                         alt="<?php echo esc_attr(get_bloginfo('name')); ?> Hero Background"
                         fetchpriority="high"
                         decoding="sync"
                         class="hero-bg-image">
                </picture>
            <?php else : ?>
                <img src="<?php echo esc_url($background_image); ?>" 
                     alt="<?php echo esc_attr(get_bloginfo('name')); ?> Hero Background"
                     fetchpriority="high"
                     decoding="sync"
                     class="hero-bg-image">
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="hero-content">
            <h1><?php echo tripeak_test_seven_format_text_with_breaks($hero_title); ?></h1>
            <p><?php echo tripeak_test_seven_format_text_with_breaks($hero_description); ?></p>
        </div>
    </section>

    <!-- Main Content Area -->
    <div class="container">
        <!-- Page Content Section -->
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <section class="page-content" style="padding: 4rem 0;">
                    <div class="content-wrapper">
                        <?php if (get_the_title()) : ?>
                            <header class="page-header text-center mb-4">
                                <h1 class="page-title"><?php the_title(); ?></h1>
                            </header>
                        <?php endif; ?>
                        
                        <div class="page-content-inner">
                            <?php
                            the_content();
                            
                            wp_link_pages(array(
                                'before' => '<div class="page-links">' . esc_html__('Pages:', 'tripeak-test-seven'),
                                'after'  => '</div>',
                            ));
                            ?>
                        </div>
                    </div>
                </section>
            <?php endwhile; ?>
        <?php endif; ?>

        <!-- Featured Posts Section -->
        <?php
        // Get Customizer settings
        $fs_header = get_theme_mod('homepage_article_loop_header', __('Featured Stories', 'tripeak-test-seven'));
        $fs_body = get_theme_mod('homepage_article_loop_body', __('Discover our latest insights and perspectives', 'tripeak-test-seven'));
        $fs_show_images = get_theme_mod('homepage_article_loop_show_images', true);
        $fs_num_posts = absint(get_theme_mod('homepage_article_loop_num_posts', 6));
        $fs_category = absint(get_theme_mod('homepage_article_loop_category', 0));
        ?>
        <section class="image-card-grid">
            <div class="container">
                <div class="section-header text-center mb-4">
                    <h2><?php echo esc_html($fs_header); ?></h2>
                    <p><?php echo esc_html($fs_body); ?></p>
                </div>
                
                <div class="grid grid-3">
                    <?php
                    // Build query args
                    $query_args = array(
                        'posts_per_page' => $fs_num_posts,
                        'post_status' => 'publish',
                    );
                    
                    // Add category filter if selected
                    if ($fs_category > 0) {
                        $query_args['cat'] = $fs_category;
                    }
                    
                    // Query for featured posts first
                    $featured_query_args = array_merge($query_args, array(
                        'meta_query' => array(
                            array(
                                'key' => '_featured_post',
                                'value' => '1',
                                'compare' => '='
                            )
                        )
                    ));
                    
                    $featured_posts = new WP_Query($featured_query_args);
                    
                    // If no featured posts, show recent posts (with category filter if set)
                    if (!$featured_posts->have_posts()) {
                        $featured_posts = new WP_Query($query_args);
                    }
                    
                    if ($featured_posts->have_posts()) :
                        while ($featured_posts->have_posts()) : $featured_posts->the_post();
                            if ($fs_show_images) {
                                get_template_part('template-parts/content', 'card');
                            } else {
                                get_template_part('template-parts/content', 'card-noimage');
                            }
                        endwhile;
                        wp_reset_postdata();
                    else :
                        // Show placeholder cards if no posts
                        for ($i = 1; $i <= 3; $i++) :
                    ?>
                            <article class="card">
                                <?php if ($fs_show_images) : ?>
                                <div class="card-image-placeholder" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
                                </div>
                                <?php endif; ?>
                                <div class="card-content">
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
    </div>

</main>

<?php
get_footer(); 