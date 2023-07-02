<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'ABSPATH undefined.' );
}

// ==================
// = Plugin Version =
// ==================
define( 'AASM_VERSION', '1.0.0' );

// ===============
// = Plugin Name =
// ===============
define( 'AASM_PLUGIN_NAME', 'azue-app-service-migration' );

// ================
// = Storage Path =
// ================
// TO DO: CHANGE FILE NAME AS NEEDED WHILE MERGING WITH EXPORT FUNCTIONALITY
define( 'AASM_IMPORT_ZIP_PATH', AZURE_APP_SERVICE_MIGRATION_PLUGIN_PATH . DIRECTORY_SEPARATOR . 
                                'storage' . DIRECTORY_SEPARATOR . 
                                'import' . DIRECTORY_SEPARATOR .
                                'importfile.zip');

define( 'AASM_DATABASE_RELATIVE_PATH_IN_ZIP', 'backupdb' . DIRECTORY_SEPARATOR );

define( 'AASM_DATABASE_TEMP_DIR', AZURE_APP_SERVICE_MIGRATION_PLUGIN_PATH . 'storage' . 
                                DIRECTORY_SEPARATOR . 'dbtempdir' . DIRECTORY_SEPARATOR );