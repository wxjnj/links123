var Zld = { // 自留地
    IsSortable: false, //是否为拖拽点击，true则不打开自留地网址
    Resize: function(){
        //自适应算法
        var box = $('#J_ZldList');
        var boxWidth = box.width();
        var lis = box.find('li:not(.add)');
        lis.css('width', 'auto');
        var liWidth = 0;
        var overIndex = null;
        var fstLineWidth = null;
        //var o;
        $.each(lis, function(k, v){
            liWidth += $(v).width() + 4;
            if(!overIndex && liWidth > boxWidth){
                overIndex = k;
                fstLineWidth = liWidth - $(v).width() - 4;
            }
        });
        if(liWidth <= boxWidth) return;

        if(boxWidth - fstLineWidth > 40){
            var w = lis.eq(overIndex).width() + 4 - (boxWidth - fstLineWidth);
            w = ~~Math.ceil(w / (overIndex + 1) / 2);
            var s = lis.filter(':lt(' + (overIndex+1) +')');
            $.each(s, function(k, v){
	            var ow = $(v).width();
	            var nm = $(v).find('.nm');
	            var pl = nm.css('padding-left');
	            var pr = nm.css('padding-right');
	            nm.css({
		            'padding-left': parseInt(pl) - w + 'px',
		            'padding-right': parseInt(pr) - w + 'px'
	            });
            });
        }else if(boxWidth - fstLineWidth <= 40 && boxWidth - fstLineWidth > 10){
	        var w = boxWidth - fstLineWidth + 4;//lis.eq(overIndex).width() + 4 - (boxWidth - fstLineWidth);
	        w = ~~Math.floor(w / (overIndex-1) / 2);
	        var s = lis.filter(':lt(' + overIndex +')');
	        $.each(s, function(k, v){
		        var ow = $(v).width();
		        //$(v).css('width', ow + w + 'px');
		        var nm = $(v).find('.nm');
		        var pl = nm.css('padding-left');
		        var pr = nm.css('padding-right');
		        nm.css({
			        'padding-left': parseInt(pl) + w + 'px',
			        'padding-right': parseInt(pr) + w + 'px'
		        });
	        });
        }
    },
    Init: function(){
        var self = this;
        var obj = $('#J_ZldList');
        self.Resize();
        $(document).on('click', '#J_ZldList .add', function(){
            if(User.CheckLogin()){
                self.Create();
            }
        });

        $(document).on('click', '.K_link_it .nm', function(){
            if (!Zld.IsSortable) {
                var o = $(this).closest('li');
                var url = o.data('url');
                self.Go(url);
            } else {
                Zld.IsSortable = false;
            }
            return false;
        });

	    /*
        $(document).on('mouseenter', '.K_link_it .nm', function(){
            var li = $(this).closest('li');
            var panel = $('.link-custom-panel');
			clearTimeout(self.timer)
	        self.timer = null;
	        panel.hide().appendTo(li);
	        self.timer = setTimeout(function(){
		        panel.fadeIn(100);
	        }, 400);
	        return false;
        }).on('mouseleave', '.K_link_it', function(){
			clearTimeout(self.timer);
			self.timer = null;
		    self.timer = setTimeout(function(){
			    $('.link-custom-panel').fadeOut(50);
		    }, 300);
			return false;
        });
        */
        $(document).on('click',  '.cs-btn', function(e){
            if(User.CheckLogin()){
                if($(this).hasClass('add')){ return false; }
                var o = $(this).closest('li');
                var id = o.data('id');
                var nm = o.find('.nm').html();
                var url = o.data('url');
                self.Create(id, nm, url);
                return false;
            }
	        return false;
        });

        $('#J_ZldList').sortable({
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
                self.Resize();
                Zld.IsSortable = false;
                $(ui.item).find('span').css('cursor', 'pointer');
            }
        });
        $('#J_sortable').sortable('enable');

	    $(document).on('click', '.radio-li', function(){
		    $('.radio-li').removeClass('checked');
		    $(this).addClass('checked');
	    });
        
        
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
	                    self.Resize();
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
        
        $(document).on('click', '#J_Zld .lkd-del, .link-remove, .zld-remove-btn', function(){

	        if($('#J_Zld').size()){
		        var o = $('#J_Zld');
		        var id = o.find('input[name="id"]').val();
	        }else{
		        var o = $(this).closest('li');
		        var id = o.data('id');
	        }

            $.post(
                URL + '/delArea', 
                { 'web_id': id },
                function(data) {
                    var licur = function(){
                        var li = null;
                        $('.K_link_it').each(function(k, v){
                            if($(v).data('id') == id){
                                li = $(v);
                                return;
                            }
                        });
                        return li;
                    }
                    if(data == 1){
                        var li = licur();
                        li.remove();
	                    self.Resize();
                        o.dialog('close');
                    }else if(data == -1){
                        User.Login('请先登录');
                    }else{
                        alert('操作失败');
                    }
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
	    var obj = $('#J_Zld');

		obj.dialog({
			autoOpen: false,
			modal: true,
			resizable: false,
			width: 440,
			show: {
				effect: "clip",
				duration: 150
			},
			hide: {
				effect: "clip",
				duration: 150
			},
			open: function(){
				setTimeout(function(){obj.find('input[name="name"]').select();}, 20);
			}
		});

		obj.find('.close-btn').on('click', function(){
			obj.dialog('close');
			return false;
		});

		obj.find('input[name="name"],input[name="url"]').on('mouseover', function(){
			$(this).focus().select();
		});

		obj.find('input[name="name"],input[name="url"]').on('keydown', function(event){
			if (event.keyCode == 13) {
				if(!self.etimestamp){
					self.etimestamp = event.timeStamp;
				}else{
					if(self.etimestamp == event.timeStamp){
						return false;
					}
					self.etimestamp = event.timeStamp
				}
				//if(obj.find('.editp').is(":visible")){
				//	obj.find('.lkd-edit').trigger('click');
				//}else{
					obj.find('.lkd-add').trigger('click');
				//}
				return false;
			}
		});
        if(id){
            obj.find('input[name="id"]').val(id);
            obj.find('input[name="name"]').val(nm);
            obj.find('input[name="url"]').val(url);
	        obj.find('.zld-remove-btn').show();
            //obj.find('.editp').show();
            //obj.find('.addp').hide();
        }else{
	        obj.find('.zld-remove-btn').hide();
            obj.find('input[name="id"]').val('');
            obj.find('input[name="name"]').val('');
            obj.find('input[name="url"]').val('');
            //obj.find('.editp').hide();
            //obj.find('.addp').show();
        }
        obj.dialog('open');
    },
    CreateItem: function(id, nm, url){
        var hl = '<li class="K_link_it" id="'+ id +'" url="/Link/index.html?mod=myarea&amp;url='+ url +'" data-id="'+ id +'" data-url="'+ url +'">';
        hl = hl + '<b class="nm">'+ nm +'</b><span class="cs-btn"></span>';
        hl = hl + '</li>';
        return hl;
    }
};