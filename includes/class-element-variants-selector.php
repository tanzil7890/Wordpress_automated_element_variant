<?php
/**
 * Handles element selection in the frontend.
 */
class Element_Variants_Selector {

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Constructor
    }

    /**
     * Generate a unique CSS selector for an element.
     *
     * @param array $element_data Element data from the frontend.
     * @return string The CSS selector.
     */
    public function generate_selector($element_data) {
        $selector = '';

        // If element has an ID, use that
        if (!empty($element_data['id'])) {
            $selector = '#' . $element_data['id'];
            return $selector;
        }

        // If element has classes, use those
        if (!empty($element_data['classes']) && is_array($element_data['classes'])) {
            // Sort the classes to make the selector consistent
            sort($element_data['classes']);
            
            // Use up to 3 classes to avoid too specific selectors
            $classes = array_slice($element_data['classes'], 0, 3);
            $selector = '.' . implode('.', $classes);
            
            // If we have a tag name, add it
            if (!empty($element_data['tag'])) {
                $selector = $element_data['tag'] . $selector;
            }
            
            return $selector;
        }

        // If we just have a tag name, use that with an nth-child selector
        if (!empty($element_data['tag']) && !empty($element_data['position'])) {
            $selector = $element_data['tag'] . ':nth-child(' . $element_data['position'] . ')';
            
            // If we have parent data, add that
            if (!empty($element_data['parent'])) {
                $parent_selector = $this->generate_selector($element_data['parent']);
                $selector = $parent_selector . ' > ' . $selector;
            }
            
            return $selector;
        }

        // Fallback: use a data attribute if we can, or generate a unique selector
        if (!empty($element_data['attributes']) && is_array($element_data['attributes'])) {
            foreach ($element_data['attributes'] as $attr => $value) {
                if (strpos($attr, 'data-') === 0) {
                    $selector = '[' . $attr . '="' . $value . '"]';
                    return $selector;
                }
            }
        }

        // If we get here, generate a unique selector based on the element's path
        if (!empty($element_data['path']) && is_array($element_data['path'])) {
            $selector = $this->generate_path_selector($element_data['path']);
            return $selector;
        }

        // Last resort: return a selector that will likely only match this element
        return $element_data['tag'] . '[data-element-variants-target="' . uniqid() . '"]';
    }

    /**
     * Generate a selector based on the element's path.
     *
     * @param array $path The element's path in the DOM.
     * @return string The CSS selector.
     */
    private function generate_path_selector($path) {
        $selectors = array();
        
        // Start from the body and go down
        foreach ($path as $element) {
            $selector = '';
            
            // Use tag name
            $selector .= $element['tag'];
            
            // Add ID if available
            if (!empty($element['id'])) {
                $selector .= '#' . $element['id'];
            }
            // Add classes if available (limit to 2 for specificity)
            elseif (!empty($element['classes']) && is_array($element['classes'])) {
                sort($element['classes']);
                $classes = array_slice($element['classes'], 0, 2);
                $selector .= '.' . implode('.', $classes);
            }
            // Add position if needed
            elseif (!empty($element['position'])) {
                $selector .= ':nth-child(' . $element['position'] . ')';
            }
            
            $selectors[] = $selector;
        }
        
        return implode(' > ', $selectors);
    }

    /**
     * Validate if a selector is valid and specific enough.
     *
     * @param string $selector The CSS selector to validate.
     * @return bool Whether the selector is valid and specific.
     */
    public function validate_selector($selector) {
        // Check if the selector is empty
        if (empty($selector)) {
            return false;
        }
        
        // Check if the selector would select the whole document or too many elements
        $blacklist = array('html', 'body', '*', 'div', 'span', 'p', 'a', 'img', 'ul', 'ol', 'li');
        
        foreach ($blacklist as $banned) {
            if ($selector === $banned) {
                return false;
            }
        }
        
        // Ensure we're not selecting elements by common classes only
        $common_classes = array('container', 'row', 'col', 'section', 'header', 'footer', 'main', 'nav', 'sidebar', 'content');
        
        foreach ($common_classes as $class) {
            if ($selector === '.' . $class) {
                return false;
            }
        }
        
        // All checks passed
        return true;
    }
} 