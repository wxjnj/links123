//检查必要库
(function(){
    document.write('<link media="screen" href="http://a.links123.cn/v303b/linksMusicBox/links-musicbox.css" type="text/css" rel="stylesheet" >');
    window.jQuery || document.write('<script src="http://a.links123.cn/v303b/js/jquery-1.7.1.min.js"></script>');
    window.jQuery.fn.draggable || document.write('<script src="http://a.links123.cn/v303b/js/jquery.plugins.js"></script>');
    //window.jQuery.fn.cookies || document.write('<script src="http://a.links123.cn/v303b/js/jquery.cookies.2.2.0.min.js"></script>');
})();


$(function(){

    /* 用户配置参数，稍后开放（参数参见MusicBox.config）
    window.linksMusicBoxConfig = {};
    */

    var tpl = '<div id="J_box_music"class="normal_music_box"style="display: none">\
        <div class="mini_music_channel_list">\
            <div class="mini_music_channel_select">\
                <div class="mini_current_channel"></div>\
                <i class="mini_music_ang"></i>\
                <div class="mini_channel_list"></div>\
            </div>\
            <div class="music-close-tip">听烦了</div>\
            <a class="mini_music_box_close_btn"href="javascript:;">×</a>\
            <a class="mini_music_box_play_btn mini_music_box_play_btn_pause"href="javascript:;"></a>\
            <a class="mini_music_box_size_btn size_mini"href="javascript:;"data-size="mini">小</a>\
            <a class="mini_music_box_size_btn size_normal"href="javascript:;"data-size="normal">中</a>\
            <a class="mini_music_box_size_btn size_fullscreen"href="javascript:;"data-size="fullscreen">大</a>\
            <a href="http://www.links123.cn/" target="_blank" style="position: absolute;right: 20px;line-height: 32px;">links123.cn</a>\
        </div>\
        <div class="normal_music_iframe_box">\
            <iframe id="K_303_music_iframe"allowtransparency="true"frameborder="0"style=""></iframe>\
            <div class="normal_music_iframe_box_pause_status">\
                <a href="javascript:;"></a>\
                <p>点击继续听...</p>\
            </div>\
        </div>\
        <div class="normal_music_channel_list">\
            <div class="normal_music_channel_list_toggle">\
                <span class="list-open"></span>\
            </div>\
            <div class="music-close-tip">听烦了</div>\
            <a class="normal_music_box_close_btn"href="javascript:;">×</a>\
            <a class="normal_music_box_play_btn"href="javascript:;"></a>\
            <!--a href="javascript:;"class="normal_music_box_toggle_btn">展开</a-->\
            <ul></ul>\
            <a class="normal_music_box_size_btn size_nimi"href="javascript:;"data-size="mini">小</a>\
            <a class="normal_music_box_size_btn size_normal"href="javascript:;"data-size="normal">中</a>\
            <a class="normal_music_box_size_btn size_fullscreen"href="javascript:;"data-size="fullscreen">大</a>\
        </div>\
    </div>';
    
    var MusicBox = {
        Init: function(){
            var self = this;
            $('body').append(tpl);

            self.config = {
                view: 'mini',   //初始视图
                autoPlay: false     //初始是否自动播放
            };

            $.extend(self.config, window.linksMusicBoxConfig);

            self.music_channel_list = [
                {
                    id: 'douban',
                    name: '豆瓣FM',
                    url: 'http://douban.fm/partner/playerhao123'
                },{
                    id: 'xiami',
                    name: '虾米音乐',
                    url: 'http://www.xiami.com/kuang/hao123/'
                },{
                    id: 'baidu',
                    name: '百度随心听',
                    url: 'http://fm.baidu.com/?embed=hao123"'
                },{
                    id: 'duole',
                    name: '多乐音乐',
                    url: 'http://www.duole.com/application/qihu360'
                },{
                    id: 'kugou',
                    name: '酷狗音乐',
                    url: 'http://web.kugou.com/default.html'
                },{
                    id: 'duomi',
                    name: '多米音乐',
                    url: 'http://app.duomiyy.com/webradio/hao123/'
                },{
                    id: 'kuwo',
                    name: '酷我音乐',
                    url: 'http://player.kuwo.cn/webmusic/web/play'
                },{
                    id: 'beiwa',
                    name: '贝瓦儿歌',
                    url: 'http://app.beva.com/360/fm'
                },{
                    id: 'yinyuetai',
                    name: '音悦台',
                    url: 'http://www.yinyuetai.com/baidu/hao123'
                },{
                    id: 'st',
                    name: 'SongTest',
                    url: 'http://www.songtaste.com/radio.php'
                }
            ];

            self.changeMode(self.config.view, true);
            var lis = '';
            var divs = '';
            $.each(self.music_channel_list, function(k, v){
                lis += '<li><a class="normal_music_channel_btn normal_music_channel_' + v.id + '" href="javascript:;" data-url="' + v.url + '" data-channel="' + v.id + '"></a></li>'
                divs += '<div class="mini_music_channel_btn mini_music_channel_' + v.id + '" data-url="' + v.url + '" data-channel="' + v.id + '"><b></b><span>' + v.name + '</span></div>';
            });
            $('.normal_music_channel_list').find('ul').html(lis);
            $('.mini_channel_list').html(divs);

            $('#J_box_music').show();

            //默认播放第一个频道
            //if($.cookies.get('music_box_v303_close') != 1){
                //$('.music-close-tip').show();   // 关闭提示
            //    self.play();
            //    self.pause();
            //}else{
                self.play();
                self.pause();
            //}

            //绑定事件
            $('.normal_music_channel_list').on('click', '.normal_music_channel_btn', function(){
                var id = $(this).attr('data-channel');
                var url = $(this).attr('data-url');
                self.play(id, url);
            });
            $('.mini_channel_list').on('click', '.mini_music_channel_btn', function(){
                var id = $(this).attr('data-channel');
                var url = $(this).attr('data-url');
                self.play(id, url);
                return false;
            });

            $('#J_box_music').draggable({handle: '.mini_music_channel_list'});

            $('.normal_music_box_size_btn, .mini_music_box_size_btn').click(function(){
                var mode = $(this).attr('data-size');
                self.changeMode(mode);
            });

            $('.mini_channel_list').on('mouseover', '.mini_music_channel_btn', function(){
                $('.mini_music_channel_btn').removeClass('hover_class');
                $(this).addClass('hover_class');
            }).on('mouseout', '.mini_music_channel_btn', function(){
                $(this).removeClass('hover_class');
            })

            $('.mini_music_channel_select').mouseover(function(){
                if(!$('.mini_channel_list').is(':hidden')) return;
                var h = $('.mini_channel_list').height();
                var bt = $('.mini_music_box').css('top');
                var bh = $('.mini_music_box').height();
                var wh = $(window).height();
                bt = bt == 'auto' ? wh - bh : parseInt(bt);
                if(wh - bt - bh <= h){
                    $('.mini_channel_list').css('margin-top', - (h + bh - 8) + 'px');
                }else{
                    $('.mini_channel_list').css('margin-top', '0');
                }
                $('.mini_channel_list').show();
            }).mouseout(function(){
                $('.mini_music_channel_btn').removeClass('hover_class');
                $('.mini_channel_list').hide();
            });

            $('.normal_music_box_close_btn, .mini_music_box_close_btn, .music-close-tip').click(function(){
                self.close();
            });

            $('.normal_music_channel_list_toggle').click(function(){
                self.changeNormalPosition();
            });

            $(document).on('keydown', function(e){
                if (e.keyCode == 27) {
                    var mode1 = $('.active-size').hasClass('size_fullscreen');
                    var mode2 = $('.active-size').hasClass('size_normal');
                    if(mode1){
                        self.changeMode('normal');
                    }else if(mode2 && parseInt($('#J_box_music').css('left')) == 0){
                        self.changeNormalPosition();
                    }
                }
            });

            $('.normal_music_box_play_btn').click(function(){
                if($(this).hasClass('normal_music_box_play_btn_play')){
                    self.play();
                }else{
                    self.pause();
                }
            });
            $('.mini_music_box_play_btn').click(function(){
                if($(this).hasClass('mini_music_box_play_btn_play')){
                    self.play();
                }else{
                    self.pause();
                }
            });

            $('.normal_music_iframe_box_pause_status a').click(function(){
                self.play();
            });

            $('.mini_current_channel').click(function(){
                if(!$('#K_303_music_iframe').attr('src')){
                    self.play();
                }
            });

            if(self.config.autoPlay){
                self.play();
            }

        },
        close: function(){
            $('#J_box_music').hide();
            $('#K_303_music_iframe').attr('src', '');
            //$.cookies.set('music_box_v303_close', '1', { expiresAt: (new Date).add_day(365) });

        },
        pause: function(){
            $('.normal_music_box_play_btn').removeClass('normal_music_box_play_btn_pause').addClass('normal_music_box_play_btn_play');
            $('.mini_music_box_play_btn').removeClass('mini_music_box_play_btn_pause').addClass('mini_music_box_play_btn_play');
            
            $('#K_303_music_iframe').attr('src', '').hide();
            $('.normal_music_iframe_box').addClass('normal_music_iframe_box_pause');

        },
        play: function(id, url){
            var self = this;
            if(arguments.length == 0){
                var cur = $('.normal_music_channel_list').find('.active');
                if(cur.size()){
                    id = cur.find('a').attr('data-channel');
                    url = cur.find('a').attr('data-url');
                }else{
                    var defaultChannel = self.music_channel_list[0]
                    id = defaultChannel.id;
                    url = defaultChannel.url;
                }
            }
            $('.normal_music_iframe_box').removeClass('normal_music_iframe_box_pause');
            $('.normal_music_box_play_btn').removeClass('normal_music_box_play_btn_play').addClass('normal_music_box_play_btn_pause');
            $('.mini_music_box_play_btn').removeClass('mini_music_box_play_btn_play').addClass('mini_music_box_play_btn_pause');

            $('.normal_music_channel_list').find('li').removeClass('active');
            $('.normal_music_channel_' + id).closest('li').addClass('active');
            $('#K_303_music_iframe').attr('src', url).show();
            $('.mini_current_channel').find('.mini_music_channel_btn').appendTo('.mini_channel_list');
            $('.mini_music_channel_' + id).appendTo('.mini_current_channel');
        },
        changeMode: function(mode, isFirst, iconClick){
            var self = this;
            if(!isFirst && parseInt($('#J_box_music').css('left')) != 0){
                self.changeNormalPosition();
            }

            var t = $(window).scrollTop();
            var h = $(window).height() / 2;

            $('#J_box_music').attr('class', '').addClass(mode +'_music_box');

            if(mode == 'normal'){
                if($(window).height() < 560){
                    $('.normal_music_box').css({
                        'bottom': 'auto',
                        'top' : '0',
                        'position' : 'absolute'
                    });
                }else{
                    $('.normal_music_box').css({
                        'bottom': 0, //-t, // 这里fixed了，bottom为负值，啥意思？
                        'top' : 'auto',
                        'position' : 'fixed'
                    });
                }
            }
            if(mode == 'mini'){
                $('.mini_music_box').css({
                    'bottom' : 0,// '80px',//-t + h,
                    'top' : 'auto'
                });
            }
            if(mode == 'fullscreen'){
                $('.fullscreen_music_box').css({
                    bottom:0,
                    top:0,
                    left:0,
                    right:0
                });
                var ww = $('.normal_music_iframe_box').width();
                var hh = $('.normal_music_iframe_box').height();
                $('#K_303_music_iframe').width(ww - 40).height(hh - 40);
            }else{
                $('#K_303_music_iframe').width(749).height(508);
            }

            $('.normal_music_box_size_btn, .mini_music_box_size_btn').removeClass('active-size');
            $('.size_' + mode).addClass('active-size');
        },
        changeNormalPosition: function(){
            var self = this;
            var mode = $('.active-size').hasClass('size_fullscreen');
            if(mode){
                self.changeMode('normal');
            }

            var st = $('#J_box_music').css('left');
            var tit, pos;

            st = parseInt(st);
            if(st != 0){
                tit = '收起';
                pos = '0';
                $('.normal_music_channel_list_toggle').find('span').removeAttr('class').addClass('list-close');
                if(!$('#K_303_music_iframe').attr('src')){
                    self.play();
                }
            }else{
                tit = '展开';
                pos = '-789px';
                $('.normal_music_channel_list_toggle').find('span').removeAttr('class').addClass('list-open');
            }
            $('#J_box_music').animate({'left': pos});
            $('.normal_music_box_toggle_btn').html(tit);
        }
    };

    MusicBox.Init();
    

});