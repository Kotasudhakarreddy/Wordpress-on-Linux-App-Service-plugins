<?php

class AASM_Common_Utils {
   
    public static function generate_random_string_short() {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($characters), 0, 4);
    }

    // This function updates DB_NAME constant in wp-config.php file
    public static function update_dbname_wp_config($new_db_name) {
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

    public static function delete_file($filePath) {
        if (file_exists($filePath)) {
            if (unlink($filePath)) {
                echo "File deleted successfully.";
            } else {
                echo "Unable to delete the file.";
            }
        } else {
            echo "File does not exist.";
        }
    }

    
    public static function clear_directory_recursive($directoryPath) {
        // Retrieve list of files and directories in the directory
        $files = glob($directoryPath . '/*');
      
        // Iterate over each file or directory
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                // Recursively clear subdirectory
                clear_directory($file);
                // Remove empty subdirectory
                rmdir($file);
            }
        }
    }
}