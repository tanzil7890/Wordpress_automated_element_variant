<?php
/**
 * Plugin Name: Outhad AI ELement Variant
 * Plugin URI: https://outhad.com/element-variants
 * Description: A plugin that allows users to select page elements and create variations for different users.
 * Version: 1.0.0
 * Author: Outhad AI
 * Author URI: https://example.com
 * Text Domain: element-variants
 * License: GPL v2 or later
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die;
}

// Define plugin constants
define('ELEMENT_VARIANTS_VERSION', '1.0.0');
define('ELEMENT_VARIANTS_PATH', plugin_dir_path(__FILE__));
define('ELEMENT_VARIANTS_URL', plugin_dir_url(__FILE__));

// Include required files
require_once ELEMENT_VARIANTS_PATH . 'includes/class-element-variants.php';

// Initialize the plugin
function element_variants_init() {
    $plugin = new Element_Variants();
    $plugin->init();
}
add_action('plugins_loaded', 'element_variants_init');

// Register activation hook
register_activation_hook(__FILE__, 'element_variants_activate');
function element_variants_activate() {
    // Load all required files for activation
    require_once ELEMENT_VARIANTS_PATH . 'includes/class-element-variants-db.php';
    require_once ELEMENT_VARIANTS_PATH . 'includes/class-element-variants-manager.php';
    require_once ELEMENT_VARIANTS_PATH . 'includes/class-element-variants-selector.php';
    require_once ELEMENT_VARIANTS_PATH . 'includes/class-element-variants-activator.php';
    
    // Create necessary database tables and initialize settings
    Element_Variants_Activator::activate();
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'element_variants_deactivate');
function element_variants_deactivate() {
    // Clean up if needed
    require_once ELEMENT_VARIANTS_PATH . 'includes/class-element-variants-deactivator.php';
    Element_Variants_Deactivator::deactivate();
} 