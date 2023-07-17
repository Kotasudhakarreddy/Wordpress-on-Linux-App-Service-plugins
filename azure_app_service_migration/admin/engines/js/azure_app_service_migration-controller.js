jQuery(function ($) {
	var ajaxurl = azure_app_service_migration.ajaxurl;
  
	$("#blinkdata").hide();  
  
	function logCurrentFolder(folder) {
	  alert(folder);
	  var logMessage = "Exporting folder: " + folder;
	  document.getElementById("runtimeLog").value = logMessage;
	}
  
	// Ajax call before function
	function pagebeforeloadresonse() {
	  $("#downloadfile").hide();
	  //$("body").addClass("loading");
	  $("#blinkdata").show();
	}
	var blinkInterval;
	var blinkTimeout;
	// Processing event on button click
	$("#generatefile").click(function () {
		
		stopBlinking($("#exportdownloadfile"));
	  // Hide the exportdownloadfile element
	  $("#exportdownloadfile").hide();
    $('#downloadLink').hide();
  
	  // Disable the button and change text to indicate processing
	  $(this).prop("disabled", true).text("Generating Export...");
  
	  // Form submission
	  var postdata = $("#frm-chkbox-data").serialize();
	  postdata += "&action=admin_ajax_request&param=wp_filebackup";
	  $.post(ajaxurl, postdata, function (response) {
		var data = $.parseJSON(response);
		console.log(response);
		if (data.status == 1) {
		  alert(data.message);
		  $("#exportdownloadfile").show();
      $('#downloadLink').show().css('display', 'inline-block');

		  blinkElement("#exportdownloadfile");
		  $('#exportdownloadfile').load(window.location.href + ' #exportdownloadfile');
		} else {
		  alert(data.message);
		}
		// Enable the button and restore original text
		// $("#generatefile").prop('disabled', false).text('Generate Export File');
		
	  })
		.always(function () {
		  // Enable the button and restore original text
		  $("#generatefile").prop("disabled", false).text("Generate Export File");
		})
		.fail(function () {
		  // Enable the button and restore original text on error
		  $("#generatefile").prop("disabled", false).text("Generate Export File");
		});
	});
	
  
	function blinkElement(selector) {
		var element = $(selector);
		if (element.length > 0) {
		  // Start the blinking animation
		  startBlinking(element);
		}
	  }
	  
	  function startBlinking(element) {
		// Set the blink interval
		blinkInterval = setInterval(function() {
		  element.fadeOut(500, function() {
			$(this).fadeIn(500);
		  });
		}, 1000); // Adjust the interval as needed
	  
		// Schedule the stopBlinking function after 10 seconds
		blinkTimeout = setTimeout(function() {
		  stopBlinking(element);
		}, 10000); // 10 seconds (10000 milliseconds)
	  }

	  function stopBlinking(element) {
		// Clear the interval and timeout, and show the element
		clearInterval(blinkInterval);
		clearTimeout(blinkTimeout);
		element.stop().show();
	  }   
   
	// Add event listeners for drag and drop functionality
	$("#dropzone")
	  .on("dragover", function (e) {
		e.preventDefault();
		$(this).addClass("dragover");
	  })
	  .on("dragleave", function (e) {
		e.preventDefault();
		$(this).removeClass("dragover");
	  })
	  .on("drop", function (e) {
		e.preventDefault();
		$(this).removeClass("dragover");
  
		// Retrieve the dropped file
		var files = e.originalEvent.dataTransfer.files;
  
		// Check if any file is dropped
		if (files.length > 0) {
		  // Assign the dropped file to the file input
		  $("#importFile")[0].files = files;
		}
	  });
  
	$("#confpassword").on("keyup", function () {
	  var password = $("#password").val();
	  var confpassword = $("#confpassword").val();
	  if (password != confpassword) {
		$("#CheckPasswordMatch")
		  .html("Password does not match!")
		  .css("color", "red");
	  } else {
		$("#CheckPasswordMatch").html("Password match!").css("color", "green");
	  }
	});
  });
  