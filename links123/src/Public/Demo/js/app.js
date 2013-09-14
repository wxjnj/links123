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
				return false;
			});

			obj.find('.vcode').on('keydown', function(event){
				if (event.keyCode == 13) {
					obj.find('.lkd-reg').trigger('click');
				}
			});

			obj.find('input[type="text"], input[type="password"').on('focus', function(){
				$(this).css('background', '#fff');
			}).on('blur', function(){
				$(this).css('background', '#eeefef');
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
	Login: function(msg){
		var self = this;
		if(!$('#J_Login').size()){
			var hl = '';
			hl = hl + '<div class="lk-dialog lk-dialog-login" id="J_Login">';
			hl = hl + '	<div class="lkd-hd">';
			hl = hl + '		<em>登录</em>';
			hl = hl + '		<span class="msg"></span>';
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

			if(msg){
				obj.find('.msg').html(msg);
			}

			obj.dialog({
				autoOpen: true,
				width: 384,
				modal: true,
				resizable: false,
				open: function(){
					setTimeout(function(){obj.find('input[name="user"]').select();}, 20);
				}
			});

			obj.find('.close').on('click', function(){
				obj.dialog('close');
				return false;
			});
			obj.find('.fgpass').on('click', function(){
				obj.dialog('close');
				self.FindPass();
			});
			obj.find('.reg').on('click', function(){
				obj.dialog('close');
				self.Reg();
			});

			obj.find('input[type="password"').on('keydown', function(event){
				if (event.keyCode == 13) {
					obj.find('.lkd-reg').trigger('click');
				}
			});

			obj.find('input[type="text"], input[type="password"').on('focus', function(){
				$(this).css('background', '#fff');
			}).on('blur', function(){
				$(this).css('background', '#eeefef');
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

				return false;
			});
		}else{
			var obj = $('#J_Login');
			obj.find('input[name="user"]').val('');
			obj.find('input[name="password"]').val('');
			obj.find('input[name="autologin"]').attr('checked', false);
			if(msg){
				obj.find('.msg').html(msg);
			}else{
				obj.find('.msg').html('');
			}
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
			hl = hl + '				<li><input class="ipt" type="text" name="email" placeholder="请输入登录邮箱" /></li>';
			hl = hl + '				<li class="vcode">';
			hl = hl + '					<input class="ipt" type="text" name="vcode" id="" placeholder="验证码" /><img src="/Verify" alt="验证码" class="J_VerifyImg" title="点击刷新" />';
			hl = hl + '				</li>';
			hl = hl + '			</ul>';
			hl = hl + '			</form>';
			hl = hl + '		</div>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-ft">';
			hl = hl + '		<a class="lkd-btn" href="javascript:;">发送验证邮件</a>';
			hl = hl + '	</div>';
			hl = hl + '</div>';
			$('body').append(hl);

			var obj = $('#J_FindPass');

			obj.dialog({
				autoOpen: true,
				width: 390,
				modal: true,
				resizable: false,
				open: function(){
					setTimeout(function(){obj.find('input[name="email"]').select();}, 20);
				}
			});

			obj.find('.close').on('click', function(){
				$('#J_FindPass').dialog('close');
				return false;
			});

			obj.find('.vcode').on('keydown', function(event){
				if (event.keyCode == 13) {
					obj.find('.lkd-btn').trigger('click');
				}
			});

			obj.find('input[type="text"], input[type="password"').on('focus', function(){
				$(this).css('background', '#fff');
			}).on('blur', function(){
				$(this).css('background', '#eeefef');
			});

			var _loading = false;

			obj.find('.lkd-btn').on('click', function(){
				var email = obj.find('input[name="email').val();
				var verify = obj.find('input[name="vcode"]').val();

				if (!email) {
					alert("请输入邮箱");
					return false;
				}
				var result = email.match(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/);
				if (result == null) {
					alert("请正确输入邮箱");
					return false;
				}

				if (!verify) {
					alert('验证码不能为空');
					return false;
				}
				
				$.post(APP + 'Members/Login/missPwd', {
					email: email,
					verify: verify
					}, 
					function(data){
						if ( data.indexOf("sendOK") >= 0 ) {
							var mailserver = data.split('|');
							alert("请进入您的账户邮箱获取新密码");
							window.open("http://" + mailserver[1]);
						}else {
							alert(data);
						}
					}
				);
			});

		}else{
			$('#J_FindPass').dialog('open');
		}
	}
};
var Zld = {
	IsSortable: false, //是否为拖拽点击，true则不打开自留地网址
	Init: function(){
		var self = this;
		var obj = $('#J_ZldList');

		$(document).on('click', '#J_ZldList .add', function(){
			if(self.CheckLogin()){
				self.Create();
			}
		});
		$(document).on('click',  '#J_ZldList .ctl', function(){
			if(self.CheckLogin()){
				if($(this).hasClass('add')){ return false; }
				var o = $(this).closest('li');
				var id = o.data('id');
				var nm = o.find('b').html();
				var url = o.data('url');
				self.Create(id, nm, url);
				return false;
			}
		});
		$(document).on('click', '#J_ZldList .nm', function(){
			if (!Zld.IsSortable) {
				var o = $(this).closest('li');
				var url = o.data('url');
				self.Go(url);
			} else {
				Zld.IsSortable = false;
			}
			return false;
		});

		$('#J_sortable').sortable({
			update: function(event, ui){
				Zld.IsSortable = true;
				
				$.post(
					URL + '/sortArealist', 
					{'area' : $(this).sortable('toArray')},
					function(data) {
						if (data == 1) {
							//成功
						} else if (data == 0){
							//失败
						} else {
							//失败
						}
					}
				);
			}  
		});
		$('#J_sortable').sortable('enable');
		
		$(document).on('click', '#J_Zld .lkd-add, #J_Zld .lkd-edit', function(){

			var o = $('#J_Zld');

			var id = o.find('input[name="id"]').val();
			var name = o.find('input[name="name"]').val();
			var url = o.find('input[name="url"]').val();

			if (!name) {
				alert("请输入网站名称");
				return false;
			}
			if (!url) {
				alert("请输入网址");
				return false;
			}
			
			$.post(
				URL + '/updateArea', 
				{ 'web_id': id, 'web_url': url, 'web_name': name },
				function(data) {
					var licur = function(){
						var li = null;
						obj.find('ul>li').each(function(){
							if($(this).data('id') == id){
								li = $(this);
								return;
							}
						});
						return li;
					}
					if(data == 1){ //更新成功
						var li = licur();
						li.attr('url', '/Link/index.html?mod=myarea&amp;url=' + url);
						li.data('url', url);
						li.find('b').html(name);
					}else if(data > 1){ //新加成功
						var li = obj.find('.add').closest('li');
						li.before(self.CreateItem(data, name, url));
					}else if(data == -1){
						User.Login('请先登录');
					}else{
						alert('操作失败');
					}
					o.dialog('close');
				}
			);
			return false;
		});
		
		$(document).on('click', '#J_Zld .lkd-del', function(){

			var o = $('#J_Zld');
			var id = o.find('input[name="id"]').val();
			
			$.post(
				URL + '/delArea', 
				{ 'web_id': id },
				function(data) {
					var licur = function(){
						var li = null;
						obj.find('ul>li').each(function(){
							if($(this).data('id') == id){
								li = $(this);
								return;
							}
						});
						return li;
					}
					if(data == 1){
						var li = licur();
						li.remove();
					}else if(data == -1){
						User.Login('请先登录');
					}else{
						alert('操作失败');
					}
					o.dialog('close');
				}
			);
			return false;
		});
	},
	CheckLogin: function(){
		if($CONFIG.IsLogin){
			User.Login('请先登录');
			return false;
		}
		return true;
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
			hl = hl + '				<li><input class="ipt" type="text" name="name" placeholder="网站名称" /></li>';
			hl = hl + '				<li><input class="ipt" type="text" name="url" placeholder="网址" /></li>';
			hl = hl + '			</ul>';
			hl = hl + '		</form>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-ft">';
			hl = hl + '		<input type="hidden" name="id" value="" />';
			hl = hl + '		<span class="editp" style="display:none;"><a class="lkd-edit" href="javascript:;">确认编辑</a>';
			hl = hl + '		<a class="lkd-del" href="javascript:;">删除</a></span>';
			hl = hl + '		<span class="addp"><a class="lkd-add" href="javascript:;">确认添加</a></span>';
			hl = hl + '	</div>';
			hl = hl + '</div>';
			$('body').append(hl);

			var obj = $('#J_Zld');

			obj.dialog({
				autoOpen: false,
				width: 384,
				modal: true,
				resizable: false,
				open: function(){
					setTimeout(function(){obj.find('input[name="name"]').select();}, 20);
				}
			});

			obj.find('.close').on('click', function(){
				obj.dialog('close');
				return false;
			});

			obj.find('input[type="text"]').on('focus', function(){
				$(this).css('background', '#fff');
			}).on('blur', function(){
				$(this).css('background', '#eeefef');
			});

			obj.find('input[name="name"],input[name="url"]').on('keydown', function(event){
				if (event.keyCode == 13) {
					if(obj.find('.editp').is(":visible")){
						obj.find('.lkd-edit').trigger('click');	
					}else{
						obj.find('.lkd-add').trigger('click');
					}
				}
			});
		}

		var obj = $('#J_Zld');		
		if(id){
			obj.find('input[name="id"]').val(id);
			obj.find('input[name="name"]').val(nm);
			obj.find('input[name="url"]').val(url);
			obj.find('.editp').show();
			obj.find('.addp').hide();
		}else{
			obj.find('input[name="id"]').val('');
			obj.find('input[name="name"]').val('');
			obj.find('input[name="url"]').val('');
			obj.find('.editp').hide();
			obj.find('.addp').show();
		}

		obj.dialog('open');
	},
	CreateItem: function(id, nm, url){
		var hl = '<li id="'+ id +'" url="/Link/index.html?mod=myarea&amp;url='+ url +'" data-id="'+ id +'" data-url="'+ url +'">';
		hl = hl + '<span class="nm"><i class="mask"></i><b>'+ nm +'</b></span>';
		hl = hl + '<span class="ctl"><i class="mask"></i></span>';
		hl = hl + '</li>';
		return hl;
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