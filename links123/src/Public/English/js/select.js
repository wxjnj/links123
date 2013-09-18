
$(function() {

    $('.ranklist .ranklist-hd').hover(function(){
        $(this).find('ul').show();
        $(this).find('.all').addClass('on');
    }, function(){
        $(this).find('ul').hide();
        $(this).find('.all').removeClass('on');
    });
    $('.ranklist .ranklist-hd ul a').hover(function(){
        $(this).addClass('hv');
    }, function(){
        $(this).removeClass('hv');
    });
    $('#J_RankListExpose').click(function(){
       var self = $(this);
       if(self.is('.rl-open')){
            self.attr('class', 'rl-close');
            $('.ranklist').animate({'left' : '0'});
            getTopUserList($(this).attr("type"), $(this));
       }else{

            self.attr('class', 'rl-open');
            $('.ranklist').animate({'left' : '-273px'});
       }
       
     
        return false;
    });
    $(document).click(function(){
        $('#J_RankListExpose').is('.rl-close') && $('#J_RankListExpose').click();
    });
    $('.ranklist').click(function(event){
        var e=window.event || event;
        if(e.stopPropagation){
            e.stopPropagation();
        }else{
            e.cancelBubble = true;
        }
    });

    $('.ranklist .ranklist-hd ul a').click(function(e) {
        $('.J_now_Object em').text($(this).text());
        //$(this).addClass("current").siblings().removeClass("current");
        getTopUserList($(this).attr("type"), $(this));
        $(".J_now_Object").attr("value",$(this).attr("type"));
        
    });

})
/**
 *获取排行榜用户列表
 * @param string type [排行类型]
 * @param object target [点击的对象]
 * @return void
 * @author Adam $date2013.6.20$
 */
function getTopUserList(type, target) {

    if (type == "more_object") {
    	
        return false;
    }

    $.post(URL + "/get_top_user", {
        'type': type
    }, function(msg) {
        if (msg) {
            //移除之前的数据
            $(".J_top_1").html('');
            //
            //第一个排行榜
            var top_1 = msg.data[0];
            var top_str_1 = "";
          
            if (top_1 != null) {
                for (var i = 0; i < top_1.length; i++) {
                    top_str_1 += '<li><p class="idx"><img src="' + PUBLIC + '/English/images/';
                    if (i == 0) {
                        top_str_1 += 'gold.png" width="26" height="25" />';
                    } else if (i == 1) {
                        top_str_1 += 'yingpai.png" width="26" height="25" />';
                    } else if (i == 2) {
                        top_str_1 += 'tongpai.png" width="26" height="25" />';
                    }
                    
                    top_str_1 += '</p>';
                    top_str_1 += '<p class="nm" title="' + top_1[i]['nickname'] + '">' + top_1[i]['nickname'] + '</p>';
                    top_str_1 += '<p class="sum">' + top_1[i]['rice_sum'] + '</p>';
                    top_str_1 += '<p class="ln">' + top_1[i]['best_level_name'] + '</p>';
                    top_str_1 += '</li>';
                }
            }
            $(".J_top_1").html(top_str_1);
            //
            //第二个排行榜
            var top_2 = msg.data[1];
           
            //
            //是否显示说力听力
//            if (type == "total_rice" || type == "continue_right_num") {
//                $(".J_orderVoiceTr").hide();
//            } else {
//                $(".J_orderVoiceTr").show();
//            }
            
            $('.J_now_Object').removeClass('on');
            $('.ranklist .ranklist-hd ul').hide();
        }
    }, "json");
}