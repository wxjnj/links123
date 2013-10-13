var HelpMouse = {
	Init: function(){
		var self = this;
		var isSearchTxtSelected = false;
		var mouseOnTopNavBar = 0;

		//当页面翻过首屏时，通过坐标判断直达栏是否获取焦点的方法就不再适用，
		//这里增加鼠标移至直达栏直接获取焦点
		$(document).on('mousemove', '#direct_text', function(){
			if($('#direct_text').val() == $('#direct_text').attr('txt')){
				$('#direct_text').select().removeClass('ipton');
				isSearchTxtSelected = false;
				if($.trim($('#search_text').val()) ==""){
					$('#J_thl_div').hide();
				}
			}
			//在直达栏上移动鼠标，不冒泡，避免与ev坐标判断焦点方法冲突
			return false;
		});

		//通过顶部nav给鼠标位置增加来源属性，强化ev位置获取焦点的判断能力
		$(document).on('mouseenter', '.top-nav-inner', function(){
			if(mouseOnTopNavBar == 0) mouseOnTopNavBar = 1;
		}).on('mouseleave', '.top-nav-inner', function(){
				mouseOnTopNavBar = 0;
			}).on('mousemove', '#direct_text, #J_direct_submit', function(){
				mouseOnTopNavBar = 2;
			});

		$(document).on('mousemove', function(ev){
			var isNeedHelp = 1;
			$('.links123-app-frame').each(function(){
				if($(this).is(":visible")){
					isNeedHelp ? isNeedHelp = 0 : '';
				}
			});
			// $('.fancybox-wrap').each(function(){
			// if($(this).is(":visible")){
			// isNeedHelp ? isNeedHelp = 0 : '';
			// }
			// });
			if(!isNeedHelp){ return false; }
			var mousePos = self.getcoords(ev);

			var $search_text = $('#search_text');
			var $direct_text = $('#direct_text');
			var search_text_left_end_pos = $search_text.offset().left - 10;
			var search_text_bottom_edn_pos = $search_text.offset().top + $search_text.height();
			var search_text_right_end_pos = search_text_left_end_pos + $search_text.width();
			var direct_text_right_end_pos = $direct_text.offset().left + $direct_text.width() + 10;

			var $header = $('.header')
			var header_bottom_end_pos = $header.offset().top + $header.height();

			var $zld = $('.zld');
			var zld_bottom_end_pos = $zld.offset().top + $zld.height();
			//向下滚800px后不再判断焦点
			if($(window).scrollTop() > 800) return;

			if(mouseOnTopNavBar == 1){
				if($('#direct_text').val() == $('#direct_text').attr('txt')){
					$('#direct_text').select().removeClass('ipton');
					isSearchTxtSelected = false;
					if($.trim($('#search_text').val()) ==""){
						$('#J_thl_div').hide();
					}
				}
				return;
			}
			//search_text_bottom_edn_pos,header_bottom_end_pos

			if((mousePos.y < header_bottom_end_pos) && (mousePos.x < search_text_left_end_pos)){
				if($('#direct_text').val() == $('#direct_text').attr('txt')){
					$('#direct_text').select().removeClass('ipton');
					isSearchTxtSelected = false;
					if($.trim($('#search_text').val()) ==""){
						$('#J_thl_div').hide();
					}
				}
			}//else{

			if((mousePos.y < header_bottom_end_pos)  && (mousePos.x > search_text_left_end_pos)){
				if($('#J_thl_div').is(':hidden') && $('#J_thl_div').attr('data-hide') == 'true'){
					$('#J_thl_div').attr('data-hide', 'false').show();
				}
			}
			if((mousePos.y > header_bottom_end_pos && mousePos.y < zld_bottom_end_pos) || mousePos.x > search_text_left_end_pos){
				$('#direct_text').val($('#direct_text').attr('txt')).addClass('ipton');
				if($('#J_thl_div').is(':hidden') && $('#J_thl_div').attr('data-hide') == 'true'){
					return;
				}
				if(!isSearchTxtSelected){
					$('#search_text').select().trigger('mouseenter');
					isSearchTxtSelected = true;
				}
			}
			if(mousePos.y > zld_bottom_end_pos){
				$('#J_thl_div').attr('data-hide', 'true').hide();
			}

		});
	},
	getcoords: function(ev){
		if(ev.pageX || ev.pageY){
			return { x: ev.pageX, y: ev.pageY };
		}
		return{
			x: ev.clientX + document.body.scrollLeft - document.body.clientLeft,
			y: ev.clientY + document.body.scrollTop// - document.body.clientTop
		};
	}
};
