/**
* Index 3.0 
*
* @author slate date: 2013-09-09
*/ 

var APP = $('#J_APP').val();
var URL = $('#J_URL').val();
var PUBLIC = $('#J_PUBLIC').val();

$(function() {

	$('.skins').on('click', function(){
		$('#J_Skins').attr('href', '__PUBLIC__/Demo/skins/light/style.css');
	});
	
	//保存自留地网址
	$('.J_myarea_web_save').click(function() {
		var id = $('#J_myarea_id').val();
		var web_name= $('#J_myarea_web_name').val();
		var url= $('#J_myarea_web_url').val();
		var t = getLength(url);

		$('#J_myarea_web_name').trigger('keyup');

		if(t == 0){
			$('#J_myarea_tip').text('链接为空!').css('color', '#f00');
			return false;
		}

		$.post(URL + "/updateArealist", {
				id: id,
				web_name: web_name,
				url: url
			},
			function(data) {
				if (data.indexOf("updateOK") >= 0) {
					var myarea_web_obj = $('.J_myarea_div ul li[id="'+id+'"] span');
					$('.J_myarea_div ul li[id="'+id+'"] span b').text(web_name);
					var new_url = myarea_web_obj.attr('url').replace(myarea_web_obj.attr('data-url'), url);
					myarea_web_obj.attr('url', new_url);
					myarea_web_obj.attr('data-url', url);
					$('#J_myarea_tip').text('保存成功!').css('color', '#d20015');
					$('#J_myarea_tip').show();

				} else {
					$('#J_myarea_tip').text('保存失败!').css('color', '#f00');
					$('#J_myarea_tip').show();
				}
				setTimeout(function(){
					$('#J_myarea_tip').hide();
					$('.J_zld_edit_box').hide();
				}, 2000);
		});
	});
	
	//登录窗口
	$('.J_signin').click(function(){
		 $('#J_signin_form').dialog('open');
		 $('.lk-dialog-login').show();
		 $('.ui-dialog-titlebar').hide();
		 return false;
	});
	
	//注册窗口
	$('.J_signup').click(function(){
		$("#verifyImg").trigger("click");
		$('#J_signup_form').dialog('open');
		$('.lk-dialog-reg').show();
		$('.ui-dialog-titlebar').hide();
		return false;
	});
	
	$('#J_forgetpass').click(function(){
		$('#J_forgetpass_form').dialog('open');
		$('.reglogin').show();
		$('.ui-dialog-titlebar').hide();
		return false;
	});
	
	$('#J_signin_form').dialog({
		autoOpen: false,
		height: 478,
		width: 587,
		modal: true,
		resizable: false,
		open: function(){
			setTimeout(function(){$('#J_signin_user').select();}, 20);
		}
	});
	
	$('#J_signup_form').dialog({
		autoOpen: false,
		height: 478,
		width: 587,
		modal: true,
		resizable: false,
		open: function(){
			setTimeout(function(){$('#J_signup_user').select();}, 20);
		}
	});
	
	$('#J_forgetpass_form').dialog({
		autoOpen: false,
		height: 323,
		width: 587,
		modal: true,
		resizable: false,
		open: function(){
			setTimeout(function(){$('#J_forgetpass_email').select();}, 20);
		}
	});
	
	$('.J_signin_close').click(function(){
		$('#J_signin_form').dialog('close');
	});
	
	$('.J_signup_close').click(function(){
		$('#J_signup_form').dialog('close');
	});
	
	$('.J_forgetpass_close').click(function(){
		$('#J_forgetpass_form').dialog('close');
	});
	
	$('#verifyImg').click(function(){
		var timenow = new Date().getTime();
		$(this).attr("src", APP+'Verify?'+timenow);

	});
	
	$('.J_signin_submit').click(function(){
		
		var username = $('#J_signin_user').val();
		var password = $('#J_signin_password').val();
		var auto_login = $('#auto_login').attr('checked');
		
		if (!username || username == '帐号') {
			
			alert('帐号不能为空');
			return false;
		}
		
		if (!password) {
			alert('密码不能为空');
			return false;
		}
		
		var data = {"username":username,"password":password,"auto_login": (auto_login=='checked' ? 1 : 0)};
		
		$.post(APP + "Members/Login/checkLogin", data, 
			function(data){
				if ( data.indexOf("loginOK") >= 0 ) {
					if(window.opener){
						window.opener.location.reload();
					}
					window.location.href = APP+"Index";
				}else{
					alert(data);
				}
			}); 
	});
	
	$('.J_signup_submit').click(function(){
		
		var username = $('#J_signup_user').val();
		var password = $('#J_signup_password').val();
		var repassword = $('#J_signup_repassword').val();
		var verify = $('#vcode').val();
		
		if (!username || username == '昵称') {
			
			alert('昵称不能为空');
			return false;
		}
		
		if (!password) {
			alert('密码不能为空');
			return false;
		}
		
		if (password != repassword) {
			alert('密码不一致');
			return false;
		}
		
		if (!verify) {
			alert('验证码不能为空');
			return false;
		}
		
		var data = {"nickname":username,"password":password,"verify":verify};
		
		$.post(APP + "Members/Register/saveReg", data, 
			function(data){
				if ( data.indexOf("regOK") >= 0 ) {
					window.location.href = APP+"Members/Index/";
				} else {
					alert(data);
				}
		}); 
	});
	
	$('.J_forgetpass_submit').click(function(){
		
		var email = $('#J_forgetpass_email').val();
		if (!email) {
			alert("请输入邮箱");
			return false;
		}
		var result = email.match(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/);
		if (result == null) {
			alert("请正确输入邮箱.");
			return false;
		}
		
		$.post(URL + "/missPwd", {
			email: email
			}, 
			function(data){
				if ( data.indexOf("sendOK") >= 0 ) {
					var mailserver = data.split('|');
					alert("请进入您的账户邮箱获取新密码。");
					
					window.open("http://" + mailserver[1]);
				}else {
					alert(data);
				}
			});
	});
	

	$('#J_signin_password').keypress(function(event) {
		if (event.keyCode == 13) {
			$(".J_signin_submit").trigger("click");
			return false;
		}
	});

	$('#verifyImg').keypress(function(event) {
		if (event.keyCode == 13) {
			$(".J_signup_submit").trigger("click");
			return false;
		}
	});

	$('#J_forgetpass_email').keypress(function(event) {
		if (event.keyCode == 13) {
			$(".J_forgetpass_submit").trigger("click");
			return false;
		}
	});

	$('#J_myarea_web_url').keypress(function(event) {
		if (event.keyCode == 13) {
			$(".J_myarea_web_save").trigger("click");
			return false;
		}
	});
	
});

