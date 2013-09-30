var ajaxRequest;
var playState = "unable";// 播放器状态，默认不可用
var sign = false;
var next_question_lvlup = false;
var timer;
var is_local_play = false;// 是否播放本地地址
$(function() {
	resetMediaTitle();
	// 提示施工中...
	var style = {
		'top' : $(".J_tabs").offset().top - 20,
		'left' : $(".J_tabs").offset().left + 500,
		'font-size' : "18px",
		'width' : '125px'
	};
	showMsg("施工中 . . .", style, 5000);
	$("#J_mediaLocalPlayButton").click(function() {
		var local_path = $("#J_mediaLocalPath").text();
		if (local_path) {
			playLocalMedia(local_path);
		}
		is_local_play = true;
		$(this).hide();
	})

	// 答题提示
	var ansLayer = $('#answer-tip-layer');
	getTipArea();

	/*
	 * 通过Cookie和id记录实现， 判断用户是否是第一次来英语角。 只有在用户是第一次英语角的情况下， 才显示英语答题提示。
	 */
	if(getCookie("user")==""){
		ansLayer.show();
		setCookie("user", "visited", 30);
	} 
	function setCookie(c_name, value, expiredays) {
		var exdate = new Date()
		exdate.setDate(exdate.getDate() + expiredays)
		document.cookie = c_name
				+ "="
				+ escape(value)
				+ ((expiredays == null) ? "" : ";expires="
						+ exdate.toGMTString())
	}

	function getCookie(c_name) {
		if (document.cookie.length > 0) {
			c_start = document.cookie.indexOf(c_name + "=")
			if (c_start != -1) {
				c_start = c_start + c_name.length + 1
				c_end = document.cookie.indexOf(";", c_start)
				if (c_end == -1)
					c_end = document.cookie.length
				return unescape(document.cookie.substring(c_start, c_end))
			}
		}
		return ""
	}
	
	ansLayer.click(function() {
		$(this).hide();
	});
	$(window).on('resize', function() {
		getTipArea();
	});
	function getTipArea() {
		var docHeight = $(document).height();
		var docWidth = $(document).width();
		var ansLayer = $('#answer-tip-layer');
		ansLayer.find('.layer').height(docHeight);
		var btn = $('#J_answerButton');
		var pos = btn.offset();
		pos.top -= 10;
		pos.left -= 20;
		ansLayer.find('.tip').css({
			left : pos.left - 210,
			top : pos.top - 90
		});
		ansLayer.find('.layer-top').height(pos.top);
		ansLayer.find('.layer-bottom').height(docHeight - pos.top - 60);
		ansLayer.find('.layer-lft').width(pos.left);
		ansLayer.find('.layer-rgt').width(docWidth - 110 - pos.left);
	}

	// 说力，上一句事件
	$("#J_preSentenceButton img").click(function() {
		$('#Links123Player')[0].prev();
	})
	// 说力，慢放事件
	$("#J_slowPlayButton img").click(function() {
		$('#Links123Player')[0].slow();
	})
	// 说力，暂停播放事件
	$("#J_pauseButton img").click(function() {
		$('#Links123Player')[0].playPause();
	})
	// 说力，回放本句事件
	$("#J_replaySentenceButton img").click(function() {
		$('#Links123Player')[0].replay();
	})
	// 说力，下一句事件
	$("#J_nextSentenceButton img").click(function() {
		$('#Links123Player')[0].next();
	})
	bindSpeakListenSwitchEvent();
	//
	// easyloader加载主题和插件
	easyloader.theme = "metro";
	easyloader.load('messager');
	$(".J_tabs a")
			.click(
					function() {
						if ($(this).hasClass("current")) {
							var style = {
								'top' : $(".videottl").offset().top - 30,
								'left' : "45%"
							};
							showMsg("您已在: " + $(this).text(), style, 2000);
							return false;
						} else if ($(this).hasClass("grey")) {
							var top = $(this).offset().top - 15;
							var left = $(this).offset().left + 10;
							fadeTip(
									"<span  class='messager_span'>Coming soon...</span>",
									top, left);
							return false;
						}
						if ($(this).attr("value") != 1) {
							var top = $(this).offset().top - 15;
							var left = $(this).offset().left + 10;
							fadeTip(
									"<span  class='messager_span'>施工中...</span>",
									top, left);
						}
						requestQuestion("switch_view_type", $(this));
					})
	if ($("#J_currentRice").text() == 1000) {
		next_question_lvlup = true;
	}
	$(".scrollable").scrollable({
		circular : true
	});

	// 答题按钮点击事件
	$("#J_answerButton")
			.click(
					function() {

						// $(this).toggleClass("current");
						if ($(".answer").is(":visible")) {
							$("#J_answerButton").removeClass("current");
							if ($("#J_media_div").attr("play_type") == 4) {
								$("#J_media_div").css({
									'position' : '',
									'left' : ''
								});
							}
							$(".answer")
									.slideUp(
											"slow",
											function() { // 这里收起后显示
												if ($("#J_media_div").attr(
														"play_type") == 1
														|| $("#J_media_div")
																.attr(
																		"play_type") == 2) {
													$("#J_media_div").css({
														'display' : 'block',
														'position' : '',
														'left' : ''
													}).show();
												} else if ($("#J_media_div")
														.attr("play_type") == 4) {
													if ($("#Links123Player")[0]) {
														$("#Links123Player")[0]
																.playPause();
													}
												} else if ($("#J_media_div")
														.attr(
																"data_isaboutvideo") == 1) {
													$(".J_player").show();
												} else {
													$("#J_media_div").css({
														'display' : 'block',
														'position' : '',
														'left' : ''
													}).show();
												}
											});

							$(this).text(' 　答  题');
						} else { // 这里先隐藏后展开
							// 播放的视频停止
							if ($("#J_media_div").attr("play_type") == 4) {
								if (playState == "playing") {
									$("#Links123Player")[0].playPause();
								}
								$("#J_media_div").css({
									'position' : 'absolute',
									'left' : '-9999px'
								});
							} else if ($("#J_media_div").attr(
									"data_isaboutvideo") == 1) {
								$(".J_player").hide();
								$('#J_media_div').html('');
							} else {
								$("#J_media_div").css({
									'display' : 'none',
									'position' : 'absolute',
									'left' : '-9999px'
								}).hide();
							}
							$("#J_answerButton").addClass("current");
							$(".answer").slideDown("slow");

							$(this).text(' 　视  频');
						}
					});

	// 科目点击事件
	$('.J_object li')
			.live(
					'click',
					function() {
						if ($(this).hasClass("grey")) {
							var top = $(this).offset().top - 15;
							var left = $(this).offset().left + 10;
							fadeTip(
									"<span  class='messager_span'>Coming soon...</span>",
									top, left);
							return false;
						} else if ($(this).hasClass("current")) {
							var style = {
								'top' : $(".videottl").offset().top - 30,
								'left' : "45%"
							};
							showMsg("您已在科目: " + $(this).text(), style, 2000);
							return false;
						}
						requestQuestion("object", $(this));

					});
	// 等级点击事件
	$(".J_level li")
			.live(
					'click',
					function() {
						if ($(this).hasClass("grey")) {
							var top = $(this).offset().top - 10;
							var left = $(this).offset().left + 5;
							fadeTip(
									"<span  class='messager_span'>Coming soon...</span>",
									top, left);
							return false;
						} else if ($(this).hasClass("current")) {
							var style = {
								'top' : $(".videottl").offset().top - 30,
								'left' : "45%"
							};
							showMsg("您已在等级: " + $(this).text(), style, 2000);
							return false;
						}

						requestQuestion("level", $(this));

					});
	// 专题点击事件
	$('.J_subject li')
			.live(
					'click',
					function() {
						if ($(this).hasClass("grey")) {
							var top = $(this).offset().top - 15;
							var left = $(this).offset().left + 10;
							fadeTip(
									"<span  class='messager_span'>Coming soon...</span>",
									top, left);
							return false;
						} else if ($(this).hasClass("current")) {
							var style = {
								'top' : $(".videottl").offset().top - 30,
								'left' : "45%"
							};
							showMsg("您已在专题: " + $(this).text(), style, 2000);
							return false;
						}
						requestQuestion("subject", $(this));

					});
	// 推荐点击事件
	$('.J_recommend li')
			.live(
					'click',
					function() {
						if ($(this).hasClass("grey")) {
							var top = $(this).offset().top - 15;
							var left = $(this).offset().left + 10;
							fadeTip(
									"<span  class='messager_span'>Coming soon...</span>",
									top, left);
							return false;
						} else if ($(this).hasClass("current")) {
							var style = {
								'top' : $(".videottl").offset().top - 30,
								'left' : "45%"
							};
							showMsg("您已在: " + $(this).text(), style, 2000);
							return false;
						}
						requestQuestion("recommend", $(this));

					});
	// 推荐点击事件
	$('.J_ted li')
			.live(
					'click',
					function() {
						if ($(this).hasClass("grey")) {
							var top = $(this).offset().top - 15;
							var left = $(this).offset().left + 10;
							fadeTip(
									"<span  class='messager_span'>Coming soon...</span>",
									top, left);
							return false;
						} else if ($(this).hasClass("current")) {

							var style = {
								'top' : $(".videottl").offset().top - 30,
								'left' : "45%"
							};
							showMsg("您已在: " + $(this).text(), style, 2000);
							return false;
						}
						requestQuestion("ted", $(this));

					});
	// 难度点击事件
	$('.J_subjectDifficulty li,.J_recommendDifficulty li,.J_tedDifficulty li')
			.live(
					'click',
					function() {
						if ($(this).hasClass("grey")) {
							var top = $(this).offset().top - 15;
							var left = $(this).offset().left + 10;
							fadeTip(
									"<span  class='messager_span'>Coming soon...</span>",
									top, left);
							return false;
						} else if ($(this).hasClass("current")) {
							var style = {
								'top' : $(".videottl").offset().top - 30,
								'left' : "45%"
							};
							showMsg("您已在难度: " + $(this).text(), style, 2000);
							return false;
						}
						requestQuestion("difficulty", $(this));

					});

	//
	// 特别推荐点击
	$("#J_specialReommendDiv img")
			.live(
					'click',
					function() {
						var media_id = $(this).attr("media_id");
						if (media_id > 0) {
							requestQuestion("special_recommend", $(this),
									media_id);
						} else {
							var top = $(this).offset().top - 25;
							var left = $(this).offset().left + 10;
							fadeTip(
									"<span  class='messager_span'>Coming soon...</span>",
									top, left);
							return false;
						}
					})

	// 升级事件
	$("#J_levelUpButton").live(
			'click',
			function() {
				var targetClassName = "J_level";
				var next_level_li;
				var viewType = $(".J_tabs a.current").attr("value");
				if (viewType == 2) {
					targetClassName = "J_subjectDifficulty";
				} else if (viewType == 3) {
					targetClassName = "J_recommendDifficulty";
				} else if (viewType == 5) {
					targetClassName = "J_tedDifficulty";
				}
				$("." + targetClassName + " .current").nextAll("li").each(
						function() {
							if (!$(this).hasClass("not_allowed")
									&& typeof next_level_li == "undefined") {
								next_level_li = $(this);
							}
						})
				if (typeof next_level_li != "undefined") {
					next_level_li.click();
				} else {
					var style = {
						'top' : $(".videottl").offset().top - 30,
						'left' : "45%"
					};
					showMsg("最高级别，无法升级！", style, 2000);
				}
			});

	// 降级事件
	$("#J_levelDownButton").live(
			'click',
			function() {
				var targetClassName = "J_level";
				var prev_level_li;
				var viewType = $(".J_tabs a.current").attr("value");
				if (viewType == 2) {
					targetClassName = "J_subjectDifficulty";
				} else if (viewType == 3) {
					targetClassName = "J_recommendDifficulty";
				} else if (viewType == 5) {
					targetClassName = "J_tedDifficulty";
				}
				$("." + targetClassName + " .current").prevAll("li").each(
						function() {
							if (!$(this).hasClass("not_allowed")
									&& typeof prev_level_li == "undefined") {
								prev_level_li = $(this);
							}
						})
				if (typeof prev_level_li != "undefined") {
					prev_level_li.click();
				} else {
					var style = {
						'top' : $(".videottl").offset().top - 30,
						'left' : "45%"
					};
					showMsg("最小级别，无法降级！", style, 2000);
				}
			});

	//
	// 英音美音点击事件
	$(".menuleft .voice")
			.click(
					function() {
						if ($(this).hasClass("grey")) {
							var top = $(this).offset().top - 15;
							var left = $(this).offset().left;
							fadeTip(
									"<span  class='messager_span'>Coming soon...</span>",
									top, left);
							return false;
						} else if ($(this).hasClass("current")) {
							var style = {
								'top' : $(".videottl").offset().top - 30,
								'left' : "45%"
							};
							showMsg("您已在"
									+ trim_all($(this).children("span").text())
									+ "状态", style, 2000);
							return false;
						}
						requestQuestion("category", $(this));
					});
	//
	// 说力听力点击事件
	$(".menuleft .target")
			.click(
					function() {
						if ($(this).hasClass("grey")) {
							var top = $(this).offset().top - 15;
							var left = $(this).offset().left;
							fadeTip(
									"<span  class='messager_span'>Coming soon...</span>",
									top, left);
							return false;
						} else if ($(this).hasClass("current")) {
							var style = {
								'top' : $(".videottl").offset().top - 30,
								'left' : "45%"
							};
							showMsg("您已在"
									+ trim_all($(this).children("span").text())
									+ "状态", style, 2000);
							return false;
						}
						if ($(this).attr("value") == 2) {
							var top = $(this).offset().top - 15;
							var left = $(this).offset().left;
							fadeTip(
									"<span  class='messager_span'>施工中...</span>",
									top, left);
						}
						requestQuestion("category", $(this));
					});
	//
	// 视频音频点击事件
	$(".menuleft .pattern")
			.click(
					function() {
						if ($(this).hasClass("grey")) {
							var top = $(this).offset().top - 15;
							var left = $(this).offset().left;
							fadeTip(
									"<span  class='messager_span'>Coming soon...</span>",
									top, left);
							return false;
						} else if ($(this).hasClass("current")) {
							var style = {
								'top' : $(".videottl").offset().top - 30,
								'left' : "45%"
							};
							showMsg("您已在"
									+ trim_all($(this).children("span").text())
									+ "状态", style, 2000);
							return false;
						}
						requestQuestion("category", $(this));
					});

	$("#J_nextQuestion").click(function() {
		requestQuestion("quick_select_next");
	})
	$("#J_prevQuestion").click(function() {
		requestQuestion("quick_select_prev");
	})

	/** $视频播放 * */
	$('.J_player')
			.click(
					function() {
						var isAboutVideo = $('#J_media_div').attr(
								'data_isAboutVideo');
						if (isAboutVideo == 1) {
							// $(this).hide();

							var media_url = $('#J_media_div').attr(
									'data_media_url');
							var videoStr = '';
							if (media_url) {
								videoStr += '<object id="J_media_object" height="100%" width="100%" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
								videoStr += '<param name="wmode" value="transparent">';
								videoStr += '<param name="movie" value="'
										+ media_url + '">';
								videoStr += '<embed name="swf" height="'
										+ '100%'
										+ '" width="100%" play="true" type="application/x-shockwave-flash" allowfullscreen="true" wmode="transparent" src="'
										+ media_url + '">';
								videoStr += '</object>';
							} else {
								videoStr += '<span>视频无法加载，刷新重试！</span>'
							}
							$('#J_media_div').html(videoStr);
							checkObjectLoaded();
						}
						return false;
					});
	/** 视频播放$ * */

	/** $用户反馈 * */
	$('.J_feedback').live('click', function() {

		var question_id = $("#J_questionId").text();
		var type = 0; // 0=>视频错误 1=>建议
		var media_html = $('.video_div').html();
		var tip_type = $(this).attr('data-type');

		var style = {
			'top' : $(".videottl").offset().top - 30,
			'left' : "45%"
		};
		showMsg("感谢您的反馈,我们会尽快处理！", style, 2000);

		$.ajax({
			url : URL + '/feedback',
			type : 'POST',
			dataType : 'json',
			data : {
				'type' : type,
				'question_id' : question_id,
				'media_html' : media_html
			},
			success : function(msg) {

			}
		});

		if (tip_type == 'video_tip') {
			requestQuestion("quick_select_next");
		}

		return false;
	});
	/** 用户反馈$ * */

	bindMediaTextClickEvent("disable");
	bindOptionClickEvent();
	trimWhiteSpace();
});

/**
 * 请求题目 响应分类、科目、等级以及上下题的点击，最终目的为请求题目。
 * 
 * @param {string}
 *            type
 *            [请求类型，大类为category，科目为object，等级为level,专题为subject，推荐为recommend，难度为difficulty,特别推荐为special_recommend,ted,上一题下一题为quick_select_prev和quick_select_next
 * @returns {void}
 * @author Adam $date2013-07-26$
 */
function requestQuestion(type, clickObject, media_id) {
	// 如果有题目正在请求，中断此次请求并提示（修改为abort ajax）
	if (typeof ajaxRequest != "undefined") {
		var style = {
			'top' : $(".videottl").offset().top - 30,
			'left' : "45%"
		};
		showMsg("题目正在加载，请稍后...", style, 2000);
		ajaxRequest.abort();
	}
	// 查看类型
	var viewType = $(".J_tabs li a.current").attr("value");
	if (type == "switch_view_type") {
		viewType = clickObject.attr("value");
	}

	var now_question_id = $("#J_questionId").text();
	if ($(".answer").is(":visible")) {
		$("#J_answerButton").click();
	}
	// 特别推荐的类型
	if (type == "special_recommend") {
		viewType = 4;
	}
	// 特别推荐下的上下题
	if (viewType == 3
			&& typeof $(".J_recommend .current").attr("value") == "undefined"
			&& typeof $(".J_recommendDifficulty .current").attr("value") == "undefined") {
		if (type == "quick_select_prev" || type == "quick_select_next") {
			viewType = 4;
		}
	}
	var data = {
		'viewType' : viewType,
		'voice' : $(".voice.current").attr("value"),
		'target' : $(".target.current").attr("value"),
		'pattern' : $(".pattern.current").attr("value"),
		'type' : type,
		'now_question_id' : now_question_id
	};

	var postUrl = '/ajax_get_question';
	if (viewType == 5) {
		data.ted = $(".J_ted .current").attr("value");
		data.difficulty = $(".J_tedDifficulty .current").attr("value");
		postUrl = '/get_question';
	} else if (viewType == 4) {
		data.media_id = media_id;
		postUrl = '/get_question';
	} else if (viewType == 3) {
		data.recommend = $(".J_recommend .current").attr("value");
		data.difficulty = $(".J_recommendDifficulty .current").attr("value");
		postUrl = '/get_question';
	} else if (viewType == 2) {
		data.subject = $(".J_subject .current").attr("value");
		data.difficulty = $(".J_subjectDifficulty .current").attr("value");
		postUrl = '/get_question';
	} else {
		data.object = $(".kecheng .current").attr("value");
		data.level = $(".grade .current").attr("value");
		postUrl = '/get_question';
	}
	// 根据点击的对象，获取最新请求的条件
	if (type == "category") {
		if (clickObject.hasClass("target")) {
			data.target = clickObject.attr("value");
		} else if (clickObject.hasClass("pattern")) {
			data.pattern = clickObject.attr("value");
		} else if (clickObject.hasClass("voice")) {
			data.voice = clickObject.attr("value");
		}
	} else if (type == 'level') {
		data.level = clickObject.attr("value");
	} else if (type == 'object') {
		data.object = clickObject.attr("value");
	} else if (type == "subject") {
		data.subject = clickObject.attr("value");
	} else if (type == "recommend") {
		data.recommend = clickObject.attr("value");
	} else if (type == "ted") {
		data.ted = clickObject.attr("value");
	} else if (type == "difficulty") {
		data.difficulty = clickObject.attr("value");
	}

	if (data.target == 2) {
		postUrl = '/ajax_get_question';
	}

	layer_div("show");
	ajaxRequest = $
			.ajax({
				url : URL + postUrl,
				data : data,
				type : 'POST',
				dataType : 'json',
				cache : false,
				success : function(msg) {

					if (msg) {
						var data = msg.data;
						if (data == null) {
							var style = {
								'top' : $(".videottl").offset().top - 30,
								'left' : "45%"
							};
							showMsg("该题目不存在，请重试...", style, 2000);
							ajaxRequest = undefined;
							return false;
						}

						if (data.question) {
							if (data.question.tested) {
								var content = "做过了？换个类别吧！";
							} else if (data.question.viewed) {
								var content = "看过了？换个题目吧！";
							}
							if (data.question.max) {
								var content = "已经最后一题了，升级吧！";
								if (viewType == 4) {
									content = "已经最后一个了，往前看看吧！";
								}
							} else if (data.question.min) {
								var content = "已经第一题了，降级吧！";
								if (viewType == 4) {
									content = "已经第一个了，往后看看吧！";
								}
							} else {
								if (data.question.play_code == false) {
									window.location.reload();
								}
							}
							if (content) {
								var style = {
									'top' : $(".videottl").offset().top - 30,
									'left' : "45%"
								};
								showMsg(content, style, 2000);
							}

							if (!data.question.id) {
								layer_div();
								return false;
							}
						}
						if (type == "category") {
							//
							// 科目为空
							if (data['object_info'] == null) {
								data['object_info'] = new Array();
								data['object_info']['id'] = 1;
							}
							//
							// 等级为空
							if (data['level_info'] == null) {
								data['level_info'] = new Array();
								data['level_info']['id'] = 1;
							}
							rewriteObjectAndLevelList(data.object_list,
									data['object_info']['id'], data.level_list,
									data['level_info']['id']);
							rewriteReommendAndDifficultyList(
									data.recommend_list,
									data.question.recommend,
									data.recommend_difficulty_list,
									data.question.difficulty);
							rewriteSubjectAndDifficultyList(data.subject_list,
									data.question.subject,
									data.subject_difficulty_list,
									data.question.difficulty);
						}

						if (viewType == 5) {
							if (type == "ted") {
								rewriteTedAndDifficultyList(null, 0,
										data.ted_difficulty_list,
										data.question.difficulty);
							} else if (type == "switch_view_type") {
								rewriteTedAndDifficultyList(data.ted_list,
										data.question.ted,
										data.ted_difficulty_list,
										data.question.difficulty);
							}
						} else if (viewType == 4) {
							rewriteReommendAndDifficultyList(
									data.recommend_list, 0,
									data.recommend_difficulty_list, 0);
							$(".J_recommend li.current").removeClass("current");
							$(".J_recommendDifficulty li.current").removeClass(
									"current");
						} else if (viewType == 3) {
							if (type == "recommend") {
								rewriteReommendAndDifficultyList(null, 0,
										data.recommend_difficulty_list,
										data.question.difficulty);
							} else if (type == "quick_select_prev"
									|| type == "switch_view_type") {
								rewriteReommendAndDifficultyList(
										data.recommend_list,
										data.question.recommend,
										data.recommend_difficulty_list,
										data.question.difficulty);
							}
						} else if (viewType == 2) {
							if (type == "subject") {
								rewriteSubjectAndDifficultyList(null, 0,
										data.subject_difficulty_list,
										data.question.difficulty);
							} else if (type == "quick_select_prev"
									|| type == "switch_view_type") {
								rewriteSubjectAndDifficultyList(
										data.subject_list,
										data.question.subject,
										data.subject_difficulty_list,
										data.question.difficulty);
							}
						} else {
							if (type != "category") {
								//
								// 科目为空
								if (data['object_info'] == null) {
									data['object_info'] = new Array();
									data['object_info']['id'] = 0;
								}
								//
								// 等级为空
								if (data['level_info'] == null) {
									data['level_info'] = new Array();
									data['level_info']['id'] = 0;
								}
								rewriteObjectAndLevelList(data.object_list,
										data['object_info']['id'],
										data.level_list,
										data['level_info']['id']);
							}
						}
						//
						// 更新题目
						var question = data.question;
						//
						// 更改美音英音等状态
						$(".menuleft .voice[value='" + question.voice + "']")
								.addClass("current").siblings(".voice")
								.removeClass("current");
						$(".menuleft .target[value='" + question.target + "']")
								.addClass("current").siblings(".target")
								.removeClass("current");
						$(
								".menuleft .pattern[value='" + question.pattern
										+ "']").addClass("current").siblings(
								".pattern").removeClass("current");
						//
						if (question.content == null) {
							$(".answertitle").text("");
						} else {
							$(".answertitle").text(question.content);
						}
						$("#J_questionId").text(question.id);
						$("#J_mediaTitle").text(question.name).attr("title",
								question.name);
						resetMediaTitle();
						$("#J_textButton").attr("media_text_url",
								question.media_source_url);
						is_local_play = false;
						if (question.media_local_path) {
							$("#J_mediaLocalPath").text(
									question.media_local_path);
							$("#J_mediaLocalPlayButton").show();
						} else {
							$("#J_mediaLocalPath").text("");
							$("#J_mediaLocalPlayButton").hide();
						}
						bindMediaTextClickEvent("disable");// 点击选项后才能查看文本
						updateOption(question.option);
						// 说力听力耳朵切换
						if (question.target == 2) {
							$(".J_listenButtons").hide();
							$(".J_speakButtons").show();
							$(".playbutton").show();
							$(".voice[value='2']").addClass("grey").children(
									"span").addClass("grey");
						} else {
							$(".playbutton").hide();
							$(".J_speakButtons").hide();
							$(".J_listenButtons").show();
							$(".voice[value='2']").removeClass("grey")
									.children("span").removeClass("grey");
						}
						if (question.voice == 2) {
							$(".target[value='2']").addClass("grey").children(
									"span").addClass("grey");
						} else {
							$(".target[value='2']").removeClass("grey")
									.children("span").removeClass("grey");
						}
						//
						if (data['user_count_info'] == null
								|| data['user_count_info']['right_num'] == null) {
							data['user_count_info']['right_num'] = 0;
						}

						// 是否已经满10题，是则设置下一题自动升级
						if (viewType == 1
								&& data['user_count_info']['right_num'] >= 10) {
							next_question_lvlup = true;
						} else {
							next_question_lvlup = false;
						}
						if (viewType == 1 && next_question_lvlup) {
							//
							// 提示可以升级了
							var style = {
								'top' : $(".videottl").offset().top - 30,
								'left' : "45%"
							};
							showMsg("恭喜，你可以升级了", style, 2000);
						}

						$("#J_currentRice").text(
								data['user_count_info']['right_num'] * 100);
						if (data['user_count_info']['right_num'] == 0) {
							$("#J_riceDiv").attr("src", "");
							$("#J_riceDiv").hide();
						} else {
							$("#J_riceDiv")
									.attr(
											"src",
											PUBLIC
													+ "/English/images/rice"
													+ data['user_count_info']['right_num']
													* 100 + ".png?2013092301");
							$("#J_riceDiv").show();
						}
						// $("#J_riceDiv").removeClass().addClass("rice_" +
						// data['user_count_info']['right_num'] * 100);
						//
						/** $视频播放 * */
						var videoStr = '';
						$('#J_media_div').html(videoStr);

						if (question.play_code || question.media_info) {

							if (question.isAboutVideo != 1) {
								if (question.target == 2) {
									var media_info = question.media_info;
									$("#J_mediaId").text(media_info.id);// 记录媒体id
									$("#J_mediaPath").text(
											media_info.question_id);// 记录媒体地址
									$('#J_media_div').html(
											"<div id='flashContent'></div>");
									var swfVersionStr = "10.2.0";
									var xiSwfUrlStr = PUBLIC
											+ "/Js/Links123Player/playerProductInstall.swf";
									var flashvars = {};
									flashvars.logging = true;
									flashvars.url = media_info.real_path;
									flashvars.playerMode = 2;
									flashvars.url = $("#J_mediaPath").text();
									flashvars.questionId = media_info.question_id;
									flashvars.rate = 44;// 采样率配置
									flashvars.codec = 1;// 模式 1.默认模式 2.speex压缩模式
									flashvars.mediaId = media_info.id;
									flashvars.datahost = encodeURIComponent(URL
											+ "/requestionMediaInfo");
									flashvars.sendDataHost = encodeURIComponent(URL
											+ "/speakScore");
									flashvars.mp3host = encodeURIComponent("http://dict.youdao.com/dictvoice?audio=###&type=1");
									flashvars.wordhost = URL
											+ "/requestionWordInfo";
									var params = {};
									params.quality = "high";
									params.bgcolor = "#ffffff";
									params.allowscriptaccess = "sameDomain";
									params.allowfullscreen = "true";
									var attributes = {};
									attributes.id = "Links123Player";
									attributes.name = "Links123Player";
									attributes.align = "middle";
									swfobject
											.embedSWF(
													PUBLIC
															+ "/Js/Links123Player/Links123Player.swf",
													"flashContent",
													media_width, media_height,
													swfVersionStr, xiSwfUrlStr,
													flashvars, params,
													attributes);
									swfobject.createCSS("#flashContent",
											"display:block;text-align:left;");
									$("#J_viewButton").addClass("current");
									$("#J_speakButton").removeClass("current");
								} else if (question.play_type == 1) {
									videoStr = question.play_code;
								} else if (question.play_type == 2) {
									videoStr = '<iframe class="media_iframe" src="'
											+ question.play_code
											+ '" width="100%" height="100%" scrolling="no" frameborder="0">';
								} else if (question.play_type == 3) {

									$('#J_media_div')
											.html(
													'<div id="J_media_swfobject_div"></div>');

									var swfUrl = 'http://www.kizphonics.com/wp-content/uploads/jw-player-plugin-for-wordpress/player/player.swf';
									var version = '10.2.0';
									var params = {
										quality : "high",
										wmode : "opaque",
										scale : "noscale",
										align : "left",
										allowFullScreen : "true",
										allowScriptAccess : "always",
										bgColor : "#000000"
									};

									swfobject.embedSWF(swfUrl,
											"J_media_swfobject_div", "100%",
											"100%", version,
											"/swf/playerProductInstall.swf",
											question.play_code, params);

								} else if (question.play_type == 4
										&& question.target == 1) {
									playLocalMedia(question.play_code);
									if (question.priority_type == 2) {
										is_local_play = true;
										$("#J_mediaLocalPlayButton").hide();
									}
									// $('#J_media_div').html('<div
									// id="J_media_swfobject_div"
									// style="height:' + media_height +
									// 'px;width:' + media_width +
									// 'px;"></div>');

									// flowplayer("J_media_swfobject_div",
									// "http://releases.flowplayer.org/swf/flowplayer-3.2.16.swf",
									// {playlist: [question.media_thumb_img,
									// {url: question.play_code, autoPlay:
									// false}]});

								} else {

									videoStr += '<object id="J_media_object" height="100%" width="100%" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
									videoStr += '<param name="wmode" value="transparent">';
									videoStr += '<param name="movie" value="'
											+ question.play_code + '">';
									videoStr += '<embed name="swf" menu="true" height="100%" width="100%" play="true" type="application/x-shockwave-flash" allowfullscreen="true" wmode="transparent" src="'
											+ question.play_code + '">';
									videoStr += '</object>';
								}
							}
						} else {

							videoStr += '<p class="video_tip_error">该视频无法正常播放了，<a href="javascript:void(0);" class="J_feedback" data-type="video_tip">请反馈给我们</a>，谢谢!</p>';
						}

						if (question.isAboutVideo) {
							if (question.media_thumb_img != "") {
								$('#J_media_img').attr('src',
										question.media_thumb_img);
							} else {
								$('#J_media_img')
										.attr(
												'src',
												PUBLIC
														+ "/English/images/deafult_media_img.jpg");
							}
							$('#J_media_div').css('visibility', 'hidden');

							$('.J_player').css('display', 'block');

						} else {
							$('.J_player').css('display', 'none');

							$('#J_media_div').css({
								'height' : media_height,
								'width' : media_width,
								'visibility' : ''
							});
						}

						if ($("#J_media_div").css('display') == 'none') {
							$("#J_media_div").css({
								'display' : '',
								'position' : '',
								'left' : ''
							}).show();
						}

						if (question.isAboutVideo == 1) {
							$('#J_media_div').attr('data_media_url',
									question.play_code);
						} else {
							$('#J_media_div').attr('data_media_url', '');
						}
						$('#J_media_div').attr('play_type', question.play_type);

						$('#J_media_div').attr('data_isAboutVideo',
								question.isAboutVideo);

						if (videoStr) {
							$('#J_media_div').html(videoStr);
						}

						/** 视频播放$ * */
						trimWhiteSpace();
						layer_div("hide");

						if (type == 'category') {
							if (clickObject.hasClass("target")) {
								clickObject.addClass("current").siblings(
										"li.target").removeClass("current");
							} else if (clickObject.hasClass("pattern")) {
								clickObject.addClass("current").siblings(
										"li.pattern").removeClass("current");
							} else if (clickObject.hasClass("voice")) {
								clickObject.addClass("current").siblings(
										"li.voice").removeClass("current");
							}
							$(".J_tabs a[value='" + data.viewType + "']")
									.addClass("current").parent("li").siblings(
											"li").children("a").removeClass(
											"current");
							$(".panes > div:eq(" + (data.viewType - 1) + ")")
									.show().siblings().hide();
						} else if (type == "object" || type == "level"
								|| type == "subject" || type == "recommend"
								|| type == "ted" || type == "difficulty") {
							clickObject.addClass("current").siblings("li")
									.removeClass("current");
						} else if (type == "switch_view_type") {
							var index = clickObject.parent("li").index();
							if (viewType == 1) {
								$("#J_currentRiceNameLabel").text("本级：");
							} else {
								$("#J_currentRiceNameLabel").text("本类：");
							}
							clickObject.addClass("current");
							clickObject.parent("li").siblings().children("a")
									.removeClass("current");
							$(".panes > div:eq(" + index + ")").show()
									.siblings().hide();
						} else if (type == "special_recommend") {
							$(".J_tabs a[value='3']").addClass("current")
									.parent("li").siblings("li").children("a")
									.removeClass("current");
							$(".panes > div:eq(2)").show().siblings().hide();
						}
						if (data.recommedsQuestionNum > 0) {
							$(".J_tabs a[value='3']").removeClass("grey");
						} else {
							$(".J_tabs a[value='3']").addClass("grey");
						}
						if (data.subjectsQuestionNum > 0) {
							$(".J_tabs a[value='2']").removeClass("grey");
						} else {
							$(".J_tabs a[value='2']").addClass("grey");
						}
						if (data.tedsQuestionNum > 0) {
							$(".J_tabs a[value='5']").removeClass("grey");
						} else {
							$(".J_tabs a[value='5']").addClass("grey");
						}
						ajaxRequest = undefined;
						return true;
					} else {

						ajaxRequest = undefined;
						return false;
					}
				},
				error : function() {

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

	return (typeof ajaxRequest != 'undefined')
			&& (typeof ajaxRequest == 'object');
}

//
/**
 * 绑定选项点击事件
 * 
 * @returns {void}
 * @author Adam $date2013-07-26$
 */
function bindOptionClickEvent() {
	$(".J_option").unbind("click");
	$(".J_option")
			.click(
					function() {
						var target = $(this);
						var select_option = $(this).attr("value");
						var object = $(".kecheng .current").attr("value");
						var viewType = $(".J_tabs a.current").attr("value");
						var data = {
							'question_id' : $("#J_questionId").text(),
							'viewType' : viewType,
							'object' : object,
							'select_option' : select_option
						};
						if (viewType == 3) {
							if (typeof $(".J_recommend .current").attr("value") == "undefined"
									&& typeof $(
											".J_recommendDifficulty .current")
											.attr("value") == "undefined") {
								viewType = 4;
								data.viewType = viewType;
							} else {
								data.recommend = $(".J_recommend .current")
										.attr("value");
							}
						}
						$
								.post(
										URL + "/answer_question",
										data,
										function(msg) {
											if (msg.status) {
												var data = msg.data;
												var user_count_info = data.user_count_info
												if (user_count_info == null
														|| user_count_info.right_num == null) {
													user_count_info.right_num = 0;
												}
												// $("#J_rightNum").text(user_count_info.right_num);
												$("#J_currentRice")
														.text(
																user_count_info.right_num * 100);
												if (data['user_count_info']['right_num'] == 0) {
													$("#J_riceDiv").attr("src",
															"");
													$("#J_riceDiv").hide();
												} else {
													$("#J_riceDiv")
															.attr(
																	"src",
																	PUBLIC
																			+ "/English/images/rice"
																			+ data['user_count_info']['right_num']
																			* 100
																			+ ".png?2013092301");
													$("#J_riceDiv").show();
												}
												// $("#J_riceDiv").removeClass().addClass("rice_"
												// + user_count_info.right_num *
												// 100);
												//
												var english_user_info = data.english_user_info;
												if (english_user_info == null) {
													english_user_info.total_rice = 0;
												}
												$("#J_totalRice")
														.text(
																english_user_info.total_rice);

												if (data.level_up) {
													var style = {
														'top' : $(".videottl")
																.offset().top - 30,
														'left' : "45%"
													};
													showMsg("恭喜，你可以升级了", style,
															2000);
												}
												// 最佳科目和等级
												// if(english_user_info!=null){
												// if(english_user_info.best_object!=0&&english_user_info.best_level!=0&&english_user_info.best_object!=null&&english_user_info.best_level!=null){
												// $("#J_best_level").text(english_user_info.best_object_name+"/"+english_user_info.best_level_name);
												// }
												// }
												answer = data.question_info.answer;
												if (select_option == answer) {
													// target.children(".J_optionContent").children("span").removeClass("gc").addClass("right");
													target.children(".gc")
															.removeClass("gc")
															.addClass("right");
												} else {
													target.children(".gc")
															.removeClass("gc")
															.addClass("wrong");

													setTimeout(
															function() {
																$(
																		'#J_option_'
																				+ answer)
																		.children(
																				".gc")
																		.removeClass(
																				"gc")
																		.addClass(
																				"right");
															}, 1000);
													// target.children(".J_optionContent").children("span").removeClass("gc").addClass("wrong");
													// setTimeout("$(\".J_option[value='\"+answer+\"']\").children(\".J_optionContent\").children(\"span\").removeClass(\"gc\").addClass(\"right\");",
													// 1000);
												}
												$(".J_option").unbind("click");
												// 更新排行榜
												var now_cat = $(
														".ranklist .J_now_Object")
														.attr("value");
												$(
														".ranklist .ranklist-hd ul a[type='"
																+ now_cat
																+ "']").click();
												//
												// 根据用户上次答题情况进行提示操作
												var english_user_record = data.english_user_record;
												// 做对的再做对
												if (english_user_record.is_right == 1
														&& english_user_record.right_num >= 2) {
													// 无新题自动升级
													var content = "";
													if (data.question_info.untested_num == 0) {
														next_question_lvlup = true;
														content = "本题已做对"
																+ english_user_record.right_num
																+ "次，升级吧";
													} else {
														next_question_lvlup = false;
														content = "本题已做对"
																+ english_user_record.right_num
																+ "次，换新题吧";
													}
													var style = {
														'top' : $(".videottl")
																.offset().top - 30,
														'left' : "45%"
													};
													showMsg(content, style,
															2000);
												} else if (english_user_record.is_right == 0
														&& english_user_record.error_num >= 2) {
													var style = {
														'top' : $(".videottl")
																.offset().top - 30,
														'left' : "45%"
													};
													showMsg("唉，又错了！", style,
															2000);
												}
											}
											bindMediaTextClickEvent();
											$(".J_option").unbind();
											$(".J_option").css("cursor",
													"default");
										}, "json");
					})
}
/**
 * 重写科目和等级列表
 * 
 * @param {object}
 *            object_list [科目列表]
 * @param {int}
 *            object [当前科目id]
 * @param {object}
 *            level_list [等级列表]
 * @param {int}
 *            level [当前等级id]
 * @returns {void}
 * @author Adam $date2013.08.31$
 */
function rewriteObjectAndLevelList(object_list, object, level_list, level) {
	//
	// 更新科目列表
	if (object_list != null) {
		var str = '';
		for ( var i = 0; i < object_list.length; i++) {
			str += '<li value="' + object_list[i]['id'] + '"';
			if (object == object_list[i]['id']) {
				str += ' class="current" ';
			} else {
				if (object_list[i]['question_num'] == 0) {
					str += ' class="grey not_allowed" ';
				}
			}
			str += '><span>' + object_list[i]['name'] + '</span></li>';
		}
		$(".J_object").html(str);
		if ($(".J_object .current").size() < 1) {
			$(".J_object li").not(".grey").first().addClass("current");
		}
	}
	//
	// 更新等级列表
	if (level_list != null) {
		var str = '';
		for ( var i = 0; i < level_list.length; i++) {
			str += '<li value="' + level_list[i]['id'] + '"';
			if (level == level_list[i]['id']) {
				str += ' class="current" ';
			} else {
				if (level_list[i]['question_num'] == 0) {
					str += ' class="grey not_allowed" ';
				}
			}
			str += '><span>' + level_list[i]['name'] + '</span></li>';
		}
		$(".J_level").html(str);
		if ($(".J_level .current").size() < 1) {
			$(".J_level li").not(".grey").first().addClass("current");
		}
		// $(".J_level li:eq(13)").css("margin-left", level_margin_index +
		// "px");
	}
}
/**
 * 重写专题和难度列表
 * 
 * @param {object}
 *            subject_list [专题列表]
 * @param {int}
 *            subject [当前专题id]
 * @param {object}
 *            difficulty_list [难度列表]
 * @param {int}
 *            difficulty [当前难度id]
 * @returns {void}
 * @author Adam $date2013.09.03$
 */
function rewriteSubjectAndDifficultyList(subject_list, subject,
		difficulty_list, difficulty) {
	//
	// 更新专题列表
	if (subject_list != null) {
		var str = '';
		for ( var i = 0; i < subject_list.length; i++) {
			str += '<li value="' + subject_list[i]['id'] + '"';
			if (subject == subject_list[i]['id']) {
				str += ' class="current" ';
			} else {
				if (subject_list[i]['question_num'] == 0) {
					str += ' class="grey not_allowed" ';
				}
			}
			str += '><span>' + subject_list[i]['name'] + '</span></li>';
		}
		$(".J_subject").html(str);
		if ($(".J_subject .current").size() < 1) {
			$(".J_subject li").not(".grey").first().addClass("current");
		}
	}
	//
	// 更新等级列表
	if (difficulty_list != null) {
		var str = '';
		for ( var i = 0; i < difficulty_list.length; i++) {
			str += '<li value="' + difficulty_list[i]['id'] + '"';
			if (difficulty == difficulty_list[i]['id']) {
				str += ' class="current" ';
			} else {
				if (difficulty_list[i]['question_num'] == 0) {
					str += ' class="grey not_allowed" ';
				}
			}
			str += '><span>' + difficulty_list[i]['name'] + '</span></li>';
		}
		$(".J_subjectDifficulty").html(str);
		if ($(".J_subjectDifficulty .current").size() < 1) {
			$(".J_subjectDifficulty li").not(".grey").first().addClass(
					"current");
		}
	}
}
/**
 * 重写推荐和难度列表
 * 
 * @param {object}
 *            recommend_list [推荐列表]
 * @param {int}
 *            recommend [当前推荐id]
 * @param {object}
 *            difficulty_list [难度列表]
 * @param {int}
 *            difficulty [当前难度id]
 * @returns {void}
 * @author Adam $date2013.09.03$
 */
function rewriteReommendAndDifficultyList(recommend_list, recommend,
		difficulty_list, difficulty) {
	//
	// 更新专题列表
	if (recommend_list != null) {
		var str = '';
		for ( var i = 0; i < recommend_list.length; i++) {
			str += '<li value="' + recommend_list[i]['id'] + '"';
			if (recommend == recommend_list[i]['id']) {
				str += ' class="current" ';
			} else {
				if (recommend_list[i]['question_num'] == 0) {
					str += ' class="grey not_allowed" ';
				}
			}
			str += '><span>' + recommend_list[i]['name'] + '</span></li>';
		}
		$(".J_recommend").html(str);
		if ($(".J_recommend .current").size() < 1) {
			$(".J_recommend li").not(".grey").first().addClass("current");
		}
	}
	//
	// 更新等级列表
	if (difficulty_list != null) {
		var str = '';
		for ( var i = 0; i < difficulty_list.length; i++) {
			str += '<li value="' + difficulty_list[i]['id'] + '"';
			if (difficulty == difficulty_list[i]['id']) {
				str += ' class="current" ';
			} else {
				if (difficulty_list[i]['question_num'] == 0) {
					str += ' class="grey not_allowed" ';
				}
			}
			str += '><span>' + difficulty_list[i]['name'] + '</span></li>';
		}
		$(".J_recommendDifficulty").html(str);
		if ($(".J_recommendDifficulty .current").size() < 1) {
			$(".J_recommendDifficulty li").not(".grey").first().addClass(
					"current");
		}
	}
}
/**
 * 重写TED和难度列表
 * 
 * @param {object}
 *            ted_list [TED列表]
 * @param {int}
 *            ted [当前TEDid]
 * @param {object}
 *            difficulty_list [难度列表]
 * @param {int}
 *            difficulty [当前难度id]
 * @returns {void}
 * @author Adam $date2013.09.03$
 */
function rewriteTedAndDifficultyList(ted_list, ted, difficulty_list, difficulty) {
	//
	// 更新专题列表
	if (ted_list != null) {
		var str = '';
		for ( var i = 0; i < ted_list.length; i++) {
			str += '<li value="' + ted_list[i]['id'] + '"';
			if (ted == ted_list[i]['id']) {
				str += ' class="current" ';
			} else {
				if (ted_list[i]['question_num'] == 0) {
					str += ' class="grey not_allowed" ';
				}
			}
			str += '><span>' + ted_list[i]['name'] + '</span></li>';
		}
		$(".J_ted").html(str);
		if ($(".J_ted .current").size() < 1) {
			$(".J_ted li").not(".grey").first().addClass("current");
		}
	}
	//
	// 更新等级列表
	if (difficulty_list != null) {
		var str = '';
		for ( var i = 0; i < difficulty_list.length; i++) {
			str += '<li value="' + difficulty_list[i]['id'] + '"';
			if (difficulty == difficulty_list[i]['id']) {
				str += ' class="current" ';
			} else {
				if (difficulty_list[i]['question_num'] == 0) {
					str += ' class="grey not_allowed" ';
				}
			}
			str += '><span>' + difficulty_list[i]['name'] + '</span></li>';
		}
		$(".J_tedDifficulty").html(str);
		if ($(".J_tedDifficulty .current").size() < 1) {
			$(".J_tedDifficulty li").not(".grey").first().addClass("current");
		}
	}
}
/**
 * 更新选项
 * 
 * @param {array}
 *            option [选项数组]
 * @returns {void}
 * @author Adam $date2013-07-26$
 */
function updateOption(option) {
	var str = "";
	$(".J_option").css("cursor", "pointer");
	$('.answer').hide();

	if (option != null) {
		for ( var i in option) {
			str += '<p class="J_option" id="J_option_' + option[i]['id']
					+ '" value="' + option[i]['id']
					+ '"><span class="gc"></span>'
					+ '<span class="ricenumber">';
			if (i == 0) {
				str += "A";
			} else if (i == 1) {
				str += "B";
			} else if (i == 2) {
				str += "C";
			} else if (i == 3) {
				str += "D";
			}
			str += "</span><span class='J_optionContent'>"
					+ option[i]['content'] + '</span></p>';
		}
	}
	$(".J_option").remove();
	$(".answertitle").after(str);
	// bindOptionHoverEvent();
	bindOptionClickEvent();// 点击选项
}
//
/*
 * 绑定文本点击事件 @param {string} type [操作] @returns {undefined}
 */
function bindMediaTextClickEvent(type) {
	$("#J_textButton").unbind("click");
	if (type != "disable") {
		$("#J_textButton").click(function() {
			var url = trim($(this).attr("media_text_url"));
			if (url == "") {
				var style = {
					'top' : $(".videottl").offset().top - 30,
					'left' : "45%"
				};
				showMsg("对不起，文本不存在!", style, 2000);
				return false;
			}
			window.open(url, "_blank");
		})
	} else {
		$("#J_textButton").click(function() {
			var style = {
				'top' : $(".videottl").offset().top - 30,
				'left' : "45%"
			};
			showMsg("答题后才能查看文本！", style, 2000);
			return false;
		})
	}
}

/**
 * 操作透明阻挡层的方法
 * 
 * @param {string}
 *            type [显示还是隐藏]
 * @returns {void}
 * @author Adam $date2013-07-20$
 */
function layer_div(type) {
	var layer_div = $(".layer_div_img");
	if (type == "show") {
		if (layer_div.hasClass("layer_div_img")) {
			layer_div.show();
		} else {
			layer_div = $("<img class='layer_div_img' src='" + PUBLIC
					+ "/English/images/loading.gif' />");
			$("body").append(layer_div);
		}
		var top = window.screen.availHeight * 0.4;
		// var left = window.screen.availWidth * 0.5 - 95;
		var left = '48%'; // 用于在播放器居中显示
		layer_div.css("position", "absolute").css("top", top).css("left", left)
				.css("z-index", "9999");

	} else {
		layer_div.hide();
	}
}

//
/**
 * 去除试题内容以及选项的内容中的空格
 * 
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
 * 
 * @param {string}
 *            str
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
 * 
 * @param {string}
 *            str 需要处理的字符串
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
	} else if (window.sidebar) {
		if (window.netscape) {
			try {
				netscape.security.PrivilegeManager
						.enablePrivilege("UniversalXPConnect");
			} catch (e) {
				alert("该操作被浏览器拒绝, 如果想启用该功能, 请在地址栏内输入 about:config, 然后将项 signed.applets.codebase_principal_support 值改为 true.");
			}
		}
		//
		if (window.confirm("你确定要设置" + url + "为首页吗？") == 1) {
			var prefs = Components.classes['@mozilla.org/preferences-service;1']
					.getService(Components.interfaces.nsIPrefBranch);
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
			'height' : media_height,
			'width' : media_width,
			'visibility' : ''
		});
		layer_div("hide");
	} else {
		setTimeout('checkObjectLoaded()', 100);
	}
}

/**
 * 闪现提示
 * 
 * @param {string}
 *            content [内容文本]
 * @param {number}
 *            top [顶部距离]
 * @param {number}
 *            left [左边距离]
 * @param {number}
 *            time [显示时间]
 * @param {number}
 *            index [div层的z-index]
 * @returns {void}
 * @author Adam $date2013.08.29$
 */
function fadeTip(content, top, left, time, index) {
	clearTimeout(timer);
	var time = arguments[4] ? arguments[4] : 2000;
	var index = arguments[5] ? arguments[5] : 99;
	$('.J_fadeDiv').remove();

	var div = $("<div class='J_fadeDiv' style='color:#ffffff;display:none;position:absolute;z-index:"
			+ index + ";top:" + top + "px;left:" + left + "px;'></div>");
	$("body").append(div);
	div.html(content);
	div.fadeIn(1000);
	timer = setTimeout(
			"$('.J_fadeDiv').fadeOut(1000,function(){$('.J_fadeDiv').remove();})",
			time);
}

function showMsg(content, style, time, is_alert) {
	clearTimeout(timer);
	$('.J_fadeDiv').remove();
	style = arguments[1] ? arguments[1] : {
		top : "30%",
		left : "45%",
		index : 99,
		witdh : "auto"
	};
	time = arguments[2] ? arguments[2] : 2000;
	is_alert = arguments[3] ? arguments[3] : false;

	var div = $("<div class=\"autobox autobox-text J_fadeDiv\" style='display:none;position:absolute;'><div class=\"autobox-layer\"><span class=\"autobox-arr\"></span>"
			+ "<div class=\"autobox-ct\">"
			+ content
			+ "</div><span class=\"autobox-end\"></span></div></div>");
	div.css(style);
	$("body").append(div);
	if (is_alert) {
		div.addClass("autobox-hits").removeClass("autobox-text");
		div.css("padding-left:65px;");
	} else {
		div.find(".autobox-ct").css("padding", "0 20px");
	}
	div.find(".autobox-ct").css("width", div.width() - 34);
	div.fadeIn(1000);
	timer = setTimeout(
			"$('.J_fadeDiv').fadeOut(1000,function(){$('.J_fadeDiv').remove();})",
			time);
}
function bindSpeakListenSwitchEvent() {
	$(".J_speakButtons")
			.click(
					function() {
						if ($(this).hasClass("current")) {
							return false;
						}
						var playerMode = 2;
						sign = false;
						if ($(this).attr("id") == "J_speakButton") {
							playerMode = 3;
							sign = true;
						}
						$("#Links123Player")[0].playerChoose(playerMode);
						$(this).addClass("current").siblings(".J_speakButtons")
								.removeClass("current");
						return false;
						// 旧的切换
						$("#J_media_div").html("<div id='flashContent'></div>");
						var swfVersionStr = "10.2.0";
						var xiSwfUrlStr = PUBLIC
								+ "/Js/Links123Player/playerProductInstall.swf";
						var flashvars = {};
						if ($(this).attr("id") == "J_speakButton") {
							flashvars.playerMode = 3;
						} else {
							flashvars.playerMode = 2;
						}
						flashvars.logging = true;
						flashvars.url = $("#J_mediaPath").text();
						flashvars.questionId = $("#J_questionId").text();
						flashvars.rate = 22;// 采样率配置
						flashvars.codec = 1;// 模式 1.默认模式 2.speex压缩模式
						flashvars.mediaId = $("#J_mediaId").text();
						flashvars.datahost = encodeURIComponent(URL
								+ "/requestionMediaInfo");
						flashvars.sendDataHost = encodeURIComponent(URL
								+ "/speakScore");
						flashvars.googleHost = google_host;
						flashvars.mp3host = mp3host;
						flashvars.wordhost = encodeURIComponent(URL
								+ "/requestionWordInfo");
						var params = {};
						params.quality = "high";
						params.bgcolor = "#ffffff";
						params.allowscriptaccess = "sameDomain";
						params.allowfullscreen = "true";
						var attributes = {};
						attributes.id = "Links123Player";
						attributes.name = "Links123Player";
						attributes.align = "middle";
						swfobject.embedSWF(PUBLIC
								+ "/Js/Links123Player/Links123Player.swf",
								"flashContent", media_width, media_height,
								swfVersionStr, xiSwfUrlStr, flashvars, params,
								attributes);
						swfobject.createCSS("#flashContent",
								"display:block;text-align:left;");
						$(this).addClass("current").siblings(".J_speakButtons")
								.removeClass("current");
					})
}
/**
 * 播放器状态改变接口
 * 
 * @param {string}
 *            state
 * @returns {string}
 * @author Adam $date2013.08.22$
 */
function playerStateChange(state) {
	playState = state;
	// alert(state);
	if ($(".target.current").attr("value") == 2) {
		if (state == "playing") {
			$("#J_pauseButton img").attr("src",
					PUBLIC + "/English/images/video6.png");
		} else {
			$("#J_pauseButton img").attr("src",
					PUBLIC + "/English/images/video5.png");
		}
		if (state == "completed") {
			// 测试由看切换到说
			if (sign == false) {
				$("#Links123Player")[0].playerChoose(3);
				$("#J_speakButton").addClass("current");
				$("#J_viewButton").removeClass("current");
				$("#Links123Player")[0].playPause();
				sign = true;
			}
		}
	}
	return state;
}

function playLocalMedia(local_path) {
	if ($(".focus_player")) {
		$(".focus_player").hide();
	}
	$("#J_media_div").html("");
	$("#J_media_div").css("visibility", "");
	var type = /\.[^\.]+$/.exec(local_path);
	type = type.toString().toLowerCase();
	if (type == ".swf") {
		var videoStr = "";
		videoStr += '<object id="J_media_object" height="100%" width="100%" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000">';
		videoStr += '<param name="wmode" value="transparent">';
		videoStr += '<param name="movie" value="' + local_path + '">';
		videoStr += '<embed name="swf" menu="true" height="100%" width="100%" play="true" type="application/x-shockwave-flash" allowfullscreen="true" wmode="transparent" src="'
				+ local_path + '">';
		videoStr += '</object>';
		$("#J_media_div").html(videoStr);
		$("#J_media_div").attr("play_type", "0");
	} else {
		$("#J_media_div").html("<div id='flashContent'></div>");
		var swfVersionStr = "10.2.0";
		var xiSwfUrlStr = PUBLIC
				+ "/Js/Links123Player/playerProductInstall.swf";
		var flashvars = new Object();
		flashvars.logging = true;
		flashvars.playerMode = 1;// 播放器播放类型，1一般看，2说力看，3说力说模式
		flashvars.url = local_path;
		var params = new Object();
		params.quality = "high";
		params.bgcolor = "#ffffff";
		params.allowscriptaccess = "sameDomain";
		params.allowfullscreen = "true";
		var attributes = new Object();
		attributes.id = "Links123Player";
		attributes.name = "Links123Player";
		attributes.align = "middle";
		swfobject.embedSWF(PUBLIC + "/Js/Links123Player/Links123Player.swf",
				"flashContent", media_width, media_height, swfVersionStr,
				xiSwfUrlStr, flashvars, params, attributes);
		swfobject.createCSS("#flashContent", "display:block;text-align:left;");
		$("#J_media_div").attr("play_type", "4");
	}
}

function resetMediaTitle() {
	var text = $("#J_mediaTitle").text();
	var length = text.length;
	var index = 40;
	if (screen_type == 2) {
		index = 60;
	}
	text = text.substr(0, index);
	if (text.length < length) {
		text += "...";
	}
	$("#J_mediaTitle").text(text);
	// $("#J_mediaTitle").tooltip();
}
