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
			,main: {
				files: {
					'~temp/css.css' : [
						'css/jquery-ui.css',
						//'css/jquery-ui-1.10.3.custom.min.css',
						'css/style.css',
						'css/g-theme.css',
						'css/g-dialog.css',
						'css/g-uc.css',
						//'css/slide.css',
						'css/index-banner.css',
						'css/index-weather.css',
						'css/index-app.css',
						'css/index-apps.css',
						'css/index-social.css',
						//'css/index-ted.css',
						'css/index-calendar.css',
						//'css/index-music.css',
						'css/g-dialog.css',
						'css/jquery.autocomplete.css'
					],
					'~temp/jquery.plugins.js' : [
						'js/jquery.placeholder.js',
						//'js/jquery-ui.min.js',
						'js/jquery-ui-1.9.2.custom.min.js',
						'js/json2.js',
						//'js/jquery.dropdown.js',
						//'js/jquery.slides.js',
						'js/jquery.autocomplete.js'
					],
					'~temp/index.js' : [
						'js/common.js',
						'js/date.js',
						'js/index.js',
						'js/index-weather.js',
						'js/index-banner.js',
						'js/index-calendar.js',
						'js/detect.js',
						'js/index-apps-tpl.js',
						'js/index-apps.js'//,
						//'jplayer/jquery.jplayer.min.js',
						//'jplayer/jplayer.playlist.min.js'
					],
					'../../dest/v303b/css/base.css' : 'css/base.css',
					'~temp/g-uc.css' : 'css/g-uc.css',
					'~temp/init.js' : 'js/init.js',
					'../../dest/v303b/js/jquery.cookies.2.2.0.min.js' : 'js/jquery.cookies.2.2.0.min.js',
					//音乐盒外部引用
					'~temp/links-musicbox.css': 'linksMusicBox/links-musicbox.css',
					'~temp/links-musicbox.js': 'linksMusicBox/links-musicbox.js'	
				}
			}
		}
		,copy: {
			main: {
				files: [
					{
						'../../dest/v303b/js/jquery-1.7.1.min.js' : 'js/jquery-1.7.1.min.js'
					}
					,{
						expand: true,
						cwd: 'imgs',
						src: ['**'],
						dest: '../../dest/v303b/imgs/'
					}
					,{
						expand: true,
						cwd: 'skins',
						src: ['**'],
						dest: '../../dest/v303b/skins/'
					}
				]
			}
		}
		,cssmin: {
			main: {
				files: {
					'../../dest/v303b/css/css.min.css' : ['~temp/css.css'],
					'../../dest/v303b/css/g-uc.css' : ['~temp/g-uc.css'],
					'../../dest/v303b/linksMusicBox/links-musicbox.css' : ['~temp/links-musicbox.css']
				}
			}
		}
		,uglify: {
			main: {
				files: {
					'../../dest/v303b/js/init.js' : ['~temp/init.js'],
					'../../dest/v303b/js/jquery.plugins.js' : ['~temp/jquery.plugins.js'],
					'../../dest/v303b/js/index.js' : ['~temp/index.js'],
					'../../dest/v303b/linksMusicBox/links-musicbox.js': ['~temp/links-musicbox.js']
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
	grunt.registerTask('build', ['concat', 'copy', 'cssmin', 'uglify', 'clean:build']);
};