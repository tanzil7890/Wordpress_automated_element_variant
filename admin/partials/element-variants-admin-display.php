<?php
/**
 * Main admin page display.
 */
?>

<div class="wrap element-variants-admin-page">
    <div class="element-variants-admin-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p><?php _e('Create and manage element variants for different users.', 'element-variants'); ?></p>
    </div>
    
    <div class="element-variants-admin-cards">
        <div class="element-variants-admin-card">
            <div class="element-variants-card-header">
                <h2><?php _e('Quick Start', 'element-variants'); ?></h2>
            </div>
            <div class="element-variants-card-body">
                <p><?php _e('Element Variants allows you to create variations of page elements for different users.', 'element-variants'); ?></p>
                <p><?php _e('To get started:', 'element-variants'); ?></p>
                <ol>
                    <li><?php _e('Visit any page on your site with the editor enabled', 'element-variants'); ?></li>
                    <li><?php _e('Click "Select Element" in the editor that appears at the bottom right', 'element-variants'); ?></li>
                    <li><?php _e('Click on any element on the page to select it', 'element-variants'); ?></li>
                    <li><?php _e('Create your variant with custom content and conditions', 'element-variants'); ?></li>
                </ol>
                <p>
                    <a href="<?php echo esc_url(add_query_arg('element_variants_editor', 'true', home_url())); ?>" class="button button-primary" target="_blank">
                        <?php _e('Open Frontend Editor', 'element-variants'); ?>
                    </a>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=element-variants-variants')); ?>" class="button">
                        <?php _e('Manage Variants', 'element-variants'); ?>
                    </a>
                </p>
            </div>
        </div>
        
        <div class="element-variants-admin-card">
            <div class="element-variants-card-header">
                <h2><?php _e('Latest Variants', 'element-variants'); ?></h2>
            </div>
            <div class="element-variants-card-body">
                <?php
                $variants_manager = new Element_Variants_Manager();
                $variants = $variants_manager->get_variants();
                
                if (empty($variants)) {
                    echo '<p>' . __('No variants created yet. Use the editor to create your first variant.', 'element-variants') . '</p>';
                } else {
                    // Show the 5 most recent variants
                    $latest_variants = array_slice($variants, 0, 5);
                    ?>
                    <table class="element-variants-table">
                        <thead>
                            <tr>
                                <th><?php _e('Name', 'element-variants'); ?></th>
                                <th><?php _e('Selector', 'element-variants'); ?></th>
                                <th><?php _e('Actions', 'element-variants'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($latest_variants as $variant) : ?>
                            <tr>
                                <td><?php echo esc_html($variant->name); ?></td>
                                <td><code class="element-variants-selector"><?php echo esc_html($variant->selector); ?></code></td>
                                <td class="element-variants-actions">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=element-variants-variants&action=edit&id=' . $variant->id)); ?>" class="button button-small">
                                        <?php _e('Edit', 'element-variants'); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (count($variants) > 5) : ?>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=element-variants-variants')); ?>">
                            <?php _e('View all variants', 'element-variants'); ?>
                        </a>
                    </p>
                    <?php endif; ?>
                <?php } ?>
            </div>
        </div>
    </div>
    
    <div class="element-variants-admin-card">
        <div class="element-variants-card-header">
            <h2><?php _e('Documentation', 'element-variants'); ?></h2>
        </div>
        <div class="element-variants-card-body">
            <h3><?php _e('How Element Variants Works', 'element-variants'); ?></h3>
            <p><?php _e('Element Variants allows you to create personalized content variations for different users on your WordPress site.', 'element-variants'); ?></p>
            
            <h4><?php _e('1. Select Elements', 'element-variants'); ?></h4>
            <p><?php _e('Use the frontend editor to visually select any element on your page that you want to create a variant for.', 'element-variants'); ?></p>
            
            <h4><?php _e('2. Create Variants', 'element-variants'); ?></h4>
            <p><?php _e('For each selected element, you can create custom HTML content that will replace the original content.', 'element-variants'); ?></p>
            
            <h4><?php _e('3. Add Conditions', 'element-variants'); ?></h4>
            <p><?php _e('Specify conditions for when your variants should appear. You can target specific user roles, logged-in status, or user IDs.', 'element-variants'); ?></p>
            
            <h4><?php _e('4. View Personalized Content', 'element-variants'); ?></h4>
            <p><?php _e('When users visit your site, they\'ll see personalized content based on the conditions you\'ve set up.', 'element-variants'); ?></p>
            
            <h3><?php _e('Tips for Best Results', 'element-variants'); ?></h3>
            <ul>
                <li><?php _e('Select elements with unique IDs or specific classes for more reliable targeting', 'element-variants'); ?></li>
                <li><?php _e('Use the preview feature to test how your variants look before saving', 'element-variants'); ?></li>
                <li><?php _e('Keep your variants organized by using clear, descriptive names', 'element-variants'); ?></li>
                <li><?php _e('Test your variants with different user accounts to ensure they display correctly', 'element-variants'); ?></li>
            </ul>
        </div>
    </div>
</div> 