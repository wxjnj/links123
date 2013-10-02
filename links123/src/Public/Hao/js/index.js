$(function(){
	var gutterWidth = $('body').hasClass('widescreen') ? 8 : 15;
	$('#K_waterfall').waterfall({
		itemCls: 'item-box',
		isFadeIn: true,
		fitWidth: false,
		colWidth: 228,
		gutterWidth: gutterWidth,
		gutterHeight: 15,
		checkImagesLoaded: false,
		path: function(page) {
			return $CONFIG.PUBLIC + '/Hao/js/data.json?page=' + page;
		}
	});
});

