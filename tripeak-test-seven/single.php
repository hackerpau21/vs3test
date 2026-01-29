<?php
/**
 * The template for displaying all single posts
 *
 * @package tripeak-test-seven
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        <div class="single-post-layout">
            
            <?php
            while (have_posts()) :
                the_post();
                ?>
                
                <article id="post-<?php the_ID(); ?>" <?php post_class('single-post'); ?>>

                    <header class="entry-header">
                        <?php echo tripeak_test_seven_breadcrumb(); ?>
                        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                        <div class="entry-meta">
                            <span class="posted-on">
                                <i class="far fa-calendar-alt"></i>
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                    <?php echo esc_html(get_the_date()); ?>
                                </time>
                            </span>
                            <!-- Comments disabled -->
                        </div>
                    </header>

                    <div class="entry-content">
                        <?php
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
                        ?>
                    </div>

                    <?php
                    // Display tags
                    $tags = get_the_tags();
                    if ($tags) :
                    ?>
                        <div class="post-tags">
                            <h4><?php esc_html_e('Tags:', 'tripeak-test-seven'); ?></h4>
                            <div class="tags-list">
                                <?php foreach ($tags as $tag) : ?>
                                    <a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="tag-link">
                                        <?php echo esc_html($tag->name); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <footer class="entry-footer">
                        <!-- Removed post navigation -->
                    </footer>
                </article>

                <?php
                // Author bio section
                $author_id = get_the_author_meta('ID');
                $author_bio = get_the_author_meta('description');
                
                if ($author_bio) :
                ?>
                    <div class="author-bio">
                        <div class="author-avatar">
                            <?php echo get_avatar($author_id, 80); ?>
                        </div>
                        <div class="author-info">
                            <h3 class="author-name">
                                <a href="<?php echo esc_url(get_author_posts_url($author_id)); ?>">
                                    <?php echo esc_html(get_the_author()); ?>
                                </a>
                            </h3>
                            <div class="author-description">
                                <?php echo wp_kses_post($author_bio); ?>
                            </div>
                            <a href="<?php echo esc_url(get_author_posts_url($author_id)); ?>" class="author-link">
                                <?php esc_html_e('View all posts by', 'tripeak-test-seven'); ?> <?php echo esc_html(get_the_author()); ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php
                // Related posts
                $related_posts = new WP_Query(array(
                    'posts_per_page' => 2,
                    'post__not_in' => array(get_the_ID()),
                    'category__in' => wp_get_post_categories(get_the_ID()),
                ));

                if ($related_posts->have_posts()) :
                ?>
                    <section class="related-posts">
                        <h3><?php esc_html_e('Related Posts', 'tripeak-test-seven'); ?></h3>
                        <div class="grid grid-2">
                            <?php
                            while ($related_posts->have_posts()) : $related_posts->the_post();
                                get_template_part('template-parts/content', 'card');
                            endwhile;
                            wp_reset_postdata();
                            ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php
                // (Remove comments_template and related logic)

            endwhile; // End of the loop.
            ?>

        </div>
    </div>
</main>

<?php
get_footer(); 