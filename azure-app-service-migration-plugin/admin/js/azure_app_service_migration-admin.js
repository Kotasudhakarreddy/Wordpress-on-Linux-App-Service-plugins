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

	// Processing event on button click
	jQuery(document).on("click", "#generatefile", function(){
		$("#downloadfile").hide();
		$("body").addClass("loading");
		$("#blinkdata").show();
		var postdata="action=admin_ajax_request&param=wp_filebackup";
		jQuery.post(ajaxurl,postdata, function(response){
			var data=jQuery.parseJSON(response);
			//console.log(response);
			if(data.status==1){
				alert(data.message);
				pageloadresonse();
			}else{
				alert(data.message);
			}
		});
	});

	// Do not Export database(sql)
	$('#dbsql').click(function () {
		if ($(this).prop('checked')) {
			$("#downloadfile").hide();
			$("body").addClass("loading");
			var postdata="action=admin_ajax_request&param=wp_donotexportsql";
			jQuery.post(ajaxurl,postdata, function(response){
				var data=jQuery.parseJSON(response);
				//console.log(response);
				if(data.status==1){
					alert(data.message);
					$("body").removeClass("loading"); 
					location.reload();
					$("#downloadfile").show();
				}else{
					alert(data.message);
				}
			});
		}else {
		   // do what you need here         
		   //alert("Unchecked");
		}
	});

	// Protect this backup with a password
	$('#protectbkuppwd').click(function () {
		if ($(this).prop('checked')) {
			$("#downloadfile").hide();
			$("body").addClass("loading");
			var postdata="action=admin_ajax_request&param=wp_protectbkuppwd";
			jQuery.post(ajaxurl,postdata, function(response){
				var data=jQuery.parseJSON(response);
				//console.log(response);
				if(data.status==1){
					$("body").removeClass("loading"); 
					alert(data.message);
					location.reload();
					$("#downloadfile").show();
				}else{
					alert(data.message);
				}
			});
		}else {
			// do what you need here         
			//alert("Unchecked");
		}
	});

	// Do not export media library (files)
	$('#dontexptsmedialibrary').click(function () {
		if ($(this).prop('checked')) {
			$("#downloadfile").hide();
			$("body").addClass("loading");
			var postdata="action=admin_ajax_request&param=wp_dontexptsthems";
			jQuery.post(ajaxurl,postdata, function(response){
				var data=jQuery.parseJSON(response);
				//console.log(response);
				if(data.status==1){
					$("body").removeClass("loading"); 
					alert(data.message);
					location.reload();
					$("#downloadfile").show();
				}else{
					alert(data.message);
				}
			});
		}else {
			// do what you need here         
			//alert("Unchecked");
		}
	});

	// Do not export media library (files)
	$('#dontexptsthems').click(function () {
		if ($(this).prop('checked')) {
			$("#downloadfile").hide();
			$("body").addClass("loading"); 
			var postdata="action=admin_ajax_request&param=wp_dontexptsthems";
			jQuery.post(ajaxurl,postdata, function(response){
				var data=jQuery.parseJSON(response);
				//console.log(response);
				if(data.status==1){
					$("body").removeClass("loading"); 
					alert(data.message);
					location.reload();
					$("#downloadfile").show();
				}else{
					alert(data.message);
				}
			});
		}else {
			// do what you need here         
			//alert("Unchecked");
		}
	});

	// Do not export spam comments
	$('#dontexptspamcmt').click(function () {
		if ($(this).prop('checked')) {
			$("#downloadfile").hide();
			$("body").addClass("loading"); 
			var postdata="action=admin_ajax_request&param=wp_dontexptspamcmt";
			jQuery.post(ajaxurl,postdata, function(response){
				var data=jQuery.parseJSON(response);
				//console.log(response);
				if(data.status==1){
					$("body").removeClass("loading"); 
					alert(data.message);
					setTimeout(function(){
						location.reload();
					}, 1000);
					//$("#downloadfile").load();
					$("#downloadfile").show();
				}else{
					alert(data.message);
				}
			});
		}else {
			// do what you need here         
			//alert("Unchecked");
		}
	});

	// Do not export plugins
	$('#dontexptplugins').click(function () {
		if ($(this).prop('checked')) {
			$("#downloadfile").hide();
			$("body").addClass("loading"); 
			var postdata="action=admin_ajax_request&param=wp_dontexptplugins";
			jQuery.post(ajaxurl,postdata, function(response){
				var data=jQuery.parseJSON(response);
				//console.log(response);
				if(data.status==1){
					$("body").removeClass("loading"); 
					alert(data.message);
					//$("#downloadfile").load();
					location.reload();
					$("#downloadfile").show();
				}else{
					alert(data.message);
				}
			});
		}else {
			// do what you need here         
			// alert("Unchecked");
		}
	});

	// Do not export post revisions
	$('#dontexptpostrevisions').click(function () {
		if ($(this).prop('checked')) {
			$("#downloadfile").hide();
			$("body").addClass("loading"); 
			var postdata="action=admin_ajax_request&param=wp_dontexptpostrevisions";
			jQuery.post(ajaxurl,postdata, function(response){
				var data=jQuery.parseJSON(response);
				//console.log(response);
				if(data.status==1){
					$("body").removeClass("loading"); 
					alert(data.message);
					//$("#downloadfile").load();
					location.reload();
					$("#downloadfile").show();
				}else{
					alert(data.message);
				}
			});
		}else {
			// do what you need here         
			//alert("Unchecked");
		}
	});

	// Do not export post revisions
	$('#dontreplaceemaildomain').click(function () {
		if ($(this).prop('checked')) {
			$("#downloadfile").hide();
			$("body").addClass("loading"); 
			var postdata="action=admin_ajax_request&param=wp_dontreplaceemaildomain";
			jQuery.post(ajaxurl,postdata, function(response){
				var data=jQuery.parseJSON(response);
				//console.log(response);
				if(data.status==1){
					$("body").removeClass("loading"); 
					alert(data.message);
					//$("#downloadfile").load();
					location.reload();
					$("#downloadfile").show();
				}else{
					alert(data.message);
				}
			});
		}else {
			// do what you need here         
			//alert("Unchecked");
		}
	});
});
// $(document).on({
// 	ajaxStart: function(){
// 		$("body").addClass("loading"); 
// 	},
// 	ajaxStop: function(){ 
// 		$("body").removeClass("loading"); 
// 	}    
// });