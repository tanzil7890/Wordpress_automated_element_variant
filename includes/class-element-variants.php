<?php
/**
 * The main plugin class
 */
class Element_Variants {
    /**
     * The unique instance of the plugin.
     */
    private static $instance;

    /**
     * Gets an instance of the plugin.
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize the plugin.
     */
    public function init() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // Admin-specific functionality
        require_once ELEMENT_VARIANTS_PATH . 'admin/class-element-variants-admin.php';
        
        // Public-facing functionality
        require_once ELEMENT_VARIANTS_PATH . 'public/class-element-variants-public.php';
        
        // Database handler
        require_once ELEMENT_VARIANTS_PATH . 'includes/class-element-variants-db.php';
        
        // Element selector and variant manager
        require_once ELEMENT_VARIANTS_PATH . 'includes/class-element-variants-selector.php';
        require_once ELEMENT_VARIANTS_PATH . 'includes/class-element-variants-manager.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     */
    private function define_admin_hooks() {
        $plugin_admin = new Element_Variants_Admin();
        
        // Admin menu and settings
        add_action('admin_menu', array($plugin_admin, 'add_plugin_menu'));
        add_action('admin_init', array($plugin_admin, 'register_settings'));
        
        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
        
        // AJAX handlers for admin
        add_action('wp_ajax_save_element_variant', array($plugin_admin, 'save_element_variant'));
        add_action('wp_ajax_get_element_variants', array($plugin_admin, 'get_element_variants'));
        add_action('wp_ajax_delete_element_variant', array($plugin_admin, 'delete_element_variant'));
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $plugin_public = new Element_Variants_Public();
        
        // Public scripts and styles
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($plugin_public, 'enqueue_scripts'));
        
        // Content filter to apply variants
        add_filter('the_content', array($plugin_public, 'apply_element_variants'));
        
        // Front-end editor when user has permission
        add_action('wp_footer', array($plugin_public, 'maybe_load_editor'));
    }
} 