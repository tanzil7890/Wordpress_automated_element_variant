<?php
/**
 * Fired during plugin deactivation.
 */
class Element_Variants_Deactivator {

    /**
     * Execute actions during plugin deactivation.
     */
    public static function deactivate() {
        // Clear caches if any
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        // Clear any transients
        self::delete_transients();
        
        // Clear any scheduled events
        self::clear_scheduled_events();
        
        // Clear permalinks
        flush_rewrite_rules();
    }
    
    /**
     * Delete any transients created by the plugin.
     */
    private static function delete_transients() {
        global $wpdb;
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_element_variants_%'");
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_timeout_element_variants_%'");
    }
    
    /**
     * Clear any scheduled events created by the plugin.
     */
    private static function clear_scheduled_events() {
        $timestamp = wp_next_scheduled('element_variants_cleanup_event');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'element_variants_cleanup_event');
        }
    }
} 