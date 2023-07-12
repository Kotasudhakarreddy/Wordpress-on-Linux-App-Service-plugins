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
        <button type="button" class="btn btn-primary" id="importfile" onclick="handleImport()">Import</button>
      </div>
    </form>
    <!-- <div class="progress">
      <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
    </div> -->
    <div style="margin-top: 20px;">
      <input type="checkbox" name="caching_cdn" id="caching_cdn" style="margin-right: 8px; transform: scale(0.8);">
      <label for="caching_cdn" style="font-size: 14px;">Re-enable caching and/or CDN/AFD features</label>
    </div>
  </div>
</div>

<script type="text/javascript">
  function splitFile(file, chunkSize) {
    const chunks = [];
    const fileSize = file.size;
    let offset = 0;

    while (offset < fileSize) {
      const chunk = file.slice(offset, offset + chunkSize);
      chunks.push(chunk);
      offset += chunkSize;
    }

    return chunks;
  }

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
      document.getElementById('importFile').files = files;
    } else {
      fileInfo.textContent = "Drag and drop files here or click to select files.";
    }
  }

  function handleImport() {
    var ajaxurl = azure_app_service_migration.ajaxurl;
    var fileInput = document.getElementById('importFile');
    var fileInfo = document.getElementById('fileInfo');
    if (fileInput.files.length === 0) {
      fileInfo.textContent = 'Please select a file to import.';
      document.getElementById('dropzone').classList.add('error');
      return;
    }

    var formData = new FormData();
    // formData.append('importFile', fileInput.files[0]);
    formData.append('param', 'wp_ImportFile');

    var file = fileInput.files[0];
    var chunkSize = 5 * 1024 * 1024; // 5MB chunk size
    var chunks = splitFile(file, chunkSize);

    fileInfo.textContent = 'Importing...'; // Update the file info text

    // Upload each chunk sequentially
    var index = 0;

            function uploadChunk() {
              // console.log('uploadChunk is called');
              // console.log('chunk length', chunks.length);
        if (index >= chunks.length) {
            // Perform further actions after all chunks are uploaded
            $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'handle_combine_chunks', // Adjust the server-side action name
                param: 'wp_ImportFile'
            },
            success: function (response) {
                // Handle the success response after combining the chunks
                console.log(response);
                fileInfo.textContent = 'File imported successfully.';
            },
            error: function (xhr, status, error) {
                // Handle the error response after combining the chunks
                console.log(error);
                fileInfo.textContent = 'Failed to import file.';
            }
            });
            return;
        }
        
        var chunk = chunks[index];
        formData.set('fileChunk', chunk);
        formData.append("action", "handle_upload_chunk");
		formData.append("param", "wp_ImportFile_chunks");
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
            // Handle the success response
            // console.log(response);
            // console.log('POST');
            // console.log('Index number:', index);

            // Upload the next chunk
            index++;
            uploadChunk();
            },
            error: function (xhr, status, error) {
            // Handle the error response
            console.log(error);
            fileInfo.textContent = 'Failed to upload chunk.';
            }
        });
        }
            uploadChunk();
  }
</script>
