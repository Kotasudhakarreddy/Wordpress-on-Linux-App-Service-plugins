<?php
// CustomLogger.php

class Azure_app_service_migration_Custom_Logger
{
    // Initialize the custom logging functionality
    public static function init()
    {
        // Hook the log_user_registration function to the user_register action
        add_action('user_register', array('Azure_app_service_migration_Custom_Logger', 'log_user_registration'), 10, 1);

        // Set up error and exception handling
        set_error_handler(array('Azure_app_service_migration_Custom_Logger', 'handleError'));
        set_exception_handler(array('Azure_app_service_migration_Custom_Logger', 'handleException'));
    }

    // Clear Log file
    public static function reset_log_file()
    {
        $log_file = AASM_MIGRATION_LOGFILE_PATH;
        file_put_contents($log_file, 'Azure App Service Migration Logs' . PHP_EOL . PHP_EOL);
    }

    // Write log messages to the custom log file
    // parameters: service_type = {IMPORT/EXPORT}
    public static function writeToLog($status, $message = '', $service_type = '')
    {
        // Define the log file path and name
        $log_file = AASM_MIGRATION_LOGFILE_PATH;
        // Get the current date and time
        $current_time = date('Y-m-d H:i:s');

        // Format the log message
        $log_message = "[{$current_time}] {$service_type} {$status} {$message}" . PHP_EOL;

        // Append the log message to the log file
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }

    // Log the user registration event
    public static function log_user_registration($user_id)
    {
        $username = get_user_by('id', $user_id)->user_login;
        $message = "User '{$username}' registered successfully.";
        self::writeToLog($message);
    }

    // Custom error handler
    public static function handleError($severity, $message)
    {
        // Get the current date and time
        $current_time = date('Y-m-d H:i:s');
        $error_message = "Error [{$current_time}]: {$severity} {$message}";
        self::writeToLog($error_message);
        
        // Update AASM_MIGRATION_STATUS option in Database
        $migration_status = array( 'type' => 'error', 'title' => $severity, 'message' => $message );
        self::update_migration_status($migration_status);
    }

    // Custom error handler
    public static function logInfo($service_type, $message)
    {
        // Get the current date and time
        $current_time = date('Y-m-d H:i:s');
        $info_message = "AASM_LOG [{$current_time}]: {$service_type} {$message}";
        self::writeToLog($info_message);
        
        // Update AASM_MIGRATION_STATUS option in Database
        $migration_status = array( 'type' => 'Status', 'title' => $service_type, 'message' => $message );
        self::update_migration_status($migration_status);
    }

    // Custom exception handler
    public static function handleException($exception)
    {
        // Get the exception details
        $message = 'Exception: ' . $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();

        // Build the log message with details
        $log_message = "Exception:\n";
        $log_message .= "Message: {$message}\n";
        $log_message .= "File: {$file}\n";
        $log_message .= "Line: {$line}\n";
        $log_message .= "Trace:\n{$trace}";

        // Log the exception details
        self::writeToLog($log_message);

        // Update AASM_MIGRATION_STATUS option in Database
        $migration_status = array( 'type' => 'error', 'title' => 'exception', 'message' => $message . ' in file ' . $file . ' at line ' . $line );
        self::update_migration_status($migration_status);
    }

    private static function update_migration_status($data)
    {
        update_option( AASM_MIGRATION_STATUS, $data );
    }
}

// Initialize the custom logger
Azure_app_service_migration_Custom_Logger::init();
