<?php

class AASM_Zip_Extractor {
    private $zip_path = null;
    private $file_handle = null;

    public function __construct( $zip_file_name ) {
        $this->zip_path = $zip_file_name;
        
        // Open input zip file for reading
        if ( ( $this->file_handle = @fopen( $zip_file_name, 'rb' ) ) === false ) {
            throw new AASM_File_Not_Found_Exception( "File Not Found: Couldn't find file at " . $zip_file_name );
        }
    }
    
    public function extract( $destination_dir, $files_to_exclude = [] ) {

        $destination_dir = $this->replace_forward_slash_with_directory_separator($destination_dir);
        if ($destination_dir === null)
        {
            throw new AASM_Archive_Destination_Dir_Exception ('Zip extract error: Target destination not provided.');
        }

        $zip = zip_open($this->zip_path);
        
        $count=0;
        while ($zip_entry = zip_read($zip))
        {
            $filename = $this->replace_forward_slash_with_directory_separator(zip_entry_name($zip_entry));
            
            // remove AASM_IMPORT_ZIP_FILE_NAME prefix in $filename
            if (str_starts_with($filename, AASM_IMPORT_ZIP_FILE_NAME . DIRECTORY_SEPARATOR))
            {
                $filename = substr($filename, strlen(AASM_IMPORT_ZIP_FILE_NAME)+1);
            }

            if (zip_entry_open($zip, $zip_entry, "r"))
            {
                $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                $path_file = $this->replace_forward_slash_with_directory_separator($destination_dir . $filename);
                $new_dir = dirname($path_file);
                
                if (!str_ends_with($new_dir, DIRECTORY_SEPARATOR))
                {
                    $new_dir .= DIRECTORY_SEPARATOR;
                }

                // determine if this file needs to be skipped
                $should_exclude_file = false;
                for ( $i = 0; $i < count( $files_to_exclude ); $i++ ) {
                    if ( str_starts_with( $path_file , $this->replace_forward_slash_with_directory_separator( $files_to_exclude[ $i ] ) . DIRECTORY_SEPARATOR ) === 0 ) {
                        $should_exclude_file = true;
                        break;
                    }
                }

                // extract only wp-content files
                if(!str_starts_with($filename, 'wp-content' . DIRECTORY_SEPARATOR))
                    $should_exclude_file = true;

                if ($should_exclude_file === false)
                {
                    // Create Recursive Directory (if not exist)  
                    if (!file_exists($new_dir)) {
                        mkdir($new_dir, 0777, true);
                    }
                    
                    // write only files to new directory
                    if ( !str_ends_with($path_file, DIRECTORY_SEPARATOR))
                    {
                        $fp = fopen($path_file, "w");
                        fwrite($fp, $buf);
                        fclose($fp);
                    }
                }
                zip_entry_close($zip_entry);            
            }
            $count++;
        }

        zip_close($zip);
    }

    public function extract_database_files($dir_to_extract = AASM_DATABASE_RELATIVE_PATH_IN_ZIP, $destination_dir) {
        
        if ($destination_dir === null)
            return;
        
        $dir_to_extract = $this->replace_forward_slash_with_directory_separator($dir_to_extract);
        $destination_dir = $this->replace_forward_slash_with_directory_separator($destination_dir);
        
        // Create Recursive Directory (if not exist)  
        if (!file_exists($destination_dir)) {
            mkdir($destination_dir, 0777, true);
        }
        
        $zip = zip_open($this->zip_path);

        $count=0;
        while ($zip_entry = zip_read($zip))
        {
            $filename = $this->replace_forward_slash_with_directory_separator(zip_entry_name($zip_entry));

            // remove AASM_IMPORT_ZIP_FILE_NAME prefix in $filename
            if (str_starts_with($filename, AASM_IMPORT_ZIP_FILE_NAME . DIRECTORY_SEPARATOR))
            {
                $filename = substr($filename, strlen(AASM_IMPORT_ZIP_FILE_NAME)+1);
            }

            if (str_starts_with($filename, $dir_to_extract) && str_ends_with($filename, '.sql')) {
                if (zip_entry_open($zip, $zip_entry, "r")) {
                    $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                    $path_file = $destination_dir . basename($filename);
                    $new_dir = dirname($path_file);

                    if (!str_ends_with($new_dir, DIRECTORY_SEPARATOR))
                    {
                        $new_dir .= DIRECTORY_SEPARATOR;
                    }

                    // Create Recursive Directory (if not exist)  
                    if (!file_exists($new_dir)) {
                        mkdir($new_dir, 0777, true);
                    }

                    // write only files to new directory
                    if ( !str_ends_with($path_file, DIRECTORY_SEPARATOR))
                    {
                        $fp = fopen($path_file, "w");
                        fwrite($fp, $buf);
                        fclose($fp);
                    }
                    zip_entry_close($zip_entry);
                }
                
            }
            $count++;
        }

        zip_close($zip);
    }

    public function replace_forward_slash_with_directory_separator ( $dir ) {
        return str_replace("/", DIRECTORY_SEPARATOR, $dir);
    }
}