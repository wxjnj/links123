$(function() {
	// 编辑器
	var editor;
	var txtare;
	KindEditor.ready(function(K) {
		editor = K.create("textarea[name='comment']", {
			resizeType : 1,
			allowPreviewEmoticons : true,
			allowImageUpload : true,
			width : 765,
			themeType : 'simple',
			items : [
				'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
				'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
				'insertunorderedlist', '|', 'image', 'insertfile', 'emoticons', 'link'],
			afterChange : function() {
					if (this.count('text') > 10000) {
						alert("仅限10000字！");
						editor.html(txtare);
					}
					else {
						txtare = editor.html();
						K('#word_remain').html(10000-this.count('text'));
					}
				}
		});
	});
	
	/**/
	$(".btn_edit").click(function(){
		$("#sg_id").val($(this).attr('sg_id'));
		editor.html($(this).parent("div").siblings(".grzx_wdss_font").children(".suggest").html());
	});
	
	/* 建议投诉 */
	$("#btn_sub_edit").click(function(){
		//
		if (editor.text() == "" ) {
			alert("请输入内容.");
			obj.focus();
			return false;
		}
		//
		$.post(URL+"/saveSuggestion", {
			id: $("#sg_id").val(),
			suggest: editor.html()
		}, 
		function(data){
			if ( data.indexOf("saveOK") >= 0 ) {
				$("textarea").val();
				window.location.reload();
			}
			else {
				alert(data);
			}
		}); 
	});


});

