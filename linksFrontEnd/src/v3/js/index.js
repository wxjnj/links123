var APP = $CONFIG['APP'];
var URL = $CONFIG['URL'];
var PUBLIC = $CONFIG['PUBLIC'];

$(function() {

	Zld.Init();
	ZhiDaLan.Init();
	MusicPlayer.Init();
	HelpMouse.init();
	Calendar.Init()

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

	// 弹出页
	$(".newWin").on('click', function() {
		window.open($(this).attr('url'));
	});

	$('#J_Apps').sortable();

	// banner
	// setTimeout(function(){
	// 	var o = $('.banner');
	// 	var o1 = null;
	// 	$('#J_Apps>li').each(function(){
	// 		if($(this).attr('id') == 1){
	// 			o1 = $(this).find('img');
	// 			return;
	// 		}
	// 	});
	// 	if(!o1){ return; }
	// 	var pos = o1.position();
	// 	o.css({border: '1px dotted #ccc', position: 'absolute'});
	// 	o.animate({
	// 		width: 0,
	// 		height: 0,
	// 		top: pos.top-o.height()+40+36,
	// 		left: pos.left+36
	// 	}, 2000, function(){
	// 		o.remove();
	// 	});

	// }, 3000);

	// 幻灯
	$('#J_ScrollBox').find('.items').slidesjs({
		play: {
			active: true,
			auto: false,
			interval: 10000,
			swap: false
		},
    	//异步加载幻灯片
    	//初始化加载0
		callback: {
			loaded: function(){
				var cur = $('#J_ScrollBox').find('li:eq(' + 0 + ')').find('img');
				if(!cur.attr('src')){
					var path = cur.data('src');
					cur.attr('src', path);
				}
			},
			start: function(cur, next) {
				var target = $('#J_ScrollBox').find('li:eq(' + next + ')').find('img');
				if(!target.attr('src')){
					var path = target.data('src');
					target.attr('src', path);
				}
			}
   		}
	});

	$('#J_ScrollBox').find('li').on('hover', function() {
		$(this).toggleClass('hover');
	});

	// 发现
	$('#J_Find .find li').on('hover', function() {
		$(this).toggleClass('hover');
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

		window.sladePlugin.update();
		Zld.Resize();
	});

	(function(){ //app图标相关
		var nmlLen = 9, wideLen = 11;
		var appsList = $('#J_Apps>li');
		var appsListLen = appsList.size();

		var appPkg = function(){
			var isWide = $('body').is('.widescreen');
			var needLen = nmlLen;
			if(isWide){
				needLen = wideLen;
			}
			if(appsListLen<= needLen){ 
				$('.app-icon-list').hide();
				return;
			}
			$('.app-icon-list').show();

			var panel = $('.app-icon-list').find('ul');
			panel.empty();
			appsList.each(function(index, el){
				if(index>needLen - 1){
					panel.append($(el).clone());
				}
			});
		}
		appPkg();
		$('body').on('screenchange', function(){
			appPkg();
		});
		$('.app-more').on('mouseenter', function(){
			$('.app-more-box').show();
		}).on('mouseleave', function(){
			$('.app-more-box').hide();
		});
	})();
});

var ZhiDaLan = { // 直达框
	Init: function() {
		$(document).on('click', function() {
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
			$('#direct_text').select();
		});
	}
};

var Zld = { // 自留地
    IsSortable: false,
    //是否为拖拽点击，true则不打开自留地网址
    Resize: function() {
        //自适应算法
        var box = $('#J_sortable');
        var boxWidth = box.width();
        var lis = box.find('li');
        lis.css('width', 'auto');
        var liWidth = 0;
        var overIndex = null;
        var fstLineWidth = null;
        $.each(lis, function(k, v) {
            liWidth += ($(v).width() + 5);
            if (!overIndex && liWidth > boxWidth) {
                overIndex = k;
                fstLineWidth = liWidth - $(v).width() - 5;
            }
        });
        lis.find('.nm').trigger('mouseout');
        if (liWidth <= boxWidth) return;
        //TODO: 算法还不完美！
        if (boxWidth - fstLineWidth > 45) {
            var w = lis.eq(overIndex).width() + 5 - (boxWidth - fstLineWidth);
            w = ~~Math.ceil(w / (overIndex + 1));
            var s = lis.filter(':lt(' + (overIndex + 1) + ')');
            $.each(s, function(k, v) {
                var ow = $(v).width();
                $(v).css('width', ow - w + 'px');
            });
        }
    },
    Init: function() {
        var self = this;
        var obj = $('#J_ZldList');
        self.Resize();
        $(document).on('click', '#J_ZldList .add', function() {
            //if(User.CheckLogin()){
            self.Create();
            //}
        });
        $(document).on('click', '#J_ZldList .ctl', function() {
            //			if(User.CheckLogin()){
            if ($(this).hasClass('add')) {
                return false;
            }
            var o = $(this).closest('li');
            var id = o.data('id');
            var nm = o.find('b').html();
            var url = o.data('url');
            self.Create(id, nm, url);
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

        var holder = {};
        $(document).on('mouseover', '#J_sortable li .nm', function() {
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
        }).on('mouseout', '#J_sortable li .nm', function() {
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
            start: function(event, ui) {
                $(ui.item).find('span').css('cursor', 'move');
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
                self.Resize();
                Zld.IsSortable = false;
                $(ui.item).find('span').css('cursor', 'pointer');
            }
        });
        $('#J_sortable').sortable('enable');

        $('#J_Apps').sortable({
            update: function(event, ui) {
                $.post(
                    URL + '/sortApp', {
                        'appIds': $(this).sortable('toArray')
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
        hl = hl + '<span class="ctl"></span>';
        hl = hl + '</li>';
        return hl;
    }
};

var MusicPlayer = {
	Init: function() {
		var self = this;
		$('#J_Music').find('.top-mv .nm a').on('click', function() {
			self.Play($(this).data('url'), 1);
			return false;
		});
		$('#J_Music').find('.hot-music a').on('click', function() {
			self.Play($(this).data('url'), 2, $(this).closest('li').data('id'));
			return false;
		})

		$('#J_Music').on('mouseenter', '.top-mv li', function() {
			$(this).find('.music-controller').show();
		});

		var ctrls = $('.music-controller');
		ctrls.on('mouseleave', function() {
			if ($(this).find('.pause').length) return;
			$(this).hide();
		}).on('click', '.go', function() {
			var b = $(this);
			b.removeClass('go').addClass('pause');
			$('.music .on').removeClass('on');
			var url = b.parent('.music-controller').siblings('.nm').find('a').addClass('on').attr('data-url');
			self.Play(url);
			b.parents('li').siblings('li').find('.big').removeClass('pause').addClass('go').parent('.music-controller').hide();
		}).on('click', '.pause, .stop', function() {
			var b = $(this);
			b.removeClass('pause').addClass('go');
			$('.music .on').removeClass('on');
			self.Stop();
			b.parent('.music-controller').hide();
		}).on('click', '.next', function() {
			$(this).parent('.music-controller').show().find('.big').removeClass('go').addClass('pause')
			var on = $('.music .on');
			var id;
			if (on.length == 0 || on.parents('.top-mv').length != 0) {
				id = 1;
			} else {
				id = on.parent('li').attr('data-id') * 1 + 1;
			}
			on.removeClass('on');
			if ($('.song' + id).length == 0) id = 1;
			var url = $('.song' + id).find('a').addClass('on').attr('data-url');
			self.Play(url);
			$(this).parents('li').siblings('li').find('.big').removeClass('pause').addClass('go').parent('.music-controller').hide();
		}).on('click', '.prev', function() {
			$(this).parent('.music-controller').show().find('.big').removeClass('go').addClass('pause');
			var on = $('.music .on');
			var id;
			var last_id = $('.hot-music').find('li:last').attr('data-id');
			if (on.length == 0 || on.parents('.top-mv').length != 0) {
				id = last_id;
			} else {
				id = on.parent('li').attr('data-id') * 1 - 1;
			}
			on.removeClass('on');
			if ($('.song' + id).length == 0) id = last_id;
			var url = $('.song' + id).find('a').addClass('on').attr('data-url');
			self.Play(url);
			$(this).parents('li').siblings('li').find('.big').removeClass('pause').addClass('go').parent('.music-controller').hide();
		});

	},
	Play: function(url, type, id) { //type=1 专辑 type=2 单曲
		$('#J_Music').find('.top-mv li').eq(0).trigger('mouseover');

		var mc = $('#J_Music').find('.music-control');
		//$('.music-controller').find('.big').removeClass('go').addClass('pause');
		if (id) {
			$('.top' + 1).find('.music-controller').show().find('.big').removeClass('go').addClass('pause');
			$('.top' + 2).find('.music-controller').hide().find('.big').removeClass('pause').addClass('go');
		}

		if (!$('#J_MusicPlayer').size()) {
			//兼容模式下iframe display:none 不播 包括父级 display:none 也不播
			//TODO
			$('body').append('<p style="height:0;overflow:hidden;"><iframe id="J_MusicPlayer" frameborder="0" style="visibility:hidden;" src="' + url + '"></iframe></p>');
		} else {
			$('#J_MusicPlayer').attr('src', url);
		}

		$('#J_MusicPlayer').data('type', type);
		$('#J_MusicPlayer').data('currid', id ? id: 0);
		if (type == 1) {
			//TODO
		} else if (type == 2) {
			$('.top-mv .nm a').removeClass('on');
			var o = $('.song' + id);
			o.siblings().find('a').removeClass('on');
			o.find('a').addClass('on');
		}
	},
	Stop: function() {
		$('.hot-music>ul>li>a').removeClass('on');
		$('.top-mv .nm a').removeClass('on');
		$('#J_MusicPlayer').size() && $('#J_MusicPlayer').remove();
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
			// $('.fancybox-wrap').each(function(){
			// if($(this).is(":visible")){
			// isNeedHelp ? isNeedHelp = 0 : '';
			// }
			// });
			if (!isNeedHelp) {
				return false;
			}
			var mousePos = self.getcoords(ev);

			var $search_text = $('#search_text');
			var $direct_text = $('#direct_text');
			var search_text_left_end_pos = $search_text.offset().left - 10;
			var search_text_right_end_pos = search_text_left_end_pos + $search_text.width();
			var direct_text_right_end_pos = $direct_text.offset().left + $direct_text.width() + 10;

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

			if ((mousePos.y < 200) && (mousePos.x < search_text_left_end_pos)) {
				if ($('#direct_text').val() == $('#direct_text').attr('txt')) {
					$('#direct_text').select().removeClass('ipton');
					isSearchTxtSelected = false;
					if ($.trim($('#search_text').val()) == "") {
						$('#J_thl_div').hide();
					}
				}
			} //else{
			if ((mousePos.y < 200) && (mousePos.x > search_text_left_end_pos)) {
				if ($('#J_thl_div').is(':hidden') && $('#J_thl_div').attr('data-hide') == 'true') {
					$('#J_thl_div').attr('data-hide', 'false').show();
				}
			}
			if ((mousePos.y > 200 && mousePos.y < 360) || mousePos.x > search_text_left_end_pos) {
				$('#direct_text').val($('#direct_text').attr('txt')).addClass('ipton');
				if ($('#J_thl_div').is(':hidden') && $('#J_thl_div').attr('data-hide') == 'true') {
					return;
				}
				if (!isSearchTxtSelected) {
					$('#search_text').select().trigger('mouseenter');
					isSearchTxtSelected = true;
				}
			}
			if (mousePos.y > 360) {
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

/*
 *   Calendar
 */
(function() {
    var config = {};
    config.CHS_WEEKS = ['一', '二', '三', '四', '五', '六', '日'];
    config.CHS_MONTHS = ['一', '二', '三', '四', '五', '六', '七', '八', '九', '十', '十一', '十二'];
    config.ENG_WEEKS = ['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];
    config.ENG_MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'Auguest', 'September', 'October', 'November', 'December'];
    function countObjLength(o) {
        var count = 0,
            i;
        for (i in o) count++;
        return count;
    }
    Date.prototype.add_day = function(days) {
        return new Date(this * 1 + days * 86400000);
    };
    //oo
    var Class = function(parent) {
        var _class = function() {
                this.init.apply(this, arguments);
            },
            key,
            subclass;
        _class.prototype.init = function() {};
        if (parent) {
            subclass = function() {};
            subclass.prototype = parent.prototype;
            _class.uber = parent.prototype;
            _class.prototype = new subclass;
        }
        _class.extend = function(obj) {
            for (key in obj) {
                _class[key] = obj[key];
            }
        };
        _class.include = function(obj) {
            for (key in obj) {
                _class.prototype[key] = obj[key];
                if (_class.uber && typeof(_class.uber[key]) == 'function') {
                    obj[key].spfunc = _class.uber[key];
                }
            }
        };
        _class.prototype._super = function() {
            arguments.callee.caller.spfunc.apply(this, arguments);
        };
        return _class;
    };

    //top控制器
    var Calendar = {
        Init: function(type) {
            var self = this;
            self.defaultMarkTitle = '新建日程';
            self.type = type || 'Date';
            self.weekStart = 1;
            self.marksStore = {};
            self.timer = null;
            var today = self.today = Date.today();
            self.tooltip = $('<div class="calendar-tip"><div class="content"></div><span class="ang"></span></div>');
            self.ajaxOverlayer = $('.cal-ajax-overlayer');
            self.setCurrentDate(today);
            $('.cal-header-date-prev').on('click', function() {
                self.prevView();
            });
            $('.cal-header-date-next').on('click', function() {
                self.nextView();
            });
            $('.cal-view-select-btn').on('click', function() {
                $('.cal-view-select-btn').removeClass('active');
                var type = $(this).attr('data-type');
                self.changeType(type);
            });

            $('.cal_v303_prev_btn').on('click', function() {
                self.MainViewChangeFunc(self.currentDate - 1);
            });
            $('.cal_v303_next_btn').on('click', function() {
                self.MainViewChangeFunc(self.currentDate + 1);
            });
            $('.cal_v303_mini_cal').on('click', 'td', function() {

                var d = $(this).find('a').attr('data-date');
                self.MainViewChangeFunc(d);

            });

            $(document).on('click', '.extra-calendar-overlayer', function() {
                $(this).hide();
                $('.extra-calendar').hide();
            });

            $(document).on('keydown', function(e){
                if (e.keyCode == 27) {
                    $('.extra-calendar-overlayer').hide();
                    $('.extra-calendar').hide();
                }
            });

            $(document).on('click', '.cal-close', function(){
                $('.extra-calendar-overlayer').hide();
                $('.extra-calendar').hide();
            });

            $(document).on('click', '.cal_show_pop_btn', function() {
                var type = $(this).attr('data-type');
                self.changeType(type);
                $('.extra-calendar, .extra-calendar-overlayer').show();
            });
            $(document).on('click', '.cal_v303_more', function() {
                self.changeType('Date');
                $('.extra-calendar, .extra-calendar-overlayer').show();
            });

            /*
             $('.cal-body').on('mouseover', '.cal-month td', function(){
             var td = $(this);
             var date = td.find('a').attr('data-date');
             if(!self.marksStore[self.currentMarkId] || !self.marksStore[self.currentMarkId][date]) return;
             var marks = self.marksStore[self.currentMarkId][date];
             var tem = '';
             $.each(marks, function(k, v){
             tem += '<p>' + v.desc + '</p>';
             });
             self.tooltip.find('.content').html(tem).end().appendTo(td).show();
             }).on('mouseout', '.cal-month td', function(){
             self.tooltip.hide();
             });
             */
            self.MainView.Init();
            /**/
            self.DateView.Init();
            self.MonthView.Init();
            self.WeekView.Init();
            self.DateView.miniMonthView.render();
            self.DateView.mainPanel.show();
            self.loadMarks();
            /**/
        },
        showDate: function() {
            var self = this;
            var type = self.type;
            var currentDate = self.currentDate;
            var currentMonth = self.currentMonth;
            var currentYear = self.currentYear;
            if (type == 'Date') {
                self.date_text = currentYear + '年' + (currentMonth + 1) + '月' + currentDate + '日';
            }
            if (type == 'Week') {
                var start = self.currentWeekStartObject;
                var end = self.currentWeekEndObject;
                var startYear = start.getFullYear();
                var startMonth = start.getMonth() + 1;
                var startDate = start.getDate();
                var endYear = end.getFullYear();
                var endMonth = end.getMonth() + 1;
                var endDate = end.getDate();
                var endShowYear = endYear == startYear ? '': endYear + '年';
                if (endShowYear != '' || endMonth != startMonth) {
                    var endShowMonth = endMonth + '月';
                } else {
                    var endShowMonth = '';
                }
                self.date_text = startYear + '年' + startMonth + '月' + startDate + '日 - ' + endShowYear + endShowMonth + endDate + '日';
            }
            if (type == 'Month') {
                self.date_text = currentYear + '年' + (currentMonth + 1) + '月';
            }
            self.cal_date_text = currentYear + '年' + (currentMonth + 1) + '月' + currentDate + '日';
            $('.cal-header-date').html(self.date_text);
            $('.cal_v303_show_date').html(self.cal_date_text);
        },
        changeType: function(targetType) {
            this.type = targetType;
            $('.cal-view-select-btn').removeClass('active');
            $('.cal-view-select-btn-' + targetType.toLowerCase()).addClass('active');
            this.showDate();
            this.DateView.mainPanel.hide();
            this.WeekView.mainPanel.hide();
            this.MonthView.mainPanel.hide();
            var cur = this[targetType + 'View'];
            cur.mainPanel.show();
            this.MainView.renderMonthView();
            if (targetType == 'Date') {
                Calendar.DateView.miniMonthView.render();
            }
            if (targetType == 'Week') {}
            if (targetType == 'Month') {
                Calendar.MonthView.tableView.render();
            }
            cur.renderMarks();
        },
        MainViewChangeFunc: function(t) {
            var self = this;
            var targetDate = new Date(self.currentYear, self.currentMonth, t);
            self.setCurrentDate(targetDate);
            self.MainView.renderMonthView();
            if (self.marksStore[self.currentMarkId]) {
                self.DateView.renderMarks();
            } else {
                self.loadMarks();
            }
        },
        prevView: function() {
            this[this.type + 'PrevView']();
        },
        nextView: function() {
            this[this.type + 'NextView']();
        },
        DateChangeFunc: function(t) {
            var self = this;
            var targetDate = new Date(self.currentYear, self.currentMonth, t);
            self.setCurrentDate(targetDate);
            self.DateView.miniMonthView.render();
            self.MainView.renderMonthView();
            if (self.marksStore[self.currentMarkId]) {
                self.DateView.renderMarks();
            } else {
                self.loadMarks();
            }
        },
        DatePrevView: function() {
            this.DateChangeFunc(this.currentDate - 1);
        },
        DateNextView: function() {
            this.DateChangeFunc(this.currentDate + 1);
        },
        WeekChangeFunc: function(t) {
            var self = this;
            var targetDate = new Date(self.currentYear, self.currentMonth, t);
            self.setCurrentDate(targetDate);
            var id_start = self.currentWeekStartObject.getFullYear() + '-' + self.currentWeekStartObject.getMonth();
            var id_end = self.currentWeekEndObject.getFullYear() + '-' + self.currentWeekEndObject.getMonth();
            self.MainView.renderMonthView();
            if (self.marksStore[id_start] && self.marksStore[id_end]) {
                self.WeekView.renderMarks();
            } else {
                self.loadMarks();
            }
        },
        WeekPrevView: function() {
            this.WeekChangeFunc(this.currentWeekStart - 7);
        },
        WeekNextView: function() {
            this.WeekChangeFunc(this.currentWeekStart + 7);
        },
        MonthChangeFunc: function(t) {
            var self = this;
            var targetDate = new Date(self.currentYear, t, 1);
            self.setCurrentDate(targetDate);
            self.MonthView.tableView.render();
            self.MainView.renderMonthView();
            if (self.marksStore[self.currentMarkId]) {
                self.MonthView.renderMarks();
            } else {
                self.loadMarks();
            }
        },
        MonthPrevView: function() {
            this.MonthChangeFunc(this.currentMonth - 1);
        },
        MonthNextView: function() {
            this.MonthChangeFunc(this.currentMonth + 1);
        },
        //ajax封装
        request: function(params) {
            var self = this;
            //self.ajaxOverlayer.find('.content').html('').end().show();
            var defaultConfig = {
                type: 'POST',
                dataType: 'json'
            };
            params = $.extend(defaultConfig, params);
            var defer = $.Deferred();
            var ajax = $.ajax(params).done(function(json) {
                //defer.resolveWith(ajax, [json]);
                if(typeof json.status == 'undefined') {
                    defer.resolveWith(ajax, [json]);
                }
                if (json['status'] === 1) {
                    defer.resolveWith(ajax, [json['data']]);
                } else {
                    defer.rejectWith(ajax, [json['status'], json['info']]);
                }
            }).fail(function(xhr, textStatus) {
                    if (xhr.status == 200) {
                        defer.rejectWith(ajax, [xhr.status, 'JSON解析失败']);
                    } else {
                        defer.rejectWith(ajax, [xhr.status, xhr.statusText]);
                    }
                });
            return defer.promise();
        },
        //计算月份天数
        howManyDaysInMonth: function(y, m) {
            var len = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            if (m == 1 && (y % 40 == 0 && y % 100 != 0) || y % 400 == 0) len[1] = 29;
            return len[m];
        },
        //计算日期是全年的第几天
        posInYear: function(y, m, d) {
            for (var i = 1; i < m; ++i) {
                d += howManyDaysInMonth(y, i);
            }
            return d;
        },
        compare: function(a, b) {
            if (a.getFullYear() != b.getFullYear()) {
                return a.getFullYear() - b.getFullYear();
            }
            if (a.getMonth() != b.getMonth()) {
                return a.getMonth() - b.getMonth();
            }
            if (a.getDate() != b.getDate()) {
                return a.getDate() - b.getDate();
            }
            return 0;
        },
        //获取日期是星期几
        posInWeek: function(y, m, d) {
            return (new Date(y, m, d)).getDay();
        },
        setCurrentDate: function(day) {
            var self = this;
            self.currentDateObject = day;
            self.currentDate = day.getDate();
            self.currentMonth = day.getMonth();
            self.currentYear = day.getFullYear();
            self.currentWeekStart = day.getDate() - (day.getDay() == 0 ? 7 : day.getDay() ) + Calendar.weekStart;
            self.currentWeekEnd = self.currentWeekStart + 6;
            self.currentWeekStartObject = new Date(self.currentYear, self.currentMonth, self.currentWeekStart);
            self.currentWeekEndObject = new Date(self.currentYear, self.currentMonth, self.currentWeekEnd);
            self.currentMarkId = self.currentYear + '-' + (self.currentMonth);
            self.showDate();
        },
        //获取数据接口
        loadMarks: function(callBack) {
            var self = this;
            var year = self.currentYear;
            var month = self.currentMonth;
            if (self.type == 'Week') {
                var year1 = self.currentWeekStartObject.getFullYear();
                var month1 = self.currentWeekStartObject.getMonth();
                var year2 = self.currentWeekEndObject.getFullYear();
                var month2 = self.currentWeekEndObject.getMonth();
            }
            //周有跨年和跨月
            if (self.type == 'Week' && year1 != year2 || month1 != month2) {
                var o1 = self.request({
                    url: URL + '/getSchedule?year=' + year1 + '&month=' + (month1 + 1),
                    type: 'GET'
                });
                var o2 = self.request({
                    url: URL + '/getSchedule?year=' + year2 + '&month=' + (month2 + 1),
                    type: 'GET'
                });

                $.when(o1, o2).fail(function(c, e) {
                    self[self.type + 'View'].renderMarks();
                }).done(function(d1, d2) {
                        self.ajaxOverlayer.hide();
                        d1 = d1 === null ? [] : d1;
                        d2 = d2 === null ? [] : d2;

                        if(d1.length != 0) {
                            $.each(d1, function(k, v){
                                $.each(v, function(i, c){
                                    c.desc = c.content;
                                    c.time = c.datetime;
                                });
                                d1[parseInt(k)] = v;
                                delete d1[k];
                            });
                        }
                        if(d2.length != 0) {
                            $.each(d2, function(k, v){
                                $.each(v, function(i, c){
                                    c.desc = c.content;
                                    c.time = c.datetime;
                                });
                                d2[parseInt(k)] = v;
                                delete d2[k];
                            });
                        }

                        self.marksStore[year1 + '-' + month1] = d1;
                        self.marksStore[year2 + '-' + month2] = d2;
                        self[self.type + 'View'].renderMarks();
                        self.MainView.renderMarks();
                    });
            } else {
                self.request({
                    url: URL + '/getSchedule?year=' + year + '&month=' + (month + 1),
                    type: 'GET'
                }).fail(function(c, e) {
                        self[self.type + 'View'].renderMarks();
                    }).done(function(d) {
                        self.ajaxOverlayer.hide();
                        d = d === null ? [] : d;
                        if(d.length != 0) {
                            $.each(d, function(k, v){
                                $.each(v, function(i, c){
                                    c.desc = c.content;
                                    c.time = c.datetime;
                                });
                                d[parseInt(k)] = v;
                                delete d[k];
                            });
                        }
                        self.marksStore[self.currentMarkId] = d;
                        self[self.type + 'View'].renderMarks();
                        self.MainView.renderMarks();
                    });
            }
        },

        //新建和更新日程: id==null为新建，有id值为更新
        update: function(id, time, desc, element){
            User.CheckLogin();
            var self = this;
            var url, data;
            if(id == 'null' || id == 0 ) {
                url = URL + '/addSchedule';
                data = {
                    time: time/1000,
                    desc: desc
                };
            }else{
                url = URL + '/updateSchedule';
                data = {
                    time: time/1000,
                    desc: desc,
                    id: id
                };
            }
            self.request({
                url: url,
                data: data
            }).fail(function(c, e){
                }).done(function(d){
                    if(id != 'null'){
                        element.attr('data-id', d);
                    }
                    self.marksStore = {};
                    self.loadMarks();
                });
        },
        //删除任务
        deleteMark: function(id){
            var self = this;
            self.request({
                url: URL + '/delSchedule',
                data: {
                    id: id
                }
            }).fail(function(c, e){
                }).done(function(d){
                    self.marksStore = {};
                    self.loadMarks();
                });
        }
    };

    Calendar.MainView = {
        Init: function() {
            var self = this;
            self.mainPanel = $('.cal_v303');
            self.monthPanelElement = $('.cal_v303_mini_cal');
            self.monthView = new MiniMonthView;
            self.listElement = $('.cal_v303_mark_list_wrap').find('ul');
            self.renderMonthView();
            self.listElement.on('click', '.cal_v303_mark_content', function(){
                if($(this).find('input').size()) return false;
                var c = $(this).attr('title');
                if(c == '来创建今天新的日程吧！'){
                    c = '';
                }
                var it = $('<input type="text" class="input-content" maxlength="30" />')
                it.val(c);
                $(this).html(it);
                it.select();
                $(this).siblings('.delete-mark').removeClass('delete-mark').addClass('enter-mark').html('确定');
            });

            //主视图点击空白区域增加事件
            self.listElement.on('click', function(e){
                e = $.event.fix(e);
                if(e.target == this){
                    $('.cal_v303_new_mark').trigger('click');
                    return false;
                }
            });

            self.listElement.on('click', '.enter-mark', function(){
                $(this).removeClass('enter-mark').addClass('delete-mark').html('×');
                var o = $(this).siblings('.cal_v303_mark_content');
                var c = o.find('.input-content').val() || Calendar.defaultMarkTitle;
                o.html(c).attr('title', c);
                var desc = c;
                var hm = o.siblings('.cal_v303_time_span').html();
                var ymd = $('.cal_v303_show_date').html().replace(/\D\b/g, '-').replace(/\D\B/g, '');
                var time = Date.parse(ymd + ' ' + hm);
                var id = $(this).parent('li').attr('data-id');
                self.format();
                Calendar.update(id, time, desc, $(this).parent('li'));
            });

            self.listElement.on('keydown', '.input-content', function(e){
                if(e.keyCode == 13){
                    var c = $(this).val() || Calendar.defaultMarkTitle;
                    var o = $(this).parent('.cal_v303_mark_content');
                    var li = $(this).closest('li');
                    var id = li.attr('data-id');
                    o.html(c).attr('title', c).siblings('.enter-mark').removeClass('.enter-mark').addClass('delete-mark').html('×');
                    var desc = c;
                    var hm = o.siblings('.cal_v303_time_span').html();
                    var ymd = $('.cal_v303_show_date').html().replace(/\D\b/g, '-').replace(/\D\B/g, '');
                    var time = Date.parse(ymd + ' ' + hm);
                    self.format();
                    Calendar.update(id, time, desc, li);
                    return false;
                }
            });

            self.listElement.on('click', '.cal_v303_time_enter', function(){
                var $self = $(this);

                var li = $self.parents('li');

                var h = Calendar.tooltip.find('.cal_v303_time_hour_select').val();
                var m = Calendar.tooltip.find('.cal_v303_time_minute_select').val();
                var o = Calendar.tooltip.parent('.cal_v303_time_span');

                o.html(h + ':' + m);

                var desc = li.find('.cal_v303_mark_content').attr('title');
                var hm = h + ':' + m;
                var ymd = $('.cal_v303_show_date').html().replace(/\D\b/g, '-').replace(/\D\B/g, '');
                var time = Date.parse(ymd + ' ' + hm);
                var id = li.attr('data-id');
                Calendar.update(id, time, desc, li);
                setTimeout(function(){
                    Calendar.tooltip.appendTo('body');
                    Calendar.tooltip.hide();
                },0)
            });

            self.listElement.on('click', '.cal_v303_time_span', function(){
                if($(this).find('select').size()){
                    return false;
                }
                var h = '';
                var tem;
                for(var i = 0; i < 24; i++){
                    tem = i < 10 ?  '0' + i : i;
                    h += '<option value="' + tem + '">' + tem + '</option>';
                }
                h = '<select class="cal_v303_time_hour_select">' + h + '</select>'
                var m = '<select class="cal_v303_time_minute_select">' +
                    '<option value="00">00</option>' +
                    '<option value="15">15</option>' +
                    '<option value="30">30</option>' +
                    '<option value="45">45</option>' +
                    '</select>';
                h = $(h);
                m = $(m);
                var cur = $(this).html().split(':');
                h.val(cur[0]);
                m.val(cur[1]);
                Calendar.tooltip.find('.content').empty()
                    .append(h).append(':').append(m)
                    .append('<a class="cal_v303_time_enter" href="javascript:;">确定</a>');
                Calendar.tooltip.appendTo($(this)).show();
            });

            self.listElement.on('click', '.delete-mark', function() {
                var li = $(this).parent('li');
                var id = li.attr('data-id');
                li.remove();
                Calendar.deleteMark(id);
            });

            $(document).on('click', '.cal_v303_new_mark', function(){
                var li = '<li data-id="null"><b></b><span class="cal_v303_time_span">00:00</span>'+
                    '<span class="cal_v303_mark_content">输入日程</span><a class="delete-mark" href="javascript:;">×</a></li>'
                li = $(li);
                if(self.listElement.find('li').size() == 4){
                    self.listElement.find('li:last').remove();
                }
                self.listElement.prepend(li);
                li.find('.cal_v303_mark_content').trigger('click');
            });
        },
        renderMonthView: function() {
            var self = this;
            self.monthView.render(self.monthPanelElement.find('tbody'));
            self.renderMarks();
        },
        format: function(){
            var self = this;
            self.listElement.find('li').each(function(k, v){
                var c = $(v).find('.cal_v303_mark_content');
                if(c.find('input').size()) return;
                var h = c.html().length > 15 ? c.html().substring(0, 15) + '...' : c.html();
                c.html(h);
            });
        },
        renderMarks: function() {
            var self = this;
            var tem = Calendar.marksStore[Calendar.currentYear + '-' + Calendar.currentMonth];
            if (tem && tem[Calendar.currentDate]) {
                var marks = self.marks = tem[Calendar.currentDate];
            } else {
                var marks = self.marks = [];
            }
            marks.sort(function(a, b){
                return a.time - b.time;
            });
            var lis = '';
            var v;
            for (var i = 0, len = marks.length < 4 ? marks.length: 4; i < len; i++) {
                v = marks[i];
                lis += '<li data-id="' + v.id + '"><b></b><span class="cal_v303_time_span">' +
                    (new Date(v.time*1000)).toString('HH:mm') + '</span><span class="cal_v303_mark_content" title="' + v.desc + '">' +
                    v.desc + '</span><a class="delete-mark" href="javascript:;">×</a></li>';
            }
            self.listElement.html(lis);
        }

    };

    Calendar.DateView = {
        Init: function() {
            var self = Calendar.DateView;
            self.mainPanel = $('.cal-day');
            self.miniMonthElement = $('.cal-mini-month-panel').find('tbody');
            self.marksListElement = $('.cal-day-note-list');
            self.burnDownElement = $('.cal-day-burn-down-chart');
            self.burnDownChartElement = self.burnDownElement.find('.chart');
            self.burnDownChartTimeElement = self.burnDownChartElement.find('.time-show');
            self.miniMonthView = new MiniMonthView;
            self.miniMonthElement.on('click', 'a', function() {
                var d = $(this).attr('data-date');
                Calendar.DateChangeFunc(d);
            });
            self.burnDownElement.find('.chart-body').dblclick(function(e) {
                var pos = e.pageY - $(this).offset().top;
                pos = pos - pos % 3;
                var time = + Date.today() + pos / 3 * 15 * 60 * 1000
                var mark = new MarkClass({
                    time: time / 1000,
                    desc: '',
                    id: 'null'
                });
                mark.appendTo($(this));
                var baseLine = self.burnDownChartElement.offset().top + self.burnDownChartElement.height();
                if (mark.html.offset().top + mark.html.height() <= baseLine) {
                    mark.html.addClass('cal-day-event-item-pass');
                }
                mark.html.find('.desc').trigger('click');
            });
        },
        renderBurnDownChart: function() {
            clearTimeout(Calendar.timer);
            var self = this;
            var op = Calendar.compare(Calendar.currentDateObject, Date.today());
            if (op != 0) {
                self.burnDownChartTimeElement.hide();
                if (op < 0) {
                    self.burnDownChartElement.height(self.burnDownElement.height()).show();
                    self.burnDownElement.find('.cal-day-event-item').addClass('cal-day-event-item-pass');
                } else {
                    self.burnDownChartElement.hide();
                }
            } else {
                var now = new Date();
                var pass = now - Date.today();
                var sec_px = 24 * 60 * 60 / self.burnDownElement.find('.chart-body').height();
                var height = pass / 1000 / sec_px + 39;
                self.burnDownChartElement.height(height).show();
                self.burnDownChartTimeElement.html(now.toString('HH:mm')).show();

                setTimeout(function(){
                    var baseLine = self.burnDownChartElement.offset().top + self.burnDownChartElement.height();
                    self.burnDownElement.find('.cal-day-event-item').each(function() {
                        if ($(this).offset().top + $(this).height() <= baseLine) {
                            $(this).addClass('cal-day-event-item-pass');
                        }
                    });
                }, 0);

                Calendar.timer = setTimeout(function() {
                        Calendar.DateView.renderBurnDownChart();
                    },
                    30000);
            }
        },
        renderMarks: function() {
            var self = this;
            var tem = Calendar.marksStore[Calendar.currentYear + '-' + Calendar.currentMonth];
            if (tem && tem[Calendar.currentDate]) {
                var marks = self.marks = tem[Calendar.currentDate];
            } else {
                var marks = self.marks = [];
            }
            var lis = '';
            var count = 0;
            var chartBody = self.burnDownElement.find('.chart-body');
            chartBody.empty();

            var len = marks.length > 5 ? 5 : marks.length;
            var mark;
            var v;
            for(var i = 0; i < len; i++){
                v = marks[i];
                lis += '<li>' + v.desc + '</li>';
                count++;
                mark = new MarkClass(v);
                mark.appendTo(chartBody);
            }
            self.marksListElement.find('.cal-day-ul').html(lis);
            self.marksListElement.find('.marks-count').html(count);
            self.renderBurnDownChart();
        }
    };
    Calendar.MonthView = {
        Init: function() {
            var self = this;
            self.mainPanel = $('.cal-month');
            self.tableElement = self.mainPanel.find('table');
            self.tableView = new MonthTable;
            self.tableElement.on('click', 'a', function() {
                var y = Calendar.currentYear;
                var m = Calendar.currentMonth;
                var d = $(this).attr('data-date');
                d = new Date(y, m, d);
                Calendar.setCurrentDate(d);
                Calendar.changeType('Date');
            });
        },
        renderBurnDownChart: function() {
            var self = this;
            var year = Calendar.currentYear;
            var month = Calendar.currentMonth;
            var todayDate = Calendar.today.getDate();

            if (year <= Calendar.today.getFullYear() && month < Calendar.today.getMonth()) {
                self.tableElement.find('td').addClass('month_pass_date');
            } else if (year == Calendar.today.getFullYear() && month == Calendar.today.getMonth()) {
                self.tableElement.find('td').each(function(k, v) {
                    var cur = $(v).find('a').attr('data-date');
                    if (cur != - 1 && cur < todayDate) {
                        $(v).addClass('month_pass_date');
                    } else if (cur == todayDate) {
                        self.todayElement = $(v);
                    }
                });
            }
        },
        renderMarks: function() {
            var self = this;
            var marks = Calendar.marksStore[Calendar.currentYear + '-' + Calendar.currentMonth];
            if (marks) {
                self.marks = marks;
            } else {
                self.marks = marks = {};
            }
            $.each(marks, function(k, v) {
                var cur = self.tableElement.find('.td_' + k).parent('td');
                $.each(v, function(i, n) {
                    var top = parseInt(i / 7) * 7 + 5;
                    var left = i % 7 * 7 + 5;
                    top = 'top:' + top + 'px;';
                    left = 'left:' + left + 'px;';
                    var style = top + left;
                    cur.append('<b class="month_task_dot" style="' + style + '"></b>');
                });
            });
            self.renderBurnDownChart();
        }
    };
    Calendar.WeekView = {
        Init: function() {
            var self = this;
            self.mainPanel = $('.cal-week');
            self.tableElement = self.mainPanel.find('table');
            self.tableElement.on('click', 'td', function() {
                var y = $(this).attr('data-year');
                var m = $(this).attr('data-month');
                var d = $(this).attr('data-date');
                Calendar.setCurrentDate(new Date(y, m, d));
                Calendar.changeType('Date');
            });
        },
        renderBurnDownChart: function() {
            clearTimeout(Calendar.timer);

            var self = this;
            var start = Calendar.currentWeekStartObject;
            var today = Calendar.today;
            self.burnDownChartElement = $('<div class="chart"><h5 class="time-show">00:00</h5></div>');
            var todayIndex = today.getDay() - Calendar.weekStart;
            if(today.getDay() == 0) todayIndex = 6;
            var today_td = self.tableElement.find('td:eq(' + todayIndex + ')');
            self.burnDownChartElement.appendTo(today_td);
            var now = new Date();
            var pass = now - Calendar.today;
            var sec_px = 24 * 60 * 60 / (today_td.height() || 293) - 6;
            var height = pass / 1000 / sec_px - 6;
            self.burnDownChartElement.height(height).show();
            self.burnDownChartElement.find('.time-show').html(now.toString('HH:mm'));
            //var baseLine = self.burnDownChartElement.offset().top + self.burnDownChartElement.height();
            var baseLine =  self.burnDownChartElement.height();
            setTimeout(function(){
                today_td.find('.week-mark-line').each(function() {
                    if (parseInt($(this).css('top')) + $(this).height() <= baseLine) {
                        $(this).addClass('week-mark-line-pass');
                    }
                });
            },0);
            Calendar.timer = setTimeout(function() {
                    Calendar.DateView.renderBurnDownChart();
                },
                30000);
        },
        renderMarks: function() {
            var self = this;
            var start = Calendar.currentWeekStartObject;
            var end = Calendar.currentWeekEndObject;
            var today = Calendar.today;
            var year, month, date, marks, tem, td, op, i, line, time, desc, hour, minute;
            self.tableElement.find('td').empty();
            var burndown = false;

            for (i = 0; i <= 6; i++) {
                tem = start.add_day(i);
                year = tem.getFullYear();
                month = tem.getMonth();
                date = tem.getDate();
                if (Calendar.marksStore[year + '-' + month] && Calendar.marksStore[year + '-' + month][date]) {
                    marks = Calendar.marksStore[year + '-' + month][date];
                } else {
                    marks = [];
                }
                td = self.tableElement.find('td:eq(' + i + ')');
                td.attr({
                    'data-date': date,
                    'data-month': month,
                    'data-year': year
                });
                op = Calendar.compare(tem, today);
                td.removeClass('month_pass_date');
                if (op < 0) td.addClass('month_pass_date');
                if (op == 0) burndown = true;
                $.each(marks, function(k, v) {
                    line = $('<div class="week-mark-line"><b></b><span></span></div>');
                    time = new Date(v.time * 1000);
                    desc = v.desc;
                    hour = time.getHours();
                    minute = time.getMinutes();
                    line.css('top', hour * 4 * 3 + minute / 15 * 3);
                    td.append(line);
                });
            }
            if (burndown) {
                self.renderBurnDownChart();
            }
        }
    };
    //月级视图 - 基础
    var MonthView = new Class;
    MonthView.include({
        init: function() {
            this.weekStart = Calendar.weekStart;
            this.year = null;
            this.month = null;
        },
        initBaseArray: function(y, m) {
            var self = this;
            self.year = y;
            self.month = m;
            var weekStart = self.weekStart;
            var len = Calendar.howManyDaysInMonth(y, m);
            var startWeekDay = Calendar.posInWeek(y, m, 1);
            var arr = [];
            var i;
            for (i = 1; i <= len; i++) {
                arr.push(i);
            }
            for (i = 1, len = startWeekDay - weekStart; i <= len; i++) {
                arr.unshift(0);
            }
            len = arr.length % 7;
            if (!len == 0) {
                len = 7 - len;
                for (i = 1; i <= len; i++) {
                    arr.push( - 1);
                }
            }
            self.baseArray = arr;
        },
        buildTable: function(date) {
            var self = this;
            if (self.year !== Calendar.currentYear || self.month !== Calendar.currentMonth) {
                self.initBaseArray(Calendar.currentYear, Calendar.currentMonth);
            }
            var baseArray = self.baseArray;
            var o;
            var table = '';
            for (var i = 0, len = baseArray.length; i < len; i++) {
                o = baseArray[i];
                if (o == 0 || o == - 1) {
                    o = '<td class="op-td-'+i%7+'"><a data-date="' + o + '" style="display:none"></a></td>';
                } else {
                    o = '<td class="op-td-'+i%7+'"><a class="td_' + o + '" href="javascript:;" data-date="' + o + '">' + o + '</a></td>';
                }
                if ((i + 1) % 7 == 0) {
                    o += '</tr>'
                }
                if (i % 7 == 0) {
                    o = '<tr>' + o;
                }
                table += o;
            }
            return table;
        }
    });
    //日级视图的月历
    var MiniMonthView = new Class(MonthView);
    MiniMonthView.include({
        render: function(wrap) {
            var self = this;
            wrap = wrap || Calendar.DateView.miniMonthElement;
            var table = self.buildTable();
            if (wrap instanceof $) {
                wrap.html(table);
            } else {
                $(wrap).html(table);
            }
            wrap.find('.td_' + Calendar.currentDate).addClass('active');
        }
    });
    //月级视图的月历
    var MonthTable = new Class(MonthView);
    MonthTable.include({
        render: function(wrap) {
            var self = this;
            wrap = wrap || Calendar.MonthView.tableElement.find('tbody');
            var table = self.buildTable();
            if (wrap instanceof $) {
                wrap.html(table);
            } else {
                $(wrap).html(table);
            }
            wrap.find('.td_' + Calendar.currentDate).addClass('active');
        }
    });
    //事件
    var MarkClass = new Class;
    MarkClass.include({
        init: function(m) {
            var self = this;
            var time = new Date(m.time * 1000);
            var desc = m.desc;
            var year = time.getFullYear();
            var month = time.getMonth();
            var date = time.getDate();
            var hour = time.getHours();
            var minute = time.getMinutes();
            self.id = m.id;
            self.date = date;
            self.time = time;
            self.desc = desc;
            self.baseTime = + new Date(year, month, date, 0);
            var showtime = time.toString('HH:mm');
            var html = '<div class="cal-day-event-item">' + '<b class="dot"></b>' + '<span class="time">' + showtime + '</span>' + '<span class="desc">' + desc + '</span>' + '<a class="enter" href="javascript:;">确定</a>' + '<a class="delete" href="javascript:;">×</a>' + '</div>';

            self.html = $(html).css('top', hour * 4 * 3 + minute / 15 * 3);
            self.html.find('.desc').click(function() {
                if($(this).find('input').size()){
                    return false;
                }
                var desc = $(this).html();
                $(this).html('<input class="input_desc" type="text" maxlength="30" value="' + desc + '" >');
                self.html.find('.input_desc').select();
                self.html.find('.delete').hide();
                self.html.find('.enter').show();
                return false;
            });
            self.html.on('keydown', '.input_desc', function(e){
                if(e.keyCode == 13){
                    var d = $(this).closest('.desc')
                    var v = $(this).val() || Calendar.defaultMarkTitle;
                    d.html(v);
                    self.desc = v;
                    $(this).hide();
                    self.html.find('.show').hide();
                    Calendar.update(self.id, self.time, self.desc, self.html);
                }
            });
            self.html.find('.enter').click(function() {
                var d = $(this).siblings('.desc')
                var v = d.find('.input_desc').val() || Calendar.defaultMarkTitle;
                d.html(v);
                self.desc = v;
                $(this).hide();
                self.html.find('.show').hide();
                Calendar.update(self.id, self.time, self.desc, self.html);
            });
            self.html.find('.delete').click(function() {
                Calendar.deleteMark(self.id);
                self.html.remove();
            });
        },
        removeElement: function() {
            this.html.remove();
            Calendar.deleteMark(this.id)
        },
        appendTo: function(el) {
            var self = this;
            self.html.appendTo(el);
            self.html.draggable({
                axis: 'y',
                grid: [100, 3],
                containment: 'parent',
                start: function(event, ui) {},
                drag: function(event, ui) {
                    var top = ui.position.top;
                    var time = top / 3 * 15 * 60 * 1000 + self.baseTime;
                    time = new Date(time);
                    if (time.getDate() != self.date) {
                        time = '24:00';
                    } else {
                        time = time.toString('HH:mm');
                    }
                    self.time = Date.parse(time);
                    $(this).find('.time').html(time);
                    baseLine = Calendar.DateView.burnDownChartElement.offset().top + Calendar.DateView.burnDownChartElement.height();
                    if ($(this).offset().top + $(this).height() <= baseLine) {
                        $(this).addClass('cal-day-event-item-pass');
                    } else {
                        $(this).removeClass('cal-day-event-item-pass');
                    }
                },
                stop: function(event, ui) {
                    Calendar.update(self.id, self.time, self.desc, self.html);
                }
            });
        }
    });
    window.Calendar = Calendar;
})();

