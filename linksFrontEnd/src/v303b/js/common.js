// Avoid `console` errors in browsers that lack a console.
(function() {
		var method;
		var noop = function() {
		};
		var methods = ['assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error', 'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log', 'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd', 'timeStamp', 'trace', 'warn'];
		var length = methods.length;
		var console = (window.console = window.console || {});

		while (length--) {
			method = methods[length];
			// Only stub undefined methods.
			if (!console[method]) {
				console[method] = noop;
			}
		}
}());

// stop default link behavior
$(document).on('click', '[href="#"],.disabled', function(e) {
	e.preventDefault();
});

$(function() {
	User.Init();
	THL.Init();
	Theme.Init();
	// $("#direct_text").autocomplete("/Home/Link/tag", {
	// 	dataType : "json",
	// 	minChars : 1,
	// 	'async': true,
	// 	width : 298,
	// 	scroll : false,
	// 	matchContains : true,
	// 	parse : function(data) {
	// 		return $.map(data, function(row) {
	// 			return {
	// 				data : row,
	// 				value : row.tag,
	// 				result : row.tag
	// 			};
	// 		});
	// 	},
	// 	formatItem : function(item) {
	// 		return item.tag;
	// 	}
	// }).result(function(e, item) {
	// 	$('#direct_text').val(item.tag);
	// 	$('#frm_drct').submit();
	// });
});

// 登录注册dialog弹出后，阻止mousemove事件冒泡，避免焦点丢失
$(document).on('mousemove', '.ui-widget-overlay', function(e){
	e.stopPropagation();
});
var User = {
	Init : function() {
		var self = this;
		$('.uc-menu .nm').on('mouseenter', function() {
            clearTimeout(self.menuTimer);
            self.menuTimer = null;
			$(this).find('ul, .ang').show();
		}).on('mouseleave', function() {
            var cur = $(this);
            self.menuTimer = setTimeout(function(){
                cur.find('ul, .ang').hide();
            }, 500);
		});
		$('.J_SignUp').on('click', function() {
			self.Reg();
			return false;
		});
		$('.J_SignIn').on('click', function() {
			self.Login();
			return false;
		});
		$(document).on('click', '.J_VerifyImg', function() {
			$(this).attr("src", APP + 'Verify?' + (+new Date()));
		});
	},
	CheckLogin : function() {
		var self = this;
		if ($CONFIG.IsLogin) {
			self.Login('请先登录');
			return false;
		}
		return true;
	},
	Reg : function() {
		var self = this;
		if (!$('#J_Reg').size()) {
			var hl = '';
			hl = hl + '<div class="lk-dialog lk-dialog-reg" id="J_Reg">';
			hl = hl + '	<div class="lkd-hd">';
			hl = hl + '		<em>快速注册</em>';
			hl = hl + '		<span class="msg"></span>';
			hl = hl + '		<a class="close" href="javascript:;">X</a>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-bd">';
			hl = hl + '		<ul>';
			hl = hl + '			<li><input class="ipt" type="text" name="user" placeholder="昵称" /></li>';
			hl = hl + '			<li><input class="ipt" type="text" name="email" placeholder="邮箱" /></li>';
			hl = hl + '			<li><input class="ipt" type="password" name="password" placeholder="密码" /></li>';
			hl = hl + '			<li class="vcode">';
			hl = hl + '				<input class="ipt" type="text" name="vcode" placeholder="验证码" value="" /><img src="/Verify" alt="验证码" class="J_VerifyImg" title="点击刷新" />';
			hl = hl + '			</li>';
			hl = hl + '		</ul>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-ft">';
			hl = hl + '		<a class="lkd-reg" href="javascript:;">注&nbsp;册</a>';
			hl = hl + '		<a class="lkd-login" href="javascript:;">已有帐号！登录！</a>';
			hl = hl + '	</div>';
			hl = hl + '</div>';
			$('body').append(hl);

			var obj = $('#J_Reg');

			obj.find('input').placeholder();

			obj.dialog({
				autoOpen : true,
				width : 384,
				modal : true,
				resizable : false,
				open : function() {
					setTimeout(function() {
						obj.find('input[name="user"]').select();
					}, 20);
				}
			});

			obj.find('.lkd-login').on('click', function() {
				obj.dialog('close');
				self.Login();
			});

			obj.find('.close').on('click', function() {
				obj.dialog('close');
				return false;
			});

			obj.find('input[name="vcode"]').on('keydown', function(event) {
				if (event.keyCode == 13) {
					obj.find('.lkd-reg').trigger('click');
					return false;
				}
			});

			obj.find('input[type="text"], input[type="password"]').on('focus', function() {
				$(this).css('background', '#fff');
			}).on('blur', function() {
				$(this).css('background', '#eeefef');
			});

			$(document).on('mouseenter', '#J_Reg input[name="user"], #J_Reg input[name="password"], #J_Reg input[name="email"], #J_Reg input[name="vcode"]', function() {
				$(this).select();
			}).on('mousemove', '#J_Reg input[name="user"], #J_Reg input[name="password"], #J_Reg input[name="email"], #J_Reg input[name="vcode"]', function(){
				//禁用冒泡 避免触发糖葫芦的焦点
				e.stopPropagation();
				return false;
			});

			var _loading = false;

			obj.find('.lkd-reg').on('click', function() {
				if (_loading) {
					return false;
				}

				var objusername = obj.find('input[name="user"]');
				var objpassword = obj.find('input[name="password"]');
				var objemail = obj.find('input[name="email"]');
				var objverify = obj.find('input[name="vcode"]');
				var objmsg = obj.find('.msg');

				var username = objusername.val();
				var password = objpassword.val();
				var email = objemail.val();
				var verify = objverify.val();

				if (!username || username == '昵称') {
					objmsg.html('昵称不能为空');
					objusername[0].focus();
					return false;
				}

				if (!/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/.test(email)) {
					objmsg.html('email格式不正确');
					objemail[0].focus();
					return false;
				}

				if (!password) {
					objmsg.html('密码不能为空');
					objpassword[0].focus();
					return false;
				}

				if (!verify) {
					objmsg.html('验证码不能为空');
					objverify[0].focus();
					return false;
				}

				objmsg.html('');
				_loading = true;

				var data = {
					"nickname" : username,
					"email" : email,
					"password" : password,
					"verify" : verify
				};

				$.post(APP + "Members/Register/saveReg", data, function(data) {
					if (data.indexOf("regOK") >= 0) {
						window.location.href = APP + "Members/Index/";
					} else {
						objmsg.html(data);
					}
					_loading = false;
				});

				return false;
			});

		} else {
			var obj = $('#J_Reg');
			obj.find('input[name="user"]').val('');
			obj.find('input[name="password"]').val('');
			obj.find('input[name="email"]').val('');
			obj.find('input[name="vcode"]').val('');
			obj.find('.msg').html('');
			obj.dialog('open');
		}
	},
	Login : function(msg) {
		var self = this;
		if (!$('#J_Login').size()) {
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
			hl = hl + '					<label for=""><input type="checkbox" name="autologin" checked="checked" id=""> 记住密码</label> <a class="fgpass" href="javascript:;">忘记密码？</a>';
			hl = hl + '				</li>';
			hl = hl + '			</ul>';
			hl = hl + '		</form>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-ft">';
			hl = hl + '		<a class="lkd-reg" href="javascript:;">登&nbsp;录</a>';
			hl = hl + '	</div>';
			hl = hl + '</div>';
			$('body').append(hl);

			var obj = $('#J_Login');
			var objmsg = obj.find('.msg');

			if (msg) {
				objmsg.html(msg);
			}

			obj.dialog({
				autoOpen : true,
				width : 384,
				modal : true,
				resizable : false,
				open : function() {
					setTimeout(function() {
						obj.find('input[name="user"]').select();
					}, 20);
				}
			});

			obj.find('.close').on('click', function() {
				obj.dialog('close');
				return false;
			});
			obj.find('.fgpass').on('click', function() {
				obj.dialog('close');
				self.FindPass();
			});
			obj.find('.reg').on('click', function() {
				obj.dialog('close');
				self.Reg();
			});

			obj.find('input[name="password"]').on('keydown', function(event) {
				if (event.keyCode == 13) {
					obj.find('.lkd-reg').trigger('click');
					return false;
				}
			});

			obj.find('input[type="text"], input[type="password"]').on('focus', function() {
				$(this).css('background', '#fff');
			}).on('blur', function() {
				$(this).css('background', '#eeefef');
			});

			$(document).on('mouseenter', '#J_Login input[name="user"], #J_Login input[name="password"]', function() {
				$(this).select();
			}).on('mousemove', '#J_Login input[name="user"], #J_Login input[name="password"]', function(e){
				//禁用冒泡 避免触发糖葫芦的焦点
				e.stopPropagation();
				return false;
			});

			obj.find('.lkd-reg').on('click', function() {
				var objusername = obj.find('input[name="user"]');
				var objpassword = obj.find('input[name="password"]');
				var username = objusername.val();
				var password = objpassword.val();
				var verify = "";
				var auto_login = obj.find('input[name="autologin"]').attr('checked');

				if (!username || username == '帐号') {
					objmsg.html('帐号不能为空');
					objusername[0].focus();
					return false;
				}

				if (!password) {
					objmsg.html('密码不能为空');
					objpassword[0].focus();
					return false;
				}

				var data = {
					"username" : username,
					"password" : password,
					"auto_login" : (auto_login == 'checked' ? 1 : 0)
				};

				if ($('#J_Login').find('li.vcode').length !== 0) {
					var objverify = obj.find('input[name="vcode"]');
					verify = objverify.val();

					if (!verify) {
						objmsg.html('验证码不能为空');
						objverify.focus();
						return false;
					}

					data["verify"] = verify;
				}

				$.post(APP + "Members/Login/checkLogin", data, function(data) {
					var resp = eval('(' + data + ')');
					var showmsg = function(obj, msg) {
						$(obj).val("");
						$(obj).attr('placeholder', msg);
						$(obj).focus();
					};

					switch(resp.code) {
						case 200:
							if (window.opener && document.referrer) {
								window.opener.location.href = document.referrer;
								//window.opener.location.reload();
							}
							window.location.href = APP + "Index";
							break;
						case 403:
							$(objusername[0]).val("");
							$(objpassword[0]).val("");
							objmsg.html(resp.content);
							break;
						case 501:
							showmsg(objusername[0], resp.content);
							break;
						case 502:
							showmsg(objusername[0], resp.content);
							break;
						case 503:
							showmsg(objpassword[0], resp.content);
							break;
						case 504:
							showmsg(objpassword[0], resp.content);
							break;
						case 505:
							var vcode = $('#J_Login').find('li.vcode');
							if (vcode.length === 0) {
								$('<li class="vcode">\n\<input class="ipt" type="text" name="vcode" placeholder="验证码" value="" /><img src="/Verify" alt="验证码" class="J_VerifyImg" title="点击刷新" />\n\</li>').insertAfter($('#J_Login input[name="password"]').parent("li"));
							} else {
								$('#J_Login').find('li.vcode img').click();
							}
							break;
						case 506:
							objmsg.html(resp.content);
							obj.find('input[name="vcode"]').focus();
							$('#J_Login').find('li.vcode img').click();
							break;
					}

				});

				return false;
			});
		} else {
			var obj = $('#J_Login');
			obj.find('input[name="user"]').val('');
			obj.find('input[name="password"]').val('');
			obj.find('.msg').html('');
			obj.find('input[name="autologin"]').attr('checked', true);
			if (msg) {
				obj.find('.msg').html(msg);
			} else {
				obj.find('.msg').html('');
			}
			$('#J_Login').dialog('open');
		}
	},
	FindPass : function() {
		if (!$('#J_FindPass').size()) {
			var hl = '';
			hl = hl + '<div class="lk-dialog lk-dialog-fpass" id="J_FindPass">';
			hl = hl + '	<div class="lkd-hd">';
			hl = hl + '		<a class="close" href="javascript:;">X</a>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-bd">';
			hl = hl + '		<div class="tabs">';
			hl = hl + '			<a class="on" href="javascript:;">通过电子邮件</a><a style="display:none;" href="javascript:;">通过其他方式</a>';
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
				autoOpen : true,
				width : 390,
				modal : true,
				resizable : false,
				open : function() {
					setTimeout(function() {
						obj.find('input[name="email"]').select();
					}, 20);
				}
			});

			obj.find('.close').on('click', function() {
				$('#J_FindPass').dialog('close');
				return false;
			});

			obj.find('input[name="vcode"]').on('keydown', function(event) {
				if (event.keyCode == 13) {
					obj.find('.lkd-btn').trigger('click');
					return false;
				}
			});

			obj.find('input[type="text"], input[type="password"]').on('focus', function() {
				$(this).css('background', '#fff');
			}).on('blur', function() {
				$(this).css('background', '#eeefef');
			});

			$(document).on('mouseenter', '#J_FindPass input[name="email"], #J_FindPass input[name="vcode"]', function() {
				$(this).select();
			});

			var _loading = false;

			obj.find('.lkd-btn').on('click', function() {
				var objemail = obj.find('input[name="email"]');
				var objverify = obj.find('input[name="vcode"]');
				var email = objemail.val();
				var verify = objverify.val();

				if (!email) {
					alert("请输入邮箱");
					objemail[0].focus();
					return false;
				}
				var result = email.match(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/);
				if (result == null) {
					alert("请正确输入邮箱");
					objemail[0].focus();
					return false;
				}

				if (!verify) {
					alert('验证码不能为空');
					objverify[0].focus();
					return false;
				}

				$.post(APP + 'Members/Login/missPwd', {
					email : email,
					verify : verify
				}, function(data) {
					if (data.indexOf("sendOK") >= 0) {
						var mailserver = data.split('|');
						alert("请进入您的账户邮箱获取新密码");
						window.open("http://" + mailserver[1]);
					} else {
						alert(data);
					}
				});
			});

		} else {
			var obj = $('#J_FindPass');
			obj.find('input[name="email"]').val('');
			obj.find('input[name="vcode"]').val('');
			$('#J_FindPass').dialog('open');
		}
	}
};

var THL = {
	conf : {
		topnm : 34,
		topex : 64
	},
	Init : function() {
		var self = this;

		//重新加载页面清除keyword 清除浏览器未清除的文本框的值
		$.cookies.set('keyword', '');
		$("#search_text").val('').select();
		//选中文本框

		$('.thl').mouseover(function() {
			$('#J_thl_div').show();
			$("#search_text").select();
		});
		//移入糖葫芦区域 显示糖葫芦, 选中 文本

		$('.J_thl_area').mouseleave(function() {
			$('#J_thl_div').hide();
		});
		//移除糖葫芦主区域 隐藏糖葫芦

		$(".J_thlz a").click(function() {//糖葫芦籽点击
			$(this).addClass("on").siblings("a").removeClass("on");
			$("#btn_search").trigger("click");
			return false;
		});

		$("#J_thl_div a").click(function() {//糖葫芦点击
			$(this).addClass("on").siblings("a").removeClass("on");
			var index = $(this).index();
			$(".J_thlz:eq(" + index + ")").show().siblings(".J_thlz").hide();
			$(".J_thlz a").removeClass("on");
			$(".J_thlz:eq(" + index + ")").find("a:first").addClass("on");
			return false;
		});

		$("#search_text").blur(function() {//文本框失去焦点 如 内容为空 清除keyword
			if ($(this).val() == '') {
				$.cookies.set('keyword', '');
			}
		});

		$("#search_text").keyup(function(event) {//文本框输入内容 设置糖葫芦 位置
			$('#J_thl_div').show();
			self.setpos();
		});

		$('#search_text').on('click', function() {
			if (!$.cookies.get('keyword')) {
				var key = $.trim($("#search_text").val());
				if (key != '') {
					$(this).data('key', key);
					$(this).val(key);
				}
			} else {
				$(this).data('key', '');
			}
		});

		$('#search_text').on('webkitspeechchange', function() {//onwebkitspeechchange
			var key = $(this).data('key');
			if (key && key != '') {
				var v = $(this).val();
				if (v.indexOf(key) == 0) {
					$(this).val(v.replace(key, key + ' '));
					$(this).data('key', '');
				}
			}
			self.setpos();
		});

		$(document).mouseup(function(ev) {// 搜索文本框始终获取焦点
			if (document.activeElement.tagName == 'SELECT' || document.activeElement.tagName == "INPUT" || document.activeElement.tagName == "TEXTAREA" || document.activeElement.tagName == "IFRAME" || document.activeElement.id == "direct_text" || document.activeElement.id == "search_text" || document.activeElement.id == "search_text") {
				return;
			}
			var txt = '';
			if (window.getSelection) {// mozilla FF
				txt = window.getSelection();
			} else if (document.getSelection) {
				txt = document.getSelection();
			} else if (document.selection) {//IE
				txt = document.selection.createRange().text;
			}
			if (txt == '') {
				$("#search_text").select();
			}//未划选文本 划选 文本框
			if ($.cookies.get('keyword')) {
				$("#search_text").select();
			} //有keyword时 直接划选 文本框
		});

		$("#btn_search").click(function() {//单击搜索按钮
			$("#search_text").select();
			var keyword = $.trim($("#search_text").val());
			$.cookies.set('keyword', keyword);
			//保存keyword
			keyword = keyword.replace('http://', '');
			keyword = encodeURIComponent(keyword);
			var url = $(".J_thlz a.on").attr("url").replace('keyword', keyword);
			var tid = $(".J_thlz a.on").attr("tid");
			self.go(url, tid, keyword);
			return false;
		});
	},
	go : function(url, tid, keyword) {
		if (tid == '4' || tid == '26' || tid == '40' || tid == '58' || tid == '110' || tid == '117') {// 谷歌、美试、啪啪、PQuora
			$.post(URL + "/thl_count", {
				tid : tid
			}, function() {
			});
			window.open("http://" + url);
		} else if (tid == '10' || tid == '20') {// 另客、维修
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
	setpos : function() {
		var top, self = this;
		if ($("#search_text").val() != '') {//文本框有值调整位置
			top = self.conf.topex;
			$("#J_thl_div").css("top", top).removeClass('cate-in');
		} else {
			top = self.conf.topnm;
			$('#J_thl_div').hide();
			//没值隐藏
			$("#J_thl_div").css("top", top).addClass('cate-in');
		}
	}
};

var Theme = {
	Init : function() {
		var self = this;
		var isImgSrc = false;

		$('#K_change_skin_btn').on('mouseenter', function(){
            clearTimeout(self.timer);
            self.timer = null;
            if($('.skin-list').is(':hidden')){
                $('.skin-list').fadeIn(150);
            }
		}).on('mouseleave', function(){
                clearTimeout(self.timer);
                self.timer = null;
                self.timer = setTimeout(function(){
                    $('.skin-list').hide();
                },500);
        });

        $('#K_change_skin_btn_old').on('mouseleave', '.skin-list',function(){
			clearTimeout(self.timer);
			self.timer = null;
			self.timer = setTimeout(function(){
				$('.skin-list').hide();
			},500);
		}).on('mouseover', '.skin-list', function(){
			if(!$(this).is(':hidden')){
				clearTimeout(self.timer);
				self.timer = null;
			}
		}).on('mouseover', function(){
                if(!isImgSrc){
                    isImgSrc = true;
                    $(this).find('img').each(function(){
                        $(this).attr('src', $(this).data('src'));
                    });
                }
            });

		//靠右的皮肤 图例靠右显示
		//$('#J_skin_pics').find('.item:eq(3)').css('text-align','center')
			//.end().find('.item:gt(3)').css('text-align', 'right');

		/*
		$('#J_Styles>ul>li:not(.skin_selection_li)').on('click', function() {
			var obj = $(this).closest('li');
			if (!obj.is('.on')) {
				obj.addClass('on').siblings().removeClass('on');
				var bg = obj.data('bg');
				var theme = obj.data('theme');
				var id = obj.data('id');
				self.SetTheme(id, theme, bg);
			}
			return false;
		});
		*/
		$('#link_skin_themes').on('click', 'a', function(){
			var obj = $(this);
			var bg = obj.data('bg')
			var theme = obj.data('theme');
			var id = obj.data('id');
			self.SetTheme(id, theme, bg);
			return false;
		});
		//皮肤items容器
		var $skinPicsWrap = $("#J_skin_pics");
		//点击皮肤分类的链接显示对应的皮肤图片

		$('#J_skin_selection').on('mouseover', '.J_link_skin_type', function() {
			var $self = $(this);
			if (!$self.hasClass('active')) {
				$self.addClass('active').siblings().removeClass('active');
				$skinPicsWrap.children().css('display', 'none');
				$('#link_skin_' + $self.data('id')).fadeIn();
			}
		});
		// 点击图片换肤

		$skinPicsWrap.on('click', '.skin-link-btn', function() {
			var obj = $(this);
			var bg = obj.data('bg');
			var theme = obj.data('theme');
			var id = obj.data('id');

			if(id && theme && bg) self.SetBackGround(id, theme, bg);
		});

	},
	SetBackGround : function(id, tm, bg) {
		var tmurl = $CONFIG['PUBLIC'] + '/IndexV3/skins/{0}/style.css';
		$('#J_Skins').attr('href', tmurl.replace('{0}', tm));
		$('#container').css('background-image', 'url(' + bg + ')');

		$.post(URL + "/updateSkin", {
			'skinId' : id
		});
		return false;
	},
	SetTheme : function(id, tm, bg) {
		var tmurl = $CONFIG['PUBLIC'] + '/IndexV3/skins/{0}/style.css';
		$('#J_Skins').attr('href', tmurl.replace('{0}', tm));
		$('#container').css('background-image', 'url(' + bg + ')');
		$.post(URL + "/updateSkinTheme", {
			'themeId' : id
		});
		return false;
	},
	SetGlobal : function() {
		var self = this;
		if (!$('#J_StyleGlobal').size()) {
			var hl = '';
			hl = hl + '<div class="lk-dialog lk-dialog-settings" id="J_StyleGlobal">';
			hl = hl + '	<div class="lkd-hd">';
			hl = hl + '		<a class="close" href="#">X</a>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-bd">';
			hl = hl + '		<div class="settings">';
			hl = hl + '			<div class="settings-tabs">';
			hl = hl + '				<a class="on" href="javascript:;">全局设置</a> <a class="J_SetBackGround" href="javascript:;">背景设置</a>';
			hl = hl + '			</div>';
			hl = hl + '			<div class="settings-content">';
			hl = hl + '				<div class="ttl">全局风格设置</div>';
			hl = hl + '				<ul class="ct">';
			hl = hl + '					<li class="on"><img src="' + PUBLIC + '/indexv3/skins/styleshot01.jpg" alt="" /></li>';
			hl = hl + '				</ul>';
			hl = hl + '				<div class="ft">';
			hl = hl + '					<em>板块不透明度调节</em>';
			hl = hl + '					<span>0%</span>';
			hl = hl + '					<div class="process"></div>';
			hl = hl + '					<span>100%</span>';
			hl = hl + '				</div>';
			hl = hl + '			</div>';
			hl = hl + '		</div>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-ft">';
			hl = hl + '		<label for=""><input type="checkbox" name="" id="" /> 发条另客告诉大家</label>';
			hl = hl + '		<a class="lkd-add" href="javascript:;"><em>确&nbsp;认</em></a>';
			hl = hl + '	</div>';
			hl = hl + '</div>';

			$('body').append(hl);

			var obj = $('#J_StyleGlobal');

			obj.find('.close').on('click', function() {
				obj.dialog('close');
				return false;
			});

			obj.find('.lkd-add').on('click', function() {
				//TODO 确定按钮事件
				return false;
			});

			obj.find('.J_SetBackGround').on('click', function() {
				obj.dialog('close');
				self.SetCustom();
				return false;
			});

			var xslider = obj.find('.process');

			xslider.slider({
				range : "min",
				value : 0,
				min : 0,
				max : 100,
				slide : function(event, ui) {
					$(xslider).next().html(ui.value + '%');
				}
			});

			obj.dialog({
				autoOpen : true,
				width : 502,
				modal : true,
				resizable : false
			});

			return false;
		} else {
			var obj = $('#J_StyleGlobal');
			obj.find('input[type="checkbox"]').attr('checked', false);
			obj.dialog('open');
		}
	},
	SetCustom : function() {
		var self = this;
		if (!$('#J_StyleCustom').size()) {
			var hl = '';
			hl = hl + '<div class="lk-dialog lk-dialog-settings" id="J_StyleCustom">';
			hl = hl + '	<div class="lkd-hd">';
			hl = hl + '		<a class="close" href="#">X</a>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-bd">';
			hl = hl + '		<div class="settings">';
			hl = hl + '			<div class="settings-tabs">';
			hl = hl + '				<a class="J_SetGlobal" href="javascript:;">全局设置</a> <a class="on" href="javascript:;">背景设置</a>';
			hl = hl + '			</div>';
			hl = hl + '			<div class="settings-content">';
			hl = hl + '				<div class="bg-ttl">';
			hl = hl + '					<div class="pg">';
			hl = hl + '						<ul>';
			hl = hl + '							<li class="prev"><a href="#">上一页</a></li>';
			hl = hl + '							<li class="next"><a href="#">下一页</a></li>';
			hl = hl + '						</ul>';
			hl = hl + '					</div>';
			hl = hl + '					<div class="bg-cate bg-style bg-on">';
			hl = hl + '						炫彩光晕';
			hl = hl + '						<ul class="dropdown bg-cate-dropdown">';
			hl = hl + '							<li><a href="#">测试效果</a></li>';
			hl = hl + '							<li><a href="#">效果测试</a></li>';
			hl = hl + '						</ul>';
			hl = hl + '					</div><a class="bg-custom" href="javascript:;">自定义</a>';
			hl = hl + '				</div>';
			hl = hl + '				<div class="bg-list">';
			hl = hl + '					<ul class="imglist">';
			hl = hl + '						<li class="on"><img src="skins/dark/screenshot.jpg" alt="" /></li>';
			hl = hl + '						<li><img src="skins/dark/screenshot.jpg" alt="" /></li>';
			hl = hl + '						<li><img src="skins/dark/screenshot.jpg" alt="" /></li>';
			hl = hl + '						<li><img src="skins/dark/screenshot.jpg" alt="" /></li>';
			hl = hl + '						<li><img src="skins/dark/screenshot.jpg" alt="" /></li>';
			hl = hl + '						<li><img src="skins/dark/screenshot.jpg" alt="" /></li>';
			hl = hl + '						<li><img src="skins/dark/screenshot.jpg" alt="" /></li>';
			hl = hl + '						<li><img src="skins/dark/screenshot.jpg" alt="" /></li>';
			hl = hl + '					</ul>';
			hl = hl + '					<div class="upload-panel">';
			hl = hl + '						<div class="upload-demo">';
			hl = hl + '							<div class="upload-demo-box">';
			hl = hl + '								<img src="imgs/imgdemo.jpg" alt="" style="width:190px;" />';
			hl = hl + '							</div>';
			hl = hl + '						</div>';
			hl = hl + '						<div class="upload-ct">';
			hl = hl + '							<div class="row1">';
			hl = hl + '								<input class="txtimg" readonly="readonly" type="text" name="" id="upfile" /><a onclick="path.click();return false;" class="btnimg" href="#">选择图片</a>';
			hl = hl + '								<input type="file" id="path" style="display:none" onchange="upfile.value=this.value" />';
			hl = hl + '							</div>';
			hl = hl + '							<div class="row2">';
			hl = hl + '								<div class="bg-align bg-style">';
			hl = hl + '									<em>居中对齐</em>';
			hl = hl + '									<ul class="dropdown bg-align-dropdown">';
			hl = hl + '										<li><a href="#">居中对齐</a></li>';
			hl = hl + '									</ul>';
			hl = hl + '								</div>';
			hl = hl + '								<label for=""><input type="checkbox" name="" id=""> 平铺</label>';
			hl = hl + '								<label for=""><input type="checkbox" name="" id=""> 锁定</label>';
			hl = hl + '								背景配色 <a href="javascript:;" class="color"></a>';
			hl = hl + '							</div>';
			hl = hl + '						</div>';
			hl = hl + '						<div class="upload-ctls"><a href="#">点击上传</a></div>';
			hl = hl + '					</div>';
			hl = hl + '				</div>';
			hl = hl + '			</div>';
			hl = hl + '		</div>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-ft">';
			hl = hl + '		<label for=""><input type="checkbox" name="" id="" /> 发条另客告诉大家</label>';
			hl = hl + '		<a class="lkd-add" href="#"><em>确认</em></a>';
			hl = hl + '	</div>';
			hl = hl + '</div>';
			$('body').append(hl);

			var obj = $('#J_StyleCustom');

			obj.find('.bg-cate').dropdown({
				classNm : ".bg-cate-dropdown"
			});
			obj.find('.bg-cate').on('mouseenter', function() {
				$(this).removeClass('bg-on');
			}).on('mouseleave', function() {
				$(this).addClass('bg-on');
			});
			obj.find('.bg-align').dropdown({
				classNm : ".bg-align-dropdown"
			});
			obj.find('.bg-align').on('mouseenter', function() {
				$(this).removeClass('bg-on');
			}).on('mouseleave', function() {
				$(this).addClass('bg-on');
			});

			obj.find('.close').on('click', function() {
				obj.dialog('close');
				return false;
			});

			obj.find('.bg-custom').on('click', function() {
				obj.find('.upload-panel').show().siblings().hide();
				$(this).addClass('bg-custom-on');
				obj.find('.pg').hide();
			});

			obj.find('.lkd-add').on('click', function() {
				//TODO 确定按钮事件
				return false;
			});

			obj.find('.J_SetGlobal').on('click', function() {
				obj.dialog('close');
				self.SetGlobal();
				return false;
			});

			obj.dialog({
				autoOpen : true,
				width : 502,
				modal : true,
				resizable : false
			});

			return false;
		} else {
			var obj = $('#J_StyleCustom');
			obj.find('input[type="checkbox"]').attr('checked', false);
			obj.dialog('open');
		}
	}
};