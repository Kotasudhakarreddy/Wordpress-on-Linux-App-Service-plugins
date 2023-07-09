<?php
class Azure_app_service_migration_Import_AjaxHandler {
    public function handle_ajax_requests_admin() {
        $param = isset($_REQUEST['param']) ? $_REQUEST['param'] : "";
        exit($param);
        if (!empty($param)) {
            if ($param == "wp_fileImport") {
                $fileBackupHandler = new Azure_app_service_migration_Import_FileBackupHandler();
                $fileBackupHandler->handle_wp_fileimport();
            }
        }
        wp_die();
    }
}
?>
