$(function(){

	//瀑布
	var gutterWidth = $('body').hasClass('widescreen') ? 8 : 15;
	$('#K_waterfall').waterfall({
		itemCls: 'item-box',
		isFadeIn: true,
		fitWidth: false,
		colWidth: 228,
		gutterWidth: gutterWidth,
		gutterHeight: 15,
		checkImagesLoaded: false,
		maxPage: 3,
		resizable: false,
		path: function(page) {
			return $CONFIG.PUBLIC + '/Hao/js/data.json?page=' + page;
		},
		callbacks: {
        	loadingFinished: function($loading, isBeyondMaxPage) {
            	if ( !isBeyondMaxPage ) {
                	$loading.fadeOut();
            	} else {
                	$loading.hide();
                	$('.pagination, .footer').show();
            	}
        	}
    	}
	});

	//分页
	$('#pagination').pagination({
    	total: 90,     // 总页数
    	current: 1   // 当前所在页
  	});

  	$('#pagination').on('click', 'a', function(){
    	var self = $(this);
    	var num;
    	if(self.parent().hasClass('disabled') || 
      		self.parent().hasClass('active')) return;
    	num = self.attr('data-num');
    	$('#pagination').pagination({
      		current: num
    	});
  	});

	//回到页顶
	function gototop(){
		var y = document.documentElement.scrollTop || document.body.scrollTop;
		window.scrollTo(0, y / 1.1);
		if(y > 0){
			setTimeout(gototop, 5);
		}else{
			return;
		}
	}
	$('#K_top_btn').on('click', function(){
		gototop();
	});


});

