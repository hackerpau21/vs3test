<?php
/**
 * Template for displaying search forms
 *
 * @package tripeak-test-seven
 */

$unique_id = wp_unique_id('search-form-');
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <label for="<?php echo esc_attr($unique_id); ?>" class="screen-reader-text">
        <?php esc_html_e('Search for:', 'tripeak-test-seven'); ?>
    </label>
    
    <div class="search-input-container">
        <input 
            type="search" 
            id="<?php echo esc_attr($unique_id); ?>" 
            class="search-field" 
            placeholder="<?php echo esc_attr_x('Search...', 'placeholder', 'tripeak-test-seven'); ?>" 
            value="<?php echo get_search_query(); ?>" 
            name="s" 
            required
        />
        
        <button type="submit" class="search-submit" aria-label="<?php esc_attr_e('Search', 'tripeak-test-seven'); ?>">
            <i class="fas fa-search" aria-hidden="true"></i>
            <span class="search-submit-text"><?php esc_html_e('Search', 'tripeak-test-seven'); ?></span>
        </button>
    </div>
</form> 