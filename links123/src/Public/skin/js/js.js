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

//获得天气，通过当前ip added by Tony 2013/07/08
$(document).ready(function(){
	var city = remote_ip_info["city"];
	var str = encodeURI(city); //alert(re);		
	jQuery('#sinaWeatherToolIframe').attr('src','http://weather.news.sina.com.cn/chajian/iframe/weatherStyle0.html?city='+str);
});
//---------------------------------------------------  
//日期格式化  
//格式 YYYY/yyyy/YY/yy 表示年份  
//MM/M 月份  
//W/w 星期  
//dd/DD/d/D 日期  
//hh/HH/h/H 时间  
//mm/m 分钟  
//ss/SS/s/S 秒  
//---------------------------------------------------  
function dateFormat(formatStr) {
    var d = new Date();
    var str = formatStr;
    var Week = ['日', '一', '二', '三', '四', '五', '六'];

    str = str.replace(/yyyy|YYYY/, d.getFullYear());
    str = str.replace(/yy|YY/, (d.getYear() % 100) > 9 ? (d.getYear() % 100).toString() : '0' + (d.getYear() % 100));

    str = str.replace(/MM/, d.getMonth() > 8 ? (d.getMonth() + 1).toString() : '0' + (d.getMonth() + 1));
    str = str.replace(/M/g, (d.getMonth() + 1));

    str = str.replace(/w|W/g, Week[d.getDay()]);

    str = str.replace(/dd|DD/, d.getDate() > 9 ? d.getDate().toString() : '0' + d.getDate());
    str = str.replace(/d|D/g, d.getDate());

    str = str.replace(/hh|HH/, d.getHours() > 9 ? d.getHours().toString() : '0' + d.getHours());
    str = str.replace(/h|H/g, d.getHours());
    str = str.replace(/mm/, d.getMinutes() > 9 ? d.getMinutes().toString() : '0' + d.getMinutes());
    str = str.replace(/m/g, d.getMinutes());

    str = str.replace(/ss|SS/, d.getSeconds() > 9 ? d.getSeconds().toString() : '0' + d.getSeconds());
    str = str.replace(/s|S/g, d.getSeconds());

    return str;
}

//
$(function() {
    //
    var APP = $("#js_APP").val();
    var URL = $("#js_URL").val();
    var PUBLIC = $("#js_PUBLIC").val();

    // 设为首页
    $(".a_setHome").click(function() {
        setHome('http://www.links123.cn');
    });

    // tip标签
    $(".tip").tipTip({maxWidth: "auto", edgeOffset: 3, defaultPosition: "top"});

    /* 收藏 */
    bindLinksCollectEvent();

    // 关闭本页
    $("#btn_close_me").click(function() {
        closeme();
    });

    // 个人中心左边导航
    $("#grzx_nav li").hover(function() {
        $(this).addClass("hov");
    },
            function() {
                $(this).removeClass("hov");
            });

    // 留言板文本框
    $(".wdjyts_ts a").toggle(function() {
        $(this).parent().next("div").show();
    },
            function() {
                $(this).parent().next("div").hide();
            });

    // 会员左栏菜单关闭本页
    if ($("#grzx_nav")[0]) {
        $("#grzx_nav li:last").css("border", "none");
        //
        $("#grzx_nav li:last").children("a").click(function() {
            //opener.location.reload();
            closeme();
        })
    }

    // 弹出页
    $(".newWin").click(function() {
        myWinOpen($(this).attr('url'), '', '');
    });

    // 时钟
    if ($("#li_today")[0]) {
        var invId = window.setInterval(function() {
            $("#li_today").text(dateFormat("MM月DD日 周W hh:mm:ss"));
        }, 1000);
    }

    // 直达框获得焦点
    $(".header_top").mouseover(function() {
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

var w = null;
// 弹出页
function myWinOpen(theURL, winName, features) {
    if (w != undefined && isOpen()) {
        w.close();
    }
    w = window.open(theURL);
}
//
function isOpen() {
    try {
        w.document;
        return true;
    }
    catch (ex) {
    }
    return false;
}

//关闭浏览器
var browserName = navigator.appName;
if (browserName == "Netscape") {
    function closeme() {
        window.open('', '_parent', '');
        window.close();
    }
} else {
    if (browserName == "Microsoft Internet Explorer") {
        function closynoshowsme() {
            window.opener = "whocares";
            window.close();
        }
    }
}

/* cookie */
function getCookie(name) {
    var search;
    search = name + "=";
    offset = document.cookie.indexOf(search);
    if (offset != -1) {
        offset += search.length;
        var end = document.cookie.indexOf(";", offset);
        if (end == -1)
            end = document.cookie.length;
        return unescape(document.cookie.substring(offset, end));
    } else
        return "";
}
//
function setCookie(name, value) {
    document.cookie = name + "=" + value + "; path=/;";
}
function bindLinksCollectEvent() {
    $(".btn_collect").click(function() {
        var label = $(this).next("label");
        var num = parseInt(label.text());
        if (typeof(label.text()) == "undefined" || label.text() == "") {
            num = 0;
        }
        $.post(APP + "Member/saveCollect", {
            lnk_id: $(this).attr('lnk_id'),
            link: $(this).attr('link')
        },
        function(data) {
            if (data.indexOf("saveOK") >= 0) {
                alert("收藏成功！");
                num++;
                label.text(num);
                //window.location.reload();
            }
            else {
                alert(data);
            }
        });
    });
}
//
var APP = $("#js_APP").val();
var URL = $("#js_URL").val();
var PUBLIC = $("#js_PUBLIC").val();

