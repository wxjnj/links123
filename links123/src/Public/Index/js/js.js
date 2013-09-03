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

	// 弹出页
	$(".newWin").live('click', function() {
		myWinOpen($(this).attr('url'), '', '');
	});

	// 直达框
	$(document).on('click', function(){
		$('#direct_text').val($('#direct_text').attr('txt')).removeClass('ipson');
	});
	$(".J_header_top").on('mouseenter', function(){
		var tag = $.trim($('#direct_text').val());
		if(tag == $('#direct_text').attr('txt')){
			$("#direct_text").select().addClass('ipson');
		}else{
			$("#direct_text").addClass('ipson');
		}
	}).on('mouseleave', function(){
		var tag = $.trim($('#direct_text').val());
		if(tag == '' || tag == $('#direct_text').attr('txt')){
			$('#search_text').select();
			$('#direct_text').removeClass('ipson');
		}
	});
	$("#direct_text").on('click', function(){
		var tag = $.trim($('#direct_text').val());
		if (tag == $('#direct_text').attr('txt')){
			$('#direct_text').val('').addClass('ipson');
		}
		return false;
	});

	$('.J_direct_submit').on('click', function(){
		$("#frm_drct").trigger('submit');
		$("#direct_text")[0].focus();
		return false;
	});
	$("#frm_drct").on('submit', function(){
		var tag = $.trim($('#direct_text').val());
		if (tag == '' || tag == $('#direct_text').attr('txt')){
			return false;
		}
		$('#direct_text').select();
	});

	// 编辑自留地
	$('.J_myarea').click(function() {
		$('.J_myarea_div').addClass('zld-edit');
		$('.J_myarea_div ul li span').removeClass('newWin');
		
		$('#J_sortable').sortable({
			update: function (event, ui) {  

				$('.zld .bg-none').removeClass('bg-none');
				
				var zldli = $('.zld ul li');
				zldli.eq(0).addClass('bg-none');
				zldli.eq(15).addClass('bg-none');
				
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

	//自留地竖线边框
	var zldli = $('.zld ul li');
	zldli.eq(0).addClass('bg-none');
	zldli.eq(15).addClass('bg-none');
	
	//编辑自留地网址
	$('.zld-edit ul li span').live('click', function() {
		$('#J_myarea_id').val($(this).attr('data-id'));
		$('#J_myarea_web_name').val($(this).text());
		$('#J_myarea_web_url').val($(this).attr('data-url'));
		$('.J_zld_edit_box').show();
		$('#J_myarea_web_name').select();
		$('#J_myarea_tip').text('');

	});
	
	$('#J_myarea_web_url').mouseover(function() {
		$(this).select();
	});
	
	//绑定事件
	$('#J_myarea_web_name').on('keyup', function(event){
		var web_name = $(this).val();
		var t = getLength(web_name);
		if(t==0||t>8){
			$('.utips').css({'color':'#f00'});
		}else{
			$('.utips').css({'color':'#999'});
		}
	});

	var getLength = function(s){
		s = s.toString();
		var len = 0;
		for (var i = 0; i < s.length; i++) {
			len++;
			if (s.charCodeAt(i) >= 255) {
				len++;
			}
		}
		return len;
	}

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

	//应用部分选中
	$('.apps li').on('mouseleave mouseenter', function(){
		$(this).toggleClass('on');
	});
	
	//登录窗口
	$('#J_signin').click(function(){
		 $('#J_signin_form').dialog('open');
		 $('.reglogin').show();
		 $('.ui-dialog-titlebar').hide();
		 return false;
	});
	
	//注册窗口
	$('.J_signup').click(function(){
		$('#J_signin_form').dialog('close');
		$("#verifyImg").trigger("click");
		$('#J_signup_form').dialog('open');
		$('.reglogin').show();
		$('.ui-dialog-titlebar').hide();
		return false;
	});
	
	$('#J_forgetpass').click(function(){
		$('#J_signin_form').dialog('close');
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
	
	THL.init();
	
	/** $换肤 **/
	$('.skins-style li').on('mouseover', function(){
		var id = $(this).data('id');
		$(this).toggleClass('on');
		$('.sa'+id).show().siblings().hide();
	});
	$('.skins-all li').on('click', function(){
		var bg = $(this).data('bg');
		$('body').css('background-image', 'url('+bg+')');
		$(this).addClass('added').siblings().removeClass('added');
		$(this).find('.imgb').hide();
		$(this).find('s').hide();
		$.post(URL + "/updateSkin", {'skinId': $(this).data('id')});
	}).on('mouseenter', function(){
		$(this).find('.imgb').show();
		$(this).find('s').show();
	}).on('mouseleave', function(){
		$(this).find('.imgb').hide();
		$(this).find('s').hide();
	});
	
	$('.skins').on('mouseleave', function(){
		$('.skins-all dd').hide();
	});
	/** 换肤$ **/
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

/* 糖葫芦 */
var THL = {
	conf : {
		topnm : 40,
		topex : 65
	},
	init : function(){
		var self = this;

		//重新加载页面清除keyword 清除浏览器未清除的文本框的值
		$.cookies.set('keyword', '');
		$("#search_text").val('').select(); //选中文本框
		
		$('.thl').mouseover(function(){ $('#J_thl_div').show(); }); //移入糖葫芦区域 显示糖葫芦
		$('.J_thl_area').mouseleave(function(){ $('#J_thl_div').hide(); }); //移除糖葫芦主区域 隐藏糖葫芦
		
		$(".J_thlz a").click(function(){ //糖葫芦籽点击
			$(this).addClass("on").siblings("a").removeClass("on");
			$("#btn_search").trigger("click");
			return false;
		});
		
		$("#J_thl_div a").click(function(){ //糖葫芦点击
			$(this).addClass("on").siblings("a").removeClass("on");
			var index = $(this).index();
			$(".J_thlz:eq(" + index + ")").show().siblings(".J_thlz").hide();
			$(".J_thlz a").removeClass("on");
			$(".J_thlz:eq(" + index + ")").find("a:first").addClass("on");
			return false;
		});

		$("#search_text").blur(function(){ //文本框失去焦点 如 内容为空 清除keyword
			if ($(this).val() == '') {
				$.cookies.set('keyword', '');
			}
		});
		
		$("#search_text").keyup(function(event){ //文本框输入内容 设置糖葫芦 位置
			$('#J_thl_div').show();
			self.setpos();
		});

		$(".thl").mouseenter(function(){ $("#search_text").select(); }); //移入 糖葫芦 选中 文本

		$('#search_text').on('click', function(){
			if (!$.cookies.get('keyword')) {
				var key  = $.trim($("#search_text").val());
				if(key != ''){
					$(this).data('key', key);
					$(this).val(key);
				}
			} else {
				
				$(this).data('key', '');
			}
		});

		$('#search_text').on('webkitspeechchange', function(){ //onwebkitspeechchange
			var key = $(this).data('key');
			if(key && key != ''){
				var v = $(this).val();
				if(v.indexOf(key) == 0){
					$(this).val(v.replace(key, key+' '));
					$(this).data('key', '');
				}
			}
			self.setpos();
		}); 

		$(document).mouseup(function(ev){ // 搜索文本框始终获取焦点
			if ( document.activeElement.tagName == "INPUT" 
				|| document.activeElement.tagName == "TEXTAREA" 
				|| document.activeElement.tagName == "IFRAME"
				|| document.activeElement.id == "direct_text"
				|| document.activeElement.id == "search_text"
			) {
				return;
			}
			var txt = '';
			if (window.getSelection){ // mozilla FF 
				txt = window.getSelection();
			}else if(document.getSelection){
				txt = document.getSelection();
			}else if(document.selection){ //IE
				txt = document.selection.createRange().text;
			}
			if (txt == '') { $("#search_text").select(); } //未划选文本 划选 文本框
			if ($.cookies.get('keyword')){ $("#search_text").select(); } //有keyword时 直接划选 文本框
		});

		$("#btn_search").click(function() { //单击搜索按钮
			$("#search_text").select();
			var keyword  = $.trim($("#search_text").val());
			$.cookies.set('keyword', keyword); //保存keyword
			keyword = keyword.replace('http://','');
			keyword = encodeURIComponent(keyword);
			var url = $(".J_thlz a.on").attr("url").replace('keyword', keyword);
			var tid = $(".J_thlz a.on").attr("tid");
			self.go(url, tid, keyword);
			return false;
		});
	},
	go : function(url, tid, keyword){
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
		}
	},
	setpos : function(){
		var top, self = this;
		if($("#search_text").val() != ''){ //文本框有值调整位置
			top = self.conf.topex;
		}else{
			top = self.conf.topnm;
			$('#J_thl_div').hide(); //没值隐藏
		}
		$("#J_thl_div").css("top", top);		
	}
}

var HelpMouse = {
	init: function(){
		var self = this;
		var isSearchTxtSelected = false;

		$(document).on('mousemove', function(ev){
			var isNeedHelp = 1;
			$('.ui-dialog').each(function(){
				if($(this).is(":visible")){
					isNeedHelp ? isNeedHelp = 0 : '';
				}
			});
			$('.fancybox-wrap').each(function(){
				if($(this).is(":visible")){
					isNeedHelp ? isNeedHelp = 0 : '';
				}
			});
			if(!isNeedHelp){ return false; }
			var mousePos = self.getcoords(ev);
			if(mousePos.y < 70){
				if($('#direct_text').val() == $('#direct_text').attr('txt')){
					$('#direct_text').select().addClass('ipson');
					isSearchTxtSelected = false;
					if($.trim($('#search_text').val()) ==""){
						$('#J_thl_div').hide();
					}
				}
			}
			if(mousePos.y > 100 && mousePos.y < 200){
				$('#direct_text').val($('#direct_text').attr('txt')).removeClass('ipson');
				if(!isSearchTxtSelected){
					$('#search_text').select().trigger('mouseenter');
					isSearchTxtSelected = true;
				}
			}
			if(mousePos.y > 290){
				if($.trim($('#search_text').val()) ==""){
					$('#search_text').select().trigger('mouseleave');
					isSearchTxtSelected = false;
				}
			}
		});
	},
	getcoords: function(ev){
		if(ev.pageX || ev.pageY){ 
			return { x: ev.pageX, y: ev.pageY }; 
		} 
		return{ 
			x: ev.clientX + document.body.scrollLeft - document.body.clientLeft, 
			y: ev.clientY + document.body.scrollTop - document.body.clientTop 
		}; 
	}
}
HelpMouse.init();

