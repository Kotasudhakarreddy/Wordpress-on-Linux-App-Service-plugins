<?php
class Azure_app_service_migration_Import_Content {

    private $import_zip_path;
    private $params;

    public function __construct( $import_zip_path, $params ) {
        // Path to the uploaded import zip file
        $this->import_zip_path = ($import_zip_path === null) 
                                ? AASM_IMPORT_ZIP_PATH
                                : $import_zip_path;
        $this->params = $params;
    }

    public function import_content()
    {
        // Flag to hold if file data has been processed
		$completed = true;

		// Start time
        // To Do: display time taken in UI
		$start = microtime( true );

		// create extractor object for import zip file
		$archive = new AASM_Zip_Extractor( $this->import_zip_path );
        
        $files_to_exclude = array_keys( _get_dropins() );
        $files_to_exclude = array_merge(
            $exclude_files,
            array(
                AASM_DATABASE_RELATIVE_PATH_IN_ZIP,
                AZURE_APP_SERVICE_MIGRATION_PLUGIN_PATH,
            )
        );

        // exclude extracting to w3 total cache plugin if retain AFD/CDN/BlobStorage enabled
        if ( isset( $params['retain_w3tc_config'] ) && $params['retain_w3tc_config'] === true ) {
			$files_to_exclude = array_merge(
                $exclude_files,
                array(
                    AASM_W3TC_PLUGIN_DIR,
                    AASM_W3TC_ADVANCED_CACHE_PATH,
                    AASM_W3TC_OBJECT_CACHE_PATH,
                    AASM_W3TC_DB_PATH,
                )
            );
		}

        // Extract a file from archive to WP_CONTENT_DIR
        try {
            $archive->extract( WP_CONTENT_DIR, $files_to_exclude );
        } catch (AASM_Archive_Target_Dir_Exception $ex) {
            $completed = false;
        }

        // delete helper files produced by w3tc plugin
        if ( isset( $params['retain_w3tc_config'] ) && $params['retain_w3tc_config'] === true ) {
            $this->delete_w3tc_helper_files();
        }
    }

    private function delete_w3tc_helper_files()
    {
        AASM_Common_Utils::delete_file(AASM_W3TC_ADVANCED_CACHE_PATH);
        AASM_Common_Utils::delete_file(AASM_W3TC_OBJECT_CACHE_PATH);
        AASM_Common_Utils::delete_file(AASM_W3TC_DB_PATH);        
    }
}