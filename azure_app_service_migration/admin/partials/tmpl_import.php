<div class="col-md-11 mt-5">
  <div class="shadow p-3 mb-5 bg-body rounded">
    <div class="shadow-sm p-4 mb-4 bg-white boderbottom">Import Content</div>
    <div id="filestatus" class="text-center"></div>
    <form id="frm-Import-file" enctype="multipart/form-data">
      <div id="dropzone" onclick="document.getElementById('importFile').click();" ondragover="handleDragOver(event);" ondragleave="handleDragLeave(event);" ondrop="handleDrop(event);" style="cursor: pointer;">
        <input type="file" name="importFile" id="importFile" style="display: none;" onchange="handleFileChange(event);">
        <p id="fileInfo">Drag and drop files here or click to select files.</p>
        <div id="progressBarContainer" class="progress" style="display: none;">
          <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0px;" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
      </div>
      <div style="text-align: center;">
        <button type="button" class="btn btn-primary" id="importfile" onclick="handleImport()">Import</button>
      </div>
      <div id = "statusdiv"><p id="statusText">Import status text</p></div>
    </form>
    <div style="margin-top: 20px;">
      <input type="checkbox" name="caching_cdn" id="caching_cdn" value="caching_cdn" style="margin-right: 8px; transform: scale(0.8);">
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
    formData.append('param', 'wp_ImportFile');

    var file = fileInput.files[0];
    var chunkSize = 5 * 1024 * 1024; // 5MB chunk size
    var chunks = splitFile(file, chunkSize);

    fileInfo.textContent = 'Importing...'; // Update the file info text

    var index = 0;

    // To Do: Commenting out this function for now since it may prevent Import/Export in some cases
    /*function verifyMigrationStatus(retryCount) {
      // Set max retry count for getting status from server
      maxRetryCount = 5;

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'get_migration_status', // Adjust the server-side action name
        },
        success: function(response) {
          // Handle the success response after combining the chunks
          console.log(response);
          
          // To Do (Sudhakar): Display popup message here when import/export already in progress
          // Currently updating the statusText Value
          if (response.type == 'status')
          {
            // Update status text value
            statusText.textContent = 'Import/Export process is already running on the server! Please wait a while and try again.';
          }
          else
          {
            // Update status text value
            statusText.textContent = 'Starting Migration.';

            // Start Import process (with uploading zip file) if there is no Import/Export in progress
            document.getElementById('progressBarContainer').style.display = 'block'; // Display the progress bar
            uploadChunkWithRetry();
          }
        },
        error: function(xhr, status, error) {
          // Retry the updateStatus call if the maximum number of retries is not reached
          if (retryCount < maxRetryCount) {
            updateStatusText(retryCount+1);
          } else {
            // Max retries reached, display error message
            statusText.textContent = 'Failed to connect to server. Import can still be in progress';
          }
        }
      });
    }
    */

      // Makes a GET request to the server to get IMPORT status
    function updateStatusText(retryCount) {
      // Set max retry count for getting status from server
      maxRetryCount = 15;

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'get_migration_status', // Adjust the server-side action name
        },
        success: function(response) {
          // Handle the success response after combining the chunks
          console.log(response);
          
          // To Do (Sudhakar): Display response.message in status box.
          // Currently updating a text field (statusText) in the page. 
          
          // Update status text value
          statusText.textContent = response.message;
          
          // Call updateStatusText recursively only if migration is still in progress
          if (response.type == 'status')
          {
            updateStatusText(0);
            return;
          }
        },
        error: function(xhr, status, error) {
          // Handle the error response
          console.log(error);

          // Retry the updateStatus call if the maximum number of retries is not reached
          if (retryCount < maxRetryCount) {
            updateStatusText(retryCount+1);
          } else {
            // Max retries reached, display error message
            statusText.textContent = 'Failed to connect to server. Import can still be in progress';
          }
        }
      });
    }

    function uploadChunkWithRetry() {
      console.log('uploadChunk is called');
      console.log('chunk length', chunks.length);
      if (index >= chunks.length) {
        // Perform further actions after all chunks are uploaded
        // Get the checkbox element
        var cachingCdnCheckbox = document.getElementById('caching_cdn');

        var retries = 0;
        var maxRetries = 3;
        var retryDelay = 1000;

        function combineChunksWithRetry() {
          $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
              action: 'handle_combine_chunks', // Adjust the server-side action name
              param: 'wp_ImportFile',
              caching_cdn: cachingCdnCheckbox.checked,
            },
            success: function(response) {
              // Handle the success response after combining the chunks
              console.log(response);
              fileInfo.textContent = 'File imported successfully.';
              document.getElementById('progressBarContainer').style.display = 'none'; // Hide the progress bar
            },
            error: function(xhr, status, error) {
              // Handle the error response after combining the chunks
              console.log(error);
              fileInfo.textContent = 'Failed to import file.';

              // Retry the combineChunks call if the maximum number of retries is not reached
              if (retries < maxRetries) {
                retries++;
                setTimeout(combineChunksWithRetry, retryDelay);
              } else {
                // Max retries reached, display error message
                fileInfo.textContent = 'Failed to import file after multiple retries.';
                document.getElementById('progressBarContainer').style.display = 'none'; // Hide the progress bar
              }
            }
          });
        }

        combineChunksWithRetry();
        // Commenting this for now to avoid getting realtime status
        //updateStatusText(0);
        return;
      }

      var chunk = chunks[index];
      formData.set('fileChunk', chunk);
      formData.append("action", "handle_upload_chunk");
      formData.append("param", "wp_ImportFile_chunks");

      var retries = 0;
      var maxRetries = 3;
      var retryDelay = 1000;

      function uploadChunk() {
        $.ajax({
          url: ajaxurl,
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          xhr: function() {
            var xhr = new window.XMLHttpRequest();
            // Upload progress
            xhr.upload.addEventListener("progress", function(evt) {
              if (evt.lengthComputable) {
                var percentComplete = (index / chunks.length) * 100;
                var progressBarWidth = Math.floor(percentComplete) + '%';
                document.getElementById('progressBar').style.width = progressBarWidth;
              }
            }, false);
            return xhr;
          },
          success: function(response) {
            console.log(response);
            console.log('POST');
            console.log('Index number:', index);

            index++;
            retries = 0; // Reset the retry counter
            uploadChunkWithRetry();
          },
          error: function(xhr, status, error) {
            console.log(error);
            fileInfo.textContent = 'Failed to upload chunk.';

            // Retry the upload if the maximum number of retries is not reached
            if (retries < maxRetries) {
              retries++;
              setTimeout(uploadChunk, retryDelay);
            } else {
              // Max retries reached, display error message
              fileInfo.textContent = 'Failed to upload chunk after multiple retries.';
              document.getElementById('progressBarContainer').style.display = 'none'; // Hide the progress bar
            }
          }
        });
      }

      uploadChunk();
    }

    document.getElementById('progressBarContainer').style.display = 'block'; // Display the progress bar
    uploadChunkWithRetry()
  }
</script>
