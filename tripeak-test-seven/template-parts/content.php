<?php
/**
 * Template part for displaying posts
 *
 * @package tripeak-test-seven
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class('blog-post-item'); ?>>
    
    <?php if (has_post_thumbnail()) : 
        $attachment_id = get_post_thumbnail_id();
        $image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ?: get_the_title();
        
        // For single posts (not lazy loaded - LCP optimization)
        if (is_singular()) :
            // Generate responsive image URLs for single posts
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
        <div class="post-thumbnail">
            <picture>
                <?php if ($webp_medium !== $image_medium) : ?>
                <source type="image/webp" 
                        srcset="<?php echo esc_url($webp_medium); ?> 768w, <?php echo esc_url($webp_large); ?> 1024w, <?php echo esc_url($webp_full); ?> 1920w"
                        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 90vw, 1200px">
                <?php endif; ?>
                <source type="image/jpeg" 
                        srcset="<?php echo esc_url($image_medium); ?> 768w, <?php echo esc_url($image_large); ?> 1024w, <?php echo esc_url($image_full); ?> 1920w"
                        sizes="(max-width: 768px) 100vw, (max-width: 1200px) 90vw, 1200px">
                <img class="post-image" 
                     src="<?php echo esc_url($image_large); ?>"
                     alt="<?php echo esc_attr($image_alt); ?>"
                     fetchpriority="high"
                     decoding="sync"
                     width="<?php echo esc_attr($img_width); ?>"
                     height="<?php echo esc_attr($img_height); ?>" />
            </picture>
        </div>
        <?php else : 
            // For archive/listing pages (with lazy loading)
            $image_medium = wp_get_attachment_image_url($attachment_id, 'medium_large');
            $image_large = wp_get_attachment_image_url($attachment_id, 'large');
            
            // Get WebP versions
            $webp_medium = tripeak_test_seven_get_webp_image_url($attachment_id, 'medium_large');
            $webp_large = tripeak_test_seven_get_webp_image_url($attachment_id, 'large');
            
            // Get actual image dimensions
            $image_data = wp_get_attachment_image_src($attachment_id, 'large');
            $img_width = $image_data[1] ?? 1024;
            $img_height = $image_data[2] ?? 768;
        ?>
        <div class="post-thumbnail">
            <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                <picture>
                    <?php if ($webp_medium !== $image_medium) : ?>
                    <source type="image/webp" 
                            srcset="<?php echo esc_url($webp_medium); ?> 768w, <?php echo esc_url($webp_large); ?> 1024w"
                            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 90vw, 1200px">
                    <?php endif; ?>
                    <source type="image/jpeg" 
                            srcset="<?php echo esc_url($image_medium); ?> 768w, <?php echo esc_url($image_large); ?> 1024w"
                            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 90vw, 1200px">
                    <img class="post-image" 
                         src="<?php echo esc_url($image_large); ?>"
                         alt="<?php echo esc_attr($image_alt); ?>"
                         loading="lazy"
                         decoding="async"
                         width="<?php echo esc_attr($img_width); ?>"
                         height="<?php echo esc_attr($img_height); ?>" />
                </picture>
            </a>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <header class="entry-header">
        <?php
        // Display category
        $categories = get_the_category();
        if (!empty($categories)) :
        ?>
            <div class="post-categories">
                <?php foreach ($categories as $category) : ?>
                    <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="post-category">
                        <?php echo esc_html($category->name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
        if (is_singular()) :
            the_title('<h1 class="entry-title">', '</h1>');
        else :
            the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
        endif;
        ?>

        <?php if ('post' === get_post_type()) : ?>
            <div class="entry-meta">
                <span class="posted-on">
                    <i class="far fa-calendar-alt"></i>
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                        <?php echo esc_html(get_the_date()); ?>
                    </time>
                </span>
                
                <span class="byline">
                    <i class="far fa-user"></i>
                    <span class="author vcard">
                        <a class="url fn n" href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>">
                            <?php echo esc_html(get_the_author()); ?>
                        </a>
                    </span>
                </span>

                <!-- Comments disabled -->

                <?php
                // Display tags
                $tags = get_the_tags();
                if ($tags) :
                ?>
                    <span class="tags-links">
                        <i class="fas fa-tags"></i>
                        <?php
                        $tag_links = array();
                        foreach ($tags as $tag) {
                            $tag_links[] = '<a href="' . esc_url(get_tag_link($tag->term_id)) . '">' . esc_html($tag->name) . '</a>';
                        }
                        echo implode(', ', $tag_links);
                        ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </header>

    <div class="entry-content">
        <?php
        if (is_singular()) {
            the_content(sprintf(
                wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers */
                    __('Continue reading<span class="screen-reader-text"> "%s"</span>', 'tripeak-test-seven'),
                    array(
                        'span' => array(
                            'class' => array(),
                        ),
                    )
                ),
                wp_kses_post(get_the_title())
            ));

            wp_link_pages(array(
                'before' => '<div class="page-links">' . esc_html__('Pages:', 'tripeak-test-seven'),
                'after'  => '</div>',
            ));
        } else {
            // Show excerpt for blog listing
            if (has_excerpt()) {
                the_excerpt();
            } else {
                echo '<p>' . wp_trim_words(get_the_content(), 35, '...') . '</p>';
            }
            ?>
            <p>
                <a href="<?php the_permalink(); ?>" class="btn">
                    <?php esc_html_e('Read More', 'tripeak-test-seven'); ?>
                </a>
            </p>
            <?php
        }
        ?>
    </div>

    <?php if (is_singular()) : ?>
        <footer class="entry-footer">
            <?php
            // Post navigation for single posts
            the_post_navigation(array(
                'prev_text' => '<span class="nav-subtitle">' . esc_html__('Previous:', 'tripeak-test-seven') . '</span> <span class="nav-title">%title</span>',
                'next_text' => '<span class="nav-subtitle">' . esc_html__('Next:', 'tripeak-test-seven') . '</span> <span class="nav-title">%title</span>',
            ));
            ?>
        </footer>
    <?php endif; ?>

</article> 