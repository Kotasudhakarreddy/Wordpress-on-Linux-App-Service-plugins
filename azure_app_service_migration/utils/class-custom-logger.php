<?php
// CustomLogger.php

class Azure_app_service_migration_Custom_Logger
{   

    // Handle uncaught exceptions and log them
    public static function handleUncaughtException($exception)
    {
        // Get the exception details
        $message = 'Uncaught Exception: ' . $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();

        // Build the log message with details
        $log_message = "Uncaught Exception:\n";
        $log_message .= "Message: {$message}\n";
        $log_message .= "File: {$file}\n";
        $log_message .= "Line: {$line}\n";
        $log_message .= "Trace:\n{$trace}";

        // Log the exception details
        self::writeToLog($log_message);
    }

    // Write log messages to the custom log file
    public static function writeToLog($message)
    {
        // Define the log file path and name
        $log_file = WP_PLUGIN_DIR .'/azure_app_service_migration' . '/azure_app_service_migration-plugin-log.txt';
        // Get the current date and time
        $current_time = date('Y-m-d H:i:s');

        // Format the log message
        $log_message = "[{$current_time}] {$message}" . PHP_EOL;

        // Append the log message to the log file
        file_put_contents($log_file, $log_message, FILE_APPEND);
    }

    // Initialize the custom logging functionality
    public static function init()
    {
        // Hook the log_user_registration function to the user_register action
        add_action('user_register', array('Azure_app_service_migration_Custom_Logger', 'log_user_registration'), 10, 1);

        // Set up error and exception handling
        set_error_handler(array('Azure_app_service_migration_Custom_Logger', 'handleError'));
        set_exception_handler(array('Azure_app_service_migration_Custom_Logger', 'handleException'));
    }

    // Log the user registration event
    public static function log_user_registration($user_id)
    {
        $username = get_user_by('id', $user_id)->user_login;
        $message = "User '{$username}' registered successfully.";
        self::writeToLog($message);
    }

    // Custom error handler
    public static function handleError($severity, $message, $file, $line)
    {
        $error_message = "Error [{$severity}]: {$message} in {$file} on line {$line}";
        self::writeToLog($error_message);
    }

    // Custom exception handler
    public static function handleException($exception)
    {
        // Get the exception details
        $message = 'Uncaught Exception: ' . $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();

        // Build the log message with details
        $log_message = "Uncaught Exception:\n";
        $log_message .= "Message: {$message}\n";
        $log_message .= "File: {$file}\n";
        $log_message .= "Line: {$line}\n";
        $log_message .= "Trace:\n{$trace}";

        // Log the exception details
        self::writeToLog($log_message);
    }
}

// Initialize the custom logger
Azure_app_service_migration_Custom_Logger::init();
