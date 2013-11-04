var APP = $CONFIG['APP'];
var URL = $CONFIG['URL'];
var PUBLIC = $CONFIG['PUBLIC'];

$(function() {

	Zld.Init();
	ZhiDaLan.Init();
	//MusicPlayer.Init();
	HelpMouse.init();
    Calendar.Init()

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

	$('#J_Apps').sortable();

	// 幻灯
    /*
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
     */

	// 发现
    /*
	$('#J_Find .find li').on('hover', function() {
		$(this).toggleClass('hover');
	});
	*/

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

		//window.sladePlugin.update();
		Zld.Resize();
        Calendar.Init();
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
        //lis.css('width', 'auto');
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
                //var ow = $(v).width();
                var xw = ~~Math.ceil(w/2);
                var opl = $(v).find('.nm').css('padding-left');
                var opr = $(v).find('.nm').css('padding-right');
                opl = parseInt(opl);
                opr = parseInt(opr);
                $(v).find('.nm').css({
                    'padding-left': opl - xw + 'px',
                    'padding-right': opr - xw + 'px'
                });
            });
        } else if(boxWidth - fstLineWidth <= 45 && boxWidth - fstLineWidth > 10) { 
            // 差距过小，使用本行增加宽度适应行宽
            var w = boxWidth - fstLineWidth + 5;
            w = ~~Math.floor(w / (overIndex-1) / 2);
            var s = lis.filter(':lt(' + overIndex + ')');

            $.each(s, function(k, v){
                var opl = $(v).find('.nm').css('padding-left');
                var opr = $(v).find('.nm').css('padding-right');
                opl = parseInt(opl);
                opr = parseInt(opr);
                $(v).find('.nm').css({
                    'padding-left': opl + xw + 'px',
                    'padding-right': opr + xw + 'px'
                });
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

        /* 鼠标经过缩放*/
         /*
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
        */
         /**/
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
