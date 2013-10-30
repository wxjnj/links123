/*----------------------------------------------------
 * Module Setting
 *-----------------------------------------------------*/
module.exports = function(grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json')
		,concat : {
			options: {
				stripBanners: true,
				banner: '/*! <%= pkg.name %> - v<%= pkg.version %> - ' + 
				'<%= grunt.template.today("yyyy-mm-dd") %> */\n'
			}
			,v2: {
				files: {
					'~temp/css.css' : [
						'src/v2/css/css.css', 
						'src/v2/css/tipTip.css',
						'src/v2/css/shadowbox.css',
						'src/v2/css/nivo-slider.css',
						'src/v2/css/jquery.fancybox.css',
						'src/v2/css/easyui.css'
					],
					'~temp/jquery.plugins.js' : [
						'src/v2/js/jquery.cookies.2.2.0.min.js',
						'src/v2/js/jquery.nivo.slider.js',
						'src/v2/js/jquery.tipTip.js',
						'src/v2/js/shadowbox.js',
						'src/v2/js/json2.js',
						'src/v2/js/easyui-lang-zh_CN.js',
						'src/v2/js/jquery.easyui.min.js',
						'src/v2/js/kxbdSuperMarquee.js',
						'src/v2/js/jquery.mousewheel-3.0.6.pack.js',
						'src/v2/js/jquery.fancybox.js',
						'src/v2/js/jquery.form.js'
					],
					'~temp/global.js' : [
						'src/v2/js/thl.js',
						'src/v2/js/js.js',
						'src/v2/js/searchTip.js',
						'src/v2/js/righter.js'
					],
					'~temp/index.js' : [
						'src/v2/js/index.js',
						'src/v2/js/mail.js',
						'src/v2/js/gadgets.js'
					],
					'~temp/DD_belatedPNG.js' : 'src/v2/js/DD_belatedPNG.js'
				}
			}
			,v3: {
				files: {
					'~temp/css.css' : [
						'src/v3/css/jquery-ui.css', 
						'src/v3/css/style.css',
						'src/v3/css/g-theme.css',
						'src/v3/css/g-dialog.css',
						'src/v3/css/g-uc.css',
						'src/v3/css/slide.css',
						'src/v3/css/index-app.css',
						'src/v3/css/index-apps.css',
						'src/v3/css/index-ted.css',
						'src/v3/css/index-calendar.css',
						'src/v3/css/index-music.css',
						'src/v3/css/g-dialog.css',
						'src/v3/css/jquery.autocomplete.css'
					],
					'~temp/jquery.plugins.js' : [
						'src/v3/js/jquery.placeholder.js',
						'src/v3/js/jquery-ui.min.js',
						'src/v3/js/json2.js',
						'src/v3/js/jquery.dropdown.js',
						'src/v3/js/jquery.slides.js',
						'src/v3/js/jquery.autocomplete.js'
					],
					'~temp/index.js' : [
						'src/v3/js/common.js',
						'src/v3/js/date.js',
						'src/v3/js/index.js',
						'src/v3/js/detect.js',
						'src/v3/js/index-apps-tpl.js',
						'src/v3/js/index-apps.js',
						'src/v3/jplayer/jquery.jplayer.min.js',
						'src/v3/jplayer/jplayer.playlist.min.js'
					],
					'dest/v3/css/base.css' : 'src/v3/css/base.css',
					'~temp/g-uc.css' : 'src/v3/css/g-uc.css',
					'dest/v3/js/init.js' : 'src/v3/js/init.js',
					'dest/v3/js/jquery.cookies.2.2.0.min.js' : 'src/v3/js/jquery.cookies.2.2.0.min.js'
				}
			}
		}
		,copy: {
			v2: {
				files: [
					{
						'dest/v2/js/jquery-1.7.1.min.js' : 'src/v2/js/jquery-1.7.1.min.js',
						'dest/v2/js/jquery-ui.min.js' : 'src/v2/js/jquery-ui.min.js',
						'dest/v2/js/index.js' : '~temp/index.js',
						'dest/v2/js/recommend.js' : 'src/v2/js/recommend.js',
						'dest/v2/js/suggestion.js' : 'src/v2/js/suggestion.js',
						'dest/v2/js/category.js' : 'src/v2/js/category.js',
						'dest/v2/js/comment.js' : 'src/v2/js/comment.js',
						'dest/v2/js/swfobject.js' : 'src/v2/js/swfobject.js',
						'dest/v2/js/righter.js' : 'src/v2/js/righter.js',
						'dest/v2/js/reg.js' : 'src/v2/js/reg.js',
						'dest/v2/js/myinfo.js' : 'src/v2/js/myinfo.js',
						'dest/v2/js/member.js' : 'src/v2/js/member.js',
						'dest/v2/js/mySuggestion.js' : 'src/v2/js/mySuggestion.js'
					}
					,{
						expand: true,
						cwd: 'src/v2/js/ZeroClipboard',
						src: ['**'],
						dest: 'dest/v2/js/ZeroClipboard/'
					}
					,{
					 	expand: true,
					 	cwd: 'src/v2/imgs',
					 	src: ['**'],
					 	dest: 'dest/v2/imgs/'
					}
				]
			}
			,v3: {
				files: [
					{
						'dest/v3/js/jquery-1.7.1.min.js' : 'src/v3/js/jquery-1.7.1.min.js'
					}
					,{
					 	expand: true,
					 	cwd: 'src/v3/imgs',
					 	src: ['**'],
					 	dest: 'dest/v3/imgs/'
					}
				]
			}
		}
		,cssmin: {
			v2: {
				files: {
					'dest/v2/css/css.min.css' : ['~temp/css.css']
				}
			}
			,v3: {
				files: {
					'dest/v3/css/css.min.css' : ['~temp/css.css'],
					'dest/v3/css/g-uc.css' : ['~temp/g-uc.css']
				}
			}
		}
		,uglify: {
			v2: {
				files: {
					'dest/v2/js/jquery.plugins.js' : ['~temp/jquery.plugins.js'],
					'dest/v2/js/global.min.js' : ['~temp/global.js'],
					'dest/v2/js/DD_belatedPNG.js' : ['~temp/DD_belatedPNG.js']
				}
			}
			,v3: {
				files: {
					'dest/v3/js/jquery.plugins.js' : ['~temp/jquery.plugins.js'],
					'dest/v3/js/index.js' : ['~temp/index.js']
				}
			}
		}
		,clean: {
			build: ['~temp']
		}
	});

	// 载入插件
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-yui-compressor');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-copy');

	// 注册任务
	grunt.registerTask('default', ['clean']);
	grunt.registerTask('build', ['concat:v2', 'copy:v2', 'cssmin:v2', 'uglify:v2', 'clean:build']);
	grunt.registerTask('buildv3', ['concat:v3', 'copy:v3', 'cssmin:v3', 'uglify:v3', 'clean:build']);
};