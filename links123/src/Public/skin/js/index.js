$(function() {
    initIndexEvent();

    //公告
    if ($('#annt')[0] && $('#annt li').size() > 1) {
        $('#annt').kxbdSuperMarquee({
            isEqual: false,
            distance: 30,
            time: 4,
            //btnGo:{up:'#goU',down:'#goD'},
            direction: 'up'
        });
    }
    
	
    //
    if ($("#myarea")[0]) {
        //
        Shadowbox.init();
        //
        function openMyArea() {
            Shadowbox.open({
                player: "html",
                title: "自留地",
                content: $("#pop_box").html(),
                height: 200,
                width: 810,
                options: {
                    enableKeys: false,
                    onFinish: function() {
                        $("#sb-container #ul_myarea").sortable({
                            placeholder: "ui-state-highlight"
                        });
                        $("#sb-container #ul_myarea").sortable({
                            stop: function(event, ui) {
                                var xx = $("#sb-container #ul_myarea").sortable("serialize");
                                //更新我的地盘排序
                                $.ajax({
                                    type: 'POST',
                                    url: URL + '/sortArealist',
                                    data: xx,
                                    success: function(msg) {
                                        if (msg) {
                                            msg = jQuery.parseJSON(msg);
                                            if (msg['status'] == 'ok') {
                                                var data = msg['data'];
                                                var hide_myarea_html = "";
                                                var myfave_html = "";
                                                for (var i = 0; i < data.length; i++) {
                                                    hide_myarea_html += '<li id="area_' + data[i]['id'] + '" myid="' + data[i]['id'] + '" url="' + data[i]['url'] + '">' + data[i]['web_name'] + '</li>';
                                                    myfave_html += '<li><a myid="' + data[i]['id'] + '" target="_blank" href="'+APP+'Index/link_out?mod=myarea&url=' + data[i]['url'] + '">' + data[i]['web_name'] + '</a></li>';
                                                }
                                                $(".div_myarea ul").html(hide_myarea_html);
                                                $("#myfave ul").html(myfave_html);
                                            }
                                        }
                                    }
                                });
                            }
                        });
                    }
                }
            });
        }
        //
        $("#myarea").click(function() {
            $.post(URL + "/count_myarea_open");
            openMyArea();
        });
        //
        $(".english_concert").click(function(){
            $.post(URL + "/count_english_open");
        })
        //
        $("#myarea").mouseover(function() {
            $("#myfave").css("background-position-y", "0px");
        });
        $("#myarea").mouseout(function() {
            $("#myfave").css("background-position-y", "-68px");
        });
        //
        $(document).on("mouseover", "input.web_name", function() {
            $(this).select();
        });
        //
        $(document).on("mouseover", "input.url", function() {
            $(this).select();
        });
        //
        $(document).on("keydown", "input.web_name", function(event) {
            if (event.keyCode == 13) {
                $(this).siblings(".url").select();
            }
        });
        //
        $(document).on("keydown", "input.url", function(event) {
            if (event.keyCode == 13) {
                $(this).siblings(".web_name").select();
                $(this).siblings(".btn_sub_myarea").trigger("click");
            }
        });
        //
        $(document).on("click", ".div_myarea ul li", function() {
            $(this).css("color", "#ffffff").css("background", 'url("' + $("#js_PUBLIC").val() + '/skin/images/link_bg_select.gif") no-repeat scroll 0 0 transparent');
            $(this).siblings("li").css("color", "#666").css("background", 'url("' + $("#js_PUBLIC").val() + '/skin/images/link_bg.gif") no-repeat scroll 0 0 transparent');
            var obj = $(this).parent("ul").siblings(".botn_ipt");
            obj.css("display", "block");
            obj.children(".web_name").val($(this).text());
            obj.children(".url").val($(this).attr("url"));
            obj.children(".myid").val($(this).attr("myid"));
            obj.children(".web_name").select();
        });
        // 更新我的地盘
        $(document).on("click", ".div_myarea .btn_sub_myarea", function() {
            var button = $(this);
            var web_name = $.trim($(this).siblings(".web_name").val());
            var url = $.trim($(this).siblings(".url").val());
            url = url.replace('http://', '');
            url = url.replace('https://', '');
            var myid = $(this).siblings(".myid").val();
            //
            $.post(URL + "/updateArealist", {
                id: myid,
                web_name: web_name,
                url: url
            },
            function(data) {
                if (data.indexOf("updateOK") >= 0) {
                    $("#sb-container #area_" + myid).css("color", "#666").css("background", 'url("' + $("#js_PUBLIC").val() + '/skin/images/link_bg.gif") no-repeat scroll 0 0 transparent');
                    //
                    $(".div_myarea ul li").each(function(index, domEle) {
                        if ($(domEle).attr("myid") == myid) {
                            $(domEle).text(web_name);
                            $(domEle).attr("url", url)
                        }
                    });
                    //
                    $("#myfave li a").each(function(index, domEle) {
                        if ($(domEle).attr("myid") == myid) {
                            $(domEle).text(web_name);
                            $(domEle).attr("href", "http://" + url);
                        }
                    });
                    //
                    var top = button.offset().top - 27;
                    $.messager.show({msg: "保存成功", showType: 'fade', width: 65, height: 26, timeout: 1000, style: {left: button.offset().left, top: top}});
                }
                else {
                    alert(data);
                }
            });
        });
        // 恢复默认
        $(document).on("click", ".btn_default", function() {
            var obj = $(this);
            $.get(URL + "/getArealistDefault",
                    function(data) {
                        if (data.indexOf("getOK") >= 0) {
                            var temp = data.split("|");
                            obj.parents(".top_btn").siblings("ul").html(temp[1]);
                            $("#myfave ul").html(temp[2]);
                        }
                        else {
                            alert(data);
                        }
                    });
        });

        /* 你的地盘你做主 */
        setYouMasterPos();
        window.onresize = setYouMasterPos;
        //
        if (getCookie("you_are_master") == "") {
            var hdlItv = window.setInterval(function() {
                $("#you_are_master").slideDown("slow");
                window.setTimeout(function() {
                    $("#you_are_master").slideUp("slow");
                }, 15000);
            }, 75000);
        }
        //
        $("#you_are_master").click(function() {
            setCookie("you_are_master", 1);
            window.clearInterval(hdlItv);
            $("#you_are_master").slideUp("slow");
            $("#myarea").trigger("click");
        });

    }

    /* 排列 */
    $(".pailie .xx").click(function() {
        $.post(URL + "/setPailie", {
            val: 2
        },
        function(data) {
            if (data.indexOf("setOK") >= 0) {
				var grade = $("#frm_links input[name='grade']").val();
				var page = $("#frm_links input[name='p']").val();
				$(".cont_lie_jj").addClass('cont_lie');
				$(".cont_lie_jj").removeClass('cont_lie_jj');
				getPage(page,grade,2);
				$(".pailie .jj").removeClass('xxon');
				$(".pailie .xx").addClass('xxon');
              //  window.location.reload();
            }
            else {
                alert(data);
            }
        });
    });
    //
    $(".pailie .jj").click(function() {
        $.post(URL + "/setPailie", {
            val: 1
        },
        function(data) {
            if (data.indexOf("setOK") >= 0) {
				var grade = $("#frm_links input[name='grade']").val();
				var page = $("#frm_links input[name='p']").val();
				$(".cont_lie").addClass('cont_lie_jj');
				$(".cont_lie").removeClass('cont_lie');
				getPage(page,grade,1);
				$(".pailie .xx").removeClass('xxon');
				$(".pailie .jj").addClass('xxon');
               // window.location.reload();
            }
            else {
                alert(data);
            }
        });
    });

    //
    if ($(".cont_lie_jj")[0]) {
        $(".cont_lie_jj li:odd").css("float", "right");
    }
    bindLinksDingCaiEvent();
    /**/
    $(".cont_pl_name").click(function() {
        alert("暂时保密");
    });

    /**/
    if (!$(".cont_sx span a")[0]) {
        $(".cont_lie dl.cont_tit dd").css("width", "400px");
    }


});
function setYouMasterPos() {
    $("#you_are_master").css('top', $("#myfave").offset().top);
    var myLeft = $("#myfave").offset().left + 648;
    $("#you_are_master").css('left', myLeft + "px");
}
function initIndexEvent() {
    // 按grade查询
    $(".a_grade").unbind();
    $(".a_grade").click(function() {
        $("#frm_links input[name='grade']").val($(this).attr("grade"));
		$("#frm_links input[name='p']").val(1);
        if ($(this).attr("grade") == '1') {
            $("#frm_links input[name='sort']").val('grade asc,sort asc');
        }
        else {
            $("#frm_links input[name='sort']").val('grade desc,sort asc');
        }
		
		getPage(1,$(this).attr("grade"));
        //$("#frm_links").submit();
    });
    // 初始排序
    $("#cspx").unbind();
    $("#cspx").click(function() {
		$("#frm_links input[name='p']").val(1);
        $("#frm_links input[name='grade']").val('');
        $("#frm_links input[name='sort']").val('category asc,sort asc');
		getPage(1,'');
       // $("#frm_links").submit();
    });
    //
	/*
    $(".page a").unbind();
    $(".page a").click(function() {
        if ($("#frm_links")[0]) {
            $("#frm_links input[name='p']").val($(this).attr("p"));
            $("#frm_links").submit();
        }
        else {
            $("#frm_search input[name='p']").val($(this).attr("p"));
            $("#frm_search").submit();
        }
    });
	*/
    /* 换页回车键响应 */
	/*
    $(".page input").unbind();
    $(".page input").keydown(function(event) {
        if (event.keyCode == 13) {
            if ($("#frm_links")[0]) {
                $("#frm_links input[name='p']").val($(this).val());
                $("#frm_links").submit();
            }
            else {
                $("#frm_search input[name='p']").val($(this).val());
                $("#frm_search").submit();
            }
        }
    });
	 */
}
function bindLinksDingCaiEvent() {
    /* 顶 */
    $(".ding").click(function() {
        var obj = $(this);
        //
        if (getCookie("dmz" + obj.attr('lnk_id')) == "1") {
            alert("您已顶过!");
            return false;
        }
        //
        $.post(URL + "/ding", {
            id: obj.attr('lnk_id')
        },
        function(data) {
            if (data.indexOf("dingOK") >= 0) {
                obj.text(parseInt(obj.text()) + 1);
                setCookie("dmz" + obj.attr('lnk_id'), "1");
            }
            else {
                alert(data);
            }
        });
    });

    /* 踩 */
    $(".cai").click(function() {
        var obj = $(this);
        //
        if (getCookie("dmz" + obj.attr('lnk_id')) == "1") {
            alert("您已踩过!");
            return false;
        }
        //
        $.post(URL + "/cai", {
            id: obj.attr('lnk_id')
        },
        function(data) {
            if (data.indexOf("caiOK") >= 0) {
                obj.text(parseInt(obj.text()) + 1);
                setCookie("dmz" + obj.attr('lnk_id'), "1");
            }
            else {
                alert(data);
            }
        });
    });
}

/*初级中级高级*/


/*异步翻页 p为要跳转的页数，g为等级*/
function getPage() {   
	var p = arguments[0] ? arguments[0] : false;
  	var g = arguments[1] ? arguments[1] : false;
	var cid = $("#frm_links input[name='cid']").val();
	var grade = '' ;
	if(g!==false){
		grade = g;
	}else{
		grade = $("#frm_links input[name='grade']").val();
	}
	 
	changeCat(p,grade);	
	$("#frm_links").find("input[name='cid']").val(cid);
	$("#frm_links").find("input[name='p']").val(p);
	$("#cid").val(cid);
	$("#grade").val(grade);
	//回到顶部代码
	//$('html').animate({scrollTop:$('html').offset().top},1500,'linear'); 
	//$("html,body").animate({scrollTop:0});
}
/*enter键翻页*/
$(document).ready(function () {				
	$(document).on("keydown", ".keyPage", function (event) { 							
		if (event.keyCode == 13) {
			var cid = $("#frm_links input[name='cid']").val();
			var grade = $("#frm_links input[name='grade']").val();
			var p = $(".page input").val();
			changeCat(p,grade);
			$("#frm_links").find("input[name='cid']").val(cid);
			$("#frm_links").find("input[name='p']").val(p);
			$("#cid").val(cid);
			$("#grade").val(grade);
			
		}
		
	});	  							  
});
