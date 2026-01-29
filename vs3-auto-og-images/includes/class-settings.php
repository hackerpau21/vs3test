<?php
/**
 * Settings Manager
 * Handles network-wide and per-site settings
 */

if (!defined('ABSPATH')) {
    exit;
}

class VS3_Auto_OG_Settings {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        if (is_multisite()) {
            // Network admin menu
            add_action('network_admin_menu', array($this, 'add_network_menu'));
            add_action('admin_init', array($this, 'register_network_settings'));
            
            // Site admin menu
            add_action('admin_menu', array($this, 'add_site_menu'));
        } else {
            // Single site menu
            add_action('admin_menu', array($this, 'add_site_menu'));
        }
        
        add_action('admin_init', array($this, 'register_site_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add network admin menu
     */
    public function add_network_menu() {
        add_submenu_page(
            'settings.php',
            __('VS3 Auto OG Images', 'vs3-auto-og'),
            __('Auto OG Images', 'vs3-auto-og'),
            'manage_network_options',
            'vs3-auto-og-network',
            array($this, 'render_network_settings_page')
        );
    }
    
    /**
     * Add site admin menu
     */
    public function add_site_menu() {
        add_options_page(
            __('VS3 Auto OG Images', 'vs3-auto-og'),
            __('Auto OG Images', 'vs3-auto-og'),
            'manage_options',
            'vs3-auto-og',
            array($this, 'render_site_settings_page')
        );
    }
    
    /**
     * Register network settings
     */
    public function register_network_settings() {
        if (!is_network_admin()) {
            return;
        }
        
        // Handle form submission
        if (isset($_POST['vs3_auto_og_network_submit']) && check_admin_referer('vs3_auto_og_network_settings')) {
            $settings = array(
                'enabled' => isset($_POST['vs3_auto_og_enabled']) ? 1 : 0,
                'default_bg_color' => sanitize_hex_color($_POST['vs3_auto_og_bg_color']),
                'default_text_color' => sanitize_hex_color($_POST['vs3_auto_og_text_color']),
                'default_accent_color' => sanitize_hex_color($_POST['vs3_auto_og_accent_color']),
                // Cloudflare AI settings
                'cf_enabled' => isset($_POST['vs3_auto_og_cf_enabled']) ? 1 : 0,
                'cf_account_id' => isset($_POST['vs3_auto_og_cf_account_id']) ? sanitize_text_field($_POST['vs3_auto_og_cf_account_id']) : '',
                'cf_api_token' => isset($_POST['vs3_auto_og_cf_api_token']) ? sanitize_text_field($_POST['vs3_auto_og_cf_api_token']) : '',
                'cf_model' => isset($_POST['vs3_auto_og_cf_model']) ? sanitize_text_field($_POST['vs3_auto_og_cf_model']) : 'flux-1-schnell',
                'cache_version' => time(),
            );

            update_site_option('vs3_auto_og_network_settings', $settings);
            
            add_action('network_admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>';
                echo __('Network settings saved successfully!', 'vs3-auto-og');
                echo '</p></div>';
            });
        }
    }
    
    /**
     * Register site settings
     */
    public function register_site_settings() {
        register_setting('vs3_auto_og_settings', 'vs3_auto_og_site_settings', array(
            'sanitize_callback' => array($this, 'sanitize_site_settings'),
        ));
        
        add_settings_section(
            'vs3_auto_og_main',
            __('OG Image Settings', 'vs3-auto-og'),
            array($this, 'render_main_section'),
            'vs3_auto_og_settings'
        );
        
        add_settings_field(
            'vs3_auto_og_enabled',
            __('Enable OG Images', 'vs3-auto-og'),
            array($this, 'render_enabled_field'),
            'vs3_auto_og_settings',
            'vs3_auto_og_main'
        );
        
        add_settings_field(
            'vs3_auto_og_colors',
            __('Image Colors', 'vs3-auto-og'),
            array($this, 'render_colors_field'),
            'vs3_auto_og_settings',
            'vs3_auto_og_main'
        );
        
        // Cloudflare AI Settings Section
        add_settings_section(
            'vs3_auto_og_cloudflare',
            __('Cloudflare AI Settings', 'vs3-auto-og'),
            array($this, 'render_cloudflare_section'),
            'vs3_auto_og_settings'
        );
        
        add_settings_field(
            'vs3_auto_og_cf_enabled',
            __('Enable AI Generation', 'vs3-auto-og'),
            array($this, 'render_cf_enabled_field'),
            'vs3_auto_og_settings',
            'vs3_auto_og_cloudflare'
        );
        
        add_settings_field(
            'vs3_auto_og_cf_credentials',
            __('API Credentials', 'vs3-auto-og'),
            array($this, 'render_cf_credentials_field'),
            'vs3_auto_og_settings',
            'vs3_auto_og_cloudflare'
        );
        
        add_settings_field(
            'vs3_auto_og_cf_model',
            __('AI Model', 'vs3-auto-og'),
            array($this, 'render_cf_model_field'),
            'vs3_auto_og_settings',
            'vs3_auto_og_cloudflare'
        );
        
        add_settings_field(
            'vs3_auto_og_cf_style',
            __('Image Style', 'vs3-auto-og'),
            array($this, 'render_cf_style_field'),
            'vs3_auto_og_settings',
            'vs3_auto_og_cloudflare'
        );
        
        add_settings_field(
            'vs3_auto_og_cf_prompt',
            __('Custom Prompt', 'vs3-auto-og'),
            array($this, 'render_cf_prompt_field'),
            'vs3_auto_og_settings',
            'vs3_auto_og_cloudflare'
        );
        
        add_settings_section(
            'vs3_auto_og_tools',
            __('Tools', 'vs3-auto-og'),
            array($this, 'render_tools_section'),
            'vs3_auto_og_settings'
        );
        
        // Handle cache clearing
        if (isset($_POST['vs3_auto_og_clear_cache']) && check_admin_referer('vs3_auto_og_clear_cache')) {
            $cache_manager = VS3_Auto_OG_Cache_Manager::get_instance();
            $cache_manager->clear_all_images();
            
            add_settings_error(
                'vs3_auto_og_settings',
                'cache_cleared',
                __('Cache cleared successfully!', 'vs3-auto-og'),
                'success'
            );
        }
        
        // Handle Cloudflare AI connection test
        if (isset($_POST['vs3_auto_og_test_cf']) && check_admin_referer('vs3_auto_og_test_cf')) {
            $cf_ai = new VS3_Auto_OG_Cloudflare_AI();
            $result = $cf_ai->test_connection();
            
            add_settings_error(
                'vs3_auto_og_settings',
                'cf_test',
                $result['message'],
                $result['success'] ? 'success' : 'error'
            );
        }
    }
    
    /**
     * Sanitize site settings
     */
    public function sanitize_site_settings($input) {
        $sanitized = array();
        
        // Checkbox: if not set in input, it means unchecked (false)
        // Always explicitly set the enabled value
        $sanitized['enabled'] = !empty($input['enabled']) ? true : false;
        
        if (isset($input['bg_color'])) {
            $sanitized['bg_color'] = sanitize_hex_color($input['bg_color']);
        }
        
        if (isset($input['text_color'])) {
            $sanitized['text_color'] = sanitize_hex_color($input['text_color']);
        }
        
        if (isset($input['accent_color'])) {
            $sanitized['accent_color'] = sanitize_hex_color($input['accent_color']);
        }
        
        // Cloudflare AI settings
        $sanitized['cf_enabled'] = !empty($input['cf_enabled']) ? true : false;
        
        if (isset($input['cf_account_id'])) {
            $sanitized['cf_account_id'] = sanitize_text_field($input['cf_account_id']);
        }
        
        if (isset($input['cf_api_token'])) {
            $sanitized['cf_api_token'] = sanitize_text_field($input['cf_api_token']);
        }
        
        if (isset($input['cf_model'])) {
            $valid_models = array_keys(VS3_Auto_OG_Cloudflare_AI::get_available_models());
            $sanitized['cf_model'] = in_array($input['cf_model'], $valid_models) 
                ? $input['cf_model'] 
                : 'flux-1-schnell';
        }
        
        if (isset($input['cf_style'])) {
            $valid_styles = array_keys(VS3_Auto_OG_Cloudflare_AI::get_available_styles());
            $sanitized['cf_style'] = in_array($input['cf_style'], $valid_styles) 
                ? $input['cf_style'] 
                : 'abstract';
        }
        
        if (isset($input['cf_prompt_template'])) {
            $sanitized['cf_prompt_template'] = sanitize_textarea_field($input['cf_prompt_template']);
        }
        
        // Bump cache version when settings change
        $old_settings = get_option('vs3_auto_og_site_settings', array());
        if ($old_settings !== $sanitized) {
            $sanitized['cache_version'] = time();
        } else {
            $sanitized['cache_version'] = isset($old_settings['cache_version']) ? $old_settings['cache_version'] : time();
        }
        
        return $sanitized;
    }
    
    /**
     * Render network settings page
     */
    public function render_network_settings_page() {
        $settings = get_site_option('vs3_auto_og_network_settings', array(
            'enabled' => true,
            'default_bg_color' => '#ffffff',
            'default_text_color' => '#000000',
            'default_accent_color' => '#0073aa',
            'cf_enabled' => false,
            'cf_account_id' => '',
            'cf_api_token' => '',
            'cf_model' => 'flux-1-schnell',
        ));

        $models = VS3_Auto_OG_Cloudflare_AI::get_available_models();
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('VS3 Auto OG Images - Network Settings', 'vs3-auto-og'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('vs3_auto_og_network_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Enable by Default', 'vs3-auto-og'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="vs3_auto_og_enabled" value="1" <?php checked($settings['enabled'], 1); ?> />
                                <?php echo esc_html__('Enable OG image generation by default for all sites', 'vs3-auto-og'); ?>
                            </label>
                            <p class="description">
                                <?php echo esc_html__('Individual sites can override this setting.', 'vs3-auto-og'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php echo esc_html__('Default Colors', 'vs3-auto-og'); ?></th>
                        <td>
                            <p>
                                <label>
                                    <?php echo esc_html__('Background Color:', 'vs3-auto-og'); ?>
                                    <input type="text" name="vs3_auto_og_bg_color" value="<?php echo esc_attr($settings['default_bg_color']); ?>" class="vs3-color-picker" />
                                </label>
                            </p>
                            <p>
                                <label>
                                    <?php echo esc_html__('Text Color:', 'vs3-auto-og'); ?>
                                    <input type="text" name="vs3_auto_og_text_color" value="<?php echo esc_attr($settings['default_text_color']); ?>" class="vs3-color-picker" />
                                </label>
                            </p>
                            <p>
                                <label>
                                    <?php echo esc_html__('Accent Color:', 'vs3-auto-og'); ?>
                                    <input type="text" name="vs3_auto_og_accent_color" value="<?php echo esc_attr($settings['default_accent_color']); ?>" class="vs3-color-picker" />
                                </label>
                            </p>
                            <p class="description">
                                <?php echo esc_html__('These colors will be used as defaults. Sites can override them.', 'vs3-auto-og'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <h2><?php echo esc_html__('Cloudflare AI Settings (Network-Wide)', 'vs3-auto-og'); ?></h2>
                <p class="description">
                    <?php echo esc_html__('These Cloudflare AI credentials will be used by all sites in the network. Individual sites will not need to enter their own credentials.', 'vs3-auto-og'); ?>
                </p>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Enable AI Generation', 'vs3-auto-og'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="vs3_auto_og_cf_enabled" value="1" <?php checked($settings['cf_enabled'], 1); ?> />
                                <?php echo esc_html__('Enable Cloudflare AI for all sites in the network', 'vs3-auto-og'); ?>
                            </label>
                            <p class="description">
                                <?php echo esc_html__('Generate AI-powered backgrounds using Cloudflare Workers AI.', 'vs3-auto-og'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo esc_html__('Account ID', 'vs3-auto-og'); ?></th>
                        <td>
                            <input type="text" name="vs3_auto_og_cf_account_id"
                                   value="<?php echo esc_attr($settings['cf_account_id']); ?>"
                                   class="regular-text"
                                   placeholder="<?php echo esc_attr__('Your Cloudflare Account ID', 'vs3-auto-og'); ?>" />
                            <p class="description">
                                <?php echo esc_html__('Find this in your Cloudflare dashboard under Workers & Pages.', 'vs3-auto-og'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo esc_html__('API Token', 'vs3-auto-og'); ?></th>
                        <td>
                            <input type="password" name="vs3_auto_og_cf_api_token"
                                   value="<?php echo esc_attr($settings['cf_api_token']); ?>"
                                   class="regular-text"
                                   placeholder="<?php echo esc_attr__('Your Cloudflare API Token', 'vs3-auto-og'); ?>" />
                            <p class="description">
                                <?php
                                printf(
                                    /* translators: %s: link to Cloudflare dashboard */
                                    esc_html__('Create an API token with "Workers AI" read permission in the %s.', 'vs3-auto-og'),
                                    '<a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank">' .
                                    esc_html__('Cloudflare Dashboard', 'vs3-auto-og') .
                                    '</a>'
                                );
                                ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php echo esc_html__('AI Model', 'vs3-auto-og'); ?></th>
                        <td>
                            <select name="vs3_auto_og_cf_model">
                                <?php foreach ($models as $key => $model): ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($settings['cf_model'], $key); ?>>
                                        <?php echo esc_html($model['name'] . ' - ' . $model['description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">
                                <?php echo esc_html__('Flux 1 Schnell is recommended for best cost/quality balance.', 'vs3-auto-og'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <?php submit_button(__('Save Network Settings', 'vs3-auto-og'), 'primary', 'vs3_auto_og_network_submit'); ?>
            </form>
            
            <hr />
            
            <h2><?php echo esc_html__('How It Works', 'vs3-auto-og'); ?></h2>
            <p><?php echo esc_html__('This plugin automatically generates 1200×900 OG images for posts and pages that don\'t have a featured image.', 'vs3-auto-og'); ?></p>
            <ul>
                <li><?php echo esc_html__('Images are stored in /wp-content/uploads/vs3-og/', 'vs3-auto-og'); ?></li>
                <li><?php echo esc_html__('Design includes: Site Logo + Post Title + Site Name', 'vs3-auto-og'); ?></li>
                <li><?php echo esc_html__('Images are cached and regenerated when the site title or post content changes', 'vs3-auto-og'); ?></li>
                <li><?php echo esc_html__('Each site can override network defaults in Settings → Auto OG Images', 'vs3-auto-og'); ?></li>
            </ul>
        </div>
        <?php
    }
    
    /**
     * Render site settings page
     */
    public function render_site_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('VS3 Auto OG Images', 'vs3-auto-og'); ?></h1>
            
            <?php settings_errors('vs3_auto_og_settings'); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('vs3_auto_og_settings');
                do_settings_sections('vs3_auto_og_settings');
                submit_button();
                ?>
            </form>
            
            <hr />
            
            <h2><?php echo esc_html__('Clear Cache', 'vs3-auto-og'); ?></h2>
            <p><?php echo esc_html__('Clear all generated OG images. They will be regenerated on next page load.', 'vs3-auto-og'); ?></p>
            <form method="post" action="">
                <?php wp_nonce_field('vs3_auto_og_clear_cache'); ?>
                <button type="submit" name="vs3_auto_og_clear_cache" class="button button-secondary">
                    <?php echo esc_html__('Clear All OG Images', 'vs3-auto-og'); ?>
                </button>
            </form>
            
            <hr />
            
            <h2><?php echo esc_html__('Preview OG Image', 'vs3-auto-og'); ?></h2>
            <p><?php echo esc_html__('Generate a preview image to test your current settings (including Cloudflare AI if enabled).', 'vs3-auto-og'); ?></p>
            <?php $this->render_preview_section(); ?>
            
            <hr />
            
            <h2><?php echo esc_html__('Font Diagnostics', 'vs3-auto-og'); ?></h2>
            <?php
            // Check font availability
            $generator = new VS3_Auto_OG_Image_Generator();
            $normal_font = $this->check_font_path('normal');
            $bold_font = $this->check_font_path('bold');
            ?>
            <table class="widefat">
                <thead>
                    <tr>
                        <th><?php echo esc_html__('Font Type', 'vs3-auto-og'); ?></th>
                        <th><?php echo esc_html__('Status', 'vs3-auto-og'); ?></th>
                        <th><?php echo esc_html__('Path', 'vs3-auto-og'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo esc_html__('Normal Font', 'vs3-auto-og'); ?></td>
                        <td>
                            <?php if ($normal_font): ?>
                                <span style="color: green;">✓ <?php echo esc_html__('Found', 'vs3-auto-og'); ?></span>
                            <?php else: ?>
                                <span style="color: orange;">⚠ <?php echo esc_html__('Using built-in fallback', 'vs3-auto-og'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><code><?php echo $normal_font ? esc_html($normal_font) : 'GD built-in font 5'; ?></code></td>
                    </tr>
                    <tr>
                        <td><?php echo esc_html__('Bold Font', 'vs3-auto-og'); ?></td>
                        <td>
                            <?php if ($bold_font): ?>
                                <span style="color: green;">✓ <?php echo esc_html__('Found', 'vs3-auto-og'); ?></span>
                            <?php else: ?>
                                <span style="color: orange;">⚠ <?php echo esc_html__('Using built-in fallback', 'vs3-auto-og'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td><code><?php echo $bold_font ? esc_html($bold_font) : 'GD built-in font 5'; ?></code></td>
                    </tr>
                </tbody>
            </table>
            <p class="description">
                <?php if (!$normal_font || !$bold_font): ?>
                    <strong><?php echo esc_html__('To use TTF fonts:', 'vs3-auto-og'); ?></strong><br>
                    <?php echo esc_html__('Upload Arial.ttf and Arial-Bold.ttf to:', 'vs3-auto-og'); ?><br>
                    <code><?php echo esc_html(VS3_AUTO_OG_PLUGIN_DIR . 'fonts/'); ?></code>
                <?php else: ?>
                    <?php echo esc_html__('TTF fonts are working correctly!', 'vs3-auto-og'); ?>
                <?php endif; ?>
            </p>
            
            <?php if (is_multisite()): ?>
                <hr />
                <h2><?php echo esc_html__('Network Settings', 'vs3-auto-og'); ?></h2>
                <p>
                    <?php echo esc_html__('This is a multisite installation. Network administrators can configure default settings in', 'vs3-auto-og'); ?>
                    <a href="<?php echo network_admin_url('settings.php?page=vs3-auto-og-network'); ?>">
                        <?php echo esc_html__('Network Settings', 'vs3-auto-og'); ?>
                    </a>.
                </p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render main section
     */
    public function render_main_section() {
        echo '<p>' . esc_html__('Configure how OG images are generated for your site.', 'vs3-auto-og') . '</p>';
    }
    
    /**
     * Render tools section
     */
    public function render_tools_section() {
        // Empty - handled separately
    }
    
    /**
     * Render enabled field
     */
    public function render_enabled_field() {
        $settings = get_option('vs3_auto_og_site_settings', array());
        $network_settings = is_multisite() ? get_site_option('vs3_auto_og_network_settings', array()) : array();
        
        // Check if enabled has been explicitly set in site settings
        if (array_key_exists('enabled', $settings)) {
            $enabled = $settings['enabled'];
        } elseif (isset($network_settings['enabled'])) {
            // Fall back to network setting
            $enabled = $network_settings['enabled'];
        } else {
            // Default to true (enabled by default)
            $enabled = true;
        }
        
        ?>
        <label>
            <input type="checkbox" name="vs3_auto_og_site_settings[enabled]" value="1" <?php checked($enabled, true); ?> />
            <?php echo esc_html__('Enable automatic OG image generation', 'vs3-auto-og'); ?>
        </label>
        <p class="description">
            <?php echo esc_html__('Generate OG images for posts and pages without featured images.', 'vs3-auto-og'); ?>
        </p>
        <?php
    }
    
    /**
     * Render colors field
     */
    public function render_colors_field() {
        $settings = get_option('vs3_auto_og_site_settings', array());
        $network_settings = is_multisite() ? get_site_option('vs3_auto_og_network_settings', array()) : array();
        
        $bg_color = isset($settings['bg_color']) ? $settings['bg_color'] : 
                   (isset($network_settings['default_bg_color']) ? $network_settings['default_bg_color'] : '#ffffff');
        
        $text_color = isset($settings['text_color']) ? $settings['text_color'] : 
                     (isset($network_settings['default_text_color']) ? $network_settings['default_text_color'] : '#000000');
        
        $accent_color = isset($settings['accent_color']) ? $settings['accent_color'] : 
                       (isset($network_settings['default_accent_color']) ? $network_settings['default_accent_color'] : '#0073aa');
        
        ?>
        <p>
            <label>
                <?php echo esc_html__('Background Color:', 'vs3-auto-og'); ?><br />
                <input type="text" name="vs3_auto_og_site_settings[bg_color]" value="<?php echo esc_attr($bg_color); ?>" class="vs3-color-picker" />
            </label>
        </p>
        <p>
            <label>
                <?php echo esc_html__('Text Color:', 'vs3-auto-og'); ?><br />
                <input type="text" name="vs3_auto_og_site_settings[text_color]" value="<?php echo esc_attr($text_color); ?>" class="vs3-color-picker" />
            </label>
        </p>
        <p>
            <label>
                <?php echo esc_html__('Accent Color:', 'vs3-auto-og'); ?><br />
                <input type="text" name="vs3_auto_og_site_settings[accent_color]" value="<?php echo esc_attr($accent_color); ?>" class="vs3-color-picker" />
            </label>
        </p>
        <?php
    }
    
    /**
     * Render Cloudflare AI section description
     */
    public function render_cloudflare_section() {
        $cf_ai = new VS3_Auto_OG_Cloudflare_AI();
        $is_enabled = $cf_ai->is_enabled();
        
        ?>
        <p>
            <?php echo esc_html__('Use Cloudflare Workers AI to generate beautiful, unique backgrounds for your OG images.', 'vs3-auto-og'); ?>
            <a href="https://developers.cloudflare.com/workers-ai/" target="_blank">
                <?php echo esc_html__('Learn more', 'vs3-auto-og'); ?> &rarr;
            </a>
        </p>
        
        <?php if ($is_enabled): ?>
            <?php $this->render_usage_stats(); ?>
        <?php else: ?>
            <p class="description">
                <?php echo esc_html__('Free tier: 10,000 neurons/day (~150 images). Cost after: ~$0.0007 per image.', 'vs3-auto-og'); ?>
            </p>
        <?php endif;
    }
    
    /**
     * Render usage statistics
     */
    private function render_usage_stats() {
        $status = VS3_Auto_OG_Cloudflare_AI::check_daily_limit();
        $settings = get_option('vs3_auto_og_site_settings', array());
        $current_model = isset($settings['cf_model']) ? $settings['cf_model'] : 'flux-1-schnell';
        $images_remaining = VS3_Auto_OG_Cloudflare_AI::get_images_remaining($current_model);
        
        // Determine bar color
        if ($status['reached']) {
            $bar_color = '#dc3545'; // Red
            $status_text = __('Daily limit reached - using GD fallback', 'vs3-auto-og');
            $status_class = 'notice-error';
        } elseif ($status['warning']) {
            $bar_color = '#ffc107'; // Yellow
            $status_text = __('Approaching daily limit', 'vs3-auto-og');
            $status_class = 'notice-warning';
        } else {
            $bar_color = '#28a745'; // Green
            $status_text = __('Within free tier', 'vs3-auto-og');
            $status_class = 'notice-success';
        }
        
        // Calculate reset time
        $now_utc = new DateTime('now', new DateTimeZone('UTC'));
        $tomorrow_utc = new DateTime('tomorrow', new DateTimeZone('UTC'));
        $diff = $now_utc->diff($tomorrow_utc);
        $reset_time = sprintf('%dh %dm', $diff->h, $diff->i);
        ?>
        
        <div class="vs3-usage-stats" style="background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; padding: 15px; margin: 10px 0;">
            <h4 style="margin-top: 0; margin-bottom: 10px;">
                <?php echo esc_html__('Daily Neuron Usage', 'vs3-auto-og'); ?>
                <span style="font-weight: normal; font-size: 12px; color: #666;">
                    (<?php echo esc_html__('Resets in', 'vs3-auto-og'); ?> <?php echo esc_html($reset_time); ?>)
                </span>
            </h4>
            
            <!-- Progress Bar -->
            <div style="background: #e0e0e0; border-radius: 10px; height: 20px; overflow: hidden; margin-bottom: 10px;">
                <div style="background: <?php echo esc_attr($bar_color); ?>; height: 100%; width: <?php echo esc_attr($status['percent']); ?>%; transition: width 0.3s;"></div>
            </div>
            
            <!-- Stats -->
            <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div>
                    <strong><?php echo number_format($status['used']); ?></strong> / <?php echo number_format($status['limit']); ?> 
                    <?php echo esc_html__('neurons used', 'vs3-auto-og'); ?>
                    <span style="color: #666;">(<?php echo esc_html($status['percent']); ?>%)</span>
                </div>
                <div>
                    <strong><?php echo number_format($status['images_generated']); ?></strong> 
                    <?php echo esc_html__('images generated today', 'vs3-auto-og'); ?>
                </div>
                <div>
                    <strong>~<?php echo number_format($images_remaining); ?></strong> 
                    <?php echo esc_html__('images remaining (free)', 'vs3-auto-og'); ?>
                </div>
            </div>
            
            <!-- Status Message -->
            <div class="notice <?php echo esc_attr($status_class); ?> inline" style="margin: 10px 0 0 0; padding: 8px 12px;">
                <p style="margin: 0;">
                    <?php if ($status['reached']): ?>
                        <strong><?php echo esc_html__('Limit Reached:', 'vs3-auto-og'); ?></strong>
                        <?php echo esc_html__('New images will use the classic GD design until the limit resets at midnight UTC.', 'vs3-auto-og'); ?>
                    <?php elseif ($status['warning']): ?>
                        <strong><?php echo esc_html__('Warning:', 'vs3-auto-og'); ?></strong>
                        <?php echo esc_html__('You\'re approaching the free tier limit. Consider using a different model or waiting for reset.', 'vs3-auto-og'); ?>
                    <?php else: ?>
                        <?php echo esc_html($status_text); ?> - 
                        <?php echo esc_html__('AI generation is active.', 'vs3-auto-og'); ?>
                    <?php endif; ?>
                </p>
            </div>
            
            <!-- Link to Cloudflare Dashboard -->
            <p style="margin: 10px 0 0 0; font-size: 12px;">
                <a href="https://dash.cloudflare.com/?to=/:account/ai/workers-ai" target="_blank">
                    <?php echo esc_html__('View actual usage in Cloudflare Dashboard', 'vs3-auto-og'); ?> &rarr;
                </a>
                <span style="color: #666; margin-left: 10px;">
                    (<?php echo esc_html__('Local tracking is an estimate', 'vs3-auto-og'); ?>)
                </span>
            </p>
        </div>
        <?php
    }
    
    /**
     * Check if network-level Cloudflare credentials are configured
     *
     * @return bool True if network credentials are set
     */
    private function has_network_cloudflare_credentials() {
        if (!is_multisite()) {
            return false;
        }

        $network_settings = get_site_option('vs3_auto_og_network_settings', array());
        return !empty($network_settings['cf_account_id']) && !empty($network_settings['cf_api_token']);
    }

    /**
     * Render Cloudflare AI enabled field
     */
    public function render_cf_enabled_field() {
        $settings = get_option('vs3_auto_og_site_settings', array());
        $network_settings = is_multisite() ? get_site_option('vs3_auto_og_network_settings', array()) : array();

        // Check if network has credentials configured
        if ($this->has_network_cloudflare_credentials()) {
            $cf_enabled = !empty($network_settings['cf_enabled']);
            ?>
            <div class="notice notice-info inline" style="margin: 0; padding: 10px 12px;">
                <p style="margin: 0;">
                    <strong><?php echo esc_html__('Managed by Network Admin', 'vs3-auto-og'); ?></strong><br>
                    <?php if ($cf_enabled): ?>
                        <span style="color: green;">&#10004;</span>
                        <?php echo esc_html__('Cloudflare AI is enabled network-wide.', 'vs3-auto-og'); ?>
                    <?php else: ?>
                        <span style="color: gray;">&#9679;</span>
                        <?php echo esc_html__('Cloudflare AI is disabled network-wide.', 'vs3-auto-og'); ?>
                    <?php endif; ?>
                </p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                    <?php echo esc_html__('Contact your network administrator to change this setting.', 'vs3-auto-og'); ?>
                </p>
            </div>
            <!-- Hidden field to preserve the value -->
            <input type="hidden" name="vs3_auto_og_site_settings[cf_enabled]" value="<?php echo $cf_enabled ? '1' : '0'; ?>" />
            <?php
            return;
        }

        $cf_enabled = isset($settings['cf_enabled']) ? $settings['cf_enabled'] : false;
        ?>
        <label>
            <input type="checkbox" name="vs3_auto_og_site_settings[cf_enabled]" value="1" <?php checked($cf_enabled, true); ?> />
            <?php echo esc_html__('Generate image backgrounds using Cloudflare AI', 'vs3-auto-og'); ?>
        </label>
        <p class="description">
            <?php echo esc_html__('When disabled, images use the classic GD-based design with colorful shapes.', 'vs3-auto-og'); ?>
        </p>
        <?php
    }
    
    /**
     * Render Cloudflare API credentials fields
     */
    public function render_cf_credentials_field() {
        // Check if network credentials are configured
        if ($this->has_network_cloudflare_credentials()) {
            $network_settings = get_site_option('vs3_auto_og_network_settings', array());
            $masked_account_id = substr($network_settings['cf_account_id'], 0, 8) . '...' . substr($network_settings['cf_account_id'], -4);
            ?>
            <div class="notice notice-info inline" style="margin: 0; padding: 10px 12px;">
                <p style="margin: 0;">
                    <strong><?php echo esc_html__('Using Network Credentials', 'vs3-auto-og'); ?></strong><br>
                    <?php echo esc_html__('Cloudflare API credentials are configured at the network level.', 'vs3-auto-og'); ?>
                </p>
                <p style="margin: 8px 0 0 0;">
                    <strong><?php echo esc_html__('Account ID:', 'vs3-auto-og'); ?></strong>
                    <code><?php echo esc_html($masked_account_id); ?></code><br>
                    <strong><?php echo esc_html__('API Token:', 'vs3-auto-og'); ?></strong>
                    <code>********</code>
                </p>
                <p style="margin: 8px 0 0 0; font-size: 12px; color: #666;">
                    <?php
                    printf(
                        /* translators: %s: link to network settings */
                        esc_html__('These credentials are managed in %s.', 'vs3-auto-og'),
                        '<a href="' . network_admin_url('settings.php?page=vs3-auto-og-network') . '">' .
                        esc_html__('Network Settings', 'vs3-auto-og') .
                        '</a>'
                    );
                    ?>
                </p>
            </div>
            <?php
            return;
        }

        $settings = get_option('vs3_auto_og_site_settings', array());
        $account_id = isset($settings['cf_account_id']) ? $settings['cf_account_id'] : '';
        $api_token = isset($settings['cf_api_token']) ? $settings['cf_api_token'] : '';
        ?>
        <p>
            <label>
                <?php echo esc_html__('Account ID:', 'vs3-auto-og'); ?><br />
                <input type="text" name="vs3_auto_og_site_settings[cf_account_id]"
                       value="<?php echo esc_attr($account_id); ?>"
                       class="regular-text"
                       placeholder="<?php echo esc_attr__('Your Cloudflare Account ID', 'vs3-auto-og'); ?>" />
            </label>
        </p>
        <p>
            <label>
                <?php echo esc_html__('API Token:', 'vs3-auto-og'); ?><br />
                <input type="password" name="vs3_auto_og_site_settings[cf_api_token]"
                       value="<?php echo esc_attr($api_token); ?>"
                       class="regular-text"
                       placeholder="<?php echo esc_attr__('Your Cloudflare API Token', 'vs3-auto-og'); ?>" />
            </label>
        </p>
        <p class="description">
            <?php
            printf(
                /* translators: %s: link to Cloudflare dashboard */
                esc_html__('Find your Account ID in the %s. Create an API token with "Workers AI" read permission.', 'vs3-auto-og'),
                '<a href="https://dash.cloudflare.com/?to=/:account/ai/workers-ai" target="_blank">' .
                esc_html__('Cloudflare AI Dashboard', 'vs3-auto-og') .
                '</a>'
            );
            ?>
        </p>

        <!-- Test Connection Button -->
        <p style="margin-top: 15px;">
            <button type="button" class="button" id="vs3-test-cf-connection">
                <?php echo esc_html__('Test Connection', 'vs3-auto-og'); ?>
            </button>
            <span id="vs3-cf-test-result" style="margin-left: 10px;"></span>
        </p>

        <script>
        jQuery(document).ready(function($) {
            $('#vs3-test-cf-connection').on('click', function() {
                var $button = $(this);
                var $result = $('#vs3-cf-test-result');

                $button.prop('disabled', true).text('<?php echo esc_js(__('Testing...', 'vs3-auto-og')); ?>');
                $result.html('');

                // Get current form values
                var accountId = $('input[name="vs3_auto_og_site_settings[cf_account_id]"]').val();
                var apiToken = $('input[name="vs3_auto_og_site_settings[cf_api_token]"]').val();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'vs3_test_cloudflare_connection',
                        account_id: accountId,
                        api_token: apiToken,
                        nonce: '<?php echo wp_create_nonce('vs3_test_cf_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            $result.html('<span style="color: green;">&#10004; ' + response.data.message + '</span>');
                        } else {
                            $result.html('<span style="color: red;">&#10008; ' + response.data.message + '</span>');
                        }
                    },
                    error: function() {
                        $result.html('<span style="color: red;">&#10008; <?php echo esc_js(__('Connection failed', 'vs3-auto-og')); ?></span>');
                    },
                    complete: function() {
                        $button.prop('disabled', false).text('<?php echo esc_js(__('Test Connection', 'vs3-auto-og')); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Render Cloudflare AI model field
     */
    public function render_cf_model_field() {
        // Check if network credentials are configured
        if ($this->has_network_cloudflare_credentials()) {
            $network_settings = get_site_option('vs3_auto_og_network_settings', array());
            $current_model = isset($network_settings['cf_model']) ? $network_settings['cf_model'] : 'flux-1-schnell';
            $models = VS3_Auto_OG_Cloudflare_AI::get_available_models();
            $model_name = isset($models[$current_model]) ? $models[$current_model]['name'] : $current_model;
            ?>
            <div class="notice notice-info inline" style="margin: 0; padding: 10px 12px;">
                <p style="margin: 0;">
                    <strong><?php echo esc_html__('Network Setting:', 'vs3-auto-og'); ?></strong>
                    <?php echo esc_html($model_name); ?>
                </p>
                <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                    <?php echo esc_html__('The AI model is configured at the network level.', 'vs3-auto-og'); ?>
                </p>
            </div>
            <?php
            return;
        }

        $settings = get_option('vs3_auto_og_site_settings', array());
        $current_model = isset($settings['cf_model']) ? $settings['cf_model'] : 'flux-1-schnell';
        $models = VS3_Auto_OG_Cloudflare_AI::get_available_models();
        ?>
        <select name="vs3_auto_og_site_settings[cf_model]">
            <?php foreach ($models as $key => $model): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($current_model, $key); ?>>
                    <?php echo esc_html($model['name'] . ' - ' . $model['description']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php echo esc_html__('Flux 1 Schnell is recommended for best cost/quality balance.', 'vs3-auto-og'); ?>
        </p>
        <?php
    }
    
    /**
     * Render Cloudflare AI style field
     */
    public function render_cf_style_field() {
        $settings = get_option('vs3_auto_og_site_settings', array());
        $current_style = isset($settings['cf_style']) ? $settings['cf_style'] : 'abstract';
        $styles = VS3_Auto_OG_Cloudflare_AI::get_available_styles();
        ?>
        <select name="vs3_auto_og_site_settings[cf_style]">
            <?php foreach ($styles as $key => $label): ?>
                <option value="<?php echo esc_attr($key); ?>" <?php selected($current_style, $key); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p class="description">
            <?php echo esc_html__('Choose the visual style for AI-generated backgrounds.', 'vs3-auto-og'); ?>
        </p>
        <?php
    }
    
    /**
     * Render Cloudflare AI prompt field
     */
    public function render_cf_prompt_field() {
        $settings = get_option('vs3_auto_og_site_settings', array());
        $cf_ai = new VS3_Auto_OG_Cloudflare_AI();
        $default_prompt = $cf_ai->get_default_prompt_template();
        $current_prompt = isset($settings['cf_prompt_template']) ? $settings['cf_prompt_template'] : '';
        $current_style = isset($settings['cf_style']) ? $settings['cf_style'] : 'abstract';
        
        // Get example prompts for different styles
        $example_prompts = array(
            'geometric' => 'Create a geometric background with sharp triangles, circles, and hexagons. Use flat solid colors - no gradients. Left side lighter, right side vibrant.',
            'minimal' => 'Minimalist design with lots of white space, subtle lines, and one or two accent colors. Clean and uncluttered.',
            'tech' => 'Futuristic tech background with circuit patterns, digital grid lines, and neon accents. Cyberpunk aesthetic.',
            'vibrant' => 'Bold, energetic background with bright saturated colors and dynamic patterns. High contrast and eye-catching.',
        );
        ?>
        <textarea name="vs3_auto_og_site_settings[cf_prompt_template]" 
                  rows="5" 
                  class="large-text" 
                  placeholder="<?php echo esc_attr($default_prompt); ?>"><?php echo esc_textarea($current_prompt); ?></textarea>
        <p class="description">
            <?php echo esc_html__('Optional: Customize the AI prompt. Available placeholders: {title}, {excerpt}, {site_name}, {style}', 'vs3-auto-og'); ?>
        </p>
        <p class="description">
            <?php echo esc_html__('Leave empty to use the default prompt. The {style} placeholder will be replaced with your selected style description.', 'vs3-auto-og'); ?>
        </p>
        <div class="notice notice-info inline" style="margin: 10px 0 0 0; padding: 8px 12px;">
            <p style="margin: 0;">
                <strong><?php echo esc_html__('Tips for better results:', 'vs3-auto-og'); ?></strong>
            </p>
            <ul style="margin: 5px 0 0 20px;">
                <li><?php echo esc_html__('To avoid gradients, explicitly say "NO gradients" or "use flat colors" in your prompt.', 'vs3-auto-og'); ?></li>
                <li><?php echo esc_html__('For patterns/textures, be specific: "geometric X marks", "sharp lines", "distinct shapes".', 'vs3-auto-og'); ?></li>
                <li><?php echo esc_html__('If Flux-1-schnell keeps creating gradients, try switching to Flux-2-klein model (better at following instructions).', 'vs3-auto-og'); ?></li>
                <li><?php echo esc_html__('Cloudflare filters may flag words like "cocktail" or "party" - these are automatically sanitized.', 'vs3-auto-og'); ?></li>
            </ul>
        </div>
        
        <?php 
        // Show current model and suggest alternative if Flux-1-schnell
        $current_model = isset($settings['cf_model']) ? $settings['cf_model'] : 'flux-1-schnell';
        if ($current_model === 'flux-1-schnell'): ?>
        <div class="notice notice-warning inline" style="margin: 10px 0 0 0; padding: 8px 12px;">
            <p style="margin: 0;">
                <strong><?php echo esc_html__('Note:', 'vs3-auto-og'); ?></strong>
                <?php echo esc_html__('Flux-1-schnell sometimes defaults to gradients. If you need precise control over the design, consider switching to Flux-2-klein in the AI Model setting above.', 'vs3-auto-og'); ?>
            </p>
        </div>
        <?php endif; ?>
        
        <?php if (isset($example_prompts[$current_style])): ?>
        <details style="margin-top: 10px;">
            <summary style="cursor: pointer; color: #0073aa; font-weight: 500;">
                <?php echo esc_html__('Example prompt for', 'vs3-auto-og'); ?> <?php echo esc_html(ucfirst($current_style)); ?> <?php echo esc_html__('style', 'vs3-auto-og'); ?>
            </summary>
            <div style="background: #f9f9f9; border: 1px solid #ddd; padding: 10px; margin-top: 5px; border-radius: 3px;">
                <code style="font-size: 12px; line-height: 1.6;"><?php echo esc_html($example_prompts[$current_style]); ?></code>
                <button type="button" class="button button-small" style="margin-top: 8px;" onclick="document.querySelector('textarea[name=\'vs3_auto_og_site_settings[cf_prompt_template]\']').value = '<?php echo esc_js($example_prompts[$current_style]); ?>'">
                    <?php echo esc_html__('Use this prompt', 'vs3-auto-og'); ?>
                </button>
            </div>
        </details>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'vs3-auto-og') === false && $hook !== 'settings_page_vs3-auto-og') {
            return;
        }
        
        // WordPress color picker
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Initialize color pickers
        wp_add_inline_script('wp-color-picker', '
            jQuery(document).ready(function($) {
                $(".vs3-color-picker").wpColorPicker();
            });
        ');
    }
    
    /**
     * Render preview section
     */
    private function render_preview_section() {
        // Get a sample post without featured image
        $sample_posts = get_posts(array(
            'post_type' => array('post', 'page'),
            'posts_per_page' => 10,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_thumbnail_id',
                    'compare' => 'NOT EXISTS',
                ),
            ),
        ));
        
        // Also get posts with featured images as fallback
        if (empty($sample_posts)) {
            $sample_posts = get_posts(array(
                'post_type' => array('post', 'page'),
                'posts_per_page' => 5,
                'post_status' => 'publish',
            ));
        }
        
        $cf_ai = new VS3_Auto_OG_Cloudflare_AI();
        $settings = get_option('vs3_auto_og_site_settings', array());
        
        $cf_enabled = !empty($settings['cf_enabled']);
        $has_account_id = !empty($settings['cf_account_id']);
        $has_api_token = !empty($settings['cf_api_token']);
        $is_ready = $cf_ai->is_enabled();
        
        if ($is_ready) {
            $ai_status = '<span style="color: green;">&#10004; Cloudflare AI is ready</span>';
        } elseif ($cf_enabled && (!$has_account_id || !$has_api_token)) {
            $ai_status = '<span style="color: orange;">&#9888; AI enabled but credentials missing</span>';
        } elseif (!$cf_enabled) {
            $ai_status = '<span style="color: gray;">&#9679; AI disabled (using classic GD design)</span>';
        } else {
            $ai_status = '<span style="color: red;">&#10008; AI not properly configured</span>';
        }
        ?>
        
        <p><strong><?php echo esc_html__('AI Status:', 'vs3-auto-og'); ?></strong> <?php echo $ai_status; ?></p>
        
        <?php if ($is_ready): ?>
        <p style="color: #666; font-size: 12px;">
            <?php echo esc_html__('Model:', 'vs3-auto-og'); ?> 
            <?php echo esc_html(isset($settings['cf_model']) ? $settings['cf_model'] : 'flux-1-schnell'); ?> | 
            <?php echo esc_html__('Style:', 'vs3-auto-og'); ?> 
            <?php echo esc_html(isset($settings['cf_style']) ? $settings['cf_style'] : 'abstract'); ?>
        </p>
        <?php endif; ?>
        
        <?php if (!empty($sample_posts)): ?>
            <p>
                <label for="vs3-preview-post"><?php echo esc_html__('Select a post to preview:', 'vs3-auto-og'); ?></label>
                <select id="vs3-preview-post" style="min-width: 300px;">
                    <?php foreach ($sample_posts as $post): ?>
                        <option value="<?php echo esc_attr($post->ID); ?>">
                            <?php echo esc_html($post->post_title); ?> 
                            (ID: <?php echo esc_html($post->ID); ?>)
                            <?php echo has_post_thumbnail($post->ID) ? ' - has featured image' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            
            <p>
                <button type="button" class="button button-primary" id="vs3-generate-preview">
                    <?php echo esc_html__('Generate Preview', 'vs3-auto-og'); ?>
                </button>
                <span id="vs3-preview-status" style="margin-left: 10px;"></span>
            </p>
            
            <div id="vs3-preview-container" style="margin-top: 20px; display: none;">
                <h3><?php echo esc_html__('Generated OG Image:', 'vs3-auto-og'); ?></h3>
                <p id="vs3-preview-info" style="color: #666;"></p>
                <div style="border: 1px solid #ccc; padding: 10px; background: #f9f9f9; display: inline-block;">
                    <img id="vs3-preview-image" src="" alt="OG Preview" style="max-width: 600px; height: auto; display: block;" />
                </div>
                <p style="margin-top: 10px;">
                    <a id="vs3-preview-link" href="" target="_blank" class="button">
                        <?php echo esc_html__('View Full Size', 'vs3-auto-og'); ?>
                    </a>
                </p>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                $('#vs3-generate-preview').on('click', function() {
                    var $button = $(this);
                    var $status = $('#vs3-preview-status');
                    var $container = $('#vs3-preview-container');
                    var postId = $('#vs3-preview-post').val();
                    
                    $button.prop('disabled', true);
                    $status.html('<span style="color: #666;">Generating image... This may take up to 30 seconds for AI generation.</span>');
                    $container.hide();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'vs3_generate_preview_image',
                            post_id: postId,
                            nonce: '<?php echo wp_create_nonce('vs3_preview_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                var timestamp = new Date().getTime();
                                $('#vs3-preview-image').attr('src', response.data.url + '?t=' + timestamp);
                                $('#vs3-preview-link').attr('href', response.data.url);
                                $('#vs3-preview-info').html(response.data.info);
                                $container.show();
                                $status.html('<span style="color: green;">&#10004; Image generated successfully!</span>');
                            } else {
                                $status.html('<span style="color: red;">&#10008; ' + response.data.message + '</span>');
                            }
                        },
                        error: function() {
                            $status.html('<span style="color: red;">&#10008; Request failed. Please try again.</span>');
                        },
                        complete: function() {
                            $button.prop('disabled', false);
                        }
                    });
                });
            });
            </script>
        <?php else: ?>
            <p style="color: #666;">
                <?php echo esc_html__('No published posts found. Create a post first to preview OG images.', 'vs3-auto-og'); ?>
            </p>
        <?php endif;
    }
    
    /**
     * Check font path for diagnostics
     */
    private function check_font_path($weight = 'normal') {
        // Check system fonts first
        $system_fonts = array(
            'normal' => array(
                '/System/Library/Fonts/Arial.ttf',
                '/System/Library/Fonts/Helvetica.ttc',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
                '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
                'C:\\Windows\\Fonts\\arial.ttf',
                'C:\\Windows\\Fonts\\verdana.ttf',
            ),
            'bold' => array(
                '/System/Library/Fonts/Arial Bold.ttf',
                '/System/Library/Fonts/Helvetica.ttc',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
                'C:\\Windows\\Fonts\\arialbd.ttf',
                'C:\\Windows\\Fonts\\verdanab.ttf',
            ),
        );
        
        $paths = isset($system_fonts[$weight]) ? $system_fonts[$weight] : $system_fonts['normal'];
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Check plugin fonts
        $plugin_font_dir = VS3_AUTO_OG_PLUGIN_DIR . 'fonts/';
        $plugin_fonts = array(
            'normal' => array('Arial.ttf', 'arial.ttf', 'Arial-Regular.ttf', 'LiberationSans-Regular.ttf'),
            'bold' => array('Arial-Bold.ttf', 'arial-bold.ttf', 'ArialBold.ttf', 'LiberationSans-Bold.ttf'),
        );
        
        $plugin_paths = isset($plugin_fonts[$weight]) ? $plugin_fonts[$weight] : $plugin_fonts['normal'];
        foreach ($plugin_paths as $font_file) {
            $font_path = $plugin_font_dir . $font_file;
            if (file_exists($font_path)) {
                return $font_path;
            }
        }
        
        return false;
    }
}

