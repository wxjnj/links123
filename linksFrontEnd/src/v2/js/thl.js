$(function(){
	/* 糖葫芦 */
	var top = $("#search_text").offset().top + Math.floor(($("#search_text").height() - $("#J_thl_div").height())/2)+1;
	
	//调整糖葫芦位置
	function setThlPnt() {
       
		var top = $("#search_text").offset().top + Math.floor(($("#search_text").height() - $("#J_thl_div").height())/2)+1;
        var left1 = $("#search_text").offset().left + ($("#search_text").width() - $("#J_thl_div").width())/2;
        var left2 = $("#search_text").offset().left + ($("#search_text").width() - $("#J_thl_div").width()) - 1;
        var left = 0;
        
        //根据id=J_str_len的span来计算search_text的 width
        //$('#J_str_len').text($('#search_text').val());
        
        //当输入或复制的关键词触碰糖葫芦,糖葫芦就跳到搜索框下方
        if ( $("#search_text").val() != "" ) {
        	//left = left2;
        	top = $("#search_text").offset().top + $("#search_text").height() + 4;//4=padding*2
			left = left1;
		} else {
			left = left1;
		}
        
        $("#J_thl_div").css("top", top);
        $("#J_thl_div").css("left", left);
	}
	
	window.onresize = setThlPnt;
	// 定位
	$("#J_thl_div").css("top", top);
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
	
	//
	var fjx_top = $("#div_blank").offset().top + $("#div_blank").height();
	$("#div_blank").mousemove(function(ev){
		if (ev.pageY <= fjx_top) {
			thl_show();
			$('.head_tools_bar').show();
		}
	});
	
	$('#head_tools').mouseover(function() {
		$('.head_tools_bar').show();
	});
	$('#head_tools').mouseout(function() {
		$('.head_tools_bar').hide();
	});
	$('#J_logonav').mouseout(function() {
		$('.head_tools_bar').hide();
	});
	
	//
	$(".thlz").mouseover(function() {
		thl_hid();
	});
	//
	$(".search_buttom").mouseover(function() {
		thl_hid();
	});
	// 双岛一线
	$(".home_cont_left").hover(function(){
		thl_hid();
		$("#J_thl_div dl a").eq(0).trigger("click");
	});
	//
	$(".right_sider").hover(function(){
		thl_hid();
		$("#J_thl_div dl a").eq(0).trigger("click");
	});
	
	$(".header_top").mouseover(function() {
		$('.head_tools_bar').hide();
		thl_hid();
    });

	// 第一个糖葫芦去背景
	$("#J_thl_div dl:first").css("background-position","-6px 6px");
	
    //糖葫芦籽点击事件
    $(".thlz dl a").click(function(){
            $(this).addClass("light").parent("dl").siblings("dl").children("a").removeClass("light");
            $("#btn_search").trigger("click");
    });
            
	// 功能
	$("#J_thl_div dl a").click(function(){
        
		$(this).addClass("light").parent('dl').siblings("dl").children("a").removeClass("light");
        var index = $(this).parent("dl").index();
        $(".search .thlz:eq("+index+")").show().siblings(".thlz").hide().each(function(){
            $(this).find(".light").removeClass("light");
        })
        if($(".search .thlz:eq("+index+")").find(".light").size()<1){
            $(".search .thlz:eq("+index+")").find("a:first").addClass("light");
        }
        return false;
	});
	
	//
	$("#J_thl_div dl a.light").trigger("click");

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
	
	$('#rch_sch').mousemove(function(){
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
	
	//
	$(".logonav").mouseover(function(ev){
		if (ev.pageY > 58) {
			$("#search_text").select();
		}
	});
	
	$('#J_logonav').mouseover(function(){
		$('.head_tools_bar').show();
		$("#search_text").select();
	});
	window.focus();
	
	
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
	
	/* ctrl+,ctrl- */
	$(document).keydown(function(event){
		if( event.ctrlKey && ( event.keyCode==107 || event.keyCode==187 || event.keyCode==61 || event.keyCode==109 || event.keyCode==189 || event.keyCode==173 ) ) {
			window.setTimeout(function(){setThlPnt();},1000);
        }
	});

	/* 搜索 */ 

	$("#btn_search").click(function() {
//		if(parent.document.getElementById('searchFrameSet')){
//			parent.document.getElementById('searchFrameSet').rows="71,*";
//			$(".header_top,#head_tools").hide(500);
//			$(".show_header").show();
//			$("#J_thl_div").css("top", "1000px");
//		}
		$("#search_text").select();
		$.cookies.set( 'keyword', $("#search_text").val() );
		var keyword  = $.trim($("#search_text").val());
		keyword = keyword.replace('http://','');
		var re = /。/g;             // 创建正则表达式模式。
		
		//keyword = keyword.replace(/&/g, '%26');
		//keyword = keyword.replace(/\+/g, '%2B');
		//keyword = keyword.replace(/#/g, '%23');
		//十六进制的转义
		keyword = encodeURIComponent(keyword);
		
		if ($("#J_thl_div dl a.light").text() == "网") {
			window.open(URL + "/wang/q/" + keyword);
		} else {
			var url = $(".thlz dl a.light").attr("url");
			
			var tid = $(".thlz dl a.light").attr("tid");
			
			//TODO 少儿关键词参数被替换临时修复
			if (tid == 21) {
				
				url = url.replace('{keyword}', keyword);
			} else {
				url = url.replace('keyword', keyword);
			}
			
			//
			if (window.location.href.indexOf("/Thl/top") > 0) { // 框架页
				if (tid == '4' || tid == '26' || tid == '40' || tid == '58' || tid == '110' || tid == '117') { // 谷歌、美试、啪啪、PP、Quora
					
					$.post(URL + "/thl_count", {tid : tid}, function() {});
					window.open("http://" + url);
				
				} else if (tid == '10' || tid == '20') { // 另客、维修
					
					window.open(url);
				} else {
					
					$.post(URL + "/thl_count", {tid : tid}, function() {});
					parent.document.getElementById('main').src = "http://" + url;
				}
			} else {
				if (tid == '4' || tid == '40' || tid == '58' || tid == '110' || tid == '117') { // 谷歌、美试、啪啪、PQuora
					
					$.post(URL + "/thl_count", {tid : tid}, function() {});
					window.open("http://" + url);
					
				} else if (tid == '10' || tid == '20') { // 另客、维修
					if ($("#you_are_master")[0]) {
						window.open(url);
					} else {
						window.location.href = url;
					}
				} else {
					if ($("#you_are_master")[0]) {
						
						url = APP + "Thl/index";
						//window.open(url);
						//因window.open会被浏览器阻止，所以才用表单提交
						var searchFormObj = $('#searchForm');
						
						$('#J_thl').val($("#J_thl_div dl a.light").text());
						$('#J_tid').val(tid);
						$('#J_q').val(keyword);
						
						searchFormObj.attr('action', url);
						searchFormObj.attr('target', '_blank');
						searchFormObj.submit();
						searchFormObj.attr('action', '');
						searchFormObj.attr('target', '');
						$("#search_text").select();
						return false;
					} else {
						url = APP + "Thl/index?thl="
								+ $("#J_thl_div dl a.light").text()
								+ "&tid=" + tid + "&q="
								+ keyword;
						url = encodeURI(url);
						window.location.href = url;
					}
				}
			}
		}
	});
//	$(".show_header").click(function(){
//		thl_hid();
//		parent.document.getElementById('searchFrameSet').rows="130,*";
//		$(".header_top,#head_tools").show(500);
//		$(this).hide();
//	});

	$("#search_text").mouseenter(function(){
		setThlPnt();
	});
	$("#J_thl_div").mouseleave(function(){
		thl_hid();
	});
	// 头部框架获得焦点
	if ( window.location.href.indexOf("/Thl/top") > 0 ) {
		parent.document.getElementById('top').focus();
	}

});