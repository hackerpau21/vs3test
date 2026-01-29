<?php
/**
 * Template part for displaying posts in card format (no image)
 *
 * @package tripeak-test-seven
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class('card'); ?>>
    <div class="card-content">
        <?php
        // Display category (hidden by CSS for featured stories)
        $categories = get_the_category();
        if (!empty($categories)) :
            $category = $categories[0];
        ?>
            <span class="card-category">
                <a href="<?php echo esc_url(get_category_link($category->term_id)); ?>">
                    <?php echo esc_html($category->name); ?>
                </a>
            </span>
        <?php endif; ?>

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