var Ted = {
	Init: function(){
		var self = this;
		var tedBox = $('.extra-ted');
		var d_layer = tedBox.find('.ted-detial-layer');

		tedBox.on('mouseenter', 'li', function(){
			var li = $(this);
			var idx = li.index();
			var left;

			switch(idx){
				case 0:
				case 3:
					left = '-20px';
					break;
				case 1:
				case 4:
					left = '-150px';
					break;
				case 2:
				case 5:
					left = '-270px';
					break
			}

			var content = li.find('.hide-content').html();
			var img = li.find('.ted-img').html();
			d_layer.hide().appendTo(li).find('.content').html(content).end().find('.img').html(img);
			d_layer.css('left', left).fadeIn(200);
		}).on('mouseleave', function(){
			d_layer.hide();
		});

	}
};