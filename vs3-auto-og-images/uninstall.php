<?php
/**
 * Uninstall script
 * Cleans up plugin data when uninstalled
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete network options
if (is_multisite()) {
    delete_site_option('vs3_auto_og_network_settings');
    
    // Delete options from all sites
    global $wpdb;
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        delete_option('vs3_auto_og_site_settings');
        restore_current_blog();
    }
} else {
    delete_option('vs3_auto_og_site_settings');
}

// Delete generated images
function vs3_auto_og_delete_images() {
    $upload_dir = wp_upload_dir();
    $og_dir = $upload_dir['basedir'] . '/vs3-og';
    
    if (file_exists($og_dir)) {
        $files = glob($og_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
        @rmdir($og_dir);
    }
}

if (is_multisite()) {
    global $wpdb;
    $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
    
    foreach ($blog_ids as $blog_id) {
        switch_to_blog($blog_id);
        vs3_auto_og_delete_images();
        restore_current_blog();
    }
} else {
    vs3_auto_og_delete_images();
}

// Flush rewrite rules
flush_rewrite_rules();

