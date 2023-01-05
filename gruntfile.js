module.exports = function(grunt) {
    require('jit-grunt')(grunt);

    grunt.initConfig({
        less: {
            development: {
                options: {
                    compress: false,
                    yuicompress: true,
                    optimization: 2
                },
                files: {
                    'assets/css/pg-helper.css'                    : 'assets/less/helper.less',
                    'assets/css/pg-editor.css'                    : 'assets/less/editor.less',
                    'assets/css/pg-preview.css'                   : 'assets/less/preview.less',

                    // Core widget less to css
                    'assets/css/pg-axen.css'                      : 'assets/less/widgets/axen.less',
                    'assets/css/pg-elixir.css'                    : 'assets/less/widgets/elixir.less',
                    'assets/css/pg-epoch.css'                     : 'assets/less/widgets/epoch.less',
                    'assets/css/pg-fabric.css'                    : 'assets/less/widgets/fabric.less',
                    'assets/css/pg-fluid.css'                     : 'assets/less/widgets/fluid.less',
                    'assets/css/pg-fever.css'                     : 'assets/less/widgets/fever.less',
                    'assets/css/pg-glaze.css'                     : 'assets/less/widgets/glaze.less',
                    'assets/css/pg-zilax.css'                     : 'assets/less/widgets/zilax.less',
                    'assets/css/pg-marron.css'                    : 'assets/less/widgets/marron.less',
                    'assets/css/pg-mastery.css'                   : 'assets/less/widgets/mastery.less',
                    'assets/css/pg-mosaic.css'                    : 'assets/less/widgets/mosaic.less',
                    'assets/css/pg-mystic.css'                    : 'assets/less/widgets/mystic.less',
                    'assets/css/pg-nexus.css'                     : 'assets/less/widgets/nexus.less',
                    'assets/css/pg-plumb.css'                     : 'assets/less/widgets/plumb.less',
                    'assets/css/pg-remix.css'                     : 'assets/less/widgets/remix.less',
                    'assets/css/pg-ruby.css'                      : 'assets/less/widgets/ruby.less',
                    'assets/css/pg-sonic.css'                     : 'assets/less/widgets/sonic.less',
                    'assets/css/pg-spirit.css'                    : 'assets/less/widgets/spirit.less',
                    'assets/css/pg-verse.css'                     : 'assets/less/widgets/verse.less',
                    'assets/css/pg-walden.css'                    : 'assets/less/widgets/walden.less',
                    'assets/css/pg-doodle.css'                    : 'assets/less/widgets/doodle.less',
                    'assets/css/pg-wisdom.css'                    : 'assets/less/widgets/wisdom.less',
                    'assets/css/pg-lunar.css'                     : 'assets/less/widgets/lunar.less',
                    'assets/css/pg-plex.css'                      : 'assets/less/widgets/plex.less',
                    'assets/css/pg-flame.css'                     : 'assets/less/widgets/flame.less',
                    'assets/css/pg-koral.css'                     : 'assets/less/widgets/koral.less',
                    'assets/css/pg-craze.css'                     : 'assets/less/widgets/craze.less',
                    'assets/css/pg-orbit.css'                     : 'assets/less/widgets/orbit.less',
                    'assets/css/pg-lytical.css'                   : 'assets/less/widgets/lytical.less',
                    'assets/css/pg-turbo.css'                     : 'assets/less/widgets/turbo.less',
                    'assets/css/pg-ranch.css'                     : 'assets/less/widgets/ranch.less',
                    'assets/css/pg-punch.css'                     : 'assets/less/widgets/punch.less',
                    'assets/css/pg-panda.css'                     : 'assets/less/widgets/panda.less',
                    'assets/css/pg-glam.css'                      : 'assets/less/widgets/glam.less',
                    'assets/css/pg-shark.css'                     : 'assets/less/widgets/shark.less',
                    'assets/css/pg-tour.css'                      : 'assets/less/widgets/tour.less',
                    'assets/css/pg-lumen.css'                     : 'assets/less/widgets/lumen.less',
                    'assets/css/pg-fixer.css'                     : 'assets/less/widgets/fixer.less',
                    'assets/css/pg-humble.css'                    : 'assets/less/widgets/humble.less',
                    'assets/css/pg-trance.css'                    : 'assets/less/widgets/trance.less',
                    'assets/css/pg-insta.css'                     : 'assets/less/widgets/insta.less',
                    'assets/css/pg-aware.css'                     : 'assets/less/widgets/aware.less',
                    'assets/css/pg-alien.css'                     : 'assets/less/widgets/alien.less',
                    'assets/css/pg-ocean.css'                     : 'assets/less/widgets/ocean.less',
                    'assets/css/pg-crop.css'                      : 'assets/less/widgets/crop.less',
                    

                    // third party widget less to css

                    // Vendor styles

                    'admin/assets/css/pg-admin.css'               : 'assets/less/admin.less',

                    //combine css file
                    'assets/css/pg-styles.css': [
                        'assets/less/widgets/axen.less',
                        'assets/less/widgets/elixir.less',
                        'assets/less/widgets/epoch.less',
                        'assets/less/widgets/fabric.less',
                        'assets/less/widgets/fluid.less',
                        'assets/less/widgets/fever.less',
                        'assets/less/widgets/glaze.less',
                        'assets/less/widgets/zilax.less',
                        'assets/less/widgets/marron.less',
                        'assets/less/widgets/mastery.less',
                        'assets/less/widgets/mosaic.less',
                        'assets/less/widgets/mystic.less',
                        'assets/less/widgets/nexus.less',
                        'assets/less/widgets/plumb.less',
                        'assets/less/widgets/remix.less',
                        'assets/less/widgets/ruby.less',
                        'assets/less/widgets/sonic.less',
                        'assets/less/widgets/spirit.less',
                        'assets/less/widgets/verse.less',
                        'assets/less/widgets/walden.less',
                        'assets/less/widgets/doodle.less',
                        'assets/less/widgets/wisdom.less',
                        'assets/less/widgets/lunar.less',
                        'assets/less/widgets/plex.less',
                        'assets/less/widgets/flame.less',
                        'assets/less/widgets/koral.less',
                        'assets/less/widgets/craze.less',
                        'assets/less/widgets/orbit.less',
                        'assets/less/widgets/lytical.less',
                        'assets/less/widgets/turbo.less',
                        'assets/less/widgets/ranch.less',
                        'assets/less/widgets/punch.less',
                        'assets/less/widgets/panda.less',
                        'assets/less/widgets/glam.less',
                        'assets/less/widgets/shark.less',
                        'assets/less/widgets/tour.less',
                        'assets/less/widgets/lumen.less',
                        'assets/less/widgets/fixer.less',
                        'assets/less/widgets/humble.less',
                        'assets/less/widgets/trance.less',
                        'assets/less/widgets/insta.less',
                        'assets/less/widgets/aware.less',
                        'assets/less/widgets/alien.less',
                        'assets/less/widgets/ocean.less',
                        'assets/less/widgets/crop.less',
                        
                    ]
                }
            }
        },
        
        rtlcss: {
            siteRTL: {
                // task options
                options: {
                    // rtlcss options
                    opts: {
                        clean: true
                    },
                    // rtlcss plugins
                    plugins: [],
                    // save unmodified files
                    saveUnmodified: true
                },
                expand: true,
                cwd: 'assets/css/',
                dest: 'assets/css/',
                src: ['**/*.css', '!**/*.rtl.css'],
                ext: '.rtl.css'
            },
            adminRTL: {
                // task options
                options: {
                    // rtlcss options
                    opts: {
                        clean: true
                    },
                    // rtlcss plugins
                    plugins: [],
                    // save unmodified files
                    saveUnmodified: true
                },
                expand: true,
                cwd: 'admin/assets/css/',
                dest: 'admin/assets/css/',
                src: ['**/*.css', '!**/*.rtl.css'],
                ext: '.rtl.css'
            }
        },

        concat: {
            js: {
                src: [
                    'assets/js/common/helper.js',
                    'assets/js/modules/pg-turbo.js',
                    'assets/js/modules/pg-lumen.js',

                    // Extensiosns JS
                    'assets/js/extensions/pg-animations.js' 
                    
                ],
                dest: 'assets/js/pg-scripts.js',
                // options: {
                //     banner: ';(function($, elementor){\n\'use strict\';\n',
                //     footer: '\n})(jQuery, window.elementorFrontend);'
                // },
            }
        },

        terser: {
            options: {
                mangle: true
            },
            my_target: {
                files: {

                    'assets/js/common/helper.min.js'                         : [ 'assets/js/common/helper.js' ],

                    //Widgets js
                    'assets/js/modules/pg-turbo.min.js'                      : [ 'assets/js/modules/pg-turbo.js' ],
                    'assets/js/modules/pg-lumen.min.js'                       : [ 'assets/js/modules/pg-lumen.js' ],


                    // Extensiosns JS
                    'assets/js/extensions/pg-animations.min.js'           : 'assets/js/extensions/pg-animations.js',
                    

                    //vendor js

                    'assets/js/pg-scripts.min.js'                           : ['assets/js/pg-scripts.js'],
                    'assets/js/pg-editor.min.js'                            : ['assets/js/pg-editor.js'],
                    'admin/assets/js/pg-admin.min.js'                       : ['admin/assets/js/pg-admin.js'],


                    //admin js
                }
            }
        },

        watch: {
            styles: {
                files: ['assets/less/**/*.less'], // which files to watch
                tasks: ['less', 'rtlcss'],
                options: {
                    nospawn: true
                }
            },
            scripts: {
                files: ['assets/js/**/*.js', 'admin/assets/js/**/*.js'],
                tasks: ['concat', 'terser'],
                options: {
                  spawn: false,
                },
            }
        },


    });

    grunt.loadNpmTasks('grunt-rtlcss');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-terser');
    //grunt.loadNpmTasks('grunt-contrib-copy');

    grunt.registerTask('default', ['less', 'rtlcss', 'concat', 'terser', 'watch']);
};