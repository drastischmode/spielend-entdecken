<?php
/**
 * Auto-run bulk meta on first theme activation
 * Triggered via wp_footer hook on next page load
 */

if (!defined('ABSPATH')) exit;

/**
 * Check if bulk meta has been run
 * If not, queue it for execution
 */
add_action('init', function() {
    $last_run = get_option('se_bulk_meta_last_run');
    $current_version = defined('SE_THEME_VERSION') ? SE_THEME_VERSION : '0';
    
    // Run on each version upgrade or if never run
    if (!$last_run || version_compare($last_run, $current_version, '<')) {
        // Schedule as a background action
        if (!wp_next_scheduled('se_bulk_meta_cron')) {
            wp_schedule_single_event(time() + 10, 'se_bulk_meta_cron');
        }
    }
});

/**
 * Cron action: Execute bulk meta
 */
add_action('se_bulk_meta_cron', function() {
    require_once get_template_directory() . '/inc/bulk-meta-tool.php';
    
    $result = se_bulk_meta_auto_fill();
    
    // Log result
    update_option('se_bulk_meta_last_run', defined('SE_THEME_VERSION') ? SE_THEME_VERSION : '2.0.0');
    update_option('se_bulk_meta_last_result', array(
        'timestamp' => current_time('Y-m-d H:i:s'),
        'updated' => $result['updated'],
        'error' => $result['error'],
    ));
});

// Force wp-cron to run (for shared hosting)
if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
    // Cron is disabled, need manual trigger via admin UI
    // User must go to Tools → Spielend Bulk-Meta and click "Auto-Fill starten"
}
