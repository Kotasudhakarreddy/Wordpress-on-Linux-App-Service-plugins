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

            // Get the latest chunk number in the upload directory
            $latestChunkNumber = $this->getLatestChunkNumber($uploadDir);

            // Generate the chunk filename based on the latest chunk number
            $chunkFilename = $uploadDir . 'chunk_' . $latestChunkNumber;

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

    private function getLatestChunkNumber($uploadDir)
    {
        $latestChunkNumber = 0;
        $counterFilePath = $uploadDir . 'chunk_counter.txt';

        // Check if the counter file exists
        if (file_exists($counterFilePath)) {
            // Read the current chunk number from the counter file
            $currentChunkNumber = intval(file_get_contents($counterFilePath));

            // Calculate the latest chunk number
            $latestChunkNumber = $currentChunkNumber + 1;
        } else {
            // Create the counter file with initial value 0
            file_put_contents($counterFilePath, '0');
            $latestChunkNumber = 0; // Start with chunk number 1
        }

        // Update the counter file with the latest chunk number
        file_put_contents($counterFilePath, $latestChunkNumber);

        return $latestChunkNumber;
    }   

    public function handle_combine_chunks()
    {
        // continue executing if client side request aborts
        ignore_user_abort(true);

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
            
            // Create the original file
            $originalFilePath = $uploadDir . $originalFilename;

            // Open the original file in write mode
            $originalFile = fopen($originalFilePath, 'wb');

            if ($originalFile !== false) {
                $chunkIndex = 0;
                $chunkFile = $uploadDir . $chunkPrefix . $chunkIndex;

                while (file_exists($chunkFile)) {
                    set_time_limit(0);
                    // Read the content of the current chunk file
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

                    $chunkIndex++;
                    $chunkFile = $uploadDir . $chunkPrefix . $chunkIndex;
                }

                // Close the original file
                fclose($originalFile);
                $counterFilePath = $uploadDir . 'chunk_counter.txt';

                // Update the counter file with the value 0
                file_put_contents($counterFilePath, '-1');
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
            // Send an error response
            http_response_code(400);
            echo 'Invalid action parameter.';
        }

        wp_die(); // Terminate the request
    }

}

new Azure_app_service_migration_Import_FileBackupHandler();
