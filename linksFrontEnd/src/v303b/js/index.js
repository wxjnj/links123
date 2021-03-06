var APP = $CONFIG['APP'];
var URL = $CONFIG['URL'];
var PUBLIC = $CONFIG['PUBLIC'];
$(function() {

	Zld.Init();
	ZhiDaLan.Init();
	//MusicPlayer.Init();
	HelpMouse.init();
	Calendar.Init()

	$(".tip").tipTip({maxWidth: "auto", edgeOffset: 3, defaultPosition: "top"});
	/*
	var musicReady = function(list){
		new jPlayerPlaylist({
			jPlayer: "#jquery_jplayer_1",
			cssSelectorAncestor: "#jp_container_1"
		}, list, {
			swfPath: $CONFIG.STATIC+"/v3/jplayer",
			supplied: "mp3",
			wmode: "window",
			smoothPlayBar: true,
			keyEnabled: false
		});
	};

	if(mlist){ musicReady(mlist); }
	*/
	// 弹出页
	$(".newWin").on('click', function() {
		window.open($(this).attr('url'));
	});

	$('#J_Apps, #J_Apps_more_list').sortable({
		tolerance: 'pointer',
		connectWith: ".connectedSortable",
		sort: function(e, ui){
			if(ui.item.parent('#J_Apps').size()){
				window.appPkg();
			}else{
				window.appPkg(-1);
			}
		}
	});

	$('#J_Apps').attr('data-sort', false);
	$('#J_Apps').sortable({
		tolerance: 'pointer',
		//helper: 'clone',
		start: function(event, ui){
            //开始拖动的时候，关闭tips
            if($('#app-tip').size()){
                $('#app-tip').find('.zld-tip-close').trigger('click');
            }
		},
		stop: function(){
			$('#J_Apps').attr('data-sort', true);
		},
		update: function(event, ui) {
			//var b = $('#J_Apps_more_list').sortable('toArray');
			//var a = $('#J_Apps').sortable('toArray');

			var a = [], b = [];
			$('#J_Apps_more_list').find('li').each(function(){
				b.push($(this).attr('data-id'));
			});
			$('#J_Apps').find('li').each(function(){
				a.push($(this).attr('data-id'));
			});

			$('#J_Apps').attr('data-sort', false);
			a = a.concat(b);
			$.post(
				URL + '/sortApp', {
					'appIds': a
				},
				function(data) {
					if (data == 1) {
						//成功
					} else if (data == 0) {
						//失败
					} else {
						//失败
					}
				});
		}
	});
	$('#J_Apps').sortable('enable');
	$('#J_Apps_more_list').sortable({
		tolerance: 'pointer',
		//helper: 'clone',
		stop: function(){
			$('#J_Apps').attr('data-sort', true);
		}
	});

	// 搜索框
	/**/
	$("#search_text").autocomplete("/Index/searchSupplement", {
		dataType : "json",
    	minChars: 1,
    	resultsClass: "ac_results_search",
		selectFirst: false,	//默认不选择第一个
		async: true,
		scroll : false,
		parse : function(data) {
			//data -> ['', ''];
			//var ks = $.cookies.get('keywords');
			var ks = Keywords.get();
			var cur = $.trim($('#search_text').val()).replace('http://', '');
			var has = [];
			var unique = {};
			if(!ks) ks = [];
			$.each(ks, function(k, v){
				v = decodeURIComponent(v);
				if(v.indexOf(cur) >= 0){
					has.push(v);
					unique[v] = true;
				}
			});

			this.hasLength = has.length;

			$.each(data, function(k, v){
				if(!unique[v]) has.push(v);
			});

			//data = has.concat(data);
			return $.map(has, function(row) {
				return {
					data : row,
					value : row,
					result : row
				};
			});
		},
		formatItem : function(item) {
			return item;
		}
	}).result(function(e, item) {
		$('#search_text').val(item);
		//setTimeout(function(){
			$("#search_text").select();
			var keyword = $.trim($("#search_text").val());
			//$.cookies.set('keyword', keyword);
			//保存keyword
			keyword = keyword.replace('http://', '');
			$.cookies.set('keyword', keyword);
			keyword = encodeURIComponent(keyword);
			var url = $(".J_thlz a.on").attr("url").replace('keyword', keyword);
			var tid = $(".J_thlz a.on").attr("tid");
			THL.go(url, tid, keyword);
		//}, 0);
	});
/**/

	$("#direct_text").autocomplete("/Home/Link/tag", {
		dataType : "json",
		minChars : 1,
		selectFirst: false,	//默认不选择第一个
		async: true,
		width : 262,
		scroll : false,
		matchContains : true,
		parse : function(data) {
			var ks = Keywords.get('tags');
			var cur = $.trim($('#direct_text').val());
			var has = [];
			var unique = {};
			if(!ks) ks = [];
			$.each(ks, function(k, v) {
				v = decodeURIComponent(v);
				if(v.indexOf(cur) >= 0){
					has.push({
						tag: v
					});
					unique[v] = true;
				}
			});
			this.hasLength = has.length;
			$.each(data, function(k, v) {
				if(!unique[v.tag]) has.push(v);
			});
			//data = has.concat(data);

			return $.map(has, function(row) {
				return {
					data : row,
					value : row.tag,
					result : row.tag
				};
			});
		},
		formatItem : function(item) {
			return item.tag;
		}
	}).result(function(e, item) {
		$('#direct_text').val(item.tag);
		$('#frm_drct').submit();
	});

	// 切换宽屏
	$('.screen-change-btn').on('click', 'a', function() {
		if ($(this).attr('data-size') == 'wide') {
			createCookie('screenStyle', 'wide', 30);
			$('body').attr('class', 'widescreen');
		} else {
			createCookie('screenStyle', 'nml', 30);
			$('body').attr('class', '');
		}
		$('body').trigger('screenchange'); //触发body上自定义的方法screenchange

		Zld.Resize();
		Calendar.ReInit();
	});

	(function() { //app图标相关
		var nmlLen = 9, wideLen = 10;
		var appsList = $('#J_Apps>li');

		var appPkg = window.appPkg = function(type){

			type = type || 0;

			var appsListLen = $('#J_Apps').find('li').size() + $('#J_Apps_more_list').find('li').size();

			var isWide = $('body').is('.widescreen');
			var needLen = nmlLen;
			if(isWide){
				needLen = wideLen;
			}
			if(appsListLen<= needLen){ 
				$('.app-icon-list .app-more').hide();
				return;
			}
			$('.app-icon-list .app-more').show();

			var panel = $('.app-icon-list').find('ul');

			$('#J_Apps').find('li:gt(' + (needLen + type) + ')').appendTo(panel);
			/*
			appsList.each(function(index, el){
				if(index>needLen - 1){
					panel.append($(el));
				}
			});*/
		}
		appPkg(-1);
		$('body').on('screenchange', function() {
			$('#J_Apps_more_list').find('li').appendTo('#J_Apps');
			appPkg(-1);
		});
		$('.app-more').on('mouseenter', function() {
			$('.app-more-box').show();
		}).on('mouseleave', function() {
			$('.app-more-box').hide();
		});
	})();
});

var ZhiDaLan = { // 直达框
	Init: function() {
		$(document).on('click', function() {
			$('#direct_text').val($('#direct_text').attr('txt')).addClass('ipton');
		});
		$('#direct_text').on('keydown', function(e){
			if(e.keyCode != 13 && $('#direct_text').val() == $('#direct_text').attr('txt')){
				$('#direct_text').val('');
			}
		});
		/*
		$("#header").on('mouseenter', function(){
			var tag = $.trim($('#direct_text').val());
			if(tag == $('#direct_text').attr('txt')){
				$("#direct_text").select().removeClass('ipton');
			}else{
				$("#direct_text").removeClass('ipton');
			}
		}).on('mouseleave', function(){
			var tag = $.trim($('#direct_text').val());
			if(tag == '' || tag == $('#direct_text').attr('txt')){
				$('#search_text').select();
				$('#direct_text').addClass('ipton');
			}
		});*/

		$("#direct_text").on('mouseout', function() {
			var tag = $.trim($('#direct_text').val());
			if (tag == '' || tag == $('#direct_text').attr('txt')) {
				$('#search_text').select();
				$('#direct_text').addClass('ipton');
			}
		});

		$("#direct_text").on('click', function() {
			var tag = $.trim($('#direct_text').val());
			if (tag == $('#direct_text').attr('txt')) {
				$('#direct_text').val('').removeClass('ipton');
			}
			return false;
		}).on('blur', function() {
			$('#direct_text').addClass('ipton');
		});

		$('.J_direct_submit').on('click', function() {
			$("#frm_drct").trigger('submit');
			$("#direct_text")[0].focus();
			return false;
		});

		$("#frm_drct").on('submit', function() {
			var tag = $.trim($('#direct_text').val());
			if (tag == '' || tag == $('#direct_text').attr('txt')) {
				return false;
			}
			Keywords.set(tag, 'tags');
			$('#direct_text').select();
		});
	}
};


var Zld = { // 自留地
	IsSortable: false,
	//是否为拖拽点击，true则不打开自留地网址
	_resizeLine: function(start){
		//console.log('=======================');
		//console.log(start);
		var self = this;

		var box = $('#J_sortable'),
			boxWidth = box.width(),
			lis, 
			randomDiff, 
			w, 
			xw, 
			diff, 
			s,
			oi,
			opl, 
			opr,
			m_right = box.find('li:first').css('margin-right'),
			liWidth = 0,
			overIndex = null,
			fstLineWidth = null;

		m_right = parseInt(m_right);

		if(start == 0){
			lis = box.find('li');
			start = -1;
		}else{
			lis = box.find('li:gt('+(start)+')');
		}
		//console.log(lis[0]);

		$.each(lis, function(k, v) {
			liWidth += ($(v).width() + m_right);
			if (!overIndex && liWidth > boxWidth) {
				overIndex = k;
				fstLineWidth = liWidth - $(v).width();
			}
		});


		if (liWidth <= boxWidth) {
			if(lis.length == 1){
				self.lastLineOnlyAdd = true;
			}else{
				self.lastLineOnlyAdd = false;
			}
			return false;
		}

		oi = overIndex + start;

		randomDiff = start == -1 ? 0 : Math.random() * 15 + 5;

		if (boxWidth - fstLineWidth > 55) {
			w = lis.eq(overIndex).width() + m_right - (boxWidth - fstLineWidth);
			xw = Math.floor(w / (overIndex + 1) / 2);
			diff = w - xw * (overIndex + 1) * 2 + randomDiff;
			s = lis.filter(':lt(' + (overIndex + 1) + ')');
			$.each(s, function(k, v) {
				opl = $(v).find('.nm').css('padding-left');
				opr = $(v).find('.nm').css('padding-right');
				opl = parseInt(opl);
				if(diff > 0) {
					opl -= 1;
					diff--;
				}
				opr = parseInt(opr);
				if(diff > 0) {
					opr -= 1;
					diff--;
				}
				$(v).find('.nm').css({
					'padding-left': opl - xw + 'px',
					'padding-right': opr - xw + 'px'
				});
			});
			oi += 1;
		} else if(boxWidth - fstLineWidth <= 55 && boxWidth - fstLineWidth > 0) { 
			// 差距过小，使用本行增加宽度适应行宽
			w = boxWidth - fstLineWidth;
			xw = Math.floor(w / (overIndex) / 2);
			diff = w - xw * 2 * overIndex - randomDiff;
			s = lis.filter(':lt(' + overIndex + ')');
			$.each(s, function(k, v){
				opl = $(v).find('.nm').css('padding-left');
				opr = $(v).find('.nm').css('padding-right');
				opl = parseInt(opl);
				if(diff > 0){
					opl += 1;
					diff--;
				}
				opr = parseInt(opr);
				if(diff > 0){
					opr += 1;
					diff--;
				}
				$(v).find('.nm').css({
					'padding-left': opl + xw + 'px',
					'padding-right': opr + xw + 'px'
				});
			});
		}
		return oi;	
	},
	Resize: function(op) {
		//自适应算法
		var self = this;
		var box = $('#J_sortable');

		//先恢复默认值
		box.find('.nm').removeAttr('style').css({
			//'padding-left': self.holderPaddingLeft,
			//'padding-right': self.holderPaddingRight
		});
		var oi;
		var s = 0;

		var line_count = 0;
		do{
			oi = self._resizeLine(s);
			s = oi;
			line_count++;
		}while(oi !== false);

		//超过两行 行高减小8
		//if(box.height() > 90) {
		if(line_count > 2 && !self.lastLineOnlyAdd){
			box.addClass('manylines');
			/*
			box.find('.nm').css({
				'padding-top': '6px',
				'padding-bottom': '6px'
			}).end().find('.ctl').css({
				'padding': '1px',
				'margin-top': 0 //部分主题有这个值
			}).end().find('li').css({
				'height': '28px',
				'line-height': '28px'
			});
			*/
			//ctl的宽度减小后，需要再做一次行宽自适应
			!op && self.Resize(true);
		}
	},
	Init: function() {
		var self = this;
		var obj = $('#J_ZldList');

		//先记录默认值（用于调整自适应）
		self.holderPaddingLeft = obj.find('.nm').css('padding-left');
		self.holderPaddingRight = obj.find('.nm').css('padding-right');
		self.Resize();

		self.zld_tip = $('#zld-tip');
		self.zld_tip.on('click', '.zld-tip-close', function(){
			self.zld_tip.hide();
			$.cookies.set('zld_tip_close', 1,  { expiresAt: (new Date).add_day(365) });
		});

		$('#J_ZldList').on('mouseenter', function(){
			if(!$.cookies.get('zld_tip_close')){
				self.zld_tip.show();
			}
		}).on('mouseleave', function(){
			self.zld_tip.hide();
		});

		$(document).on('click', '#J_ZldList .add', function() {
			//if(User.CheckLogin()){
			self.Create();
			/*
			if(!$.cookies.get('zld_tip_close')){
				self.zld_tip.show();
			}*/
			//}
		});
		$(document).on('click', '#J_ZldList .ctl', function() {
			//if(User.CheckLogin()){
			if ($(this).hasClass('add')) {
				return false;
			}
			var o = $(this).closest('li');
			var id = o.data('id');
			var nm = o.find('b').html();
			var url = o.data('url');
			self.Create(id, nm, url);
			/*
			if(!$.cookies.get('zld_tip_close')){
				self.zld_tip.show();
			}
			*/
			return false;
			//			}
		});
		$(document).on('click', '#J_ZldList .nm', function() {
			if (!Zld.IsSortable) {
				var o = $(this).closest('li');
				var url = o.data('url');
				self.Go(url);
			} else {
				Zld.IsSortable = false;
			}
			return false;
		});

		$('#J_sortable').sortable({
			tolerance: 'pointer',
			items: '> li:not(.add)',
			start: function(event, ui) {
				$(ui.item).find('span').css('cursor', 'move');
				//修正部分浏览器拖动时错位的问题
				var w = $(ui.item).width();
				$(ui.item).css('width', w + 2 + 'px');
                //自留地开始拖动的时候，关闭tips
                if($('#zld-tip').size()){
                    $('#zld-tip').find('.zld-tip-close').trigger('click');
                }
			},
			update: function(event, ui) {
				$(ui.item).find('span').css('cursor', 'pointer');
				Zld.IsSortable = true;

				$.post(
					URL + '/sortArealist', {
						'area': $(this).sortable('toArray')
					},
					function(data) {
						if (data == 1) {
							//成功
						} else if (data == 0) {
							//失败
						} else {
							//失败
						}
					});
			},
			stop: function(event, ui) {
				if(!$.cookies.get('zld_tip_close')){
					$.cookies.set('zld_tip_close', 1,  { expiresAt: (new Date).add_day(365) });
				}
				self.Resize();
				Zld.IsSortable = false;
				$(ui.item).find('span').css('cursor', 'pointer');
			}
		});
		$('#J_sortable').sortable('enable');



		$(document).on('click', '#J_Zld .lkd-add, #J_Zld .lkd-edit', function() {

			var o = $('#J_Zld');

			var objname = o.find('input[name="name"]');
			var objurl = o.find('input[name="url"]');
			var id = o.find('input[name="id"]').val();
			var name = objname.val();
			var url = objurl.val();

			if (!name) {
				alert("请输入网站名称");
				objname[0].focus();
				return false;
			}
			if (!url) {
				alert("请输入网址");
				objurl[0].focus();
				return false;
			}

			var reg = /^((https|http|ftp|rtsp|mms)?:\/\/)?(([0-9A-Za-z_!~*'().&=+$%-]+: )?[0-9A-Za-z_!~*'().&=+$%-]+@)?(([0-9]{1,3}.){3}[0-9]{1,3}|([0-9A-Za-z_!~*'()-]+\.)*([0-9A-Za-z][0-9A-Za-z-]{0,61})?[0-9A-Za-z]\.[a-zA-Z]{2,6})(:[0-9]{1,4})?((\/?)|(\/[0-9A-Za-z_!~*'().;?:@&=+$,%#-]+)+\/?)$/;
			if (reg.test(url)) {

			} else {
				alert("输入的网址有误");
				objurl[0].focus();
				return false;
			}

			$.post(
				URL + '/updateArea', {
					'web_id': id,
					'web_url': url,
					'web_name': name
				},
				function(data) {
					var licur = function() {
						var li = null;
						obj.find('ul>li').each(function() {
							if ($(this).data('id') == id) {
								li = $(this);
								return;
							}
						});
						return li;
					}
					if (data == 1) { //更新成功
						var li = licur();
						li.attr('url', '/Link/index.html?mod=myarea&amp;url=' + url);
						li.data('url', url);
						li.find('b').html(name);
					} else if (data > 1) { //新加成功
						var li = obj.find('.add').closest('li');
						li.before(self.CreateItem(data, name, url));
					} else if (data == - 1) {
						User.Login('请先登录');
					} else {
						alert('操作失败');
					}
					o.dialog('close');
				});
			return false;
		});

		$(document).on('click', '#J_Zld .lkd-del', function() {

			var o = $('#J_Zld');
			var id = o.find('input[name="id"]').val();

			$.post(
				URL + '/delArea', {
					'web_id': id
				},
				function(data) {
					var licur = function() {
						var li = null;
						obj.find('ul>li').each(function() {
							if ($(this).data('id') == id) {
								li = $(this);
								return;
							}
						});
						return li;
					}
					if (data == 1) {
						var li = licur();
						li.remove();
					} else if (data == - 1) {
						User.Login('请先登录');
					} else {
						alert('操作失败');
					}
					o.dialog('close');
				});
			return false;
		});
	},
	Go: function(url) {
		var obj = $('#J_MyAreaForm');
		obj.find('input[name="url"]').val(url);
		obj.submit();
	},
	Create: function(id, nm, url) {
		if (!$('#J_Zld').size()) {
			var hl = '';
			hl = hl + '<div class="lk-dialog lk-dialog-zld" id="J_Zld">';
			hl = hl + '	<div class="lkd-hd">';
			hl = hl + '		<a class="close" href="javascript:;">X</a>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-bd">';
			if ($CONFIG.IsLogin == 1) {
				hl = hl + '<p class="zld-create-tip">非登录状态，编辑结果不能长期保存。</p>';
			}
			hl = hl + '		<form action="">';
			hl = hl + '			<ul>';
			hl = hl + '				<li><input class="ipt" type="text" name="name" placeholder="网站名称" /></li>';
			hl = hl + '				<li><input class="ipt" type="text" name="url" placeholder="网址" /></li>';
			hl = hl + '			</ul>';
			hl = hl + '		</form>';
			hl = hl + '	</div>';
			hl = hl + '	<div class="lkd-ft">';
			hl = hl + '		<input type="hidden" name="id" value="" />';
			hl = hl + '		<span class="editp" style="display:none;"><a class="lkd-edit" href="javascript:;">确认编辑</a>';
			hl = hl + '		<a class="lkd-del" href="javascript:;">删除网址</a></span>';
			hl = hl + '		<span class="addp"><a class="lkd-add" href="javascript:;">确认添加</a></span>';
			hl = hl + '	</div>';
			hl = hl + '</div>';
			$('body').append(hl);

			var obj = $('#J_Zld');

			obj.dialog({
				autoOpen: false,
                show: { effect: "fade" },
                hide: { effect: "fade" },
				width: 384,
				modal: true,
				resizable: false,
				open: function() {
					setTimeout(function() {
							obj.find('input[name="name"]').select();
						},
						20);
				}
			});

			obj.find('.close').on('click', function() {
				obj.dialog('close');
				return false;
			});

			obj.find('input[type="text"]').on('focus', function() {
				$(this).css('background', '#fff');
			}).on('blur', function() {
					$(this).css('background', '#eeefef');
				});

			obj.find('input[name="name"],input[name="url"]').on('mouseover', function() {
				$(this).focus().select();
			});

			obj.find('input[name="name"],input[name="url"]').on('keydown', function(event) {
				if (event.keyCode == 13) {
					if (obj.find('.editp').is(":visible")) {
						obj.find('.lkd-edit').trigger('click');
					} else {
						obj.find('.lkd-add').trigger('click');
					}
				}
			});
		}

		var obj = $('#J_Zld');
		if (id) {
			obj.find('input[name="id"]').val(id);
			obj.find('input[name="name"]').val(nm);
			obj.find('input[name="url"]').val(url);
			obj.find('.editp').show();
			obj.find('.addp').hide();
		} else {
			obj.find('input[name="id"]').val('');
			obj.find('input[name="name"]').val('');
			obj.find('input[name="url"]').val('');
			obj.find('.editp').hide();
			obj.find('.addp').show();
		}

		obj.dialog('open');
	},
	CreateItem: function(id, nm, url) {
		var hl = '<li id="' + id + '" url="/Link/index.html?mod=myarea&amp;url=' + url + '" data-id="' + id + '" data-url="' + url + '">';
		hl = hl + '<i class="mask"></i><span class="nm"><b>' + nm + '</b></span>';
		hl = hl + '<span class="ctl"><i></i></span>';
		hl = hl + '</li>';
		return hl;
	}
};

var HelpMouse = {
	init: function() {
		var self = this;
		var isSearchTxtSelected = false;
		var mouseOnTopNavBar = 0;

		//当页面翻过首屏时，通过坐标判断直达栏是否获取焦点的方法就不再适用，
		//这里增加鼠标移至直达栏直接获取焦点
		$(document).on('mousemove', '#direct_text', function() {
			if ($('#direct_text').val() == $('#direct_text').attr('txt')) {
				$('#direct_text').select().removeClass('ipton');
				isSearchTxtSelected = false;
				if ($.trim($('#search_text').val()) == "") {
					$('#J_thl_div').hide();
				}
			}
			//在直达栏上移动鼠标，不冒泡，避免与ev坐标判断焦点方法冲突
			return false;
		});

		//通过顶部nav给鼠标位置增加来源属性，强化ev位置获取焦点的判断能力
		$(document).on('mouseenter', '#J_header', function() {
			if (mouseOnTopNavBar == 0) mouseOnTopNavBar = 1;
		}).on('mouseleave', '#J_header', function() {
			mouseOnTopNavBar = 0;
		}).on('mousemove', '#direct_text, #J_direct_submit', function() {
			mouseOnTopNavBar = 2;
		});

		$(document).on('mousemove', function(ev) {

			var isNeedHelp = 1;
			$('.links123-app-frame').each(function() {
				if ($(this).is(":visible")) {
					isNeedHelp ? isNeedHelp = 0: '';
				}
			});
			if (!isNeedHelp) {
				return false;
			}
			var mousePos = self.getcoords(ev);

			var $search_text = $('#search_text');
			var $direct_text = $('#direct_text');
			var search_text_left_end_pos = $search_text.offset().left - 10;
			var search_text_right_end_pos = search_text_left_end_pos + $search_text.width();
			var direct_text_right_end_pos = $direct_text.offset().left + $direct_text.width() + 10;

			var app_top_pos = $('.box-apps').offset().top;
			var zld_top_pos = $('#J_sortable').offset().top + 5;

			//向下滚800px后不再判断焦点
			if ($(window).scrollTop() > 800) return;

			if (mouseOnTopNavBar == 1) {
				if ($('#direct_text').val() == $('#direct_text').attr('txt')) {
					$('#direct_text').select().removeClass('ipton');
					isSearchTxtSelected = false;
					if ($.trim($('#search_text').val()) == "") {
						$('#J_thl_div').hide();
					}
				}
				return;
			}
			if ((mousePos.y < zld_top_pos) && (mousePos.x < search_text_left_end_pos)) {
				if ($('#direct_text').val() == $('#direct_text').attr('txt')) {
					$('#direct_text').select().removeClass('ipton');
					isSearchTxtSelected = false;
					if ($.trim($('#search_text').val()) == "") {
						$('#J_thl_div').hide();
					}
				}
			} //else{

			if ((mousePos.y < app_top_pos) && (mousePos.x > search_text_left_end_pos)) {
				if ($('#J_thl_div').is(':hidden') && $('#J_thl_div').attr('data-hide') == 'true') {
					$('#J_thl_div').attr('data-hide', 'false').show();
				}
			}
			if ((mousePos.y > zld_top_pos && mousePos.y < app_top_pos) || mousePos.x > search_text_left_end_pos) {
				
				$('#direct_text').val($('#direct_text').attr('txt')).addClass('ipton');
				if ($('#J_thl_div').is(':hidden') && $('#J_thl_div').attr('data-hide') == 'true') {
					return;
				}
				if (!isSearchTxtSelected) {
					$('#search_text').select().trigger('mouseenter');
					isSearchTxtSelected = true;
				}
			}
			if (mousePos.y > app_top_pos) {
				$('#J_thl_div').attr('data-hide', 'true').hide();
			}


		});
	},
	getcoords: function(ev) {
		if (ev.pageX || ev.pageY) {
			return {
				x: ev.pageX,
				y: ev.pageY
			};
		}
		return {
			x: ev.clientX + document.body.scrollLeft - document.body.clientLeft,
			y: ev.clientY + document.body.scrollTop // - document.body.clientTop 
		};
	}
};
