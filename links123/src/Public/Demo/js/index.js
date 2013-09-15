var APP = $CONFIG['APP'];
var URL = $CONFIG['URL'];
var PUBLIC = $CONFIG['PUBLIC'];

$(function() {

	Zld.Init();
	ZhiDaLan.Init();
	HelpMouse.init();

	// 用户菜单
	$('.uc-menu').hover(
		function(){
			$(this).find('ul').toggle();
		}
	);
	
	// 弹出页
	$(".newWin").on('click', function() {
		window.open($(this).attr('url'));
	});

	// 幻灯
	$('#J_ScrollBox').switchable({
		putTriggers: 'appendTo',
		triggersWrapCls: 'pg',
		panels: '.items li',
		effect: 'scrollLeft',
		interval: 2,
		loop: true,
		autoplay: true
	});
	
});

var ZhiDaLan = { // 直达框
	Init: function(){
		$(document).on('click', function(){
			$('#direct_text').val($('#direct_text').attr('txt')).addClass('ipton');
		});

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
			if(self.CheckLogin()){
				self.Create();
			}
		});
		$(document).on('click',  '#J_ZldList .ctl', function(){
			if(self.CheckLogin()){
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

		$('#J_sortable').sortable({
			update: function(event, ui){
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
	CheckLogin: function(){
		if($CONFIG.IsLogin){
			User.Login('请先登录');
			return false;
		}
		return true;
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

var HelpMouse = {
	init: function(){
		var self = this;
		var isSearchTxtSelected = false;

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
			if(mousePos.y < 70){
				if($('#direct_text').val() == $('#direct_text').attr('txt')){
					$('#direct_text').select().addClass('ipson');
					isSearchTxtSelected = false;
					if($.trim($('#search_text').val()) ==""){
						$('#J_thl_div').hide();
					}
				}
			}
			if(mousePos.y > 110 && mousePos.y < 220){
				$('#direct_text').val($('#direct_text').attr('txt')).removeClass('ipson');
				if(!isSearchTxtSelected){
					$('#search_text').select().trigger('mouseenter');
					isSearchTxtSelected = true;
				}
			}
			if(mousePos.y > 300){
				if($.trim($('#search_text').val()) ==""){
					$('#search_text').select().trigger('mouseleave');
					isSearchTxtSelected = false;
				}
			}
		});
	},
	getcoords: function(ev){
		if(ev.pageX || ev.pageY){ 
			return { x: ev.pageX, y: ev.pageY }; 
		} 
		return{ 
			x: ev.clientX + document.body.scrollLeft - document.body.clientLeft, 
			y: ev.clientY + document.body.scrollTop - document.body.clientTop 
		}; 
	}
};
