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
		// password 
		var password=$("#password").val();
		$("#hiddenpassword").val(password);
		// Confirm password
		var confpassword=$("#confpassword").val();
		$("#hiddenconfpassword").val(confpassword);
		// Do not export post revisions 
		if($("#dontexptpostrevisions").prop('checked') == true){
			var dontexptpostrevisions=$("#dontexptpostrevisions").val();
			$("#hiddendontexptpostrevisions").val(dontexptpostrevisions);
		}
		// Do not export media library (files) 
		if($("#dontexptsmedialibrary").prop('checked') == true){
			var dontexptsmedialibrary=$("#dontexptsmedialibrary").val();
			$("#hiddendontexptsmedialibrary").val(dontexptsmedialibrary);
		}
		// Do not export themes (files) 
		if($("#dontexptsthems").prop('checked') == true){
			var dontexptsthems=$("#dontexptsthems").val();
			$("#hiddendontexptsthems").val(dontexptsthems);
		}
		// Do not export must-use plugins (files) 
		if($("#dontexptmustuseplugs").prop('checked') == true){
			var dontexptmustuseplugs=$("#dontexptmustuseplugs").val();
			$("#hiddendontexptmustuseplugs").val(dontexptmustuseplugs);
		}
		// Do not export plugins (files) 
		if($("#dontexptplugins").prop('checked') == true){
			var dontexptplugins=$("#dontexptplugins").val();
			$("#hiddendontexptplugins").val(dontexptplugins);
		}
		// Do not export database (sql)
		if($("#dbsql").prop('checked') == true){
			var dbsql=$("#dbsql").val();
			$("#hiddendbsql").val(dbsql);
		}
		// password and confirm password validations
		function pwdvalid(){
			if($("#password").val()==''){
				alert('Please Enter Password');
			}
			if($("#confpassword").val()==''){
				alert('Please Enter Password');
			}
		}
		// Form submition
		// pwdvalid();
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