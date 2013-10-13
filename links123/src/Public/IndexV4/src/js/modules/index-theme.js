var Theme = {

	Init: function(){
		var self = this;

		self.panel = $('.screen-theme');

		self.panel.on('click', '.change-theme-btn', function(){
			var target = $(this).attr('data-theme');
			if($('body').hasClass(target)) return;
			self.setTheme(target);
		});

		self.panel.on('mouseover', '.J_link_skin_type', function(){
			$('.J_link_skin_type').removeClass('active');
			$(this).addClass('active');
			var skin_type = $(this).attr('data-id');
			$('#J_skin_pics').find('.item').hide();
			$('#link_skin_' + skin_type).show();
		}).on('click', '#J_skin_pics a', function(){
			self.setSkin(this);
		});

		$('#K_change_skin_btn').on('mouseenter', function(){
			$('.skin-list').slideDown(150);
		}).on('mouseleave', function(){
			$('.skin-list').slideUp(150);
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

	setSkin: function(a){
		var self = this;
		var o = $(a);
		var url = o.attr('data-bg');
		var skinId = o.attr('data-id');
		$('body').css('background-image', 'url(' + url + ')');
		self.change('skin', skinId);
	},

	change: function(type, target){
		var self = this;
		//update
		var url = URL + '/updateSkin';
		var data;
		if(type == 'theme'){
			$('body').attr('style','');
			//self.oldTheme.remove();
			self.css.className = 'theme-css-link';
			data = {
				themeId: target
			};
			url += 'Theme';
		}else{
			data = {
				skinId: target
			};
		}
		$.post(url, data);
	}

};