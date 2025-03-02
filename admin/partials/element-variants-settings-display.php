<?php
/**
 * Settings page display.
 */
?>

<div class="wrap element-variants-admin-page">
    <div class="element-variants-admin-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p><?php _e('Configure settings for Element Variants.', 'element-variants'); ?></p>
    </div>
    
    <div class="element-variants-admin-card">
        <div class="element-variants-card-header">
            <h2><?php _e('General Settings', 'element-variants'); ?></h2>
        </div>
        <div class="element-variants-card-body">
            <form method="post" action="options.php" class="element-variants-settings-form">
                <?php
                settings_fields('element_variants_settings');
                do_settings_sections('element_variants_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="element_variants_user_roles"><?php _e('User Roles', 'element-variants'); ?></label>
                        </th>
                        <td>
                            <?php
                            $allowed_roles = get_option('element_variants_user_roles', array('administrator', 'editor'));
                            $roles = wp_roles()->roles;
                            
                            foreach ($roles as $role_key => $role) :
                            ?>
                                <label>
                                    <input type="checkbox" name="element_variants_user_roles[]" value="<?php echo esc_attr($role_key); ?>" <?php checked(in_array($role_key, $allowed_roles)); ?>>
                                    <?php echo esc_html($role['name']); ?>
                                </label><br>
                            <?php endforeach; ?>
                            <p class="description"><?php _e('Select which user roles can see variants.', 'element-variants'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="element_variants_enable_editor"><?php _e('Frontend Editor', 'element-variants'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" name="element_variants_enable_editor" value="1" <?php checked(get_option('element_variants_enable_editor', true)); ?>>
                                <?php _e('Enable frontend editor', 'element-variants'); ?>
                            </label>
                            <p class="description"><?php _e('Allow users with permissions to use the frontend editor to create variants.', 'element-variants'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
    </div>
    
    <div class="element-variants-admin-card">
        <div class="element-variants-card-header">
            <h2><?php _e('Advanced Options', 'element-variants'); ?></h2>
        </div>
        <div class="element-variants-card-body">
            <h3><?php _e('Debug Mode', 'element-variants'); ?></h3>
            <p><?php _e('Enable debug mode to visualize which elements have variants applied to them.', 'element-variants'); ?></p>
            <p>
                <label>
                    <input type="checkbox" id="element-variants-debug-mode" <?php checked(get_option('element_variants_debug_mode', false)); ?>>
                    <?php _e('Enable debug mode', 'element-variants'); ?>
                </label>
            </p>
            <p class="description"><?php _e('When enabled, elements with variants will be highlighted on the frontend for administrators.', 'element-variants'); ?></p>
            
            <h3><?php _e('Reset Plugin', 'element-variants'); ?></h3>
            <p><?php _e('Use this option to reset all plugin data. This will delete all variants and settings.', 'element-variants'); ?></p>
            <p>
                <button id="element-variants-reset" class="button button-secondary"><?php _e('Reset Plugin Data', 'element-variants'); ?></button>
            </p>
            <p class="description"><?php _e('Warning: This action cannot be undone.', 'element-variants'); ?></p>
            
            <script>
                jQuery(document).ready(function($) {
                    // Handle debug mode toggle
                    $('#element-variants-debug-mode').on('change', function() {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'element_variants_toggle_debug',
                                debug: $(this).is(':checked') ? 1 : 0,
                                nonce: '<?php echo wp_create_nonce('element_variants_settings'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    alert('<?php _e('Debug mode setting updated.', 'element-variants'); ?>');
                                } else {
                                    alert('<?php _e('Error updating debug mode.', 'element-variants'); ?>');
                                }
                            }
                        });
                    });
                    
                    // Handle reset button
                    $('#element-variants-reset').on('click', function(e) {
                        e.preventDefault();
                        
                        if (confirm('<?php _e('Are you sure you want to reset all plugin data? This action cannot be undone.', 'element-variants'); ?>')) {
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'element_variants_reset_plugin',
                                    nonce: '<?php echo wp_create_nonce('element_variants_settings'); ?>'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        alert('<?php _e('Plugin data has been reset successfully.', 'element-variants'); ?>');
                                        window.location.reload();
                                    } else {
                                        alert('<?php _e('Error resetting plugin data.', 'element-variants'); ?>');
                                    }
                                }
                            });
                        }
                    });
                });
            </script>
        </div>
    </div>
</div> 