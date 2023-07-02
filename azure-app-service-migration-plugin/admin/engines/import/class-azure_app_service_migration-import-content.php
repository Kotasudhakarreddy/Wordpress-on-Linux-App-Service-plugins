<?php
class Azure_app_service_migration_Import_Content {

    public static function import_content(string $import_zip_path, $params)
    {
        // Flag to hold if file data has been processed
		$completed = true;

		// Start time
        // To Do: display time taken in UI
		$start = microtime( true );

		// create extractor object for import zip file
		$archive = new AASM_Zip_Extractor( $import_zip_path );

        $files_to_exclude = array_keys( _get_dropins() );
        $files_to_exclude = array_merge(
            $exclude_files,
            array(
                AASM_DATABASE_RELATIVE_PATH_IN_ZIP,
            )
        );

        // Extract a file from archive to WP_CONTENT_DIR
        try {
            $archive->extract( WP_CONTENT_DIR, $files_to_exclude );
        } catch (AASM_Archive_Target_Dir_Exception $ex) {
            $completed = false;
        }
    }
}