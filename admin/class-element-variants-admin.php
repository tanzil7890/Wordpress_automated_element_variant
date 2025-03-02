<?php
/**
 * The admin-specific functionality of the plugin.
 */
class Element_Variants_Admin {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Constructor
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style('element-variants-admin', ELEMENT_VARIANTS_URL . 'admin/css/element-variants-admin.css', array(), ELEMENT_VARIANTS_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script('element-variants-admin', ELEMENT_VARIANTS_URL . 'admin/js/element-variants-admin.js', array('jquery'), ELEMENT_VARIANTS_VERSION, false);
        
        // Localize the script with new data
        $localize_data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('element_variants_nonce'),
        );
        wp_localize_script('element-variants-admin', 'element_variants_admin', $localize_data);
    }

    /**
     * Add menu item for the plugin.
     */
    public function add_plugin_menu() {
        add_menu_page(
            __('Element Variants', 'element-variants'),
            __('Element Variants', 'element-variants'),
            'manage_options',
            'element-variants',
            array($this, 'display_plugin_admin_page'),
            'dashicons-layout',
            30
        );
        
        add_submenu_page(
            'element-variants',
            __('Manage Variants', 'element-variants'),
            __('Manage Variants', 'element-variants'),
            'manage_options',
            'element-variants-variants',
            array($this, 'display_variants_page')
        );
        
        add_submenu_page(
            'element-variants',
            __('Settings', 'element-variants'),
            __('Settings', 'element-variants'),
            'manage_options',
            'element-variants-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Display the main plugin admin page.
     */
    public function display_plugin_admin_page() {
        include_once ELEMENT_VARIANTS_PATH . 'admin/partials/element-variants-admin-display.php';
    }

    /**
     * Display the variants management page.
     */
    public function display_variants_page() {
        include_once ELEMENT_VARIANTS_PATH . 'admin/partials/element-variants-variants-display.php';
    }

    /**
     * Display the settings page.
     */
    public function display_settings_page() {
        include_once ELEMENT_VARIANTS_PATH . 'admin/partials/element-variants-settings-display.php';
    }

    /**
     * Register settings for the plugin.
     */
    public function register_settings() {
        register_setting('element_variants_settings', 'element_variants_user_roles', array(
            'type' => 'array',
            'description' => 'User roles that can see variants',
            'sanitize_callback' => array($this, 'sanitize_user_roles'),
            'default' => array('administrator', 'editor'),
        ));
        
        register_setting('element_variants_settings', 'element_variants_show_all_users', array(
            'type' => 'boolean',
            'description' => 'Show variants to all users, including non-logged-in visitors',
            'sanitize_callback' => 'rest_sanitize_boolean',
            'default' => true,
        ));
        
        register_setting('element_variants_settings', 'element_variants_enable_editor', array(
            'type' => 'boolean',
            'description' => 'Enable frontend editor',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => true,
        ));
    }

    /**
     * Sanitize the user roles.
     */
    public function sanitize_user_roles($input) {
        $valid_roles = array_keys(wp_roles()->roles);
        return array_intersect($input, $valid_roles);
    }
    
    /**
     * AJAX handler for saving element variants.
     */
    public function save_element_variant() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'element_variants_nonce')) {
            wp_send_json_error('Security check failed.');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }
        
        // Validate and sanitize inputs
        $selector = isset($_POST['selector']) ? sanitize_text_field($_POST['selector']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $conditions = isset($_POST['conditions']) ? json_decode(stripslashes($_POST['conditions']), true) : array();
        
        if (empty($selector) || empty($content) || empty($name)) {
            wp_send_json_error('Required fields are missing.');
        }
        
        // Get the variants manager
        $variants_manager = new Element_Variants_Manager();
        
        // Save the variant
        $result = $variants_manager->save_variant($selector, $content, $name, $conditions);
        
        if ($result) {
            wp_send_json_success('Variant saved successfully.');
        } else {
            wp_send_json_error('Failed to save variant.');
        }
    }
    
    /**
     * AJAX handler for getting element variants.
     */
    public function get_element_variants() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'element_variants_nonce')) {
            wp_send_json_error('Security check failed.');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }
        
        // Get the variants manager
        $variants_manager = new Element_Variants_Manager();
        
        // Get the variants
        $variants = $variants_manager->get_variants();
        
        wp_send_json_success($variants);
    }
    
    /**
     * AJAX handler for deleting element variants.
     */
    public function delete_element_variant() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'element_variants_nonce')) {
            wp_send_json_error('Security check failed.');
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }
        
        // Validate and sanitize inputs
        $variant_id = isset($_POST['variant_id']) ? intval($_POST['variant_id']) : 0;
        
        if (empty($variant_id)) {
            wp_send_json_error('Variant ID is required.');
        }
        
        // Get the variants manager
        $variants_manager = new Element_Variants_Manager();
        
        // Delete the variant
        $result = $variants_manager->delete_variant($variant_id);
        
        if ($result) {
            wp_send_json_success('Variant deleted successfully.');
        } else {
            wp_send_json_error('Failed to delete variant.');
        }
    }
} 