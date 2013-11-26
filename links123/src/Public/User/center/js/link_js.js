// JavaScript Document


//文本框点击文字消失
$(document).ready(function(){
	$("input[focucmsg]") .each (function(){
	$(this).val($(this).attr("focucmsg"));
	$(this).val($(this).attr("focucmsg")).css("color","#7a7a7a");
	$(this).focus(function(){
	if($(this).val() == $(this).attr("focucmsg"))
	{
	$(this).val('');
	$(this).val('').css("color","#7a7a7a");
	}
	});
	$(this).blur(function(){
	if(!$(this).val()){
	$(this).val($(this).attr("focucmsg"));
	$(this).val($(this).attr("focucmsg")).css("color","#7a7a7a");
	}
	});
	});
	});
//文本框点击文字消失