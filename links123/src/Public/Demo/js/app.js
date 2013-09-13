$(function(){
	User.Init();
	Zld.Init();
	THL.Init();
});
var User = {
	Init: function(){
		var self = this;
		$('.J_SignUp').on('click', function(){
			self.Reg();
			return false;
		});
		$('.J_SignIn').on('click', function(){
			self.Login();
			return false;
		});
		$(document).on('click', '.J_VerifyImg', function(){
			$(this).attr("src", APP+'Verify?'+(+new Date()));
		});
	},
	Reg: function(){
		var self = this;
		if(!$('#J_Reg').size()){
			var hl = '';
			hl = hl + '<div class="lk-dialog lk-dialog-reg" id="J_Reg">';
			hl = hl + '	<div class="lkd-hd">';
			hl = hl + '		<em>快速注册</em>';
			hl = hl + '		<a class="close" href="javascript:;">X</a>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-bd">';
			hl = hl + '		<ul>';
			hl = hl + '			<li><input class="ipt" type="text" name="user" placeholder="昵称" /></li>';
			hl = hl + '			<li><input class="ipt" type="text" name="email" placeholder="邮箱" /></li>';
			hl = hl + '			<li><input class="ipt" type="password" name="password" placeholder="密码" /></li>';
			hl = hl + '			<li class="vcode">';
			hl = hl + '				<input class="ipt" type="text" name="vcode" placeholder="验证码" /><img src="/Verify" alt="验证码" class="J_VerifyImg" title="点击刷新" />';
			hl = hl + '			</li>';
			hl = hl + '		</ul>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-ft">';
			hl = hl + '		<a class="lkd-reg" href="javascript:;">注册</a>';
			hl = hl + '		<a class="lkd-login" href="javascript:;">已有帐号！登录！</a>';
			hl = hl + '	</div>';
			hl = hl + '</div>';
			$('body').append(hl);

			var obj = $('#J_Reg');

			obj.find('input').placeholder();

			obj.dialog({
				autoOpen: true,
				width: 384,
				modal: true,
				resizable: false,
				open: function(){
					setTimeout(function(){obj.find('input[name="user"]').select();}, 20);
				}
			});

			obj.find('.lkd-login').on('click', function(){
				obj.dialog('close');
				self.Login();
			});

			obj.find('.close').on('click', function(){
				obj.dialog('close');
			});

			obj.find('.vcode').on('keydown', function(){
				if (event.keyCode == 13) {
					obj.find('.lkd-reg').trigger('click');
				}
			});

			var _loading = false;

			obj.find('.lkd-reg').on('click', function(){
				if(_loading){ return false; }

				var username = obj.find('input[name="user"]').val();
				var password = obj.find('input[name="password"]').val();
				var email = obj.find('input[name="email"]').val();
				var verify = obj.find('input[name="vcode"]').val();
				
				if (!username || username == '昵称') {
					alert('昵称不能为空');
					return false;
				}

				if(!/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/.test(email)){
					alert('email格式不正确');
					return false;
				}
				
				if (!password) {
					alert('密码不能为空');
					return false;
				}
				
				if (!verify) {
					alert('验证码不能为空');
					return false;
				}
				
				_loading = true;

				var data = { "nickname": username, "email": email, "password": password, "verify": verify };
				
				$.post(APP + "Members/Register/saveReg", data, 
					function(data){
						if ( data.indexOf("regOK") >= 0 ) {
							window.location.href = APP+"Members/Index/";
						} else {
							alert(data);
						}
						_loading = false;
					}
				);

				return false;
			});

		}else{
			var obj = $('#J_Reg');
			obj.find('input[name="user"]').val('');
			obj.find('input[name="password"]').val('');
			obj.find('input[name="email"]').val('');
			obj.find('input[name="vcode"]').val('');
			obj.dialog('open');
		}
	},
	Login: function(){
		var self = this;
		if(!$('#J_Login').size()){
			var hl = '';
			hl = hl + '<div class="lk-dialog lk-dialog-login" id="J_Login">';
			hl = hl + '	<div class="lkd-hd">';
			hl = hl + '		<em>登录</em>';
			hl = hl + '		<a class="close" href="javascript:;">X</a>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-bd">';
			hl = hl + '		<form action="">';
			hl = hl + '			<ul>';
			hl = hl + '				<li><input class="ipt" type="text" name="user" placeholder="邮箱/账户/手机号" /></li>';
			hl = hl + '				<li><input class="ipt" type="password" name="password" placeholder="密码" /></li>';
			hl = hl + '				<li class="rpass">';
			hl = hl + '					<span>还不是会员？<a class="reg" href="javascript:;">注册</a></span>';
			hl = hl + '					<label for=""><input type="checkbox" name="autologin" id=""> 记住密码</label> <a class="fgpass" href="javascript:;">忘记密码？</a>';
			hl = hl + '				</li>';
			hl = hl + '			</ul>';
			hl = hl + '		</form>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-ft">';
			hl = hl + '		<a class="lkd-reg" href="#">登录</a>';
			hl = hl + '	</div>';
			hl = hl + '</div>';
			$('body').append(hl);

			var obj = $('#J_Login');

			obj.dialog({
				autoOpen: true,
				width: 384,
				modal: true,
				resizable: false,
				open: function(){
					setTimeout(function(){$('#J_signin_user').select();}, 20);
				}
			});

			obj.find('.close').on('click', function(){
				obj.dialog('close');
			});
			obj.find('.fgpass').on('click', function(){
				obj.dialog('close');
				self.FindPass();
			});
			obj.find('.reg').on('click', function(){
				obj.dialog('close');
				self.Reg();
			});
			obj.find('.lkd-reg').on('click', function(){
				var username = obj.find('input[name="user"]').val();
				var password = obj.find('input[name="password"]').val();
				var auto_login = obj.find('input[name="autologin"]').attr('checked');
				
				if (!username || username == '帐号') {
					alert('帐号不能为空');
					return false;
				}
				
				if (!password) {
					alert('密码不能为空');
					return false;
				}
				
				var data = { "username": username, "password": password, "auto_login": (auto_login=='checked' ? 1 : 0) };
				
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
					}
				); 
			});
		}else{
			var obj = $('#J_Login');
			obj.find('input[name="user"]').val('');
			obj.find('input[name="password"]').val('');
			obj.find('input[name="autologin"]').attr('checked', false);
			$('#J_Login').dialog('open');
		}
	},
	FindPass: function(){
		if(!$('#J_FindPass').size()){
			var hl = '';
			hl = hl + '<div class="lk-dialog lk-dialog-fpass" id="J_FindPass">';
			hl = hl + '	<div class="lkd-hd">';
			hl = hl + '		<a class="close" href="javascript:;">X</a>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-bd">';
			hl = hl + '		<div class="tabs">';
			hl = hl + '			<a class="on" href="javascript:;">通过电子邮件</a><a href="javascript:;">通过其他方式</a>';
			hl = hl + '		</div>';
			hl = hl + '		<div class="ct">';
			hl = hl + '			<form action="">';
			hl = hl + '			<ul>';
			hl = hl + '				<li><input class="ipt" type="text" name="" id="J_forgetpass_email" placeholder="请输入登录邮箱" /></li>';
			hl = hl + '				<li class="vcode">';
			hl = hl + '					<input class="ipt" type="text" name="" id="" placeholder="验证码" /><img src="/Verify" alt="验证码" class="J_VerifyImg" title="点击刷新" />';
			hl = hl + '				</li>';
			hl = hl + '			</ul>';
			hl = hl + '			</form>';
			hl = hl + '		</div>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-ft">';
			hl = hl + '		<a class="lkd-btn" href="#">发送验证邮件</a>';
			hl = hl + '	</div>';
			hl = hl + '</div>';
			$('body').append(hl);

			$('#J_FindPass').dialog({
				autoOpen: true,
				width: 390,
				modal: true,
				resizable: false,
				open: function(){
					setTimeout(function(){$('#J_signin_user').select();}, 20);
				}
			});

			$('#J_FindPass').find('.close').on('click', function(){
				$('#J_FindPass').dialog('close');
			});

		}else{
			$('#J_FindPass').dialog('open');
		}
	}
};
var Zld = {
	Init: function(){
		var self = this;
		var obj = $('#J_ZldList');
		obj.find('.add').on('click', function(){
			self.Add();
		});
		obj.find('.ctl').on('click', function(){
			var o = $(this).closest('li');
			var id = o.data('id');
			var nm = o.find('b').html();
			var url = o.data('url');
			self.Edit(id, nm, url);
		});
		obj.find('.nm').on('click', function(){
			var o = $(this).closest('li');
			var url = o.data('url');
			self.Go(url);
		});
		
	},
	Go: function(url){
		var obj = $('#J_MyAreaForm');
		obj.find('input[name="url"]').val(url);
		obj.submit();
	},
	Create: function(id, nm, url){
		if(!$('#J_Zld').size()){
			var hl = '';
			hl = hl + '<div class="lk-dialog lk-dialog-zld" id="J_Zld">';
			hl = hl + '	<div class="lkd-hd">';
			hl = hl + '		<a class="close" href="javascript:;">X</a>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-bd">';
			hl = hl + '		<form action="">';
			hl = hl + '			<ul>';
			hl = hl + '				<li><input class="ipt" type="text" name="" id="J_myarea_web_name" placeholder="网站名称" /></li>';
			hl = hl + '				<li><input class="ipt" type="text" name="" id="J_myarea_web_url" placeholder="网址" /></li>';
			hl = hl + '			</ul>';
			hl = hl + '		</form>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-ft">';
			hl = hl + '		<input type="hidden" name="" id="J_myarea_id" value="" />';
			hl = hl + '		<a class="lkd-add" href="javascript:;">确认添加</a>';
			hl = hl + '	</div>';
			hl = hl + '</div>';
			$('body').append(hl);

			$('#J_Zld').dialog({
				autoOpen: false,
				width: 384,
				modal: true,
				resizable: false,
				open: function(){
					setTimeout(function(){$('#J_myarea_web_name').select();}, 20);
				}
			});

			$('#J_Zld').find('.close').on('click', function(){
				$('#J_Zld').dialog('close');
			});
		}
		$('#J_Zld').dialog('open');
			$('#J_myarea_id').val('');
			$('#J_myarea_web_name').val('');
			$('#J_myarea_web_url').val('');
		if(id){
			$('#J_myarea_id').val(id);
			$('#J_myarea_web_name').val(nm);
			$('#J_myarea_web_url').val(url);
		}
	},
	Add: function(){
		var self = this;
		self.Create();
	},
	Edit: function(id, nm, url){
		this.Create(id, nm, url);
	}
};
var THL = {
	conf : {
		topnm : 36,
		topex : 68
	},
	Init : function(){
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
			$("#J_thl_div").css("top", top).removeClass('cate-in');	
		}else{
			top = self.conf.topnm;
			$('#J_thl_div').hide(); //没值隐藏
			$("#J_thl_div").css("top", top).addClass('cate-in');	
		}	
	}
};