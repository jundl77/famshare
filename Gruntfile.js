var pjson = require('./package.json');
var version = pjson.version;

module.exports = function (grunt) {

    grunt.initConfig({

        clean: ["build", "build_temp"],

        concat: {
            js_filehub: {
                src: ['js/fileHubManager.js', 'js/filehub.js', 'js/api/dropzone.js', 'js/api/exif.js'],
                dest: 'build_temp/js/filehub.js'
            },

            css_filehub: {
                src: ['css/filehub/filehub.css', 'css/dropzone.css'],
                dest: 'build_temp/css/filehub.css'
            }
        },

        uglify: {
            options: {
                mangle: false,
                preserveComments: require('uglify-save-license')
            },
            target: {
                files: {
                    'build/js/script.min.js': ['js/script.js'],
                    'build/js/filehub.min.js': ['build_temp/js/filehub.js'],
                    'build/js/jquery-2.1.1.min.js': ['js/api/jquery-2.1.1.js'],
                    'build/js/includes/header.min.js': ['js/includes/header.js']
                }
            }
        },

        cssmin: {
            options: {
                shorthandCompacting: false,
                roundingPrecision: -1
            },
            target: {
                files: {
                    // about css
                    'build/css/filehub/filehub.min.css': ['build_temp/css/filehub.css'],
                    'build/css/filehub/filehub_319.min.css': ['css/filehub/filehub_319.css'],
                    'build/css/filehub/filehub_430.min.css': ['css/filehub/filehub_430.css'],
                    'build/css/filehub/filehub_479.min.css': ['css/filehub/filehub_479.css'],
                    'build/css/filehub/filehub_767.min.css': ['css/filehub/filehub_767.css'],
                    'build/css/filehub/filehub_960.min.css': ['css/filehub/filehub_960.css'],

                    // home css
                    'build/css/home/stylesheet.min.css': ['css/home/stylesheet.css'],
                    'build/css/home/stylesheet_319.min.css': ['css/home/stylesheet_319.css'],
                    'build/css/home/stylesheet_479.min.css': ['css/home/stylesheet_479.css'],
                    'build/css/home/stylesheet_767.min.css': ['css/home/stylesheet_767.css'],
                    'build/css/home/stylesheet_960.min.css': ['css/home/stylesheet_960.css'],

                    // error screen
                    'build/css/errorScreen/errorScreen.min.css': ['css/errorScreen/errorScreen.css'],

                    // 404 screen
                    'build/css/404Screen/404Screen.min.css': ['css/404Screen/404Screen.css'],

                    // includes
                    'build/css/includes/includes.min.css': ['css/includes/includes.css'],
                    'build/css/includes/header.min.css': ['css/includes/header.css']
                }
            }
        },

        imagemin: {
            dynamic: {
                options: {
                    optimizationLevel: 3
                },
                files: [{
                    expand: true,
                    cwd: 'images/',
                    src: ['**/*.{png,jpg,gif}'],
                    dest: 'build/images'
                }]
            }
        },

        replace: {
            index: {
                src: ['index.php'],
                dest: 'build/',
                replacements: [{
                    from: 'css/home/stylesheet.css',
                    to: 'css/home/stylesheet.min.css'
                }, {
                    from: 'stylesheet_960',
                    to: 'stylesheet_960.min'
                }, {
                    from: 'stylesheet_767',
                    to: 'stylesheet_767.min'
                }, {
                    from: 'stylesheet_479',
                    to: 'stylesheet_479.min'
                }, {
                    from: 'stylesheet_319',
                    to: 'stylesheet_319.min'
                }, {
                    from: 'js/api/jquery-2.1.1.js',
                    to: 'js/jquery-2.1.1.min.js'
                }, {
                    from: 'js/script.js',
                    to: 'js/script.min.js'
                }]
            },

            filehub: {
                src: ['filehub.php'],
                dest: 'build/',
                replacements: [{
                    from: 'css/filehub/filehub.css',
                    to: 'css/filehub/filehub.min.css'
                }, {
                    from: 'filehub_960',
                    to: 'filehub_960.min'
                }, {
                    from: 'filehub_767',
                    to: 'filehub_767.min'
                }, {
                    from: 'filehub_479',
                    to: 'filehub_479.min'
                }, {
                    from: 'filehub_430',
                    to: 'filehub_430.min'
                }, {
                    from: 'filehub_319',
                    to: 'filehub_319.min'
                }, {
                    from: '<script type="text/javascript" src="js/api/dropzone.js"></script>',
                    to: ''
                }, {
                    from: '<script type="text/javascript" src="js/api/exif.js"></script>',
                    to: ''
                }, {
                    from: '<link rel="stylesheet" href="css/dropzone.css">',
                    to: ''
                }, {
                    from: '<script type="text/javascript" src="js/fileHubManager.js"></script>',
                    to: ''
                }, {
                    from: 'js/api/jquery-2.1.1.js',
                    to: 'js/jquery-2.1.1.min.js'
                }, {
                    from: 'js/filehub.js',
                    to: 'js/filehub.min.js'
                }, {
                    from: 'css/filehub/filehub.css',
                    to: 'css/filehub/filehub.min.css'
                }]
            },

            errorScreen: {
                src: ['errorScreen.php'],
                dest: 'build/',
                replacements: [{
                    from: 'css/errorScreen/errorScreen.css',
                    to: 'css/errorScreen/errorScreen.min.css'
                }]
            },

            screen404: {
                src: ['404Screen.php'],
                dest: 'build/',
                replacements: [{
                    from: 'css/404Screen/404Screen.css',
                    to: 'css/404Screen/404Screen.min.css'
                }]
            },

            includes: {
                src: ['includes/top.php'],
                dest: 'build/includes/',
                replacements: [{
                    from: 'css/includes/includes.css',
                    to: 'css/includes/includes.min.css'
                }]
            },

            header: {
                src: ['includes/header.php'],
                dest: 'build/includes/',
                replacements: [{
                    from: 'css/includes/header.css',
                    to: 'css/includes/header.min.css'
                }, {
                    from: 'js/api/jquery-2.1.1.js',
                    to: 'js/jquery-2.1.1.min.js'
                }, {
                    from: 'js/includes/header.js',
                    to: 'js/includes/header.min.js'
                }]
            }
        },

        copy: {
            main: {
                files: [
                    // Folders
                    {expand: true, src: ['config/**'], dest: 'build/'},
                    {expand: true, src: ['favicon/**'], dest: 'build/'},
                    {expand: true, src: ['fonts/**'], dest: 'build/'},
                    {expand: true, src: ['php/**'], dest: 'build/'},
                    {expand: true, src: ['includes/**', '!includes/header.php', '!includes/top.php'], dest: 'build/'},

                    // Files
                    {expand: true, src: ['images/file_icons/LICENSE'], dest: 'build/'},
                    {expand: true, src: ['images/file_icons/README.md'], dest: 'build/'},
                    {expand: true, src: ['LICENSE'], dest: 'build/'},
                    {expand: true, src: ['README.md'], dest: 'build/'}
                ]
            }
        },

        mkdir: {
            upload_data: {
                options: {
                    mode: 0755,
                    create: ['build/upload_data']
                }
            },
            upload_data_thumb: {
                options: {
                    mode: 0755,
                    create: ['build/upload_data_thumb']
                }
            }
        },

        compress: {
            main: {
                options: {
                    archive: 'build/famshare-' + version +'.zip'
                },
                files: [
                    // includes files in path and its subdirs
                    {src: ['build/**', '!build/famshare-' + version + '.zip', '!build/.DS_Store'], dest: '/'}
                ]
            }
        },

        watch: {
            js: {
                files: ['**/*.js'],
                tasks: ['concat', 'uglify']
            },
            css: {
                files: ['**/*.css'],
                tasks: ['concat', 'cssmin']
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-text-replace');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-mkdir');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-watch');

    grunt.registerTask('default', ['clean', 'concat', 'uglify', 'cssmin','imagemin', 'replace', 'copy', 'mkdir',
        'compress']);
};
