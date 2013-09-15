$(function(){

	$('#J_apps_add').click(function(){
		alert('Coming soon...');
	});
	$('#J_weather').click(function(){
//      $.post("/Weather/index", function(data){
//          $('#J_box_weather').html(data);
//      }); 
		
		$.fancybox({
			href: '#J_box_weather',
			helpers:  {
				title:  null,
				overlay : null
			},
			width: 380,
			height: 240,
			autoSize: false
		});
		
		return false;
	});
	
	//听音乐 add by tellen 20130906 
	var g_music_currentkey = -1;
	$('#J_music').dblclick(function(){
		music_box_proc();
	});
	
	$('#J_music').click(function(){
		
		if(g_music_currentkey != -1){
			if ($('#J_box_music').is(":hidden")){
			  $('#J_box_music').show(); 
			  $('.music_button').show();
			  $('.music_button_min').show();
			}
			else {
				$('#J_box_music').hide(); 
				$('.music_button').hide();
				$('.music_button_min').hide();
			}
			return;
		}
		music_box_proc();

	});
	
	$('.music_button').click(function(){
			$('#J_music_iframe').attr('src','');
			$('.music_button').hide();
			$('.music_button_min').hide();
			g_music_currentkey = -1;
	}); 
	$('.music_button_min').click(function(){
			$('#J_box_music').hide(); 
		$("#bModal").trigger("click");
			//$('.music_button').hide();
			//$('.music_button_min').hide();
	});
	
	function music_box_proc(){
		if(-1 == g_music_currentkey){
			g_music_currentkey = 0;
		}
		var musicurl = {
			1:'http://web.kugou.com/default.html',
			2:'http://kuang.xiami.com/res/kuang/xiamikuang0709.swf',
			3:'http://y.qq.com/player/',
			0:'http://douban.fm/swf/53053/radioplayer.swf'
		};
		$.each(musicurl, function(key, value){
			if (g_music_currentkey == key)  {
				g_music_currentkey++;

				g_music_currentkey = g_music_currentkey % 4;
			
				return false;
			}
		});
		var myposition=[200,10];
		//适配不同播放器
		switch(g_music_currentkey){
			case 0:{//douban
				$('#J_music_iframe').attr('width', 450);
				$('#J_music_iframe').attr('height', 170);
					
				myposition = [$(window).height()-300,$(window).width()-580];
				break;
			}
			case 2:{//xiami
				$('#J_music_iframe').attr('width', 530);
				$('#J_music_iframe').attr('height', 250);
					
				myposition = [$(window).height()-280,$(window).width()-680];
				break;
			}
			case 1:{//kugou
				$('#J_music_iframe').attr('width', 320);
				$('#J_music_iframe').attr('height', 150);
				myposition = [$(window).height()-300,$(window).width()-450];
				break;
			}
			case 3:{//qq
				$('#J_music_iframe').attr('width', 360);
				$('#J_music_iframe').attr('height', 380);
				myposition = [$(window).height()-450,$(window).width()-550];
				//myposition = [400,300];
				break;
			}
		}

		$('#J_box_music').bPopup({
			position :myposition,
			modal : false
		}); 
		$('#J_music_iframe').attr('src', musicurl[g_music_currentkey]);

		$('.music_button').show();
		$('.music_button_min').show();
	}
	
	//fancybox esc
	$(document).on('keyup', function(ev){
		if(ev.keyCode == 27){
			$.fancybox.close();
			$('#search_text').select();
		}
	});
		
	/** $计算器 * */
	$('#J_calc').click(function(){
		
		$('#J_calc_iframe').attr('src', 'http://qiqiapp3.duapp.com/yuyinjisuanqi/');
		
		$.fancybox({
			href: '#J_box_calc',
			//closeBtn : false,
			helpers:  {
				title:  null,
				overlay : null
			},
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
	var _c = 'bg_1';
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
				$.cookies.set('stikynot_num', stikynot_num); //TODO 兼容模式下从这往下不执行了
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
	//  'display' : 'inline-block',
	//  'overflow' : 'hidden',
	//  'height' : function() {
	//      return $('.resizable', this).height();
	//  },
	//  'width' : function() {
	//      return $('.resizable', this).width();
	//  },
	//  'paddingBottom' : '12px',
	//  'paddingRight' : '12px'
	//
	//}).resizable().find('.resizable').css({
	//  overflow : 'auto',
	//  width : '100%',
	//  height : '100%'
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
		stikynot += '<div class="box_stikynot_color_bar color_1" data-class="bg_1"></div>';
		stikynot += '<div class="box_stikynot_color_bar color_2" data-class="bg_2"></div>';
		stikynot += '<div class="box_stikynot_color_bar color_3" data-class="bg_3"></div>';
		stikynot += '<div class="box_stikynot_color_bar color_4" data-class="bg_4"></div>';
		stikynot += '<div class="box_stikynot_color_bar color_5" data-class="bg_5"></div>';
		stikynot += '<div class="box_stikynot_color_bar color_6" data-class="bg_6"></div>';
		// stikynot += '<div class="box_stikynot_color_bar color_b" data-class="bg_b"></div>';
		// stikynot += '<div class="box_stikynot_color_bar color_g" data-class="bg_g"></div>';
		// stikynot += '<div class="box_stikynot_color_bar color_pink" data-class="bg_pink"></div>';
		// stikynot += '<div class="box_stikynot_color_bar color_p" data-class="bg_p"></div>';
		// stikynot += '<div class="box_stikynot_color_bar color_w" data-class="bg_w"></div>';
		// stikynot += '<div class="box_stikynot_color_bar color_y" data-class="bg_y"></div>';
		stikynot += '</div>';
		stikynot += '<div class="box_stikynot_bar box_stikynot_del J_stikynot_del">';
		stikynot += '<a href="#" title="删除便签" class="">del</a>';
		stikynot += '</div>';
		stikynot += '<div style="clear: float;"></div>';
		stikynot += '</div>';
		stikynot += '<div class="box_stikynot_text"><textarea name="text" class="J_stikynot_text bg_1"';
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
			minWidth: 370,
			minHeight: 441,
			dialogClass: 'ui-stikynot',
			position: [_x, _y]
		});
		
		stikynot_id = stikynot_max_id;
		$('.ui-dialog').resizable({ alsoResize: '.box_stikynot_head,.box_stikynot_text,.J_stikynot_text',autoHide: true });
		
		$('.J_stikynot_text').select();
		return false;
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
		return false;
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
				stikynot += '<div class="box_stikynot_color_bar color_1" data-class="bg_1"></div>';
				stikynot += '<div class="box_stikynot_color_bar color_2" data-class="bg_2"></div>';
				stikynot += '<div class="box_stikynot_color_bar color_3" data-class="bg_3"></div>';
				stikynot += '<div class="box_stikynot_color_bar color_4" data-class="bg_4"></div>';
				stikynot += '<div class="box_stikynot_color_bar color_5" data-class="bg_5"></div>';
				stikynot += '<div class="box_stikynot_color_bar color_6" data-class="bg_6"></div>';
				// stikynot += '<div class="box_stikynot_color_bar color_b" data-class="bg_b"></div>';
				// stikynot += '<div class="box_stikynot_color_bar color_g" data-class="bg_g"></div>';
				// stikynot += '<div class="box_stikynot_color_bar color_pink" data-class="bg_pink"></div>';
				// stikynot += '<div class="box_stikynot_color_bar color_p" data-class="bg_p"></div>';
				// stikynot += '<div class="box_stikynot_color_bar color_w" data-class="bg_w"></div>';
				// stikynot += '<div class="box_stikynot_color_bar color_y" data-class="bg_y"></div>';
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
			minWidth: 370,
			minHeight: 441,
			position: (x && y) ? [x, y] : '',
			dialogClass: 'ui-stikynot',
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
		$('.J_stikynot_text').select();
	});
	// $(document).on('mouseover', '.box_stikynot_color_bar', function(){
	//  $(this).css('opacity', '1');
	// });
	// $(document).on('mouseout', '.box_stikynot_color_bar', function(){
	//  $('.box_stikynot_color_bar').css('opacity', '0.5');
	// });
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

	/** $Email **/
	var Config = {
		MailConfig : [
				{
					action: "https://ssl.mail.163.com/entry/coremail/fcg/ntesdoor2?df=mail163_letter&from=web&funcid=loginone&iframe=1&language=-1&net=t&passtype=1&product=mail163&race=382_92_196_gz&style=-1",
					name: "@163.com",
					params: {
					  //  url: "http://entry.mail.163.com/coremail/fcg/ntesdoor2?lightweight=1&verifycookie=1&language=-1&style=15",
						username: "#{u}",
						password: "#{p}",
						savelogin:  "0",
						url2:   "http://mail.163.com/errorpage/err_163.htm"
					}
				},
				{
					action: "https://ssl.mail.126.com/entry/cgi/ntesdoor?hid=10010102&funcid=loginone&df=mail126_letter&language=-1&passtype=1&verifycookie=-1&iframe=1&from=web&net=failed&product=mail126&style=-1&race=-2_-2_-2_db",
					name: "@126.com",
					params: {
						domain: "126.com",
						username: "#{u}@126.com",
						password: "#{p}",
						savelogin:  "0",
						url2:   "http://mail.126.com/errorpage/err_126.htm"
						//url: "http://entry.mail.126.com/cgi/ntesdoor?lightweight%3D1%26verifycookie%3D1%26language%3D0%26style%3D-1"
					}
				},
				{
					action : "https://login.sina.com.cn/sso/login.php",
					name: "@sina.com",
					params : {
						username : "#{u}@sina.com",
						password : "#{p}",
						entry : "freemail",
						gateway : "0",
						encoding : "UTF-8",
						url : "http://mail.sina.com.cn/",
						returntype : "META"
					}
				},
				{
					action: "https://edit.bjs.yahoo.com/config/login",
					name: "@yahoo.com.cn",
					params: {
						login: "#{u}@yahoo.com.cn",
						passwd: "#{p}",
						domainss: "yahoo",
						".intl": "cn",
						".src": "ym"
					}
				},
				{
					action : "https://login.sina.com.cn/sso/login.php",
					name: "@sina.cn",
					params : {
						username : "#{u}@sina.cn",
						password : "#{p}",
						entry : "freemail",
						gateway : "0",
						encoding : "UTF-8",
						url : "http://mail.sina.com.cn/",
						returntype : "META"
					}
				},
				{
					action: "http://passport.sohu.com/login.jsp",
					name: "@sohu.com",
					params: {
						loginid: "#{u}@sohu.com",
						passwd: "#{p}",
						fl: "1",
						vr: "1|1",
						appid: "1113",
						ru: "http://login.mail.sohu.com/servlet/LoginServlet",
						ct: "1173080990",
						sg: "5082635c77272088ae7241ccdf7cf062"
					}
				},
				{
					action: "https://mail.yeah.net/entry/cgi/ntesdoor?df=webmailyeah&from=web&funcid=loginone&iframe=1&language=-1&passtype=1&verifycookie=1&product=mailyeah&style=-1&",
					name: "@yeah.net",
					params: {
						domain: "yeah.net",
						username: "#{u}@yeah.net",
						user: "#{u}",
						password: "#{p}",
						savelogin:  "0",
						url2:   "http://mail.yeah.net/errorpage/err_yeah.htm"
						//url: "http://entry.mail.yeah.net/cgi/ntesdoor?lightweight%3D1%26verifycookie%3D1%26style%3D-1"
					}
				},
				{
					action: "https://mail.10086.cn/Login/Login.ashx?_fv=5&cguid=1144153566504&_=3900d6b56d0742590535b3bb5ddee9f923b4326c ",
					name: "@139.com",
					params: {
						UserName: "#{u}",
						Password: "#{p}"
						//clientid: "5015"
					}
				},
				{
					action: "http://passport.21cn.com/maillogin.jsp",
					name: "@21cn.com",
					params: {
						UserName: "#{u}@21cn.com",
						passwd: "#{p}",
						domainname: "21cn.com"
					}
				},
				{
					action : "http://mail.qq.com",
					type : "link"
				}, 
				{
					action : "http://gmail.google.com",
					type : "link"
				}, 
				{
					action : "http://www.hotmail.com",
					type : "link"
				},
				{
					action : "https://passport.alipay.com/login/login.htm?fromSite=9&return_url=http%3A%2F%2Fmail.aliyun.com%2Funiquelogin.htm",
					type : "link"
				},
				{
					action : "https://login.yahoo.com/config/login_verify2?&.src=ym&.intl=us",
					type : "link"
				}
				]
	}
	var MailLogin = {
		mailCache : [],

		sendMail : function() {
			var mailUserName = $.trim($('#mailUserName').val());
			var mailPassWord = $.trim($('#mailPassWord').val());

			if (mailUserName == "") {
				alert("\u7528\u6237\u540d\u4e0d\u80fd\u4e3a\u7a7a\uff01");
				return false;
			}
			if (mailPassWord == "") {
				alert("\u5bc6\u7801\u4e0d\u80fd\u4e3a\u7a7a\uff01");
				return false;
			}

			var mailFormObj = $('#J_mailForm');
			var mailType = $('#mail_list').attr('selectindex');

			var mailConfig = Config.MailConfig[mailType];

			if (mailConfig.val == 0) {
				alert("\u60a8\u6ca1\u6709\u9009\u62e9\u90ae\u7bb1\uff01");
				return;
			}

			mailFormObj.attr('action', mailConfig.action);
			var str = '';
			for (param in mailConfig.params) {
				
				str = '<input type="hidden" class="J_mail_form_hidden" name="' + param + '" value="'
					+ mailConfig.params[param].replace('#{u}', mailUserName).replace('#{p}', mailPassWord) + '" />';
				
				mailFormObj.append(str);
			}
			
			mailFormObj.submit();
			$('.J_mail_form_hidden').remove();
			$('#mailPassWord').value = '';
		},

		change : function(mailType) {
			var mailConfig = Config.MailConfig[mailType];
			if (mailConfig.type == "link") {
				
				$('#mailSelect').text(Config.MailConfig[0].name);
				$('#mail_list').attr('selectindex', 0);
				
				var mailFormObj = $('#J_mailForm');
				
				mailFormObj.attr('action', mailConfig.action);
				var str = '';
				for (param in mailConfig.params) {
					
					str = '<input type="hidden" class="J_mail_form_hidden" name="' + param + '" value="'
						+ mailConfig.params[param].replace('#{u}', mailUserName).replace('#{p}', mailPassWord) + '" />';
					
					mailFormObj.append(str);
				}
				mailFormObj.append(str);
				
				mailFormObj.submit();
				$('.J_mail_form_hidden').remove();
				$('#mailPassWord').value = '';
			} else {
				$('#mailSelect').text(Config.MailConfig[mailType].name);
				$('#mail_list').attr('selectindex', mailType);
			}
		}
	}

	/** $翻译 **/

	var translateLang = 0;

	$('#J_translate').click(function(){
		$.fancybox({
			href: '#J_box_translate',
			//closeBtn : false,
			helpers:  {
				title:  null,
				overlay : null
			},
			width: 692,
			height: 525,
			autoSize: false
			
		});
		$('.J_translate_source').select();
		return false;
	});

	$('.J_translate_clear').click(function(){
		$('#gt-res-dict').html('');
		$('#result_box').html('');
		$('.J_translate_source').val('');
		$('.J_translate_source').select();
		return false;
	});

	$('#gt-sl-gms').click(function(){
		translateLang = 0;
		if ($(this).hasClass('goog-flat-menu-button-focused')) {
			$(this).removeClass('goog-flat-menu-button-focused');
			$('#gt-sl-gms-menu').hide();
		} else {
			$('#gt-tl-gms').removeClass('goog-flat-menu-button-focused');
			$(this).addClass('goog-flat-menu-button-focused');
			$('.J_lang_auto').show();
			$('#gt-sl-gms-menu').show();
		}
		return false;
	});
	$('#gt-tl-gms').click(function(){
		translateLang = 1;
		if ($(this).hasClass('goog-flat-menu-button-focused')) {
			$(this).removeClass('goog-flat-menu-button-focused');
			$('#gt-sl-gms-menu').hide();
		} else {
			$('#gt-sl-gms').removeClass('goog-flat-menu-button-focused');
			$(this).addClass('goog-flat-menu-button-focused');
			$('.J_lang_auto').hide();
			$('#gt-sl-gms-menu').show();
		}
		return false;
	});

	$('.goog-menuitem').mouseover(function(){
		$('.goog-menuitem').removeClass('goog-menuitem-highlight');
		$(this).addClass('goog-menuitem-highlight');
	});

	$('.goog-menuitem').click(function(){
		var langText = $.trim($(this).text());
		var lang = $(this).attr('data-id');
		if (translateLang == 1) {
			$('#J_lang_tgt').text(langText);
			$('#gt-tl-gms').removeClass('goog-flat-menu-button-focused');
			$('#gt-tl').val(lang);
		} else {
			$('#J_lang_src').text(langText);
			$('#gt-sl-gms').removeClass('goog-flat-menu-button-focused');
			$('#gt-sl').val(lang);
		}
		$('#gt-sl-gms-menu').hide();
		
		$("#gt-submit").trigger("click");
		return false;
	});

	$('#gt-swap').click(function(){
		var slLangText = $.trim($('#J_lang_src').text());
		var tlLangText = $.trim($('#J_lang_tgt').text());
		var slLang = $('#gt-sl').val();
		var tlLang = $('#gt-tl').val();
		
		$('#J_lang_tgt').text(slLangText);
		$('#gt-tl').val(slLang);
		
		$('#J_lang_src').text(tlLangText);
		$('#gt-sl').val(tlLang);
		
		$("#gt-submit").trigger("click");
		
		return false;
	});

	$('#gt-submit').click(function() {

		var sl = $('#gt-sl').val();
		var tl = $('#gt-tl').val();
		var q = $('.J_translate_source').val();
		
		$('#gt-res-dict').html('<tr><td colspan="4"><div class="gt-baf-cell gt-baf-pos">翻译中...</div></td></tr>');
		$('#result_box').html('');
		
		$.ajax({
			type : 'POST',
			url : APP + 'Index/google_translate',
			data : {
				'sl' : sl,
				'tl' : tl,
				'q'  : q
			},
			cache : false,
			dataType : 'json',
			success : function(data) {
				
				var dictArr = eval(data.data);
				
				if (dictArr) {
					var dictStr = '';
					dictStr += '<table class="gt-baf-table"><tbody>';
					
					var dictSubArr = '';
					var dictType = 0;   //0为词语，1为句子
					if (typeof dictArr[1] != "undefined") {
						dictSubArr = dictArr[1];
					} else if (typeof dictArr[0] != "undefined") {
						dictSubArr = dictArr[0];
						dictType = 1;
					}
					
					if (dictSubArr) {
						
						if (dictType) {
							dictStr += '<span lang="zh-CN" id="result_box">';
							for (var i = 0; i < dictSubArr.length; i++) {
								
								var bafArr = dictSubArr[i];
								
								//dictStr += '<tr><td colspan="4"><div class="gt-baf-cell gt-baf-pos">' + bafArr[0] + '</div></td></tr>';
								dictStr += '<span>' + bafArr[0].replace('\n','</br>')+ '</span>';
							}
							dictStr += '</span>';
						} else {
						
							for (var i = 0; i < dictSubArr.length; i++) {
								
								var bafArr = dictSubArr[i];
								
								dictStr += '<tr><td colspan="4"><div class="gt-baf-cell gt-baf-pos">' + bafArr[0] + '</div></td></tr>';
							
								if (bafArr[2] instanceof Array) {
									for (var j = 0; j < bafArr[2].length; j++) {
										dictStr += '<tr>';
										var wordArr = bafArr[2][j];
										
										dictStr += '<td>';
										var cts_width = 24;
										if (wordArr[3] < 0.01) {
											cts_width = 8;
										} else if (wordArr[3] < 0.1) {
											cts_width = 16;
										}
										dictStr += '<div class="gt-baf-cell gt-baf-marker-container"><div class="gt-baf-cts" style="width:' + cts_width + 'px;"></div></div>'; 
										
										dictStr += '</td>';
										
										dictStr += '<td><div class="gt-baf-cell gt-baf-bar"></div></td>';
										
										dictStr += '<td>';
										dictStr += '<div class="gt-baf-cell gt-baf-word-clickable" style="text-align: left; direction: ltr;">' + wordArr[0] + '</div>';
										dictStr += '</td>';
										
										dictStr += '<td style="width: 100%;">';
										dictStr += '<div class="gt-baf-cell gt-baf-translations" style="direction: ltr;">';
										
										for (var k = 0; k < wordArr[1].length; k++) {
											dictStr += '<span class="gt-baf-back">' + wordArr[1][k];
											if (k != wordArr[1].length - 1) {
												dictStr += ', '
											}
											dictStr += '</span>'
										}
										
										dictStr += '</div>';
										dictStr += '</td>';
										dictStr += '</tr>';
									}
								}
							}
						}
						dictStr += '</tbody></table>';
						
						$('#gt-res-dict').html(dictStr);
					} else {
						$('#gt-res-dict').html('');
					}
					
					if (!dictType) {
						$('#result_box').html(dictArr[0][0][0]);
					}
					
				} else {
					$('#result_box').html('<span style="font-size:14px; color: red;">亲，未找到你所查询的结果，再试下吧!</span>');
				}
			},
			error : function() {
			}
		});

	});

	$('#J_box_translate').mouseout(function(){
		$('.J_translate_source').select();
		return false;
	});


	/** 翻译$ * */

	/** $日历 * */
	$('#J_calendar').click(function(){
		
		$('#J_calendar_iframe').attr('src', 'http://baidu365.duapp.com/wnl.html');
		
		$.fancybox({
			href: '#J_box_calendar',
			//closeBtn : false,
			helpers:  {
				title:  null,
				overlay : null
			},
			width: 550,
			height: 525,
			autoSize: false
			
		});
		
		return false;
	});

	$('#J_box_calendar_list a').click(function() {
		$('#J_calendar_iframe').attr('src', $(this).attr('data-url'));
		return false;
	});

	/** 日历$ **/

	/** $网页邮箱 **/

	$('#J_mail').click(function(){
		$.fancybox({
			href: '#J_box_mail',
			helpers:  {
				title:  null,
				overlay : null
			},
			width: 390,
			height: 300,
			autoSize: false
		});
		
		//$('#J_box_mail').css('display', $('#J_box_mail').css('display') == 'none' ? 'block' : 'none');
		$("#mailUserName").select();
		return false;
	});

	$('#J_box_mail').mouseover(function(){
		$('#mailUserName').select();
		return false;
	});

	$('#mailPassWord').mouseover(function(){
		$('#mailPassWord').select();
		return false;
	});

	$('#J_mail_submit').click(function(){
		MailLogin.sendMail();
		return false;
	});

	$("#mailPassWord").keypress(function(event){
		  if(event.keyCode==13) {
			  MailLogin.sendMail();
			  return false;
		  }
	});


	$('.mail-list li').click(function() {
		
		MailLogin.change($(this).attr('dn'));
		$("#mailUserName").select();
	 });
	 
	 $('.mail-list li').mouseover(function() {
		$(this).addClass('option-hover');       
	 });
	 $('.mail-list li').mouseout(function() {
		$(this).removeClass('option-hover');      
	 });
	 /** 网页邮箱$ **/
});