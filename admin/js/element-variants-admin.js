/**
 * Element Variants Admin JavaScript
 */
(function( $ ) {
    'use strict';

    // Initialize admin functionality
    function initAdmin() {
        bindEvents();
        initVariantsTable();
    }

    // Bind events
    function bindEvents() {
        // Handle variant deletion
        $(document).on('click', '.element-variants-delete', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to delete this variant? This action cannot be undone.')) {
                deleteVariant($(this).data('id'));
            }
        });
        
        // Handle add condition button in the variant form
        $('#element-variants-add-condition').on('click', function() {
            addConditionField();
        });
        
        // Handle condition type change
        $(document).on('change', '.condition-type', function() {
            updateConditionValueField($(this));
        });
        
        // Handle condition removal
        $(document).on('click', '.element-variants-remove-condition', function() {
            $(this).closest('.element-variants-condition-row').remove();
        });
    }

    // Initialize the variants table
    function initVariantsTable() {
        // If the table exists
        if ($('#element-variants-table').length > 0) {
            loadVariants();
        }
    }

    // Load variants from the server
    function loadVariants() {
        $.ajax({
            url: element_variants_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'get_element_variants',
                nonce: element_variants_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderVariantsTable(response.data);
                } else {
                    $('#element-variants-table tbody').html('<tr><td colspan="5">Error loading variants: ' + response.data + '</td></tr>');
                }
            },
            error: function() {
                $('#element-variants-table tbody').html('<tr><td colspan="5">Error loading variants. Please try again.</td></tr>');
            }
        });
    }

    // Render the variants table
    function renderVariantsTable(variants) {
        const tbody = $('#element-variants-table tbody');
        tbody.empty();
        
        if (variants.length === 0) {
            tbody.html('<tr><td colspan="5">No variants found. Create your first variant to get started.</td></tr>');
            return;
        }
        
        variants.forEach(function(variant) {
            const row = $(`
                <tr>
                    <td>${variant.name}</td>
                    <td><code class="element-variants-selector">${variant.selector}</code></td>
                    <td class="element-variants-preview">${variant.content}</td>
                    <td>${formatConditions(variant.processed_conditions)}</td>
                    <td class="element-variants-actions">
                        <a href="?page=element-variants-variants&action=edit&id=${variant.id}" class="button button-small">Edit</a>
                        <a href="#" class="element-variants-delete button button-small button-link-delete" data-id="${variant.id}">Delete</a>
                    </td>
                </tr>
            `);
            
            tbody.append(row);
        });
    }

    // Format the conditions for display
    function formatConditions(conditions) {
        if (!conditions || conditions.length === 0) {
            return '<span class="element-variants-badge">No conditions</span>';
        }
        
        return conditions.map(function(condition) {
            let text = '';
            
            switch (condition.type) {
                case 'user_role':
                    text = 'User role: ' + (Array.isArray(condition.value) ? condition.value.join(', ') : condition.value);
                    break;
                    
                case 'user_logged_in':
                    text = 'User is ' + (condition.value ? 'logged in' : 'not logged in');
                    break;
                    
                case 'user_id':
                    text = 'User ID: ' + condition.value;
                    break;
                    
                default:
                    text = condition.type + ': ' + condition.value;
            }
            
            return '<span class="element-variants-badge">' + text + '</span>';
        }).join(' ');
    }

    // Delete a variant
    function deleteVariant(id) {
        $.ajax({
            url: element_variants_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'delete_element_variant',
                nonce: element_variants_admin.nonce,
                variant_id: id
            },
            success: function(response) {
                if (response.success) {
                    // Reload the table
                    loadVariants();
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while deleting the variant.');
            }
        });
    }

    // Add a new condition field to the form
    function addConditionField() {
        const container = $('#element-variants-conditions');
        const index = container.find('.element-variants-condition-row').length;
        
        const conditionRow = $(`
            <div class="element-variants-condition-row">
                <div class="element-variants-form-row">
                    <label for="condition_type_${index}">Condition Type</label>
                    <select name="condition_type[]" id="condition_type_${index}" class="condition-type">
                        <option value="user_role">User Role</option>
                        <option value="user_logged_in">User Logged In</option>
                        <option value="user_id">User ID</option>
                    </select>
                </div>
                <div class="element-variants-form-row condition-value-container">
                    <!-- Value field will be added dynamically -->
                </div>
                <button type="button" class="button element-variants-remove-condition">Remove Condition</button>
            </div>
        `);
        
        container.append(conditionRow);
        
        // Initialize the value field
        updateConditionValueField(conditionRow.find('.condition-type'));
    }

    // Update the condition value field based on the condition type
    function updateConditionValueField(select) {
        const type = select.val();
        const container = select.closest('.element-variants-condition-row').find('.condition-value-container');
        const index = select.closest('.element-variants-condition-row').index();
        
        container.empty();
        
        switch (type) {
            case 'user_role':
                container.html(`
                    <label for="condition_value_${index}">User Roles</label>
                    <select name="condition_value[]" id="condition_value_${index}" multiple>
                        <option value="administrator">Administrator</option>
                        <option value="editor">Editor</option>
                        <option value="author">Author</option>
                        <option value="contributor">Contributor</option>
                        <option value="subscriber">Subscriber</option>
                    </select>
                    <p class="element-variants-description">Select one or more user roles.</p>
                `);
                break;
                
            case 'user_logged_in':
                container.html(`
                    <label for="condition_value_${index}">User Must Be Logged In</label>
                    <select name="condition_value[]" id="condition_value_${index}">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                `);
                break;
                
            case 'user_id':
                container.html(`
                    <label for="condition_value_${index}">User ID</label>
                    <input type="number" name="condition_value[]" id="condition_value_${index}" value="" placeholder="User ID">
                    <p class="element-variants-description">Enter the WordPress user ID.</p>
                `);
                break;
                
            default:
                container.html(`
                    <label for="condition_value_${index}">Value</label>
                    <input type="text" name="condition_value[]" id="condition_value_${index}" value="">
                `);
        }
    }

    // Initialize when the document is ready
    $(document).ready(function() {
        initAdmin();
    });

})( jQuery ); 