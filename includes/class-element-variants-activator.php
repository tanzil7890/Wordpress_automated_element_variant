<?php
/**
 * Fired during plugin activation.
 */
class Element_Variants_Activator {

    /**
     * Execute actions during plugin activation.
     */
    public static function activate() {
        // Load all required classes
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-element-variants-db.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-element-variants-manager.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-element-variants-selector.php';
        
        // Create necessary database tables
        $db = new Element_Variants_DB();
        $db->create_tables();
        
        // Set default options
        if (!get_option('element_variants_user_roles')) {
            update_option('element_variants_user_roles', array('administrator', 'editor'));
        }
        
        if (!get_option('element_variants_enable_editor')) {
            update_option('element_variants_enable_editor', true);
        }
        
        // Maybe add sample variant for first-time users
        self::maybe_add_sample_variant();
        
        // Clear permalinks
        flush_rewrite_rules();
    }
    
    /**
     * Add a sample variant for first-time users.
     */
    private static function maybe_add_sample_variant() {
        global $wpdb;
        
        // No need to require classes here as they're already loaded in the activate method
        
        // Check if we already have variants
        $table_name = $wpdb->prefix . 'element_variants';
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        // If we have variants, don't add a sample
        if ($count > 0) {
            return;
        }
        
        // Add a sample variant
        $variants_manager = new Element_Variants_Manager();
        $variants_manager->save_variant(
            '.entry-content p:first-child', // Selector
            '<p>This is a sample variant created by Element Variants. You can edit or delete this in the Element Variants settings.</p>', // Content
            'Sample Variant', // Name
            array( // Conditions
                array(
                    'type' => 'user_role',
                    'value' => array('administrator'),
                ),
            )
        );
    }
} 