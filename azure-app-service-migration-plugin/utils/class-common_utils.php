<?php

class AASM_Common_Utils {
   
    public static function generate_random_string_short() {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($characters), 0, 4);
    }

    // This function updates DB_NAME constant in wp-config.php file
    private function update_dbname_wp_config($new_db_name) {
        // Path to the wp-config.php file
        $config_file_path = ABSPATH . 'wp-config.php';
    
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
}