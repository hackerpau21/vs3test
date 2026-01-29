<?php
/**
 * Cloudflare AI Integration
 * Generates OG image backgrounds using Cloudflare Workers AI
 */

if (!defined('ABSPATH')) {
    exit;
}

class VS3_Auto_OG_Cloudflare_AI {
    
    private $account_id;
    private $api_token;
    private $model;
    
    // Store the last error for debugging
    private static $last_error = '';
    
    // Available models with their characteristics
    private static $models = array(
        'flux-1-schnell' => array(
            'id' => '@cf/black-forest-labs/flux-1-schnell',
            'name' => 'Flux 1 Schnell',
            'description' => 'Fast, cost-effective (~$0.0007/image)',
            'default_steps' => 4,
            'max_steps' => 8,
            'format' => 'json', // Uses JSON format
            'supports_dimensions' => false, // Fixed output size
            'neurons_per_image' => 67, // Approximate neurons per image
        ),
        'flux-2-klein' => array(
            'id' => '@cf/black-forest-labs/flux-2-klein-4b',
            'name' => 'Flux 2 Klein',
            'description' => 'Good balance of speed and quality',
            'default_steps' => 4,
            'max_steps' => 8,
            'format' => 'multipart', // Uses multipart format
            'supports_dimensions' => true,
            'neurons_per_image' => 180, // Approximate neurons per 1200x900 image
        ),
        'leonardo-phoenix' => array(
            'id' => '@cf/leonardo/phoenix-1.0',
            'name' => 'Leonardo Phoenix',
            'description' => 'Premium quality, higher cost',
            'default_steps' => 20,
            'max_steps' => 30,
            'format' => 'multipart',
            'supports_dimensions' => true,
            'neurons_per_image' => 3200, // Approximate neurons per image
        ),
    );
    
    // Daily free tier limit
    const DAILY_FREE_NEURONS = 10000;
    
    public function __construct() {
        $this->load_credentials();
    }
    
    /**
     * Load API credentials from settings
     */
    private function load_credentials() {
        if (is_multisite()) {
            $network_settings = get_site_option('vs3_auto_og_network_settings', array());
            $site_settings = get_option('vs3_auto_og_site_settings', array());
            
            // Site settings override network settings
            $this->account_id = !empty($site_settings['cf_account_id']) 
                ? $site_settings['cf_account_id'] 
                : (!empty($network_settings['cf_account_id']) ? $network_settings['cf_account_id'] : '');
            
            $this->api_token = !empty($site_settings['cf_api_token']) 
                ? $site_settings['cf_api_token'] 
                : (!empty($network_settings['cf_api_token']) ? $network_settings['cf_api_token'] : '');
            
            $this->model = !empty($site_settings['cf_model']) 
                ? $site_settings['cf_model'] 
                : (!empty($network_settings['cf_model']) ? $network_settings['cf_model'] : 'flux-1-schnell');
        } else {
            $settings = get_option('vs3_auto_og_site_settings', array());
            $this->account_id = !empty($settings['cf_account_id']) ? $settings['cf_account_id'] : '';
            $this->api_token = !empty($settings['cf_api_token']) ? $settings['cf_api_token'] : '';
            $this->model = !empty($settings['cf_model']) ? $settings['cf_model'] : 'flux-1-schnell';
        }
    }
    
    /**
     * Check if Cloudflare AI is configured and enabled
     */
    public function is_enabled() {
        if (empty($this->account_id) || empty($this->api_token)) {
            return false;
        }
        
        // Check if AI generation is enabled in settings
        if (is_multisite()) {
            $network_settings = get_site_option('vs3_auto_og_network_settings', array());
            $site_settings = get_option('vs3_auto_og_site_settings', array());
            
            if (array_key_exists('cf_enabled', $site_settings)) {
                return (bool)$site_settings['cf_enabled'];
            }
            
            return isset($network_settings['cf_enabled']) ? (bool)$network_settings['cf_enabled'] : false;
        }
        
        $settings = get_option('vs3_auto_og_site_settings', array());
        return isset($settings['cf_enabled']) ? (bool)$settings['cf_enabled'] : false;
    }
    
    /**
     * Get available models
     */
    public static function get_available_models() {
        return self::$models;
    }
    
    /**
     * Generate an image using Cloudflare AI
     * 
     * @param string $prompt The image generation prompt
     * @param int $width Image width (default 1200)
     * @param int $height Image height (default 900)
     * @return string|false Raw image data on success, false on failure
     */
    public function generate_image($prompt, $width = 1200, $height = 900) {
        // Clear previous error
        self::$last_error = '';
        
        if (!$this->is_enabled()) {
            $this->log_error('Cloudflare AI is not enabled. Account ID: ' . (!empty($this->account_id) ? 'set' : 'empty') . ', API Token: ' . (!empty($this->api_token) ? 'set' : 'empty'));
            return false;
        }
        
        $model_config = isset(self::$models[$this->model]) 
            ? self::$models[$this->model] 
            : self::$models['flux-1-schnell'];
        
        $api_url = sprintf(
            'https://api.cloudflare.com/client/v4/accounts/%s/ai/run/%s',
            $this->account_id,
            $model_config['id']
        );
        
        $this->log_error('Generating image with model: ' . $model_config['id'] . ' (format: ' . $model_config['format'] . ')');
        $this->log_error('Prompt: ' . substr($prompt, 0, 100) . '...');
        
        // Build request based on model format
        if ($model_config['format'] === 'json') {
            // JSON format for Flux-1-schnell
            $body_data = array(
                'prompt' => $prompt,
                'steps' => min($model_config['default_steps'], $model_config['max_steps']),
            );
            
            $response = wp_remote_post($api_url, array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_token,
                    'Content-Type' => 'application/json',
                ),
                'body' => wp_json_encode($body_data),
                'timeout' => 120,
            ));
        } else {
            // Multipart format for Flux-2 and other models
            $boundary = wp_generate_password(24, false);
            $multipart_data = array(
                'prompt' => $prompt,
                'steps' => $model_config['default_steps'],
            );
            
            // Add dimensions if supported
            if (!empty($model_config['supports_dimensions'])) {
                $multipart_data['width'] = $width;
                $multipart_data['height'] = $height;
            }
            
            $body = $this->build_multipart_body($boundary, $multipart_data);
            
            $response = wp_remote_post($api_url, array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->api_token,
                    'Content-Type' => 'multipart/form-data; boundary=' . $boundary,
                ),
                'body' => $body,
                'timeout' => 120,
            ));
        }
        
        if (is_wp_error($response)) {
            $this->log_error('API request failed: ' . $response->get_error_message());
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $content_type = wp_remote_retrieve_header($response, 'content-type');
        $response_body = wp_remote_retrieve_body($response);
        
        $this->log_error('API response code: ' . $response_code . ', content-type: ' . $content_type . ', body length: ' . strlen($response_body));
        
        if ($response_code !== 200) {
            // Parse error response to check for specific error types
            $error_data = json_decode($response_body, true);
            
            if (isset($error_data['errors']) && is_array($error_data['errors'])) {
                foreach ($error_data['errors'] as $error) {
                    $error_message = isset($error['message']) ? $error['message'] : '';
                    $error_code = isset($error['code']) ? $error['code'] : '';
                    
                    // Check for NSFW/content filter errors
                    if (stripos($error_message, 'NSFW') !== false || 
                        stripos($error_message, 'content') !== false ||
                        $error_code == 3030) {
                        $this->log_error('Content filter blocked prompt (NSFW/content policy): ' . $error_message);
                        self::$last_error = 'Content filter blocked: ' . $error_message . '. Try removing words like "cocktail", "party", or other potentially flagged terms from your prompt.';
                        return false;
                    }
                }
            }
            
            $this->log_error('API returned error ' . $response_code . ': ' . $response_body);
            return false;
        }
        
        // Check if response is a direct image (binary)
        if (strpos($content_type, 'image/') === 0) {
            $this->log_error('Received direct image response');
            return $response_body;
        }
        
        // Response is JSON with base64 encoded image
        $data = json_decode($response_body, true);
        
        // Standard Cloudflare AI response format: result.image contains base64 string
        if (isset($data['result']['image'])) {
            $this->log_error('Received base64 image in result.image');
            $image_data = base64_decode($data['result']['image']);
            if ($image_data !== false) {
                return $image_data;
            }
            $this->log_error('Failed to decode base64 image');
        }
        
        // Alternative: image directly in result (older format)
        if (isset($data['result']) && is_string($data['result'])) {
            $this->log_error('Received data in result string');
            $image_data = base64_decode($data['result']);
            if ($image_data !== false) {
                return $image_data;
            }
        }
        
        // Alternative: image directly at top level
        if (isset($data['image'])) {
            $this->log_error('Received base64 image in image field');
            $image_data = base64_decode($data['image']);
            if ($image_data !== false) {
                return $image_data;
            }
        }
        
        // Log what we actually received
        if (is_array($data)) {
            $this->log_error('Unexpected response format. Keys: ' . implode(', ', array_keys($data)));
            if (isset($data['errors'])) {
                $this->log_error('Errors: ' . wp_json_encode($data['errors']));
            }
            if (isset($data['messages'])) {
                $this->log_error('Messages: ' . wp_json_encode($data['messages']));
            }
        } else {
            $this->log_error('Response is not valid JSON. First 200 chars: ' . substr($response_body, 0, 200));
        }
        
        return false;
    }
    
    /**
     * Build multipart/form-data body
     * 
     * @param string $boundary The boundary string
     * @param array $fields The form fields
     * @return string The multipart body
     */
    private function build_multipart_body($boundary, $fields) {
        $body = '';
        
        foreach ($fields as $name => $value) {
            $body .= '--' . $boundary . "\r\n";
            $body .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
            $body .= $value . "\r\n";
        }
        
        $body .= '--' . $boundary . '--' . "\r\n";
        
        return $body;
    }
    
    /**
     * Generate a prompt for OG image background based on post content
     *
     * @param WP_Post $post The post object
     * @param array $settings Design settings
     * @return string The generated prompt
     */
    public function generate_prompt($post, $settings = array()) {
        // Always use the X-marks pattern regardless of style setting
        // This creates a clean, consistent look across all OG images
        $prompt = $this->get_xmarks_prompt();

        // Sanitize prompt to avoid false NSFW positives
        $prompt = $this->sanitize_prompt($prompt);

        // Log the final prompt for debugging
        $this->log_error('Final prompt: ' . substr($prompt, 0, 200));

        return $prompt;
    }

    /**
     * Get the X-marks pattern prompt
     * Creates a white background with medium-sized colorful X marks in a pattern
     *
     * @return string The X-marks prompt
     */
    private function get_xmarks_prompt() {
        return 'Create a simple, clean background image with a white or very light background. ' .
               'Place medium-sized X marks arranged in a regular grid or repeating pattern across the entire image. ' .
               'Each X mark should be made of two intersecting straight lines forming an X shape. ' .
               'The X marks should be in different bright, cheerful flat colors: red, blue, green, yellow, orange, pink, and purple. ' .
               'All X marks should be approximately the same size - medium sized, clearly visible but not too large. ' .
               'The X marks should be evenly spaced in a consistent pattern like a wallpaper or textile design. ' .
               'CRITICAL REQUIREMENTS: ' .
               '1. Background MUST be pure white or very light gray - no colors, no gradients. ' .
               '2. Use ONLY flat solid colors for the X marks - absolutely NO gradients, NO color blends, NO shadows. ' .
               '3. Each X mark must have sharp, crisp, clean edges - no blur, no glow, no soft edges. ' .
               '4. NO flowing shapes, NO organic curves, NO wavy lines, NO abstract art. ' .
               '5. Think of simple geometric stamps or stickers arranged neatly on white paper. ' .
               '6. The pattern should look like a cheerful, colorful, minimalist wallpaper design. ' .
               'FORBIDDEN: gradients, color transitions, flowing colors, organic shapes, abstract swirls, bokeh, blur effects.';
    }
    
    /**
     * Sanitize prompt to reduce false NSFW content filter triggers
     * Only replaces common false positives that are safe to substitute
     * 
     * @param string $prompt The original prompt
     * @return string Sanitized prompt
     */
    private function sanitize_prompt($prompt) {
        // Only replace words that are clearly false positives and have safe alternatives
        // This helps avoid Cloudflare's content filter blocking innocent words
        $replacements = array(
            // Common false positives in post titles/content
            'cocktail' => 'beverage',
            'party' => 'gathering',
        );
        
        // Apply replacements conservatively - only whole words
        foreach ($replacements as $word => $replacement) {
            // Match whole word only (case-insensitive)
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';
            $prompt = preg_replace($pattern, $replacement, $prompt);
        }
        
        return $prompt;
    }
    
    /**
     * Get default prompt template
     * Note: This is kept for backwards compatibility but the plugin now uses the X-marks pattern by default
     */
    public function get_default_prompt_template() {
        return $this->get_xmarks_prompt();
    }
    
    /**
     * Get style description for prompt
     */
    private function get_style_description($style) {
        $styles = array(
            'abstract' => 'Modern abstract art with flowing organic shapes, bold color blocks, and dynamic composition. Avoid gradients - use solid colors and shapes instead.',
            'geometric' => 'Clean geometric design with sharp lines, triangles, circles, squares, and polygons. Use flat colors, no gradients. Structured, architectural feel.',
            'gradient' => 'Smooth color gradients with soft transitions between colors. Dreamy, atmospheric feel with flowing color blends.',
            'nature' => 'Abstract nature-inspired design with organic shapes reminiscent of leaves, water, clouds, or landscapes. Earthy and natural color palette.',
            'tech' => 'Futuristic tech aesthetic with circuit patterns, digital grid lines, hexagons, and neon accents. Cyberpunk-inspired, high-tech feel.',
            'minimal' => 'Extremely minimalist design with simple shapes, lots of white space, subtle lines, and restrained color palette. Clean and uncluttered.',
            'vibrant' => 'Bold, energetic design with bright saturated colors, dynamic patterns, and high contrast. Eye-catching and lively composition.',
            'professional' => 'Corporate professional style with subtle patterns, muted colors, clean lines, and sophisticated composition. Business-appropriate aesthetic.',
            'x-marks' => 'Clean white background with scattered X marks in flat solid colors (light green, pink, yellow, blue, light grey). Each X is a simple geometric shape - two intersecting lines. Consistent sizing, even distribution. NO gradients, NO flowing shapes, NO organic curves. Sharp crisp edges only.',
        );
        
        return isset($styles[$style]) ? $styles[$style] : $styles['abstract'];
    }
    
    /**
     * Get available styles
     */
    public static function get_available_styles() {
        return array(
            'abstract' => 'Abstract',
            'geometric' => 'Geometric',
            'gradient' => 'Gradient',
            'nature' => 'Nature-inspired',
            'tech' => 'Tech/Futuristic',
            'minimal' => 'Minimal',
            'vibrant' => 'Vibrant/Bold',
            'professional' => 'Professional/Corporate',
            'x-marks' => 'X Marks Pattern',
        );
    }
    
    /**
     * Test API connection
     * 
     * @return array Result with 'success' and 'message' keys
     */
    public function test_connection() {
        if (empty($this->account_id) || empty($this->api_token)) {
            return array(
                'success' => false,
                'message' => 'Account ID and API Token are required',
            );
        }
        
        // Try to list available models to verify credentials
        $api_url = sprintf(
            'https://api.cloudflare.com/client/v4/accounts/%s/ai/models/search',
            $this->account_id
        );
        
        $response = wp_remote_get($api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_token,
            ),
            'timeout' => 15,
        ));
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Connection failed: ' . $response->get_error_message(),
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code === 200) {
            return array(
                'success' => true,
                'message' => 'Connection successful! Cloudflare AI is ready to use.',
            );
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        $error_message = isset($body['errors'][0]['message']) 
            ? $body['errors'][0]['message'] 
            : 'Unknown error (HTTP ' . $response_code . ')';
        
        return array(
            'success' => false,
            'message' => 'API error: ' . $error_message,
        );
    }
    
    /**
     * Log error for debugging
     */
    private function log_error($message) {
        self::$last_error = $message;
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[VS3 Auto OG - Cloudflare AI] ' . $message);
        }
    }
    
    /**
     * Get the last error message
     * 
     * @return string The last error message
     */
    public static function get_last_error() {
        return self::$last_error;
    }
    
    /**
     * Clear the last error
     */
    public static function clear_last_error() {
        self::$last_error = '';
    }
    
    /**
     * Record neuron usage for an image generation
     * 
     * @param string $model_key The model used
     */
    public static function record_usage($model_key = 'flux-1-schnell') {
        $usage = self::get_daily_usage();
        $model_config = isset(self::$models[$model_key]) ? self::$models[$model_key] : self::$models['flux-1-schnell'];
        $neurons = isset($model_config['neurons_per_image']) ? $model_config['neurons_per_image'] : 67;
        
        $usage['neurons'] += $neurons;
        $usage['images'] += 1;
        $usage['date'] = gmdate('Y-m-d'); // UTC date
        
        update_option('vs3_auto_og_cf_usage', $usage, false);
    }
    
    /**
     * Get daily usage statistics
     * 
     * @return array Usage data with 'neurons', 'images', 'date'
     */
    public static function get_daily_usage() {
        $usage = get_option('vs3_auto_og_cf_usage', array(
            'neurons' => 0,
            'images' => 0,
            'date' => gmdate('Y-m-d'),
        ));
        
        // Reset if it's a new day (UTC)
        $today = gmdate('Y-m-d');
        if (!isset($usage['date']) || $usage['date'] !== $today) {
            $usage = array(
                'neurons' => 0,
                'images' => 0,
                'date' => $today,
            );
            update_option('vs3_auto_og_cf_usage', $usage, false);
        }
        
        return $usage;
    }
    
    /**
     * Check if daily limit is reached or close
     * 
     * @return array Status with 'reached', 'warning', 'percent', 'remaining'
     */
    public static function check_daily_limit() {
        $usage = self::get_daily_usage();
        $neurons_used = $usage['neurons'];
        $percent = min(100, round(($neurons_used / self::DAILY_FREE_NEURONS) * 100, 1));
        $remaining = max(0, self::DAILY_FREE_NEURONS - $neurons_used);
        
        return array(
            'reached' => $neurons_used >= self::DAILY_FREE_NEURONS,
            'warning' => $neurons_used >= (self::DAILY_FREE_NEURONS * 0.8), // 80% warning
            'percent' => $percent,
            'remaining' => $remaining,
            'used' => $neurons_used,
            'limit' => self::DAILY_FREE_NEURONS,
            'images_generated' => $usage['images'],
        );
    }
    
    /**
     * Get estimated images remaining for the day
     * 
     * @param string $model_key The model to estimate for
     * @return int Estimated images remaining
     */
    public static function get_images_remaining($model_key = 'flux-1-schnell') {
        $status = self::check_daily_limit();
        $model_config = isset(self::$models[$model_key]) ? self::$models[$model_key] : self::$models['flux-1-schnell'];
        $neurons_per_image = isset($model_config['neurons_per_image']) ? $model_config['neurons_per_image'] : 67;
        
        return floor($status['remaining'] / $neurons_per_image);
    }
    
    /**
     * Reset daily usage (manual reset)
     */
    public static function reset_usage() {
        $usage = array(
            'neurons' => 0,
            'images' => 0,
            'date' => gmdate('Y-m-d'),
        );
        update_option('vs3_auto_og_cf_usage', $usage, false);
    }
}
