<div class="col-md-11 mt-5">
    <div class="shadow p-3 mb-5 bg-body rounded">
        <div class="shadow-sm p-4 mb-4 bg-white boderbottom"> Export Download Content <span id="blinkdata" style="margin-left:16em;font-weight: bold;" class="blink">Generating Export Files</span></div>
        <form id="frm-chkbox-data">
            <ul>
                <li><fluent-checkbox value="true" class="exportdata" name="exportdata[]" id="prtbkuppwd"> Protect this backup with a password </fluent-radio></li>
                <div id="prtbkuppwdfields" style="margin-left:2em;">
                    <fluent-text-field type="password" appearance="filled" name="password" id="password" placeholder="Enter a password"></fluent-text-field>
                    <fluent-text-field type="password" appearance="filled" name="confpassword" id="confpassword" placeholder="Repeat the password"></fluent-text-field>
                    <div style="margin-top: 3px;margin-bottom: 6px;" id="CheckPasswordMatch"></div>
                </div>
            <li><fluent-checkbox class="exportdata" name="dontexptpostrevisions" id="dontexptpostrevisions" value="dontexptpostrevisions"> Do not export post revisions </fluent-radio></li>
            <li><fluent-checkbox class="exportdata" name="dontexptsmedialibrary" id="dontexptsmedialibrary" value="dontexptsmedialibrary"> Do not export media library (files) </fluent-radio></li>
            <li><fluent-checkbox class="exportdata" name="dontexptsthems" id="dontexptsthems" value="dontexptsthems"> Do not export themes (files) </fluent-radio></li>
            <li><fluent-checkbox class="exportdata" name="dontexptmustuseplugs" id="dontexptmustuseplugs" value="dontexptmustuseplugs"> Do not export must-use plugins (files) </fluent-radio></li>
            <li><fluent-checkbox class="exportdata" name="dontexptplugins" id="dontexptplugins" value="dontexptplugins"> Do not export plugins (files) </fluent-radio></li>
            <li><fluent-checkbox class="exportdata" name="donotdbsql" id="dbsql" value="donotdbsql"> Do not Export database (sql) </fluent-radio></li>
        </ul>
        <fluent-button class="generatefile" name="generatefile" id="generatefile" appearance="accent">Generate Export File</fluent-button>

       </form>

    <div id="exportdownloadfile">
        <?php
$wp_root_url = get_home_url();
$wp_root_filepath = $wp_root_url . "/wp-content/plugins/azure_app_service_migration/";

$dirname = AZURE_APP_SERVICE_MIGRATION_PLUGIN_PATH;

$reportfiles = scandir($dirname, 1);
foreach ($reportfiles as $file) {
    if (substr($file, -4) == ".zip") {
        $folderpath = $wp_root_filepath;
        $finame = $folderpath . '' . $file;
        print "<a style='color:#ffffff;margin-top:2em' href='" . $folderpath . "" . $file . "' name='downloadfile' id='downloadfile' class='btn btn-success btn-sm'>Download Export File - $file</a>";
    }
}
//$src = 'https://unpkg.com/@fluentui/web-components';
$src = $wp_root_url . "/wp-content/plugins/azure_app_service_migration/assets/node_modules/@fluentui/web-components/dist/web-components.js";
?>
        <div class="overlay"></div>
    </div>
</div>
<script type="module" src="<?php echo esc_url($src); ?>"></script>

<script type="text/javascript" language="javascript">
    $(document).ready(function(){
        $("#prtbkuppwdfields").hide();
    })

    $('#prtbkuppwd').click(function(){
        if ($(this).prop('checked')) {
            $("#prtbkuppwdfields").hide();
            $("#password").val("");
            $("#confpassword").val("");
        }else{
            $("#prtbkuppwdfields").show();
        }
    });

</script>