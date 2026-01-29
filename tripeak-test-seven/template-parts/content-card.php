<?php
/**
 * Template part for displaying posts in card format
 *
 * @package tripeak-test-seven
 */

?>

<?php
// Get post categories for filtering
$post_categories = get_the_category();
$category_ids = array();
if (!empty($post_categories)) {
    foreach ($post_categories as $category) {
        $category_ids[] = $category->term_id;
    }
}
// If no categories, use uncategorized category ID (default is 1)
if (empty($category_ids)) {
    $uncategorized_id = get_option('default_category', 1);
    $category_ids[] = $uncategorized_id;
}
$category_data = implode(',', $category_ids);
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('card'); ?> data-categories="<?php echo esc_attr($category_data); ?>">
    <?php if (has_post_thumbnail()) : 
        $attachment_id = get_post_thumbnail_id();
        $image_alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ?: get_the_title();
        
        // Generate responsive image URLs
        $image_small = wp_get_attachment_image_url($attachment_id, 'card-small');
        $image_medium = wp_get_attachment_image_url($attachment_id, 'card-medium');
        $image_large = wp_get_attachment_image_url($attachment_id, 'card-large');
        
        // Get WebP versions if available
        $webp_small = tripeak_test_seven_get_webp_image_url($attachment_id, 'card-small');
        $webp_medium = tripeak_test_seven_get_webp_image_url($attachment_id, 'card-medium');
        $webp_large = tripeak_test_seven_get_webp_image_url($attachment_id, 'card-large');
        
        // Get actual image dimensions
        $image_data = wp_get_attachment_image_src($attachment_id, 'card-medium');
        $img_width = $image_data[1] ?? 400;
        $img_height = $image_data[2] ?? 300;
        ?>
        <div class="card-image-wrapper">
            <a href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                <picture>
                    <?php if ($webp_small !== $image_small) : ?>
                    <source type="image/webp" 
                            srcset="<?php echo esc_url($webp_small); ?> 300w, <?php echo esc_url($webp_medium); ?> 400w, <?php echo esc_url($webp_large); ?> 600w"
                            sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw">
                    <?php endif; ?>
                    <source type="image/jpeg" 
                            srcset="<?php echo esc_url($image_small); ?> 300w, <?php echo esc_url($image_medium); ?> 400w, <?php echo esc_url($image_large); ?> 600w"
                            sizes="(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw">
                    <img class="card-image" 
                         src="<?php echo esc_url($image_medium); ?>"
                         alt="<?php echo esc_attr($image_alt); ?>"
                         loading="lazy"
                         decoding="async"
                         width="<?php echo esc_attr($img_width); ?>"
                         height="<?php echo esc_attr($img_height); ?>" />
                </picture>
            </a>
        </div>
    <?php else : ?>
        <div class="card-image-placeholder" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); height: 200px; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-image" style="font-size: 3rem; color: #ccc;"></i>
        </div>
    <?php endif; ?>

    <div class="card-content">

        <?php
        the_title(
            '<h3 class="card-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">',
            '</a></h3>'
        );
        ?>

        <div class="card-excerpt">
            <?php
            if (has_excerpt()) {
                the_excerpt();
            } else {
                echo '<p>' . wp_trim_words(get_the_content(), 20, '...') . '</p>';
            }
            ?>
        </div>

        <div class="card-meta">
            <span class="post-date">
                <i class="far fa-calendar-alt"></i>
                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                    <?php echo esc_html(get_the_date()); ?>
                </time>
            </span>
            
            <!-- Comments disabled -->
        </div>

        <div class="card-footer">
            <a href="<?php the_permalink(); ?>" class="btn">
                <?php esc_html_e('Read More', 'tripeak-test-seven'); ?>
            </a>
        </div>
    </div>
</article> 