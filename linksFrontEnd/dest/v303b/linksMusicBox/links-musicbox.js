function linksMusicBoxLoad(){var a='<div id="J_box_music"class="normal_music_box"style="display: none">        <div class="mini_music_channel_list">            <div class="mini_music_channel_select">                <div class="mini_current_channel"></div>                <i class="mini_music_ang"></i>                <div class="mini_channel_list"></div>            </div>            <div class="music-close-tip">听烦了</div>            <a class="mini_music_box_close_btn"href="javascript:;">×</a>            <a class="mini_music_box_play_btn mini_music_box_play_btn_pause"href="javascript:;"></a>            <a class="mini_music_box_size_btn size_mini"href="javascript:;"data-size="mini">小</a>            <a class="mini_music_box_size_btn size_normal"href="javascript:;"data-size="normal">中</a>            <a class="mini_music_box_size_btn size_fullscreen"href="javascript:;"data-size="fullscreen">大</a>            <a href="http://www.links123.cn/" target="_blank" style="position: absolute;right: 20px;line-height: 32px;">另客网</a>        </div>        <div class="normal_music_iframe_box">            <iframe id="K_303_music_iframe"allowtransparency="true"frameborder="0"style=""></iframe>            <div class="normal_music_iframe_box_pause_status">                <a href="javascript:;"></a>                <p>点击继续听...</p>            </div>        </div>        <div class="normal_music_channel_list">            <div class="normal_music_channel_list_toggle">                <span class="list-open"></span>            </div>            <div class="music-close-tip">听烦了</div>            <a class="normal_music_box_close_btn"href="javascript:;">×</a>            <a class="normal_music_box_play_btn"href="javascript:;"></a>            <!--a href="javascript:;"class="normal_music_box_toggle_btn">展开</a-->            <ul></ul>            <a class="normal_music_box_size_btn size_nimi"href="javascript:;"data-size="mini">小</a>            <a class="normal_music_box_size_btn size_normal"href="javascript:;"data-size="normal">中</a>            <a class="normal_music_box_size_btn size_fullscreen"href="javascript:;"data-size="fullscreen">大</a>            <a href="http://www.links123.cn/" target="_blank" style="display:block;text-align:center">另客网</a>        </div>    </div>',b={Init:function(){var b=this;jQuery("body").append(a),b.config={view:"mini",autoPlay:!1},jQuery.extend(b.config,window.linksMusicBoxConfig),b.music_channel_list=[{id:"douban",name:"豆瓣FM",url:"http://douban.fm/partner/playerhao123"},{id:"xiami",name:"虾米音乐",url:"http://www.xiami.com/kuang/hao123/"},{id:"baidu",name:"百度随心听",url:'http://fm.baidu.com/?embed=hao123"'},{id:"duole",name:"多乐音乐",url:"http://www.duole.com/application/qihu360"},{id:"kugou",name:"酷狗音乐",url:"http://web.kugou.com/default.html"},{id:"duomi",name:"多米音乐",url:"http://app.duomiyy.com/webradio/hao123/"},{id:"kuwo",name:"酷我音乐",url:"http://player.kuwo.cn/webmusic/web/play"},{id:"beiwa",name:"贝瓦儿歌",url:"http://app.beva.com/360/fm"},{id:"yinyuetai",name:"音悦台",url:"http://www.yinyuetai.com/baidu/hao123"},{id:"st",name:"SongTest",url:"http://www.songtaste.com/radio.php"}],b.changeMode(b.config.view,!0);var c="",d="";jQuery.each(b.music_channel_list,function(a,b){c+='<li><a class="normal_music_channel_btn normal_music_channel_'+b.id+'" href="javascript:;" data-url="'+b.url+'" data-channel="'+b.id+'"></a></li>',d+='<div class="mini_music_channel_btn mini_music_channel_'+b.id+'" data-url="'+b.url+'" data-channel="'+b.id+'"><b></b><span>'+b.name+"</span></div>"}),jQuery(".normal_music_channel_list").find("ul").html(c),jQuery(".mini_channel_list").html(d),jQuery("#J_box_music").show(),b.play(),b.pause(),jQuery(".normal_music_channel_list").on("click",".normal_music_channel_btn",function(){var a=jQuery(this).attr("data-channel"),c=jQuery(this).attr("data-url");b.play(a,c)}),jQuery(".mini_channel_list").on("click",".mini_music_channel_btn",function(){var a=jQuery(this).attr("data-channel"),c=jQuery(this).attr("data-url");return b.play(a,c),!1}),jQuery("#J_box_music").draggable({handle:".mini_music_channel_list"}),jQuery(".normal_music_box_size_btn, .mini_music_box_size_btn").click(function(){var a=jQuery(this).attr("data-size");b.changeMode(a)}),jQuery(".mini_channel_list").on("mouseover",".mini_music_channel_btn",function(){jQuery(".mini_music_channel_btn").removeClass("hover_class"),jQuery(this).addClass("hover_class")}).on("mouseout",".mini_music_channel_btn",function(){jQuery(this).removeClass("hover_class")}),jQuery(".mini_music_channel_select").mouseover(function(){if(jQuery(".mini_channel_list").is(":hidden")){var a=jQuery(".mini_channel_list").height(),b=jQuery(".mini_music_box").css("top"),c=jQuery(".mini_music_box").height(),d=jQuery(window).height();b="auto"==b?d-c:parseInt(b),a>=d-b-c?jQuery(".mini_channel_list").css("margin-top",-(a+c-8)+"px"):jQuery(".mini_channel_list").css("margin-top","0"),jQuery(".mini_channel_list").show()}}).mouseout(function(){jQuery(".mini_music_channel_btn").removeClass("hover_class"),jQuery(".mini_channel_list").hide()}),jQuery(".normal_music_box_close_btn, .mini_music_box_close_btn, .music-close-tip").click(function(){b.close()}),jQuery(".normal_music_channel_list_toggle").click(function(){b.changeNormalPosition()}),jQuery(document).on("keydown",function(a){if(27==a.keyCode){var c=jQuery(".active-size").hasClass("size_fullscreen"),d=jQuery(".active-size").hasClass("size_normal");c?b.changeMode("normal"):d&&0==parseInt(jQuery("#J_box_music").css("left"))&&b.changeNormalPosition()}}),jQuery(".normal_music_box_play_btn").click(function(){jQuery(this).hasClass("normal_music_box_play_btn_play")?b.play():b.pause()}),jQuery(".mini_music_box_play_btn").click(function(){jQuery(this).hasClass("mini_music_box_play_btn_play")?b.play():b.pause()}),jQuery(".normal_music_iframe_box_pause_status a").click(function(){b.play()}),jQuery(".mini_current_channel").click(function(){jQuery("#K_303_music_iframe").attr("src")||b.play()}),b.config.autoPlay&&b.play()},close:function(){jQuery("#J_box_music").hide(),jQuery("#K_303_music_iframe").attr("src","")},pause:function(){jQuery(".normal_music_box_play_btn").removeClass("normal_music_box_play_btn_pause").addClass("normal_music_box_play_btn_play"),jQuery(".mini_music_box_play_btn").removeClass("mini_music_box_play_btn_pause").addClass("mini_music_box_play_btn_play"),jQuery("#K_303_music_iframe").attr("src","").hide(),jQuery(".normal_music_iframe_box").addClass("normal_music_iframe_box_pause")},play:function(a,b){var c=this;if(0==arguments.length){var d=jQuery(".normal_music_channel_list").find(".active");if(d.size())a=d.find("a").attr("data-channel"),b=d.find("a").attr("data-url");else{var e=c.music_channel_list[0];a=e.id,b=e.url}}jQuery(".normal_music_iframe_box").removeClass("normal_music_iframe_box_pause"),jQuery(".normal_music_box_play_btn").removeClass("normal_music_box_play_btn_play").addClass("normal_music_box_play_btn_pause"),jQuery(".mini_music_box_play_btn").removeClass("mini_music_box_play_btn_play").addClass("mini_music_box_play_btn_pause"),jQuery(".normal_music_channel_list").find("li").removeClass("active"),jQuery(".normal_music_channel_"+a).closest("li").addClass("active"),jQuery("#K_303_music_iframe").attr("src",b).show(),jQuery(".mini_current_channel").find(".mini_music_channel_btn").appendTo(".mini_channel_list"),jQuery(".mini_music_channel_"+a).appendTo(".mini_current_channel")},changeMode:function(a,b){var c=this;if(b||0==parseInt(jQuery("#J_box_music").css("left"))||c.changeNormalPosition(),jQuery(window).scrollTop(),jQuery(window).height()/2,jQuery("#J_box_music").attr("class","").addClass(a+"_music_box"),"normal"==a&&(jQuery(window).height()<560?jQuery(".normal_music_box").css({bottom:"auto",top:"0",position:"absolute"}):jQuery(".normal_music_box").css({bottom:0,top:"auto",position:"fixed"})),"mini"==a&&jQuery(".mini_music_box").css({bottom:0,top:"auto"}),"fullscreen"==a){jQuery(".fullscreen_music_box").css({bottom:0,top:0,left:0,right:0});var d=jQuery(".normal_music_iframe_box").width(),e=jQuery(".normal_music_iframe_box").height();jQuery("#K_303_music_iframe").width(d-40).height(e-40)}else jQuery("#K_303_music_iframe").width(749).height(508);jQuery(".normal_music_box_size_btn, .mini_music_box_size_btn").removeClass("active-size"),jQuery(".size_"+a).addClass("active-size")},changeNormalPosition:function(){var a=this,b=jQuery(".active-size").hasClass("size_fullscreen");b&&a.changeMode("normal");var c,d,e=jQuery("#J_box_music").css("left");e=parseInt(e),0!=e?(c="收起",d="0",jQuery(".normal_music_channel_list_toggle").find("span").removeAttr("class").addClass("list-close"),jQuery("#K_303_music_iframe").attr("src")||a.play()):(c="展开",d="-789px",jQuery(".normal_music_channel_list_toggle").find("span").removeAttr("class").addClass("list-open")),jQuery("#J_box_music").animate({left:d}),jQuery(".normal_music_box_toggle_btn").html(c)}};b.Init()}!function(){function a(a,b){var c=document.createElement("script");c.type="text/javascript",c.readyState?c.onreadystatechange=function(){("loaded"==c.readyState||"complete"==c.readyState)&&(c.onreadystatechange=null,b())}:c.onload=function(){b()},c.src=a,document.getElementsByTagName("head")[0].appendChild(c)}document.write('<link media="screen" href="http://a.links123.net/v303b/linksMusicBox/links-musicbox.css?20131123" type="text/css" rel="stylesheet" >'),window.jQuery?window.jQuery.fn.draggable?linksMusicBoxLoad():a("http://a.links123.net/v303b/js/jquery.plugins.js",function(){linksMusicBoxLoad()}):a("http://a.links123.net/v303b/js/jquery-1.7.1.min.js",function(){window.jQuery.fn.draggable?linksMusicBoxLoad():a("http://a.links123.net/v303b/js/jquery.plugins.js",function(){linksMusicBoxLoad()})})}();