$(function(){
	THL.Init();
//	Theme.Init();
});


var THL = {
	conf : {
		topnm : 34,
		topex : 64
	},
	Init : function(){
		var self = this;

		// F5清key
		$(document).keydown(function(event){
			if (event.keyCode == 116) {	// F5
				$.cookies.set( 'keyword', '');
				$("#search_text").val('');
			}
		});
		
		//$.cookies.set('keyword', '');
		$("#search_text").select(); //选中文本框
		self.setpos();
		
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
			var url = $(".J_thlz a.on").attr("url");
			var tid = $(".J_thlz a.on").attr("tid");
			if (tid == 21) {
				
				url = url.replace('{keyword}', keyword);
			} else {
				url = url.replace('keyword', keyword);
			}
			self.go(url, tid, keyword);
			return false;
		});
	},
	go : function(url, tid, keyword){
		if (tid == '4' || tid == '26' || tid == '40' || tid == '58' || tid == '110' || tid == '117') { // 谷歌、美试、啪啪、PQuora
			$.post(URL + "/thl_count", {tid : tid}, function() {});
			window.open("http://" + url);
		} else if (tid == '10' || tid == '20') { // 另客、维修
			window.open(url);
		} else {	
			$.post(URL + "/thl_count", {tid : tid}, function() {});
			parent.document.getElementById('main').src = "http://" + url;
			$("#search_text").select();
		}

	},
	setpos : function(){
		var top, self = this;
		if($("#search_text").val() != ''){ //文本框有值调整位置
			top = self.conf.topex;
			$("#J_thl_div").css("top", top).removeClass('cate-in');	
		}else{
			top = self.conf.topnm;
			$('#J_thl_div').hide(); //没值隐藏
			$("#J_thl_div").css("top", top).addClass('cate-in');	
		}	
	}
};
