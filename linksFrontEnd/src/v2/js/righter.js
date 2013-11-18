var PUBLIC = $("#js_PUBLIC").val();
var APP = $("#js_APP").val();
var timer;
$(window).load(function() {
    /* 右边目录 */
    if ($(".right_sider")[0]) {
        // 定位
        if ($(".cont_sx")[0]) {
            var left = ($(".home_cont_left").width() - $("#rid_tip").width()) / 2;
            $("#rid_tip").css("left", left);
        }
        else {
            $("#right_sider").css("top", "9px");
        }
        // 滚动事件
        $(window).scroll(function() {
            var scrollTop = $(window).scrollTop();
            var isIE6 = /msie 6/i.test(navigator.userAgent);
            var scrtop;
            if ($("#annt").html()) {
                scrtop = 210
            }
            else {
                scrtop = 136
            }
            if (isIE6) {
                if (scrollTop > scrtop) {
                    $("#right_sider").css("top", scrollTop - scrtop);
                }
                else {
                    if ($(".cont_sx")[0]) {
                        $("#right_sider").css("top", 39);
                    }
                    else {
                        $("#right_sider").css("top", 9);
                    }
                }
            }
            else {
                if (scrollTop > scrtop) {
                    if ($("#left_sider")[0]) {
                        $("#left_sider").css("position", "fixed");
                    }
                    else {
                        $("#right_sider").css("position", "fixed");
                        $("#right_sider").css("top", 0);
                        $("#right_sider").css("left", $(".container").offset().left + 780);
                    }
                }
                else {
                    if ($("#left_sider")[0]) {
                        $("#left_sider").css("position", "absolute");
                    }
                    else {
                        $("#right_sider").css("position", "absolute");
                        if ($(".cont_sx")[0]) {
                            $("#right_sider").css("top", 39);
                        }
                        else {
                            $("#right_sider").css("top", 9);
                        }
                        $("#right_sider").css("left", 780);
                    }
                }
            }
        });
        // 语言切换
        $(".sider_tit span").hover(function() {
            if (!$(this).hasClass("on")) {
                $(this).children("font").css("color", "#FF0000");
            }
        }, function() {
            if (!$(this).hasClass("on")) {
                $(this).children("font").css("color", "#666666");
            }
        })
        $("#sider_tit02").click(function() {
            if ($(".home_cont_left")[0] && $(".cont_sx")[0]) {
                $("#sider_tit01").css("border-right", "none").removeClass("on");
                $(this).css("border-left", "1px solid #e2e2e2").addClass("on");
                $(this).siblings("span").children("font").css("color", "#666666");
//            $("#sider_cont01").hide();
//            $("#sider_cont02").show();
                $("#cid").val($("#rid").val());
                $("#lan").val(2);
                $("#frm_links input[name='cid']").val($("#rid").val());
                $("#frm_links input[name='lan']").val(2);
                changeCat();
            } else {
                window.location.href = APP + "Index/nav/cid/" + $("#rid").val() + "/lan/2";
            }
        });
        $("#sider_tit01").click(function() {
            if ($(".home_cont_left")[0] && $(".cont_sx")[0]) {
                $("#sider_tit02").css("border-left", "none").removeClass("on");
                $(this).css("border-right", "1px solid #e2e2e2").addClass("on");
                $(this).siblings("span").children("font").css("color", "#666666");
//            $("#sider_cont02").hide();
//            $("#sider_cont01").show();
                $("#cid").val($("#rid").val());
                $("#lan").val(1);
                $("#frm_links input[name='cid']").val($("#rid").val());
                $("#frm_links input[name='lan']").val(1);
                changeCat();
            } else {
                window.location.href = APP + "Index/nav/cid/" + $("#rid").val() + "/lan/1";
            }
        });
        // 主菜单
        $("#right_menu dl").mouseover(function() {
            $(this).css('background-position', '-34px top');
        });
        $("#right_menu dl").mouseout(function() {
            $(this).css('background-position', '0px top');
        });
        // 红点点
        var idx = parseInt($("#rid").val()) - 1;
        $("#right_pot").css('top', $("#right_menu dl").eq(idx).offset().top - $(".right_sider").offset().top + 26);
        $("#right_pot").css("display", "inline-block");

        //绑定图片轮转事件
        timer = bindNivoSliderEvent(timer);

        $("#right_menu").find("a").click(function() {
            if ($(".home_cont_left")[0] && $(".cont_sx")[0]) {
                var cid = $(this).parent("dl").attr("class").substr(13);
                $("#cid").val(cid);
                $("#rid").val(cid);
                $("#frm_links").find("input[name='cid']").val(cid);
                changeCat();
                //百度ajax统计
                _hmt.push(['_trackPageview', $(this).attr('href')]);
                return false;
            }
        })
        siderLinksBindClickEvent();
    }
});
function siderLinksBindClickEvent() {
    $(".sider_cont:visible").find("a").click(function() {
        if ($(".home_cont_left")[0] && $(".cont_sx")[0]) {
            $(this).addClass("on").siblings().removeClass("on");
            var cid = $(this).attr("id").substr(6);
            $("#cid").val(cid);
            $("#frm_links").find("input[name='cid']").val(cid);
            changeCat();
            
            //百度ajax统计
            _hmt.push(['_trackPageview', $(this).attr('href')]);
            return false;
        }
    })
}
function changeCat() {
	 var p = arguments[0] ? arguments[0] : false;
  	 var grade = arguments[1] ? arguments[1] : false;
	 var getPailie = arguments[2] ? arguments[2] : false;
    var cid = $("#cid").val();
    var lan = $("#lan").val();
    var pailie = 1;
    if (typeof($(".cont_lie_jj")[0]) == "undefined") {
        pailie = 2;
    }
	if(getPailie){
		pailie = getPailie;
	}
//    var grade = $("#frm_links input[name='grade']").val();
    // 红点点
    var idx = parseInt($("#rid").val()) - 1;
    $("#right_pot").css('top', $("#right_menu dl").eq(idx).offset().top - $(".right_sider").offset().top + 26);
//    layer_div("show");
	
    $.post(APP + "Index/ajax_get_links", {cid: cid, lan: lan, p:p, grade:grade},
    function(response) {
        if (response) {
            if (response.status) {
                var str = "";
                //小分类列表
                var cat_list = response.data.cat_list;
                if (cat_list != null) {
                    for (var i = 0; i < cat_list.length; i++) {
                        str += '<a id="sider_' + cat_list[i]['id'] + '" class="tip" title="' + cat_list[i]['cat_name'] +
                                '" href="' + APP + 'Index/nav/lan/' + cat_list[i]['flag'] + '/cid/' + cat_list[i]['id'] + '">' + cat_list[i]['cat_name'] + '</a>';
                    }
                    $(".sider_cont:visible").html(str);
                    siderLinksBindClickEvent();
                }
                //顶级分类等级信息
                var grades = response.data.grades;
                var str = "";
                if (grades != null) {
                    str = '<dl class="grades">';
                    for (var i = 0; i < grades.length; i++) {
						if(grade==i + 1){
							str += '<a class="a_grade on" grade="' + (i + 1) + '">' + grades[i] + '</a>&nbsp;';
						}else{
							str += '<a class="a_grade" grade="' + (i + 1) + '">' + grades[i] + '</a>&nbsp;';	
						}
                        
                    }
					if(grade>0){
                    	str += '&nbsp;<a id="cspx" >初始排序</a>&nbsp;';
					}else{
						 str += '&nbsp;<a id="cspx" class="on">初始排序</a>&nbsp;';
					}
                    str += '<dl>';
                }
                $(".grades").remove();
                $("#rid_tip").before(str);
                //顶级分类信息
                var root_cat_info = response.data.root_cat_info;
                if (root_cat_info != null) {
                    $("#rid_tip").text(root_cat_info.intro);
                }
                //顶级分类图片
                var root_cat_pic = response.data.cat_pic;
                if (root_cat_pic != null) {
                    str = '<div id="slider" class="nivoSlider">';
                    for (var i = 0; i < root_cat_pic.length; i++) {
                        str += ' <a class="fancybox  nivo-imageLink" data-fancybox-group="gallery"' +
                                ' href="' + PUBLIC + '/Uploads/CatPics/' + root_cat_pic[i]['pic_big'] + '" title="' + root_cat_pic[i]['name'] +
                                '"><img src="' + PUBLIC + '/Uploads/CatPics/' + root_cat_pic[i]['pic'] + '" alt="' + root_cat_pic[i]['name'] + '" /></a>';
                    }
                    str += '</div>';
                    $(".nivo-controlNav").remove();
                    $("#slider").remove();
                    $("#sub_menu_pic").html(str);
                    timer = bindNivoSliderEvent(timer);
                }
                //链接列表
                var links = response.data.links;
                str = "";
                
                var link = '';
                
                //给TED下的链接添加ted_link样式root_cat_info.id
                if (root_cat_info.id == 5) {
                	$('.home_cont_left').addClass('ted_link');
                } else {
                	$('.home_cont_left').removeClass('ted_link');
                }
                
                if (links != null) {
                    for (var i = 0; i < links.length; i++) {
                    	
                    	link = (root_cat_info.id == 5) ? ('Detail?id=' + links[i]['id']) : ('Link?url=' + links[i]['link']);
                    	
                        if (pailie == 1 && i % 2 == 1) {
                            str += '<li style="float: right;">';
                        } else {
                            str += '<li>';
                        }
                        if (links[i]['logo'] != '') {
                            str += '<div class="cont_lie_logoleft"><div class="cont_lie_logo">' +
                                    '<a target="_blank" href="' + APP + link + '">' +
                                    '<img src="' + PUBLIC + '/Uploads/Links/' + links[i]['logo'] + '"></a></div></div>';
                            str += '<div class="cont_font cont_font_logo">';
                        } else {
                            str += '<div class="cont_font">';
                        }
                        if (pailie == 1) {
                            str += '<dl class="cont_tit" title="' + links[i]['linkTitle'] + '">' +
                                    '<dd><a target="_blank" href="' + APP + link + '">' + links[i]['title'] +
                                    '</a>';
                            if (links[i]['recommended'] != '') {
                                str += ' 　<span>' + links[i]['recommended'] + ' 推荐</span>';
                            }
                            str += '</dd></dl><dl class="cont_botfont">' + links[i]['sintro'] + '</dl>';
//                        if(links[i]['more']==1){
                            str += '<dl class="dl_more"><a class="newWin" url="' + APP + 'Detail?id=' + links[i]['id'] + '">> 更多</a></dl>'
//                        }
                            str += '<div class="clr"></div><dl class="cont_btns"><div class="sc">' +
                                    '<a class="cont_fontright btn_collect" link="' + links[i]['link'] + '" lnk_id="' + links[i]['id'] + '">收藏</a>&nbsp;<label>';
                            if (links[i]['collect_num'] > 0) {
                                str += links[i]['collect_num'];
                            }
                            str += '</label></div>';
                            str += '<div class="cai" lnk_id="' + links[i]['id'] + '">' + links[i]['cai'] + '</div>' +
                                    '<div class="ding" lnk_id="' + links[i]['id'] + '">' + links[i]['ding'] + '</div>';
                            str += '<div class="lst_dt">'
                            if (links[i]['grade_name'] != null) {
                                str += links[i]['grade_name'];
                            }
                            str += '</div></dl>';
                        } else {
                            str += '<dl class="cont_tit"><div class="tlt_left"><a target="_blank" href="' + APP + link + '">' + links[i]['title'] +
                                    '</a>';
                            if (links[i]['recommended'] != '') {
                                str += ' 　<span>' + links[i]['recommended'] + ' 推荐</span>';
                            }
                            str += '</div><div class="sc">' +
                                    '<a class="cont_fontright btn_collect" link="' + links[i]['link'] + '" lnk_id="' + links[i]['id'] + '">收藏</a>&nbsp;<label>';
                            if (links[i]['collect_num'] > 0) {
                                str += links[i]['collect_num'];
                            }
                            str += '</label></div>';
                            str += '<div class="cai" lnk_id="' + links[i]['id'] + '">' + links[i]['cai'] + '</div>' +
                                    '<div class="ding" lnk_id="' + links[i]['id'] + '">' + links[i]['ding'] + '</div>';
                            str += '<div class="lst_dt">'
                            if (links[i]['grade_name'] != null) {
                                str += links[i]['grade_name'];
                            }
                            str += '</div></dl><div class="cont_botfont">' + links[i]['sintro'] +
                                    '<a class="newWin" url="' + APP + 'Detail?id=' + links[i]['id'] + '">> 更多</a></div>';
                        }
                        str += '</div>';
                        if (pailie == 2) {
                            str += '<div class="clr"></div>';
                        }
                        str += '</li>';
                        if (pailie == 1) {
                            $(".cont_lie_jj").html(str);
                        } else {
                            $(".cont_lie").html(str);
                        }
                        bindLinksDingCaiEvent();
                        bindLinksCollectEvent();
                        // 弹出页
                        $(".newWin").unbind("click");
                        $(".newWin").click(function() {
                            myWinOpen($(this).attr('url'), '', '');
                        });
                        var page = response.data.page;
                        if (page != null) {
                            $(".page_b").html(page);
                        }
                    }
                }
                initIndexEvent();
            }
        }
//        layer_div("hide");
    }, "json")
}
function bindNivoSliderEvent(timer) {
    clearTimeout(timer);
    timer = window.setTimeout(function() {
        //
        $('#slider').nivoSlider({
            'pauseTime': $("#pauseTime").val()
        });
        // 目录图片
        $(".fancybox").fancybox({
            helpers: {
                title: {
                    type: 'outside'
                },
                overlay: {
                    css: {
                        'background': 'rgba(0,0,0,0.8)'
                    }
                }
            }
        });
    }, 1000);
    return timer;
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