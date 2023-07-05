<?php
class Azure_app_service_migration_Import_Controller {

    public static function import($params) {
        
        //To DO: upload zip file and check for encryption
        
		//Import wp-content
		$aasm_import_wpcontent = new Azure_app_service_migration_Import_Content(AASM_IMPORT_ZIP_PATH, []);
		$aasm_import_wpcontent->import_content();
		
		//Import database
		$aasm_import_database = new Azure_app_service_migration_Import_Database(AASM_IMPORT_ZIP_PATH, []);
		$aasm_import_database->import_database();

		
    }
}