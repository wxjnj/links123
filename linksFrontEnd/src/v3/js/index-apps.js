/*
 * @name: 首页app相关 js
 * @author: lpgray
 * @datetime: 2013-09-25 13:05
 */

$( function($) {

	/*
	 * app开关触发器
	 */
	$.fn.links123_apptrigers = function(selector) {
		this.on('click', selector, function() {
			var appId = $(this).data('href');
			if(!$(appId).size()){
				$('body').append(AppsTpl[appId]);
			}
			$(this).data('links_app') || $(this).data('links_app', new App(appId));
			var app = $(this).data('links_app');
			app.show();
			return false;
		});
	}
	$('#J_Apps').links123_apptrigers('.J_app_trig');
	
	$('#J_Apps').on('click', '.J_app_link', function(){
	  window.open($(this).data('href'));
	});

	/*
	 * 当前的 App集合
	 */
	var apps = [];
	/*
	 * App类
	 */
	var App = function(appId) {
		this.appId = appId;
		this.$elem = $(appId);
		this.$elem.addClass('links123-app-frame');
		this.initStyle();
		this.bindEvent();
		apps.push(this);
		callbacks[appId] && callbacks[appId](this);
	};
	App.prototype = {
		show : function() {
			var self = this;
			// this.$elem.css('display', 'block');
			this.$elem.fadeIn(function(){
				if(self.appId == '#J_box_translate'){
					setTimeout(function(){
						$('.J_translate_source').select();
					},0);
				}
			});
		},
		close : function() {
			// this.$elem.css('display', 'none');
			this.$elem.fadeOut();
		},
		initStyle : function() {
			this.w = this.$elem.outerWidth();
			var h = this.$elem.outerHeight();
			var fixedStyle = parseInt(h) < parseInt($(window).height()) ? {
				'margin-left' : -this.w / 2,
				'margin-top' : -h / 2 + parseInt( $('#J_header').outerHeight() )
			} : {
				'margin-left' : -this.w / 2,
				'margin-top' : 0,
				'top' : parseInt( $('#header').outerHeight() ) + 10
			}
			this.$elem.css(fixedStyle);
			$(window).height();
		},
		bindEvent : function() {
			var self = this;
			if(self.$elem.children('.links123-close-wrap').length == 0){
			  self.$elem.prepend('<div class="links123-close-wrap"><a href="#">x</a></div>');
			}
			self.$closeBtn = self.$elem.children('.links123-close-wrap').children('a');
			self.$closeBtn.bind('click', function() {
				self.close();
			});
			self.$elem.bind('mousedown keydown', function() {
				$.map(apps, function(app) {
					app.$elem.css('z-index', '9000');
				});
				self.$elem.css('z-index', '10000');
			});

			$(document).bind('keyup', function(e) {
				e.keyCode === 27 && self.close();
			});

		},
		clone : function() {
			var $cloneObj = this.$elem.clone(), 
				left = parseInt(this.$elem.offset().left) - parseInt(this.$elem[0].style.marginLeft) + 20, 
				top = parseInt(this.$elem.offset().top) - parseInt(this.$elem[0].style.marginTop) + 20;
			$cloneObj.attr('id', (Math.random()).toString().substring(3));
			$cloneObj.css({
				'left' : left,
				'top' : top
			});
			$('body').append($cloneObj);
			return new App('#' + $cloneObj.attr('id'));
		},
		sync : function() {
			$.post('', this.data, function() {
			}, 'json');
		},
		enableDrag : function(selector) {
			var self = this, $dragBar = this.$elem.find(selector),
				move = function(e) {
					var cX = parseInt(e.clientX);
					var cY = parseInt(e.clientY);
					var mL = parseInt(self.$elem[0].style.marginLeft);
					var mT = parseInt(self.$elem[0].style.marginTop);
					var left = cX - self._x - mL;
					var top = cY - self._y - mT;
					var headerH = $('#J_header').outerHeight();
					if(selector == '.music_icon'){
						self.$elem.css('bottom', 'auto');
						left -= $(window).scrollLeft();
						top -= $(window).scrollTop();
					}
					if( (top + mT) <= headerH ){
						top = headerH - mT;
					}
					if( (left) >= $(window).width()+mL ){
					  left = $(window) - mL/2;
					}
					self.$elem.css({
						'left' : left,
						'top' : top
					});

					$(document).bind('mouseup', stop);
				}, stop = function() {
					$(document).unbind('mousemove', move);
					self.$elem.removeClass('K_box_music_move');
				};
				
			$dragBar.css('cursor', 'move');
			$dragBar.bind('mousedown', function(e) {
				self._x = parseInt(e.clientX) - parseInt(self.$elem.offset().left);
				self._y = parseInt(e.clientY) - parseInt(self.$elem.offset().top);
				if(selector == '.music_icon'){
					// 用.K_box_music_move覆盖css的transition，消除拖动时的延迟行为
					self.$elem.addClass('K_box_music_move');
				}
				$(document).bind('mousemove', move);
				$(document).bind('mouseup', stop);
			});
			$dragBar.bind('mouseup', stop);
		}
	};

	/*
	 * callbacks
	 * 不同app初始化会调用这里的callback
	 * 以dom id为key调用对应的函数，每个函数欧诺只会调用一次，而且是按需调用
	 */
	var callbacks = {
		'#J_box_note' : function(app) {
			var $note = app.$elem;
			var $textarea = $note.find('textarea');
			var textareaBg = null;
			var content = null;
			var theapp = app;
			theapp.enableDrag('.box_note_header');
			load();
			// 变背景色
			$note.on('click', '[class^=color_]', function() {
				textareaBg = '' + $(this).css('background-color');
				$textarea.css('background', textareaBg);
				remember();
			});
			// 新建
			$note.on('click', '.btn_add', function() {
				var back = theapp.clone();
				callbacks['#J_box_note'](back);
				back.$elem.find('textarea').val('');
				back.$elem.find('textarea').css('background', '#fff');
			});
			// 删除
			$note.on('click', '.btn_clear', function() {
				$textarea.val('').select();
				remember();
			});
			// remember
			function remember() {
				$.cookies.set('links123_note_bg', textareaBg);
				//$.cookies.set('links123_note_content', $textarea.val());
			}

			// load
			function load() {
				setTimeout(function(){
					$textarea.focus();
				},100);
				$textarea.css('background', $.cookies.get('links123_note_bg'));
				//!!$.cookies.get('links123_note_content') && $textarea.val( $.cookies.get('links123_note_content') );
			}

		},

		'#J_box_mail' : function() {
			var Config = {
				MailConfig : [{
					action : "https://ssl.mail.163.com/entry/coremail/fcg/ntesdoor2?df=mail163_letter&from=web&funcid=loginone&iframe=1&language=-1&net=t&passtype=1&product=mail163&race=382_92_196_gz&style=-1",
					name : "@163.com",
					params : {
						//  url: "http://entry.mail.163.com/coremail/fcg/ntesdoor2?lightweight=1&verifycookie=1&language=-1&style=15",
						username : "#{u}",
						password : "#{p}",
						savelogin : "0",
						url2 : "http://mail.163.com/errorpage/err_163.htm"
					}
				}, {
					action : "https://ssl.mail.126.com/entry/cgi/ntesdoor?hid=10010102&funcid=loginone&df=mail126_letter&language=-1&passtype=1&verifycookie=-1&iframe=1&from=web&net=failed&product=mail126&style=-1&race=-2_-2_-2_db",
					name : "@126.com",
					params : {
						domain : "126.com",
						username : "#{u}@126.com",
						password : "#{p}",
						savelogin : "0",
						url2 : "http://mail.126.com/errorpage/err_126.htm"
						//url: "http://entry.mail.126.com/cgi/ntesdoor?lightweight%3D1%26verifycookie%3D1%26language%3D0%26style%3D-1"
					}
				}, {
					action : "https://login.sina.com.cn/sso/login.php",
					name : "@sina.com",
					params : {
						username : "#{u}@sina.com",
						password : "#{p}",
						entry : "freemail",
						gateway : "0",
						encoding : "UTF-8",
						url : "http://mail.sina.com.cn/",
						returntype : "META"
					}
				}, {
					action : "https://edit.bjs.yahoo.com/config/login",
					name : "@yahoo.com.cn",
					params : {
						login : "#{u}@yahoo.com.cn",
						passwd : "#{p}",
						domainss : "yahoo",
						".intl" : "cn",
						".src" : "ym"
					}
				}, {
					action : "https://login.sina.com.cn/sso/login.php",
					name : "@sina.cn",
					params : {
						username : "#{u}@sina.cn",
						password : "#{p}",
						entry : "freemail",
						gateway : "0",
						encoding : "UTF-8",
						url : "http://mail.sina.com.cn/",
						returntype : "META"
					}
				}, {
					action : "http://passport.sohu.com/login.jsp",
					name : "@sohu.com",
					params : {
						loginid : "#{u}@sohu.com",
						passwd : "#{p}",
						fl : "1",
						vr : "1|1",
						appid : "1113",
						ru : "http://login.mail.sohu.com/servlet/LoginServlet",
						ct : "1173080990",
						sg : "5082635c77272088ae7241ccdf7cf062"
					}
				}, {
					action : "https://mail.yeah.net/entry/cgi/ntesdoor?df=webmailyeah&from=web&funcid=loginone&iframe=1&language=-1&passtype=1&verifycookie=1&product=mailyeah&style=-1&",
					name : "@yeah.net",
					params : {
						domain : "yeah.net",
						username : "#{u}@yeah.net",
						user : "#{u}",
						password : "#{p}",
						savelogin : "0",
						url2 : "http://mail.yeah.net/errorpage/err_yeah.htm"
						//url: "http://entry.mail.yeah.net/cgi/ntesdoor?lightweight%3D1%26verifycookie%3D1%26style%3D-1"
					}
				}, {
					action : "https://mail.10086.cn/Login/Login.ashx?_fv=5&cguid=1144153566504&_=3900d6b56d0742590535b3bb5ddee9f923b4326c ",
					name : "@139.com",
					params : {
						UserName : "#{u}",
						Password : "#{p}"
						//clientid: "5015"
					}
				}, {
					action : "http://passport.21cn.com/maillogin.jsp",
					name : "@21cn.com",
					params : {
						UserName : "#{u}@21cn.com",
						passwd : "#{p}",
						domainname : "21cn.com"
					}
				}, {
					action : "http://mail.qq.com",
					type : "link"
				}, {
					action : "http://gmail.google.com",
					type : "link"
				}, {
					action : "http://www.hotmail.com",
					type : "link"
				}, {
					action : "https://passport.alipay.com/login/login.htm?fromSite=9&return_url=http%3A%2F%2Fmail.aliyun.com%2Funiquelogin.htm",
					type : "link"
				}, {
					action : "https://login.yahoo.com/config/login_verify2?&.src=ym&.intl=us",
					type : "link"
				}, {
					action : 'http://mail.links123.cn',
					type : "link"
				}]
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

						str = '<input type="hidden" class="J_mail_form_hidden" name="' + param + '" value="' + mailConfig.params[param].replace('#{u}', mailUserName).replace('#{p}', mailPassWord) + '" />';

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

							str = '<input type="hidden" class="J_mail_form_hidden" name="' + param + '" value="' + mailConfig.params[param].replace('#{u}', mailUserName).replace('#{p}', mailPassWord) + '" />';

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

			$('#J_box_mail').mouseover(function() {
				$('#mailUserName').select();
				return false;
			});

			$('#mailPassWord').mouseover(function() {
				$('#mailPassWord').select();
				return false;
			});

			$('#J_mail_submit').click(function() {
				MailLogin.sendMail();
				return false;
			});

			$("#mailPassWord").keypress(function(event) {
				if (event.keyCode == 13) {
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

		},

		'#J_box_calendar' : function() {
			$('#J_calendar_iframe').attr('src', 'http://baidu365.duapp.com/wnl.html');
			$('#J_box_calendar_list a').click(function() {
				$('#J_calendar_iframe').attr('src', $(this).attr('data-url'));
				return false;
			});
		},

		'#J_box_calc' : function() {
			$('#J_calc_iframe').attr('src', 'http://qiqiapp3.duapp.com/yuyinjisuanqi/');
			$('#J_box_calc_list a').click(function() {
				$('#J_calc_iframe').attr('src', $(this).attr('data-url'));
				return false;
			});
		},

		'#J_box_translate' : function() {
			var translateLang = 0;
			//$('.J_translate_source').select();
			

			var lns = {
				"group-1" : [
					["sq","阿尔巴尼亚语"],["ar","阿拉伯语"],["az","阿塞拜疆语"],["ga","爱尔兰语"],
					["et","爱沙尼亚语"],["eu","巴斯克语"],["be","白俄罗斯语"],["bg","保加利亚语"],
					["is","冰岛语"],["pl","波兰语"]
				],
				"group-2" : [
					["bs","波斯尼亚语"],["fa","波斯语"],["af","布尔语(南非荷兰语)"],["da","丹麦语"],
					["de","德语"],["ru","俄语"],["fr","法语"],["tl","菲律宾语"],
					["fi","芬兰语"],["km","高棉语"],["ka","格鲁吉亚语"]
				],
				"group-3" : [
					["gu","古吉拉特语"],["ht","海地克里奥尔语"],["ko","韩语"],["nl","荷兰语"],
					["gl","加利西亚语"],["ca","加泰罗尼亚语"],["cs","捷克语"],["kn","卡纳达语"],
					["hr","克罗地亚语"],["la","拉丁语"],["lv","拉脱维亚语"]
				],
				"group-4" : [
					["lo","老挝语"],["lt","立陶宛语"],["ro","罗马尼亚语"],["mt","马耳他语"],
					["mr","马拉地语"],["ms","马来语"],["mk","马其顿语"],["bn","孟加拉语"],
					["hmn","苗语"],["no","挪威语"],["pt","葡萄牙语"]
				],
				"group-5" : [
					["ja","日语"],["sv","瑞典语"],["sr","塞尔维亚语"],["eo","世界语"],
					["sk","斯洛伐克语"],["sl","斯洛文尼亚语"],["sw","斯瓦希里语"],["ceb","宿务语"],
					["te","泰卢固语"],["ta","泰米尔语"],["th","泰语"]
				],
				"group-6" : [
					["tr","土耳其语"],["cy","威尔士语"],["ur","乌尔都语"],["uk","乌克兰语"],
					["iw","希伯来语"],["el","希腊语"],["es","西班牙语"],["hu","匈牙利语"],
					["hy","亚美尼亚语"],["it","意大利语"],["yi","意第绪语"]
				],
				"group-7" : [
					["hi","印地语"],["id","印尼语"],["jw","印尼爪哇语"],["vi","越南语"],
					["zh-TW","中文(繁体)"],["zh-CN","中文(简体)"],["en","英语"]
				]
			};

			var totalHTML = '';
			var groupHTML;
			var curClass;
			$.each(lns, function(idx, group){
				groupHTML = idx == 'group-1' ? 
					'<div class="goog-menuitem goog-option goog-option-selected goog-menuitem-highlight J_lang_auto"aria-selected="true" style="-moz-user-select: none;" data-id="auto" id=":2"><div class="goog-menuitem-content"><div class="goog-menuitem-checkbox"></div>检测语言</div></div><div class="goog-menuseparator" role="separator" style="-moz-user-select: none;" id=":3"></div>' : '';
				$.each(group, function(k, v){
					curClass = v[0] == 'en' ? 'goog-menuitem-emphasize-highlight' : '';
					if(v[0] == 'zh-CN' || v[0] == 'en') v[1] = '<b>' + v[1] + '</b>';
					groupHTML += '<div class="goog-menuitem goog-option ' + curClass + 
						'" style="-moz-user-select: none;" data-id="' + v[0] + 
						'"><div class="goog-menuitem-content"><div class="goog-menuitem-checkbox"></div>' + v[1] + 
						'</div></div>';
				});
				groupHTML = '<td><div id="goog-menuitem-' + idx + '" class="goog-menuitem-group" style="width: 100%;">' + groupHTML + '</div></td>';
				totalHTML += groupHTML;
			});
			$('#gt-sl-gms-menu').find('tr').html(totalHTML);

			$('.J_translate_clear').click(function() {
				$('#gt-res-dict').html('');
				$('#result_box').html('');
				$('.J_translate_source').val('');
				$('.J_translate_source').select();
				return false;
			});

			$('#gt-sl-gms').click(function() {
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
			$('#gt-tl-gms').click(function() {
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

			$('.goog-menuitem').mouseover(function() {
				$('.goog-menuitem').removeClass('goog-menuitem-highlight');
				$(this).addClass('goog-menuitem-highlight');
			});

			$('.goog-menuitem').click(function() {
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

			$('#gt-swap').click(function() {
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

				$('#gt-res-dict').html('<tr><td colspan="4"><div class="gt-baf-cell gt-baf-pos">翻译中...</div></td></tr>');
				$('#result_box').html('');

				$.ajax({
					type : 'POST',
					url : APP + 'Index/google_translate',
					data : {
						'sl' : sl,
						'tl' : tl,
						'q' : q
					},
					cache : false,
					dataType : 'json',
					success : function(data) {

						var dictArr = eval(data.data);

						if (dictArr) {
							var dictStr = '';
							dictStr += '<table class="gt-baf-table"><tbody>';

							var dictSubArr = '';
							var dictType = 0;
							//0为词语，1为句子
							if ( typeof dictArr[1] != "undefined") {
								dictSubArr = dictArr[1];
							} else if ( typeof dictArr[0] != "undefined") {
								dictSubArr = dictArr[0];
								dictType = 1;
							}

							if (dictSubArr) {

								if (dictType) {
									dictStr += '<span lang="zh-CN" id="result_box">';
									for (var i = 0; i < dictSubArr.length; i++) {

										var bafArr = dictSubArr[i];

										//dictStr += '<tr><td colspan="4"><div class="gt-baf-cell gt-baf-pos">' + bafArr[0] + '</div></td></tr>';
										dictStr += '<span>' + bafArr[0].replace('\n', '</br>') + '</span>';
									}
									dictStr += '</span>';
								} else {

									for (var i = 0; i < dictSubArr.length; i++) {

										var bafArr = dictSubArr[i];

										dictStr += '<tr><td colspan="4"><div class="gt-baf-cell gt-baf-pos">' + bafArr[0] + '</div></td></tr>';

										if (bafArr[2] instanceof Array) {
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
									}
								}
								dictStr += '</tbody></table>';

								$('#gt-res-dict').html(dictStr);
							} else {
								$('#gt-res-dict').html('');
							}

							if (!dictType) {
								$('#result_box').html(dictArr[0][0][0]);
							}

						} else {
							$('#result_box').html('<span style="font-size:14px; color: red;">亲，未找到你所查询的结果，再试下吧!</span>');
						}
					},
					error : function() {

					}
				});

			});
		},

		'#J_box_weather' : function() {
			//解决safari禁用第三方cookie造成天气控件不显示的bug
			if ($.browser.safari && navigator.userAgent.toLowerCase().match(/chrome/) == null) {
				var weatherPlugin = '<iframe frameborder="0" scrolling="no" src="/Home/weather/index_new.html" ' + 'style="z-index: 99999; width: 380px; height: 220px; border: 0px;"></iframe>';
				$('#J_box_weather').find('.K_weather_box').remove().end().append(weatherPlugin);
			}else{
				var html = '<script src="http://ext.weather.com.cn/47111.js"></script>';
				$('#J_box_weather').find('.K_weather_box').html(html);
			}
		},
		
		'#J_box_music' : function( app ){

		  //MacQQbrowser启用定制版豆瓣fm（iframe直接嵌入无法遮盖）
		  //qq的web播放器一样不能被遮盖，所以这里嵌入ipad播放器
		  //等MacQQbrowser升级的时候，需要重新检查这里
		  var ua = detect.parse(navigator.userAgent);
		  if(ua.browser.isMacQQBrowser){
		  	app.$elem.find('.douban-source').attr('data-href', '/Index/dbfm.html');
		  	app.$elem.find('.qq-source').attr('data-href', 'http://soso.music.qq.com/ipad/player.html').attr('data-height', '430');
		  }

		  //启用拖动
		  app.enableDrag('.music_icon');
		  //复写默认的点击关闭函数
		  app.$closeBtn.unbind('click');
		  app.$closeBtn.bind('click', function(){
		  	if( $(this).hasClass('closed') ){
		  		app.show();
		  	}else{
		  		app.$elem.css({
		  			'bottom': '10px',
		  			'top': 'auto'
		  		});
				app.close();
		  	}
		  });
		  //音乐app的关闭效果跟常规不同，它是向左侧寻边收缩，所以重写音乐app的关闭函数
		  app.close = function(){
		  	var w = parseInt( app.$elem.outerWidth() );
		  	var mL = parseInt( app.$elem.css("margin-left") );
		  	app.$elem.css({
		  		'position' : 'fixed',
		  		'left' : 0 - w - mL + 5 + 'px'
		  	});
		  	app.$elem.find('.music-close-wrap').hide();
		  	app.$closeBtn.addClass('closed');
		  }
		  //音乐app增加关闭&清除iframe.src的按钮
		  app.$elem.find('.music-close-wrap a').on('click', function(){
		  	app.$elem.fadeOut(function(){
		  		app.$elem.css({
		  			'bottom': '10px',
		  			'top': 'auto'
		  		});
		  	});
		  	$('#J_iframe').find('iframe').removeAttr('src');
		  });

		  // 音乐app在切换提供商以后会进行大小重设
		  var appPositionReset = function(){
		  	var mL = parseInt( app.$elem[0].style.marginLeft );
		  	if(app.$elem.css('bottom') == '10px'){
		  		app.$elem.css({
		  			'position' : 'fixed',
		  			'left' : (0 - mL - 3) + 'px'
		  		});
		  	}
		  };
		  app.show = function(){
		  	app.$elem.find('.music-close-wrap').show();
		  	appPositionReset();
		  	//完全关闭后再次打开，需要重新载入面板及频道
		  	if(app.$elem.is(':hidden')){
		  		app.$elem.show();
		  		$submenu.children(':first-child').children('a').click();
		  	}
		  	//app.$elem.css('display' , 'block');
		  	app.$closeBtn.removeClass('closed');
		  }
		  // 加载
		  var $submenu = $('#J_with_submenu').find('.submenu');
		  var $rootMenu = $('#J_with_submenu').children('a').children('.J_root_menu');
		  $submenu.on('click', 'a', function(){
		    $(this).parent().siblings().removeClass('hide last');
		    $(this).parent().addClass('hide').removeClass('last');
		    $rootMenu.html($(this).html());
		    // 给最后一个添加圆角
		    var currentMenu = $submenu.children('[class!=hide]');
		    $(currentMenu[currentMenu.length - 1]).addClass('last');
		    // 选择音乐频道
		    selectChannel( $(this) );
		  });
		  // 根据选中的提供商重设app大小
		  var resetAppSizeByProvider = function( $providerBtn ){
		  	$("#J_box_music").css({
		      'margin-left' : '-' + $providerBtn.data('width')/2 + 'px',
		      'margin-top' : '-' + $providerBtn.data('height')/2 + 'px'
		    });
		    $('#J_music_iframe_wrap').css({
		    	'width' : $providerBtn.data('width'),
		    	'height' : $providerBtn.data('height')
		    });
		  };
		  var musicAppInitOnlyOnce = function(){
		  	var $firstProvider = $submenu.children(':first-child').children('a'); // 使用按钮关联提供商的尺寸
		  	$firstProvider.data('width')
		  };
		  var selectChannel = function( btn ){
		  	if(btn.hasClass('qq-source') && (ua.browser.family == 'Firefox' || ua.browser.family == 'IE' || navigator.userAgent.search('OPR'))){
		  		$('#K_qq_music_browser_tip').show();
		  	}else{
		  		$('#K_qq_music_browser_tip').hide();
		  	}
		  	var iframe = '<iframe scrolling="no" height="'+btn.data('height')+'" width="'+btn.data('width')+'" frameborder="0" style="overflow:hidden" allowtransparency="true" src="'+btn.data('href')+'"></iframe>'
		    resetAppSizeByProvider( btn );
		    $('#J_iframe').html( iframe );
        appPositionReset();
		  };
		  
		  // 默认加载第一个
		  $submenu.children(':first-child').children('a').click();
		  app.close();
		  app.$elem.fadeIn();
		}
	}

}(jQuery));
