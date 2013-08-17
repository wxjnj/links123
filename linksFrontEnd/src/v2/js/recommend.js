$(function() {
	// 回车键响应 
	$("#frm_rec input[name='title']").keydown(function(event){
		if(event.keyCode==13) {
			$("#frm_rec input[name='link']").focus();
		}
	});
	$("#frm_rec input[name='link']").keydown(function(event){
		if(event.keyCode==13) {
			$("#frm_rec textarea[name='intro']").focus();
		}
	});
	//
	$("input[type='text']").focus(function cls() {
		with(event.srcElement)
		if (value == defaultValue) value = "";
	});
	$("input[type='text']").blur(function res() {
		with(event.srcElement)
		if (value == "") value = defaultValue;
	});
	
	//推荐链接请输入简介
	$("#tjlj_txte").focus(function() {
		if ($(this).val() == "请输入简介") {
			$(this).val("");
			$(this).css("color", "#666");
			$("#tjlj_txte").trigger("keyup");
		};
	});
	//
	$("#tjlj_txte").keyup(function() {
		if (1000 - $(this).val().length < 0) {
			$(this).val(txtare);
		} else {
			txtare = $(this).val();
		}
		var thislength = $(this).val().length;
		$(this).next("div").find("span").html(1000 - thislength);
	});
	//
	$("#tjlj_txte").blur(function() {
		if ($(this).val() == "") {
			$(this).val("请输入简介").css("color", "#999");
			$("#tjlj_txte").trigger("keyup");
		}
	});
	//
	$("#tjlj_txte").trigger("keyup");
	
	//
	$("#frm_rec input[name='language']").click(function(){
		window.location.href = URL+"/recommend/lan/"+$(this).val();
	});
	
	//
	$(".just_name").click(function(){
		$(".tjlj_ul > .just_name").show("fast");
		$(".tjlj_ul > .tjlj_li").fadeOut("fast");
		$(this).fadeOut("fast");
		$(this).next(".tjlj_li").show("fast");
	});
	
	//
	$(".tjlj_pic").click(function(){
		var obj = $(this).parent(".tjlj_li");
		obj.hide("fast");
		obj.prev(".just_name").fadeIn("fast");
	});
	
	/* 提交 */
	$("#btn_sub_rec").click(function(){
		//easyui错误提示
		var top = $(".tjlj_tit01").offset().top + 250;
		var left = $(".tjlj_tit01").offset().left + 385;

		//
		var data = {"id":"","language":"","category":"","grade":"","title":"","link":"","intro":""};
		//
		if ( !$("#frm_rec input[name='language']:checked")[0] ) {
			$.messager.show({
				msg: "<span  class='messager_span'>请选择语言.</span>",
				showType: 'fade',
				width: 150,
				height: 45,
				timeout: 4000,
				style: {
					left: left,
					top: top
				}
			});
			return false;
		}
		data.language = $("#frm_rec input[name='language']:checked").val();
		//
		if ( !$("#frm_rec input[name='category']:checked")[0]) {
			$.messager.show({
				msg: "<span  class='messager_span'>请选择分类目录.</span>",
				showType: 'fade',
				width: 150,
				height: 45,
				timeout: 4000,
				style: {
					left: left,
					top: top
				}
			});
			return false;
		}
		data.category = $("#frm_rec input[name='category']:checked").val();

		//
		data.grade = $("#frm_rec input[name='grade']:checked").val();
		//
		var obj = $("#frm_rec input[name='title']");
		if (obj.val() == "" || obj.val() == "请输入标题") {
			$.messager.show({
				msg: "<span  class='messager_span'>请输入标题.</span>",
				showType: 'fade',
				width: 150,
				height: 45,
				timeout: 4000,
				style: {
					left: left,
					top: top
				}
			});
			obj.focus();
			return false;
		}
		data.title = obj.val();
		//
		var obj = $("#frm_rec input[name='link']");
		if (obj.val() == "" || obj.val() == "请输入链接") {
			$.messager.show({
				msg: "<span  class='messager_span'>请输入链接.</span>",
				showType: 'fade',
				width: 150,
				height: 45,
				timeout: 4000,
				style: {
					left: left,
					top: top
				}
			});
			obj.focus();
			return false;
		}
		data.link = obj.val();
		//
		obj = $("#tjlj_txte");
		if (obj.val() == "" || obj.val() == "请输入简介") {
			$.messager.show({
				msg: "<span  class='messager_span'>请输入简介.</span>",
				showType: 'fade',
				width: 150,
				height: 45,
				timeout: 4000,
				style: {
					left: left,
					top: top
				}
			});
			obj.focus();
			return false;
		}
		data.intro = obj.val();
		//
		data.id = $("#frm_rec input[name='id']").val();
		//
		$.post(URL+"/saveRecommend", data, 
		function(data){
			if ( data.indexOf("addOK") >= 0 ) {
				$.messager.show({
					msg: "<span  class='messager_span'>链接提交成功！</span>",
					showType: 'fade',
					width: 150,
					height: 45,
					timeout: 4000,
					style: {
						left: left,
						top: top
					}
				});
				$("#btn_reset").trigger("click");
				window.location.reload();
			}
			else if ( data.indexOf("editOK") >= 0 ) {
				$.messager.show({
					msg: "<span  class='messager_span'>链接编辑成功！</span>",
					showType: 'fade',
					width: 150,
					height: 45,
					timeout: 4000,
					style: {
						left: left,
						top: top
					}
				});
			}
			else {
				alert(data);
			}
		}); 
	});

	//
	if ( $("#alt").val()=="1" ) {
		$.messager.show({
			msg: "<span  class='messager_span'>对不起，该网站尚未被另客收录，您可以在本页推荐该网站!</span>",
			showType: 'fade',
			width: 150,
			height: 45,
			timeout: 4000,
			style: {
				left: left,
				top: top
			}
		});
	}

});

