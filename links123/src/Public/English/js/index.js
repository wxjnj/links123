var ajaxRequest;
var next_question_lvlup = false;
$(function() {
    if ($("#J_currentRice").text() == 1000) {
        next_question_lvlup = true;
    }
    $(".scrollable").scrollable({circular: true});
    $(".grade li:eq(13)").css("margin-left", level_margin_index + "px");

    //答题按钮点击事件
    $("#J_answerButton").click(function() {
//        $(this).toggleClass("current");
//        if ($(".answer").is(":visible")) {
//            $(".answer").slideUp("slow");
//            if ($("#J_media_div").attr("media_type") == 1 || $("#J_media_div").attr("media_type") == 2) {
//                $("#J_media_div").show();
//            } else if ($("#J_media_div").attr("media_type") == 0 || $("#J_media_div").attr("media_type") == 'null') {
//            	$(".J_player").show();
//            }
//           
//        } else {
//            if ($("#J_media_div").attr("media_type") == 1 || $("#J_media_div").attr("media_type") == 2) {
//                $("#J_media_div").hide();
//            } else if ($("#J_media_div").attr("media_type") == 0 || $("#J_media_div").attr("media_type") == 'null') {
//            	$(".J_player").hide();
//            }
//            $(".answer").slideDown("slow");
//        }

        $(this).toggleClass("current");
        if ($(".answer").is(":visible")) {
            $(".answer").slideUp("slow", function() { //这里收起后显示
                if ($("#J_media_div").attr("media_type") == 1 || $("#J_media_div").attr("media_type") == 2) {
                    $("#J_media_div").css({'display': '', 'position': '', 'left': ''}).show();
                } else if ($("#J_media_div").attr("media_type") == 4) {
                	$("#J_media_swfobject_div").show();
                } else if ($("#J_media_div").attr("data_isaboutvideo") == 1) {
                    $(".J_player").show();
                }
            });
        } else { //这里先隐藏后展开
            if ($("#J_media_div").attr("media_type") == 1 || $("#J_media_div").attr("media_type") == 2) {
                $("#J_media_div").css({'display': 'block', 'position': 'absolute', 'left': '-9999px'}).hide();
            } else if ($("#J_media_div").attr("media_type") == 4) {
            	$("#J_media_swfobject_div").hide();
            }  else if ($("#J_media_div").attr("data_isaboutvideo") == 1) {
                $(".J_player").hide();
                $('#J_media_div').html('');
            }
            $(".answer").slideDown("slow");
        }
    });

    //科目点击事件
    // bindObjectClickEvent();
    $('.kecheng li').live('click', function() {

        if ($(this).hasClass("grey")) {

            alert("抱歉，该科目不存在试题！");
            return false;
        } else if ($(this).hasClass("current")) {

            var top = $(this).offset().top - 50;
            var left = $(this).offset().left - 10;
            $.messager.show({
                msg: "<span  class='messager_span'>您已在科目: " + $(this).text() + "</span>",
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
        //$(this).addClass("current").siblings("li").removeClass("current");
        requestQuestion("object", $(this));

    });
    //等级点击事件
    //bindLevelClickEvent();
    $(".grade li").live('click', function() {

        if ($(this).hasClass("grey")) {

            alert("抱歉，该等级不存在试题！");
            return false;
        } else if ($(this).hasClass("current")) {

            var top = $(this).offset().top - 50;
            var left = $(this).offset().left - 10;
            $.messager.show({
                msg: "<span  class='messager_span'>您已在等级: " + $(this).text() + "</span>",
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

        requestQuestion("level", $(this));

        //$(this).addClass("current").siblings("li").removeClass("current");
    });

    //升级事件
    //bindLevelUpEvent();
    $("#J_levelUpButton").live('click', function() {
        var next_level_li;
        //已是最高级
        if (($(".grade .current").index() + 1) == $(".grade li").size()) {
            if ($(".kecheng .current").index() + 1 == $(".kecheng li").size()) {
                $(".kecheng .current").prevAll("li").each(function() {
                    if (!$(this).hasClass("not_allowed") && typeof next_level_li == "undefined") {
                        next_level_li = $(this);
                    }
                });
            } else {
                $(".kecheng .current").nextAll("li").each(function() {
                    if (!$(this).hasClass("not_allowed") && typeof next_level_li == "undefined") {
                        next_level_li = $(this);
                    }
                });
            }
        } else {
            $(".grade .current").nextAll("li").each(function() {
                if (!$(this).hasClass("not_allowed") && typeof next_level_li == "undefined") {
                    next_level_li = $(this);
                }
            });
        }
        if (typeof next_level_li != "undefined") {
            next_level_li.click();
        } else {
            var top = $("#J_levelUpButton").offset().top - 50;
            var left = $("#J_levelUpButton").offset().left - 35;
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
    });

    //降级事件
    //bindLevelDownEvent();
    $("#J_levelDownButton").live('click', function() {
        var prev_level_li;
        $(".grade .current").prevAll("li").each(function() {
            if (!$(this).hasClass("not_allowed") && typeof prev_level_li == "undefined") {
                prev_level_li = $(this);
            }
        })
        if (typeof prev_level_li != "undefined") {
            prev_level_li.click();
        } else {
            var top = $(this).offset().top - 50;
            var left = $(this).offset().left - 35;
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
    });

    //
    //英音美音点击事件
    $(".menuleft ul li.class1").click(function() {
        if ($(this).hasClass("grey")) {
            alert("抱歉，该类不存在试题！");
            return false;
        } else if ($(this).hasClass("current")) {
            var top = $(".menuleft").offset().top - 50;
            var left = $(".menuleft").offset().left - 10;
            $.messager.show({
                msg: "<span  class='messager_span'>您已在" + trim_all($(this).children("span").text()) + "状态</span>",
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
        //$(this).addClass("current").siblings("li.class1").removeClass("current");
        requestQuestion("category", $(this));
    });
    //
    //说力听力点击事件
    $(".menuleft ul li.class2").click(function() {
        if ($(this).hasClass("grey")) {
            alert("抱歉，该类不存在试题！");
            return false;
        } else if ($(this).hasClass("current")) {
            var top = $(".menuleft").offset().top - 50;
            var left = $(".menuleft").offset().left - 10;
            $.messager.show({
                msg: "<span  class='messager_span'>您已在" + trim_all($(this).children("span").text()) + "状态</span>",
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
        $(this).addClass("current").siblings("li.class2").removeClass("current");
        requestQuestion("category");
    });
    //
    //视频音频点击事件
    $(".menuleft ul li.class3").click(function() {
        if ($(this).hasClass("grey")) {
            alert("抱歉，该类不存在试题！");
            return false;
        } else if ($(this).hasClass("current")) {
            var top = $(".menuleft").offset().top - 50;
            var left = $(".menuleft").offset().left - 10;
            $.messager.show({
                msg: "<span  class='messager_span'>您已在" + trim_all($(this).children("span").text()) + "状态</span>",
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
        $(this).addClass("current").siblings("li.class3").removeClass("current");
        requestQuestion("category");
    });

    $("#J_nextQuestion").click(function() {
        requestQuestion("quick_select_next");
    })
    $("#J_prevQuestion").click(function() {
        requestQuestion("quick_select_prev");
    })

    /** $视频播放 **/
    $('.J_player').click(function() {
        var isAboutVideo = $('#J_media_div').attr('data_isAboutVideo');
        if (isAboutVideo == 1) {
//            $(this).hide();

            var media_url = $('#J_media_div').attr('data_media_url');
            var videoStr = '';
            if (media_url) {
                videoStr += '<object id="J_media_object" height="100%" width="100%" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
                videoStr += '<param name="wmode" value="transparent">';
                videoStr += '<param name="movie" value="' + media_url + '">';
                videoStr += '<embed name="swf" height="' + '100%' + '" width="100%" play="true" type="application/x-shockwave-flash" allowfullscreen="true" wmode="transparent" src="' + media_url + '">';
                videoStr += '</object>';
            } else {
                videoStr += '<span>视频无法加载，刷新重试！</span>'
            }
            $('#J_media_div').html(videoStr);
            checkObjectLoaded();
        }
        return false;
    });
    /** 视频播放$ **/

    /** $用户反馈 **/
    $('.J_feedback').live('click', function() {

        var question_id = $("#J_questionId").text();
        var type = 0;	//0=>视频错误  1=>建议
        var media_html = $('.video_div').html();
        var tip_type = $(this).attr('data-type');

        $.messager.show({
            msg: "<span class='messager_span'>感谢您的反馈,我们会尽快处理！</span>",
            showType: 'fade',
            width: 215,
            height: 45,
            timeout: 2000,
            style: {
                left: '75%',
                top: '0'
            }
        });

        $.ajax({
            url: URL + '/feedback',
            type: 'POST',
            dataType: 'json',
            data: {
                'type': type,
                'question_id': question_id,
                'media_html': media_html
            },
            success: function(msg) {

            }
        });
        
        if (tip_type == 'video_tip') {
        	requestQuestion("quick_select_next");
        }
        
        return false;
    });
    /** 用户反馈$ **/

    bindMediaTextClickEvent("disable");
    bindOptionClickEvent();
    trimWhiteSpace();
});

/**
 * 请求题目
 * 响应分类、科目、等级以及上下题的点击，最终目的为请求题目。
 * @param {string} type [请求类型，大类为category，科目为object，等级为level,上一题下一题为quick_select_prev和quick_select_next
 * @returns {void}
 * @author Adam $date2013-07-26$
 */
function requestQuestion(type, clickObject) {
    //如果有题目正在请求，中断此次请求并提示（修改为abort ajax）
    if (typeof ajaxRequest != "undefined") {
        var top = $("#J_nextQuestion").offset().top + 50;
        var left = $("#J_nextQuestion").offset().left - 45;
        $.messager.show({
            msg: "<span class='messager_span'>题目正在加载，请稍后...</span>",
            showType: 'fade',
            width: 175,
            height: 45,
            timeout: 4000,
            style: {
                left: left,
                top: top
            }
        });
        //return false;
        ajaxRequest.abort();
    }

    var voice = $(".voice.current").attr("value");
    var target = $(".target.current").attr("value");
    var pattern = $(".pattern.current").attr("value");
    var object = $(".kecheng .current").attr("value");
    var level = $(".grade .current").attr("value");

    if (type == 'level') {
        level = clickObject.attr("value");
    } else if (type == 'category') {
        voice = clickObject.attr("value");
    } else if (type == 'object') {
        object = clickObject.attr("value");
    }

    var now_question_id = $("#J_questionId").text();
    if ($(".answer").is(":visible")) {
        $("#J_answerButton").click();
    }
    layer_div("show");
    ajaxRequest = $.ajax({
        url: URL + '/ajax_get_question',
        data: {
            'voice': voice,
            'target': target,
            'pattern': pattern,
            'object': object,
            'level': level,
            'type': type,
            'now_question_id': now_question_id
        },
        type: 'POST',
        dataType: 'json',
        cache: false,
        success: function(msg) {

            if (msg) {
                var data = msg.data;
                if (data == null) {

                    $.messager.show({
                        msg: "<span  class='messager_span'>该题目不存在，请重试...</span>",
                        showType: 'fade',
                        width: 180,
                        height: 45,
                        timeout: 3000,
                        style: {
                            left: '50%',
                            top: '4%'
                        }
                    });

                    ajaxRequest = undefined;
                    return false;
                }

                if (data.question && data.question.tested) {
                    var top = $("#J_nextQuestion").offset().top - 50;
                    var left = $("#J_nextQuestion").offset().left - 32;
                    $.messager.show({
                        msg: "<span class='messager_span'>暂无新题，升级吧!</span>",
                        showType: 'fade',
                        width: 150,
                        height: 45,
                        timeout: 3000,
                        style: {
                            left: left,
                            top: top
                        }
                    });
                }
                //
                //科目为空
                if (data['object_info'] == null) {
                    data['object_info'] = new Array();
                    data['object_info']['id'] = 0;
                }
                //更新科目列表
                var object_list = data.object_list;
                if (object_list != null) {
                    var str = '';
                    for (var i = 0; i < object_list.length; i++) {
                        str += '<li value="' + object_list[i]['id'] + '"';
                        if (data['object_info']['id'] == object_list[i]['id']) {
                            str += ' class="current" ';
                        } else {
                            if (object_list[i]['question_num'] == 0) {
                                str += ' class="grey not_allowed" ';
                            }
                        }
                        str += '><span>' + object_list[i]['name'] + '</span></li>';
                    }
                    $(".kecheng").html(str);
                    if ($(".kecheng .current").size() < 1) {
                        $(".kecheng li").not(".grey").first().addClass("current");
                    }
                }
                //
                //等级为空
                if (data['level_info'] == null) {
                    data['level_info'] = new Array();
                    data['level_info']['id'] = 0;
                }
                //更新等级列表
                var level_list = data.level_list;
                if (level_list != null) {
                    var str = '';
                    for (var i = 0; i < level_list.length; i++) {
                        str += '<li value="' + level_list[i]['id'] + '"';
                        if (data['level_info']['id'] == level_list[i]['id']) {
                            str += ' class="current" ';
                        } else {
                            if (level_list[i]['question_num'] == 0) {
                                str += ' class="grey not_allowed" ';
                            }
                        }
                        str += '><span>' + level_list[i]['name'] + '</span></li>';
                    }
                    $(".grade").html(str);
                    if ($(".grade .current").size() < 1) {
                        $(".grade li").not(".grey").first().addClass("current");
                    }
                    $(".grade li:eq(13)").css("margin-left", level_margin_index + "px");
                }
                //
                //更新题目
                var question = data.question;
                if (question.content == null) {
                    $(".answertitle").text("");
                } else {
                    $(".answertitle").text(question.content);
                }
                $("#J_questionId").text(question.id);
                $("#J_textButton").attr("media_text_url", question.media_text_url);
                bindMediaTextClickEvent("disable");//点击选项后才能查看文本
                updateOption(question.option);
                //
                if (data['user_count_info'] == null || data['user_count_info']['right_num'] == null) {
                    data['user_count_info']['right_num'] = 0;
                }
                
                //是否已经满10题，是则设置下一题自动升级
                if (data['user_count_info']['right_num'] >= 10) {
                    next_question_lvlup = true;
                } else {
                    next_question_lvlup = false;
                }
                if(next_question_lvlup){
                    //
                    //提示可以升级了
                    var top = $(".videoplay").offset().top - 20;
                    var left = $(".videoplay").offset().left + 210;
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
                
                $("#J_currentRice").text(data['user_count_info']['right_num'] * 100);
                $("#J_riceDiv").removeClass().addClass("rice_" + data['user_count_info']['right_num'] * 100);
                //
                /** $视频播放 **/
                var videoStr = '';
                $('#J_media_div').html(videoStr);
                
                if (question.media || question.media_url) {
                
	                if (question.isAboutVideo != 1) {
	                    if (question.media_type == 1) {
	                        videoStr = question.media;
	                    } else if (question.media_type == 2) {
	                        videoStr = '<iframe class="media_iframe" src="' + question.media + '" width="100%" height="100%" scrolling="no" frameborder="0">';
	                    } else if (question.media_type == 3) {
	                    	
	                    	$('#J_media_div').html('<div id="J_media_swfobject_div"></div>');
	                    	
	                        var swfUrl = 'http://www.kizphonics.com/wp-content/uploads/jw-player-plugin-for-wordpress/player/player.swf';
	                        var version = '10.2.0';
	                        var params = {
	                            quality: "high",
	                            wmode: "opaque",
	                            scale: "noscale",
	                            align: "left",
	                            allowFullScreen: "true",
	                            allowScriptAccess: "always",
	                            bgColor: "#000000"
	                        };
	
	                        swfobject.embedSWF(swfUrl, "J_media_swfobject_div", "100%", "100%", version, "/swf/playerProductInstall.swf", question.media, params);
	
	                    } else if (question.media_type == 4) {
	                    	$('#J_media_div').html('<div id="J_media_swfobject_div" style="height:'+media_height+'px;width:'+media_width+'px;"></div>');
	                    	
	                    	flowplayer("J_media_swfobject_div", "http://releases.flowplayer.org/swf/flowplayer-3.2.16.swf", {playlist:[question.media_img_url,{url: question.media,autoPlay: false}]});

	                    } else {
	
	                        videoStr += '<object id="J_media_object" height="100%" width="100%" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
	                        videoStr += '<param name="wmode" value="transparent">';
	                        videoStr += '<param name="movie" value="' + question.media_url + '">';
	                        videoStr += '<embed name="swf" menu="true" height="100%" width="100%" play="false" type="application/x-shockwave-flash" allowfullscreen="true" wmode="transparent" src="' + question.media_url + '">';
	                        videoStr += '</object>';
	                    }
	                }
                } else {
                	
                	 videoStr += '<p class="video_tip_error">该视频无法正常播放了，<a href="javascript:void(0);" class="J_feedback" data-type="video_tip">请反馈给我们</a>，谢谢!</p>';
                }
                
                if (question.isAboutVideo) {
                    $('#J_media_img').attr('src', question.media_img_url);
                    $('#J_media_div').css('visibility', 'hidden');

                    $('.J_player').css('display', 'block');

                } else {
                    $('.J_player').css('display', 'none');

                    $('#J_media_div').css({
                        'height': media_height,
                        'width': media_width,
                        'margin': media_marin,
                        'visibility': ''
                    });
                }
                
                if ($("#J_media_div").css('display') == 'none') {
                    $("#J_media_div").css({'display': '', 'position': '', 'left': ''}).show();
                }

                if (question.isAboutVideo == 1) {
                    $('#J_media_div').attr('data_media_url', question.media_url);
                } else {
                	 $('#J_media_div').attr('data_media_url', '');
                }
                $('#J_media_div').attr('media_type', question.media_type);

                $('#J_media_div').attr('data_isAboutVideo', question.isAboutVideo);
                
                if (videoStr) {
                	$('#J_media_div').html(videoStr);
                }

                /** 视频播放$ **/
                trimWhiteSpace();
                layer_div("hide");

                if (type == 'level' || type == 'object') {
                    clickObject.addClass("current").siblings("li").removeClass("current");
                } else if (type == 'category') {
                    clickObject.addClass("current").siblings("li.class1").removeClass("current");
                }

                ajaxRequest = undefined;
                return true;
            } else {

                ajaxRequest = undefined;
                return false;
            }
        },
        error: function() {

            ajaxRequest = undefined;
            return false;
        }
    });
}

/**
 * 判断是否为ajax请求状态中
 * 
 * @returns {Boolean} true：ajax发送中 false：暂无ajax请求
 */
function isAjaxRequest() {

    return (typeof ajaxRequest != 'undefined') && (typeof ajaxRequest == 'object');
}

//
/**
 * 绑定选项点击事件
 * @returns {void} 
 * @author Adam $date2013-07-26$            
 */
function bindOptionClickEvent() {
    $(".J_option").unbind("click");
    $(".J_option").click(function() {
        var target = $(this);
        var select_option = $(this).attr("value");
        var object = $(".kecheng .current").attr("value");
        $.post(URL + "/answer_question", {
            question_id: $("#J_questionId").text(),
            object: object,
            select_option: select_option
        }, function(msg) {
            if (msg.status) {
                var data = msg.data;
                var user_count_info = data.user_count_info
                if (user_count_info == null || user_count_info.right_num == null) {
                    user_count_info.right_num = 0;
                }
//                            $("#J_rightNum").text(user_count_info.right_num);
                $("#J_currentRice").text(user_count_info.right_num * 100);
                $("#J_riceDiv").removeClass().addClass("rice_" + user_count_info.right_num * 100);
                //
                var english_user_info = data.english_user_info;
                if (english_user_info == null) {
                    english_user_info.total_rice = 0;
                }
                $("#J_totalRice").text(english_user_info.total_rice);

                if (data.level_up) {
                    var top = $("#question_content").offset().top + 10;
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
                //最佳科目和等级
//                            if(english_user_info!=null){
//                                if(english_user_info.best_object!=0&&english_user_info.best_level!=0&&english_user_info.best_object!=null&&english_user_info.best_level!=null){
//                                    $("#J_best_level").text(english_user_info.best_object_name+"/"+english_user_info.best_level_name);
//                                }
//                            }
                answer = data.question_info.answer;
                if (select_option == answer) {
                    //target.children(".J_optionContent").children("span").removeClass("gc").addClass("right");
                    target.children(".gc").removeClass("gc").addClass("right");
                } else {
                    target.children(".gc").removeClass("gc").addClass("wrong");

                    setTimeout(function() {
                        $('#J_option_' + answer).children(".gc").removeClass("gc").addClass("right");
                    }, 1000);
                    //target.children(".J_optionContent").children("span").removeClass("gc").addClass("wrong");
                    //setTimeout("$(\".J_option[value='\"+answer+\"']\").children(\".J_optionContent\").children(\"span\").removeClass(\"gc\").addClass(\"right\");", 1000);
                }
                $(".J_option").unbind("click");
                //更新排行榜
                var now_cat = $(".ranklist .J_now_Object").attr("value");
                $(".ranklist .ranklist-hd ul a[type='" + now_cat + "']").click();
                //
                //根据用户上次答题情况进行提示操作
                var english_user_record = data.english_user_record;
                //做对的再做对
                if (english_user_record.is_right == 1 && english_user_record.right_num >= 2) {
                    //无新题自动升级
                    var content = "";
                    if (data.question_info.untested_num == 0) {
                        next_question_lvlup = true;
                        content = "本题已做对" + english_user_record.right_num + "次，升级吧";
                    } else {
                        next_question_lvlup = false;
                        content = "本题已做对" + english_user_record.right_num + "次，换新题吧";
                    }
                    var top = $(".videoplay").offset().top - 20;
                    var left = $(".videoplay").offset().left + 210;
                    $.messager.show({
                        msg: "<span  class='messager_span'>" + content + "</span>",
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
                } else if (english_user_record.is_right == 0 && english_user_record.error_num >= 2) {
                	var top = $(".videoplay").offset().top - 20;
                    var left = $(".videoplay").offset().left + 230;
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
        }, "json");
        bindMediaTextClickEvent();
        $(".J_option").unbind();
    })
}

/**
 * 更新选项
 * @param {array} option [选项数组]
 * @returns {void}
 * @author Adam $date2013-07-26$
 */
function updateOption(option) {
    var str = "";
    
    $('.answer').hide();
    
    if (option != null) {
        for (var i = 0; i < option.length; i++) {
            str += '<p class="J_option" id="J_option_' + option[i]['id'] + '" value="' + option[i]['id'] + '"><span class="gc"></span>' + '<span class="ricenumber">';
            if (i == 0) {
                str += "A";
            } else if (i == 1) {
                str += "B";
            } else if (i == 2) {
                str += "C";
            } else if (i == 3) {
                str += "D";
            }
            str += "</span><span class='J_optionContent'>" + option[i]['content'] + '</span></p>';
        }
    }
    $(".J_option").remove();
    $(".answertitle").after(str);
//                bindOptionHoverEvent();
    bindOptionClickEvent();//点击选项
}
//
/*
 * 绑定文本点击事件
 * @param {string} type [操作]
 * @returns {undefined}
 */
function bindMediaTextClickEvent(type) {
    $("#J_textButton").unbind("click");
    if (type != "disable") {
        $("#J_textButton").click(function() {
            var url = trim($(this).attr("media_text_url"));
            if (url == "") {
                var top = $("#J_answerButton").offset().top - 5;
                var left = $("#J_answerButton").offset().left + 70;
                $.messager.show({
                    msg: "<span class='messager_span'>对不起，文本不存在!</span>",
                    showType: 'fade',
                    width: 155,
                    height: 45,
                    timeout: 3000,
                    style: {
                        left: left,
                        top: top
                    }
                });
                return false;
            }
            window.open(url, "_blank");
        })
    } else {
        $("#J_textButton").click(function() {
            var top = $("#J_answerButton").offset().top - 50;
            var left = $("#J_answerButton").offset().left - 38;
            $.messager.show({
                msg: "<span class='messager_span'>答题后才能查看文本！</span>",
                showType: 'fade',
                width: 155,
                height: 45,
                timeout: 3000,
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
 * 操作透明阻挡层的方法
 * @param {string} type [显示还是隐藏]
 * @returns {void}
 * @author Adam $date2013-07-20$
 */
function layer_div(type) {
    var layer_div = $(".layer_div_img");
    if (type == "show") {
        if (layer_div.hasClass("layer_div_img")) {
            layer_div.show();
        } else {
            layer_div = $("<img class='layer_div_img' src='" + PUBLIC + "/English/images/loading.gif' />");
            $("body").append(layer_div);
        }
        var top = window.screen.availHeight * 0.4;
        //var left = window.screen.availWidth * 0.5 - 95;
        var left = '48%';	//用于在播放器居中显示
        layer_div.css("position", "absolute").css("top", top).css("left", left).css("z-index", "9999");

    } else {
        layer_div.hide();
    }
}

//
/**
 * 去除试题内容以及选项的内容中的空格
 * @returns {void}
 * @author Adam $date2013-07-26$
 */
function trimWhiteSpace() {
    var question_content = trim($(".answertitle").html());
    $(".answertitle").html(question_content);
    $(".J_option").each(function() {
        var option = trim($(this).children(".J_optionContent").html());
        $(this).children(".J_optionContent").html(option);
    })
}
/**
 * 去除前后空格
 * @param {string} str
 * @returns {string}
 * @author Adam $date2013-07-27$
 */
function trim(str) {
    var space_reg_C = /&#12288;+/g;
    var space_reg_A = /&nbsp;+/g;
    var space_reg_B = /(^\s+)|(\s+$)/g;
    str = str.replace(space_reg_C, " ");
    str = str.replace(space_reg_A, " ");
    str = str.replace(space_reg_B, "");
    return str;
}
/**
 * 去除全部空格
 * @param {string} str 需要处理的字符串
 * @returns {string}
 * @author Adam $date2013-07-27$
 */
function trim_all(str) {
    var space_reg_C = /&#12288;+/g;
    var space_reg_A = /&nbsp;+/g;
    var space_reg_B = /(\s+)/g;
    str = str.replace(space_reg_C, " ");
    str = str.replace(space_reg_A, " ");
    str = str.replace(space_reg_B, "");
    return str;
}
// 设为首页
function setHome(url) {
    if (document.all) {
        document.body.style.behavior = 'url(#default#homepage)';
        document.body.setHomePage(url);
    }
    else if (window.sidebar) {
        if (window.netscape) {
            try {
                netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
            } catch (e) {
                alert("该操作被浏览器拒绝, 如果想启用该功能, 请在地址栏内输入 about:config, 然后将项 signed.applets.codebase_principal_support 值改为 true.");
            }
        }
        //
        if (window.confirm("你确定要设置" + url + "为首页吗？") == 1) {
            var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);
            prefs.setCharPref('browser.startup.homepage', url);
        }
    }
}

function checkObjectLoaded() {
    var load_percent = 0;
    if ($.browser.msie) {
        load_percent = $("#J_media_object")[0].PercentLoaded();
    } else {
        load_percent = $("embed")[0].PercentLoaded();
    }
    layer_div("show");
    if (load_percent == 100) {
        $('.J_player').hide();
        $('#J_media_div').css({
            'height': media_height,
            'width': media_width,
            'visibility': '',
            'margin': media_marin
        });
        layer_div("hide");
    } else {
        setTimeout('checkObjectLoaded()', 100);
    }
}