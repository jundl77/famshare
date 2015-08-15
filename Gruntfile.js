module.exports = function (grunt) {

    grunt.initConfig({
        concat: {
            js_filehub: {
                src: ['js/fileHubManager.js', 'js/filehub.js', 'js/api/dropzone.js'],
                dest: 'build_temp/js/filehub.js'
            },

            css_filehub: {
                src: ['css/filehub/filehub.css', 'css/dropzone.css'],
                dest: 'build_temp/css/filehub.css'
            }
        },

        uglify: {
            options: {
                mangle: false
            },
            target: {
                files: {
                    // js for html
                    'build/js/script.min.js': ['js/script.js'],
                    'build/js/filehub.min.js': ['build_temp/js/filehub.js'],

                    // js libs
                    'build/js/jquery-2.1.1.min.js': ['js/api/jquery-2.1.1.js']
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
                    'build/css/filehub/filehub_420.min.css': ['css/filehub/filehub_420.css'],
                    'build/css/filehub/filehub_479.min.css': ['css/filehub/filehub_479.css'],
                    'build/css/filehub/filehub_767.min.css': ['css/filehub/filehub_767.css'],
                    'build/css/filehub/filehub_960.min.css': ['css/filehub/filehub_960.css'],

                    // home css
                    'build/css/home/stylesheet.min.css': ['css/home/stylesheet.css'],
                    'build/css/home/stylesheet_319.min.css': ['css/home/stylesheet_319.css'],
                    'build/css/home/stylesheet_479.min.css': ['css/home/stylesheet_479.css'],
                    'build/css/home/stylesheet_767.min.css': ['css/home/stylesheet_767.css'],
                    'build/css/home/stylesheet_960.min.css': ['css/home/stylesheet_960.css']
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
                src: ['index.html'],
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
                    from: 'filehub_420',
                    to: 'filehub_420.min'
                }, {
                    from: 'filehub_319',
                    to: 'filehub_319.min'
                }, {
                    from: '<script type="text/javascript" src="js/api/dropzone.js"></script>',
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
            }
        },

        copy: {
            main: {
                files: [
                    // Folders
                    {expand: true, src: ['config/**'], dest: 'build/'},
                    {expand: true, src: ['favicon/**'], dest: 'build/'},
                    {expand: true, src: ['fonts/**'], dest: 'build/'},

                    // Files
                    {expand: true, src: ['downloadHandler.php'], dest: 'build/'},
                    {expand: true, src: ['fileSystemHandler.php'], dest: 'build/'},
                    {expand: true, src: ['uploadHandler.php'], dest: 'build/'}
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
                    archive: 'build/famshare.zip'
                },
                files: [
                    {src: ['build/**'], dest: '/'} // includes files in path and its subdirs
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

    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-text-replace');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-mkdir');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.registerTask('default', ['concat', 'uglify', 'cssmin','imagemin', 'replace', 'copy', 'mkdir']);
};
