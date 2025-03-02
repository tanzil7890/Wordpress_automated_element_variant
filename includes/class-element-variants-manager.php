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
        if (!empty($conditions)) {
            foreach ($conditions as $condition) {
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
            $value = $condition->condition_value;

            // If the value is JSON, decode it
            if ($this->is_json($value)) {
                $value = json_decode($value, true);
            }

            $processed_conditions[] = array(
                'type' => $condition->condition_type,
                'value' => $value,
            );
        }

        return $processed_conditions;
    }

    /**
     * Check if a string is JSON.
     *
     * @param string $string The string to check.
     * @return bool Whether the string is JSON.
     */
    private function is_json($string) {
        json_decode($string);
        return (json_last_error() === JSON_ERROR_NONE);
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
} 