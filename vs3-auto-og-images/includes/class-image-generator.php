<?php
/**
 * OG Image Generator
 * Generates 1200x900 PNG images with logo, post title, and site name
 */

if (!defined('ABSPATH')) {
    exit;
}

class VS3_Auto_OG_Image_Generator {
    
    private $width = 1200;
    private $height = 900;
    private $logo_max_width = 300;
    private $logo_max_height = 200;
    
    // Track whether AI was used for the last generated image
    private $last_generation_used_ai = false;
    
    public function __construct() {
        // Check for GD library
        if (!extension_loaded('gd')) {
            add_action('admin_notices', array($this, 'gd_missing_notice'));
        }
    }
    
    public function gd_missing_notice() {
        echo '<div class="notice notice-error"><p>';
        echo __('VS3 Auto OG Images requires the GD library to be installed.', 'vs3-auto-og');
        echo '</p></div>';
    }
    
    /**
     * Check if the last generated image used AI
     * 
     * @return bool True if AI was used, false otherwise
     */
    public function was_ai_used() {
        return $this->last_generation_used_ai;
    }
    
    /**
     * Get the current AI model from settings
     * 
     * @return string Model key
     */
    private function get_current_model() {
        if (is_multisite()) {
            $network_settings = get_site_option('vs3_auto_og_network_settings', array());
            $site_settings = get_option('vs3_auto_og_site_settings', array());
            
            return !empty($site_settings['cf_model']) 
                ? $site_settings['cf_model'] 
                : (!empty($network_settings['cf_model']) ? $network_settings['cf_model'] : 'flux-1-schnell');
        }
        
        $settings = get_option('vs3_auto_og_site_settings', array());
        return !empty($settings['cf_model']) ? $settings['cf_model'] : 'flux-1-schnell';
    }
    
    /**
     * Get or generate OG image for a post
     */
    public function get_or_generate_image($post_id) {
        $post = get_post($post_id);
        
        if (!$post || !in_array($post->post_type, array('post', 'page'))) {
            return false;
        }
        
        // Exclude homepage/front page
        if ($post_id == get_option('page_on_front') || $post_id == get_option('page_for_posts')) {
            return false;
        }
        
        // Check if post has featured image
        if (has_post_thumbnail($post_id)) {
            return false;
        }
        
        $upload_dir = wp_upload_dir();
        $og_dir = $upload_dir['basedir'] . '/vs3-og';
        
        // Create directory if it doesn't exist
        if (!file_exists($og_dir)) {
            wp_mkdir_p($og_dir);
            
            // Add .htaccess to protect directory
            $htaccess = $og_dir . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "# Protect OG images directory\n<FilesMatch \"\\.png$\">\n    Allow from all\n</FilesMatch>");
            }
        }
        
        $cache_version = $this->get_cache_version();
        $image_path = $og_dir . '/' . $post_id . '-v' . $cache_version . '.png';
        
        // Check if image already exists
        if (file_exists($image_path)) {
            return $image_path;
        }
        
        // Clean up old versions
        $this->cleanup_old_versions($og_dir, $post_id, $cache_version);
        
        // Generate new image
        return $this->generate_image($post, $image_path);
    }
    
    /**
     * Get the URL for an OG image
     */
    public function get_og_image_url($post_id) {
        $cache_version = $this->get_cache_version();
        return home_url('/vs3-og/' . $post_id . '.png?v=' . $cache_version);
    }
    
    /**
     * Generate the actual image
     */
    private function generate_image($post, $image_path) {
        if (!extension_loaded('gd')) {
            return false;
        }
        
        // Reset AI usage flag
        $this->last_generation_used_ai = false;
        
        // Get settings
        $settings = $this->get_settings();
        
        // Try Cloudflare AI first if enabled
        $cf_ai = new VS3_Auto_OG_Cloudflare_AI();
        $ai_background = null;
        
        if ($cf_ai->is_enabled()) {
            // Check if daily limit is reached
            $limit_status = VS3_Auto_OG_Cloudflare_AI::check_daily_limit();
            
            if ($limit_status['reached']) {
                // Daily limit reached, skip AI and use fallback
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[VS3 Auto OG] Daily neuron limit reached, using GD fallback');
                }
            } else {
                $ai_background = $this->generate_ai_background($cf_ai, $post, $settings);
                if ($ai_background) {
                    $this->last_generation_used_ai = true;
                    // Record usage
                    $current_model = $this->get_current_model();
                    VS3_Auto_OG_Cloudflare_AI::record_usage($current_model);
                }
            }
        }
        
        // Create image from AI background or create blank canvas
        if ($ai_background) {
            $image = $ai_background;
        } else {
            // Fallback to classic GD-based generation
            $image = imagecreatetruecolor($this->width, $this->height);
            
            // Enable anti-aliasing
            imageantialias($image, true);
            
            // Allocate colors
            $bg_color = $this->hex_to_rgb($settings['bg_color']);
            $bg = imagecolorallocate($image, $bg_color[0], $bg_color[1], $bg_color[2]);
            
            // Fill background
            imagefill($image, 0, 0, $bg);
            
            // Draw scattered colorful shapes in the background
            $this->draw_scattered_shapes($image, $post->ID);
        }
        
        // Enable anti-aliasing for text overlay
        imageantialias($image, true);
        
        // Allocate colors for text
        $text_color = $this->hex_to_rgb($settings['text_color']);
        $accent_color = $this->hex_to_rgb($settings['accent_color']);
        
        $text = imagecolorallocate($image, $text_color[0], $text_color[1], $text_color[2]);
        $accent = imagecolorallocate($image, $accent_color[0], $accent_color[1], $accent_color[2]);
        
        // Layout positioning - left margin for left-aligned content
        $margin_x = 100;
        
        // Get post title for height calculation
        $post_title = html_entity_decode(get_the_title($post->ID), ENT_QUOTES, 'UTF-8');
        
        // Check if logo exists
        $custom_logo_id = get_theme_mod('custom_logo');
        $has_logo = !empty($custom_logo_id);
        
        // Calculate approximate content height for vertical centering
        $logo_block_height = $has_logo ? ($this->logo_max_height + 60) : 0; // More space after logo
        $estimated_lines = min(4, max(1, ceil(strlen($post_title) / 30)));
        $title_block_height = $estimated_lines * 58;
        $subtitle_block_height = 30;
        $gap_title_subtitle = 15; // Reduced gap between title and subtitle
        
        $total_height = $logo_block_height + $title_block_height + $gap_title_subtitle + $subtitle_block_height;
        
        // Center vertically
        $content_start_y = ($this->height - $total_height) / 2;
        $content_start_y = max(100, $content_start_y); // Minimum top margin
        
        // Add logo (left-aligned, vertically centered with content, moved 20px higher)
        $logo_y = $content_start_y - 20;
        $logo_drawn = $this->draw_logo($image, $margin_x, $logo_y);
        
        // Title position (below logo with more spacing, or at content start)
        $title_y = $logo_drawn ? $logo_y + $this->logo_max_height + 60 : $content_start_y;
        
        // Calculate text dimensions for background box
        $text_box_info = $this->calculate_text_box($post_title, $margin_x, $title_y, 85);
        
        // Subtitle "a blog post by [site name]" - closer to title
        $site_name = get_bloginfo('name');
        $subtitle = 'a blog post by ' . $site_name;
        $subtitle_y = $title_y + ($text_box_info['title_lines'] * 58) + $gap_title_subtitle;
        
        // Calculate subtitle dimensions
        $subtitle_width = $this->get_text_width($subtitle, 'space-mono-regular');
        $text_box_info['width'] = max($text_box_info['width'], $subtitle_width);
        
        // Calculate total height including subtitle
        // Title height + gap + subtitle height (approximately 30px for 26px font)
        $text_box_info['height'] = ($text_box_info['title_lines'] * 58) + $gap_title_subtitle + 30;
        
        // Draw white rounded rectangle background behind text with 30px padding on all sides
        $padding = 30; // Padding on all sides (10px original + 20px additional)
        
        $this->draw_rounded_rectangle($image, 
            $text_box_info['x'] - $padding, // Left position
            $title_y - 40 - $padding, // Top position (accounting for font baseline)
            $text_box_info['width'] + ($padding * 2), // Width with padding
            $text_box_info['height'] + ($padding * 2) + 5, // Height with padding
            10, // Corner radius
            imagecolorallocate($image, 255, 255, 255) // White
        );
        
        // Draw post title (large, bold, left-aligned) - using League Spartan Bold
        $title_lines = $this->draw_wrapped_text($image, $post_title, $margin_x, $title_y, 85, $text, 'league-spartan-bold');
        
        // Draw subtitle
        $this->draw_subtitle($image, $subtitle, $margin_x, $subtitle_y, $accent);
        
        // Save image
        $result = imagepng($image, $image_path, 9);
        imagedestroy($image);
        
        return $result ? $image_path : false;
    }
    
    /**
     * Generate AI background using Cloudflare AI
     * 
     * @param VS3_Auto_OG_Cloudflare_AI $cf_ai The Cloudflare AI instance
     * @param WP_Post $post The post object
     * @param array $settings Current settings
     * @return resource|false GD image resource on success, false on failure
     */
    private function generate_ai_background($cf_ai, $post, $settings) {
        // Generate prompt
        $prompt = $cf_ai->generate_prompt($post, $settings);
        
        // Generate image via API
        $image_data = $cf_ai->generate_image($prompt, $this->width, $this->height);
        
        if (!$image_data) {
            return false;
        }
        
        // Create GD image from raw data
        $image = @imagecreatefromstring($image_data);
        
        if (!$image) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[VS3 Auto OG] Failed to create image from Cloudflare AI response');
            }
            return false;
        }
        
        // Ensure correct dimensions (resize if needed)
        $current_width = imagesx($image);
        $current_height = imagesy($image);
        
        if ($current_width !== $this->width || $current_height !== $this->height) {
            $resized = imagecreatetruecolor($this->width, $this->height);
            imagecopyresampled(
                $resized, $image,
                0, 0, 0, 0,
                $this->width, $this->height,
                $current_width, $current_height
            );
            imagedestroy($image);
            $image = $resized;
        }
        
        // Note: No overlay added - AI backgrounds are designed to work with text
        // The prompt asks for space on the left side for text
        
        return $image;
    }
    
    /**
     * Draw scattered colorful shapes across the image
     */
    private function draw_scattered_shapes($image, $post_id) {
        // Playful color palette
        $colors = array(
            array(72, 199, 190),    // Teal
            array(237, 85, 101),    // Coral/Red
            array(248, 202, 68),    // Yellow
            array(236, 112, 160),   // Pink
            array(100, 180, 120),   // Green
            array(150, 120, 200),   // Purple
            array(200, 200, 200),   // Light gray
            array(160, 160, 160),   // Medium gray
        );
        
        // Use post ID for consistent but unique pattern per post
        mt_srand($post_id * 12345);
        
        // Define content safe zone (where text will be - centered vertically)
        $safe_zone = array(
            'x1' => 70,
            'y1' => 180,
            'x2' => 700,
            'y2' => 720
        );
        
        // Generate shapes in different zones for better coverage
        
        // Zone 1: Top area
        for ($i = 0; $i < mt_rand(12, 18); $i++) {
            $x = mt_rand(50, $this->width - 50);
            $y = mt_rand(40, 160);
            $this->draw_random_shape($image, $x, $y, $colors);
        }
        
        // Zone 2: Right side (main decorative area)
        for ($i = 0; $i < mt_rand(20, 28); $i++) {
            $x = mt_rand(720, $this->width - 50);
            $y = mt_rand(100, $this->height - 100);
            $this->draw_random_shape($image, $x, $y, $colors);
        }
        
        // Zone 3: Bottom area
        for ($i = 0; $i < mt_rand(15, 22); $i++) {
            $x = mt_rand(50, $this->width - 50);
            $y = mt_rand($this->height - 180, $this->height - 40);
            $this->draw_random_shape($image, $x, $y, $colors);
        }
        
        // Zone 4: Left side (sparse, avoid content)
        for ($i = 0; $i < mt_rand(5, 10); $i++) {
            $x = mt_rand(40, 90);
            $y = mt_rand(40, $this->height - 40);
            // Skip middle area where content is
            if ($y > 160 && $y < 740) continue;
            $this->draw_random_shape($image, $x, $y, $colors);
        }
        
        // Reset random seed
        mt_srand();
    }
    
    /**
     * Draw a random shape at position
     */
    private function draw_random_shape($image, $x, $y, $colors) {
        // Pick random color
        $color_data = $colors[mt_rand(0, count($colors) - 1)];
        
        // Randomly make some shapes lighter (opacity effect)
        if (mt_rand(1, 10) <= 4) {
            $color_data[0] = min(255, $color_data[0] + 60);
            $color_data[1] = min(255, $color_data[1] + 60);
            $color_data[2] = min(255, $color_data[2] + 60);
        }
        
        $color = imagecolorallocate($image, $color_data[0], $color_data[1], $color_data[2]);
        
        // Random size
        $size = mt_rand(10, 30);
        
        // Random shape type
        $shape_type = mt_rand(1, 10);
        
        if ($shape_type <= 5) {
            // Circle (most common)
            imagefilledellipse($image, $x, $y, $size, $size, $color);
        } elseif ($shape_type <= 7) {
            // Small plus sign
            $this->draw_plus($image, $x, $y, $size / 2, $color);
        } elseif ($shape_type <= 9) {
            // Small square
            $half = $size / 3;
            imagefilledrectangle($image, $x - $half, $y - $half, $x + $half, $y + $half, $color);
        } else {
            // Ring (circle outline)
            imageellipse($image, $x, $y, $size, $size, $color);
            if ($size > 15) {
                imageellipse($image, $x, $y, $size - 2, $size - 2, $color);
            }
        }
    }
    
    /**
     * Draw a plus sign shape
     */
    private function draw_plus($image, $x, $y, $size, $color) {
        $thickness = max(2, $size / 3);
        
        // Horizontal bar
        imagefilledrectangle($image, 
            $x - $size, $y - $thickness/2, 
            $x + $size, $y + $thickness/2, 
            $color
        );
        
        // Vertical bar
        imagefilledrectangle($image, 
            $x - $thickness/2, $y - $size, 
            $x + $thickness/2, $y + $size, 
            $color
        );
    }
    
    /**
     * Calculate text box dimensions for title
     * 
     * @param string $text The title text
     * @param int $x Starting X position
     * @param int $y Starting Y position
     * @param int $max_width_percent Maximum width percentage
     * @return array Box info with x, y, width, height, title_lines
     */
    private function calculate_text_box($text, $x, $y, $max_width_percent) {
        $max_width = ($this->width * $max_width_percent / 100) - ($x * 2);
        $font_file = $this->get_font_by_name('league-spartan-bold');
        
        if (!$font_file) {
            $font_file = $this->get_font_path('bold');
        }
        
        $font_size = 48;
        $words = explode(' ', $text);
        $lines = array();
        $current_line = '';
        $max_line_width = 0;
        
        foreach ($words as $word) {
            $test_line = $current_line . ($current_line ? ' ' : '') . $word;
            if ($font_file) {
                $bbox = imagettfbbox($font_size, 0, $font_file, $test_line);
                $width = $bbox[2] - $bbox[0];
            } else {
                // Fallback calculation
                $width = strlen($test_line) * 30; // Approximate
            }
            
            if ($width > $max_width && $current_line !== '') {
                $lines[] = $current_line;
                $current_line = $word;
            } else {
                $current_line = $test_line;
            }
            
            if ($width > $max_line_width) {
                $max_line_width = $width;
            }
        }
        
        if ($current_line) {
            $lines[] = $current_line;
        }
        
        $lines = array_slice($lines, 0, 4);
        $title_lines = count($lines);
        
        return array(
            'x' => $x,
            'y' => $y,
            'width' => min($max_line_width, $max_width),
            'height' => $title_lines * 58,
            'title_lines' => $title_lines,
        );
    }
    
    /**
     * Get text width for subtitle
     * 
     * @param string $text The subtitle text
     * @param string $font_name Font name
     * @return int Text width in pixels
     */
    private function get_text_width($text, $font_name) {
        $font_file = $this->get_font_by_name($font_name);
        
        if (!$font_file) {
            $font_file = $this->get_font_path('normal');
        }
        
        if ($font_file) {
            $font_size = 26;
            $bbox = imagettfbbox($font_size, 0, $font_file, $text);
            return $bbox[2] - $bbox[0];
        }
        
        // Fallback
        return strlen($text) * 15;
    }
    
    /**
     * Draw a rounded rectangle
     * 
     * @param resource $image GD image resource
     * @param int $x1 Top-left X
     * @param int $y1 Top-left Y
     * @param int $width Width of rectangle
     * @param int $height Height of rectangle
     * @param int $radius Corner radius
     * @param int $color Fill color
     */
    private function draw_rounded_rectangle($image, $x1, $y1, $width, $height, $radius, $color) {
        $x2 = $x1 + $width;
        $y2 = $y1 + $height;
        
        // Draw main rectangular body (excluding corner areas)
        imagefilledrectangle($image, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($image, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
        
        // Draw rounded corners using filled ellipses
        // Top-left corner
        imagefilledellipse($image, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        // Top-right corner
        imagefilledellipse($image, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        // Bottom-left corner
        imagefilledellipse($image, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        // Bottom-right corner
        imagefilledellipse($image, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
    }
    
    /**
     * Draw subtitle text - using Space Mono Regular
     */
    private function draw_subtitle($image, $text, $x, $y, $color) {
        // Try Space Mono Regular first
        $font_file = $this->get_font_by_name('space-mono-regular');
        
        // Fallback to normal font if custom font not found
        if (!$font_file) {
            $font_file = $this->get_font_path('normal');
        }
        
        if ($font_file) {
            imagettftext($image, 26, 0, $x, $y, $color, $font_file, $text);
        } else {
            $this->draw_large_text($image, $text, $x, $y, $color, 3.2);
        }
    }
    
    /**
     * Draw site logo
     */
    private function draw_logo($image, $x, $y) {
        $custom_logo_id = get_theme_mod('custom_logo');
        
        if (!$custom_logo_id) {
            return false;
        }
        
        $logo_path = get_attached_file($custom_logo_id);
        
        if (!$logo_path || !file_exists($logo_path)) {
            return false;
        }
        
        // Determine image type
        $info = getimagesize($logo_path);
        if (!$info) {
            return false;
        }
        
        switch ($info['mime']) {
            case 'image/jpeg':
                $logo = imagecreatefromjpeg($logo_path);
                break;
            case 'image/png':
                $logo = imagecreatefrompng($logo_path);
                break;
            case 'image/gif':
                $logo = imagecreatefromgif($logo_path);
                break;
            default:
                return false;
        }
        
        if (!$logo) {
            return false;
        }
        
        // Calculate resize dimensions
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);
        
        $scale = min(
            $this->logo_max_width / $logo_width,
            $this->logo_max_height / $logo_height
        );
        
        $new_width = (int)($logo_width * $scale);
        $new_height = (int)($logo_height * $scale);
        
        // Resize and copy
        imagecopyresampled(
            $image, $logo,
            $x, $y,
            0, 0,
            $new_width, $new_height,
            $logo_width, $logo_height
        );
        
        imagedestroy($logo);
        
        return true;
    }
    
    /**
     * Draw text with wrapping - returns number of lines drawn
     */
    private function draw_wrapped_text($image, $text, $x, $y, $max_width_percent, $color, $weight = 'normal') {
        $max_width = ($this->width * $max_width_percent / 100) - ($x * 2);
        
        // Check if weight is actually a custom font name (e.g., 'league-spartan-bold')
        $font_file = $this->get_font_by_name($weight);
        
        // If not a custom font, use standard font path lookup
        if (!$font_file) {
            $font_file = $this->get_font_path($weight);
        }
        
        if ($font_file) {
            // Use TTF font with proper sizing
            return $this->draw_ttf_wrapped_text($image, $text, $x, $y, $max_width, $color, $font_file);
        } else {
            // Fallback to built-in fonts
            return $this->draw_builtin_wrapped_text($image, $text, $x, $y, $max_width, $color);
        }
    }
    
    /**
     * Draw simple text
     */
    private function draw_text($image, $text, $x, $y, $font_size, $color) {
        $font_file = $this->get_font_path();
        
        if ($font_file) {
            // Use TTF font with proper sizing (28px)
            imagettftext($image, 28, 0, $x, $y, $color, $font_file, $text);
        } else {
            // Fallback to built-in fonts
            $this->draw_simple_builtin_text($image, $text, $x, $y, $color);
        }
    }
    
    /**
     * Draw simple built-in text (for site name) - 28px equivalent
     */
    private function draw_simple_builtin_text($image, $text, $x, $y, $color) {
        $this->draw_large_text($image, $text, $x, $y, $color, 3.5); // Scale to ~28px
    }
    
    /**
     * Draw wrapped text using TTF fonts - returns number of lines
     */
    private function draw_ttf_wrapped_text($image, $text, $x, $y, $max_width, $color, $font_file) {
        $font_size = 48; // 48px for post titles
        $words = explode(' ', $text);
        $lines = array();
        $current_line = '';
        
        foreach ($words as $word) {
            $test_line = $current_line . ($current_line ? ' ' : '') . $word;
            $bbox = imagettfbbox($font_size, 0, $font_file, $test_line);
            $width = $bbox[2] - $bbox[0];
            
            if ($width > $max_width && $current_line !== '') {
                $lines[] = $current_line;
                $current_line = $word;
            } else {
                $current_line = $test_line;
            }
        }
        
        if ($current_line) {
            $lines[] = $current_line;
        }
        
        // Limit to 4 lines for better readability
        $lines = array_slice($lines, 0, 4);
        
        // Draw each line with proper spacing
        $current_y = $y;
        foreach ($lines as $line) {
            imagettftext($image, $font_size, 0, $x, $current_y, $color, $font_file, $line);
            $current_y += 58; // Line height for 48px font
        }
        
        return count($lines);
    }
    
    /**
     * Draw wrapped text using built-in fonts - returns number of lines
     */
    private function draw_builtin_wrapped_text($image, $text, $x, $y, $max_width, $color) {
        $words = explode(' ', $text);
        $lines = array();
        $current_line = '';
        
        // Use proper character width calculation for scaled text
        $scale = 5.0; // Scale factor for ~48px
        $char_width = imagefontwidth(5) * $scale;
        
        foreach ($words as $word) {
            $test_line = $current_line . ($current_line ? ' ' : '') . $word;
            $width = strlen($test_line) * $char_width;
            
            if ($width > $max_width && $current_line !== '') {
                $lines[] = $current_line;
                $current_line = $word;
            } else {
                $current_line = $test_line;
            }
        }
        
        if ($current_line) {
            $lines[] = $current_line;
        }
        
        // Limit to 4 lines for better readability
        $lines = array_slice($lines, 0, 4);
        if (count($lines) == 4) {
            $max_chars = (int)($max_width / $char_width);
            if (strlen($lines[3]) > $max_chars) {
                $lines[3] = substr($lines[3], 0, $max_chars - 3) . '...';
            }
        }
        
        // Draw each line with larger text (48px equivalent)
        $current_y = $y;
        foreach ($lines as $line) {
            $this->draw_large_text($image, $line, $x, $current_y, $color, $scale);
            $current_y += 58; // Line height for 48px font
        }
        
        return count($lines);
    }
    
    /**
     * Draw large text by scaling built-in fonts
     */
    private function draw_large_text($image, $text, $x, $y, $color, $scale = 4) {
        $font_size = 5; // Use largest built-in font
        $char_width = imagefontwidth($font_size);
        $char_height = imagefontheight($font_size);
        
        // Calculate dimensions for scaled text
        $text_width = strlen($text) * $char_width;
        $text_height = $char_height;
        $scaled_width = $text_width * $scale;
        $scaled_height = $text_height * $scale;
        
        // Create temporary image for the text
        $temp_image = imagecreate($text_width, $text_height);
        $bg_color = imagecolorallocate($temp_image, 255, 255, 255);
        $text_color = imagecolorallocate($temp_image, 0, 0, 0);
        
        // Draw text on temporary image
        imagestring($temp_image, $font_size, 0, 0, $text, $text_color);
        
        // Scale and copy to main image
        imagecopyresized(
            $image, $temp_image,
            $x, $y,
            0, 0,
            $scaled_width, $scaled_height,
            $text_width, $text_height
        );
        
        // Clean up temporary image
        imagedestroy($temp_image);
        
        // Apply the correct color by replacing black pixels
        for ($i = 0; $i < $scaled_width; $i++) {
            for ($j = 0; $j < $scaled_height; $j++) {
                $rgb = imagecolorat($image, $x + $i, $y + $j);
                $colors = imagecolorsforindex($image, $rgb);
                // If pixel is black (text), replace with desired color
                if ($colors['red'] == 0 && $colors['green'] == 0 && $colors['blue'] == 0) {
                    imagesetpixel($image, $x + $i, $y + $j, $color);
                }
            }
        }
    }
    
    /**
     * Get font file path by specific font name
     * 
     * @param string $font_name Font name (e.g., 'league-spartan-bold', 'space-mono-regular')
     * @return string|false Font path or false if not found
     */
    private function get_font_by_name($font_name) {
        $plugin_font_dir = VS3_AUTO_OG_PLUGIN_DIR . 'fonts/';
        
        // Map font names to actual filenames
        $font_map = array(
            'league-spartan-bold' => 'LeagueSpartan-Bold.ttf',
            'league-spartan-light' => 'LeagueSpartan-Light.ttf',
            'league-spartan-regular' => 'LeagueSpartan-Regular.ttf',
            'space-mono-bold' => 'SpaceMono-Bold.ttf',
            'space-mono-regular' => 'SpaceMono-Regular.ttf',
        );
        
        if (isset($font_map[$font_name])) {
            $font_path = $plugin_font_dir . $font_map[$font_name];
            if (file_exists($font_path)) {
                return $font_path;
            }
        }
        
        return false;
    }
    
    /**
     * Get font file path - Try system fonts first, then plugin fonts, fallback to built-in
     */
    private function get_font_path($weight = 'normal') {
        // Try common system font paths first
        $system_fonts = array(
            'normal' => array(
                '/System/Library/Fonts/Arial.ttf', // macOS
                '/System/Library/Fonts/Helvetica.ttc', // macOS
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf', // Linux
                '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf', // Linux
                'C:\\Windows\\Fonts\\arial.ttf', // Windows
                'C:\\Windows\\Fonts\\verdana.ttf', // Windows
            ),
            'bold' => array(
                '/System/Library/Fonts/Arial Bold.ttf', // macOS
                '/System/Library/Fonts/Helvetica.ttc', // macOS
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', // Linux
                '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf', // Linux
                'C:\\Windows\\Fonts\\arialbd.ttf', // Windows
                'C:\\Windows\\Fonts\\verdanab.ttf', // Windows
            ),
        );
        
        // Check system fonts first
        $paths = isset($system_fonts[$weight]) ? $system_fonts[$weight] : $system_fonts['normal'];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Check plugin fonts as fallback
        $plugin_font_dir = VS3_AUTO_OG_PLUGIN_DIR . 'fonts/';
        $plugin_fonts = array(
            'normal' => array(
                'Arial.ttf',
                'arial.ttf',
                'Arial-Regular.ttf',
                'LiberationSans-Regular.ttf',
            ),
            'bold' => array(
                'Arial-Bold.ttf',
                'arial-bold.ttf',
                'ArialBold.ttf',
                'LiberationSans-Bold.ttf',
            ),
        );
        
        $plugin_paths = isset($plugin_fonts[$weight]) ? $plugin_fonts[$weight] : $plugin_fonts['normal'];
        foreach ($plugin_paths as $font_file) {
            $font_path = $plugin_font_dir . $font_file;
            if (file_exists($font_path)) {
                return $font_path;
            }
        }
        
        return false; // Fallback to built-in fonts
    }
    
    /**
     * Convert hex color to RGB
     */
    private function hex_to_rgb($hex) {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) == 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        return array(
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        );
    }
    
    /**
     * Get settings (network or site-specific)
     */
    private function get_settings() {
        $defaults = array(
            'bg_color' => '#ffffff',
            'text_color' => '#000000',
            'accent_color' => '#0073aa',
        );
        
        if (is_multisite()) {
            $network_settings = get_site_option('vs3_auto_og_network_settings', array());
            $site_settings = get_option('vs3_auto_og_site_settings', array());
            
            return array(
                'bg_color' => isset($site_settings['bg_color']) ? $site_settings['bg_color'] : 
                             (isset($network_settings['default_bg_color']) ? $network_settings['default_bg_color'] : $defaults['bg_color']),
                'text_color' => isset($site_settings['text_color']) ? $site_settings['text_color'] : 
                               (isset($network_settings['default_text_color']) ? $network_settings['default_text_color'] : $defaults['text_color']),
                'accent_color' => isset($site_settings['accent_color']) ? $site_settings['accent_color'] : 
                                 (isset($network_settings['default_accent_color']) ? $network_settings['default_accent_color'] : $defaults['accent_color']),
            );
        }
        
        $settings = get_option('vs3_auto_og_site_settings', array());
        return wp_parse_args($settings, $defaults);
    }
    
    /**
     * Get cache version
     */
    private function get_cache_version() {
        if (is_multisite()) {
            $network_settings = get_site_option('vs3_auto_og_network_settings', array());
            return isset($network_settings['cache_version']) ? $network_settings['cache_version'] : time();
        }
        
        $settings = get_option('vs3_auto_og_site_settings', array());
        return isset($settings['cache_version']) ? $settings['cache_version'] : time();
    }
    
    /**
     * Clean up old image versions
     */
    private function cleanup_old_versions($dir, $post_id, $current_version) {
        $pattern = $dir . '/' . $post_id . '-v*.png';
        $files = glob($pattern);
        
        foreach ($files as $file) {
            if (strpos($file, '-v' . $current_version . '.png') === false) {
                @unlink($file);
            }
        }
    }
    
    /**
     * Delete image for a specific post
     */
    public function delete_post_image($post_id) {
        $upload_dir = wp_upload_dir();
        $og_dir = $upload_dir['basedir'] . '/vs3-og';
        
        $pattern = $og_dir . '/' . $post_id . '-v*.png';
        $files = glob($pattern);
        
        foreach ($files as $file) {
            @unlink($file);
        }
    }
}

