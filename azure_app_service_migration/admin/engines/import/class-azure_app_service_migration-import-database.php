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
        $this->new_database_name = $this->generate_unique_database_name($this->old_database_name, $this->database_manager);
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

        // extract database sql files into temporary directory
        $archive->extract_database_files(AASM_DATABASE_RELATIVE_PATH_IN_ZIP, $this->db_temp_dir);

        // create new database
        $this->database_manager->create_database($this->new_database_name);

        //Retrieve the 'siteurl' and 'home' values from the original database options table
        $originalDataToUpdate = $this->database_manager->get_originaldb_data();

        // Import each table sql file into the new database
        $this->import_db_sql_files();

        // update DB_NAME constant in wp-config
        $this->update_dbname_wp_config($this->new_database_name);

        if(!$this->database_manager->update_originaldb_data($this->new_database_name, $originalDataToUpdate))
        {
            Azure_app_service_migration_Custom_Logger::logError(AASM_IMPORT_SERVICE_TYPE, "Couldn't update required original DB values into imported database.");
        }

        // imports w3tc options from original DB to new DB
        if ( isset( $params['retain_w3tc_config'] ) && $params['retain_w3tc_config'] === true ) {
            $this->import_w3tc_options();
        }

        // Clean temporary directory to hold sql files
        Azure_app_service_migration_Custom_Logger::logInfo(AASM_IMPORT_SERVICE_TYPE, 'Clearing Database files placeholder directory.', true);
        AASM_Common_Utils::clear_directory_recursive($this->db_temp_dir);

        Azure_app_service_migration_Custom_Logger::logInfo(AASM_IMPORT_SERVICE_TYPE, 'Database import complete.', true);
    }

    // Imports all sql files in wp-database/ directory inside the import zip file
    private function import_db_sql_files() {
        if (!file_exists($this->db_temp_dir)) {
            mkdir($this->db_temp_dir, 0777, true);
        }

        Azure_app_service_migration_Custom_Logger::logInfo(AASM_IMPORT_SERVICE_TYPE, 'Importing Database tables and records.', true);
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
                    if (!$this->database_manager->import_sql_file($this->new_database_name, $filePath)) {
                        Azure_app_service_migration_Custom_Logger::logError(AASM_IMPORT_SERVICE_TYPE, "Couldn't import " . $filePath . " into database.");
                    }
                }
                else if (is_file($filePath) && str_ends_with($filePath, '.sql'))
                {
                    $table_records_files[] = $filePath;
                }
            }
        }

        Azure_app_service_migration_Custom_Logger::logInfo(AASM_IMPORT_SERVICE_TYPE, 'Finished importing Database tables. Importing database records...', true);
        // Import table records
        foreach ($table_records_files as $table_records) {
            if (!$this->database_manager->import_sql_file($this->new_database_name, $table_records)) {
                Azure_app_service_migration_Custom_Logger::logError(AASM_IMPORT_SERVICE_TYPE, "Couldn't import " . $table_records . " into database.");
            }
        }
    }

    // Imports W3 Total Cache settings in wp_options table in database
    private function import_w3tc_options() {
        Azure_app_service_migration_Custom_Logger::logInfo(AASM_IMPORT_SERVICE_TYPE, 'Importing W3 Total Cache options to the new database.', true);

        $sourceDatabase = $this->old_database_name;
        $destinationDatabase = $this->new_database_name;

        $sqlResult = $this->database_manager->run_query("SELECT option_id, option_name, option_value, autoload FROM $sourceDatabase.wp_options WHERE option_name LIKE '%w3tc%'");

        // Iterate over the result rows
        foreach ($sqlResult as $row) {
            $option_id = addslashes($row->option_id);
            $option_name = addslashes($row->option_name);
            $option_value = addslashes($row->option_value);
            $autoload = addslashes($row->autoload);

            // Update option in current database
            try {
                update_option($option_name, $option_value);
            } catch( Exception $ex) {
                Azure_app_service_migration_Custom_Logger::handleException($ex);
            }
        }
    }

    // This function updates DB_NAME constant in wp-config.php file
    public static function update_dbname_wp_config($new_db_name) {
        Azure_app_service_migration_Custom_Logger::logInfo(AASM_IMPORT_SERVICE_TYPE, 'Switching to new database.', true);
        // Path to the wp-config.php file
        $config_file_path = ABSPATH . 'wp-config.php';

        // To Do: Debug the commented method and replace with the following code
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
        
        // Adds AASM_MIGRATION_STATUS option to new database in addition to logging
        Azure_app_service_migration_Custom_Logger::logInfo(AASM_IMPORT_SERVICE_TYPE, 'Updated Database name in wp-config.', true);
    }

    // Generates a unique database name. Retries 5 times
    private function generate_unique_database_name($current_dbname, $database_manager) {
        Azure_app_service_migration_Custom_Logger::logInfo(AASM_IMPORT_SERVICE_TYPE, 'Generating new database name.', true);
        $dbname_suffix = substr($current_dbname, 0, min(strlen($current_dbname), 10));

        for ($trycount = 0; $trycount < 5; $trycount++) {
            $new_dbname = $dbname_suffix . '_aasm_db_' . AASM_Common_Utils::generate_random_string_short();
            
            if (!($this->database_manager->database_exists($new_dbname)))
                return $new_dbname;
        }

        // To Do: Handle error here
        return $dbname_suffix;
    }
}