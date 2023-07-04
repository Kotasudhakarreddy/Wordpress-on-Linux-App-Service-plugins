$(document).ready(function(){
	$("#confpassword").on('keyup',function(){
		var password=$("#password").val();
		var confpassword=$("#confpassword").val();
		if(password!=confpassword){
			$("#CheckPasswordMatch").html("Password does not match !").css("color","red");
		}else{
			$("#CheckPasswordMatch").html("Password match !").css("color","green");
		}
	});
});

jQuery(function(){
	var ajaxurl=azure_app_service_migration.ajaxurl;
	$("#blinkdata").hide();
	// hide funtion
	function pageloadresonse(){
		$("body").removeClass("loading"); 
		$("#blinkdata").hide();
		$("#downloadfile").show();
		location.reload();
	}

	// Ajax call before function
	function pagebeforeloadresonse(){
		$("#downloadfile").hide();
		$("body").addClass("loading");
		$("#blinkdata").show();
	}

	// Processing event on button click
	$("#generatefile").click(function(){		
		// Form submition
		pagebeforeloadresonse();
		var postdata=$("#frm-chkbox-data").serialize();
		postdata += "&action=admin_ajax_request&param=wp_filebackup";
		jQuery.post(ajaxurl,postdata, function(response){
			var data=jQuery.parseJSON(response);
			console.log(response);
			//console.log(postdata);
			if(data.status==1){
				alert(data.message);
				pageloadresonse();
			}else{
				alert(data.message);
			}
		});
	});
});