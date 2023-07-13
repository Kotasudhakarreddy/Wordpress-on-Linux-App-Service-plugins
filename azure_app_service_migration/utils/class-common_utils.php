<?php

class AASM_Common_Utils {
   
    public static function generate_random_string_short() {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($characters), 0, 4);
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

    public static function replace_forward_slash_with_directory_separator ( $dir ) {
        return str_replace("/", DIRECTORY_SEPARATOR, $dir);
    }
}