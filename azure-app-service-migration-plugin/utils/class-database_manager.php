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

        // Temporary variable, used to store current query
        $templine = '';
        // Read in entire file
        $lines = file($sqlFilePath);
        // Loop through each line
        
        foreach ($lines as $index => $line)
        {
            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || $line == '')
                continue;

            // Add this line to the current segment
            $templine .= $line;
            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';' || $index === array_key_last($lines))
            {
                // Perform the query
                $wpdb->query($templine);
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