/*----------------------------------------------------
 * Module Setting
 *-----------------------------------------------------*/
module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json')
        ,less: {
            compile: {
                files: {
                    '~temp/css/style.css': ['src/css/style.less'],
                    '~temp/css/theme-purple.css': ['src/css/theme-purple.less']
                }
            },
            yuicompress: {
                files: {
                    'dest/css/style.css': ['~temp/css/style.css'],
                    'dest/css/theme-purple.css': ['~temp/css/theme-purple.css']
                },
                options: {
                    yuicompress: true
                }
            }
        }
        ,concat: {
            main: {
                files: {
                    '~temp/js/global.js': [
                        'src/js/libs/jquery-1.10.2.min.js',
                        'src/js/modules/es5-shim.js',
                        'src/js/modules/json2.js',
                        'src/js/modules/jquery.cookies.2.2.0.min.js',
                        'src/js/modules/jquery.rateit.js'
                    ]
                }
            }
        }
        ,uglify: {
            main: {
                files: {
                    'dest/js/global.min.js' : ['~temp/js/global.js']
                }
            }
        }
        ,copy: {
            main: {
                files: [
                    {
                        expand: true,
                        cwd: 'src/imgs',
                        src: ['**'],
                        dest: 'dest/imgs'
                    }
                ]
            }
        }
        ,clean: {
            build: ['~temp'],
            beforeBuild: ['dest/**']
        }
    });

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-yui-compressor');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-copy');

    grunt.registerTask('default', ['clean:build']);
    grunt.registerTask('build', ['clean:beforeBuild', 'less', 'concat', 'uglify','copy', 'clean:build']);
};
