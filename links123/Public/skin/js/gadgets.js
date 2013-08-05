
$(function(){

/** $计算器 * */
$('#J_calc').click(function(){
	
	$('#J_calc_iframe').attr('src', 'http://app.yesky.com/cms/jsq/');
	
	$.fancybox({
		href: '#J_box_calc',
		//closeBtn : false,
		helpers:  {
	        title:  null,
	        overlay : null
	    },
	    margin : [35, 0, 0, 650],
	    topRatio : 0,
	    leftRatio : 0,
	    width: 550,
	    height: 600,
	    autoSize: false
	    
	});
	
	return false;
});

$('#J_box_calc_list a').click(function() {
	$('#J_calc_iframe').attr('src', $(this).attr('data-url'));
	return false;
});

/** 计算器$ **/

/** $便签 * */
var stikynot_num = 1;
var stikynot_show_num = 0;
var stikynot_close_num = 0;
var stikynot_id = 1;
var stikynot_max_id = 1;
var _x,_y;// 鼠标离控件左上角的相对位置
var _w = 350,_h=439;
var _c = 'bg_y';
var isShowStikynot = false;

$(document).on('click', '#J_stikynot', function(){
	//$.cookies.set('stikynot_num', '0');
	var cookie_stikynot_num = $.cookies.get('stikynot_num');
	stikynot_num = cookie_stikynot_num ? cookie_stikynot_num : stikynot_num;
	stikynot_max_id = stikynot_num;
	
	if (!isShowStikynot) {
		isShowStikynot = true;
		
		var stikynotData;
		var stikynotIsNullNUm = 0;
		
		for (var i = 1; i <= stikynot_num; i++) {
			
			stikynotData = eval($.cookies.get('stikynot' + i));
			if (typeof stikynotData != "undefined") {
				if (stikynotData == null) {
					++stikynotIsNullNUm;
					continue;
				}
				
				if (stikynotData != '' && stikynotData != null && stikynotData.t) {
					stikynotShow(i, stikynotData.x, stikynotData.y, stikynotData.w, stikynotData.h, stikynotData.t, stikynotData.c);
					stikynot_show_num++;
				} else if (stikynot_num <= 1) {
					stikynotShow(i, 0, 0, _w, _h, '', _c);
				} else {
					++stikynotIsNullNUm;
				}
			}else {
				++stikynotIsNullNUm;
			}
		}
		
		if (stikynotIsNullNUm >= stikynot_num) {
			stikynot_num = 1;
			$.cookies.set('stikynot_num', stikynot_num);
			stikynotShow(1, 0, 0, _w, _h, '', _c);
		}
		
		$('.J_stikynot_text').select();
	} else {
		isShowStikynot = false;
		for (var i = 1; i <= stikynot_num; i++) {
			$('#J_box_stikynot_' + i).dialog('close');
		}
	}
	return false;
});

//$(".resizable").css({'overflow' : 'hidden'}).parent().css({
//	'display' : 'inline-block',
//	'overflow' : 'hidden',
//	'height' : function() {
//		return $('.resizable', this).height();
//	},
//	'width' : function() {
//		return $('.resizable', this).width();
//	},
//	'paddingBottom' : '12px',
//	'paddingRight' : '12px'
//
//}).resizable().find('.resizable').css({
//	overflow : 'auto',
//	width : '100%',
//	height : '100%'
//});

//add stikynot
$(document).on('click', '.J_stikynot_add', function(){
	
	++stikynot_max_id;
	stikynot_show_num++;
	
	var stikynot = '<div id="J_box_stikynot_' + stikynot_max_id + '" class="box_stikynot J_box_stikynot" data-id="' + stikynot_max_id + '">';
	stikynot += '<div class="box_stikynot_head" id="J_box_stikynot_head">';
	stikynot += '<div class="box_stikynot_bar box_stikynot_add J_stikynot_add">';
	stikynot += '<a href="#" title="新建便签" class="">add</a>';
	stikynot += '</div>';
	stikynot += '<div class="box_stikynot_color">';
	stikynot += '<div class="box_stikynot_color_bar color_b" data-class="bg_b"></div>';
	stikynot += '<div class="box_stikynot_color_bar color_g" data-class="bg_g"></div>';
	stikynot += '<div class="box_stikynot_color_bar color_pink" data-class="bg_pink"></div>';
	stikynot += '<div class="box_stikynot_color_bar color_p" data-class="bg_p"></div>';
	stikynot += '<div class="box_stikynot_color_bar color_w" data-class="bg_w"></div>';
	stikynot += '<div class="box_stikynot_color_bar color_y" data-class="bg_y"></div>';
	stikynot += '</div>';
	stikynot += '<div class="box_stikynot_bar box_stikynot_del J_stikynot_del">';
	stikynot += '<a href="#" title="删除便签" class="">del</a>';
	stikynot += '</div>';
	stikynot += '<div style="clear: float;"></div>';
	stikynot += '</div>';
	stikynot += '<div class="box_stikynot_text"><textarea name="text" class="J_stikynot_text bg_y"';
	stikynot += '></textarea></div>';
	stikynot += '</div>';
	
	_x = 100 * stikynot_max_id;
	_y = 50 * stikynot_max_id;
	_w = 350;
	_h = 441;
	
	$(stikynot).dialog({
		title:'',
		width: _w,
		height: _h,
		minWidth: 350,
		minHeight: 441,
		position: [_x, _y]
	});
	
	stikynot_id = stikynot_max_id;
	$('.ui-dialog').resizable({ alsoResize: '.box_stikynot_head,.box_stikynot_text,.J_stikynot_text',autoHide: true });  
});

//save stikynot
$(document).on('keypress', '.J_stikynot_text', function(){
	stikynot_id = $(this).parents('.J_box_stikynot').attr('data-id');
	stikynotSave(stikynot_id);
});

//del stikynot
$(document).on('click', '.J_stikynot_del', function(){
	stikynot_id = $(this).parents('.J_box_stikynot').attr('data-id');
	$('#J_box_stikynot_' + stikynot_id).find('.J_stikynot_text').val('');
	$.cookies.set('stikynot' + stikynot_id, '');
});

function stikynotShow(stikynot_id, x, y, w, h, _t, c) {
	
	var stikynot = '#J_box_stikynot_' + stikynot_id;
	if (stikynot_id > 1) {
		if (typeof ($('#J_box_stikynot_' + stikynot_id).attr('data-id')) == "undefined") {
			stikynot = '<div id="J_box_stikynot_' + stikynot_id + '" class="box_stikynot J_box_stikynot" data-id="' + stikynot_id + '">';
			stikynot += '<div class="box_stikynot_head" id="J_box_stikynot_head">';
			stikynot += '<div class="box_stikynot_bar box_stikynot_add J_stikynot_add">';
			stikynot += '<a href="#" title="新建便签" class="">add</a>';
			stikynot += '</div>';
			stikynot += '<div class="box_stikynot_color">';
			stikynot += '<div class="box_stikynot_color_bar color_b" data-class="bg_b"></div>';
			stikynot += '<div class="box_stikynot_color_bar color_g" data-class="bg_g"></div>';
			stikynot += '<div class="box_stikynot_color_bar color_pink" data-class="bg_pink"></div>';
			stikynot += '<div class="box_stikynot_color_bar color_p" data-class="bg_p"></div>';
			stikynot += '<div class="box_stikynot_color_bar color_w" data-class="bg_w"></div>';
			stikynot += '<div class="box_stikynot_color_bar color_y" data-class="bg_y"></div>';
			stikynot += '</div>';
			stikynot += '<div class="box_stikynot_bar box_stikynot_del J_stikynot_del">';
			stikynot += '<a href="#" title="删除便签" class="">del</a>';
			stikynot += '</div>';
			stikynot += '<div style="clear: float;"></div>';
			stikynot += '</div>';
			stikynot += '<div class="box_stikynot_text"><textarea name="text" class="J_stikynot_text ' + (c ? c : _c) +'"';
			if (w > 350) stikynot += 'style="width: ' + (w -3) +'px"';
			stikynot += '>' + _t + '</textarea></div>';
			stikynot += '</div>';
		}
	} else {
		var textObj = $(stikynot).find('.J_stikynot_text');
		textObj.removeClass();
		textObj.addClass('J_stikynot_text ' + c);
		textObj.val(_t).css('width', (w ? w : _w));
	}
	
	$(stikynot).dialog({
		title:'',
		width: w ? w : _w,
		height: h ? h : _h,
		minWidth: 350,
		minHeight: 441,
		position: (x && y) ? [x, y] : '',
		open : function(e, ui){
			_w = 350,_h=439;
		},
		dragStop: function(e, ui){
			_x = ui.position.left;
			_y = ui.position.top;
			stikynotSave(stikynot_id, _x, _y);
		},
		resizeStop: function(e, ui){
			_w = ui.size.width;
			_h = ui.size.height;
			stikynotSave(stikynot_id, 0, 0, _w, _h);
		},
		close: function(e, ui){
			stikynotSave(stikynot_id, 0, 0, 0, 0, true);
		}
	}); 
	$(stikynot).parents('.ui-dialog').resizable({ alsoResize: '.box_stikynot_head,.box_stikynot_text,.J_stikynot_text',autoHide: true }); 
}

function stikynotSave(stikynot_id, x, y, w, h, t, c) {
	var data = eval($.cookies.get('stikynot' + stikynot_id));
	
	if (t == true) {
		if (typeof data != "undefined" && data != null){
			var t = $('#J_box_stikynot_' + stikynot_id).find('.J_stikynot_text').val();
			data.t = t!=null && typeof t != "undefined"? t : '';
		}
	
		++stikynot_close_num;
		if (stikynot_close_num >= stikynot_show_num) {
			isShowStikynot = false;
		}
		
	} else if (c != '' && typeof c != "undefined" && typeof data != "undefined" && data != null) {
		var t = $('#J_box_stikynot_' + stikynot_id).find('.J_stikynot_text').val();
		data.t = t;
		data.c = c;
	} else {
		data = {
				'x' : x ? x : _x,
				'y' : y ? y : _y,
				'w' : w ? w : _w,
				'h' : h ? h : _h,
				'c' : c ? c : _c,
				't' : $('#J_box_stikynot_' + stikynot_id).find('.J_stikynot_text').val()
		};
		
		var box_stikynot_tip = $('#J_box_stikynot_' + stikynot_id).find('.box_stikynot_tip');
		
	}
	//console.log(stikynot_id, data);
	$.cookies.set('stikynot' + stikynot_id, data);
	$.cookies.set('stikynot_num', stikynot_max_id);

}

$(document).on('click', '.box_stikynot_color_bar', function(){
	
	stikynot_id = $(this).parents('.J_box_stikynot').attr('data-id');
	
	var textObj = $('#J_box_stikynot_' + stikynot_id).find('.J_stikynot_text')
	
	_c = $(this).attr('data-class');
	
	textObj.removeClass();
	textObj.addClass('J_stikynot_text ' + _c);
	
	stikynotSave(stikynot_id, 0, 0, 0, 0, '', _c);
});
$(document).on('mouseover', '.box_stikynot_color_bar', function(){
	$(this).css('opacity', '1');
});
$(document).on('mouseout', '.box_stikynot_color_bar', function(){
	$('.box_stikynot_color_bar').css('opacity', '0.5');
});
/** 便签$ **/

/** $闹钟 **/
$('#J_clock').click(function(){
	
	$('#J_clock_iframe').attr('src', 'http://qishi8.duapp.com/nz/');
	
	$.fancybox({
		href: '#J_box_clock',
		//closeBtn : false,
		helpers:  {
	        title:  null,
	        overlay : null
	    },
	    margin : [35, 0, 0, 650],
	    topRatio : 0,
	    leftRatio : 0,
	    width: 550,
	    height: 600,
	    autoSize: false
	    
	});
	
	return false;
});

$('#J_box_clock_list a').click(function() {
	$('#J_clock_iframe').attr('src', $(this).attr('data-url'));
	return false;
});
/** 闹钟$ **/
});