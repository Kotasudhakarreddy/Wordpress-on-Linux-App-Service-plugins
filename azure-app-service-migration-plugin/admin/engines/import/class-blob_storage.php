<?php
class Azure_app_service_migration_Blob_Storage {

    public static function get_blob_storage_settings() {
        $w3tc_config_filepath = AASM_W3TC_CONFIG_MASTER_PATH;
        
        // Return empty array if w3tc config file not found
        if (!file_exists($w3tc_config_filepath))
            return [];
        
        // Read the file contents
        $fileContents = file_get_contents($w3tc_config_filepath);
    
        // Remove the PHP exit tag at the beginning of the file
        $jsonString = substr($fileContents, strpos($fileContents, '{'));
    
        // Decode the JSON string into a PHP object
        $jsonData = json_decode($jsonString);
        
        // Access the values inside the JSON object
        $cdn_engine = isset($jsonData->{'cdn.engine'}) ? $jsonData->{'cdn.engine'} : null;
        $storage_account = isset($jsonData->{'cdn.azure.user'}) ? $jsonData->{'cdn.azure.user'} : null;
        $storage_account_key = isset($jsonData->{'cdn.azure.key'}) ? $jsonData->{'cdn.azure.key'} : null;
        $blob_container = isset($jsonData->{'cdn.azure.container'}) ? $jsonData->{'cdn.azure.container'} : null;

        if (is_null($cdn_engine) 
            || is_null($storage_account) 
            || is_null($storage_account_key) 
            || is_null($storage_account_container) 
            || $cdn_engine != "azure")
        {
            return [];
        }
    
        // Return an array of blob storage values
        return [
            'storage_account' => $storage_account,
            'storage_account_key' => $storage_account_key,
            'blob_container' => $blob_container
        ];
    }
}