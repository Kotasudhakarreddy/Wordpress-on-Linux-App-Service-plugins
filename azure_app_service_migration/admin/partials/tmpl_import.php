<div class="col-md-11 mt-5">
    <div class="shadow p-3 mb-5 bg-body rounded">
        <div class="shadow-sm p-4 mb-4 bg-white boderbottom">Import Content</div>
        <div id="filestatus" class="text-center"></div>
        <form id="frm-Import-file" enctype="multipart/form-data">
            <div id="dropzone" onclick="document.getElementById('importFile').click();" ondragover="handleDragOver(event);" ondragleave="handleDragLeave(event);" ondrop="handleDrop(event);" style="cursor: pointer;">
                <input type="file" name="importFile" id="importFile" style="display: none;" onchange="handleFileChange(event);">
                <p id="fileInfo">Drag and drop files here or click to select files.</p>
            </div>
            <div style="text-align: center;">
                <fluent-button appearance="accent" id="importfile" onclick="handleImport();">Import</fluent-button>
            </div>
        </form>
        <div style="margin-top: 20px;">
            <input type="checkbox" name="caching_cdn" id="caching_cdn" style="margin-right: 8px; transform: scale(0.8);">
            <label for="caching_cdn" style="font-size: 14px;">Re-enable caching and/or CDN/AFD features</label>
        </div>
    </div>
    <div id="exportdownloadfile">
        <?php
        $wp_root_url = get_home_url();
        $wp_root_filepath = $wp_root_url . "/wp-content/plugins/azure_app_service_migration/";
        $src = $wp_root_url . "/wp-content/plugins/azure_app_service_migration/assets/node_modules/@fluentui/web-components/dist/web-components.js";
        ?>
    </div>
</div>
<script type="module" src="<?php echo esc_url($src); ?>"></script>
<script type="text/javascript">
    function handleFileChange(event) {
        var fileInput = event.target;
        var fileInfo = document.getElementById('fileInfo');
        if (fileInput.files.length > 0) {
            fileInfo.textContent = fileInput.files[0].name;
        } else {
            fileInfo.textContent = "Drag and drop files here or click to select files.";
        }
    }

    function handleDragOver(event) {
        event.preventDefault();
        event.stopPropagation();
        event.target.classList.add('highlight');
    }

    function handleDragLeave(event) {
        event.preventDefault();
        event.stopPropagation();
        event.target.classList.remove('highlight');
    }

    function handleDrop(event) {
        event.preventDefault();
        event.stopPropagation();
        event.target.classList.remove('highlight');

        var files = event.dataTransfer.files;
        var fileInfo = document.getElementById('fileInfo');
        if (files.length > 0) {
            fileInfo.textContent = files[0].name;
        } 
        else {
            fileInfo.textContent = "Drag and drop files here or click to select files.";
        }
    }

    function handleImport() {
        var fileInput = document.getElementById('importFile');
        var fileInfo = document.getElementById('fileInfo');
        if (fileInput.files.length === 0) {
            fileInfo.textContent = 'Please select a file to import.';
            document.getElementById('dropzone').classList.add('error');
            return;
        }     
    }
</script>
