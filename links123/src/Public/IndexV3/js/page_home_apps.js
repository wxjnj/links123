/*
 * @name: 首页app相关 js
 * @author: lpgray
 * @datetime: 2013-09-25 13:05
 */
$( function($) {
	/*
	 * app开关触发器
	 */
	$.fn.links123_apptrigers = function(selector) {
		this.on('click', selector, function() {
			var appId = $(this).attr('href');
			$(this).data('links_app') || $(this).data('links_app', new App(appId));
			var app = $(this).data('links_app');
			app.show();
			return false;
		});
	}
	$('#J_Apps').links123_apptrigers('a');
	
	/*
	 * App类
	 */
	var App = function(appId) {
		this.appId = appId;
		this.$elem = $(appId);
		this.$elem.addClass('links123-app-frame');
		this.initStyle();
		this.bindEvent();
		callbacks[appId] && callbacks[appId]( this );
	};
	App.prototype = {
		show : function() {
			this.$elem.fadeIn();
		},
		close : function() {
			this.$elem.hide();
		},
		initStyle : function() {
			this.w = this.$elem.outerWidth();
			var h = this.$elem.outerHeight();
			var fixedStyle = {
				'margin-left' : -this.w/2,
				'margin-top' : -h/2 + $('#header').outerHeight()
			}
			this.$elem.css(fixedStyle);
		},
		bindEvent : function(){
			var $self = this;
			$self.$elem.prepend('<div class="links123-close-wrap" style="width:'+this.w+'"><a href="#">x</a></div>');
			$self.$elem.children('.links123-close-wrap').on('click', 'a', function(){
				$self.close();
			});
			$(document).bind('keyup', function( e ){
				e.keyCode === 27 && $self.close();
			});
		},
		clone : function(){
			var $cloneObj = this.$elem.clone();
			$cloneObj.attr('id', this.appId.substring(1) + 'x');
			$('body').append( $cloneObj );
			return new App( '#' + $cloneObj.attr('id') );
		},
		sync : function(){
			$.post('', this.data, function(){}, 'json');
		}
	};
	
	/*
	 * callbacks
	 * 不同app初始化会调用这里的callback
	 * 以dom id为key调用对应的函数，每个函数欧诺只会调用一次，而且是按需调用
	 */
	var callbacks = {
		/*
		 * 便签
		 */
		'#J_box_note' : function( app ){
			var $note = app.$elem;
			var $textarea = $note.find('textarea');
			var textareaBg = null;
			var content = null;
			var app = app;
			load();
			// 变背景色
			$note.on('click', '[class^=color_]', function(){
				textareaBg = '' + $(this).css('background-color');
				$textarea.css('background', textareaBg );
				remember();
			});
			// 新建
			$note.on('click', '.btn_add', function(){
				var back = app.clone();// clone a new note app object
				callbacks['#J_box_note'](back);
			});
			// 删除
			$note.on('click', '.btn_clear', function(){
				$textarea.val('');
				remember();
			});
			// remember
			function remember(){
				$.cookies.set('links123_note_bg', textareaBg);
				$.cookies.set('links123_note_content', $textarea.val());
			}
			// load
			function load(){
				$textarea.css('background', $.cookies.get('links123_note_bg') );
				$.cookies.get('links123_note_content') && $textarea.val( $.cookies.get('links123_note_content') );
			}
		}
	}

}(jQuery)); 