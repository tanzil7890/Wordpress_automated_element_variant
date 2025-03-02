/**
 * Element Variants Editor - Frontend JavaScript
 * 
 * This script handles the frontend element selection and variant creation/editing.
 */
(function( $ ) {
    'use strict';

    // Store the state of the editor
    const state = {
        active: false,
        selecting: false,
        editing: false,
        selectedElement: null,
        selectedSelector: '',
        currentVariant: null,
        hoveredElement: null
    };

    // Initialize the editor
    function initEditor() {
        createEditorUI();
        bindEvents();
    }

    // Create the editor UI
    function createEditorUI() {
        // Create the main editor container
        const editorContainer = $('<div id="element-variants-editor"></div>');
        
        // Create the toolbar
        const toolbar = $(`
            <div class="element-variants-toolbar">
                <span class="element-variants-title">Element Variants Editor</span>
                <button id="element-variants-select-btn" class="element-variants-btn">
                    ${element_variants_editor.i18n.select_element}
                </button>
                <button id="element-variants-close-btn" class="element-variants-btn element-variants-btn-danger">
                    ✕ Close
                </button>
            </div>
        `);
        
        // Create the editor panel (hidden initially)
        const editorPanel = $(`
            <div id="element-variants-panel" style="display: none;">
                <h3>${element_variants_editor.i18n.create_variant}</h3>
                <form id="element-variants-form">
                    <div class="element-variants-form-group">
                        <label for="element-variants-name">${element_variants_editor.i18n.variant_name}</label>
                        <input type="text" id="element-variants-name" name="name" required>
                    </div>
                    <div class="element-variants-form-group">
                        <label>Selected Element</label>
                        <div id="element-variants-selector" class="element-variants-code"></div>
                    </div>
                    <div class="element-variants-form-group">
                        <label>Content</label>
                        <textarea id="element-variants-content" name="content" rows="6" required></textarea>
                    </div>
                    <div class="element-variants-form-group">
                        <label>${element_variants_editor.i18n.conditions}</label>
                        <div class="element-variants-conditions">
                            <div class="element-variants-condition">
                                <select name="condition_type" class="condition-type">
                                    <option value="user_role">User Role</option>
                                    <option value="user_logged_in">User Logged In</option>
                                    <option value="user_id">User ID</option>
                                </select>
                                <div class="condition-value-container"></div>
                                <button type="button" class="element-variants-btn element-variants-btn-sm element-variants-btn-danger remove-condition">
                                    ✕
                                </button>
                            </div>
                            <button type="button" id="add-condition" class="element-variants-btn element-variants-btn-sm">
                                + Add Condition
                            </button>
                        </div>
                    </div>
                    <div class="element-variants-form-actions">
                        <button type="button" id="element-variants-cancel" class="element-variants-btn">
                            ${element_variants_editor.i18n.cancel}
                        </button>
                        <button type="submit" class="element-variants-btn element-variants-btn-primary">
                            ${element_variants_editor.i18n.save}
                        </button>
                    </div>
                </form>
            </div>
        `);
        
        // Add the UI to the page
        editorContainer.append(toolbar);
        editorContainer.append(editorPanel);
        $('body').append(editorContainer);
        
        // Initialize condition value UI
        updateConditionValueUI($('.condition-type'));
    }

    // Bind events
    function bindEvents() {
        // Toggle element selection mode
        $('#element-variants-select-btn').on('click', function() {
            state.selecting = !state.selecting;
            
            if (state.selecting) {
                $(this).addClass('element-variants-btn-active');
                $('body').addClass('element-variants-selecting');
                enableSelectionMode();
            } else {
                $(this).removeClass('element-variants-btn-active');
                $('body').removeClass('element-variants-selecting');
                disableSelectionMode();
            }
        });
        
        // Close editor
        $('#element-variants-close-btn').on('click', function() {
            // Navigate away from the editor mode
            window.location.href = window.location.href.replace(/[?&]element_variants_editor=true/, '');
        });
        
        // Cancel variant editing
        $('#element-variants-cancel').on('click', function() {
            hideEditorPanel();
            state.selectedElement = null;
            state.selectedSelector = '';
            state.editing = false;
        });
        
        // Form submission
        $('#element-variants-form').on('submit', function(e) {
            e.preventDefault();
            saveVariant();
        });
        
        // Add condition
        $('#add-condition').on('click', function() {
            addCondition();
        });
        
        // Handle condition type change
        $(document).on('change', '.condition-type', function() {
            updateConditionValueUI($(this));
        });
        
        // Handle condition removal
        $(document).on('click', '.remove-condition', function() {
            $(this).closest('.element-variants-condition').remove();
        });
    }

    // Enable element selection mode
    function enableSelectionMode() {
        // Add hover effect to all elements
        $('body').on('mouseover', '*', function(e) {
            if (state.selecting && !$(this).closest('#element-variants-editor').length) {
                e.stopPropagation();
                $(this).addClass('element-variants-hover');
                state.hoveredElement = $(this);
            }
        }).on('mouseout', '*', function() {
            $(this).removeClass('element-variants-hover');
        });
        
        // Handle element click for selection
        $('body').on('click', '*', function(e) {
            if (state.selecting && !$(this).closest('#element-variants-editor').length) {
                e.preventDefault();
                e.stopPropagation();
                
                state.selectedElement = $(this);
                state.selectedSelector = generateSelector($(this));
                
                // Show the editor panel
                showEditorPanel();
                
                // Disable selection mode
                state.selecting = false;
                $('#element-variants-select-btn').removeClass('element-variants-btn-active');
                $('body').removeClass('element-variants-selecting');
                disableSelectionMode();
            }
        });
    }

    // Disable element selection mode
    function disableSelectionMode() {
        $('body').off('mouseover', '*').off('mouseout', '*').off('click', '*');
        $('.element-variants-hover').removeClass('element-variants-hover');
    }

    // Show the editor panel
    function showEditorPanel() {
        // Set the selected element info
        $('#element-variants-selector').text(state.selectedSelector);
        
        // Set content from the selected element
        $('#element-variants-content').val(state.selectedElement.html());
        
        // Show the panel
        $('#element-variants-panel').show();
        
        // Highlight the selected element
        $('body').find('.element-variants-selected').removeClass('element-variants-selected');
        state.selectedElement.addClass('element-variants-selected');
    }

    // Hide the editor panel
    function hideEditorPanel() {
        $('#element-variants-panel').hide();
        $('body').find('.element-variants-selected').removeClass('element-variants-selected');
        
        // Reset form
        $('#element-variants-form')[0].reset();
        // Keep only one condition
        $('.element-variants-condition:not(:first)').remove();
    }

    // Generate a CSS selector for an element
    function generateSelector(element) {
        const elementData = {
            tag: element.prop('tagName').toLowerCase(),
            id: element.attr('id'),
            classes: element.attr('class') ? element.attr('class').split(/\s+/).filter(function(c) {
                return c && !c.startsWith('element-variants-');
            }) : [],
            attributes: {},
            position: element.index() + 1
        };
        
        // Get attributes
        $.each(element[0].attributes, function() {
            if (this.name !== 'class' && this.name !== 'id') {
                elementData.attributes[this.name] = this.value;
            }
        });
        
        // Build selector
        let selector = '';
        
        // If element has an ID, use that
        if (elementData.id) {
            selector = '#' + elementData.id;
            return selector;
        }
        
        // If element has classes, use those
        if (elementData.classes.length > 0) {
            // Use up to 3 classes
            const classes = elementData.classes.slice(0, 3);
            selector = '.' + classes.join('.');
            
            if (elementData.tag) {
                selector = elementData.tag + selector;
            }
            
            return selector;
        }
        
        // If we just have a tag name, try to be more specific
        if (elementData.tag) {
            // Try to include parent information
            const parent = element.parent();
            if (parent.length && parent[0] !== document) {
                const parentId = parent.attr('id');
                if (parentId) {
                    return `#${parentId} > ${elementData.tag}:nth-child(${elementData.position})`;
                }
                
                const parentClass = parent.attr('class');
                if (parentClass) {
                    const parentClasses = parentClass.split(/\s+/).filter(function(c) {
                        return c && !c.startsWith('element-variants-');
                    });
                    
                    if (parentClasses.length > 0) {
                        return `.${parentClasses[0]} > ${elementData.tag}:nth-child(${elementData.position})`;
                    }
                }
            }
            
            // Fallback to simple tag and position
            return `${elementData.tag}:nth-child(${elementData.position})`;
        }
        
        // Last resort: create a unique identifier
        return '*[data-element-variants-id="' + Math.random().toString(36).substring(2, 15) + '"]';
    }

    // Save a variant
    function saveVariant() {
        const name = $('#element-variants-name').val();
        const content = $('#element-variants-content').val();
        const selector = state.selectedSelector;
        
        // Collect conditions
        const conditions = [];
        $('.element-variants-condition').each(function() {
            const type = $(this).find('.condition-type').val();
            let value;
            
            const valueContainer = $(this).find('.condition-value-container');
            
            // Get the value based on the condition type
            switch (type) {
                case 'user_role':
                    // Multi-select for roles
                    value = valueContainer.find('select').val() || [];
                    break;
                    
                case 'user_logged_in':
                case 'from_url_shortener':
                    // Boolean values
                    value = valueContainer.find('select').val() === '1';
                    break;
                    
                case 'user_id':
                    // Numeric value
                    value = parseInt(valueContainer.find('input').val(), 10);
                    break;
                    
                case 'referrer_host':
                case 'referrer_url':
                    // Split textarea content into array
                    const text = valueContainer.find('textarea').val();
                    value = text.split('\n')
                        .map(line => line.trim())
                        .filter(line => line !== '');
                    break;
                    
                default:
                    value = valueContainer.find('input').val();
            }
            
            conditions.push({
                type: type,
                value: value
            });
        });
        
        // Send the data to the server
        $.ajax({
            url: element_variants_editor.ajax_url,
            type: 'POST',
            data: {
                action: 'save_element_variant',
                nonce: element_variants_editor.nonce,
                name: name,
                selector: selector,
                content: content,
                conditions: JSON.stringify(conditions)
            },
            success: function(response) {
                if (response.success) {
                    alert('Variant saved successfully!');
                    hideEditorPanel();
                    disableSelectionMode();
                    state.selectedElement = null;
                    state.selectedSelector = '';
                } else {
                    alert('Error saving variant: ' + response.data);
                }
            },
            error: function() {
                alert('Error saving variant. Please try again.');
            }
        });
    }

    // Add a new condition row
    function addCondition() {
        const condition = $(`
            <div class="element-variants-condition">
                <select name="condition_type" class="condition-type">
                    <option value="user_role">User Role</option>
                    <option value="user_logged_in">User Logged In</option>
                    <option value="user_id">User ID</option>
                    <option value="referrer_host">Referrer Host</option>
                    <option value="referrer_url">Referrer URL</option>
                    <option value="from_url_shortener">From URL Shortener</option>
                </select>
                <div class="condition-value-container"></div>
                <button type="button" class="element-variants-btn element-variants-btn-sm element-variants-btn-danger remove-condition">
                    ✕
                </button>
            </div>
        `);
        
        // Insert before the add button
        $('#add-condition').before(condition);
        
        // Initialize the value UI
        updateConditionValueUI(condition.find('.condition-type'));
    }

    // Update the condition value UI based on the condition type
    function updateConditionValueUI(select) {
        const type = select.val();
        const container = select.siblings('.condition-value-container');
        
        container.empty();
        
        switch (type) {
            case 'user_role':
                // Multi-select for user roles
                container.html(`
                    <select name="condition_value" multiple>
                        <option value="administrator">Administrator</option>
                        <option value="editor">Editor</option>
                        <option value="author">Author</option>
                        <option value="contributor">Contributor</option>
                        <option value="subscriber">Subscriber</option>
                    </select>
                `);
                break;
                
            case 'user_logged_in':
            case 'from_url_shortener':
                // Dropdown for boolean values
                container.html(`
                    <select name="condition_value">
                        <option value="1">${type === 'user_logged_in' ? 'Yes' : 'Coming from URL Shortener'}</option>
                        <option value="0">${type === 'user_logged_in' ? 'No' : 'Not from URL Shortener'}</option>
                    </select>
                `);
                break;
                
            case 'user_id':
                // Number input for user ID
                container.html(`
                    <input type="number" name="condition_value" placeholder="Enter user ID">
                `);
                break;
                
            case 'referrer_host':
            case 'referrer_url':
                // Textarea for multiple values
                container.html(`
                    <textarea name="condition_value" rows="3" placeholder="${
                        type === 'referrer_host' 
                            ? 'Enter domain names (one per line):\nexample.com\nanother-site.com' 
                            : 'Enter URL parts (one per line):\nexample.com/page\n?utm_source=newsletter'
                    }"></textarea>
                    <p class="element-variants-help-text">${
                        type === 'referrer_host'
                            ? 'Enter domain names that users should be coming from.'
                            : 'Enter URL parts that should be present in the referrer URL.'
                    }</p>
                `);
                break;
                
            default:
                container.html(`
                    <input type="text" name="condition_value" placeholder="Enter value">
                `);
        }
    }

    // Initialize the editor when the document is ready
    $(document).ready(function() {
        initEditor();
    });

})( jQuery ); 