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

	//weather jack 2013.08.14
	var city = '%E4%B8%8A%E6%B5%B7'; //默认为上海
	var url = 'http://weather.news.sina.com.cn/chajian/iframe/weatherStyle0.html?city=';
	$.getScript("http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=js").done(function(script){
		city = encodeURI(remote_ip_info["city"]);
		$('#sinaWeatherToolIframe').attr('src',url+city);
	}).fail(function(){
		$('#sinaWeatherToolIframe').attr('src',url+city);
	});
	
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
	$(".newWin").click(function() {
		myWinOpen($(this).attr('url'), '', '');
	});

	// 时钟
	if ($("#J_today")[0]) {
		var invId = window.setInterval(function() {
			$("#J_today").text(dateFormat("MM月DD日 周W hh:mm:ss"));
		}, 1000);
	}

	// 直达框获得焦点
	$(".J_header_top").mouseover(function() {
		$("#direct_text").select();
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
		}
        
        $("#J_thl_div").css("top", top);
        //$("#J_thl_div").css("left", left);
	}
	
	//显示糖葫芦
	function thl_show() {
		if ( !$("#J_thl_div:visible")[0] ) {
			setThlPnt();
			$("#J_thl_div").css("display", "block");
		}
	}
	//隐藏糖葫芦
	function thl_hid() {
		if ( $("#J_thl_div:visible")[0] ) {
			$("#J_thl_div").css("display", "none");
		}
	}
	
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
		setThlPnt();
		thl_show();
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

	
	$("#myarea").click(function() {
        $.post(URL + "/count_myarea_open");
        openMyArea();
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

//---------------------------------------------------  
//日期格式化  
//格式 YYYY/yyyy/YY/yy 表示年份  
//MM/M 月份  
//W/w 星期  
//dd/DD/d/D 日期  
//hh/HH/h/H 时间  
//mm/m 分钟  
//ss/SS/s/S 秒  
//---------------------------------------------------  
function dateFormat(formatStr) {
	var d = new Date();
	var str = formatStr;
	var Week = ['日', '一', '二', '三', '四', '五', '六'];

	str = str.replace(/yyyy|YYYY/, d.getFullYear());
	str = str.replace(/yy|YY/, (d.getYear() % 100) > 9 ? (d.getYear() % 100).toString() : '0' + (d.getYear() % 100));

	str = str.replace(/MM/, d.getMonth() > 8 ? (d.getMonth() + 1).toString() : '0' + (d.getMonth() + 1));
	str = str.replace(/M/g, (d.getMonth() + 1));

	str = str.replace(/w|W/g, Week[d.getDay()]);

	str = str.replace(/dd|DD/, d.getDate() > 9 ? d.getDate().toString() : '0' + d.getDate());
	str = str.replace(/d|D/g, d.getDate());

	str = str.replace(/hh|HH/, d.getHours() > 9 ? d.getHours().toString() : '0' + d.getHours());
	str = str.replace(/h|H/g, d.getHours());
	str = str.replace(/mm/, d.getMinutes() > 9 ? d.getMinutes().toString() : '0' + d.getMinutes());
	str = str.replace(/m/g, d.getMinutes());

	str = str.replace(/ss|SS/, d.getSeconds() > 9 ? d.getSeconds().toString() : '0' + d.getSeconds());
	str = str.replace(/s|S/g, d.getSeconds());

	return str;
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

function openMyArea() {
    $("#sb-container #ul_myarea").sortable({
        placeholder: "ui-state-highlight"
    });
    $("#sb-container #ul_myarea").sortable({
        stop: function(event, ui) {
            var xx = $("#sb-container #ul_myarea").sortable("serialize");
            //更新我的地盘排序
            $.ajax({
                type: 'POST',
                url: URL + '/sortArealist',
                data: xx,
                success: function(msg) {
                    if (msg) {
                        msg = jQuery.parseJSON(msg);
                        if (msg['status'] == 'ok') {
                            var data = msg['data'];
                            var hide_myarea_html = "";
                            var myfave_html = "";
                            for (var i = 0; i < data.length; i++) {
                                hide_myarea_html += '<li id="area_' + data[i]['id'] + '" myid="' + data[i]['id'] + '" url="' + data[i]['url'] + '">' + data[i]['web_name'] + '</li>';
                                myfave_html += '<li><a myid="' + data[i]['id'] + '" target="_blank" href="'+APP+'Index/link_out?mod=myarea&url=' + data[i]['url'] + '">' + data[i]['web_name'] + '</a></li>';
                            }
                            $(".div_myarea ul").html(hide_myarea_html);
                            $("#myfave ul").html(myfave_html);
                        }
                    }
                }
            });
        }
    });
}