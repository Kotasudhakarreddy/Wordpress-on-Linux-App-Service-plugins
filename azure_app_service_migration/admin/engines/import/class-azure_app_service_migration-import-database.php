<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'ABSPATH undefined.' );
}

class Azure_app_service_migration_Import_Database {

    private $import_zip_path = null;
    private $params = null;
    private $new_database_name;
    private $old_database_name;
    private $database_manager;
    private $db_temp_dir;

    public function __construct($import_zip_path, $params) {
        global $wpdb;
        $hostname = $wpdb->dbhost;
        $username = $wpdb->dbuser;
        $password = $wpdb->dbpassword;

        $this->database_manager = new AASM_Database_Manager();
        $this->old_database_name = $wpdb->$dbname;
        $this->new_database_name = $this->generate_unique_database_name($dbname, $this->database_manager);
        $this->params = $params;
        $this->db_temp_dir = AASM_DATABASE_TEMP_DIR;            // Temporary directory for extracting sql files
        $this->import_zip_path = ($import_zip_path === null)    // Path to the uploaded import zip file
                                ? AASM_IMPORT_ZIP_LOCATION
                                : $import_zip_path;
    }

    public function import_database()
    {
        // Flag to hold if file data has been processed
		$completed = true;

		// create extractor object for import zip file
		$archive = new AASM_Zip_Extractor( $this->import_zip_path );

        // Clean temporary directory to hold sql files
        AASM_Common_Utils::clear_directory_recursive($this->db_temp_dir);

        // extract database sql files into temporary directory
        $archive->extract_database_files(AASM_DATABASE_RELATIVE_PATH_IN_ZIP, $this->db_temp_dir);

        // create new database
        $this->database_manager->create_database($this->new_database_name);

        // Import each table sql file into the new database
        $this->import_db_sql_files();

        // update DB_NAME constant in wp-config
        $this->update_dbname_wp_config($this->new_database_name);
        
        // imports w3tc options from original DB to new DB
        if ( isset( $params['retain_w3tc_config'] ) && $params['retain_w3tc_config'] === true ) {
            $this->import_w3tc_options();
        }
    }

    // Imports all sql files in wp-database/ directory inside the import zip file
    private function import_db_sql_files() {
        if (!file_exists($this->db_temp_dir)) {
            mkdir($this->db_temp_dir, 0777, true);
        }

        $files = scandir($this->db_temp_dir);
        $table_records_files = [];
        
        // import table structure and keep track of table records to be imported later
        foreach ($files as $file) {
            // reset time counter to prevent timeout
            set_time_limit(0);
            // Exclude current directory (.) and parent directory (..)
            if ($file != '.' && $file != '..') {
                $filePath = $this->db_temp_dir . $file;

                // Check if the path is a file
                if (is_file($filePath) && str_ends_with($filePath, 'structure.sql')) {
                    $this->database_manager->import_sql_file($this->new_database_name, $filePath);
                }
                else if (is_file($filePath) && strpos($filePath, 'records_batch' !== false))
                {
                    $table_records_files[] = $filePath;
                }
            }
        }
        
        // Import table records
        foreach ($table_records_files as $table_records)
        {
            $this->database_manager->import_sql_file($this->new_database_name, $table_records);
        }
    }

    // Imports W3 Total Cache settings in wp_options table in database
    private function import_w3tc_options() {
        $sourceDatabase = $this->old_database_name;
        $destinationDatabase = $this->new_database_name;

        $sqlResult = $conn->query("SELECT option_id, option_name, option_value, autoload FROM $sourceDatabase.wp_options WHERE option_name LIKE '%w3tc%'");

        // Generate the import query
        $importQuery = generate_w3tc_import_query($destinationDatabase, $sqlResult);

        // Run the import query on the destination database
        $this->database_manager->run_query($destinationDatabase, $importQuery);
    }

    public function generate_w3tc_import_query($databaseName, $w3tc_options) {
        // Start building the SQL query
        $importQuery = "INSERT INTO $databaseName.wp_options (option_id, option_name, option_value, autoload) VALUES";
    
        // Iterate over the result rows
        foreach ($w3tc_options as $row) {
            $option_id = addslashes($row['option_id']);
            $option_name = addslashes($row['option_name']);
            $option_value = addslashes($row['option_value']);
            $autoload = addslashes($row['autoload']);
    
            // Add each row values to the query
            $importQuery .= " ('$option_id', '$option_name', '$option_value', '$autoload'),";
        }
    
        // Remove the trailing comma
        $importQuery = rtrim($importQuery, ',');

        $importQuery .= " ON DUPLICATE KEY UPDATE option_value = VALUES(option_value)";
    
        return $importQuery;
    }

    // This function updates DB_NAME constant in wp-config.php file
    public static function update_dbname_wp_config($new_db_name) {
        // Path to the wp-config.php file
        $config_file_path = ABSPATH . 'wp-config.php';
        
        // To Do: Debug the commented method
        // swap database names
        //$temp_database_name = $this->generate_unique_database_name();
        //$this->database_manager->rename_database($this->old_database_name, $temp_database_name);
        //$this->database_manager->rename_database($this->new_database_name, $this->old_database_name);
        // Read the contents of the wp-config.php file

        $config_file_contents = file_get_contents($config_file_path);
    
        // Replace the existing database_name value with the new one
        $updated_file_contents = preg_replace(
            "/define\(\'DB_NAME\', (.*)\);/",
            "define('DB_NAME', '" . $new_db_name . "');",
            $config_file_contents
        );
    
        // Write the updated contents back to the wp-config.php file
        file_put_contents($config_file_path, $updated_file_contents);
    }

    // Generates a unique database name. Retries 5 times
    private function generate_unique_database_name($current_dbname, $database_manager) {
        $new_dbname = 'aasm_db';

        for ($trycount = 0; $trycount < 5; $trycount++) {
            $new_dbname = 'aasm_db' . AASM_Common_Utils::generate_random_string_short();
            
            if (!($this->database_manager->database_exists($new_dbname)))
                return $new_dbname;
        }

        return $new_dbname;
    }
}