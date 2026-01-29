<?php
/**
 * The template for displaying all pages
 *
 * @package tripeak-test-seven
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        <div class="page-layout">
            
            <?php
            while (have_posts()) :
                the_post();
                ?>
                
                <article id="post-<?php the_ID(); ?>" <?php post_class('single-page'); ?>>
                    
                    <?php if (has_post_thumbnail()) : 
                        $attachment_id = get_post_thumbnail_id();
                        $image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ?: get_the_title();
                        
                        // Generate responsive image URLs
                        $image_medium = wp_get_attachment_image_url($attachment_id, 'medium_large');
                        $image_large = wp_get_attachment_image_url($attachment_id, 'large');
                        $image_full = wp_get_attachment_image_url($attachment_id, 'full');
                        
                        // Get WebP versions
                        $webp_medium = tripeak_test_seven_get_webp_image_url($attachment_id, 'medium_large');
                        $webp_large = tripeak_test_seven_get_webp_image_url($attachment_id, 'large');
                        $webp_full = tripeak_test_seven_get_webp_image_url($attachment_id, 'full');
                        
                        // Get actual image dimensions
                        $image_data = wp_get_attachment_image_src($attachment_id, 'large');
                        $img_width = $image_data[1] ?? 1024;
                        $img_height = $image_data[2] ?? 768;
                    ?>
                        <div class="page-featured-image">
                            <picture>
                                <?php if ($webp_medium !== $image_medium) : ?>
                                <source type="image/webp" 
                                        srcset="<?php echo esc_url($webp_medium); ?> 768w, <?php echo esc_url($webp_large); ?> 1024w, <?php echo esc_url($webp_full); ?> 1920w"
                                        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 90vw, 1200px">
                                <?php endif; ?>
                                <source type="image/jpeg" 
                                        srcset="<?php echo esc_url($image_medium); ?> 768w, <?php echo esc_url($image_large); ?> 1024w, <?php echo esc_url($image_full); ?> 1920w"
                                        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 90vw, 1200px">
                                <img class="featured-image" 
                                     src="<?php echo esc_url($image_large); ?>"
                                     alt="<?php echo esc_attr($image_alt); ?>"
                                     fetchpriority="high"
                                     decoding="sync"
                                     width="<?php echo esc_attr($img_width); ?>"
                                     height="<?php echo esc_attr($img_height); ?>" />
                            </picture>
                        </div>
                    <?php endif; ?>

                    <header class="entry-header">
                        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                    </header>

                    <div class="entry-content">
                        <?php
                        the_content();

                        wp_link_pages(array(
                            'before' => '<div class="page-links">' . esc_html__('Pages:', 'tripeak-test-seven'),
                            'after'  => '</div>',
                        ));
                        ?>
                    </div>

                    <?php if (get_edit_post_link()) : ?>
                        <footer class="entry-footer">
                            <?php
                            edit_post_link(
                                sprintf(
                                    wp_kses(
                                        /* translators: %s: Name of current post. Only visible to screen readers */
                                        __('Edit <span class="screen-reader-text">"%s"</span>', 'tripeak-test-seven'),
                                        array(
                                            'span' => array(
                                                'class' => array(),
                                            ),
                                        )
                                    ),
                                    wp_kses_post(get_the_title())
                                ),
                                '<span class="edit-link">',
                                '</span>'
                            );
                            ?>
                        </footer>
                    <?php endif; ?>
                </article>

                <?php
                // If comments are open or there's at least one comment, load the comment template
                // Comments disabled

            endwhile; // End of the loop.
            ?>

        </div>
    </div>
</main>

<?php
get_footer(); 