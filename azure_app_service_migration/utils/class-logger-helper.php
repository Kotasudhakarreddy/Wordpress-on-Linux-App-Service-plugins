<?php
// class-logger-helper.php

class AASM_Logger_Helper
{ 
    public static function create_log_file($logFilePath)
    {
        if (!is_dir(AASM_LOG_FILE_LOCATION)) {
            mkdir(AASM_LOG_FILE_LOCATION, 0777, true);
            // Set appropriate permissions for the directory (0777 allows read, write, and execute permissions for everyone)
        }
        $file = fopen($logFilePath, 'ab');
        fclose($file);
    }

    public static function delete_log_file($logFilePath)
    {
        if (file_exists($logFilePath)) {
            unlink($logFilePath);
        }
    }
}