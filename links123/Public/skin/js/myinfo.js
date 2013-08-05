//
$(function() {
	//
	$("input[type='text']").each(function(index,domEle){
		$(domEle).attr("defaultvalue", $(domEle).val());
	});
	//
	$("input[type='text']").focus(function() {
	    if ( $(this).val() == $(this).attr("defaultvalue") ) {
	    	$(this).val('');
	    }
	});
	//
	$("input[type='text']").blur(function() {
	    if ( $(this).val() == "" ) {
	    	$(this).val($(this).attr("defaultvalue"));
	    }
	});
	
	/* 回车键响应 */
    $(".grzx_ncmm input[name='email']").keydown(function(event){
    	  if(event.keyCode==13) {
    		  $("#btn_email").trigger("click");
    	  }
    });
    // 邮箱
	$("#btn_email").click(function(){
		//
		var obj = $(".grzx_ncmm input[name='email']");
		if (obj.val() == "" || obj.val() == "请输入邮箱" || obj.val() == "请输入新邮箱") {
			alert("请输入邮箱.");
			obj.focus();
			return false;
		}
		var result = obj.val().match(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/);
		if (result == null) {
			alert("请正确填写邮箱.");
			obj.focus();
			return false;
		}
		//
		$.post(URL+"/saveEmail", {
			email: obj.val()
		}, 
		function(data){
			if ( data.indexOf("saveOK") >= 0 ) {
				alert("email提交成功！");
				window.location.reload();
			}else{
				alert(data);
			}
		}); 
	});
	
	/* 回车键响应 */
    $(".grzx_ncmm input[name='nickname']").keydown(function(event){
    	  if(event.keyCode==13) {
    		  $("#btn_nickname").trigger("click");
    	  }
    });
    // 昵称
	$("#btn_nickname").click(function(){
		//
		var obj = $(".grzx_ncmm input[name='nickname']");
		if (obj.val() == "" || obj.val() == "请输入新昵称") {
			alert("请输入新昵称.");
			obj.focus();
			return false;
		}
		//
		$.post(URL+"/saveNickname", {
			nickname: obj.val()
		}, 
		function(data){
			if ( data.indexOf("saveOK") >= 0 ) {
				alert("昵称提交成功！");
				window.location.reload();
			}else{
				alert(data);
			}
		}); 
	});
	
	/* 回车键响应 */
    $(".grzx_ncmm input[name='password']").keydown(function(event){
    	  if(event.keyCode==13) {
    		  $("#btn_password").trigger("click");
    	  }
    });
    // 密码
	$("#btn_password").click(function(){
		//
		var obj = $(".grzx_ncmm input[name='password']");
		if (obj.val() == "" || obj.val() == "请输入新密码") {
			alert("请输入新密码.");
			obj.focus();
			return false;
		}
		result = obj.val().match(/^[a-zA-Z0-9]{6,16}$/);
		if (result == null) {
			alert("密码必须为6-16个字母或数字.");
			obj.focus();
			return false;
		}
		//
		$.post(URL+"/savePassword", {
			password: obj.val()
		}, 
		function(data){
			if ( data.indexOf("saveOK") >= 0 ) {
				alert("密码提交成功！");
				window.location.reload();
			}else{
				alert(data);
			}
		}); 
	});
	
	/* 选择头像 */
	function openFaces() {
	    Shadowbox.open({
	        player:     "html",
	        title:      "请选择头像",
	        content:    $("#pop_box").html(),
	        height:     680,
	        width:      1030
	    });
	}
	//
	Shadowbox.init();
	//
	$("#btn_select_face").click(function(){
		openFaces();
	});
	//
	$(document).on('click', '.div_faces ul li', function(){
		var url = PUBLIC+"/Uploads/Faces/"+$(this).attr("title");
		$("#face_b").attr("src", url);
		$("#face_s").attr("src", url);
		$("#face").val($(this).attr("title"));
		$("#sb-nav-close").trigger("click");
	});
	/**$(".div_faces ul li").live("click",function(){
		var url = PUBLIC+"/Uploads/Faces/"+$(this).attr("title");
		$("#face_b").attr("src", url);
		$("#face_s").attr("src", url);
		$("#face").val($(this).attr("title"));
		$("#sb-nav-close").trigger("click");
	});**/
	
	/* 上传头像 */
	var options = { 
		beforeSubmit: showRequest,
		success: showResponse
	};
	//
	$(".file_frm").submit(function() {
		$(this).ajaxSubmit(options); 
		return false;
	});
	//
	$("#file_face").change(function(){
    	if ( $(this).val() != '' ) {
    		$(this).parent(".file_frm").submit();
    	}
    });
	//
	$("#file_face").css({top:$("#btn_sub_face").offset().top,left:$("#btn_sub_face").offset().left});
	//
	$("#btn_save_face").click(function(){
		//
		$.post(URL+"/saveFace", {
			face: $("#face").val()
		}, 
		function(data){
			if ( data.indexOf("saveOK") >= 0 ) {
				alert("头像设定成功！");
				window.location.reload();
			}else{
				alert(data);
			}
		}); 
	});
	
	//
	$("#btn_hide_save").click(function(){
		$("#btn_save_face").trigger("click");
	});
	
	/* face高度调节 */
	var img = new Image;
	img.onload = function(){
		if (img.width > img.height) {
			$("#face_b").css("margin-top",Math.floor((120-img.height)/2)+"px");
		}
	};
	img.src = $("#face_b").attr("src");
	//
	img = new Image;
	img.onload = function(){
		if (img.width > img.height) {
			$(".grzx_pic img").css("margin-top",Math.floor((120-img.height)/2)+"px");
		}
	};
	img.src = $(".grzx_pic img").attr("src");
	
});

//
function showRequest(formData, jqForm, options) { 
	//var queryString = $.param(formData);
	//alert('About to submit: \n\n' + queryString);
	$("#face_b").attr("src", PUBLIC+"/skin/images/jdt.gif");
	$("#face_b").css("padding-top","50px");
	return true;
}
//
function showResponse(responseText, statusText) {
	//alert('status: ' + statusText + '\n\nresponseText: \n' + responseText); 
	var tempary = responseText.split("|");
	var url = PUBLIC+"/Uploads/Faces/"+tempary[1];
	$("#face_b").attr("src", url);
	$("#face_b").css("padding-top","0px");
	//$("#face_s").attr("src", url);
	$("#face").val(tempary[1]);
	$("#btn_hide_save").focus();
	$("#btn_save_face").css("color","#FF0000");
	//$("#btn_save_face").trigger("click");
	//
	var img = new Image;
	img.onload = function(){
		if (img.width > img.height) {
			$("#face_b").css("margin-top",Math.floor((120-img.height)/2)+"px");
		}
	};
	img.src = $("#face_b").attr("src");
}

