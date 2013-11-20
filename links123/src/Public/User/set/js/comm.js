$(function(){
	$('.uc-menu .nm').on('mouseenter', function() {
		clearTimeout(self.menuTimer);
		self.menuTimer = null;
		$(this).find('ul, .ang').show();
	}).on('mouseleave', function() {
		var cur = $(this);
		self.menuTimer = setTimeout(function(){
			cur.find('ul, .ang').hide();
		}, 500);
	});
});