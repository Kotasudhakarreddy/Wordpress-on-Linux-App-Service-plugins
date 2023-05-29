<div class="col-md-11 mt-5">
    <div class="shadow p-3 mb-5 bg-body rounded">
        <div class="shadow-sm p-4 mb-4 bg-white boderbottom">Download Content</div> 
        <ul>
            <li><input class="form-check-input" type="checkbox" value="protectbkuppwd" name="protectbkuppwd" id="protectbkuppwd"> Protect this backup with a password</li>
            <li><input class="form-check-input" type="checkbox" value="dontexptspamcmt" name="dontexptspamcmt" id="dontexptspamcmt"> Do not export spam comments</li>
            <li><input class="form-check-input" type="checkbox" value="dontexptpostrevisions" name="dontexptpostrevisions" id="dontexptpostrevisions"> Do not export post revisions</li>
            <li><input class="form-check-input" type="checkbox" value="dontexptsmedialibrary" name="dontexptsmedialibrary" id="dontexptsmedialibrary"> Do not export media library (files)</li>
            <li><input class="form-check-input" type="checkbox" value="dontexptsthems" name="dontexptsthems" id="dontexptsthems"> Do not export themes (files)</li>
            <li><input class="form-check-input" type="checkbox" value="dontexptmustuseplugs" name="dontexptmustuseplugs" id="dontexptmustuseplugs"> Do not export must-use plugins (files)</li>
            <li><input class="form-check-input" type="checkbox" value="dontexptplugins" name="dontexptplugins" id="dontexptplugins"> Do not export plugins (files)</li>
            <li><input class="form-check-input" type="checkbox" value="dbsql" name="dbsql" id="dbsql"> Do not Export database (sql)</li>
            <li><input class="form-check-input" type="checkbox" value="dontreplaceemaildomain" name="dontreplaceemaildomain" id="dontreplaceemaildomain"> Do not replace email domain (sql)</li>
        </ul>
    <button type="button" name="generatefile" id="generatefile" class="btn btn-primary">Generate Export File</button><br/><br/>
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
        print "<a style='color:#ffffff;' href='" . $folderpath ."" . $file . "' name='downloadfile' id='downloadfile' class='btn btn-success'>Download Export File - $file</a>"; 
    }
}
//$src = 'https://unpkg.com/@fluentui/web-components';
$src = $wp_root_url."/wp-content/plugins/azure_app_service_migration/assets/node_modules/@fluentui/web-components/dist/web-components.js";
?>
<div class="overlay"></div>
</div>
</div>
<script type="module" src="<?php echo esc_url($src); ?>"></script>