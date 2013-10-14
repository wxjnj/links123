var Theme = {

	Init: function(){
		var self = this;

		self.themes = [
			'purple', 'black'
		];

		self.initPanel();

		self.panel = $('.screen-theme');

		self.panel.on('click', '#J_skin_pics a', function(){
			var target = $(this).attr('data-theme');
			self.setTheme(target);
		});

		self.timer = null;

		$('#K_change_skin_btn').on('mouseenter', function(){
			$('.skin-list').fadeIn(150);
		}).on('mouseleave', '.skin-list',function(){
			clearTimeout(self.timer);
			self.timer = null;
			self.timer = setTimeout(function(){
				$('.skin-list').hide();
			},500);
		}).on('mouseover', '.skin-list', function(){
			if(!$(this).is(':hidden')){
				clearTimeout(self.timer);
				self.timer = null;
			}
		});
		self.loadBigBackground();

	},

	loadBigBackground: function(id){
		var themeId = id || $CONFIG.theme;
		var aImg = document.createElement('img');
		aImg.src = PUBLIC + '/IndexV4/dest/imgs/' + themeId + '/bg.jpg';
		aImg.onload = function(){
			$('body').css('background-image', 'url('+aImg.src+')');
		};
	},

	initPanel: function(){
		var self = this;
		var themes = self.themes;
		var html = '';
		var url;
		$.each(themes, function(k, v){
			url = PUBLIC + '/IndexV4/dest/imgs/theme-' + v + '/preview.jpg';
			html += '<a data-theme="theme-' + v + '"><img src="' + url + '" /></a>';
		});
		$('#J_skin_pics').html(html);
	},

	setTheme: function(target){
		var self = this;
		self.oldTheme = $('.theme-css-link');
		var html_doc = document.getElementsByTagName('head')[0];
		var css = self.css = document.createElement('link');
		css.setAttribute('rel', 'stylesheet');
		css.setAttribute('type', 'text/css');
		css.setAttribute('href', PUBLIC + '/IndexV4/dest/css/' + target + '.css');
		html_doc.appendChild(css);
		self.change('theme', target);
		self.loadBigBackground(target);
	},

	change: function(type, target){
		var self = this;
		var url = URL + '/updateThemeV4';
		//self.oldTheme.remove();
		self.css.className = 'theme-css-link';
		data = {
			themeId: target
		};
		$.post(url, data);
	}

};