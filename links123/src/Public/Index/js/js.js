/**
* New Index Js
*
* @author slate date: 2013-08-20
*/ 

var w = null;
var APP = $("#js_APP").val();
var URL = $("#js_URL").val();
var PUBLIC = $("#js_PUBLIC").val();

//关闭浏览器
var browserName = navigator.appName;
if (browserName == "Netscape") {
	function closeme() {
		window.open('', '_parent', '');
		window.close();
	}
} else {
	if (browserName == "Microsoft Internet Explorer") {
		function closynoshowsme() {
			window.opener = "whocares";
			window.close();
		}
	}
}

$(function() {

	// 设为首页
	$(".a_setHome").click(function() {
		setHome('http://www.links123.cn');
	});

	// tip标签
	//$(".tip").tipTip({maxWidth: "auto", edgeOffset: 3, defaultPosition: "top"});

	// 关闭本页
	$("#btn_close_me").click(function() {
		closeme();
	});

	// 弹出页
	$(".newWin").live('click', function() {
		myWinOpen($(this).attr('url'), '', '');
	});

	// 直达框获得焦点
	$(".J_header_top").mouseover(function() {
		$("#direct_text").select();
	});
	
	$('.J_header_top').mouseout(function() {
		$('#search_text').select();
		$('#direct_text').val($('#direct_text').attr('txt'));
	});
	
	// 直达回车键响应 
	$("#direct_text").keydown(function(event) {
		if (event.keyCode == 13) {
			var tag = $.trim($(this).val());
			if (tag != '') {
				$("#direct_text").select();
				//$("#frm_drct").submit();
			}
		}
	});
	
	/* 糖葫芦 */
	var top = $("#search_text").offset().top + Math.floor(($("#search_text").height() - $("#J_thl_div").height())/2)+1;
	
	//调整糖葫芦位置
	function setThlPnt() {
       
		var top = 64;
        var left1 = $("#search_text").offset().left + ($("#search_text").width() - $("#J_thl_div").width())/2;
        var left2 = $("#search_text").offset().left + ($("#search_text").width() - $("#J_thl_div").width()) - 1;
        var left = 0;
        
        //当输入或复制的关键词触碰糖葫芦,糖葫芦就跳到搜索框下方
        if ( $("#search_text").val() != "" ) {
        	top = 85;
			left = left1;
		} else {
			left = left1;
			$('#J_thl_div').hide();
		}
        
        $("#J_thl_div").css("top", top);
        //$("#J_thl_div").css("left", left);
	}
	
	$('.thl').mouseover(function(){
		$('#J_thl_div').show();
	});
	
	$('.thl').mouseleave(function(){
		$('#J_thl_div').hide();
	});
	
    //糖葫芦籽点击
    $(".J_thlz a").click(function(){
    	
            $(this).addClass("on").siblings("a").removeClass("on");
            
            $("#btn_search").trigger("click");
    });
            
	//糖葫芦点击
	$("#J_thl_div a").click(function(){
        
		$(this).addClass("on").siblings("a").removeClass("on");
		
        var index = $(this).index();
        
        $(".J_thlz:eq(" + index + ")").show().siblings(".J_thlz").hide();
        
        $(".J_thlz a").removeClass("on");
        $(".J_thlz:eq(" + index + ")").find("a:first").addClass("on");
        
        return false;
	});
	
	// F5清key
	$(document).keydown(function(event){
		if (event.keyCode == 116) {	// F5
			$.cookies.set( 'keyword', '');
		}
	});
	
	// 点击logo清key
	$(".logo a").click(function(){
		$.cookies.set( 'keyword', '');
	});
	
	// 清key
	$("#search_text").blur(function(){
		if ($(this).val()=='') {
			$.cookies.set( 'keyword', '');
		}
	});
	
	$("#search_text").keyup(function(){
		$('#J_thl_div').show();
		setThlPnt();
	});
	
	// 回车键响应 
	$("#search_text").keypress(function(event){
    	  if(event.keyCode==13) {
    		  //$(this).select();
    		  $("#btn_search").trigger("click");
    		  $("#search_text").select();
    		  return false;
    	  }
    });
	
	//
	$("#search_text").mouseover(function(){
		$("#search_text").select();
		return false;
	});
	
	$("#search_text").select();
	
	// 搜索文本框始终获取焦点
	$(document).mouseup(function(ev){
		//
		if ( document.activeElement.tagName == "INPUT" 
			|| document.activeElement.tagName == "TEXTAREA" 
			|| document.activeElement.tagName == "IFRAME" 
			|| document.activeElement.className == "ding"
			|| document.activeElement.className == "cai" 
			|| window.location.href.indexOf("suggestion") > 0  ) {
			return;
		}
		if ( document.activeElement.tagName == "BODY" ) {
			if ($("#frm_say")[0]) {
				var fw_top = $("#frm_say").offset().top;
				var fw_bottom = fw_top + 200;
				var fw_left = $("#frm_say").offset().left;
				var fw_right = fw_left + 700;
				if ( ev.pageX > fw_left && ev.pageX < fw_right && ev.pageY > fw_top && ev.pageY < fw_bottom ) {
					return;
				}				
			}
		}
		//
		var txt = '';
		if (window.getSelection) { // mozilla FF 
			txt = window.getSelection();
		} else if (document.getSelection) {
			txt = document.getSelection();
		} else if (document.selection) { //IE
			txt = document.selection.createRange().text;
		}
		//
		if ( txt == '' ) {
			$("#search_text").select();
		}
		
		if ($.cookies.get( 'keyword')) {
			$("#search_text").select();
		}
	});
	
	/* 搜索 */ 

	$("#btn_search").click(function() {
		
		$("#search_text").select();
		
		var keyword  = $.trim($("#search_text").val());
		$.cookies.set( 'keyword', keyword);
		
		keyword = keyword.replace('http://','');
		
		//十六进制的转义
		keyword = encodeURIComponent(keyword);
		
		var url = $(".J_thlz a.on").attr("url");
		url = url.replace('keyword', keyword);
		var tid = $(".J_thlz a.on").attr("tid");
		
		if (tid == '4' || tid == '40' || tid == '58' || tid == '110' || tid == '117') { // 谷歌、美试、啪啪、PQuora
			
			$.post(URL + "/thl_count", {tid : tid}, function() {});
			window.open("http://" + url);
			
		} else if (tid == '10' || tid == '20') { // 另客、维修
				window.open(url);
		} else {
				
				url = APP + "Thl/index";
				//window.open(url);
				//因window.open会被浏览器阻止，所以才用表单提交
				var searchFormObj = $('#searchForm');
				
				$('#J_thl').val($("#J_thl_div a.on").text());
				$('#J_tid').val(tid);
				$('#J_q').val(keyword);
				
				searchFormObj.attr('action', url);
				searchFormObj.attr('target', '_blank');
				searchFormObj.submit();
				searchFormObj.attr('action', '');
				searchFormObj.attr('target', '');
				$("#search_text").select();
				return false;
		}
	});

	// 编辑自留地
	$('.J_myarea').click(function() {

		$('.J_myarea_div').addClass('zld-edit');
		$('.J_myarea_div ul li span').removeClass('newWin');
		
		$('#J_sortable').sortable({
			update: function (event, ui) {  

				$.post(URL + '/sortArealist', {'area' : $(this).sortable('toArray')});
		   }  
	    });
		$('#J_sortable').sortable('enable');
    });
	
	//保存自留地
	$('.J_myarea_close').click(function() {
		$('#J_sortable').sortable('disable');
		$('.J_myarea_div').removeClass('zld-edit');
		$('.J_myarea_div ul li span').addClass('newWin');
		$('.J_zld_edit_box').hide();
	});
	
	//自留地hover状态
	$('.zld-edit ul li').live('mouseover', function() {
		
		$(this).addClass('on').siblings('li').removeClass('on');
	});
	
	//编辑自留地网址
	$('.zld-edit ul li span').live('click', function() {
		$('#J_myarea_id').val($(this).attr('data-id'));
		$('#J_myarea_web_name').val($(this).text());
		$('#J_myarea_web_url').val($(this).attr('data-url'));
		$('.J_zld_edit_box').show();
		$('#J_myarea_web_name').select();
	});
	
	$('#J_myarea_web_url').mouseover(function() {
		$(this).select();
	});
	
	//保存自留地网址
	$('.J_myarea_web_save').click(function() {
		var id = $('#J_myarea_id').val();
		var web_name= $('#J_myarea_web_name').val();
		var url= $('#J_myarea_web_url').val();
		$.post(URL + "/updateArealist", {
		        id: id,
		        web_name: web_name,
		        url: url
		    },
		    function(data) {
		        if (data.indexOf("updateOK") >= 0) {
		        	var myarea_web_obj = $('.J_myarea_div ul li[id="'+id+'"] span');
		        	myarea_web_obj.text(web_name);
		        	var new_url = myarea_web_obj.attr('url').replace(myarea_web_obj.attr('data-url'), url);
		        	myarea_web_obj.attr('url', new_url);
		        	myarea_web_obj.attr('data-url', url);
		        	$('#J_myarea_tip').text('保存成功!');
		        	$('#J_myarea_tip').show();

		        } else {
		            $('#J_myarea_tip').text('保存成功!');
		        	$('#J_myarea_tip').show();
		        }
		        setTimeout(function(){
		        	$('#J_myarea_tip').hide();
	        		$('.J_zld_edit_box').hide();
	        	}, 2000);
	    });
	});
	
	//登录窗口
	$('#J_signin').click(function(){
		 $('#J_signin_form').dialog('open');
		 $('.reglogin').show();
		 $('.ui-dialog-titlebar').hide();
		 $('#J_signin_user').select();
		 return false;
	});
	
	//注册窗口
	$('.J_signup').click(function(){
		 $('#J_signin_form').dialog('close');
		 $("#verifyImg").trigger("click");
		 $('#J_signup_form').dialog('open');
		 $('.reglogin').show();
		 $('.ui-dialog-titlebar').hide();
		 $('#J_signup_user').select();
		 
		 return false;
	});
	
	$('#J_forgetpass').click(function(){
		 $('#J_signin_form').dialog('close');
		 $('#J_forgetpass_form').dialog('open');
		 $('.reglogin').show();
		 $('.ui-dialog-titlebar').hide();
		 $('#J_forgetpass_email').select();
		 
		 return false;
	});
	
	$('#J_signin_form').dialog({
	      autoOpen: false,
	      height: 463,
	      width: 587,
	      modal: true,
	});
	
	$('#J_signup_form').dialog({
	      autoOpen: false,
	      height: 463,
	      width: 587,
	      modal: true
	});
	
	$('#J_forgetpass_form').dialog({
	      autoOpen: false,
	      height: 308,
	      width: 587,
	      modal: true
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
	
	$('.regbtn').mouseover(function(){
		if (!$(this).hasClass('J_forgetpass_submit')) {
			$('.reglogin-bd').addClass('regon');
		}
	});
	
	$('.regbtn').mouseout(function(){
		$('.reglogin-bd').removeClass('regon');
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

// 设为首页
function setHome(url) {
	if (document.all) {
		document.body.style.behavior = 'url(#default#homepage)';
		document.body.setHomePage(url);
	}
	else if (window.sidebar) {
		if (window.netscape) {
			try {
				netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
			} catch (e) {
				alert("该操作被浏览器拒绝, 如果想启用该功能, 请在地址栏内输入 about:config, 然后将项 signed.applets.codebase_principal_support 值改为 true.");
			}
		}
		//
		if (window.confirm("你确定要设置" + url + "为首页吗？") == 1) {
			var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
			prefs.setCharPref('browser.startup.homepage', url);
		}
	}
}

// 弹出页
function myWinOpen(theURL, winName, features) {
	if (w != undefined && isOpen()) {
		w.close();
	}
	w = window.open(theURL);
}
//
function isOpen() {
	try {
		w.document;
		return true;
	}
	catch (ex) {
	}
	return false;
}

/* cookie */
function getCookie(name) {
	var search;
	search = name + "=";
	offset = document.cookie.indexOf(search);
	if (offset != -1) {
		offset += search.length;
		var end = document.cookie.indexOf(";", offset);
		if (end == -1)
			end = document.cookie.length;
		return unescape(document.cookie.substring(offset, end));
	} else
		return "";
}
//
function setCookie(name, value) {
	document.cookie = name + "=" + value + "; path=/;";
}

