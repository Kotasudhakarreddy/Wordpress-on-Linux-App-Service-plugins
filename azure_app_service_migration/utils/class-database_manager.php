<?php
// To Do: make this static
class AASM_Database_Manager {
    public function __construct() {
    }

    public function create_database($databaseName) {
        global $wpdb;
        $charsetCollate = $wpdb->get_charset_collate();
        $query = "CREATE DATABASE $databaseName $charsetCollate;";
        return $wpdb->query($query) !== false;
    }

    public function drop_database($databaseName) {
        global $wpdb;
        $query = "DROP DATABASE IF EXISTS $databaseName;";
        return $wpdb->query($query) !== false;
    }

    public function rename_database($oldName, $newName) {
        global $wpdb;
        $query = "ALTER DATABASE $oldName RENAME TO $newName;";
        return $wpdb->query($query) !== false;
    }

    public function run_query($databaseName, $query) {
        global $wpdb;
        $wpdb->select($databaseName);
        return $wpdb->query($query) !== false;
    }

    public function import_sql_file($databaseName, $sqlFilePath) {
        global $wpdb;
        $wpdb->select($databaseName);
        $sql = file_get_contents($sqlFilePath); // Read in entire file
        $wpdb->query($sql);
        return true;
    }

    public function database_exists($databaseName) {
        global $wpdb;
        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$databaseName';";
        $result = $wpdb->get_row($query);
        return !empty($result);
    }
}