<?php
/**
 * Meta Tag Injector
 * Injects OG meta tags on singular post/page views
 */

if (!defined('ABSPATH')) {
    exit;
}

class VS3_Auto_OG_Meta_Injector {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_head', array($this, 'inject_og_meta'), 5);
    }
    
    /**
     * Inject OG meta tags
     */
    public function inject_og_meta() {
        if (!is_singular(array('post', 'page'))) {
            return;
        }
        
        // Exclude homepage/front page
        if (is_front_page() || is_home()) {
            return;
        }
        
        global $post;
        
        if (!$post) {
            return;
        }
        
        // Check if plugin is enabled
        if (!$this->is_enabled()) {
            return;
        }
        
        // Only inject if no featured image
        if (has_post_thumbnail($post->ID)) {
            return;
        }
        
        $generator = new VS3_Auto_OG_Image_Generator();
        $og_image_url = $generator->get_og_image_url($post->ID);
        
        if ($og_image_url) {
            echo "\n<!-- VS3 Auto OG Images -->\n";
            echo '<meta property="og:image" content="' . esc_url($og_image_url) . '" />' . "\n";
            echo '<meta property="og:image:width" content="1200" />' . "\n";
            echo '<meta property="og:image:height" content="900" />' . "\n";
            echo '<meta property="og:image:type" content="image/png" />' . "\n";
            
            // Add Twitter Card tags
            echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
            echo '<meta name="twitter:image" content="' . esc_url($og_image_url) . '" />' . "\n";
            echo "<!-- /VS3 Auto OG Images -->\n\n";
        }
    }
    
    /**
     * Check if plugin is enabled for current site
     */
    private function is_enabled() {
        if (is_multisite()) {
            $network_settings = get_site_option('vs3_auto_og_network_settings', array());
            $site_settings = get_option('vs3_auto_og_site_settings', array());
            
            // Check if site has explicitly set enabled (including false)
            if (array_key_exists('enabled', $site_settings)) {
                return (bool)$site_settings['enabled'];
            }
            
            // Fall back to network default
            return isset($network_settings['enabled']) ? (bool)$network_settings['enabled'] : true;
        }
        
        $settings = get_option('vs3_auto_og_site_settings', array());
        
        // Check if enabled has been explicitly set (including false)
        if (array_key_exists('enabled', $settings)) {
            return (bool)$settings['enabled'];
        }
        
        // Default to enabled
        return true;
    }
}

