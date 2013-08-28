$(function() {
	$("input[name='language']").click(function(){
		window.location.href = "/Category/index/lan/"+$(this).val();
	});
	$("input[name='rid']").click(function(){
		window.location.href = "/Category/index/lan/"+$("input[name='language']:checked").val()+"/rid/"+$(this).val();
	});
	$("#btn_index a").click(function(){
		var temp = "/Index/nav/lan/"+$("input[name='language']:checked").val();
		if ( $("input[name='category']:checked")[0] ) {
			temp += "/cid/"+$("input[name='category']:checked").val();
		}
		else {
			temp += "/cid/"+$("input[name='rid']:checked").val();
		}
		if ( $("input[name='grade']:checked")[0]  ) {
			temp += "/grade/"+$("input[name='grade']:checked").val();
		}
		//
		window.location.href = temp;
	})
});
