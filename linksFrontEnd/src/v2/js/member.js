$(function () {
	/**/
    $(".page_go").click(function(){
    	$("#frm_js input[name='p']").val($(this).attr("p"));
    	$("#frm_js").submit();
    });
	
    /**/
    $(".grzx_sx a").click(function(){
    	$("#frm_js input[name='rid']").val($(this).attr('rid'));
    	$("#frm_js").submit();
    });

    /* 取消收藏 */
	$(".del_coll").click( function () {
		if(confirm("您确定取消收藏吗？")){ //modif by Tony 2013/07/09
			$.post(URL+"/del_collect", {
				lnk_id: $(this).attr("lnk_id")
			}, 
			function(data){
				if ( data.indexOf("delOK") >= 0 ) {
					alert("取消收藏成功！");
					window.location.reload();
				}
				else {
					alert(data);
				}
			}); 
		}
	});
	
	/**/
	$(".btn_edit").click(function(){
		if ( $(this).text()=='编辑' ) {
			var obj = $(this).parents('.grzx_wdss_button').prev('.grzx_wdss_cont');
			$("#editarea").css({'top':obj.offset().top,'left':obj.offset().left,'height':obj.height(),'display':'block'});
			$("#editarea").val(obj.text().substr(6));
			$(this).text('保存');
		}
		else {
			$.post(URL+"/editComment", {
				id: $(this).attr("myid"),
				comment: $("#editarea").val()
			}, 
			function(data){
				if ( data.indexOf("editOK") >= 0 ) {
					alert("编辑说说成功！");
					window.location.reload();
				}
				else {
					alert(data);
				}
			}); 
		}
		
	});
    
});