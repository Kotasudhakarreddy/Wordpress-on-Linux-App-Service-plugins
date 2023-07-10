<?php

class Azure_app_service_migration_Import_FileBackupHandler
{
    public function __construct()
    {        // Add filter to modify the default file upload limit
        add_filter('upload_size_limit', array($this, 'set_upload_file_limit'));
    }

    public function set_upload_file_limit($limit)
    {
        // Set the default file upload limit to 128MB
        $default_limit = 1 * 1024 * 1024; // 128MB

        return $default_limit;
    }

    public function handle_wp_fileImport()
    {
        $param = isset($_REQUEST['param']) ? $_REQUEST['param'] : "";

        if (!empty($param) && $param == "wp_ImportFile") {
            if (isset($_FILES['importFile'])) {
                $file = $_FILES['importFile'];

                // Check for errors during file upload
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $error_message = $this->get_upload_error_message($file['error']);
                    echo json_encode(array(
                        "status" => 0,
                        "message" => $error_message,
                    ));
                    return;
                }

                // Specify the desired folder path
                $wp_root_path = get_home_path();
                $folderPath = $wp_root_path . 'wp-content/plugins/azure-app-service-migration-plugin/ImportedFile';

                // Create the folder if it doesn't exist
                if (!is_dir($folderPath)) {
                    mkdir($folderPath, 0755, true);
                }

                // Generate a unique filename
                $filename = $file['name'];

                // The temporary location of the uploaded file
                $tmpFile = $file['tmp_name'];

                // The final destination path
                $destinationPath = $folderPath . '/' . $filename;

                // Check if the uploaded file is a zip file
                $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if ($fileType !== 'zip') {
                    echo json_encode(array(
                        "status" => 0,
                        "message" => "Only ZIP files are allowed.",
                    ));
                    return;
                }

                // Copy the file to the specified destination path
                if (move_uploaded_file($tmpFile, $destinationPath)) {
                    // File successfully copied
                    echo json_encode(array(
                        "status" => 1,
                        "message" => "File successfully copied.",
                    ));
                } else {
                    // Failed to copy the file
                    echo json_encode(array(
                        "status" => 0,
                        "message" => "Failed to copy the file.",
                    ));
                }
            }
        } else {
            // Invalid request parameters
            echo json_encode(array(
                "status" => 0,
                "message" => "Invalid request parameters.",
            ));
        }

        $error_message = 'An error occurred during the backup process.';
        $plugin_log_file = plugin_dir_path(dirname(__FILE__)) . 'debug.log';
        error_log($error_message, 3, $plugin_log_file);
    }

    public function get_upload_error_message($error_code)
    {
        switch ($error_code) {
            case UPLOAD_ERR_INI_SIZE:
                return "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
            case UPLOAD_ERR_FORM_SIZE:
                return "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
            case UPLOAD_ERR_PARTIAL:
                return "The uploaded file was only partially uploaded.";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded.";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing a temporary folder.";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk.";
            case UPLOAD_ERR_EXTENSION:
                return "A PHP extension stopped the file upload.";
            default:
                return "Unknown error occurred during file upload.";
        }
    }

    private function deleteExistingZipFiles($folderPath)
    {
        try {
            if (file_exists($folderPath) && is_dir($folderPath)) {
                $fileList = scandir($folderPath);
                foreach ($fileList as $file) {
                    if ($file != '.' && $file != '..') {
                        $filePath = $folderPath . '/' . $file;
                        if (is_file($filePath)) {
                            if (!unlink($filePath)) {
                                // Unable to delete file
                                error_log('Unable to delete file: ' . $filePath);
                            }
                        }
                    }
                }
            } else {
                echo "Folder does not exist or is not a directory.";
            }
        } catch (Exception $e) {
            throw new AASM_File_Delete_Exception('File Delete error:' . $e->getMessage());
        }
    }

}

new Azure_app_service_migration_Import_FileBackupHandler();
