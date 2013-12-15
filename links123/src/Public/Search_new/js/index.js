var APP = $CONFIG['APP'];
var URL = $CONFIG['URL'];
var PUBLIC = $CONFIG['PUBLIC'];


// 关键词存取，优先使用localstorage，不支持的使用cookie
var Keywords = {
    set: function(value, type){
        type = type || 'keywords';
        if(window.localStorage){
            ks = window.localStorage.getItem(type);
            ks = JSON.parse(ks);
            if(!ks || $.type(ks) != 'array'){
                ks = [];
            }
            ks.length > 100 && ks.pop();
            var tem = [];
            $.each(ks, function(k, v){
                if(v != value) tem.push(v);
            });
            tem.unshift(value);
            window.localStorage.setItem(type, JSON.stringify(tem));
            return;
        }
        ks = $.cookies.get(type);
        if(!ks || $.type(ks) != 'array') ks = [];
        ks.length > 10 && ks.pop();
        var tem = [];
        $.each(ks, function(k, v){
            if(v != value) tem.push(v);
        });
        tem.unshift(value);
        $.cookies.set(type, tem, { expiresAt: (new Date).add_day(365) });
    },
    get: function(type){
        type = type || 'keywords';
        if(window.localStorage){
            return JSON.parse(window.localStorage.getItem(type));
        }
        return $.cookies.get(type);
    }
};

$(function(){
  getHeight();
  $(window).on('resize', function(){
    getHeight();
  });
  function getHeight(){
    var h = $(window).height() - 85;
    $("#main-wrap").find('iframe').height(h);
  }

    $("#search_text").autocomplete("/Index/searchSupplement", {
        dataType : "json",
        minChars: 1,
        resultsClass: "ac_results_search",
        selectFirst: false, //默认不选择第一个
        async: true,
        scroll : false,
        parse : function(data) {
            //data -> ['', ''];
            //var ks = $.cookies.get('keywords');
            var ks = Keywords.get();
            var cur = $.trim($('#search_text').val()).replace('http://', '');
            var has = [];
            var unique = {};
            if(!ks) ks = [];
            $.each(ks, function(k, v){
                v = decodeURIComponent(v);
                if(v.indexOf(cur) >= 0){
                    has.push(v);
                    unique[v] = true;
                }
            });

            this.hasLength = has.length;

            $.each(data, function(k, v){
                if(!unique[v]) has.push(v);
            });

            //data = has.concat(data);
            return $.map(has, function(row) {
                return {
                    data : row,
                    value : row,
                    result : row
                };
            });
        },
        formatItem : function(item) {
            return item;
        }
    }).result(function(e, item) {
        $('#search_text').val(item);
        //setTimeout(function(){
            $("#search_text").select();
            var keyword = $.trim($("#search_text").val());
            //$.cookies.set('keyword', keyword);
            //保存keyword
            keyword = keyword.replace('http://', '');
            $.cookies.set('keyword', keyword);
            keyword = encodeURIComponent(keyword);
            var url = $(".J_thlz a.on").attr("url").replace('keyword', keyword);
            var tid = $(".J_thlz a.on").attr("tid");
            THL.go(url, tid, keyword);
        //}, 0);
    });

});

