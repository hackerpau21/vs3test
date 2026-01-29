<?php
/**
 * The template for displaying search results pages
 *
 * @package tripeak-test-seven
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">

        <header class="page-header text-center">
            <h1 class="page-title">
                <?php
                printf(
                    /* translators: %s: search query. */
                    esc_html__('Search Results for: %s', 'tripeak-test-seven'),
                    '<span>' . get_search_query() . '</span>'
                );
                ?>
            </h1>
            
            <?php if (have_posts()) : ?>
                <div class="search-results-count">
                    <?php
                    global $wp_query;
                    $results_count = $wp_query->found_posts;
                    printf(
                        _n('Found %s result', 'Found %s results', $results_count, 'tripeak-test-seven'),
                        number_format_i18n($results_count)
                    );
                    ?>
                </div>
            <?php endif; ?>
            
            <!-- Search form -->
            <div class="search-form-container">
                <?php get_search_form(); ?>
            </div>
        </header>

        <?php if (have_posts()) : ?>
            
            <section class="search-results image-card-grid">
                <div class="container">
                    <div class="grid grid-3">
                    <?php
                    // Start the Loop
                    while (have_posts()) :
                        the_post();
                        
                        // Use card format for search results
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