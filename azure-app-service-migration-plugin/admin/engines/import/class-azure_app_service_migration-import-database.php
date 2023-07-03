<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'ABSPATH undefined.' );
}

class Azure_app_service_migration_Import_Database {

    private $import_zip_path = null;
    private $params = null;
    private $new_database_name;
    private $database_manager;
    private $db_temp_dir;

    public function __construct($import_zip_path, $params) {
        // Path to the uploaded import zip file
        $this->import_zip_path = ($import_zip_path === null) 
                                ? AASM_IMPORT_ZIP_PATH
                                : $import_zip_path; 
        $this->params = $params;

        // Temporary directory for extracting sql files 
        $this->db_temp_dir = AASM_DATABASE_TEMP_DIR;

        global $wpdb;
        $hostname = $wpdb->dbhost;
        $username = $wpdb->dbuser;
        $password = $wpdb->dbpassword;
        $dbname   = $wpdb->$dbname;

        $this->database_manager = new AASM_Database_Manager($hostname, $username, $password);
        $this->new_database_name = $this->generate_database_name($dbname, $this->database_manager);
    }

    public function import_database()
    {
        // Flag to hold if file data has been processed
		$completed = true;

		// Start time
		$start = microtime( true );

		// create extractor object for import zip file
		$archive = new AASM_Zip_Extractor( $this->import_zip_path );

        // extract database sql files into temporary directory
        $archive->extract_database_files(AASM_DATABASE_RELATIVE_PATH_IN_ZIP, $this->db_temp_dir);

        // create new database
        $database_manager->create_database($this->new_database_name);

        // Import each table sql file into the new database
        $this->import_db_sql_files($this->db_temp_dir);

        // update DB_NAME constant in wp-config
        AASM_Common_Utils::update_dbname_wp_config($this->new_database_name);
        
    }

    private function import_db_sql_files() {
        if (!file_exists($this->db_temp_dir)) {
            mkdir($this->db_temp_dir, 0777);
        }

        $files = scandir($this->db_temp_dir);

        // import each table (stored as sql file)
        foreach ($files as $file) {

            // Exclude current directory (.) and parent directory (..)
            if ($file != '.' && $file != '..') {
                $filePath = $this->db_temp_dir . '/' . $file;

                // Check if the path is a file
                if (is_file($filePath) && str_ends_with($filepath, '.sql')) {
                    $database_manager->import_sql_file($this->new_database_name, $sql_file_path);
                }
            }
        }
    }

    // Generates a unique database name. Retries 5 times
    private function generate_database_name($current_dbname, $database_manager) {
        $new_dbname = 'aasm_db';

        for ($trycount = 0; $trycount < 5; $trycount++) {
            $new_dbname = 'aasm_db' . AASM_Common_Utils::generate_random_string_short();
            
            if (!($this->database_manager->database_exists($new_dbname)))
                return $new_dbname;
        }

        return $new_dbname;
    }
}