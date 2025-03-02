<?php
/**
 * The public-facing functionality of the plugin.
 */
class Element_Variants_Public {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Constructor
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        // Only enqueue editor stylesheet if needed
        if ($this->should_load_editor()) {
            wp_enqueue_style('element-variants-editor', ELEMENT_VARIANTS_URL . 'public/css/element-variants-editor.css', array(), ELEMENT_VARIANTS_VERSION, 'all');
        }
        
        // Always enqueue public styles
        wp_enqueue_style('element-variants-public', ELEMENT_VARIANTS_URL . 'public/css/element-variants-public.css', array(), ELEMENT_VARIANTS_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        // Only enqueue editor script if needed
        if ($this->should_load_editor()) {
            wp_enqueue_script('element-variants-editor', ELEMENT_VARIANTS_URL . 'public/js/element-variants-editor.js', array('jquery'), ELEMENT_VARIANTS_VERSION, true);
            
            // Localize the script with editor data
            $localize_data = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('element_variants_nonce'),
                'i18n' => array(
                    'select_element' => __('Click to select an element', 'element-variants'),
                    'create_variant' => __('Create Variant', 'element-variants'),
                    'edit_variant' => __('Edit Variant', 'element-variants'),
                    'save' => __('Save', 'element-variants'),
                    'cancel' => __('Cancel', 'element-variants'),
                    'variant_name' => __('Variant Name', 'element-variants'),
                    'conditions' => __('Conditions', 'element-variants'),
                ),
            );
            wp_localize_script('element-variants-editor', 'element_variants_editor', $localize_data);
        }
        
        // Always enqueue public scripts
        wp_enqueue_script('element-variants-public', ELEMENT_VARIANTS_URL . 'public/js/element-variants-public.js', array('jquery'), ELEMENT_VARIANTS_VERSION, true);
        
        // Localize the script with variant data
        $variants_manager = new Element_Variants_Manager();
        $variants = $variants_manager->get_variants();
        
        $localize_data = array(
            'variants' => $variants,
            'current_user' => $this->get_current_user_data(),
        );
        wp_localize_script('element-variants-public', 'element_variants_public', $localize_data);
    }

    /**
     * Apply element variants to content.
     *
     * @param string $content The content.
     * @return string Modified content.
     */
    public function apply_element_variants($content) {
        // If the content is empty, just return it
        if (empty($content)) {
            return $content;
        }
        
        // If no user is logged in or user doesn't have permission to see variants, return the original content
        if (!$this->can_user_see_variants()) {
            return $content;
        }
        
        // Get all variants that should be applied
        $variants_manager = new Element_Variants_Manager();
        $variants = $variants_manager->get_applicable_variants();
        
        // If no variants to apply, return the original content
        if (empty($variants)) {
            return $content;
        }
        
        // Add a flag to the content for the JavaScript to process
        $content .= '<script>var element_variants_content_loaded = true;</script>';
        
        return $content;
    }

    /**
     * Check if the current user can see variants.
     *
     * @return bool Whether the current user can see variants.
     */
    private function can_user_see_variants() {
        // If no user is logged in, they can't see variants
        if (!is_user_logged_in()) {
            return false;
        }
        
        // Get the allowed user roles
        $allowed_roles = get_option('element_variants_user_roles', array('administrator', 'editor'));
        
        // Get the current user
        $user = wp_get_current_user();
        
        // Check if the user has any of the allowed roles
        foreach ($allowed_roles as $role) {
            if (in_array($role, $user->roles)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get data about the current user for variant conditions.
     *
     * @return array User data.
     */
    private function get_current_user_data() {
        // If no user is logged in, return empty data
        if (!is_user_logged_in()) {
            return array(
                'logged_in' => false,
            );
        }
        
        // Get the current user
        $user = wp_get_current_user();
        
        return array(
            'logged_in' => true,
            'id' => $user->ID,
            'roles' => $user->roles,
            'username' => $user->user_login,
            'email' => $user->user_email,
        );
    }

    /**
     * Check if the editor should be loaded.
     *
     * @return bool Whether the editor should be loaded.
     */
    private function should_load_editor() {
        // Check if the editor is enabled in settings
        $editor_enabled = get_option('element_variants_enable_editor', true);
        
        if (!$editor_enabled) {
            return false;
        }
        
        // Check if the current user can edit variants
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        // Check if the editor is explicitly requested via URL parameter
        if (isset($_GET['element_variants_editor']) && $_GET['element_variants_editor'] === 'true') {
            return true;
        }
        
        return false;
    }

    /**
     * Load the editor if necessary.
     */
    public function maybe_load_editor() {
        if ($this->should_load_editor()) {
            include_once ELEMENT_VARIANTS_PATH . 'public/partials/element-variants-editor.php';
        }
    }
} 