<?php
class Azure_app_service_migration_Zip_Decrypt {

    public static function is_blob_storage_enabled()
    {
        //Open/read a zip file. Return true if password is correct
        if (!$zip_file_path || !$password) {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($zip_file_path) === true) {
            $zip->setPassword($password);
            $zipfile = zip_read($zip);
            $zip->close();

            return !$zipfile;   // Return true if unable to read inside, indicating wrong password
        }
        else
            return false;
    }
}