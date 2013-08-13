var answer=0;
var next_question_lvlup = false;//下一题是否升级
var getQuestionRequest;
if((question!=null&&question.record!=null&&question.record.untested_num==0&&question.record.right_num>=2)||(user_count_info!=null&&user_count_info.right_num==10)){
    next_question_lvlup = true;
}
$(function(){
    $(".object_li:last").removeClass().addClass("object_li_last");//最后一个科目的位置
    $(".level li:first,.level li:eq(10)").css("margin-right","12px");//第一个和第十个科目的边距
                
    bindMediaTextClickEvent("disable");
    //弹出排行榜事件
    $("#J_topPrev,#J_topNext").hover(function(){
        $(this).addClass("red");
    }, function(){
        $(this).removeClass("red");
    })
    //上一个类别排行榜
    $("#J_topPrev").click(function(){
        var now_cat = $("#J_topCatBtn").attr("type_name");
        var now_li = $(".J_topCatul a:contains('"+now_cat+"')").parent("li");
        if(now_li.index(".top_used_li")==0){
            $(".J_topCatul .top_used_li:last").children("a").click();
        }else{
            var prev = now_li.prevAll(".top_used_li").first().children("a");
            getTopUserList(prev.attr("type"),prev);
        }
    })
    //下一个类别排行榜
    $("#J_topNext").click(function(){
        var now_cat = $("#J_topCatBtn").attr("type_name");
        var now_li = $(".J_topCatul a:contains('"+now_cat+"')").parent("li");
        var last_li_index = $(".J_topCatul .top_used_li").size()-1;
        if(now_li.index(".top_used_li")==last_li_index){
            $(".J_topCatul .top_used_li:first").children("a").click();
        }else{
            var next = now_li.nextAll(".top_used_li").first().children("a");
            getTopUserList(next.attr("type"),next);
        }
    })
    //    $(".pop_list").show();
    $(".pop_left").hover(function(){
        $(".pop_list").show();
    }, function(){
        $(".pop_list").hide();
        if($(".dropdown-toggle").parent("div").hasClass("open")){
            $(".dropdown-toggle").dropdown("toggle");
            $(".more_object").hide();
            $("#J_moreObjectLink").show();
        }
    })
    $("#J_moreObjectLink").mouseover(function(){
        $(".more_object").show();
        $(this).hide();
        $("body").click(function(){
            $(".more_object").hide();
            $("#J_moreObjectLink").show();
        })
    })
    $(".J_topCatul a").click(function(){
        getTopUserList($(this).attr("type"),$(this));
    });
    //分类点击事件
    $(".voice a,.target a,.pattern a").click(function(){
        if($(this).hasClass("grey")){
            alert("抱歉，该类不存在试题！");
            return false;
        }else if($(this).hasClass("red")){
            var top = $(".categorys").offset().top - 50;
            var left = $(".categorys").offset().left - 10;
            $.messager.show({
                msg: "<span  class='messager_span'>您已在"+$(this).text()+"状态</span>", 
                showType: 'fade', 
                width: 120, 
                height: 45, 
                timeout: 4000, 
                style: {
                    left: left, 
                    top: top
                }
            });
            return false;
        }
        $(this).addClass("red").siblings("a").removeClass("red");
        get_question_list("category");
    })
    //上一题，下一题按钮
    $(".left_symbol").click(function(){
        get_question_list("quick_select_prev");
    })
    $(".right_symbol").click(function(){
        if($("#J_rightNum").text()=="10"||next_question_lvlup){
            $("#J_levelUp").click();
            next_question_lvlup = false;
            return false;
        }
        get_question_list("quick_select_next");
    })
    //重置当前等级
    $("#J_restart").click(function(){
        restartLevel();
    })
    //升级
    $("#J_levelUp").click(function(){
        var next_level_li;
        //已是最高级
        if(($(".level .red").parent("li").index()+1)==$(".level li").size()){
            if($(".object_li .red").index()+1==$(".object_li").size()){
                $(".object_li .red").parent("li").prevAll("li").each(function(){
                    if(!$(this).children("a").hasClass("not_allowed")&&typeof next_level_li=="undefined"){
                        next_level_li=$(this);
                    }
                })
            }else{
                $(".object_li .red").parent("li").nextAll("li").each(function(){
                    if(!$(this).children("a").hasClass("not_allowed")&&typeof next_level_li=="undefined"){
                        next_level_li=$(this);
                    }
                })
            }
        }else{
            $(".level .red").parent("li").nextAll("li").each(function(){
                if(!$(this).children("a").hasClass("not_allowed")&&typeof next_level_li=="undefined"){
                    next_level_li=$(this);
                }
            })
        }
        if(typeof next_level_li!="undefined"){
            next_level_li.children("a").click();
        }else{
            var top = $("#J_levelDown").offset().top - 50;
            var left = $("#J_levelDown").offset().left - 65;
            $.messager.show({
                msg: "<span  class='messager_span'>最高级别，无法升级！</span>", 
                showType: 'fade', 
                width: 155, 
                height: 45, 
                timeout: 4000, 
                style: {
                    left: left, 
                    top: top
                }
            });
        }
    })
    //降级
    $("#J_levelDown").click(function(){
        var prev_level_li;
        if($(".level .red").parent("li").index()>0){
            $(".level .red").parent("li").prevAll("li").each(function(){
                if(!$(this).children("a").hasClass("not_allowed")&&typeof prev_level_li=="undefined"){
                    prev_level_li=$(this);
                }
            })
            if(typeof prev_level_li!="undefined"){
                prev_level_li.children("a").click();
            }else{
                var top = $(this).offset().top - 50;
                var left = $(this).offset().left - 65;
                $.messager.show({
                    msg: "<span  class='messager_span'>最小级别，无法降级！</span>", 
                    showType: 'fade', 
                    width: 155, 
                    height: 45, 
                    timeout: 4000, 
                    style: {
                        left: left, 
                        top: top
                    }
                });
            }
        }
    })
    bindLevelClickEvent();//绑定等级点击事件
    bindObjectClickEvent();//绑定科目点击事件
    //                bindOptionSelectEvent();//绑定选项点击事件
    resetQuestionContent();//重置问题内容的位置
    bindOptionHoverEvent();//绑定选项鼠标移动到事件
    bindOptionSelectEvent();//点击选项
    trimWhiteSpace();
                
    //                $("#video,.J_player,.video").click(function(){
    //                    bindOptionSelectEvent("enable");
    //                })
                
    /** $视频播放 **/
    $('.J_player').click(function(){
        var isAboutVideo = $('#J_media_div').attr('data_isAboutVideo');
        if (isAboutVideo == 1) {
            $(this).hide();
	                    
            var media_url = $('#J_media_div').attr('data_media_url');
            var videoStr = '';
            if (media_url) {
                videoStr += '<object id="J_media_object" height="100%" width="100%" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
                videoStr += '<param name="wmode" value="transparent">';
                videoStr += '<param name="movie" value="' + media_url +'">';
                videoStr += '<embed name="swf" height="' + '100%' + '" width="100%" play="true" type="application/x-shockwave-flash" allowfullscreen="true" wmode="transparent" src="' + media_url +'">';
                videoStr += '</object>';
            } else {
                videoStr += '<span>视频无法加载，刷新重试！</span>'
            }
            $('#J_media_div').html(videoStr);
            $('#J_media_div').css({
                'height': 220, 
                'width': 350,
                'visibility': ''
            });
        }
        return false;
    });
    /** 视频播放$ **/
                
    /** $关灯 **/
    $('#lightclose').click(function(){
        $('#playshow_mask').css({
            'display': 'block', 
            'opacity': 1
        });
        $('.video').css('z-index', '1400');
        return false;
    });
    /** 关灯$ **/
                
    /** $开灯 **/
    $('#lightopen').click(function(){
        $('#playshow_mask').css({
            'display': 'none', 
            'opacity': 0
        });
        $('.video').css('z-index', '');
        return false;
    });
    /** 开灯$ **/
                
    /** $弹出播放 **/
    $('#openVideoSmallWindow').click(function(){
        //$('.J_player').show();
        // $('#J_media_div').html(videoStr);
                	
        var media_url = $(this).attr('data_media_url');
        window.open(media_url, 'newwindow','height=400,width=600,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
        return false;
    });
    /** 弹出播放$ **/
                
    /** $视频全屏退出 **/
    $(document).keydown(function(event){
        if(event.keyCode==27 && $('#J_media_div').width() > 550) {
            videoZoom(true);
            return false;
        }
    });
    /** 视频全屏退出$ **/
                
                
    /** $视频放大 **/
    $('#lightMax').click(function(){
        if ($.trim($('#J_media_div').html()).length == 0) {
            $(".J_player").trigger("click");
        }
        videoZoom();
        return false;
    });
    /** 视频放大$ **/
                
    /** $视频缩小 **/
    $('#lightMin').click(function(){
        videoZoom(true);
        return false;
    });
    /** 视频缩小$ **/
                
    // 直达框获得焦点
    $(".top").mouseover(function() {
        $("#direct_text").select();
    });

    $("#direct_text").click(function() {
        $(this).val('');
    });

    // 直达回车键响应 
    $("#direct_text").keydown(function(event) {
        if (event.keyCode == 13) {
            var tag = $.trim($(this).val());
            if (tag != '') {
                $("#direct_text").select();
            //$("#frm_drct").submit();
            }
        }
    });
                
});
//重新开始某个等级
function restartLevel(){
    var voice = $(".voice .red").attr("value");
    var target = $(".target .red").attr("value");
    var object = $(".object .red").attr("value");
    var level = $(".level .red").attr("value");
    $.post(URL+"/restart_level",{
        voice:voice,
        target:target,
        object:object,
        level:level
    },function(msg){
        if(msg){
            if(msg.status){
                $("#J_rightNum").text(0);
                $("#J_currentRice").text(0);
                $("#J_plateImg").attr("src",PUBLIC+"/English/images/000.jpg");
                get_question_list("quick_select");//重新获取题目
            }
        }
    },"json");
}
function bindLevelClickEvent(){
    $(".level a").click(function(){
        if($(this).hasClass("grey")){
            alert("抱歉，该等级不存在试题！");
            return false;
        }else if($(this).hasClass("red")){
            var top = $(this).offset().top - 50;
            var left = $(this).offset().left - 10;
            $.messager.show({
                msg: "<span  class='messager_span'>您已在等级: "+$(this).text()+"</span>", 
                showType: 'fade', 
                width: 155, 
                height: 45, 
                timeout: 4000, 
                style: {
                    left: left, 
                    top: top
                }
            });
            return false;
        }
        $(this).addClass("red").parent("li").siblings("li").children("a").removeClass("red");
        get_question_list("level");
    })
}
function bindObjectClickEvent(){
    $(".object a").click(function(){
        if($(this).hasClass("grey")){
            alert("抱歉，该科目不存在试题！");
            return false;
        }else if($(this).hasClass("red")){
            var top = $(this).offset().top - 50;
            var left = $(this).offset().left - 10;
            $.messager.show({
                msg: "<span  class='messager_span'>您已在科目: "+$(this).text()+"</span>", 
                showType: 'fade', 
                width: 155, 
                height: 45, 
                timeout: 4000, 
                style: {
                    left: left, 
                    top: top
                }
            });
            return false;
        }
        $(this).addClass("red").parent("li").siblings("li").children("a").removeClass("red");
        get_question_list("object");
    })
}
function bindOptionSelectEvent(type){
    $(".J_option").unbind("click");
    $(".J_option").click(function(){
        var target = $(this);
        if(type=="disable"){
            var top = $("#question_content").offset().top+10;
            var left = $("#question_content").offset().left;
            $.messager.show({
                msg: "<span  class='messager_span'>请先播放视频！</span>", 
                showType: 'fade', 
                width: 125, 
                height: 45, 
                timeout: 4000, 
                style: {
                    left: left, 
                    top: top
                }
            });
        }else{
            var select_option = $(this).children("input[name='answer']").val();
            var object = $(".object .red").attr("value");
            $.post(URL+"/answer_question",{
                question_id:$("#J_question_id").text(),
                object:object,
                select_option:select_option
            },function(msg){
                if(msg.status){
                    var data = msg.data;
                    var user_count_info = data.user_count_info
                    if(user_count_info==null||user_count_info.right_num==null){
                        user_count_info.right_num=0;
                    }
                    $("#J_rightNum").text(user_count_info.right_num);
                    $("#J_currentRice").text(user_count_info.right_num*100);
                    if(user_count_info.right_num==0){
                        $("#J_plateImg").attr("src",PUBLIC+"/English/images/000.jpg");
                    }
                    else{
                        $("#J_plateImg").attr("src",PUBLIC+"/English/images/"+user_count_info.right_num*100+".jpg");
                    }
                    
                    var english_user_info = data.english_user_info;
                    if(english_user_info==null){
                        english_user_info.total_rice=0;
                    }
                    $("#J_totalRice").text(english_user_info.total_rice);
                    
                    if(data.level_up){
                        var top = $("#question_content").offset().top+10;
                        var left = $("#question_content").offset().left;
                        $.messager.show({
                            msg: "<span  class='messager_span'>恭喜，你可以升级了</span>", 
                            showType: 'fade', 
                            width: 175, 
                            height: 45, 
                            timeout: 4000, 
                            style: {
                                left: left, 
                                top: top
                            }
                        });
                    }
                    if(english_user_info!=null){
                        if(english_user_info.best_object!=0&&english_user_info.best_level!=0&&english_user_info.best_object!=null&&english_user_info.best_level!=null){
                            $("#J_best_level").text(english_user_info.best_object_name+"/"+english_user_info.best_level_name);
                        }
                    }
                    answer = data.question_info.answer;
                    if(select_option==answer){
                        target.prev(".answer_status").html("√").css("color", "green").css("font-size","20px");
                    }else{
                        target.prev(".answer_status").html("X").css("color", "red");
                        setTimeout("$(\"[name='answer']\").filter(\"[value='\"+answer+\"']\").parent('b').prev('.answer_status').html(\"√\").css(\"color\", \"green\").css('font-size','20px');",1000);
                    }
                    $(".J_option").unbind("click");
                    //更新排行榜
                    var now_cat = $("#J_topCatBtn").attr("type_name");
                    $(".J_topCatul a:contains('"+now_cat+"')").click();
                    //根据用户上次答题情况进行提示操作
                    var english_user_record = data.english_user_record;
                    //做对的再做对
                    if(english_user_record.is_right==1&&english_user_record.right_num>=2){
                        //无新题自动升级
                        var content = "";
                        if(data.question_info.untested_num==0){
                            next_question_lvlup = true;
                            content="本题已做对"+english_user_record.right_num+"次，升级吧";
                        }else{
                            next_question_lvlup = false;
                            content="本题已做对"+english_user_record.right_num+"次，换新题吧";
                        }
                        var top = $("#question_content").offset().top+10;
                        var left = $("#question_content").offset().left;
                        $.messager.show({
                            msg: "<span  class='messager_span'>"+content+"</span>", 
                            showType: 'fade', 
                            width: 200, 
                            height: 45, 
                            timeout: 4000, 
                            style: {
                                left: left, 
                                top: top
                            }
                        });
                    //                        setTimeout("$(\"#J_levelUp\").click();",5000);
                    }else if(english_user_record.is_right==0&&english_user_record.error_num>=2){
                        var top = $("#question_content").offset().top+10;
                        var left = $("#question_content").offset().left;
                        $.messager.show({
                            msg: "<span  class='messager_span'>唉，又错了！</span>", 
                            showType: 'fade', 
                            width: 125, 
                            height: 45, 
                            timeout: 4000, 
                            style: {
                                left: left, 
                                top: top
                            }
                        });
                    }
                }
            },"json");
            bindMediaTextClickEvent();
            $(".answer .J_option").removeClass("red cursor");
            $(".J_option").unbind();
        }
    })
}
            
/**
 * 视频缩放
 *
 * @param isOut: true(缩小);false(放大)
 * @param isClear: true(清除宽度高度)
 *
 * @return void
 **/
function videoZoom(isOut, isClear) {
    var mediaHeight = $('#J_media_div').height();	//视频实际高度
    var mediaWidth = $('#J_media_div').width();		//视频实际宽度
    var rightWidth = 550;							//第一次放大视频宽度
    var rightHeight = 320;							//第一次放大视频高度
    var minWidth = 350;								//视频最小宽度
    var minHeight = 220;							//视频最小高度
            	
    if (!isOut) {	//放大
            		
        if (mediaWidth < rightWidth) {	//第一次放大
            mediaWidth = rightWidth;
            mediaHeight = rightHeight;
            			
            $('.video').css('margin-left', '100px');
                		
            $('#lightMax').text('再放大').attr('title', '再放大');
        } else if (mediaWidth >= rightWidth) {	//全屏
            mediaWidth = $(window).width();
            mediaHeight = $(window).height() - 10;
                		
            $('#playshow_mask').css({
                'display': 'block', 
                'opacity': 1
            });
            $('.video').css({
                'margin-left':'', 
                'position':'fixed', 
                'top':'0', 
                'left':'0', 
                'z-index':'1400'
            });
            $('#lightclose').css('visibility', 'hidden');
            $('#lightMax').css('visibility', 'hidden');
            $('.sideTool').css('z-index', '1500');
        }
            	
    } else {	//缩小
            		
        if (mediaWidth <= rightWidth) {	//第一次放大后缩小
            mediaWidth = minWidth;
            mediaHeight = minHeight;
                		
            $('.video').css('margin-left', '');
                		
            $('#lightMax').text('放大').attr('title', '放大');
            $('#lightMin').text('缩小').attr('title', '缩小');
        } else if (mediaWidth > rightWidth) {	//全屏后缩小
            mediaWidth = rightWidth;
            mediaHeight = rightHeight;
            			
            $('#playshow_mask').css({
                'display': 'none', 
                'opacity': 0
            });
            $('.video').css({
                'margin-left':'100px', 
                'position':'', 
                'top':'', 
                'left':'', 
                'z-index':''
            });
            $('#lightclose').css('visibility', '');
            $('#lightMax').css('visibility', '');
            $('.sideTool').css('z-index', '');
                		
            $('#lightMin').text('再缩小').attr('title', '再缩小');
        }
            	
    }
    if (isClear) {
        mediaHeight = '';
        mediaWidth = '';
    }
    $('#J_media_div').css({
        'height': mediaHeight, 
        'width': mediaWidth
    });
}
            
/**
 * 获取问题列表
 * 通过大类点击、科目点击以及等级点击获取问题列表
 * type 点击类型，大类为category，科目为object，等级为lelve
 */
function get_question_list(type){
    //如果有题目正在请求，中断此次请求并提示
    if(typeof getQuestionRequest!= "undefined"){
        var top = $(".right_symbol .quick_select").offset().top - 50;
        var left = $(".right_symbol .quick_select").offset().left - 32;
        $.messager.show({
            msg: "<span class='messager_span'>题目正在加载，请稍后...</span>", 
            showType: 'fade', 
            width: 175, 
            height: 45, 
            timeout: 4000,
            style:{
                left:left,
                top:top
            }
        });
        return false;
    }
    var voice = $(".voice .red").attr("value");
    var target = $(".target .red").attr("value");
    var pattern = $(".pattern .red").attr("value");
    var object = $(".object .red").attr("value");
    var level = $(".level .red").attr("value");
    var now_question_id = $("#J_question_id").text();//指定题目id
//    layer_div("show");
    getQuestionRequest = $.post(URL+"/ajax_get_question",{
        voice:voice,
        target:target,
        pattern:pattern,
        object:object,
        level:level,
        type:type,
        now_question_id:now_question_id
    },function(msg){
        if(msg){
            var data = msg.data;
            if(data==null){
                return false;
            }
            if(data.question&&data.question.tested){
                var top = $(".right_symbol .quick_select").offset().top - 50;
                var left = $(".right_symbol .quick_select").offset().left - 32;
                $.messager.show({
                    msg: "<span class='messager_span'>暂无新题，升级吧!</span>", 
                    showType: 'fade', 
                    width: 150, 
                    height: 45, 
                    timeout: 4000,
                    style:{
                        left:left,
                        top:top
                    }
                });
            }
            //点击为类型，更新科目列表
            if(type=="category"){
                var object_list = data.object_list;
                if(object_list!=null){
                    var str= '';
                    var has_default = 0;
                    for(var i =0;i<object_list.length;i++){
                        str+='<li class="object_li"><a ';
                        if(has_default==0&&object_list[i]['question_num']>0){
                            str+=' class="red" ';
                            has_default=1;
                        }else{
                            if(object_list[i]['question_num']==0){
                                str+=' class="grey not_allowed" ';
                            }
                        }
                        str+=' value="'+object_list[i]['id']+'" href="javascript:void(0)">'+object_list[i]['name']+'</a></li>';
                    }
                    $(".object ul").html(str);
                    bindObjectClickEvent();
                    $(".object_li:last").removeClass().addClass("object_li_last");
                }
            }
            //等级为空
            if(data['level_info']==null){
                data['level_info'] = new Array();
                data['level_info']['id']=0;
            }
            //更新等级列表
            var level_list = data.level_list;
            if(level_list!=null){
                var str= '';
                for(var i =0;i<level_list.length;i++){
                    str+='<li class="level_li"><a ';
                    if(data['level_info']['id']==level_list[i]['id']){
                        str+=' class="red" ';
                    }else{
                        if(level_list[i]['question_num']==0){
                            str+=' class="grey not_allowed" ';
                        }
                    }
                    str+=' value="'+level_list[i]['id']+'" href="javascript:void(0)">'+level_list[i]['name']+'</a></li>';
                }
                $(".level ul").html(str);
                if($(".level .red").size()<1){
                    $(".level a").not(".grey").first().addClass("red");
                }
                bindLevelClickEvent();
                $(".level li:first,.level li:eq(10)").css("margin-right","12px");
            }
            if(data['user_count_info']==null||data['user_count_info']['right_num']==null){
                data['user_count_info']['right_num']=0;
            }
            //更新当前的科目和等级,正确题目数量
            $(".current_object_level").text(data['object_info']['name']+"/"+data['level_info']['name']);
            $("#J_rightNum").text(data['user_count_info']['right_num']);
            $("#J_currentRice").text(data['user_count_info']['right_num']*100);
            if(data['user_count_info']['right_num']==0){
                $("#J_plateImg").attr("src",PUBLIC+"/English/images/000.jpg");
            }else{
                $("#J_plateImg").attr("src",PUBLIC+"/English/images/"+data['user_count_info']['right_num']*100+".jpg");
            }
            var object_info = data['object_info'];
            if(object_info){
                $(".object_li .red").removeClass("red");
                $(".object_li a[value='"+object_info.id+"']").addClass("red");
            }
            if((data['user_count_info']!=null&&data['user_count_info']['right_num']==10)||(data['question']!=null&&data['question']['record']!=null&&data['question']['record']['right_num']>=2)){
                next_question_lvlup = true;
            }else{
                next_question_lvlup = false;
            }
            //更新题目
            var question = data.question;
            if(question.content==null){
                $("#question_content").text("");
            }else{
                $("#question_content").text(question.content);
            }
            $("#media_text").attr("media_text_url",question.media_text_url);
            $("#media_text").unbind("click");//点击选项后才能查看文本
            resetQuestionContent();//重置问题的文本内容
            updateOption(question.option);//更新问题选项
            
            $("#J_question_id").text(question.id);
                        
            /** $视频播放 **/
            var videoStr = '';
            if (question.isAboutVideo!=1) {
            	if (question.media_type == 1) {
            		videoStr = question.media;
            	} else if (question.media_type == 2) {
            		videoStr = '<iframe src="' + question.media + '" width="100%" height="100%" scrolling="no" frameborder="0">';
            	} else if (question.media_type == 3) {
            		var swfUrl = 'http://www.kizphonics.com/wp-content/uploads/jw-player-plugin-for-wordpress/player/player.swf';
					var version = '10.2.0';
					var params  = {
				        quality: "high",
				        wmode: "opaque",
				        scale: "noscale",
				        align: "left",
				        allowFullScreen: "true",
				        allowScriptAccess: "always",
				        bgColor: "#000000"
				    };
				    
				    swfobject.embedSWF(swfUrl, "J_media_div", "100%", "100%", version, "/swf/playerProductInstall.swf", question.media, params );
			   	    
            	} else {
            		
	                videoStr += '<object id="J_media_object" height="100%" width="100%" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
	                videoStr += '<param name="wmode" value="transparent">';
	                videoStr += '<param name="movie" value="' + question.media_url +'">';
	                videoStr += '<embed name="swf" menu="true" height="100%" width="100%" play="false" type="application/x-shockwave-flash" allowfullscreen="true" wmode="transparent" src="' + question.media_url +'">';
	                videoStr += '</object>';
            	}
            }
            if (question.isAboutVideo) {
                $('#J_media_img').attr('src', question.media_img_url);
                $('#J_media_div').css('visibility', 'hidden');
                // $('.video_jug_play').css('display', 'none');
                $('.J_player').css('display', 'block');
                
                videoZoom(true, true);
            } else {
                $('.J_player').css('display', 'none');
                //$('.video_jug_play').css('display', 'block');
                $('#J_media_div').css({
                    'height': 220, 
                    'width': 350,
                    'visibility': ''
                });
                videoZoom(true, false);
            }
                        
            $('#J_media_div').attr('data_media_url', question.media_url);
            $('#openVideoSmallWindow').attr('data_media_url', question.media_url);
            $('#J_media_div').attr('data_isAboutVideo', question.isAboutVideo); 
            $('#J_media_div').html(videoStr);
            /** 视频播放$ **/
                        
            trimWhiteSpace();
            bindMediaTextClickEvent("disable");
        }
//        layer_div("hide");
        getQuestionRequest = undefined;
    },"json")
}
function updateOption(option){
    var str="";
    for(var i=0;i<option.length;i++){
        str+='<li><label class="answer_status"></label><b class="cursor J_option">'+
        '<input type="radio" name="answer" value="'+option[i]['id']+'" class="none" />';
        if(i==0){
            str+="A";
        }else if(i==1){
            str+="B";
        }else if(i==2){
            str+="C";
        }else if(i==3){
            str+="D";
        }
        str+='.<span>'+option[i]['content']+'</span></b></li>';
    }
    $(".answer ul").html(str);
    bindOptionHoverEvent();
    bindOptionSelectEvent();//点击选项
}
function resetQuestionContent(){
    var height = $("#question_content").height();
    var line_height = $("#question_content").css("line-height");
    line_height = line_height.substr(0, line_height.length-line_height.indexOf("px"));
    if(height==line_height){
        $("#question_content").html("<br>"+$("#question_content").html());
    }
}
function bindMediaTextClickEvent(type){
    $("#media_text").unbind("click");
    if(type!="disable"){
        $("#media_text").click(function(){
            var url = trim($(this).attr("media_text_url"));
            if(url==""){
                var top = $(this).offset().top - 50;
                var left = $(this).offset().left - 35;
                $.messager.show({
                    msg: "<span class='messager_span'>对不起，文本不存在!</span>", 
                    showType: 'fade', 
                    width: 155, 
                    height: 45, 
                    timeout: 4000, 
                    style: {
                        left: left, 
                        top: top
                    }
                });
                return false;
            }
            window.open(url, "_blank");
        })
    }else{
        $("#media_text").click(function(){
            var top = $(this).offset().top - 50;
            var left = $(this).offset().left - 35;
            $.messager.show({
                msg: "<span class='messager_span'>答题后才能查看文本！</span>", 
                showType: 'fade', 
                width: 155, 
                height: 45, 
                timeout: 4000, 
                style: {
                    left: left, 
                    top: top
                }
            });
            return false;
        })
    }
}
/**
* 绑定选项鼠标Hover事件
* @param
* @return
* @author Adam $date:2013.6.18$
*/
function bindOptionHoverEvent(){
    $(".answer .J_option").hover(function(){
        $(this).addClass("red");
    },function(){
        $(this).removeClass("red");
    });
}
function trimWhiteSpace(){
    var question_content = trim($("#question_content").html());
    $("#question_content").html(question_content);
    $(".J_option").each(function(){
        var option = trim($(this).children("span").html());
        $(this).children("span").html(option);
    })
}
function trim(str){
    var space_reg_C = /&#12288;+/g;
    var space_reg_A = /&nbsp;+/g;
    var space_reg_B = /(^\s+)|(\s+$)/g;
    str = str.replace(space_reg_C, " ");
    str = str.replace(space_reg_A, " ");
    str = str.replace(space_reg_B, "");
    return str;
}
/**
*获取排行榜用户列表
* @param string type [排行类型]
* @param object target [点击的对象]
* @return void
* @author Adam $date2013.6.20$
*/
function getTopUserList(type,target){
    if(type=="more_object"){
        return false;
    }
    $.post(URL+"/get_top_user",{
        type:type
    },function(msg){
        if(msg){
            //第一个排行榜
            var top_1 = msg.data[0];
            var top_str_1 = "";
            if(top_1!=null){
                for(var i=0;i<top_1.length;i++){
                    top_str_1+='<div class="pop_listen_list'+(i+1)+
                    ' border_top tleft"><label>'+top_1[i]['nickname']+
                    '</label><strong>'+top_1[i]['best_level_name']+
                    '</strong><span>'+top_1[i]['rice_sum']+'</span></div>';
                }
            }
            $("#J_topDiv_1").html(top_str_1);

            var top_2 = msg.data[1];
            var top_str_2 = "";
            if(top_2!=null){
                for(var i=0;i<top_2.length;i++){
                    top_str_2+='<div class="pop_listen_list'+(i+1)+
                    ' border_top tleft"><label>'+top_2[i]['nickname']+
                    '</label><strong>'+top_2[i]['best_level_name']+
                    '</strong><span>'+top_2[i]['rice_sum']+'</span></div>';
                }
            }
            if(type=="total_rice"||type=="continue_right_num"){
                $(".pop_listen").hide();
            }else{
                $(".pop_listen").show();
            }
            $("#J_topDiv_2").html(top_str_2);
            $("#J_topCatBtn").html(target.text()+'<span class="caret"></span>');
            $("#J_topCatBtn").attr("type_name",target.text());
        }
    },"json")
}
function array_splice(array,index){
    var ret = new Array();
    for(var i=0;i<array.length;i++){
        if(i!=index&&array[i]!=null){
            ret.push(array[i]);
        }
    }
    return ret;
}
/**
 * 操作透明阻挡层的方法
 * @param {string} type [显示还是隐藏]
 * @returns {void}
 * @author Adam $date2013-07-20$
 */
function layer_div(type){
    var layer_div = $(".layer_div_img");
    if(type=="show"){
        if(layer_div.hasClass("layer_div_img")){
            layer_div.show();
        }else{
            layer_div = $("<img class='layer_div_img' src='"+PUBLIC+"/Images/loading.gif' />");
            $("body").append(layer_div);
        }
        var top = window.screen.availHeight*0.3;
        var left = window.screen.availWidth*0.5-95;
        layer_div.css("top",top).css("left",left);
    }else{
        layer_div.hide();
    }
}

//获得天气，通过当前ip added by Tony 2013/07/08
$(document).ready(function(){
	var city = remote_ip_info["city"];
	var str = encodeURI(city); //alert(re);		
	jQuery('#sinaWeatherToolIframe').attr('src','http://weather.news.sina.com.cn/chajian/iframe/weatherStyle0.html?city='+str);
});