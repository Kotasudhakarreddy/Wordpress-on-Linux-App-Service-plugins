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
        
        // Temporary variable, used to store current query
        $templine = '';
        
        // Read in entire file
        $lines = file($sqlFilePath);
        
        // Loop through each line
        foreach ($lines as $index => $line)
        {
            // Add this line to the current segment
            $templine .= $line;

            if (str_ends_with($line, AASM_DB_RECORDS_QUERY_SEPARATOR . PHP_EOL) || $index === array_key_last($lines))
            {
                // remove the query separator
                $templine = str_ends_with($templine, AASM_DB_RECORDS_QUERY_SEPARATOR . PHP_EOL) 
                            ? substr($templine, 0, - (strlen(AASM_DB_RECORDS_QUERY_SEPARATOR) +1))
                            : $templine;

                // Perform the query
                if ($wpdb->query($templine) == false) {
                    return false;
                }

                // Reset temp variable to empty
                $templine = '';
            }
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