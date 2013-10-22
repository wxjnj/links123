(function($) {
	$.fn.dropdown = function(options){

		var settings = {
			event: "mouseover",
			classNm: ".dropdown",
			timer: null,
			fadeSpeed: 100,
			duration: 500,
			offsetX: 82,
			offsetY: 8,
			isLocation: false
		};
		if (options) {
			$.extend(settings, options);
		}
		var triggers = this, $dropDown = $(settings.classNm);
		triggers.each(function(){
			$this = $(this);
			$this.hover(function(){	
				clearTimeout(settings.timer);
				$(".dropdown:not(" + settings.classNm + ")").hide();
				if (settings.isLocation) {
					var position = $.xuele.util.getPosition($(this)).rightBottom();
					$dropDown.css({
						left: position.x - settings.offsetX + "px",
						top: position.y + settings.offsetY + "px"
					});
				}
				$dropDown.fadeIn(settings.fadeSpeed);
			},function(){
				settings.timer = setTimeout(function() {
					$dropDown.fadeOut(settings.fadeSpeed);
				},
				settings.duration);
			});
			$dropDown.hover(function() {
				clearTimeout(settings.timer);
				$dropDown.show();
			},
			function() {
				settings.timer = setTimeout(function() {
					$dropDown.fadeOut(settings.fadeSpeed);
				},
				settings.duration);
			});
		});
	};

})(jQuery);