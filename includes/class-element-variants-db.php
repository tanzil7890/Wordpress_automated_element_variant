<?php
/**
 * Database handler for the Element Variants plugin.
 */
class Element_Variants_DB {
    /**
     * The table name for variants.
     */
    private $variants_table;

    /**
     * The table name for conditions.
     */
    private $conditions_table;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        global $wpdb;
        $this->variants_table = $wpdb->prefix . 'element_variants';
        $this->conditions_table = $wpdb->prefix . 'element_variants_conditions';
    }

    /**
     * Create the database tables.
     */
    public function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $variants_table = "CREATE TABLE {$this->variants_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            selector VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        $conditions_table = "CREATE TABLE {$this->conditions_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            variant_id BIGINT(20) UNSIGNED NOT NULL,
            condition_type VARCHAR(50) NOT NULL,
            condition_value TEXT NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY variant_id (variant_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($variants_table);
        dbDelta($conditions_table);
    }

    /**
     * Insert a variant into the database.
     *
     * @param array $data Variant data.
     * @return int|false The variant ID on success, false on failure.
     */
    public function insert_variant($data) {
        global $wpdb;

        $result = $wpdb->insert(
            $this->variants_table,
            array(
                'name' => $data['name'],
                'selector' => $data['selector'],
                'content' => $data['content'],
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );

        if ($result === false) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Update a variant in the database.
     *
     * @param int $id Variant ID.
     * @param array $data Variant data.
     * @return bool Whether the update was successful.
     */
    public function update_variant($id, $data) {
        global $wpdb;

        $result = $wpdb->update(
            $this->variants_table,
            array(
                'name' => $data['name'],
                'selector' => $data['selector'],
                'content' => $data['content'],
                'updated_at' => current_time('mysql'),
            ),
            array('id' => $id),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Delete a variant from the database.
     *
     * @param int $id Variant ID.
     * @return bool Whether the deletion was successful.
     */
    public function delete_variant($id) {
        global $wpdb;

        // Delete related conditions first
        $wpdb->delete($this->conditions_table, array('variant_id' => $id), array('%d'));

        // Delete the variant
        $result = $wpdb->delete($this->variants_table, array('id' => $id), array('%d'));

        return $result !== false;
    }

    /**
     * Get a variant from the database.
     *
     * @param int $id Variant ID.
     * @return object|false The variant object on success, false on failure.
     */
    public function get_variant($id) {
        global $wpdb;

        $variant = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->variants_table} WHERE id = %d", $id)
        );

        if (!$variant) {
            return false;
        }

        // Get the variant's conditions
        $conditions = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$this->conditions_table} WHERE variant_id = %d", $id)
        );

        $variant->conditions = $conditions;

        return $variant;
    }

    /**
     * Get all variants from the database.
     *
     * @return array The variants.
     */
    public function get_variants() {
        global $wpdb;

        $variants = $wpdb->get_results("SELECT * FROM {$this->variants_table} ORDER BY id DESC");

        // Get the conditions for each variant
        foreach ($variants as $variant) {
            $conditions = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM {$this->conditions_table} WHERE variant_id = %d", $variant->id)
            );

            $variant->conditions = $conditions;
        }

        return $variants;
    }

    /**
     * Insert a condition into the database.
     *
     * @param array $data Condition data.
     * @return int|false The condition ID on success, false on failure.
     */
    public function insert_condition($data) {
        global $wpdb;

        $result = $wpdb->insert(
            $this->conditions_table,
            array(
                'variant_id' => $data['variant_id'],
                'condition_type' => $data['condition_type'],
                'condition_value' => $data['condition_value'],
                'created_at' => current_time('mysql'),
            ),
            array('%d', '%s', '%s', '%s')
        );

        if ($result === false) {
            return false;
        }

        return $wpdb->insert_id;
    }

    /**
     * Delete all conditions for a variant.
     *
     * @param int $variant_id Variant ID.
     * @return bool Whether the deletion was successful.
     */
    public function delete_variant_conditions($variant_id) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->conditions_table,
            array('variant_id' => $variant_id),
            array('%d')
        );

        return $result !== false;
    }
} 