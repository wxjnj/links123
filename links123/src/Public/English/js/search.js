$(function(){
    $('.show-btns').on('click', 'a', function(){
        var self = $(this);
        if(self.hasClass('current-show')) return;
        $('.show-btns').find('a').removeClass('current-show');
        self.addClass('current-show');
        var which = $(this).hasClass('square-show');
        if(which){
            $('.video-list').find('li').removeClass('video-box');
        }else{
            $('.video-list').find('li').addClass('video-box');
        }
    });

	// 视频搜索autocomplete
	$("#video-search-input").autocomplete(
			'autocomplete.html?output=json&?prompt='
					+ $("#video-search-input").val(), {
				minChars : 1,
				remoteDataType : 'json',
				processData : function(data) {
					var processed = [];
					$.each(data, function(key, value) {
						processed.push([ value.name ]);
					});

					return processed;
				}
			});
    $('#video-search-form').submit(function(){
        var keyword = $("#video-search-input").val();
        if(keyword == '') return false;
    });
    
    /**
     * 视频搜索分页导航的“直达”某页面功能
     * @author Rachel
     */
    $("body").bind('keydown', function (e) {
        var key = e.which;
        if (key == 13) {// on pressing down "enter" key
           if($('.pagination input[type=text]').is(":focus")){
        	   location.href = '/English/Index/search' + '?keyword='
        	   + getURLParameter('keyword') + "&p="+$('.pagination input[type=text]').val(); 
           }
        }
    });
    //解析URL获得参数
    function getURLParameter(name) {
        return decodeURI(
            (RegExp(name + '=' + '(.+?)(&|$)').exec(location.search)||[,null])[1]
        );
    }

    //标题过长截取
    $('.video-list').find('h5').each(function(k, v){
        //trim
        var title =  $(v).html().replace(/^[\s(&nbsp;)]+/g,'').replace(/[\s(&nbsp;)]+$/g,'');
        var st = title;
        if(title.length > 22){
            st = title.substring(0, 21) + '...';
        }
        $(v).html(st).attr('title', title);
    });

});