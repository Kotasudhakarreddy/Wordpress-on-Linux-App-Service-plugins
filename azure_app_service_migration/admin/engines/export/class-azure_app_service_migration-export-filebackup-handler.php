
<?php
class Azure_app_service_migration_Export_FileBackupHandler
{
    private $logFilePath;

    public function __construct()
    {
        $this->logFilePath = AASM_LOG_FILE_LOCATION . 'export_log.txt';

    }

    public function handle_wp_filebackup()
    {
        try {
            $param = isset($_REQUEST['param']) ? $_REQUEST['param'] : "";
            if (!empty($param)) {
                if ($param == "wp_filebackup") {
                    $password = isset($_REQUEST['confpassword']) ? $_REQUEST['confpassword'] : "";
                    $dontexptpostrevisions = isset($_REQUEST['dontexptpostrevisions']) ? $_REQUEST['dontexptpostrevisions'] : "";
                    $dontexptsmedialibrary = isset($_REQUEST['dontexptsmedialibrary']) ? $_REQUEST['dontexptsmedialibrary'] : "";
                    $dontexptsthems = isset($_REQUEST['dontexptsthems']) ? $_REQUEST['dontexptsthems'] : "";
                    $dontexptmustuseplugins = isset($_REQUEST['dontexptmustuseplugs']) ? $_REQUEST['dontexptmustuseplugs'] : "";
                    $dontexptplugins = isset($_REQUEST['dontexptplugins']) ? $_REQUEST['dontexptplugins'] : "";
                    $dontdbsql = isset($_REQUEST['donotdbsql']) ? $_REQUEST['donotdbsql'] : "";

                    // delete existing log file
                    Azure_app_service_migration_Custom_Logger::delete_log_file(AASM_EXPORT_SERVICE_TYPE);
                    // Initialize log file
                    Azure_app_service_migration_Custom_Logger::init(AASM_EXPORT_SERVICE_TYPE);
                    Azure_app_service_migration_Custom_Logger::logInfo(AASM_EXPORT_SERVICE_TYPE, 'Started with the export process.');

                    $zipFileName = $this->generateZipFileName();
                    Azure_app_service_migration_Custom_Logger::logInfo(AASM_EXPORT_SERVICE_TYPE, 'Zip file name is generated as: ' . $zipFileName);

                    $zipFilePath = $this->getZipFilePath($zipFileName);
                    Azure_app_service_migration_Custom_Logger::logInfo(AASM_EXPORT_SERVICE_TYPE, 'Zip file path is: ' . $zipFilePath);

                    $excludedFolders = $this->getExcludedFolders($dontexptsmedialibrary, $dontexptsthems, $dontexptmustuseplugins, $dontexptplugins);

                    Azure_app_service_migration_Custom_Logger::logInfo(AASM_EXPORT_SERVICE_TYPE, 'Deleting the previously generated exported file.');
                    $this->deleteExistingZipFiles();

                    Azure_app_service_migration_Custom_Logger::logInfo(AASM_EXPORT_SERVICE_TYPE, 'Started generating the ZipArchive for ' . $zipFileName);
                    $zipCreated = $this->createZipArchive($zipFilePath, $excludedFolders, $dontdbsql, $password, $dontexptpostrevisions);
                    Azure_app_service_migration_Custom_Logger::logInfo(AASM_EXPORT_SERVICE_TYPE, 'Completed generating the ZipArchive for ' . $zipFileName);

                    if ($zipCreated) {
                        Azure_app_service_migration_Custom_Logger::logInfo(AASM_EXPORT_SERVICE_TYPE, 'Content is exported and Ready to download');
                        echo json_encode(array(
                            "status" => 1,
                            "message" => "Content is exported and Ready to download",
                        ));
                    } else {
                        Azure_app_service_migration_Custom_Logger::logError(AASM_EXPORT_SERVICE_TYPE, 'Failed to export after maximum retries.');
                        echo json_encode(array(
                            "status" => 0,
                            "message" => "Failed to export after maximum retries.",
                        ));
                    }
                }
            }
        } catch (Exception $e) {
            Azure_app_service_migration_Custom_Logger::logError(AASM_EXPORT_SERVICE_TYPE, 'An exception occurred: ' . $e->getMessage());
            echo json_encode(array(
                "status" => 0,
                "message" => "An exception occurred: " . $e->getMessage(),
            ));
        }
    }
    private function generateZipFileName()
    {
        $File_Name = $_SERVER['HTTP_HOST'];
        $datetime = date('Y-m-d_H-i-s');
        return $File_Name . '_' . $datetime . '.zip';
    }

    private function getZipFilePath($zipFileName)
    {
        // Create the directory if it doesn't exist
        if (!is_dir(AASM_EXPORT_ZIP_LOCATION)) {
            mkdir(AASM_EXPORT_ZIP_LOCATION, 0777, true);
            // Set appropriate permissions for the directory (0777 allows read, write, and execute permissions for everyone)
        }
        return AASM_EXPORT_ZIP_LOCATION . $zipFileName;
    }

    private function getExcludedFolders($dontexptsmedialibrary, $dontexptsthems, $dontexptmustuseplugins, $dontexptplugins)
    {
        $excludedFolders = [];
        if ($dontexptsmedialibrary) {
            $excludedFolders[] = 'uploads';
        }
        if ($dontexptsthems) {
            $excludedFolders[] = 'themes';
        }
        if ($dontexptmustuseplugins) {
            $excludedFolders[] = 'mu-plugins';
        }
        if ($dontexptplugins) {
            $excludedFolders[] = 'plugins';
        }
        return $excludedFolders;
    }

    private function deleteExistingZipFiles()
    {
        try {
            $File_Name = $_SERVER['HTTP_HOST'];
            $iterator = new DirectoryIterator(AASM_EXPORT_ZIP_LOCATION);
            foreach ($iterator as $file) {
                if ($file->isFile() && strpos($file->getFilename(), $File_Name) === 0 && pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'zip') {
                    $filePath = $file->getPathname();
                    unlink($filePath);
                }
            }
        } catch (Exception $e) {
            Azure_app_service_migration_Custom_Logger::logError(AASM_EXPORT_SERVICE_TYPE, 'File Delete error: ' . $e->getMessage());
            throw new AASM_File_Delete_Exception('File Delete error:' . $e->getMessage());
        }
    }

    private function createZipArchive($zipFilePath, $excludedFolders, $dontdbsql, $password, $dontexptpostrevisions)
    {
        $maxRetries = 3;
        $retryDelay = 5; // in seconds
        $retryCount = 0;
        $zipCreated = false;
        try {
            while ($retryCount < $maxRetries && !$zipCreated) {
                $zip = new ZipArchive();
                if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                    $wpContentFolderNameInZip = 'wp-content/';
                    $zip->addEmptyDir($wpContentFolderNameInZip);

                    if (!$dontdbsql) {
                        $wpDBFolderNameInZip = 'wp-database/';
                        $zip->addEmptyDir($wpDBFolderNameInZip);
                        $this->exportDatabaseTables($zip, $wpDBFolderNameInZip, $password, $dontexptpostrevisions);
                    }

                    $wp_root_path = get_home_path();
                    $folderPath = $wp_root_path . '/wp-content/';
                    $this->addFilesToZip($zip, $folderPath, $wpContentFolderNameInZip, $excludedFolders, $password);

                    $zip->close();
                    $zipCreated = true;
                    Azure_app_service_migration_Custom_Logger::logInfo(AASM_EXPORT_SERVICE_TYPE, 'Zip Archive closed successfully.');
                } else {
                    $retryCount++;
                    sleep($retryDelay);
                }
            }
        } catch (Exception $e) {
            Azure_app_service_migration_Custom_Logger::logError(AASM_EXPORT_SERVICE_TYPE, 'Zip creation error: ' . $e->getMessage());
            throw new AASM_Archive_Exception('Zip creation error:' . $e->getMessage());
        }
        return $zipCreated;
    }

    private function exportDatabaseTables($zip, $wpDBFolderNameInZip, $password, $dontexptpostrevisions)
    {
        global $wpdb;
        $tablesQuery = "SHOW TABLES";
        $tables = $wpdb->get_results($tablesQuery, ARRAY_N);
        try {
            $currentTable = null;
            foreach ($tables as $table) {
                $tableName = $table[0];
                $structureQuery = "SHOW CREATE TABLE {$tableName}";
                $structureResult = $wpdb->get_row($structureQuery, ARRAY_N);
                $tableStructure = $structureResult[1];
                $structureFilename = "{$tableName}_structure.sql";
                $zip->addFromString($wpDBFolderNameInZip . $structureFilename, $tableStructure);

                if ($password !== '') {
                    $zip->setEncryptionName($wpDBFolderNameInZip . $structureFilename, ZipArchive::EM_AES_256, $password);
                }

                if ($currentTable !== $tableName) {
                    $currentTable = $tableName;
                    Azure_app_service_migration_Custom_Logger::logInfo(AASM_EXPORT_SERVICE_TYPE, 'Exporting Schema for table: ' . $currentTable);
                }

                $this->exportTableRecords($wpdb, $tableName, $zip, $wpDBFolderNameInZip, $password, $dontexptpostrevisions);
            }
        } catch (Exception $e) {
            Azure_app_service_migration_Custom_Logger::logError(AASM_EXPORT_SERVICE_TYPE, 'DB Tables export exception: ' . $e->getMessage());
            throw new AASM_Export_Exception('DB Tables export exception:' . $e->getMessage());
        }
    }
    private function exportTableRecords($wpdb, $tableName, $zip, $wpDBFolderNameInZip, $password, $dontexptpostrevisions)
    {
        $batchSize = 1000;
        $offset = 0;
        $batchNumber = 1;
        try {
            Azure_app_service_migration_Custom_Logger::logInfo(AASM_EXPORT_SERVICE_TYPE, 'Exporting Records for table : ' . $tableName . '-started');            do {
                if ($dontexptpostrevisions && $tableName == 'wp_posts') {
                    $recordsQuery = "SELECT * FROM {$tableName} WHERE post_type != 'revision' LIMIT {$offset}, {$batchSize}";
                } else {
                    $recordsQuery = "SELECT * FROM {$tableName} LIMIT {$offset}, {$batchSize}";
                }

                $records = $wpdb->get_results($recordsQuery, ARRAY_A);
                $recordsFilename = "{$tableName}_records_batch{$batchNumber}.sql";

                if (!empty($records)) {
                    $recordsContent = "";

                    foreach ($records as $record) {
                        $recordValues = [];

                        foreach ($record as $value) {
                            $recordValues[] = $this->formatRecordValue($value);
                        }

                        $recordsContent .= "INSERT INTO {$tableName} VALUES (" . implode(', ', $recordValues) . ");\n";
                    }

                    if ($batchNumber === 1) {
                        $zip->addFromString($wpDBFolderNameInZip . $tableName . ".sql", $recordsContent);
                    } else {
                        $zip->appendFromString($wpDBFolderNameInZip . $tableName . ".sql", $recordsContent);
                    }

                    if ($password !== '') {
                        $zip->setEncryptionName($wpDBFolderNameInZip . $tableName . ".sql", ZipArchive::EM_AES_256, $password);
                    }
                }

                $offset += $batchSize;
                $batchNumber++;
            } while (!empty($records));

            Azure_app_service_migration_Custom_Logger::logInfo(AASM_EXPORT_SERVICE_TYPE, 'Exporting Records for table: ' . $tableName . ' - completed');
        } catch (Exception $e) {
            Azure_app_service_migration_Custom_Logger::logError(AASM_EXPORT_SERVICE_TYPE, 'Table records export exception: ' . $e->getMessage());
            throw new AASM_Export_Exception('Table records export exception:' . $e->getMessage());
        }
    }

    private function formatRecordValue($value)
    {
        try {
            if (is_null($value)) {
                return "NULL";
            } elseif (is_int($value) || is_float($value) || is_numeric($value)) {
                return $value;
            } elseif (is_bool($value)) {
                return $value ? 'TRUE' : 'FALSE';
            } elseif (is_object($value) || is_array($value)) {
                return "'" . addslashes(serialize($value)) . "'";
            } elseif (is_string($value)) {
                if (is_numeric($value)) {
                    return $value;
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    return "'" . $value . "'";
                } elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                    return "'" . $value . "'";
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                    return "'" . $value . "'";
                } elseif (is_numeric($value) && (strpos($value, '.') !== false || strpos($value, 'e') !== false)) {
                    return $value;
                } else {
                    return "'" . addslashes($value) . "'";
                }
            } else {
                return "'" . addslashes($value) . "'";
            }
        } catch (Exception $e) {
            Azure_app_service_migration_Custom_Logger::logError(AASM_EXPORT_SERVICE_TYPE, 'Table record format exception: ' . $e->getMessage());
            throw new AASM_Export_Exception('Table record format exception:' . $e->getMessage());
        }
    }

    private function addFilesToZip($zip, $folderPath, $wpContentFolderNameInZip, $excludedFolders, $password)
    {
        try {
            $iterator = new RecursiveDirectoryIterator($folderPath);
            $filteredElements = [];
            $filterIterator = new RecursiveCallbackFilterIterator($iterator, function ($current, $key, $iterator) use ($excludedFolders, &$filteredElements) {
                return $this->filterCallback($current, $excludedFolders, $filteredElements);
            });

            $files = new RecursiveIteratorIterator($filterIterator);
            $cntbatchSize = 100;
            $batchNumber = 1;
            $currentBatchFiles = [];
            $currentFolder = null; // Variable to track the current folder being processed

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($folderPath)+-1);
                    $currentBatchFiles[] = [
                        'path' => $filePath,
                        'relativePath' => $relativePath,
                    ];

                    $folder = $relativePath;
                    if ($currentFolder !== $folder) {
                        $currentFolder = $folder;
                        Azure_app_service_migration_Custom_Logger::logInfo(AASM_EXPORT_SERVICE_TYPE, 'Exporting from wp-content path: ' . $currentFolder);
                    }

                    if (count($currentBatchFiles) >= $cntbatchSize) {
                        $this->addFilesToZipBatch($zip, $currentBatchFiles, $wpContentFolderNameInZip, $password, $batchNumber);
                        $batchNumber++;
                        $currentBatchFiles = [];
                    }
                }
            }

            if (!empty($currentBatchFiles)) {
                $this->addFilesToZipBatch($zip, $currentBatchFiles, $wpContentFolderNameInZip, $password, $batchNumber);
            }
        } catch (Exception $e) {
            Azure_app_service_migration_Custom_Logger::logError(AASM_EXPORT_SERVICE_TYPE, 'Failing to add the file to ZipArchive: ' . $e->getMessage());
            throw new AASM_Archive_Exception('Failing to add the file to ZipArchive: ' . $e->getMessage());
        }
    }

    private function filterCallback($current, $excludedFolders, &$filteredElements)
    {
        $fileName = $current->getFilename();
        $filePath = $current->getPathname();
        $relativePath = substr($filePath, strlen(get_home_path()));
        $relativePath = str_replace('\\', '/', $relativePath);
        $relativePathParts = explode('/', $relativePath);
        $parentFolder = isset($relativePathParts[2]) ? $relativePathParts[2] : '';

        if ($fileName == "." || $fileName == "..") {
            return false;
        }

        if (in_array($parentFolder, $excludedFolders)) {
            return false;
        }

        if (in_array($relativePath, $filteredElements)) {
            return false;
        }

        $filteredElements[] = $relativePath;
        return true;
    }

    private function addFilesToZipBatch($zip, $currentBatchFiles, $wpContentFolderNameInZip, $password, $batchNumber)
    {
        try {
            foreach ($currentBatchFiles as $file) {
                $path = $file['path'];
                $relativePath = $file['relativePath'];
                $zip->addFile($path, $wpContentFolderNameInZip . $relativePath);

                if ($password !== '') {
                    $zip->setEncryptionName($wpContentFolderNameInZip . $relativePath, ZipArchive::EM_AES_256, $password);
                }
            }
        } catch (Exception $e) {
            Azure_app_service_migration_Custom_Logger::logError(AASM_EXPORT_SERVICE_TYPE, 'Failing to add the file to ZipArchive during batch: ' . $e->getMessage());
            throw new AASM_Archive_Exception('Failing to add the file to ZipArchive during batch:' . $e->getMessage());
        }
    }
}
?>