var Theme = {

	Init: function(){
		var self = this;

		self.themeList = [
			'purple',
			'black'
		];
		self.skinList = [
			'skin-purple',
			'skin-black'
		];

		self.panel = $('.screen-theme');

		self.panel.on('click', '.change-theme-btn', function(){
			var target = $(this).attr('data-theme');
			if($('body').hasClass(target)) return;
			self.setTheme(target);
		});
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
	},
	change: function(type, target){
		var self = this;
		//update
		var url = URL + '/updateSkin';
		var data;
		if(type == 'theme'){
			$('body').attr('style','');
			self.oldTheme.remove();
			self.css.className = 'theme-css-link';
			data = {
				themeId: target
			};
			url += 'Theme';
		}else{
			$('body').attr('style','');
			data = {
				skinId: target
			};
		}
		$.post(url, data);
	}

};