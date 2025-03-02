<?php
/**
 * Variants management page display.
 */

// Get the current action
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
$variant_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Process form submission if needed
if (isset($_POST['element_variants_save']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'element_variants_save_variant')) {
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $selector = isset($_POST['selector']) ? sanitize_text_field($_POST['selector']) : '';
    $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';
    $condition_types = isset($_POST['condition_type']) ? $_POST['condition_type'] : array();
    $condition_values = isset($_POST['condition_value']) ? $_POST['condition_value'] : array();
    
    // Build conditions array
    $conditions = array();
    for ($i = 0; $i < count($condition_types); $i++) {
        if (isset($condition_types[$i]) && isset($condition_values[$i])) {
            $conditions[] = array(
                'type' => sanitize_text_field($condition_types[$i]),
                'value' => $condition_values[$i], // Will be sanitized by the manager
            );
        }
    }
    
    // Save the variant
    $variants_manager = new Element_Variants_Manager();
    $result = $variants_manager->save_variant($selector, $content, $name, $conditions, $variant_id);
    
    if ($result) {
        $message = __('Variant saved successfully.', 'element-variants');
        $message_type = 'success';
        
        // Redirect to the list view
        if ($action === 'add') {
            wp_redirect(admin_url('admin.php?page=element-variants-variants&message=' . urlencode($message)));
            exit;
        }
    } else {
        $message = __('Error saving variant.', 'element-variants');
        $message_type = 'error';
    }
}

// Handle message parameter
if (isset($_GET['message'])) {
    $message = sanitize_text_field($_GET['message']);
    $message_type = 'success';
}

// Get variants if needed
$variants_manager = new Element_Variants_Manager();
$variant = null;

if ($action === 'edit' && $variant_id > 0) {
    $variant = $variants_manager->get_variant($variant_id);
    
    if (!$variant) {
        $message = __('Variant not found.', 'element-variants');
        $message_type = 'error';
        $action = 'list';
    }
}

?>

<div class="wrap element-variants-admin-page">
    <div class="element-variants-admin-header">
        <h1>
            <?php 
            if ($action === 'add') {
                _e('Add New Variant', 'element-variants');
            } elseif ($action === 'edit') {
                _e('Edit Variant', 'element-variants');
            } else {
                _e('Manage Variants', 'element-variants');
            }
            ?>
        </h1>
    </div>
    
    <?php if (isset($message)) : ?>
        <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>
    
    <?php if ($action === 'list') : ?>
        <!-- Variants List View -->
        <div class="element-variants-admin-card">
            <div class="element-variants-card-header">
                <h2><?php _e('All Variants', 'element-variants'); ?></h2>
            </div>
            <div class="element-variants-card-body">
                <p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=element-variants-variants&action=add')); ?>" class="button button-primary">
                        <?php _e('Add New Variant', 'element-variants'); ?>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg('element_variants_editor', 'true', home_url())); ?>" class="button" target="_blank">
                        <?php _e('Open Frontend Editor', 'element-variants'); ?>
                    </a>
                </p>
                
                <table id="element-variants-table" class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'element-variants'); ?></th>
                            <th><?php _e('Selector', 'element-variants'); ?></th>
                            <th><?php _e('Content Preview', 'element-variants'); ?></th>
                            <th><?php _e('Conditions', 'element-variants'); ?></th>
                            <th><?php _e('Actions', 'element-variants'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="5"><?php _e('Loading variants...', 'element-variants'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else : ?>
        <!-- Add/Edit Variant Form -->
        <div class="element-variants-admin-card">
            <div class="element-variants-card-header">
                <h2>
                    <?php echo $action === 'add' ? __('Add New Variant', 'element-variants') : __('Edit Variant', 'element-variants'); ?>
                </h2>
            </div>
            <div class="element-variants-card-body">
                <form method="post" action="" class="element-variants-form">
                    <?php wp_nonce_field('element_variants_save_variant'); ?>
                    
                    <div class="element-variants-form-row">
                        <label for="name"><?php _e('Variant Name', 'element-variants'); ?></label>
                        <input type="text" id="name" name="name" value="<?php echo $variant ? esc_attr($variant->name) : ''; ?>" required>
                        <p class="element-variants-description"><?php _e('A descriptive name to help you identify this variant.', 'element-variants'); ?></p>
                    </div>
                    
                    <div class="element-variants-form-row">
                        <label for="selector"><?php _e('CSS Selector', 'element-variants'); ?></label>
                        <input type="text" id="selector" name="selector" value="<?php echo $variant ? esc_attr($variant->selector) : ''; ?>" required>
                        <p class="element-variants-description"><?php _e('The CSS selector for the element. Use the frontend editor to generate this automatically.', 'element-variants'); ?></p>
                    </div>
                    
                    <div class="element-variants-form-row">
                        <label for="content"><?php _e('Variant Content', 'element-variants'); ?></label>
                        <textarea id="content" name="content" rows="10" required><?php echo $variant ? esc_textarea($variant->content) : ''; ?></textarea>
                        <p class="element-variants-description"><?php _e('The HTML content that will replace the original element content.', 'element-variants'); ?></p>
                    </div>
                    
                    <div class="element-variants-form-row">
                        <label><?php _e('Conditions', 'element-variants'); ?></label>
                        <p class="element-variants-description"><?php _e('Specify when this variant should be displayed.', 'element-variants'); ?></p>
                        
                        <div id="element-variants-conditions">
                            <?php 
                            $conditions = $variant ? $variant->processed_conditions : array();
                            
                            if (empty($conditions)) {
                                // Add a default empty condition
                                $conditions[] = array('type' => 'user_role', 'value' => array('administrator'));
                            }
                            
                            foreach ($conditions as $index => $condition) :
                            ?>
                            <div class="element-variants-condition-row">
                                <div class="element-variants-form-row">
                                    <label for="condition_type_<?php echo $index; ?>"><?php _e('Condition Type', 'element-variants'); ?></label>
                                    <select name="condition_type[]" id="condition_type_<?php echo $index; ?>" class="condition-type">
                                        <option value="user_role" <?php selected($condition['type'], 'user_role'); ?>><?php _e('User Role', 'element-variants'); ?></option>
                                        <option value="user_logged_in" <?php selected($condition['type'], 'user_logged_in'); ?>><?php _e('User Logged In', 'element-variants'); ?></option>
                                        <option value="user_id" <?php selected($condition['type'], 'user_id'); ?>><?php _e('User ID', 'element-variants'); ?></option>
                                    </select>
                                </div>
                                
                                <div class="element-variants-form-row condition-value-container">
                                    <?php if ($condition['type'] === 'user_role') : ?>
                                        <label for="condition_value_<?php echo $index; ?>"><?php _e('User Roles', 'element-variants'); ?></label>
                                        <select name="condition_value[]" id="condition_value_<?php echo $index; ?>" multiple>
                                            <?php
                                            $roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
                                            $selected_roles = is_array($condition['value']) ? $condition['value'] : array($condition['value']);
                                            
                                            foreach ($roles as $role) :
                                            ?>
                                                <option value="<?php echo esc_attr($role); ?>" <?php echo in_array($role, $selected_roles) ? 'selected' : ''; ?>>
                                                    <?php echo esc_html(ucfirst($role)); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="element-variants-description"><?php _e('Select one or more user roles.', 'element-variants'); ?></p>
                                    <?php elseif ($condition['type'] === 'user_logged_in') : ?>
                                        <label for="condition_value_<?php echo $index; ?>"><?php _e('User Must Be Logged In', 'element-variants'); ?></label>
                                        <select name="condition_value[]" id="condition_value_<?php echo $index; ?>">
                                            <option value="1" <?php selected($condition['value'], true); ?>><?php _e('Yes', 'element-variants'); ?></option>
                                            <option value="0" <?php selected($condition['value'], false); ?>><?php _e('No', 'element-variants'); ?></option>
                                        </select>
                                    <?php elseif ($condition['type'] === 'user_id') : ?>
                                        <label for="condition_value_<?php echo $index; ?>"><?php _e('User ID', 'element-variants'); ?></label>
                                        <input type="number" name="condition_value[]" id="condition_value_<?php echo $index; ?>" value="<?php echo esc_attr($condition['value']); ?>" placeholder="<?php _e('User ID', 'element-variants'); ?>">
                                        <p class="element-variants-description"><?php _e('Enter the WordPress user ID.', 'element-variants'); ?></p>
                                    <?php else : ?>
                                        <label for="condition_value_<?php echo $index; ?>"><?php _e('Value', 'element-variants'); ?></label>
                                        <input type="text" name="condition_value[]" id="condition_value_<?php echo $index; ?>" value="<?php echo esc_attr($condition['value']); ?>">
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($index > 0 || count($conditions) > 1) : ?>
                                    <button type="button" class="button element-variants-remove-condition"><?php _e('Remove Condition', 'element-variants'); ?></button>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <p>
                            <button type="button" id="element-variants-add-condition" class="button"><?php _e('Add Condition', 'element-variants'); ?></button>
                        </p>
                    </div>
                    
                    <div class="element-variants-form-row">
                        <input type="hidden" name="element_variants_save" value="1">
                        <?php if ($action === 'edit') : ?>
                            <input type="hidden" name="id" value="<?php echo esc_attr($variant_id); ?>">
                        <?php endif; ?>
                        
                        <a href="<?php echo esc_url(admin_url('admin.php?page=element-variants-variants')); ?>" class="button">
                            <?php _e('Cancel', 'element-variants'); ?>
                        </a>
                        <button type="submit" class="button button-primary">
                            <?php echo $action === 'add' ? __('Add Variant', 'element-variants') : __('Update Variant', 'element-variants'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div> 