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
						'src/v2/js/jquery.easyui.min.js',
						'src/v2/js/jquery-ui.min.js',
						'src/v2/js/kxbdSuperMarquee.js',
						'src/v2/js/jquery.mousewheel-3.0.6.pack.js',
						'src/v2/js/jquery.fancybox.js' //TODO 精简这个plugins.js的大小
					],
					'~temp/global.js' : [
						'src/v2/js/thl.js',
						'src/v2/js/js.js',
						'src/v2/js/searchTip.js',
						'src/v2/js/righter.js'
					],
					'~temp/index.js' : [
						'src/v2/js/index.js',
						'src/v2/js/righter.js',
						'src/v2/js/mail.js',
						'src/v2/js/gadgets.js'
					],
					'~temp/DD_belatedPNG.js' : 'src/v2/js/DD_belatedPNG.js'
				}
			}
		}
		,copy: {
			v2: {
				files: [
					{
						src: [ 'src/v2/js/jquery-1.7.1.min.js' ],
						dest: 'dest/v2/js/jquery-1.7.1.min.js'
					}
					,{
					 	expand: true,
					 	cwd: 'src/v2/imgs',
					 	src: ['**'],
					 	dest: 'dest/v2/imgs/'
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
		}
		,uglify: {
			v2: {
				files: {
					'dest/v2/js/index.min.js' : ['~temp/index.js'],
					'dest/v2/js/jquery.plugins.js' : ['~temp/jquery.plugins.js'],
					'dest/v2/js/global.min.js' : ['~temp/global.js'],
					'dest/v2/js/DD_belatedPNG.js' : ['~temp/DD_belatedPNG.js']
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
	// grunt.registerTask('build', ['copy']);
};