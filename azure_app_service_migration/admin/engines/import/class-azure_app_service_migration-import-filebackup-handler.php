<?php

class Azure_app_service_migration_Import_FileBackupHandler
{
    public function __construct()
    {
    }

    public function handle_upload_chunk()
    {
        $param = isset($_POST['param']) ? $_POST['param'] : "";

        if (!empty($param) && $param === "wp_ImportFile_chunks") {
            $fileChunk = $_FILES['fileChunk'];
            $uploadDir = AASM_IMPORT_ZIP_LOCATION;
            // Create the directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
                // Set appropriate permissions for the directory (0777 allows read, write, and execute permissions for everyone)
            }

            // Generate a unique filename for the chunk
            $chunkFilename = $uploadDir . uniqid('chunk_');
            // print_r($chunkFilename);

            // Move the uploaded chunk file to the upload directory
            if (move_uploaded_file($fileChunk['tmp_name'], $chunkFilename)) {
                // Chunk uploaded successfully, perform further processing if needed

                // Send a success response
                echo 'Chunk uploaded successfully!';
            } else {
                // Error handling if failed to move the chunk file
                http_response_code(500);
                echo 'Failed to upload chunk.';
            }
        } else {
            // Send an error response
            http_response_code(400);
            echo 'Invalid action parameter.';
        }

        wp_die(); // Terminate the request
    }

    public function handle_combine_chunks()
    {
        $param = isset($_POST['param']) ? $_POST['param'] : "";
        $cachingCdnValue = isset($_POST['caching_cdn']) ? $_POST['caching_cdn'] : "";

        if (!empty($param) && $param === "wp_ImportFile") {            

            // Handle the combine chunks action here
            $uploadDir = AASM_IMPORT_ZIP_LOCATION;
            $chunkPrefix = 'chunk_';
            $originalFilename = 'importfile.zip'; // Adjust the original file name

            // Remove the file if it already exists
            $filePath = $uploadDir . $originalFilename;
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }

            // Get all chunk files in the upload directory
            $chunkFiles = glob($uploadDir . $chunkPrefix . '*');

            if (!empty($chunkFiles)) {
                // Sort the chunk files by their names
                natsort($chunkFiles);

                // Create the original file
                $originalFilePath = $uploadDir . $originalFilename;

                // Open the original file in append mode
                $originalFile = fopen($originalFilePath, 'ab');

                if ($originalFile !== false) {
                    // Loop through the chunk files and append their contents to the original file
                    foreach ($chunkFiles as $chunkFile) {
                        $chunkContent = file_get_contents($chunkFile);

                        if ($chunkContent !== false) {
                            // Write the chunk content to the original file
                            fwrite($originalFile, $chunkContent);

                            // Delete the chunk file after combining
                            unlink($chunkFile);
                        } else {
                            // Error handling if failed to read chunk content
                            http_response_code(500);
                            echo 'Failed to read chunk file: ' . $chunkFile;
                            fclose($originalFile);
                            return;
                        }
                    }

                    // Close the original file
                    fclose($originalFile);

                    // Perform any further actions after combining the chunks

                    // Create the $params array and assign the value of $cachingCdnValue
                    $params = array(
                        'caching_cdn' => $cachingCdnValue,
                    );
                    // Call the import() method and pass the $params variable
                    Azure_app_service_migration_Import_Controller::import($params, $filePath);

                    // Send a success response
                    echo 'Chunks combined successfully!';
                } else {
                    // Error handling if failed to open the original file
                    http_response_code(500);
                    echo 'Failed to open the original file: ' . $originalFilePath;
                }
            } else {
                // Error handling if no chunk files found
                http_response_code(400);
                echo 'No chunk files found.';
            }
        } else {
            // Send an error response
            http_response_code(400);
            echo 'Invalid action parameter.';
        }

        wp_die(); // Terminate the request
    }

}

new Azure_app_service_migration_Import_FileBackupHandler();
