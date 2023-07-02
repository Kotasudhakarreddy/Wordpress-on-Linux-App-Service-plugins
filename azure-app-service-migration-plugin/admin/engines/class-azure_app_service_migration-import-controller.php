<?php
class Azure_app_service_migration_Import_Controller {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	
	 /* Pending implementation
	 public static function import_upload() {

	}*/

	/*
	public static function check_encryption() {

	}*/

	/*
	public static function decrypt() {

	}*/

    public static function import($params) {
        
        //To DO: get import zip path from uploaded file and check for encryption
        
		//Import wp-content
		Azure_app_service_migration_Import_Content::import_content(AASM_IMPORT_ZIP_PATH);
		
		//Import database
		$aasm_import_database = new Azure_app_service_migration_Import_Database(AASM_IMPORT_ZIP_PATH, []);
		$aasm_import_database->import_database();
    }

	

}