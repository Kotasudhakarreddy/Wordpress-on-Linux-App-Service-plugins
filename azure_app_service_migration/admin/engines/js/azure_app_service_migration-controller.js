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
		//$("body").addClass("loading");
		$("#blinkdata").show();
	}

	// Processing event on button click
	$("#generatefile").click(function () {

		// Disable the button and change text to indicate processing
		$(this).prop('disabled', true).text('Generating Export...');

		// Form submission
		var postdata = $("#frm-chkbox-data").serialize();
		postdata += "&action=admin_ajax_request&param=wp_filebackup";
		$.post(ajaxurl, postdata, function (response) {
			var data = $.parseJSON(response);
			console.log(response);
			if (data.status == 1) {
				alert(data.message);
				pageloadresonse();
			} else {
				alert(data.message);
			}

			// Enable the button and restore original text
			// $("#generatefile").prop('disabled', false).text('Generate Export File');
		}).always(function() {
			// Enable the button and restore original text
			$("#generatefile").prop('disabled', false).text('Generate Export File');
		}).fail(function() {
			// Enable the button and restore original text on error
			$("#generatefile").prop('disabled', false).text('Generate Export File');
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
