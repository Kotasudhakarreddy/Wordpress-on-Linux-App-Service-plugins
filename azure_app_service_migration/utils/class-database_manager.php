<?php
// To Do: make this static
class AASM_Database_Manager {
    public function __construct() {
    }

    public function create_database($databaseName) {
        Azure_app_service_migration_Custom_Logger::logInfo(AASM_IMPORT_SERVICE_TYPE, 'Creating new database.', true);
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
        return $wpdb->get_results($query);
    }

    public function import_sql_file($databaseName, $sqlFilePath) {
        Azure_app_service_migration_Custom_Logger::logInfo(AASM_IMPORT_SERVICE_TYPE, 'Importing sql file ' . basename($sqlFilePath) . ' to new database', true);
    
        global $wpdb;
        $wpdb->select($databaseName);
    
        // Read in entire file
        $sqlContent = file_get_contents($sqlFilePath);
    
        // Execute the SQL statements using mysqli_multi_query
        if ($wpdb->dbh->multi_query($sqlContent)) {
            do {
                // Consume all results for the executed queries
                $wpdb->dbh->store_result();
            } while ($wpdb->dbh->more_results() && $wpdb->dbh->next_result());
        } else {
            // Query execution failed, and there is an error.
            echo "Database Error: " . $wpdb->dbh->error;
            return false;
        }
    
        return true;
    }

    public function database_exists($databaseName) {
        global $wpdb;
        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$databaseName';";
        $result = $wpdb->get_row($query);
        return !empty($result);
    }
}