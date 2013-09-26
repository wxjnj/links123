var APP = $CONFIG['APP'];
var URL = $CONFIG['URL'];
var PUBLIC = $CONFIG['PUBLIC'];

$(function() {

	Zld.Init();
	ZhiDaLan.Init();
	Schedule.Init();
	MusicPlayer.Init();
	HelpMouse.init();

	// 弹出页
	$(".newWin").on('click', function() {
		window.open($(this).attr('url'));
	});

	$('#J_Apps').sortable();

	// 幻灯
	var swapi;
	var _dosw = function(){
		swapi = $('#J_ScrollBox').switchable({
			putTriggers: 'appendTo',
			triggersWrapCls: 'pg',
			panels: '.items li',
			effect: 'scrollLeft',
			interval: 10,
			loop: true,
			autoplay: true
		});
	}
	_dosw();
	$(window).on('resize', function(){
		swapi.unbind();
		_dosw();
	});
	$('#J_ScrollBox').find('li').on('hover', function(){
		$(this).toggleClass('hover');
	});

	// 发现
	$('#J_Find .find li').on('hover', function(){
		$(this).toggleClass('hover');
	});

	// 切换宽屏
	$('.screen-change-btn').on('click', 'a', function(){
		var css;
		var self = $(this);
		if(self.attr('data-size') == 'wide'){
			//self.html('窄 屏');
			//强制切换图片大小
			$('.pics .items li, .pics img').css('width', '310');
			css = 'style-widescreen';
		}else{
			//self.html('宽 屏');
			//强制切换图片大小
			css = 'style-960';
			$('.pics .items li, .pics img').css('width', '250');
		}

		// ie8下 需要先remove掉respone.js，负责该js一直hold住mediaquery部分的css，无法完成切换
		//$('#ie_respond_script').remove();
		$('#screen-style-link').remove();
		
		var link = document.createElement("link");
		link.id = "screen-style-link";
		link.rel = "stylesheet";
		link.type = "text/css";
		link.href = PUBLIC + '/IndexV3/css/' + css + '.css';
		document.getElementsByTagName("head")[0].appendChild(link);
		
		//强制切换宽窄屏 需要重新初始化今日焦点图控件
		swapi.unbind();
		_dosw();

	});
});

var ZhiDaLan = { // 直达框
	Init: function(){
		$(document).on('click', function(){
			$('#direct_text').val($('#direct_text').attr('txt')).addClass('ipton');
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

		$("#direct_text").on('mouseout', function(){
			var tag = $.trim($('#direct_text').val());
			if(tag == '' || tag == $('#direct_text').attr('txt')){
				$('#search_text').select();
				$('#direct_text').addClass('ipton');
			}			
		});

		$("#direct_text").on('click', function(){
			var tag = $.trim($('#direct_text').val());
			if (tag == $('#direct_text').attr('txt')){
				$('#direct_text').val('').removeClass('ipton');
			}
			return false;
		}).on('blur', function(){
			$('#direct_text').addClass('ipton');
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
	}
};

var Zld = { // 自留地
	IsSortable: false, //是否为拖拽点击，true则不打开自留地网址
	Init: function(){
		var self = this;
		var obj = $('#J_ZldList');

		$(document).on('click', '#J_ZldList .add', function(){
			if(User.CheckLogin()){
				self.Create();
			}
		});
		$(document).on('click',  '#J_ZldList .ctl', function(){
			if(User.CheckLogin()){
				if($(this).hasClass('add')){ return false; }
				var o = $(this).closest('li');
				var id = o.data('id');
				var nm = o.find('b').html();
				var url = o.data('url');
				self.Create(id, nm, url);
				return false;
			}
		});
		$(document).on('click', '#J_ZldList .nm', function(){
			if (!Zld.IsSortable) {
				var o = $(this).closest('li');
				var url = o.data('url');
				self.Go(url);
			} else {
				Zld.IsSortable = false;
			}
			return false;
		});

		var holder = {};
		$(document).on('mouseover', '#J_sortable li .nm', function(){
			var nm = $(this);
			var oldWidth = nm.width();
			holder.oldPaddingLeft = nm.css('padding-left').replace('px', '') * 1;
			holder.oldPaddingRight = nm.css('padding-right').replace('px', '') * 1;
			nm.css({
				'width': oldWidth + holder.oldPaddingRight + holder.oldPaddingLeft + 'px',
				'display': 'inline-block',
				'text-align': 'center',
				'font-size': '14px',
				'padding-left': 0,
				'padding-right': 0
			});
			nm.find('b').css('margin', '0 -10px -5px 0');
		}).on('mouseout', '#J_sortable li .nm', function(){
			var nm = $(this);
			nm.css({
				'width': 'auto',
				'display': '',
				'text-align': 'center',
				'font-size': '12px',
				'padding-left': holder.oldPaddingLeft + 'px',
				'padding-right': holder.oldPaddingRight + 'px'
			});
			nm.find('b').css('margin', '0');
		});
		/*
		$(document).on('mouseenter', '#J_Zld input[name="url"], #J_Zld input[name="name"]', function(){
			$(this).select();
		});
		*/
		$('#J_sortable').sortable({
			items: '> li:not(.add)',
			start: function(event, ui){
				$(ui.item).find('span').css('cursor', 'move');
			},
			update: function(event, ui){
				$(ui.item).find('span').css('cursor', 'pointer');
				Zld.IsSortable = true;
				
				$.post(
					URL + '/sortArealist', 
					{'area' : $(this).sortable('toArray')},
					function(data) {
						if (data == 1) {
							//成功
						} else if (data == 0){
							//失败
						} else {
							//失败
						}
					}
				);
			},
			stop: function(event, ui) {
				Zld.IsSortable = false;
				$(ui.item).find('span').css('cursor', 'pointer');
			}
		});
		$('#J_sortable').sortable('enable');
		
		$(document).on('click', '#J_Zld .lkd-add, #J_Zld .lkd-edit', function(){

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
			
			$.post(
				URL + '/updateArea', 
				{ 'web_id': id, 'web_url': url, 'web_name': name },
				function(data) {
					var licur = function(){
						var li = null;
						obj.find('ul>li').each(function(){
							if($(this).data('id') == id){
								li = $(this);
								return;
							}
						});
						return li;
					}
					if(data == 1){ //更新成功
						var li = licur();
						li.attr('url', '/Link/index.html?mod=myarea&amp;url=' + url);
						li.data('url', url);
						li.find('b').html(name);
					}else if(data > 1){ //新加成功
						var li = obj.find('.add').closest('li');
						li.before(self.CreateItem(data, name, url));
					}else if(data == -1){
						User.Login('请先登录');
					}else{
						alert('操作失败');
					}
					o.dialog('close');
				}
			);
			return false;
		});
		
		$(document).on('click', '#J_Zld .lkd-del', function(){

			var o = $('#J_Zld');
			var id = o.find('input[name="id"]').val();
			
			$.post(
				URL + '/delArea', 
				{ 'web_id': id },
				function(data) {
					var licur = function(){
						var li = null;
						obj.find('ul>li').each(function(){
							if($(this).data('id') == id){
								li = $(this);
								return;
							}
						});
						return li;
					}
					if(data == 1){
						var li = licur();
						li.remove();
					}else if(data == -1){
						User.Login('请先登录');
					}else{
						alert('操作失败');
					}
					o.dialog('close');
				}
			);
			return false;
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
				width: 384,
				modal: true,
				resizable: false,
				open: function(){
					setTimeout(function(){obj.find('input[name="name"]').select();}, 20);
				}
			});

			obj.find('.close').on('click', function(){
				obj.dialog('close');
				return false;
			});

			obj.find('input[type="text"]').on('focus', function(){
				$(this).css('background', '#fff');
			}).on('blur', function(){
				$(this).css('background', '#eeefef');
			});

			obj.find('input[name="name"],input[name="url"]').on('mouseover', function(){
				$(this).focus().select();
			});

			obj.find('input[name="name"],input[name="url"]').on('keydown', function(event){
				if (event.keyCode == 13) {
					if(obj.find('.editp').is(":visible")){
						obj.find('.lkd-edit').trigger('click');	
					}else{
						obj.find('.lkd-add').trigger('click');
					}
				}
			});
		}

		var obj = $('#J_Zld');		
		if(id){
			obj.find('input[name="id"]').val(id);
			obj.find('input[name="name"]').val(nm);
			obj.find('input[name="url"]').val(url);
			obj.find('.editp').show();
			obj.find('.addp').hide();
		}else{
			obj.find('input[name="id"]').val('');
			obj.find('input[name="name"]').val('');
			obj.find('input[name="url"]').val('');
			obj.find('.editp').hide();
			obj.find('.addp').show();
		}

		obj.dialog('open');
	},
	CreateItem: function(id, nm, url){
		var hl = '<li id="'+ id +'" url="/Link/index.html?mod=myarea&amp;url='+ url +'" data-id="'+ id +'" data-url="'+ url +'">';
		hl = hl + '<span class="nm"><i class="mask"></i><b>'+ nm +'</b></span>';
		hl = hl + '<span class="ctl"><i class="mask"></i></span>';
		hl = hl + '</li>';
		return hl;
	}
};

var Schedule = {
	Init: function(){
		$.datepicker.regional['zh-CN'] = {
		clearText: '清除',
		clearStatus: '清除已选日期',
		closeText: '关闭',
		closeStatus: '不改变当前选择',
		prevText: '<上月',
		prevStatus: '显示上月',
		prevBigText: '<<',
		prevBigStatus: '显示上一年',
		nextText: '下月>',
		nextStatus: '显示下月',
		nextBigText: '>>',
		nextBigStatus: '显示下一年',
		currentText: '今天',
		currentStatus: '显示本月',
		monthNames: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
		monthNamesShort: ['一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '十一', '十二'],
		monthStatus: '选择月份',
		yearStatus: '选择年份',
		weekHeader: '周',
		weekStatus: '年内周次',
		dayNames: ['星期日', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六'],
		dayNamesShort: ['周日', '周一', '周二', '周三', '周四', '周五', '周六'],
		dayNamesMin: ['日', '一', '二', '三', '四', '五', '六'],
		dayStatus: '设置 DD 为一周起始',
		dateStatus: '选择 m月 d日, DD',
		dateFormat: 'mm月dd日',
		firstDay: 1,
		initStatus: '请选择日期',
		isRTL: false
		};
		$.datepicker.setDefaults($.datepicker.regional['zh-CN']);

		var self = this;
		var obj = $('#J_Schedule');

		self.BindDatePicker();

		$(document).on('focus', '#J_Schedule .t input', function(){
			$(this).addClass('on');
			if($(this).val() == '快来创建第一个日程'){
				$(this).val('');
			}else{
				$(this).val($(this).val());
			}
		}).on('blur', '#J_Schedule .t input', function(){
			$(this).removeClass('on');
			self.Save(this);
		});
		$(document).on('click', '#J_Schedule li .sp', function(){
			self.Del(this);
			return false;
		});
		obj.find('.new').on('click', function(){
			if(User.CheckLogin()){
				var hl = self.Create();
				var ul = obj.find('ul');
				ul.find('li').removeClass('first');
				ul.prepend(hl);
				self.BindDatePicker();
				$('.task0').find('.t input')[0].focus(); //hl能取到值但无法选中
			}
			return false;
		});
		$(document).on('keydown', '#J_Schedule .t input', function(event){
			if (event.keyCode == 13) {
				$(this).blur();
			}
		});
		obj.find('input[name="name"],input[name="url"]').on('keydown', function(event){
			
		});
	},
	BindDatePicker: function(){
		var self = this;
		var obj = $('#J_Schedule');
		obj.find('.d input').datepicker('destroy');
		obj.find('.d input').datepicker({
			defaultDate: $(this).parent().data('d'),
			onSelect: function(){
				self.Save(this);
			}
		});
	},
	GetDateFormat: function(s, f){
		var str = f;
		var Week = ['日', '一', '二', '三', '四', '五', '六'];
		str = str.replace(/yyyy|YYYY/, s.getFullYear());
		str = str.replace(/yy|YY/, (s.getYear() % 100) > 9 ? (s.getYear() % 100).toString() : '0' + (s.getYear() % 100));
		var month = s.getMonth() + 1;
		str = str.replace(/MM/, month > 9 ? month.toString() : '0' + month);
		str = str.replace(/M/g, month);
		str = str.replace(/w|W/g, Week[s.getDay()]);
		str = str.replace(/dd|DD/, s.getDate() > 9 ? s.getDate().toString() : '0' + s.getDate());
		str = str.replace(/d|D/g, s.getDate());
		str = str.replace(/hh|HH/, s.getHours() > 9 ? s.getHours().toString() : '0' + s.getHours());
		str = str.replace(/h|H/g, s.getHours());
		str = str.replace(/mm/, s.getMinutes() > 9 ? s.getMinutes().toString() : '0' + s.getMinutes());
		str = str.replace(/m/g, s.getMinutes());
		str = str.replace(/ss|SS/, s.getSeconds() > 9 ? s.getSeconds().toString() : '0' + s.getSeconds());
		str = str.replace(/s|S/g, s.getSeconds());
		return str;
	},
	Create: function(){
		var now = this.GetDateFormat(new Date(), 'MM月DD日');
		var hl = '';
		hl = hl + '<li class="first task0" class="first" data-id="0">';
		hl = hl + '	<span class="sp">取消日程</span>';
		hl = hl + '	<div class="info">';
		hl = hl + '		<span data-d="'+now+'" class="d"><input type="text" value="'+now+'" /></span>';
		hl = hl + '		<span class="s"> - </span>';
		hl = hl + '		<span class="t"><input class="on" type="text" value="" /></span>';
		hl = hl + '	</div>';
		hl = hl + '</li>';
		return hl;
	},
	Save: function(el){
		var obj = $(el).closest('li');
		var id = obj.data('id');
		var datetime = obj.find('.d input').val();
		var content = obj.find('.t input').val();

		if(id == 0){
			$.post(
				URL + '/addSchedule', 
				{ 'datetime': datetime, 'content' : content },
				function(data){
					if(data > 1){
						obj.removeClass('task0').addClass('task'+data);
						obj.data('id', data);
					}else if(data == -1){
						User.Login();
					}else if(data == 0){
						alert('添加失败');
					}
				}
			);
		}else{
			$.post(
				URL + '/updateSchedule', 
				{ 'id': id, 'datetime': datetime, 'content' : content },
				function(data){
					if(data == 1){
						//TODO
					}else{
						alert('更新失败');
					}
				}
			);
		}
		return false;
	},
	Del: function(el){
		var li = $(el).closest('li');
		var id = li.data('id');
		if(id == 0){
			li.next().addClass('first');
			li.remove();
			return false;
		}
		$.post(
			URL + '/delSchedule', 
			{ 'id': id },
			function(data){
				if(data == 1){
					var li = $('.task'+id);
					li.next().addClass('first');
					li.remove();
				}else{
					alert('操作失败');
				}
			}
		);
		return false;
	}
};

var MusicPlayer = {
	Init: function(){
		var self = this;
		$('#J_Music').find('.top-mv .nm a').on('click', function(){
			self.Play($(this).data('url'));
			return false;
		});
		$('#J_Music').find('.hot-music a').on('click', function(){
			self.Play($(this).data('url'));
			return false;	
		})
		$('#J_Music').find('.del').on('click', function(){
			self.Stop();
		});

		var musicController = $('#music-controller');
		var li;
		$('#J_Music').find('.top-mv').on('mouseover', 'li', function(){
			li = $(this);
			li.find('.img').after(musicController);
			musicController.show();
		}).on('mouseout', 'li', function(){
			if(musicController.find('.pause').length !=0) return;
			musicController.hide();
		});

		$('#J_Music').find('.music-control').on('mouseover', 'a', function(){
			var name = $(this).attr('data-name');
			musicController.find('.' + name).addClass('hover');
		}).on('mouseout', 'a', function(){
			var name = $(this).attr('data-name');
			musicController.find('.' + name).removeClass('hover');
		});

		$('#J_Music').find('.music-control').on('click', 'a.big', function(){
			var st = $(this).hasClass('go') ? 'pause' : 'go';
			musicController.find('.big').removeClass('go pause').addClass(st);
			if(st == 'pause'){
				console.log(li.find('.nm a').data('url'));
				self.Play(li.find('.nm a').data('url'));
			}
		});

	},
	Play: function(url){
		if(!$('#J_MusicPlayer').size()){
			$('body').append('<iframe id="J_MusicPlayer" style="width:0;height:0;visibility:hidden" src="'+url+'"></iframe>');
		}else{
			$('#J_MusicPlayer').attr('src', url);
		}
	},
	Stop: function(){
		$('#J_MusicPlayer').size() && $('#J_MusicPlayer').remove();
	}
};

var HelpMouse = {
	init: function(){
		var self = this;
		var isSearchTxtSelected = false;


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

		$(document).on('mousemove', function(ev){
			var isNeedHelp = 1;
			$('.ui-dialog').each(function(){
				if($(this).is(":visible")){
					isNeedHelp ? isNeedHelp = 0 : '';
				}
			});
			$('.fancybox-wrap').each(function(){
				if($(this).is(":visible")){
					isNeedHelp ? isNeedHelp = 0 : '';
				}
			});
			if(!isNeedHelp){ return false; }
			var mousePos = self.getcoords(ev);

			var $search_text = $('#search_text');
			var $direct_text = $('#direct_text');
			var search_text_left_end_pos = $search_text.offset().left - 10;
			var search_text_right_end_pos = search_text_left_end_pos + $search_text.width();
			var direct_text_right_end_pos = $direct_text.offset().left + $direct_text.width() + 10;

			if((mousePos.y < 200) && (mousePos.x < search_text_left_end_pos)){
				if($('#direct_text').val() == $('#direct_text').attr('txt')){
					$('#direct_text').select().removeClass('ipton');
					isSearchTxtSelected = false;
					if($.trim($('#search_text').val()) ==""){
						$('#J_thl_div').hide();
					}
				}
			}//else{
			if((mousePos.y < 200)  && (mousePos.x > search_text_left_end_pos)){
				if($('#J_thl_div').is(':hidden') && $('#J_thl_div').attr('data-hide') == 'true'){
					$('#J_thl_div').attr('data-hide', 'false').show();
				}
			}
			if((mousePos.y > 200 && mousePos.y < 360) || mousePos.x > search_text_left_end_pos){
				$('#direct_text').val($('#direct_text').attr('txt')).addClass('ipton');
				if($('#J_thl_div').is(':hidden') && $('#J_thl_div').attr('data-hide') == 'true'){
					return;
				}
				//if(!isSearchTxtSelected){
					$('#search_text').select().trigger('mouseenter');
					isSearchTxtSelected = true;
				//}
			}
			if(mousePos.y > 360){
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
