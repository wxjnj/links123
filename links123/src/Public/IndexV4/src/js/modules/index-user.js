
// stop default link behavior
$(document).on('click', '[href="#"],.disabled', function(e) {
	e.preventDefault();
});

// 登录注册dialog弹出后，阻止mousemove事件冒泡，避免焦点丢失
$(document).on('mousemove', '.ui-widget-overlay', function(e){
	e.stopPropagation();
});

var User = {
	Init : function() {
		var self = this;
		self.r_Dialog = $('.user-reg-dialog');
		self.l_Dialog = $('.user-log-dialog');

		self.r_Dialog.dialog({
			autoOpen: false,
			modal : true,
			resizable : false,
			open : function() {
				setTimeout(function() {
					self.r_Dialog.find('input[name="username"]').select();
				}, 20);
			}
		});
		self.l_Dialog.dialog({
			autoOpen: false,
			modal : true,
			resizable : false,
			open : function() {
				setTimeout(function() {
					self.l_Dialog.find('input[name="username"]').select();
				}, 20);
			}
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
		self.l_Dialog.on('click', '.remember', function(){
			var isChecked = $(this).hasClass('checked');
			if(isChecked){
				$(this).removeClass('checked');
			}else{
				$(this).addClass('checked');
			}
		});

		$(document).on('mouseenter', 'input[name="username"], #J_Reg input[name="password"], #J_Reg input[name="email"], #J_Reg input[name="vcode"]', function() {
			$(this).select();
		}).on('mousemove', '#J_Reg input[name="username"], #J_Reg input[name="password"], #J_Reg input[name="email"], #J_Reg input[name="vcode"]', function(){
			//禁用冒泡 避免触发糖葫芦的焦点
			e.stopPropagation();
			return false;
		}).on('keyup', 'input[name="username"], #J_Reg input[name="password"], #J_Reg input[name="email"], #J_Reg input[name="vcode"]', function(){
			$('.user-dialog').find('.msg').hide().find('div').html('');
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
		var obj = self.r_Dialog;

		obj.find('input').placeholder();
		obj.find('input[name="username"]').val('');
		obj.find('input[name="password"]').val('');
		obj.find('input[name="email"]').val('');
		obj.find('input[name="vcode"]').val('');
		obj.find('.msg').hide().find('div').html('');

		obj.dialog({
			autoOpen : true,
			modal : true,
			resizable : false,
			open : function() {
				setTimeout(function() {
					obj.find('input[name="username"]').select();
				}, 20);
			}
		});

		obj.find('.close-btn').on('click', function() {
			obj.dialog('close');
			return false;
		});

		obj.find('input[name="vcode"]').on('keydown', function(event) {
			if (event.keyCode == 13) {
				obj.find('.submit-btn').trigger('click');
				return false;
			}
		});
		var _loading = false;

		obj.find('.submit-btn').on('click', function() {
			if (_loading) {
				return false;
			}

			var objusername = obj.find('input[name="username"]');
			var objpassword = obj.find('input[name="password"]');
			var objemail = obj.find('input[name="email"]');
			var objverify = obj.find('input[name="vcode"]');

			var username = objusername.val();
			var password = objpassword.val();
			var email = objemail.val();
			var verify = objverify.val();

			if (!username || username == objusername.attr('placeholder')) {
				obj.find('.msg-username').show().find('div').html('昵称不能为空');
				objusername[0].focus();
				return false;
			}

			if (!/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/.test(email)) {
				obj.find('.msg-email').show().find('div').html('email格式不正确');
				objemail[0].focus();
				return false;
			}

			if (!password) {
				obj.find('.msg-password').show().find('div').html('密码不能为空');
				objpassword[0].focus();
				return false;
			}

			if (!verify) {
				obj.find('.msg-vcode div').show().find('div').html('验证码不能为空');
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
					//TODO: 根据返回信息 判断弹出哪个msg
					//objmsg.html(data);
				}
				_loading = false;
			});

			return false;
		});
	},
	Login : function(msg) {
		var self = this;
		var obj = self.l_Dialog;

		obj.find('input[name="username"]').val('');
		obj.find('input[name="password"]').val('');
		obj.find('.msg').hide().find('div').html('');
		//obj.find('.msg').html('');
		//obj.find('input[name="autologin"]').attr('checked', true);
		/*
			if (msg) {
				obj.find('.msg').html(msg);
			} else {
				obj.find('.msg').html('');
			}
			$('#J_Login').dialog('open');
		*/
		obj.dialog('open')

		obj.find('.close-btn').on('click', function() {
			obj.dialog('close');
			return false;
		});
		obj.find('.forget_pass').on('click', function() {
			obj.dialog('close');
			self.FindPass();
		});
		obj.find('input[name="password"]').on('keydown', function(event) {
			if (event.keyCode == 13) {
				obj.find('.submit-btn').trigger('click');
				return false;
			}
		});
		
		obj.find('.submit-btn').on('click', function() {
			var objusername = obj.find('input[name="username"]');
			var objpassword = obj.find('input[name="password"]');
			var username = objusername.val();
			var password = objpassword.val();
	
			var verify = "";
			var auto_login = obj.find('.remember').hasClass('checked');

			if (!username || username == objusername.attr('placeholder')) {
				obj.find('.msg-username').show().find('div').html('帐号不能为空');
				objusername[0].focus();
				return false;
			}

			if (!password) {
				obj.find('.msg-password').show().find('div').html('密码不能为空');
				objpassword[0].focus();
				return false;
			}

			var data = {
				"username" : username,
				"password" : password,
				"auto_login" : (auto_login ? 1 : 0)
			};

			$.post(APP + "Members/Login/checkLogin", data, function(data) {
				var resp = eval('(' + data + ')');
				/*
				var showmsg = function(obj, msg) {
					$(obj).val("");
					$(obj).attr('placeholder', msg);
					$(obj).focus();
				};
				*/
				// TODO: 根据msg不同 选择出现的位置
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

