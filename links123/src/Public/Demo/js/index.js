var APP = $CONFIG['APP'];
var URL = $CONFIG['URL'];
var PUBLIC = $CONFIG['PUBLIC'];

$(function() {

	$('.skins').on('click', function(){
		$('#J_Skins').attr('href', '__PUBLIC__/Demo/skins/light/style.css');
	});
	
	// 弹出页
	$(".newWin").on('click', function() {
		window.open($(this).attr('url'));
	});
	
	// 直达框
	$(document).on('click', function(){
		$('#direct_text').val($('#direct_text').attr('txt')).removeClass('ipton');
	});
	$("#header").on('mouseenter', function(){
		var tag = $.trim($('#direct_text').val());
		if(tag == $('#direct_text').attr('txt')){
			$("#direct_text").select().addClass('ipton');
		}else{
			$("#direct_text").addClass('ipton');
		}
	}).on('mouseleave', function(){
		var tag = $.trim($('#direct_text').val());
		if(tag == '' || tag == $('#direct_text').attr('txt')){
			$('#search_text').select();
			$('#direct_text').removeClass('ipton');
		}
	});
	$("#direct_text").on('click', function(){
		var tag = $.trim($('#direct_text').val());
		if (tag == $('#direct_text').attr('txt')){
			$('#direct_text').val('').addClass('ipton');
		}
		return false;
	});

	$('.J_direct_submit').on('click', function(){
		$("#frm_drct").trigger('submit');
		$("#direct_text")[0].focus();
		return false;
	});
	$("#frm_drct").on('submit', function(){
		var tag = $.trim($('#direct_text').val());
		if (tag == '' || tag == $('#direct_text').attr('txt')){
			return false;
		}
		$('#direct_text').select();
	});
	
	//保存自留地网址
	$('.J_myarea_web_save').click(function() {
		var id = $('#J_myarea_id').val();
		var web_name= $('#J_myarea_web_name').val();
		var url= $('#J_myarea_web_url').val();
		var t = getLength(url);

		$('#J_myarea_web_name').trigger('keyup');

		if(t == 0){
			$('#J_myarea_tip').text('链接为空!').css('color', '#f00');
			return false;
		}

		$.post(URL + "/updateArealist", {
				id: id,
				web_name: web_name,
				url: url
			},
			function(data) {
				if (data.indexOf("updateOK") >= 0) {
					var myarea_web_obj = $('.J_myarea_div ul li[id="'+id+'"] span');
					$('.J_myarea_div ul li[id="'+id+'"] span b').text(web_name);
					var new_url = myarea_web_obj.attr('url').replace(myarea_web_obj.attr('data-url'), url);
					myarea_web_obj.attr('url', new_url);
					myarea_web_obj.attr('data-url', url);
					$('#J_myarea_tip').text('保存成功!').css('color', '#d20015');
					$('#J_myarea_tip').show();

				} else {
					$('#J_myarea_tip').text('保存失败!').css('color', '#f00');
					$('#J_myarea_tip').show();
				}
				setTimeout(function(){
					$('#J_myarea_tip').hide();
					$('.J_zld_edit_box').hide();
				}, 2000);
		});
	});

	$('#J_myarea_web_url').keypress(function(event) {
		if (event.keyCode == 13) {
			$(".J_myarea_web_save").trigger("click");
			return false;
		}
	});
	
});

