
$(function(){
var Config = {
	MailConfig : [
			{
				action: "https://ssl.mail.163.com/entry/coremail/fcg/ntesdoor2?df=mail163_letter&from=web&funcid=loginone&iframe=1&language=-1&net=t&passtype=1&product=mail163&race=382_92_196_gz&style=-1",
				name: "@163.com",
                params: {
                  //  url: "http://entry.mail.163.com/coremail/fcg/ntesdoor2?lightweight=1&verifycookie=1&language=-1&style=15",
                    username: "#{u}",
                    password: "#{p}",
                    savelogin:	"0",
                    url2:	"http://mail.163.com/errorpage/err_163.htm"
                }
			},
			{
				action: "https://ssl.mail.126.com/entry/cgi/ntesdoor?hid=10010102&funcid=loginone&df=mail126_letter&language=-1&passtype=1&verifycookie=-1&iframe=1&from=web&net=failed&product=mail126&style=-1&race=-2_-2_-2_db",
				name: "@126.com",
                params: {
                    domain: "126.com",
                    username: "#{u}@126.com",
                    password: "#{p}",
                    savelogin:	"0",
                    url2:	"http://mail.126.com/errorpage/err_126.htm"
                    //url: "http://entry.mail.126.com/cgi/ntesdoor?lightweight%3D1%26verifycookie%3D1%26language%3D0%26style%3D-1"
                }
			},
			{
				action : "https://login.sina.com.cn/sso/login.php",
				name: "@sina.com",
				params : {
					username : "#{u}@sina.com",
					password : "#{p}",
					entry : "freemail",
					gateway : "0",
					encoding : "UTF-8",
					url : "http://mail.sina.com.cn/",
					returntype : "META"
				}
			},
			{
				action: "https://edit.bjs.yahoo.com/config/login",
				name: "@yahoo.com.cn",
                params: {
                    login: "#{u}@yahoo.com.cn",
                    passwd: "#{p}",
                    domainss: "yahoo",
                    ".intl": "cn",
                    ".src": "ym"
                }
			},
			{
				action: "https://edit.bjs.yahoo.com/config/login",
				name: "@yahoo.cn",
                params: {
                    login: "#{u}@yahoo.cn",
                    passwd: "#{p}",
                    domainss: "yahoocn",
                    ".intl": "cn",
                    ".done": "http://mail.cn.yahoo.com/inset.html"
                }
			},
			{
				action: "http://passport.sohu.com/login.jsp",
				name: "@sohu.com",
                params: {
                    loginid: "#{u}@sohu.com",
                    passwd: "#{p}",
                    fl: "1",
                    vr: "1|1",
                    appid: "1113",
                    ru: "http://login.mail.sohu.com/servlet/LoginServlet",
                    ct: "1173080990",
                    sg: "5082635c77272088ae7241ccdf7cf062"
                }
			},
			{
				action: "https://mail.yeah.net/entry/cgi/ntesdoor?df=webmailyeah&from=web&funcid=loginone&iframe=1&language=-1&passtype=1&verifycookie=1&product=mailyeah&style=-1&",
				name: "@yeah.net",
                params: {
                    domain: "yeah.net",
                    username: "#{u}@yeah.net",
                    user: "#{u}",
                    password: "#{p}",
                    savelogin:	"0",
                    url2:	"http://mail.yeah.net/errorpage/err_yeah.htm"
                    //url: "http://entry.mail.yeah.net/cgi/ntesdoor?lightweight%3D1%26verifycookie%3D1%26style%3D-1"
                }
			},
			{
				action: "https://mail.10086.cn/Login/Login.ashx?_fv=5&cguid=1144153566504&_=3900d6b56d0742590535b3bb5ddee9f923b4326c ",
				name: "@139.com",
                params: {
                    UserName: "#{u}",
                    Password: "#{p}"
                    //clientid: "5015"
                }
			},
			{
				action: "http://passport.21cn.com/maillogin.jsp",
				name: "@21cn.com",
                params: {
                    UserName: "#{u}@21cn.com",
                    passwd: "#{p}",
                    domainname: "21cn.com"
                }
			},
			{
				action : "http://mail.qq.com",
				type : "link"
			}, 
			{
				action : "http://gmail.google.com",
				type : "link"
			}, 
			{
				action : "http://www.hotmail.com",
				type : "link"
			},
			{
				action : "https://passport.alipay.com/login/login.htm?fromSite=9&return_url=http%3A%2F%2Fmail.aliyun.com%2Funiquelogin.htm",
				type : "link"
			},
			{
				action : "https://login.yahoo.com/config/login_verify2?&.src=ym&.intl=us",
				type : "link"
			}
			]
}
var MailLogin = {
	mailCache : [],

	sendMail : function() {
		var mailUserName = $.trim($('#mailUserName').val());
		var mailPassWord = $.trim($('#mailPassWord').val());

		if (mailUserName == "") {
			alert("\u7528\u6237\u540d\u4e0d\u80fd\u4e3a\u7a7a\uff01");
			return false;
		}
		if (mailPassWord == "") {
			alert("\u5bc6\u7801\u4e0d\u80fd\u4e3a\u7a7a\uff01");
			return false;
		}

		var mailFormObj = $('#J_mailForm');
		var mailType = $('#mail_list').attr('selectindex');

		var mailConfig = Config.MailConfig[mailType];

		if (mailConfig.val == 0) {
			alert("\u60a8\u6ca1\u6709\u9009\u62e9\u90ae\u7bb1\uff01");
			return;
		}

		mailFormObj.attr('action', mailConfig.action);
		var str = '';
		for (param in mailConfig.params) {
			
			str = '<input type="hidden" class="J_mail_form_hidden" name="' + param + '" value="'
				+ mailConfig.params[param].replace('#{u}', mailUserName).replace('#{p}', mailPassWord) + '" />';
			
			mailFormObj.append(str);
		}
		
		mailFormObj.submit();
		$('.J_mail_form_hidden').remove();
		$('#mailPassWord').value = '';
	},

	change : function(mailType) {
		var mailConfig = Config.MailConfig[mailType];
		if (mailConfig.type == "link") {
			
			$('#mailSelect').text(Config.MailConfig[0].name);
			$('#mail_list').attr('selectindex', 0);
			
			var mailFormObj = $('#J_mailForm');
			
			mailFormObj.attr('action', mailConfig.action);
			var str = '';
			for (param in mailConfig.params) {
				
				str = '<input type="hidden" class="J_mail_form_hidden" name="' + param + '" value="'
					+ mailConfig.params[param].replace('#{u}', mailUserName).replace('#{p}', mailPassWord) + '" />';
				
				mailFormObj.append(str);
			}
			mailFormObj.append(str);
			
			mailFormObj.submit();
			$('.J_mail_form_hidden').remove();
			$('#mailPassWord').value = '';
		} else {
			$('#mailSelect').text(Config.MailConfig[mailType].name);
			$('#mail_list').attr('selectindex', mailType);
		}
	}
}

/** $翻译 **/

var translateLang = 0;

$('#J_translate').click(function(){
	$.fancybox({
		href: '#J_box_translate',
		//closeBtn : false,
		helpers:  {
	        title:  null,
	        overlay : null
	    },
	    margin : [35, 0, 0, 500],
	    topRatio : 0,
	    leftRatio : 0,
	    width: 692,
	    height: 525,
	    autoSize: false
	    
	});
	$('.J_translate_source').select();
	return false;
});

$('.J_translate_clear').click(function(){
	$('#gt-res-dict').html('');
	$('#result_box').html('');
	$('.J_translate_source').val('');
	$('.J_translate_source').select();
	return false;
});

$('#gt-sl-gms').click(function(){
	translateLang = 0;
	if ($(this).hasClass('goog-flat-menu-button-focused')) {
		$(this).removeClass('goog-flat-menu-button-focused');
		$('#gt-sl-gms-menu').hide();
	} else {
		$('#gt-tl-gms').removeClass('goog-flat-menu-button-focused');
		$(this).addClass('goog-flat-menu-button-focused');
		$('.J_lang_auto').show();
		$('#gt-sl-gms-menu').show();
	}
	return false;
});
$('#gt-tl-gms').click(function(){
	translateLang = 1;
	if ($(this).hasClass('goog-flat-menu-button-focused')) {
		$(this).removeClass('goog-flat-menu-button-focused');
		$('#gt-sl-gms-menu').hide();
	} else {
		$('#gt-sl-gms').removeClass('goog-flat-menu-button-focused');
		$(this).addClass('goog-flat-menu-button-focused');
		$('.J_lang_auto').hide();
		$('#gt-sl-gms-menu').show();
	}
	return false;
});

$('.goog-menuitem').mouseover(function(){
	$('.goog-menuitem').removeClass('goog-menuitem-highlight');
	$(this).addClass('goog-menuitem-highlight');
});

$('.goog-menuitem').click(function(){
	var langText = $.trim($(this).text());
	var lang = $(this).attr('data-id');
	if (translateLang == 1) {
		$('#J_lang_tgt').text(langText);
		$('#gt-tl-gms').removeClass('goog-flat-menu-button-focused');
		$('#gt-tl').val(lang);
	} else {
		$('#J_lang_src').text(langText);
		$('#gt-sl-gms').removeClass('goog-flat-menu-button-focused');
		$('#gt-sl').val(lang);
	}
	$('#gt-sl-gms-menu').hide();
	
	$("#gt-submit").trigger("click");
	return false;
});

$('#gt-swap').click(function(){
	var slLangText = $.trim($('#J_lang_src').text());
	var tlLangText = $.trim($('#J_lang_tgt').text());
	var slLang = $('#gt-sl').val();
	var tlLang = $('#gt-tl').val();
	
	$('#J_lang_tgt').text(slLangText);
	$('#gt-tl').val(slLang);
	
	$('#J_lang_src').text(tlLangText);
	$('#gt-sl').val(tlLang);
	
	$("#gt-submit").trigger("click");
	
	return false;
});

$('#gt-submit').click(function() {

	var sl = $('#gt-sl').val();
	var tl = $('#gt-tl').val();
	var q = $('.J_translate_source').val();
	
	$.ajax({
		type : 'POST',
		url : APP + 'Index/google_translate',
		data : {
			'sl' : sl,
			'tl' : tl,
			'q'  : q
		},
		cache : false,
		dataType : 'json',
		success : function(data) {
			
			var dictArr = eval(data.data);
			
			if (dictArr) {
				var dictStr = '';
				dictStr += '<table class="gt-baf-table"><tbody>';
				
				if (typeof dictArr[1] != "undefined") {
					for (var i = 0; i < dictArr[1].length; i++) {
						
						var bafArr = dictArr[1][i];
						
						dictStr += '<tr><td colspan="4"><div class="gt-baf-cell gt-baf-pos">' + bafArr[0] + '</div></td></tr>';
					
					
						for (var j = 0; j < bafArr[2].length; j++) {
							dictStr += '<tr>';
							var wordArr = bafArr[2][j];
							
							dictStr += '<td>';
							var cts_width = 24;
							if (wordArr[3] < 0.01) {
								cts_width = 8;
							} else if (wordArr[3] < 0.1) {
								cts_width = 16;
							}
							dictStr += '<div class="gt-baf-cell gt-baf-marker-container"><div class="gt-baf-cts" style="width:' + cts_width + 'px;"></div></div>'; 
							
							dictStr += '</td>';
							
							dictStr += '<td><div class="gt-baf-cell gt-baf-bar"></div></td>';
							
							dictStr += '<td>';
							dictStr += '<div class="gt-baf-cell gt-baf-word-clickable" style="text-align: left; direction: ltr;">' + wordArr[0] + '</div>';
							dictStr += '</td>';
							
							dictStr += '<td style="width: 100%;">';
							dictStr += '<div class="gt-baf-cell gt-baf-translations" style="direction: ltr;">';
							
							for (var k = 0; k < wordArr[1].length; k++) {
								dictStr += '<span class="gt-baf-back">' + wordArr[1][k];
								if (k != wordArr[1].length - 1) {
									dictStr += ', '
								}
								dictStr += '</span>'
							}
							
							dictStr += '</div>';
							dictStr += '</td>';
							dictStr += '</tr>';
						}
					}
					dictStr += '</tbody></table>';
					
					$('#gt-res-dict').html(dictStr);
				} else {
					$('#gt-res-dict').html('');
				}
				
				$('#result_box').html(dictArr[0][0][0]);
				
			} else {
				$('#result_box').html('<span style="font-size:14px; color: red;">亲，未找到你所查询的结果，再试下吧!</span>');
			}
		},
		error : function() {
		}
	});

});

$('#J_box_translate').mouseout(function(){
	$('.J_translate_source').select();
	return false;
});


/** 翻译$ * */

/** $日历 * */
$('#J_calendar').click(function(){
	
	$('#J_calendar_iframe').attr('src', 'http://baidu365.duapp.com/wnl.html');
	
	$.fancybox({
		href: '#J_box_calendar',
		//closeBtn : false,
		helpers:  {
	        title:  null,
	        overlay : null
	    },
	    margin : [35, 0, 0, 650],
	    topRatio : 0,
	    leftRatio : 0,
	    width: 550,
	    height: 525,
	    autoSize: false
	    
	});
	
	return false;
});

$('#J_box_calendar_list a').click(function() {
	$('#J_calendar_iframe').attr('src', $(this).attr('data-url'));
	return false;
});

/** 日历$ **/

/** $网页邮箱 **/

$('#J_mail').click(function(){
	$.fancybox({
		href: '#J_box_mail',
		//closeBtn : false,
		helpers:  {
	        title:  null,
	        overlay : null
	    },
	    margin : [35, 0, 0, 770],
	    topRatio : 0,
	    leftRatio : 0,
	    width: 390,
	    height: 300,
	    autoSize: false
	    
	});
	
	//$('#J_box_mail').css('display', $('#J_box_mail').css('display') == 'none' ? 'block' : 'none');
	$("#mailUserName").select();
	return false;
});

$('#J_box_mail').mouseover(function(){
	$('#mailUserName').select();
	return false;
});

$('#mailPassWord').mouseover(function(){
	$('#mailPassWord').select();
	return false;
});

$('#J_mail_submit').click(function(){
	MailLogin.sendMail();
	return false;
});

$("#mailPassWord").keypress(function(event){
	  if(event.keyCode==13) {
		  MailLogin.sendMail();
		  return false;
	  }
});


$('.mail-list li').click(function() {
	
	MailLogin.change($(this).attr('dn'));
	$("#mailUserName").select();
 });
 
 $('.mail-list li').mouseover(function() {
	$(this).addClass('option-hover');       
 });
 $('.mail-list li').mouseout(function() {
 	$(this).removeClass('option-hover');      
 });
 /** 网页邮箱$ **/
});