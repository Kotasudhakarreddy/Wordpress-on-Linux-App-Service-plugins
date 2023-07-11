jQuery(function ($) {
	var ajaxurl = azure_app_service_migration.ajaxurl;

	$("#blinkdata").hide();

	// Hide function
	function pageloadresonse() {
		$("body").removeClass("loading");
		$("#blinkdata").hide();
		$("#downloadfile").show();
		location.reload();
	}

	// Ajax call before function
	function pagebeforeloadresonse() {
		$("#downloadfile").hide();
		$("body").addClass("loading");
		$("#blinkdata").show();
	}

	// Processing event on button click
	$("#generatefile").click(function () {
		// Form submission
		pagebeforeloadresonse();
		var postdata = $("#frm-chkbox-data").serialize();
		postdata += "&action=admin_ajax_request&param=wp_filebackup";
		jQuery.post(ajaxurl, postdata, function (response) {
			var data = jQuery.parseJSON(response);
			console.log(response);
			if (data.status == 1) {
				alert(data.message);
				pageloadresonse();
			} else {
				alert(data.message);
			}
		});
	});

	// Add event listeners for drag and drop functionality
	$("#dropzone").on('dragover', function (e) {
		e.preventDefault();
		$(this).addClass('dragover');
	}).on('dragleave', function (e) {
		e.preventDefault();
		$(this).removeClass('dragover');
	}).on('drop', function (e) {
		e.preventDefault();
		$(this).removeClass('dragover');

		// Retrieve the dropped file
		var files = e.originalEvent.dataTransfer.files;

		// Check if any file is dropped
		if (files.length > 0) {
			// Assign the dropped file to the file input
			$("#importFile")[0].files = files;
		}
	});

	// Handle the button click event
	$("#importfile").click(function (e) {
		$("#filestatus").empty();
		e.preventDefault(); // Prevent the default form submission
		var importButton = document.getElementById('importfile');
		var fileInput = document.getElementById('importFile');
		var fileInfo = document.getElementById('fileInfo');
		var originalText = importButton.textContent; // Store the original text

		importButton.disabled = true; // Disable the button before the AJAX request
		importButton.textContent = "Importing..."; // Change the button text during import

		var formData = new FormData($("#frm-Import-file")[0]);
		formData.append("action", "admin_ajax_request");
		formData.append("param", "wp_ImportFile");

		$.ajax({
			url: ajaxurl,
			type: "POST",
			data: formData,
			processData: false,
			contentType: false,
			beforeSend: function () {
				importButton.disabled = true; // Disable the button before sending the request
				importButton.textContent = "Importing..."; // Change the button text during import
			},
			success: function (response) {
				var data = JSON.parse(response);
				if (data.status == 1) {
					$("#filestatus").html(data.message).removeClass("text-danger").addClass("text-success").css({
						"color": "green",
						"font-weight": "bold",
						"margin-bottom": "1em"
					});
				} else {
					$("#filestatus").html(data.message).removeClass("text-success").addClass("text-danger").css({
						"color": "red",
						"font-weight": "bold",
						"margin-bottom": "1em"
					});
				}

			},
			complete: function () {
				importButton.disabled = false; // Enable the button after the request is completed
				importButton.textContent = originalText; // Change the button text back to the original
				fileInput.value = ''; // Clear the file input
				fileInfo.textContent = "Drag and drop files here or click to select files."; // Reset the drop area text
			}
		});
	});

	$("#confpassword").on('keyup', function () {
		var password = $("#password").val();
		var confpassword = $("#confpassword").val();
		if (password != confpassword) {
			$("#CheckPasswordMatch").html("Password does not match!").css("color", "red");
		} else {
			$("#CheckPasswordMatch").html("Password match!").css("color", "green");
		}
	});
});
