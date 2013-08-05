$(function() {
	// 注册
	if ( $("#frm_reg")[0] ) {
		//
		$("#frm_reg input[name='nickname']").focus();
		
		//
		$("#verifyImg").click(function(){
			var timenow = new Date().getTime();
			$(this).attr("src", APP+'Verify?'+timenow);

		});
		
		/* 回车键响应 */
	    $("#frm_reg input").keydown(function(event){
	    	if( event.keyCode==13 ) {
	    		if ( $(this).attr('name')=='verify' ) {
	    			$("#btn_reg_sub").trigger("click");
	    		}
	    		else {
	    			$(this).parents("tr").next().find("input").focus();
	    		} 
	    	}
	    });
	    
	    /* 输入框失去焦点判断 */
	    $("#frm_reg input[name='nickname']").blur(function(){
	    	if ($.trim($(this).val()) == "") {
	    		$(this).parent("td").next("td").children(".must").css("display", 'inline-block');
	    	}
	    	else {
	    		$(this).parent("td").next("td").children(".must").css("display", 'none');
	    	}
	    })
	    $("#frm_reg input[name='password']").blur(function(){
	    	if ($.trim($(this).val()) == "") {
	    		$(this).parent("td").next("td").children(".must").css("display", 'inline-block');
	    	}
	    	else {
	    		$(this).parent("td").next("td").children(".must").css("display", 'none');
	    	}
	    })
	    $("#frm_reg input[name='repassword']").blur(function(){
	    	if ($.trim($(this).val()) == "") {
	    		$(this).parent("td").next("td").children(".must").text("　这里必填").css("display", 'inline-block');
	    	}
	    	else {
	    		if ( $(this).val() != $("#frm_reg input[name='password']").val() ) {
	    			$(this).parent("td").next("td").children(".must").text("　密码不一致").css("display", 'inline-block');
	    		}
	    		else {
	    			$(this).parent("td").next("td").children(".must").css("display", 'none');
	    		}
	    	}
	    })
	    $("#frm_reg input[name='verify']").blur(function(){
	    	if ($.trim($(this).val()) == "") {
	    		$(this).parent("td").next("td").children(".must").css("display", 'inline-block');
	    	}
	    	else {
	    		$(this).parent("td").next("td").children(".must").css("display", 'none');
	    	}
	    })
	    
		/* 注册 */
		$("#btn_reg_sub").click(function(){
			//
			var data = {"nickname":"","password":"","verify":""};
			//
			var obj = $("#frm_reg input[name='nickname']");
			if ($.trim(obj.val()) == "") {
				obj.parent("td").next("td").children(".must").css("display", 'inline-block');
				obj.focus();
				return false;
			}
			data.nickname = obj.val();
			//
			obj = $("#frm_reg input[name='password']");
			if (obj.val() == "") {
				obj.parent("td").next("td").children(".must").css("display", 'inline-block');
				obj.focus();
				return false;
			}
			data.password = obj.val();
			obj = $("#frm_reg input[name='repassword']");
			if (obj.val() == "") {
				obj.parent("td").next("td").children(".must").text("　这里必填").css("display", 'inline-block');
				obj.focus();
				return false;
			}
			if (obj.val() != $("#frm_reg input[name='password']").val()) {
				obj.parent("td").next("td").children(".must").text("　密码不一致").css("display", 'inline-block');
				obj.focus();
				return false;
			}
			//
			obj = $("#frm_reg input[name='verify']");
			if (obj.val() == "") {
				obj.parent("td").next("td").children(".must").css("display", 'inline-block');
				obj.focus();
				return false;
			}
			data.verify = obj.val();
			//
			$.post(URL+"/saveReg", data, 
			function(data){
				if ( data.indexOf("regOK") >= 0 ) {
					alert("注册成功！");
					$("#btn_reset").trigger("click");
					window.opener.location.reload();
					window.location.href = APP+"Member/index/";
				}
				else {
					$(".warning").html("<br />"+data);
				}
			}); 
		});
	}
	
	// 登录
	if ( $("#frm_login")[0] ) {
		//
		$("#frm_login input[name='username']").focus();
		
		/* 回车键响应 */
		$("#frm_login input[name='username']").keydown(function(event){
	    	  if(event.keyCode==13) {
	    		  $("#frm_login input[name='password']").focus();
	    	  }
	    });
		//
	    $("#frm_login input[name='password']").keydown(function(event){
	    	  if(event.keyCode==13) {
	    		  $("#btn_login_sub").trigger("click");
	    	  }
	    	  else {
	    		  if ($(this).val().length >= 16) {
	    			  $(this).val($(this).val().substr(0,15));
	    		  }
	    	  }
	    });
	    
	    /* 输入框失去焦点判断 */
	    $("#frm_login input[name='username']").blur(function(){
	    	if ($.trim($(this).val()) == "") {
	    		$(this).parent("td").next("td").children(".must").css("display", 'inline-block');
	    	}
	    	else {
	    		$(this).parent("td").next("td").children(".must").css("display", 'none');
	    	}
	    })
	    $("#frm_login input[name='password']").blur(function(){
	    	if ($.trim($(this).val()) == "") {
	    		$(this).parent("td").next("td").children(".must").css("display", 'inline-block');
	    	}
	    	else {
	    		$(this).parent("td").next("td").children(".must").css("display", 'none');
	    	}
	    })
		
	    /* 登录 */
		$("#btn_login_sub").click(function(){
			//
			var data = {"username":"","password":"","auto_login":0};
			//
			var obj = $("#frm_login input[name='username']");
			if ($.trim(obj.val()) == "") {
				obj.parent("td").next("td").children(".must").css("display", 'inline-block');
				obj.focus();
				return false;
			}
			data.username = obj.val();
			//
			obj = $("#frm_login input[name='password']");
			if (obj.val() == "") {
				obj.parent("td").next("td").children(".must").css("display", 'inline-block');
				obj.focus();
				return false;
			}
			data.password = obj.val();
            if(typeof $('[name="auto_login"]:checked').attr("name") !="undefined"){
                data.auto_login = 1;
            }
			//
			$.post(APP+"Member/checklogin", data, 
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
	
		/* 忘记密码 */
		function openEmail() {
		    Shadowbox.open({
		        player:     "html",
		        title:      "忘记密码",
		        content:    $("#pop_box").html(),
		        height:     180,
		        width:      500
		    });
		}
		//
		Shadowbox.init();
		//
		$("#btn_miss_pwd").click(function(){
			openEmail();
			window.setTimeout(function(){$(".email").each(function(index,domEle){$(domEle).focus();});}, 1500);
		});
		//
		$(document).on('click', '.div_btn .btn_cancel', function(){
			Shadowbox.close();
		});
		/* 回车键响应 */
	    $(document).on('keydown', '.div_email .email', function(event){
	    	  if(event.keyCode==13) {
	    		  $(this).siblings('.div_btn').children(".btn_sub_email").trigger("click");
	    	  }
	    });
		//
		$(document).on('click', '.div_btn .btn_sub_email', function(){
			//
			var obj = $(this).parent(".div_btn").siblings(".email");
			if (obj.val() == "") {
				alert("请输入邮箱.");
				obj.focus();
				return false;
			}
			var result = obj.val().match(/^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/);
			if (result == null) {
				alert("请正确输入邮箱.");
				obj.focus();
				return false;
			}
			//
			var shellnow = obj.parent(".div_email");
			shellnow.html("<img src='"+PUBLIC+"/skin/images/jdt.gif' style='padding:35px 0 0 180px;' />");
			//
			$.post(URL+"/missPwd", {
				email: obj.val()
			}, 
			function(data){
				if ( data.indexOf("sendOK") >= 0 ) {
					var mailserver = data.split('|');
					alert("请进入您的账户邮箱获取新密码。");
					obj.val('');
					Shadowbox.close();
					window.open("http://" + mailserver[1]);
				}
				else {
					shellnow.html("请输入您的注册邮箱：<input type='text' class='email' /><br /><br /><div class='div_btn'><input type='button' class='btn_sub_email' value=' 确 定 ' />&nbsp;<input type='button' class='btn_cancel' value=' 取 消 ' /></div><br />&nbsp;");
					alert(data);
				}
			}); 
			//
		});
	}
	

});

