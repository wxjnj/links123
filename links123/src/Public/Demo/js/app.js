$(function(){
	User.Init();
	Zld.Init();
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
		if(!$('#J_Reg').size()){
			var hl = '';
			hl = hl + '<div class="lk-dialog lk-dialog-reg" id="J_Reg">';
			hl = hl + '	<div class="lkd-hd">';
			hl = hl + '		<em>快速注册</em>';
			hl = hl + '		<a class="close" href="javascript:;">X</a>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-bd">';
			hl = hl + '		<form action="">';
			hl = hl + '			<ul>';
			hl = hl + '				<li><input class="ipt" type="text" name="" id="J_signin_user" placeholder="昵称" /></li>';
			hl = hl + '				<li><input class="ipt" type="text" name="" id="J_signin_email" placeholder="邮箱" /></li>';
			hl = hl + '				<li><input class="ipt" type="text" name="" id="J_signin_password" placeholder="密码" /></li>';
			hl = hl + '				<li class="vcode">';
			hl = hl + '					<input class="ipt" type="text" name="" id="vcode" placeholder="验证码" /><img src="/Verify" alt="验证码" class="J_VerifyImg" title="点击刷新" />';
			hl = hl + '				</li>';
			hl = hl + '			</ul>';
			hl = hl + '		</form>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-ft">';
			hl = hl + '		<a class="lkd-reg J_SignUp" href="#">注册</a>';
			hl = hl + '		<a class="lkd-login J_SignIn" href="#">已有帐号！登录！</a>';
			hl = hl + '	</div>';
			hl = hl + '</div>';
			$('body').append(hl);

			$('#J_Reg').dialog({
				autoOpen: true,
				width: 384,
				modal: true,
				resizable: false,
				open: function(){
					setTimeout(function(){$('#J_signin_user').select();}, 20);
				}
			});

			$('#J_Reg').find('.close').on('click', function(){
				$('#J_Reg').dialog('close');
			});

		}else{
			$('#J_Reg').dialog('open');
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
			hl = hl + '				<li><input class="ipt" type="text" name="" id="J_signin_user" placeholder="邮箱/账户/手机号" /></li>';
			hl = hl + '				<li><input class="ipt" type="text" name="" id="J_signin_password" placeholder="密码" /></li>';
			hl = hl + '				<li class="rpass">';
			hl = hl + '					<span>还不是会员？<a class="reg" href="javascript:;">注册</a></span>';
			hl = hl + '					<label for=""><input type="checkbox" name="" id=""> 记住密码</label> <a class="fgpass" href="javascript:;">忘记密码？</a>';
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
		}else{
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
}