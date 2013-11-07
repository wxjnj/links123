
// stop default link behavior
$(document).on('click', '[href="#"],.disabled', function(e) {
	e.preventDefault();
});

// 登录注册dialog弹出后，阻止mousemove事件冒泡，避免焦点丢失
$(document).on('mousemove', '.ui-widget-overlay', function(e){
	e.stopPropagation();
});

$.fn.userMsg = function(op){
	if(op == 'hide') {
		this.hide();
	} else {
		this.show().find('div').html(op);
	}
};

var User = {
	Init : function() {
		var self = this;
		var r_Dialog = self.r_Dialog = $('.user-reg-dialog');
		var l_Dialog = self.l_Dialog = $('.user-log-dialog');

		self.ok_dialog = $('.user-reg-ok-dialog');

		self.msgs = $('.user-dialog').find('.msg');

		self.r_msgs = r_Dialog.find('.msg');
		self.r_msg_username = r_Dialog.find('.msg-username');
		self.r_msg_password = r_Dialog.find('.msg-password');
		self.r_msg_email = r_Dialog.find('.msg-email');
		self.r_msg_vcode = r_Dialog.find('.msg-vcode');

		self.l_msgs = l_Dialog.find('.msg');
		self.l_msg_username = l_Dialog.find('.msg-username');
		self.l_msg_password = l_Dialog.find('.msg-password');

		self.r_Dialog.dialog({
			autoOpen: false,
			modal : true,
			resizable : false,
			width: 460,
			show: {
				effect: "clip",
				duration: 150
			},
			hide: {
				effect: "clip",
				duration: 150
			},
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
			width: 460,
			show: {
				effect: "clip",
				duration: 200
			},
			hide: {
				effect: "clip",
				duration: 200
			},
			open : function() {
				setTimeout(function() {
					self.l_Dialog.find('input[name="username"]').select();
				}, 20);
			}
		});
		self.ok_dialog.dialog({
			autoOpen: false,
			modal : true,
			resizable : false,
			width: 340,
			show: {
				effect: "clip",
				duration: 150
			},
			hide: {
				effect: "clip",
				duration: 150
			},
			open : function() {
				setTimeout(function(){
					window.location.href = APP + "Members/Index/";
				}, 3000);
			}
		});
		self.ok_dialog.on('click', '.close-btn', function(){
			window.location.href = APP + "Members/Index/";
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

		$(document).on('mouseenter', 'input[name="username"], input[name="password"], input[name="email"], input[name="vcode"]', function() {
			$(this).select();
		}).on('mousemove', '#J_Reg input[name="username"], input[name="password"], input[name="email"], input[name="vcode"]', function(e){
			//禁用冒泡 避免触发糖葫芦的焦点
			return false;
		}).on('keyup', 'input[name="username"], input[name="password"], input[name="email"], input[name="vcode"]', function(){
			var cur = $(this).attr('name');
			self['r_msg_' + cur] && self['r_msg_' + cur].userMsg('hide');
			self['l_msg_' + cur] && self['l_msg_' + cur].userMsg('hide');
			self['f_msg_' + cur] && self['f_msg_' + cur].userMsg('hide');
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
				self.r_msg_username.userMsg('昵称不能为空');
				objusername[0].focus();
				return false;
			}

			if (!/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/.test(email)) {
				self.r_msg_email.userMsg('email格式不正确');
				objemail[0].focus();
				return false;
			}

			if (!password) {
				self.r_msg_password.userMsg('密码不能为空');
				objpassword[0].focus();
				return false;
			}

			if (!verify) {
				self.r_msg_vcode.userMsg('验证不能为空');
				objverify[0].focus();
				return false;
			}

			//objmsg.html('');
			_loading = true;

			var data = {
				"nickname" : username,
				"email" : email,
				"password" : password,
				"verify" : verify
			};

			$.post(APP + "Members/Register/saveReg", data, function(data) {
				if (data.indexOf("regOK") >= 0) {
					obj.dialog('close');
					self.ok_dialog.dialog('open');
					//window.location.href = APP + "Members/Index/";
				} else {
					_loading = false;
					if(data.search(/用户名/) >= 0){
						self.r_msg_username.userMsg(data);
						return false;
					}
					if(data.search(/密码/) >= 0){
						self.r_msg_password.userMsg(data);
						return false;
					}
					if(data.search(/email/i) >= 0){
						self.r_msg_email.userMsg(data);
						return false;
					}
					if(data.search(/验证码/) >= 0){
						self.r_msg_vcode.userMsg(data);
						return false;
					}
					self.r_msg_username.userMsg(data);
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
				self.l_msg_username.userMsg('用户名不能为空');
				objusername[0].focus();
				return false;
			}

			if (!password) {
				self.l_msg_password.userMsg('密码不能为空');
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
				console.log(resp);
				switch(resp.code) {
					case 200:
						if (window.opener && document.referrer) {
							window.opener.location.href = document.referrer;
							//window.opener.location.reload();
						}
						window.location.href = APP + "Index/indexV4.html";
						break;
					case 403:
						$(objusername[0]).val("");
						$(objpassword[0]).val("");
						self.l_msg_username.html(resp.content);
						break;
					case 501:
						self.l_msg_username.html(resp.content);
						break;
					case 502:
						self.l_msg_username.html(resp.content);
						break;
					case 503:
						self.l_msg_username.html(resp.content);
						break;
					case 504:
						self.l_msg_username.html(resp.content);
						break;
					case 505:
						/*
						var vcode = $('#J_Login').find('li.vcode');
						if (vcode.length === 0) {
							$('<li class="vcode">\n\<input class="ipt" type="text" name="vcode" placeholder="验证码" value="" /><img src="/Verify" alt="验证码" class="J_VerifyImg" title="点击刷新" />\n\</li>').insertAfter($('#J_Login input[name="password"]').parent("li"));
						} else {
							$('#J_Login').find('li.vcode img').click();
						}*/
						break;
					case 506:
						self.l_msg_username.html(resp.content);
						//obj.find('input[name="vcode"]').focus();
						//$('#J_Login').find('li.vcode img').click();
						break;
				}
			});
			return false;
		});
	},
	FindPass : function() {
		if (!self.f_Dialog) {
			var obj = self.f_Dialog = $('.user-find-password-dialog');
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

			self.f_msg_email = self.f_Dialog.find('.msg-email');
			self.f_msg_vcode = self.f_Dialog.find('.msg-vcode');

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

			$(document).on('mouseenter', 'input[name="email"], input[name="vcode"]', function() {
				$(this).select();
			});

			var _loading = false;

			obj.find('.submit-btn').on('click', function() {
				var objemail = obj.find('input[name="email"]');
				var objverify = obj.find('input[name="vcode"]');
				var email = objemail.val();
				var verify = objverify.val();

				if (!email) {
					self.f_msg_email.userMsg('请输入邮箱');
					objemail[0].focus();
					return false;
				}
				var result = email.match(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/);
				if (result == null) {
					self.f_msg_email.userMsg("请正确输入邮箱");
					objemail[0].focus();
					return false;
				}

				if (!verify) {
					self.f_msg_vcode.userMsg('验证码不能为空');
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
			var obj = self.f_Dialog;
			obj.find('input[name="email"]').val('');
			obj.find('input[name="vcode"]').val('');
			obj.dialog('open');
		}
	}
};

