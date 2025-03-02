/**
 * Element Variants - Public JavaScript
 *
 * This script applies variants to matching elements on the page.
 */
(function( $ ) {
    'use strict';

    // Initialize the variants application
    function initVariants() {
        // Check if we have variants to apply
        if (typeof element_variants_public !== 'undefined' && element_variants_public.variants) {
            applyVariants(element_variants_public.variants);
        }
    }

    // Apply variants to matching elements
    function applyVariants(variants) {
        // Process each variant
        $.each(variants, function(index, variant) {
            // Check if the variant is applicable
            if (isVariantApplicable(variant)) {
                // Find matching elements
                const elements = $(variant.selector);
                
                if (elements.length > 0) {
                    // Apply the variant to each matching element
                    elements.each(function() {
                        // Store the original content for toggling if needed
                        if (!$(this).data('original-content')) {
                            $(this).data('original-content', $(this).html());
                        }
                        
                        // Apply the variant content
                        $(this).html(variant.content);
                        
                        // Mark the element as having a variant
                        $(this).addClass('element-variants-applied');
                        $(this).attr('data-variant-id', variant.id);
                    });
                }
            }
        });
    }

    // Check if a variant is applicable to the current user
    function isVariantApplicable(variant) {
        // If the variant has no conditions, it's always applicable
        if (!variant.processed_conditions || variant.processed_conditions.length === 0) {
            return true;
        }
        
        // Get the current user data
        const userData = element_variants_public.current_user;
        
        // Check each condition
        for (let i = 0; i < variant.processed_conditions.length; i++) {
            const condition = variant.processed_conditions[i];
            
            switch (condition.type) {
                case 'user_role':
                    // If user is not logged in, this condition fails
                    if (!userData.logged_in) {
                        return false;
                    }
                    
                    // Check if the user has any of the required roles
                    let hasRole = false;
                    for (let j = 0; j < condition.value.length; j++) {
                        if (userData.roles.includes(condition.value[j])) {
                            hasRole = true;
                            break;
                        }
                    }
                    
                    if (!hasRole) {
                        return false;
                    }
                    break;
                    
                case 'user_logged_in':
                    if (userData.logged_in !== condition.value) {
                        return false;
                    }
                    break;
                    
                case 'user_id':
                    // If user is not logged in, this condition fails
                    if (!userData.logged_in) {
                        return false;
                    }
                    
                    if (parseInt(userData.id) !== parseInt(condition.value)) {
                        return false;
                    }
                    break;
                    
                // Add more condition types as needed
            }
        }
        
        // If all conditions passed, the variant is applicable
        return true;
    }

    // Initialize when the document is ready
    $(document).ready(function() {
        // Initialize the variants application
        initVariants();
        
        // Handle dynamic content loading if needed
        $(document).on('element_variants_content_loaded', function() {
            initVariants();
        });
    });

})( jQuery ); 