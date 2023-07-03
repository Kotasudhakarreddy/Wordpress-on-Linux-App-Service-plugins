<div class="col-md-11 mt-5">
    <div class="shadow p-3 mb-5 bg-body rounded">
        <div class="shadow-sm p-4 mb-4 bg-white boderbottom">Import</div>
        <div style="text-align: center; margin-top: 20px;">
            <button type="button" name="import" id="import" style="padding: 8px 20px; background-color: #337ab7; color: #fff; border: none; border-radius: 4px;">Import</button>
        </div>
        <div style="margin-top: 20px;">
            <input type="checkbox" name="caching_cdn" id="caching_cdn" style="margin-right: 8px; transform: scale(0.8);">
            <label for="caching_cdn" style="font-size: 14px;">Re-enable caching and/or CDN/AFD features</label>
        </div>
        <div id="dialog-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 999;"></div>
        <div id="dialog" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #f0f0f0; padding: 20px; border-radius: 4px; z-index: 1000;">
            <p id="dialogText" style="font-size: 16px;"></p>
        </div>
        <div id="exportdownloadfile">
            <?php
            $wp_root_url = get_home_url();
            $wp_root_filepath = $wp_root_url . "/wp-content/plugins/azure_app_service_migration/backupwebsite/zipfiles/";

            $wp_root_path = get_home_path();
            $dirname = $wp_root_path . "/wp-content/plugins/azure_app_service_migration/backupwebsite/zipfiles/";

            $reportfiles = scandir($dirname, 1);
            foreach ($reportfiles as $file) {
                if (substr($file, -4) == ".zip") {
                    $folderpath = $wp_root_filepath;
                    $finame = $folderpath . '' . $file;
                    //print "<a style='color:#ffffff;' href='" . $folderpath ."" . $file . "' name='downloadfile' id='downloadfile' class='btn btn-primary btn-sm'>Download Export File - $file</a>"; 
                }
            }
            //$src = 'https://unpkg.com/@fluentui/web-components';
            $src = $wp_root_url . "/wp-content/plugins/azure_app_service_migration/assets/node_modules/@fluentui/web-components/dist/web-components.js";
            ?>
            <div class="overlay"></div>
        </div>
    </div>
</div>
<script type="module" src="<?php echo esc_url($src); ?>"></script>
<script type="text/javascript" language="javascript">
    document.addEventListener("DOMContentLoaded", function() {
        var importButton = document.getElementById("import");
        var dialogOverlay = document.getElementById("dialog-overlay");
        var dialog = document.getElementById("dialog");
        var dialogText = document.getElementById("dialogText");

        importButton.addEventListener("click", function() {
            // Display the dialog overlay
            dialogOverlay.style.display = "block";

            // Set the dynamic string from the constant
            dialogText.textContent = "<?php echo addslashes(AASM_STATUS_MSG); ?>";

            // Display the dialog box
            dialog.style.display = "block";
        });

        // Update dialog box on constant change
        setInterval(function() {
            dialogText.textContent = "<?php echo addslashes(AASM_STATUS_MSG); ?>";
        }, 1000); // Update every second (adjust as needed)
    });
</script>
