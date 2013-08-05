
$(function() {
    var newSelect = $("#J_orderTitle");
    newSelect.click(function(e) {
        if ($(this).hasClass("open")) {
            closeSelect($(this));
        } else {
            $(this).addClass("open")
            $(this).next("p").slideDown("fast");
        }
//        $(this).stop();
        e.stopPropagation();
    });

    function closeSelect(obj) {
        obj.next("p").slideUp("fast", function() {
            obj.removeClass("open");
            $(".more_object").css("display", "none");
            $("#J_moreObjectLink").show();
        });
    }
    $("body").bind("click", function() {
        closeSelect(newSelect);
    });

    $("#J_moreObjectLink").mouseover(function() {
        $(this).hide();
        $(".more_object").css("display", "block");
    })

    newSelect.next().children("a").click(function(e) {
        newSelect.text($(this).text());
        $(this).addClass("current").siblings().removeClass("current");
        getTopUserList($(this).attr("type"), $(this));
        $("#J_orderTitle").attr("value",$(this).attr("type"));
    });
})
/**
 *获取排行榜用户列表
 * @param string type [排行类型]
 * @param object target [点击的对象]
 * @return void
 * @author Adam $date2013.6.20$
 */
function getTopUserList(type, target) {
    if (type == "more_object") {
        return false;
    }
    $.post(URL + "/get_top_user", {
        type: type
    }, function(msg) {
        if (msg) {
            //移除之前的数据
            $(".J_orderListTr").remove();
            //
            //第一个排行榜
            var top_1 = msg.data[0];
            var top_str_1 = "";
            if (top_1 != null) {
                for (var i = 0; i < top_1.length; i++) {
                    top_str_1 += '<tr class="J_orderListTr"><td  class="oreder_name" height="60" valign="middle" bgcolor="#ddd1c3"><img src="' + PUBLIC + '/English/images/';
                    if (i == 0) {
                        top_str_1 += 'gold.png" width="26" height="25" />';
                    } else if (i == 1) {
                        top_str_1 += 'yingpai.png" width="26" height="25" />';
                    } else if (i == 2) {
                        top_str_1 += 'tongpai.png" width="26" height="25" />';
                    }
                    top_str_1 += top_1[i]['nickname'] + "</td>" + '<td height="60" valign="middle" bgcolor="#ddd1c3">' +
                            '<span class="ricenumber">' + top_1[i]['rice_sum'] + '</span>' +
                            '<img src="' + PUBLIC + '/English/images/rice.png" width="25" height="26" /></td>' +
                            '<td height="60" colspan="2" valign="middle" bgcolor="#ddd1c3">' + top_1[i]['best_level_name'] + '</td></tr>';
                }
            }
            $(".J_orderVoiceTr:first").after(top_str_1);
            //
            //第二个排行榜
            var top_2 = msg.data[1];
            var top_str_2 = "";
            if (top_2 != null) {
                for (var i = 0; i < top_2.length; i++) {
                    top_str_2 += '<tr class="J_orderListTr"><td  class="oreder_name" height="60" valign="middle" bgcolor="#ddd1c3"><img src="' + PUBLIC + '/English/images/';
                    switch (i) {
                        case 1:
                            top_str_2 += 'gold.png" width="26" height="25" />';
                        case 2:
                            top_str_2 += 'yingpai.png" width="26" height="25" />';
                        case 3:
                            top_str_2 += 'tongpai.png" width="26" height="25" />';
                        default:
                            top_str_2 += 'tongpai.png" width="26" height="25" />';
                    }
                    top_str_2 += top_2[i]['nickname'] + "</td>" + '<td height="60" valign="middle" bgcolor="#ddd1c3">' +
                            '<span class="ricenumber">' + top_2[i]['rice_sum'] + '</span>' +
                            '<img src="' + PUBLIC + '/English/images/rice.png" width="25" height="26" /></td>' +
                            '<td height="60" colspan="2" valign="middle" bgcolor="#ddd1c3">' + top_2[i]['best_level_name'] + '</td></tr>';
                }
            }
            $(".J_orderVoiceTr:eq(1)").after(top_str_2);
            //
            //是否显示说力听力
            if (type == "total_rice" || type == "continue_right_num") {
                $(".J_orderVoiceTr").hide();
            } else {
                $(".J_orderVoiceTr").show();
            }
        }
    }, "json")
}