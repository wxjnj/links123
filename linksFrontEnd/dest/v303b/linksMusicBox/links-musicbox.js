!function(){document.write('<link media="screen" href="http://a.links123.net/v303b/linksMusicBox/links-musicbox.css" type="text/css" rel="stylesheet" >'),window.jQuery||document.write('<script src="http://a.links123.net/v303b/js/jquery-1.7.1.min.js"></script>'),window.jQuery.fn.draggable||document.write('<script src="http://a.links123.net/v303b/js/jquery.plugins.js"></script>')}(),$(function(){var a='<div id="J_box_music"class="normal_music_box"style="display: none">        <div class="mini_music_channel_list">            <div class="mini_music_channel_select">                <div class="mini_current_channel"></div>                <i class="mini_music_ang"></i>                <div class="mini_channel_list"></div>            </div>            <div class="music-close-tip">听烦了</div>            <a class="mini_music_box_close_btn"href="javascript:;">×</a>            <a class="mini_music_box_play_btn mini_music_box_play_btn_pause"href="javascript:;"></a>            <a class="mini_music_box_size_btn size_mini"href="javascript:;"data-size="mini">小</a>            <a class="mini_music_box_size_btn size_normal"href="javascript:;"data-size="normal">中</a>            <a class="mini_music_box_size_btn size_fullscreen"href="javascript:;"data-size="fullscreen">大</a>            <a href="http://www.links123.cn/" target="_blank" style="position: absolute;right: 20px;line-height: 32px;">links123.cn</a>        </div>        <div class="normal_music_iframe_box">            <iframe id="K_303_music_iframe"allowtransparency="true"frameborder="0"style=""></iframe>            <div class="normal_music_iframe_box_pause_status">                <a href="javascript:;"></a>                <p>点击继续听...</p>            </div>        </div>        <div class="normal_music_channel_list">            <div class="normal_music_channel_list_toggle">                <span class="list-open"></span>            </div>            <div class="music-close-tip">听烦了</div>            <a class="normal_music_box_close_btn"href="javascript:;">×</a>            <a class="normal_music_box_play_btn"href="javascript:;"></a>            <!--a href="javascript:;"class="normal_music_box_toggle_btn">展开</a-->            <ul></ul>            <a class="normal_music_box_size_btn size_nimi"href="javascript:;"data-size="mini">小</a>            <a class="normal_music_box_size_btn size_normal"href="javascript:;"data-size="normal">中</a>            <a class="normal_music_box_size_btn size_fullscreen"href="javascript:;"data-size="fullscreen">大</a>        </div>    </div>',b={Init:function(){var b=this;$("body").append(a),b.config={view:"mini",autoPlay:!1},$.extend(b.config,window.linksMusicBoxConfig),b.music_channel_list=[{id:"douban",name:"豆瓣FM",url:"http://douban.fm/partner/playerhao123"},{id:"xiami",name:"虾米音乐",url:"http://www.xiami.com/kuang/hao123/"},{id:"baidu",name:"百度随心听",url:'http://fm.baidu.com/?embed=hao123"'},{id:"duole",name:"多乐音乐",url:"http://www.duole.com/application/qihu360"},{id:"kugou",name:"酷狗音乐",url:"http://web.kugou.com/default.html"},{id:"duomi",name:"多米音乐",url:"http://app.duomiyy.com/webradio/hao123/"},{id:"kuwo",name:"酷我音乐",url:"http://player.kuwo.cn/webmusic/web/play"},{id:"beiwa",name:"贝瓦儿歌",url:"http://app.beva.com/360/fm"},{id:"yinyuetai",name:"音悦台",url:"http://www.yinyuetai.com/baidu/hao123"},{id:"st",name:"SongTest",url:"http://www.songtaste.com/radio.php"}],b.changeMode(b.config.view,!0);var c="",d="";$.each(b.music_channel_list,function(a,b){c+='<li><a class="normal_music_channel_btn normal_music_channel_'+b.id+'" href="javascript:;" data-url="'+b.url+'" data-channel="'+b.id+'"></a></li>',d+='<div class="mini_music_channel_btn mini_music_channel_'+b.id+'" data-url="'+b.url+'" data-channel="'+b.id+'"><b></b><span>'+b.name+"</span></div>"}),$(".normal_music_channel_list").find("ul").html(c),$(".mini_channel_list").html(d),$("#J_box_music").show(),b.play(),b.pause(),$(".normal_music_channel_list").on("click",".normal_music_channel_btn",function(){var a=$(this).attr("data-channel"),c=$(this).attr("data-url");b.play(a,c)}),$(".mini_channel_list").on("click",".mini_music_channel_btn",function(){var a=$(this).attr("data-channel"),c=$(this).attr("data-url");return b.play(a,c),!1}),$("#J_box_music").draggable({handle:".mini_music_channel_list"}),$(".normal_music_box_size_btn, .mini_music_box_size_btn").click(function(){var a=$(this).attr("data-size");b.changeMode(a)}),$(".mini_channel_list").on("mouseover",".mini_music_channel_btn",function(){$(".mini_music_channel_btn").removeClass("hover_class"),$(this).addClass("hover_class")}).on("mouseout",".mini_music_channel_btn",function(){$(this).removeClass("hover_class")}),$(".mini_music_channel_select").mouseover(function(){if($(".mini_channel_list").is(":hidden")){var a=$(".mini_channel_list").height(),b=$(".mini_music_box").css("top"),c=$(".mini_music_box").height(),d=$(window).height();b="auto"==b?d-c:parseInt(b),a>=d-b-c?$(".mini_channel_list").css("margin-top",-(a+c-8)+"px"):$(".mini_channel_list").css("margin-top","0"),$(".mini_channel_list").show()}}).mouseout(function(){$(".mini_music_channel_btn").removeClass("hover_class"),$(".mini_channel_list").hide()}),$(".normal_music_box_close_btn, .mini_music_box_close_btn, .music-close-tip").click(function(){b.close()}),$(".normal_music_channel_list_toggle").click(function(){b.changeNormalPosition()}),$(document).on("keydown",function(a){if(27==a.keyCode){var c=$(".active-size").hasClass("size_fullscreen"),d=$(".active-size").hasClass("size_normal");c?b.changeMode("normal"):d&&0==parseInt($("#J_box_music").css("left"))&&b.changeNormalPosition()}}),$(".normal_music_box_play_btn").click(function(){$(this).hasClass("normal_music_box_play_btn_play")?b.play():b.pause()}),$(".mini_music_box_play_btn").click(function(){$(this).hasClass("mini_music_box_play_btn_play")?b.play():b.pause()}),$(".normal_music_iframe_box_pause_status a").click(function(){b.play()}),$(".mini_current_channel").click(function(){$("#K_303_music_iframe").attr("src")||b.play()}),b.config.autoPlay&&b.play()},close:function(){$("#J_box_music").hide(),$("#K_303_music_iframe").attr("src","")},pause:function(){$(".normal_music_box_play_btn").removeClass("normal_music_box_play_btn_pause").addClass("normal_music_box_play_btn_play"),$(".mini_music_box_play_btn").removeClass("mini_music_box_play_btn_pause").addClass("mini_music_box_play_btn_play"),$("#K_303_music_iframe").attr("src","").hide(),$(".normal_music_iframe_box").addClass("normal_music_iframe_box_pause")},play:function(a,b){var c=this;if(0==arguments.length){var d=$(".normal_music_channel_list").find(".active");if(d.size())a=d.find("a").attr("data-channel"),b=d.find("a").attr("data-url");else{var e=c.music_channel_list[0];a=e.id,b=e.url}}$(".normal_music_iframe_box").removeClass("normal_music_iframe_box_pause"),$(".normal_music_box_play_btn").removeClass("normal_music_box_play_btn_play").addClass("normal_music_box_play_btn_pause"),$(".mini_music_box_play_btn").removeClass("mini_music_box_play_btn_play").addClass("mini_music_box_play_btn_pause"),$(".normal_music_channel_list").find("li").removeClass("active"),$(".normal_music_channel_"+a).closest("li").addClass("active"),$("#K_303_music_iframe").attr("src",b).show(),$(".mini_current_channel").find(".mini_music_channel_btn").appendTo(".mini_channel_list"),$(".mini_music_channel_"+a).appendTo(".mini_current_channel")},changeMode:function(a,b){var c=this;if(b||0==parseInt($("#J_box_music").css("left"))||c.changeNormalPosition(),$(window).scrollTop(),$(window).height()/2,$("#J_box_music").attr("class","").addClass(a+"_music_box"),"normal"==a&&($(window).height()<560?$(".normal_music_box").css({bottom:"auto",top:"0",position:"absolute"}):$(".normal_music_box").css({bottom:0,top:"auto",position:"fixed"})),"mini"==a&&$(".mini_music_box").css({bottom:0,top:"auto"}),"fullscreen"==a){$(".fullscreen_music_box").css({bottom:0,top:0,left:0,right:0});var d=$(".normal_music_iframe_box").width(),e=$(".normal_music_iframe_box").height();$("#K_303_music_iframe").width(d-40).height(e-40)}else $("#K_303_music_iframe").width(749).height(508);$(".normal_music_box_size_btn, .mini_music_box_size_btn").removeClass("active-size"),$(".size_"+a).addClass("active-size")},changeNormalPosition:function(){var a=this,b=$(".active-size").hasClass("size_fullscreen");b&&a.changeMode("normal");var c,d,e=$("#J_box_music").css("left");e=parseInt(e),0!=e?(c="收起",d="0",$(".normal_music_channel_list_toggle").find("span").removeAttr("class").addClass("list-close"),$("#K_303_music_iframe").attr("src")||a.play()):(c="展开",d="-789px",$(".normal_music_channel_list_toggle").find("span").removeAttr("class").addClass("list-open")),$("#J_box_music").animate({left:d}),$(".normal_music_box_toggle_btn").html(c)}};b.Init()});