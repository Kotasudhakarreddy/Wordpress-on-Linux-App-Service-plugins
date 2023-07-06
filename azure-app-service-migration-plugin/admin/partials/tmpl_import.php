<div class="col-md-11 mt-5">
    <div class="shadow p-3 mb-5 bg-body rounded">
	<div class="shadow-sm p-4 mb-4 bg-white boderbottom">Import</div>
        <div style="text-align: center; margin-top: 20px;">
            <fluent-button appearance="accent" id="dialogOpener">Import</fluent-button>
        </div>
	<fluent-dialog id="defaultDialog" hidden  trap-focus modal>
  <div style="margin: 20px;">
    <h2>Import Status</h2>
    <fluent-button id="dialogCloser" appearance="accent" tabindex="0">Cancel</fluent-button>
  </div>
</fluent-dialog>
        <div style="margin-top: 20px;">
            <input type="checkbox" name="caching_cdn" id="caching_cdn" style="margin-right: 8px; transform: scale(0.8);">
            <label for="caching_cdn" style="font-size: 14px;">Re-enable caching and/or CDN/AFD features</label>
        </div>
    </div>
</div>

<div id="exportdownloadfile">                                                                                            
        <?php                                                                           
        $wp_root_url=get_home_url();                     
        $src = $wp_root_url."/wp-content/plugins/azure-app-service-migration-plugin/assets/node_modules/@fluentui/web-components/dist/web-components.js";
        ?>                                                                                                 
</div>  

<script type="module" src="<?php echo esc_url($src); ?>"></script>


<script type="text/javascript" language="javascript">                                                                               
$(document).ready(function() {                                                                                                                                                                                         
});                                                                                                                                 
document.getElementById("dialogOpener").addEventListener("click",function(){                                                   
    document.getElementById('defaultDialog').hidden = false;                                                                                             
});                                                                                                                             
document.getElementById("dialogCloser").addEventListener("click", function() {                                                  
    document.getElementById('defaultDialog').hidden = true;     
});                                                                                               
</script>
