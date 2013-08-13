$(function() {
	// 编辑器
	var editor;
	var txtare;
	KindEditor.ready(function(K) {
		editor = K.create("#xx_ss", {
			resizeType : 1,
			allowPreviewEmoticons : true,
			allowImageUpload : false,
			themeType : 'simple',
			items : [
				'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline',
				'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist',
				'insertunorderedlist', '|', 'emoticons', 'link'],
			afterChange : function() {
					if (this.count('text') > 1000) {
						alert("仅限1000字！");
						editor.html(txtare);
					}
					else {
						txtare = editor.html();
						K('#word_remain').html(1000-this.count('text'));
					}
				}
		});
	});
	
	/* 说说 */
	$("#btn_sub_say").click(function(){
        //easyui错误提示
        var top = $(".cont_xx_page").offset().top - 50;
        var left = $(".cont_xx_page").offset().left + 385 -(120/2);
        
		if (editor.text() == "" || editor.text() == "我来说说" ) {
            $.messager.show({
                msg: "<span  class='messager_span'>请输入内容.</span>",
                showType: 'fade',
                width: 120,
                height: 45,
                timeout: 4000,
                style: {
                    left: left,
                    top: top
                }
            });            
			editor.focus();
			return false;
		}
		//时间间隔处理
		var timestamp = parseInt($("input[type='hidden'][name='timestamp']").val()) ;
		//当前时间戳(秒)
		var sec=parseInt(Date.parse(new Date())/1000); 
		if(!isNaN(timestamp) &&  timestamp > sec){
            $.messager.show({
                msg: "<span  class='messager_span'>你点得太快了，先休息一下吧！</span>",
                showType: 'fade',
                width: 120,
                height: 45,
                timeout: 4000,
                style: {
                    left: left,
                    top: top
                }
            });              
			editor.focus();
			return false ;
		}

        var lnk_id = parseInt($("input[type='hidden'][name='lnk_id']").val());
        
        $.ajax({
        	  type: 'POST',
        	  url: APP + "Index/saveComment",
        	  data: {
        		  'lnk_id' : lnk_id,
        		  'comment' : editor.text()
        	  },
        	  dataType: 'json',
        	  success: function(data) {
                  if(data.data == "unllid")
                  {
                      $.messager.show({
                          msg: "<span  class='messager_span'>链接id丢失</span>",
                          showType: 'fade',
                          width: 120,
                          height: 45,
                          timeout: 4000,
                          style: {
                              left: left,
                              top: top
                          }
                      });              
                      editor.focus();
                      return false ;       
                  }
                  if(data.data == "isset")
                  {
                      $.messager.show({
                          msg: "<span  class='messager_span'>已发表过该评论</span>",
                          showType: 'fade',
                          width: 120,
                          height: 45,
                          timeout: 4000,
                          style: {
                              left: left,
                              top: top
                          }
                      });              
                      editor.focus();
                      return false ;       
                  }else if(data.data == "maxflag")
                  {
                      var left = $(".cont_xx_page").offset().left + 385 -(160/2);
                      $.messager.show({
                          msg: "<span  class='messager_span'>一天同一IP评论数大于3</span>",
                          showType: 'fade',
                          width: 160,
                          height: 45,
                          timeout: 4000,
                          style: {
                              left: left,
                              top: top
                          }
                      });              
                      editor.focus();
                      return false ;       
                  }else if(data.data == "success")
                  {
                      window.location.reload();
                  }                       
          }
        });
	});
	
	// 说说
	$(".tsjy_zk").toggle(function() {
	    $(this).siblings('span').html($(this).attr('cmt')).end().text("收起");
	    return false;
	},
	function() {
	    $(this).siblings('span').html($(this).attr('scmt')).end().text("展开");
	});

	//
	if ( window.location.href.indexOf("#say") > 0 ) {
		$("#xx_ss").focus();
	}
	
});

