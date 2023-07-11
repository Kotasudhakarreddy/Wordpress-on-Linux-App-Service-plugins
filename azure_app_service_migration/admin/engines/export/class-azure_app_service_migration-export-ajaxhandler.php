<?php
class Azure_app_service_migration_Export_AjaxHandler {
    public function handle_ajax_requests_admin() {
        $param = isset($_REQUEST['param']) ? $_REQUEST['param'] : "";
        if (!empty($param)) {
            if ($param == "wp_filebackup") {
                $fileBackupHandler = new Azure_app_service_migration_Export_FileBackupHandler();
                $fileBackupHandler->handle_wp_filebackup();
            }else{
                if ($param == "wp_ImportFile") {
                    $fileImportHandler = new Azure_app_service_migration_Import_FileBackupHandler();
                    $fileImportHandler->handle_wp_fileImport();
                }
            }
        }
        wp_die();
    }
}
?>
