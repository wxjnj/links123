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

    //视频搜索autocomplete
    $("#video-search-input").autocomplete('autocomplete.html?output=json', {
        minChars: 1,
        remoteDataType: 'json',
        processData: function(data) {
            var i, processed = [];
            for (i=0; i < data.length; i++) {
                processed.push([ data[i] ]);
            }
            return processed;
        }
    });
    $('#video-search-form').submit(function(){
        var keyword = $("#video-search-input").val();
        if(keyword == '') return false;
    });

});