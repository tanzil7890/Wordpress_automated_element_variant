<?php
/**
 * Handles variant creation and management.
 */
class Element_Variants_Manager {

    /**
     * The database handler.
     *
     * @var Element_Variants_DB
     */
    private $db;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // Make sure the DB class is loaded
        if (!class_exists('Element_Variants_DB')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-element-variants-db.php';
        }
        $this->db = new Element_Variants_DB();
    }

    /**
     * Save a variant.
     *
     * @param string $selector CSS selector for the element.
     * @param string $content Content of the variant.
     * @param string $name Name of the variant.
     * @param array $conditions Conditions for when the variant should be applied.
     * @param int $id Optional. The variant ID to update. Default 0 (insert new).
     * @return int|false The variant ID on success, false on failure.
     */
    public function save_variant($selector, $content, $name, $conditions = array(), $id = 0) {
        // Process conditions before saving
        $processed_conditions = array();
        foreach ($conditions as $condition) {
            $processed_condition = array(
                'type' => $condition['type'],
                'value' => $condition['value']
            );
            
            // Process condition values based on type
            switch ($condition['type']) {
                case 'referrer_host':
                case 'referrer_url':
                    // If it's a string (from textarea), convert to array
                    if (is_string($condition['value'])) {
                        $processed_condition['value'] = $this->process_textarea_condition($condition['value']);
                    }
                    break;
                
                case 'user_logged_in':
                case 'from_url_shortener':
                    // Convert to boolean
                    $processed_condition['value'] = (bool) $condition['value'];
                    break;
                    
                case 'user_id':
                    // Convert to integer
                    $processed_condition['value'] = (int) $condition['value'];
                    break;
            }
            
            $processed_conditions[] = $processed_condition;
        }

        // Check if we're updating an existing variant
        if ($id > 0) {
            $result = $this->db->update_variant($id, array(
                'name' => $name,
                'selector' => $selector,
                'content' => $content,
            ));

            if (!$result) {
                return false;
            }

            // Delete existing conditions
            $this->db->delete_variant_conditions($id);
        } else {
            // Insert new variant
            $id = $this->db->insert_variant(array(
                'name' => $name,
                'selector' => $selector,
                'content' => $content,
            ));

            if (!$id) {
                return false;
            }
        }

        // Insert conditions
        if (!empty($processed_conditions)) {
            foreach ($processed_conditions as $condition) {
                $this->db->insert_condition(array(
                    'variant_id' => $id,
                    'condition_type' => $condition['type'],
                    'condition_value' => is_array($condition['value']) ? json_encode($condition['value']) : $condition['value'],
                ));
            }
        }

        return $id;
    }

    /**
     * Delete a variant.
     *
     * @param int $id Variant ID.
     * @return bool Whether the deletion was successful.
     */
    public function delete_variant($id) {
        return $this->db->delete_variant($id);
    }

    /**
     * Get a variant.
     *
     * @param int $id Variant ID.
     * @return object|false The variant on success, false on failure.
     */
    public function get_variant($id) {
        $variant = $this->db->get_variant($id);

        if (!$variant) {
            return false;
        }

        // Process conditions
        $variant->processed_conditions = $this->process_conditions($variant->conditions);

        return $variant;
    }

    /**
     * Get all variants.
     *
     * @return array The variants.
     */
    public function get_variants() {
        $variants = $this->db->get_variants();

        // Process conditions for each variant
        foreach ($variants as $variant) {
            $variant->processed_conditions = $this->process_conditions($variant->conditions);
        }

        return $variants;
    }

    /**
     * Get all variants that are applicable to the current context.
     *
     * @return array The applicable variants.
     */
    public function get_applicable_variants() {
        $variants = $this->get_variants();
        $applicable_variants = array();

        // Current user data
        $user_data = $this->get_current_user_data();

        // Check each variant
        foreach ($variants as $variant) {
            if ($this->is_variant_applicable($variant, $user_data)) {
                $applicable_variants[] = $variant;
            }
        }

        return $applicable_variants;
    }

    /**
     * Check if a variant is applicable to the current context.
     *
     * @param object $variant The variant to check.
     * @param array $user_data User data.
     * @return bool Whether the variant is applicable.
     */
    private function is_variant_applicable($variant, $user_data) {
        // If there are no conditions, the variant is always applicable
        if (empty($variant->conditions)) {
            return true;
        }

        // Check each condition
        foreach ($variant->processed_conditions as $condition) {
            switch ($condition['type']) {
                case 'user_role':
                    // If user is not logged in, skip this condition
                    if (!$user_data['logged_in']) {
                        return false;
                    }

                    // Check if user has any of the specified roles
                    $has_role = false;
                    foreach ($condition['value'] as $role) {
                        if (in_array($role, $user_data['roles'])) {
                            $has_role = true;
                            break;
                        }
                    }

                    if (!$has_role) {
                        return false;
                    }
                    break;

                case 'user_logged_in':
                    if ($condition['value'] !== $user_data['logged_in']) {
                        return false;
                    }
                    break;

                case 'user_id':
                    // If user is not logged in, skip this condition
                    if (!$user_data['logged_in']) {
                        return false;
                    }

                    if ((int) $condition['value'] !== $user_data['id']) {
                        return false;
                    }
                    break;
                    
                case 'referrer_host':
                    // Check if the referrer host matches one of the specified hosts
                    if (empty($user_data['referrer_host'])) {
                        return false;
                    }
                    
                    $referrer_match = false;
                    foreach ($condition['value'] as $host) {
                        if (strpos($user_data['referrer_host'], $host) !== false) {
                            $referrer_match = true;
                            break;
                        }
                    }
                    
                    if (!$referrer_match) {
                        return false;
                    }
                    break;
                    
                case 'referrer_url':
                    // Check if the referrer URL contains one of the specified strings
                    if (empty($user_data['referrer'])) {
                        return false;
                    }
                    
                    $referrer_match = false;
                    foreach ($condition['value'] as $url_part) {
                        if (strpos($user_data['referrer'], $url_part) !== false) {
                            $referrer_match = true;
                            break;
                        }
                    }
                    
                    if (!$referrer_match) {
                        return false;
                    }
                    break;
                    
                case 'from_url_shortener':
                    // Check if user is coming from a URL shortener
                    if ($condition['value'] !== $user_data['is_from_shortener']) {
                        return false;
                    }
                    break;

                // Add more condition types as needed
            }
        }

        // If all conditions passed, the variant is applicable
        return true;
    }

    /**
     * Process conditions for easier use.
     *
     * @param array $conditions Raw conditions from the database.
     * @return array Processed conditions.
     */
    private function process_conditions($conditions) {
        $processed_conditions = array();

        foreach ($conditions as $condition) {
            $processed_conditions[] = $this->process_condition($condition);
        }

        return $processed_conditions;
    }

    /**
     * Process a condition from the database format to a usable format.
     *
     * @param object $condition The condition from the database.
     * @return array The processed condition.
     */
    private function process_condition($condition) {
        $result = array(
            'type' => $condition->condition_type,
            'value' => $condition->condition_value,
        );
        
        // Process the value based on the condition type
        switch ($condition->condition_type) {
            case 'user_role':
            case 'referrer_host':
            case 'referrer_url':
                $result['value'] = json_decode($condition->condition_value, true);
                break;
                
            case 'user_logged_in':
            case 'from_url_shortener':
                $result['value'] = (bool) $condition->condition_value;
                break;
                
            case 'user_id':
                $result['value'] = (int) $condition->condition_value;
                break;
                
            default:
                // Keep as is
        }
        
        return $result;
    }
    
    /**
     * Process textarea input for condition values that accept multiple items.
     *
     * @param string $text Textarea input with items separated by newlines.
     * @return array Array of items.
     */
    private function process_textarea_condition($text) {
        // Split by newlines and trim whitespace
        $items = preg_split('/\r\n|\r|\n/', $text);
        $items = array_map('trim', $items);
        
        // Remove empty items
        $items = array_filter($items, function($item) {
            return $item !== '';
        });
        
        return array_values($items); // Reset array keys
    }

    /**
     * Get data about the current user.
     *
     * @return array User data.
     */
    private function get_current_user_data() {
        // Get referrer information
        $referrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $referrer_host = $referrer ? parse_url($referrer, PHP_URL_HOST) : '';
        $is_shortener = $this->is_from_url_shortener($referrer_host);
        
        // Common data for both logged in and logged out users
        $data = array(
            'logged_in' => is_user_logged_in(),
            'referrer' => $referrer,
            'referrer_host' => $referrer_host,
            'is_from_shortener' => $is_shortener,
        );
        
        // If no user is logged in, return just the common data
        if (!is_user_logged_in()) {
            return $data;
        }
        
        // Get the current user
        $user = wp_get_current_user();
        
        // Add user-specific data
        $data = array_merge($data, array(
            'id' => $user->ID,
            'roles' => $user->roles,
            'username' => $user->user_login,
            'email' => $user->user_email,
        ));
        
        return $data;
    }
    
    /**
     * Check if the referrer is a known URL shortener service.
     *
     * @param string $host The host to check.
     * @return bool Whether the host is a known URL shortener.
     */
    private function is_from_url_shortener($host) {
        if (empty($host)) {
            return false;
        }
        
        // List of common URL shorteners
        $shorteners = array(
            'bit.ly',
            'tinyurl.com',
            'goo.gl',
            'ow.ly',
            't.co',
            'is.gd',
            'buff.ly',
            'rebrand.ly',
            'cutt.ly',
            'tiny.cc',
            'shorturl.at',
            's.id',
            'adf.ly',
            // Add more as needed
        );
        
        foreach ($shorteners as $shortener) {
            if (strpos($host, $shortener) !== false) {
                return true;
            }
        }
        
        return false;
    }
} 