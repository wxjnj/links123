// 编辑器
$(function() {
	initSuggestDisplay()
	initReplyDisplay();
	var txtare, txtare_b;
	
	KindEditor.ready(function(K) {
		editor = K.create("textarea[name='suggest']", {
			cssPath : '../../Public/Js/editor/themes/default/ke-content.css',
			resizeType : 1,
			allowPreviewEmoticons : true,
			allowImageUpload : true,
			width : 468,
			height : 220,
			themeType : 'simple',
			items : [ 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor',
					'bold', 'italic', 'underline', 'removeformat', '|',
					'justifyleft', 'justifycenter', 'justifyright',
					'insertorderedlist', 'insertunorderedlist', '|',
					'emoticons', 'image', 'insertfile', 'link' ],
			afterChange : function() {
				if (this.count('text') > 10000) {
					alert("仅限10000字！");
					editor.html(txtare);
				} else {
					txtare = editor.html();
					K('#word_remain').html(10000 - this.count('text'));
				}
			},
			afterCreate : function() {
				var self = this;
				//KindEditor 键盘事件
				K(self.edit.doc).keyup(function(event) { 
					var keyCode = event.keyCode; 
					
					//监控事件：13(enter);17(crtl+v);32(space);进行URL处理
					if (keyCode == 13 || keyCode == 17 || keyCode == 32) {
						
						var editHtml = self.edit.html();
						
						var urlRegOne = new RegExp(/([^>=\]"'\/@]|^)((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|qqdl|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig);
						var urlRegTwo = new RegExp(/([^\w>=\]"'\/@]|^)((www\.)([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig);
						var urlRegThree = new RegExp(/([^\w->=\]:"'\.\/]|^)(([\-\.\w]+@[\.\-\w]+(\.\w+)+))/ig);
						
						if (urlRegOne.test(editHtml) || urlRegTwo.test(editHtml) || urlRegThree.test(editHtml)) {
						
							editHtml = editHtml.replace(urlRegOne, '$1<a href="$2" target="_blank">$2</a>');
							editHtml = editHtml.replace(urlRegTwo, '$1<a href="$2" target="_blank">$2</a>');
							editHtml = editHtml.replace(urlRegThree, '$1<a href="mailto:$2">$2</a>');
							
							self.edit.html('');
							self.appendHtml(editHtml);
							self.sync();
						}
						
					}
				});
			}
		});
		editor_b = K.create("textarea[name='edit_suggest']", {
			cssPath : '../../Public/Js/editor/themes/default/ke-content.css',
			resizeType : 1,
			allowPreviewEmoticons : true,
			allowImageUpload : true,
			width : 744,
			height : 220,
			themeType : 'simple',
			items : [ 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor',
					'bold', 'italic', 'underline', 'removeformat', '|',
					'justifyleft', 'justifycenter', 'justifyright',
					'insertorderedlist', 'insertunorderedlist', '|',
					'emoticons', 'image', 'insertfile', 'link' ],
			afterChange : function() {
				if (this.count('text') > 10000) {
					alert("仅限10000字！");
					editor_b.html(txtare_b);
				} else {
					txtare_b = editor_b.html();
					K('#edit_word_remain').html(10000 - this.count('text'));
				}
			},
			afterCreate : function() {
				var self = this;
				//KindEditor 键盘事件
				K(self.edit.doc).keyup(function(event) { 
					var keyCode = event.keyCode; 
					
					//监控事件：13(enter);17(crtl+v);32(space);进行URL处理
					if (keyCode == 13 || keyCode == 17 || keyCode == 32) {
						
						var editHtml = self.edit.html();
						
						var urlRegOne = new RegExp(/([^>=\]"'\/@]|^)((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|qqdl|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig);
						var urlRegTwo = new RegExp(/([^\w>=\]"'\/@]|^)((www\.)([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig);
						var urlRegThree = new RegExp(/([^\w->=\]:"'\.\/]|^)(([\-\.\w]+@[\.\-\w]+(\.\w+)+))/ig);
						
						if (urlRegOne.test(editHtml) || urlRegTwo.test(editHtml) || urlRegThree.test(editHtml)) {
						
							editHtml = editHtml.replace(urlRegOne, '$1<a href="$2" target="_blank">$2</a>');
							editHtml = editHtml.replace(urlRegTwo, '$1<a href="$2" target="_blank">$2</a>');
							editHtml = editHtml.replace(urlRegThree, '$1<a href="mailto:$2">$2</a>');
							
							self.edit.html('');
							self.appendHtml(editHtml);
							self.sync();
						}
					}
				});
			}
		});
	});
	/* 建议投诉 */
	$(".btn_sub_suggest").click(function() {
		KindEditor.ready(function(K) {
			editor = K.create("textarea[name='suggest']", {
				cssPath : '../../Public/Js/editor/themes/default/ke-content.css',
				resizeType : 1,
				allowPreviewEmoticons : true,
				allowImageUpload : true,
				width : 468,
				height : 220,
				themeType : 'simple',
				items : [ 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor',
						'bold', 'italic', 'underline', 'removeformat', '|',
						'justifyleft', 'justifycenter', 'justifyright',
						'insertorderedlist', 'insertunorderedlist', '|',
						'emoticons', 'image', 'insertfile', 'link' ],
				afterChange : function() {
					if (this.count('text') > 10000) {
						alert("仅限10000字！");
						editor.html(txtare);
					} else {
						txtare = editor.html();
						K('#word_remain').html(10000 - this.count('text'));
					}
				},
				afterCreate : function() {
					var self = this;
					//KindEditor 键盘事件
					K(self.edit.doc).keyup(function(event) { 
						var keyCode = event.keyCode; 
						
						//监控事件：13(enter);17(crtl+v);32(space);进行URL处理
						if (keyCode == 13 || keyCode == 17 || keyCode == 32) {
							
							var editHtml = self.edit.html();
							
							var urlRegOne = new RegExp(/([^>=\]"'\/@]|^)((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|qqdl|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig);
							var urlRegTwo = new RegExp(/([^\w>=\]"'\/@]|^)((www\.)([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig);
							var urlRegThree = new RegExp(/([^\w->=\]:"'\.\/]|^)(([\-\.\w]+@[\.\-\w]+(\.\w+)+))/ig);
							
							if (urlRegOne.test(editHtml) || urlRegTwo.test(editHtml) || urlRegThree.test(editHtml)) {
							
								editHtml = editHtml.replace(urlRegOne, '$1<a href="$2" target="_blank">$2</a>');
								editHtml = editHtml.replace(urlRegTwo, '$1<a href="$2" target="_blank">$2</a>');
								editHtml = editHtml.replace(urlRegThree, '$1<a href="mailto:$2">$2</a>');
								
								self.edit.html('');
								self.appendHtml(editHtml);
								self.sync();
							}
							
						}
					});
				}
			});
		});
		
		if (editor.text() == "" || editor.text() == "我来说说") {
			alert("请输入内容.");
			obj.focus();
			return false;
		}
		
		$.post(URL + "/saveSuggestion", {
			pid : $(this).attr('pid'),
			suggest : editor.html()
		}, function(data) {
			if (data.status) {
				// alert("建议投诉提交成功！");
				$("textarea").val('我来说说');
				window.location.reload();
			} else {
				alert(data.info);
			}
		}, "json");
	});

	/**/
	$(".jyts_name a:first").click(function() {
		alert("暂时保密");
	});
	/**/
	$(".jyts_name .edit_suggest").click(
		function() {
			KindEditor.ready(function(K) {
				editor_b = K.create("textarea[name='edit_suggest']", {
					cssPath : '../../Public/Js/editor/themes/default/ke-content.css',
					resizeType : 1,
					allowPreviewEmoticons : true,
					allowImageUpload : true,
					width : 744,
					height : 220,
					themeType : 'simple',
					items : [ 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor',
							'bold', 'italic', 'underline', 'removeformat', '|',
							'justifyleft', 'justifycenter', 'justifyright',
							'insertorderedlist', 'insertunorderedlist', '|',
							'emoticons', 'image', 'insertfile', 'link' ],
					afterChange : function() {
						if (this.count('text') > 10000) {
							alert("仅限10000字！");
							editor_b.html(txtare_b);
						} else {
							txtare_b = editor_b.html();
							K('#edit_word_remain').html(10000 - this.count('text'));
						}
					},
					afterCreate : function() {
						var self = this;
						//KindEditor 键盘事件
						K(self.edit.doc).keyup(function(event) { 
							var keyCode = event.keyCode; 
							
							//监控事件：13(enter);17(crtl+v);32(space);进行URL处理
							if (keyCode == 13 || keyCode == 17 || keyCode == 32) {
								
								var editHtml = self.edit.html();
								
								var urlRegOne = new RegExp(/([^>=\]"'\/@]|^)((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|qqdl|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig);
								var urlRegTwo = new RegExp(/([^\w>=\]"'\/@]|^)((www\.)([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig);
								var urlRegThree = new RegExp(/([^\w->=\]:"'\.\/]|^)(([\-\.\w]+@[\.\-\w]+(\.\w+)+))/ig);
								
								if (urlRegOne.test(editHtml) || urlRegTwo.test(editHtml) || urlRegThree.test(editHtml)) {
								
									editHtml = editHtml.replace(urlRegOne, '$1<a href="$2" target="_blank">$2</a>');
									editHtml = editHtml.replace(urlRegTwo, '$1<a href="$2" target="_blank">$2</a>');
									editHtml = editHtml.replace(urlRegThree, '$1<a href="mailto:$2">$2</a>');
									
									self.edit.html('');
									self.appendHtml(editHtml);
									self.sync();
								}
							}
						});
					}
				});
			});
			var this_content = $(this).parent("p").next(".jytsfont").html();
			var eidt_id = $(this).attr('id').substr($(this).attr('id').indexOf("_") + 1);
			$("#reply_id").val("");
			$("#edit_id").val(eidt_id);
			editor_b.html(this_content);
			var b_top = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
			$("#edit_win").window({
				top : b_top + 125 + "px"
			});
			$("#edit_win").window("open");
		});
	$(".jyts_name .reply_suggest").click(
			function() {
				KindEditor.ready(function(K) {
					editor_b = K.create("textarea[name='edit_suggest']", {
						cssPath : '../../Public/Js/editor/themes/default/ke-content.css',
						resizeType : 1,
						allowPreviewEmoticons : true,
						allowImageUpload : true,
						width : 744,
						height : 220,
						themeType : 'simple',
						items : [ 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor',
								'bold', 'italic', 'underline', 'removeformat', '|',
								'justifyleft', 'justifycenter', 'justifyright',
								'insertorderedlist', 'insertunorderedlist', '|',
								'emoticons', 'image', 'insertfile', 'link' ],
						afterChange : function() {
							if (this.count('text') > 10000) {
								alert("仅限10000字！");
								editor_b.html(txtare_b);
							} else {
								txtare_b = editor_b.html();
								K('#edit_word_remain').html(10000 - this.count('text'));
							}
						},
						afterCreate : function() {
							var self = this;
							//KindEditor 键盘事件
							K(self.edit.doc).keyup(function(event) { 
								var keyCode = event.keyCode; 
								
								//监控事件：13(enter);17(crtl+v);32(space);进行URL处理
								if (keyCode == 13 || keyCode == 17 || keyCode == 32) {
									
									var editHtml = self.edit.html();
									
									var urlRegOne = new RegExp(/([^>=\]"'\/@]|^)((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|qqdl|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig);
									var urlRegTwo = new RegExp(/([^\w>=\]"'\/@]|^)((www\.)([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig);
									var urlRegThree = new RegExp(/([^\w->=\]:"'\.\/]|^)(([\-\.\w]+@[\.\-\w]+(\.\w+)+))/ig);
									
									if (urlRegOne.test(editHtml) || urlRegTwo.test(editHtml) || urlRegThree.test(editHtml)) {
									
										editHtml = editHtml.replace(urlRegOne, '$1<a href="$2" target="_blank">$2</a>');
										editHtml = editHtml.replace(urlRegTwo, '$1<a href="$2" target="_blank">$2</a>');
										editHtml = editHtml.replace(urlRegThree, '$1<a href="mailto:$2">$2</a>');
										
										self.edit.html('');
										self.appendHtml(editHtml);
										self.sync();
									}
								}
							});
						}
					});
				});
				var reply_id = $(this).attr('id').substr(
						$(this).attr('id').indexOf("_") + 1);
				$("#edit_id").val("");
				$("#reply_id").val(reply_id);
				
				editor_b.html("");
				var b_top = window.pageYOffset
						|| document.documentElement.scrollTop
						|| document.body.scrollTop || 0;
				$("#edit_win").window({
					top : b_top + 125 + "px",
					title : "点评留言"
				});
				$("#edit_win").window("open");
				editor_b.focus();
			});

	/**/
	$(".cont_pl_tx img").each(function(i, de) {
		var img = new Image;
		img.onload = function() {
			if (img.width < img.height) {
				$(de).css({
					width : Math.floor(img.width * 70 / img.height) + "px"
				}, {
					height : "70px"
				});
			}
		};
		img.src = $(de).attr("src");
	});
	// 展开收起留言
	$(".open_close_suggest a").click(function() {
		var content_div = $(this).parent("div").prev(".suggest_content");
		if (content_div.height() > 120) {console.log(content_div.height());
			var scrollHeight = content_div.height() - 112;	//滚动条缩减高度
			var scrollTopHeight = 0;	//	当前滚动条垂直高度
			if (document.documentElement) {
				scrollTopHeight = document.documentElement.scrollTop;
				if (!scrollTopHeight) {
					scrollTopHeight = document.body.scrollTop;
					if (scrollTopHeight >= scrollHeight) {
						document.body.scrollTop = scrollTopHeight - scrollHeight;
					}
				} else {
					if (scrollTopHeight >= scrollHeight) {
						document.documentElement.scrollTop = scrollTopHeight - scrollHeight;
					}
				}
			} else {
				scrollTopHeight = document.body.scrollTop;
				if (scrollTopHeight >= scrollHeight) {
					document.body.scrollTop = scrollTopHeight - scrollHeight;
				}
			}
			
			content_div.height("112");
			$(this).text("展开");
		} else {
			content_div.height("auto");
			if (content_div.height() > 120) {
				$(this).text("收起");
			}
		}
	})
	// 展开收起点评
	$(".suggest_reply .more_reply").click(function() {
		var total = $(this).siblings(".reply_div").length;
		var show_len = $(this).siblings(".reply_div:visible").length;
		if (show_len > 5) {
			$(this).siblings(".reply_div:gt(4)").hide();
			$(this).text("展开更多" + (total - 5) + "个点评");
		} else {
			$(this).siblings(".reply_div:gt(4)").show();
			$(this).text("收起点评");
		}
	})
});
/*
 * 更新留言
 */
function updateSuggestion() {
	
	KindEditor.ready(function(K) {
		editor_b = K.create("textarea[name='edit_suggest']", {
			cssPath : '../../Public/Js/editor/themes/default/ke-content.css',
			resizeType : 1,
			allowPreviewEmoticons : true,
			allowImageUpload : true,
			width : 744,
			height : 220,
			themeType : 'simple',
			items : [ 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor',
					'bold', 'italic', 'underline', 'removeformat', '|',
					'justifyleft', 'justifycenter', 'justifyright',
					'insertorderedlist', 'insertunorderedlist', '|',
					'emoticons', 'image', 'insertfile', 'link' ],
			afterChange : function() {
				if (this.count('text') > 10000) {
					alert("仅限10000字！");
					editor_b.html(txtare_b);
				} else {
					txtare_b = editor_b.html();
					K('#edit_word_remain').html(10000 - this.count('text'));
				}
			},
			afterCreate : function() {
				var self = this;
				//KindEditor 键盘事件
				K(self.edit.doc).keyup(function(event) { 
					var keyCode = event.keyCode; 
					
					//监控事件：13(enter);17(crtl+v);32(space);进行URL处理
					if (keyCode == 13 || keyCode == 17 || keyCode == 32) {
						
						var editHtml = self.edit.html();
						
						var urlRegOne = new RegExp(/([^>=\]"'\/@]|^)((((https?|ftp|gopher|news|telnet|rtsp|mms|callto|bctp|ed2k|thunder|qqdl|synacast):\/\/))([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig);
						var urlRegTwo = new RegExp(/([^\w>=\]"'\/@]|^)((www\.)([\w\-]+\.)*[:\.@\-\w\u4e00-\u9fa5]+\.([\.a-zA-Z0-9]+|\u4E2D\u56FD|\u7F51\u7EDC|\u516C\u53F8)((\?|\/|:)+[\w\.\/=\?%\-&;~`@':+!#]*)*)/ig);
						var urlRegThree = new RegExp(/([^\w->=\]:"'\.\/]|^)(([\-\.\w]+@[\.\-\w]+(\.\w+)+))/ig);
						
						if (urlRegOne.test(editHtml) || urlRegTwo.test(editHtml) || urlRegThree.test(editHtml)) {
						
							editHtml = editHtml.replace(urlRegOne, '$1<a href="$2" target="_blank">$2</a>');
							editHtml = editHtml.replace(urlRegTwo, '$1<a href="$2" target="_blank">$2</a>');
							editHtml = editHtml.replace(urlRegThree, '$1<a href="mailto:$2">$2</a>');
							
							self.edit.html('');
							self.appendHtml(editHtml);
							self.sync();
						}
					}
				});
			}
		});
	});
	
	var edit_id = $("#edit_id").val();
	var reply_id = $("#reply_id").val();
	var content = editor_b.html();
	if (typeof(edit_id) != "undefined" && edit_id != "") {
		$.post(URL + "/updateSuggestion", {
			id: edit_id,
			content: content
		},
		function(data) {
			$.messager.show({
				msg: data.info,
				timeout: 1000
			});
			if (data.status) {
				$("#edit_" + edit_id).parent("p").next(".jytsfont").html(content);
				$("#edit_win").window("close");
			}
		},
		"json");
	}
	if (typeof(reply_id) != "undefined" && reply_id != "") {
		$.post(URL + "/saveSuggestion", {
			reply_id: reply_id,
			suggest: content
		},
		function(data) {
			$.messager.show({
				msg: data.info,
				timeout: 1000
			});
			if (data.status) {
				$("#edit_win").window("close");
				window.location.reload();
			}
		},
		"json");
	}
}
//留言板内容行数显示初始化
function initSuggestDisplay(){
    $(".suggest_content").each(function(){
        if($(this).height()>120){
            $(this).height("112");
            $(this).next('.open_close_suggest').show();
        }else{
//            $(this).prev("p").children(".open_close_suggest").hide();
            $(this).next('.open_close_suggest').hide();
        }
    })
}
function initReplyDisplay(){
    $(".suggest_reply").each(function(){
        var total = $(this).children(".reply_div").length;
        if(total>5){
            $(this).children(".reply_div:gt(4)").hide();
            $(this).children(".more_reply").text("还有"+(total-5)+"个点评").show();
        }
    })
}

