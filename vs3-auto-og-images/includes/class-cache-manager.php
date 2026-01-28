<?php
/**
 * Cache Manager
 * Handles cache clearing and image regeneration
 */

if (!defined('ABSPATH')) {
    exit;
}

class VS3_Auto_OG_Cache_Manager {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Clear cache when site title changes
        add_action('update_option_blogname', array($this, 'on_site_title_change'), 10, 2);
        
        // Clear individual post image on post update
        add_action('save_post', array($this, 'on_post_save'), 10, 1);
        
        // Clear post image when featured image is added/removed
        add_action('added_post_meta', array($this, 'on_thumbnail_change'), 10, 4);
        add_action('updated_post_meta', array($this, 'on_thumbnail_change'), 10, 4);
        add_action('deleted_post_meta', array($this, 'on_thumbnail_change'), 10, 4);
    }
    
    /**
     * Bump cache version when site title changes
     */
    public function on_site_title_change($old_value, $new_value) {
        if ($old_value === $new_value) {
            return;
        }
        
        $this->bump_cache_version();
    }
    
    /**
     * Clear single post image on save
     */
    public function on_post_save($post_id) {
        // Avoid autosaves and revisions
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        $post = get_post($post_id);
        
        if (!$post || !in_array($post->post_type, array('post', 'page'))) {
            return;
        }
        
        // Only clear if no featured image
        if (!has_post_thumbnail($post_id)) {
            $generator = new VS3_Auto_OG_Image_Generator();
            $generator->delete_post_image($post_id);
        }
    }
    
    /**
     * Handle featured image changes
     */
    public function on_thumbnail_change($meta_id, $object_id, $meta_key, $meta_value) {
        if ($meta_key !== '_thumbnail_id') {
            return;
        }
        
        $generator = new VS3_Auto_OG_Image_Generator();
        $generator->delete_post_image($object_id);
    }
    
    /**
     * Bump cache version (clears all images)
     */
    public function bump_cache_version() {
        $new_version = time();
        
        if (is_multisite()) {
            $settings = get_site_option('vs3_auto_og_network_settings', array());
            $settings['cache_version'] = $new_version;
            update_site_option('vs3_auto_og_network_settings', $settings);
        } else {
            $settings = get_option('vs3_auto_og_site_settings', array());
            $settings['cache_version'] = $new_version;
            update_option('vs3_auto_og_site_settings', $settings);
        }
    }
    
    /**
     * Clear all OG images
     */
    public function clear_all_images() {
        $upload_dir = wp_upload_dir();
        $og_dir = $upload_dir['basedir'] . '/vs3-og';
        
        if (!file_exists($og_dir)) {
            return;
        }
        
        $files = glob($og_dir . '/*.png');
        foreach ($files as $file) {
            @unlink($file);
        }
        
        $this->bump_cache_version();
    }
}

