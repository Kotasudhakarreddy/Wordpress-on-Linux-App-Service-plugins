<?php
class AjaxHandler {
    public function handle_ajax_requests_admin() {
        $param = isset($_REQUEST['param']) ? $_REQUEST['param'] : "";
        if (!empty($param)) {
            if ($param == "wp_filebackup") {
                $fileBackupHandler = new FileBackupHandler();
                $fileBackupHandler->handle_wp_filebackup();
            }
        }
        wp_die();
    }
}
?>
