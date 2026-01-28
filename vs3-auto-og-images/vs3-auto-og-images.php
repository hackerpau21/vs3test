<?php
/**
 * Plugin Name: VS3 Auto OG Images
 * Plugin URI: https://github.com/yourusername/vs3-auto-og-images
 * Description: Auto-generates 4:3 OG images (1200×900) for posts and pages (except homepage) without Featured Images. Network-activatable with per-site settings.
 * Version: 1.1.0
 * Author: Pau Inocencio
 * Author URI: https://yoursite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: true
 * Text Domain: vs3-auto-og
 */

if (!defined('ABSPATH')) {
    exit;
}

define('VS3_AUTO_OG_VERSION', '1.1.0');
define('VS3_AUTO_OG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VS3_AUTO_OG_PLUGIN_URL', plugin_dir_url(__FILE__));

// Main plugin class
class VS3_Auto_OG_Images {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        require_once VS3_AUTO_OG_PLUGIN_DIR . 'includes/class-cloudflare-ai.php';
        require_once VS3_AUTO_OG_PLUGIN_DIR . 'includes/class-image-generator.php';
        require_once VS3_AUTO_OG_PLUGIN_DIR . 'includes/class-meta-injector.php';
        require_once VS3_AUTO_OG_PLUGIN_DIR . 'includes/class-settings.php';
        require_once VS3_AUTO_OG_PLUGIN_DIR . 'includes/class-cache-manager.php';
    }
    
    private function init_hooks() {
        // Network activation
        register_activation_hook(__FILE__, array($this, 'on_activation'));
        
        // Initialize components
        add_action('plugins_loaded', array($this, 'init_components'));
        
        // Settings
        if (is_admin()) {
            VS3_Auto_OG_Settings::get_instance();
            
            // AJAX handler for Cloudflare connection test
            add_action('wp_ajax_vs3_test_cloudflare_connection', array($this, 'ajax_test_cloudflare_connection'));
            
            // AJAX handler for preview image generation
            add_action('wp_ajax_vs3_generate_preview_image', array($this, 'ajax_generate_preview_image'));
        }
        
        // Meta injection
        VS3_Auto_OG_Meta_Injector::get_instance();
        
        // Cache management
        VS3_Auto_OG_Cache_Manager::get_instance();
        
        // Image serving endpoint
        add_action('init', array($this, 'add_rewrite_rules'));
        add_action('template_redirect', array($this, 'serve_og_image'));
    }
    
    public function init_components() {
        // Initialize any plugin components
    }
    
    public function on_activation() {
        // Create default network options
        if (is_multisite()) {
            add_site_option('vs3_auto_og_network_settings', array(
                'enabled' => true,
                'default_bg_color' => '#ffffff',
                'default_text_color' => '#000000',
                'default_accent_color' => '#0073aa',
                'cache_version' => time(),
            ));
        }
        
        // Flush rewrite rules
        $this->add_rewrite_rules();
        flush_rewrite_rules();
    }
    
    public function add_rewrite_rules() {
        add_rewrite_rule(
            '^vs3-og/([0-9]+)\.png$',
            'index.php?vs3_og_image=$matches[1]',
            'top'
        );
        add_rewrite_tag('%vs3_og_image%', '([0-9]+)');
    }
    
    public function serve_og_image() {
        $post_id = get_query_var('vs3_og_image');
        
        if (!$post_id) {
            return;
        }
        
        // Check if plugin is enabled
        if (!$this->is_enabled()) {
            status_header(404);
            exit;
        }
        
        // Clean all output buffers to prevent corrupted images
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $generator = new VS3_Auto_OG_Image_Generator();
        $image_path = $generator->get_or_generate_image($post_id);
        
        if ($image_path && file_exists($image_path)) {
            header('Content-Type: image/png');
            header('Content-Length: ' . filesize($image_path));
            header('Cache-Control: public, max-age=31536000');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
            
            readfile($image_path);
            exit;
        } else {
            status_header(404);
            exit;
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
    
    /**
     * AJAX handler for testing Cloudflare connection
     */
    public function ajax_test_cloudflare_connection() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vs3_test_cf_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'vs3-auto-og')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'vs3-auto-og')));
        }
        
        $account_id = isset($_POST['account_id']) ? sanitize_text_field($_POST['account_id']) : '';
        $api_token = isset($_POST['api_token']) ? sanitize_text_field($_POST['api_token']) : '';
        
        if (empty($account_id) || empty($api_token)) {
            wp_send_json_error(array('message' => __('Account ID and API Token are required', 'vs3-auto-og')));
        }
        
        // Test connection using provided credentials
        $api_url = sprintf(
            'https://api.cloudflare.com/client/v4/accounts/%s/ai/models/search',
            $account_id
        );
        
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_token,
            ),
            'timeout' => 15,
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code === 200) {
            wp_send_json_success(array('message' => __('Connection successful! Cloudflare AI is ready.', 'vs3-auto-og')));
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $error_message = isset($body['errors'][0]['message']) 
            ? $body['errors'][0]['message'] 
            : __('Unknown error', 'vs3-auto-og') . ' (HTTP ' . $response_code . ')';
        
        wp_send_json_error(array('message' => $error_message));
    }
    
    /**
     * AJAX handler for generating preview image
     */
    public function ajax_generate_preview_image() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'vs3_preview_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'vs3-auto-og')));
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission denied', 'vs3-auto-og')));
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id) {
            wp_send_json_error(array('message' => __('Invalid post ID', 'vs3-auto-og')));
        }
        
        $post = get_post($post_id);
        if (!$post) {
            wp_send_json_error(array('message' => __('Post not found', 'vs3-auto-og')));
        }
        
        // Delete existing image to force regeneration
        $generator = new VS3_Auto_OG_Image_Generator();
        $generator->delete_post_image($post_id);
        
        // Generate new image
        $image_path = $generator->get_or_generate_image($post_id);
        
        if (!$image_path || !file_exists($image_path)) {
            // Check if it's because post has featured image
            if (has_post_thumbnail($post_id)) {
                wp_send_json_error(array('message' => __('This post has a featured image. OG images are only generated for posts without featured images.', 'vs3-auto-og')));
            }
            wp_send_json_error(array('message' => __('Failed to generate image. Check your Cloudflare AI credentials or server error logs.', 'vs3-auto-og')));
        }
        
        // Get image URL
        $image_url = $generator->get_og_image_url($post_id);
        
        // Check if AI was actually used for this generation
        $ai_used = $generator->was_ai_used();
        
        // Also check if AI is enabled (for status info)
        $cf_ai = new VS3_Auto_OG_Cloudflare_AI();
        $ai_enabled = $cf_ai->is_enabled();
        
        // Get last error if AI failed
        $last_error = VS3_Auto_OG_Cloudflare_AI::get_last_error();
        
        if ($ai_used) {
            $method_text = 'Cloudflare AI + GD text overlay';
        } elseif ($ai_enabled && !empty($last_error)) {
            $method_text = 'Classic GD (AI Error: ' . esc_html($last_error) . ')';
        } elseif ($ai_enabled) {
            $method_text = 'Classic GD (AI failed - unknown error)';
        } else {
            $method_text = 'Classic GD with colorful shapes';
        }
        
        $info = sprintf(
            __('Post: "%s" (ID: %d) | Method: %s', 'vs3-auto-og'),
            $post->post_title,
            $post_id,
            $method_text
        );
        
        wp_send_json_success(array(
            'url' => $image_url,
            'info' => $info,
            'ai_used' => $ai_used,
        ));
    }
}

// Initialize the plugin
function vs3_auto_og_images() {
    return VS3_Auto_OG_Images::get_instance();
}

vs3_auto_og_images();

