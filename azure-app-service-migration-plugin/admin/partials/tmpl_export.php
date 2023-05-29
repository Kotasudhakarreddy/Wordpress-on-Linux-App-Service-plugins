<div class="col-md-11 mt-5">
    <div class="shadow p-3 mb-5 bg-body rounded">
        <div class="shadow-sm p-4 mb-4 bg-white boderbottom"> Export Download Content <span id="blinkdata" style="margin-left:40em;font-wight:strong" class="blink">Generating Export Files</span></div> 
        <ul>
           <li><fluent-radio name="protectbkuppwd" id="protectbkuppwd"> Protect this backup with a password </fluent-radio></li>
           <li><fluent-radio name="dontexptpostrevisions" id="dontexptpostrevisions"> Do not export post revisions </fluent-radio></li>
           <li><fluent-radio name="dontexptsmedialibrary" id="dontexptsmedialibrary"> Do not export media library (files) </fluent-radio></li>
           <li><fluent-radio name="dontexptsthems" id="dontexptsthems"> Do not export themes (files) </fluent-radio></li>
           <li><fluent-radio name="dontexptmustuseplugs" id="dontexptmustuseplugs"> Do not export must-use plugins (files) </fluent-radio></li>
           <li><fluent-radio name="dontexptplugins" id="dontexptplugins"> Do not export plugins (files) </fluent-radio></li>
           <li><fluent-radio name="dbsql" id="dbsql"> Do not Export database (sql) </fluent-radio></li>
           <li><fluent-radio name="generatefile" id="generatefile"> All Content (wp-content) </fluent-radio></li>
        </ul>

    <div id="exportdownloadfile">
        <?php
        $wp_root_url=get_home_url();
        $wp_root_filepath=$wp_root_url."/wp-content/plugins/azure_app_service_migration/backupwebsite/zipfiles/";

        $wp_root_path = get_home_path();
        $dirname = $wp_root_path."/wp-content/plugins/azure_app_service_migration/backupwebsite/zipfiles/";

        $reportfiles = scandir($dirname, 1); 
        foreach ($reportfiles as $file) {
            if (substr($file, -4) == ".zip") {
                $folderpath = $wp_root_filepath;
                $finame=$folderpath.''.$file;
                print "<a style='color:#ffffff;' href='" . $folderpath ."" . $file . "' name='downloadfile' id='downloadfile' class='btn btn-primary btn-sm'>Download Export File - $file</a>"; 
            }
        }
        //$src = 'https://unpkg.com/@fluentui/web-components';
        $src = $wp_root_url."/wp-content/plugins/azure_app_service_migration/assets/node_modules/@fluentui/web-components/dist/web-components.js";
        ?>
        <div class="overlay"></div>
    </div>
</div>
<script type="module" src="<?php echo esc_url($src); ?>"></script>