
<?php
class FileBackupHandler
{
    public function handle_wp_filebackup()
    {
        // handles all ajax request of admin
        $param = isset($_REQUEST['param']) ? $_REQUEST['param'] : "";
        if (!empty($param)) {
            if ($param == "wp_filebackup") {
                //print_r($_REQUEST);
                $password = isset($_REQUEST['hiddenconfpassword']) ? $_REQUEST['hiddenconfpassword'] : "";
                $dontexptpostrevisions = isset($_REQUEST['hiddendontexptpostrevisions']) ? $_REQUEST['hiddendontexptpostrevisions'] : "";
                $dontexptsmedialibrary = isset($_REQUEST['hiddendontexptsmedialibrary']) ? $_REQUEST['hiddendontexptsmedialibrary'] : "";
                $dontexptsthems = isset($_REQUEST['hiddendontexptsthems']) ? $_REQUEST['hiddendontexptsthems'] : "";
                $dontexptmustuseplugins = isset($_REQUEST['hiddendontexptmustuseplugs']) ? $_REQUEST['hiddendontexptmustuseplugs'] : "";
                $dontexptplugins = isset($_REQUEST['hiddendontexptplugins']) ? $_REQUEST['hiddendontexptplugins'] : "";
                $dontdbsql = isset($_REQUEST['hiddendbsql']) ? $_REQUEST['hiddendbsql'] : "";
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

                $File_Name = $_SERVER['HTTP_HOST'];
                $datetime = date('Y-m-d_H-i-s');
                $zipFileName = $File_Name . '_' . $datetime . '.zip';
                $wp_root_path = get_home_path();

                // bkupcontent directory path
                $zipFilePath = $wp_root_path . '/wp-content/plugins/azure_app_service_migration/' . $zipFileName;
                $folderPath = $wp_root_path . '/wp-content/';
                $iterator = new DirectoryIterator($wp_root_path . '/wp-content/plugins/azure_app_service_migration/');
                foreach ($iterator as $file) {

                    if ($file->isFile() && strpos($file->getFilename(), $File_Name) === 0 && pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'zip') {
                        $filePath = $file->getPathname();
                        unlink($filePath);
                    }
                }
                $maxRetries = 3;
                $retryDelay = 5; // in seconds
                $retryCount = 0;
                $zipCreated = false;
                while ($retryCount < $maxRetries && !$zipCreated) {
                    $zip = new ZipArchive();
                    if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                        $wpContentFolderNameInZip = 'wp-content/';
                        $zip->addEmptyDir($wpContentFolderNameInZip);
                        // Skips the DB exports if the user wants to skip
                        if (!$dontdbsql) {
                            $wpDBFolderNameInZip = 'wp-database/';
                            $zip->addEmptyDir($wpDBFolderNameInZip);
                            // The database file will be exported
                            // Get the list of tables
                            global $wpdb;
                            $tablesQuery = "SHOW TABLES";
                            $tables = $wpdb->get_results($tablesQuery, ARRAY_N);
                            // Loop through the tables
                            foreach ($tables as $table) {
                                $tableName = $table[0];
                                // Export table structure
                                $structureQuery = "SHOW CREATE TABLE {$tableName}";
                                $structureResult = $wpdb->get_row($structureQuery, ARRAY_N);
                                $tableStructure = $structureResult[1];
                                // Write table structure to a file
                                $structureFilename = "{$tableName}_structure.sql";
                                // file_put_contents($folderPath . $structureFilename, $tableStructure);
                                $zip->addFromString($wpDBFolderNameInZip . $structureFilename, $tableStructure);
                                // Set password for the file
                                if ($password !== '') {
                                    $zip->setEncryptionName($wpDBFolderNameInZip . $structureFilename, ZipArchive::EM_AES_256, $password);
                                }
                                // Export table records in batches
                                // Number of rows to export per batch
                                $batchSize = 1000;
                                $offset = 0;
                                $batchNumber = 1;
                                do {
                                    if ($dontexptpostrevisions && $tableName == 'wp_posts') {
                                        $recordsQuery = "SELECT * FROM {$tableName} WHERE post_type != 'revision' LIMIT {$offset}, {$batchSize}";
                                    } else {
                                        $recordsQuery = "SELECT * FROM {$tableName} LIMIT {$offset}, {$batchSize}";
                                    }
                                    $records = $wpdb->get_results($recordsQuery, ARRAY_A);
                                    // Write records to a file
                                    $recordsFilename = "{$tableName}_records_batch{$batchNumber}.sql";

                                    if (!empty($records)) {
                                        $recordsContent = "";
                                        foreach ($records as $record) {
                                            $recordValues = [];
                                            foreach ($record as $value) {
                                                if (is_null($value)) {
                                                    $recordValues[] = "NULL";
                                                } elseif (is_int($value) || is_float($value) || is_numeric($value)) {
                                                    $recordValues[] = $value;
                                                } elseif (is_bool($value)) {
                                                    $recordValues[] = $value ? 'TRUE' : 'FALSE';
                                                } elseif (is_object($value) || is_array($value)) {
                                                    $recordValues[] = "'" . addslashes(serialize($value)) . "'";
                                                } elseif (is_string($value)) {
                                                    // Handle specific data types
                                                    if (is_numeric($value)) {
                                                        // Numeric types (INTEGER, FLOAT, DECIMAL, etc.)
                                                        $recordValues[] = $value;
                                                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                                                        // Date type (YYYY-MM-DD)
                                                        $recordValues[] = "'" . $value . "'";
                                                    } elseif (preg_match('/^\d{2}:\d{2}:\d{2}$/', $value)) {
                                                        // Time type (HH:MM:SS)
                                                        $recordValues[] = "'" . $value . "'";
                                                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $value)) {
                                                        // Datetime type (YYYY-MM-DD HH:MM:SS)
                                                        $recordValues[] = "'" . $value . "'";
                                                    } elseif (is_numeric($value) && (strpos($value, '.') !== false || strpos($value, 'e') !== false)) {
                                                        // Float or Decimal type (values with decimal points or scientific notation)
                                                        $recordValues[] = $value;
                                                    } else {
                                                        // String types (CHAR, VARCHAR, TEXT, etc.)
                                                        $recordValues[] = "'" . addslashes($value) . "'";
                                                    }
                                                } else {
                                                    $recordValues[] = "'" . addslashes($value) . "'";
                                                }
                                            }
                                            $recordsContent .= "INSERT INTO {$tableName} VALUES (" . implode(', ', $recordValues) . ");\n";
                                        }
                                        $zip->addFromString($wpDBFolderNameInZip . $recordsFilename, $recordsContent);
                                        // Set password for the file
                                        if ($password !== '') {
                                            $zip->setEncryptionName($wpDBFolderNameInZip . $recordsFilename, ZipArchive::EM_AES_256, $password);
                                        }
                                    }
                                    // Increment the offset and batch number
                                    $offset += $batchSize;
                                    $batchNumber++;
                                } while (!empty($records));
                            }
                        }

                        // Create a RecursiveDirectoryIterator
                        $iterator = new RecursiveDirectoryIterator($folderPath);
                        $filteredElements = [];
                        $filterIterator = new RecursiveCallbackFilterIterator($iterator, function ($current, $key, $iterator) use ($excludedFolders, &$filteredElements) {
                            return filterCallback($current, $excludedFolders, $filteredElements);
                        });
                        function filterCallback($current, $excludedFolders, &$filteredElements)
                        {
                            // Check if the element has already been processed
                            if (in_array($current->getPathname(), $filteredElements)) {
                                return false;
                            }
                            // Exclude the specified folders and their subdirectories
                            foreach ($excludedFolders as $excludedFolder) {
                                if ($current->getFilename() === $excludedFolder && $current->isDir()) {
                                    return false;
                                }
                            }
                            // Mark the element as processed
                            $filteredElements[] = $current->getPathname();
                            return true;
                        }
                        // Create a RecursiveIteratorIterator to iterate through the filtered files
                        $files = new RecursiveIteratorIterator($filterIterator);
                        $cntbatchSize = 100;
                        $batchNumber = 1;
                        $currentBatchFiles = [];
                        foreach ($files as $name => $file) {
                            if (!$file->isDir()) {
                                $filePath = $file->getRealPath();
                                $relativePath = substr($filePath, strlen($folderPath)+-1);
                                $currentBatchFiles[] = [
                                    'path' => $filePath,
                                    'relativePath' => $relativePath,
                                ];
                                // Check if the current batch size has been reached
                                if (count($currentBatchFiles) === $cntbatchSize) {
                                    // Add the files to the zip archive for the current batch
                                    foreach ($currentBatchFiles as $batchFile) {
                                        $zip->addFile($batchFile['path'], $wpContentFolderNameInZip . $batchFile['relativePath']);
                                        // Set password for the file
                                        if ($password !== '') {
                                            $zip->setEncryptionName($wpContentFolderNameInZip . $batchFile['relativePath'], ZipArchive::EM_AES_256, $password);
                                        }
                                    }
                                    $batchNumber++;
                                    $currentBatchFiles = [];
                                }
                            }
                        }

                        // Add the remaining files to the zip archive (if any)
                        foreach ($currentBatchFiles as $batchFile) {
                            $zip->addFile($batchFile['path'], $wpContentFolderNameInZip . $batchFile['relativePath']);
                            // Set password for the file
                            if ($password !== '') {
                                $zip->setEncryptionName($wpContentFolderNameInZip . $batchFile['relativePath'], ZipArchive::EM_AES_256, $password);
                            }
                        }

                        $zip->close();
                        $zipCreated = true;
                    } else {
                        $retryCount++;
                        sleep($retryDelay);
                    }
                }

                if ($zipCreated) {
                    echo json_encode(array(
                        "status" => 1,
                        "message" => "ZIP archive created successfully.",
                    ));
                } else {
                    echo json_encode(array(
                        "status" => 0,
                        "message" => "Failed to create ZIP after maximum retries.",
                    ));
                }
            }

        }
    }

    // Define other methods specific to file backup functionality
}
?>
