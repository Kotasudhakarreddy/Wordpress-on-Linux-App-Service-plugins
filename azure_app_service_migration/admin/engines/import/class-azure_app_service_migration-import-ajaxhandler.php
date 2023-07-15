<?php
class Azure_app_service_migration_Import_AjaxHandler {
    public function handle_ajax_requests_admin() {
        
    }
    
    public function get_migration_status() {
        AASM_Common_Utils::aasm_json_response(get_option( AI1WM_STATUS, array() ));
        wp_die();
    }
}
?>
