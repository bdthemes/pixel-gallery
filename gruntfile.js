module.exports = function (grunt) {
	require('jit-grunt')(grunt);

	grunt.initConfig({
		concat: {
			scripts: {
				src: [
					'src/js/common/helper.js',
					'src/js/modules/pg-turbo.js',
					'src/js/modules/pg-lumen.js',

					// Extensiosns JS
					'src/js/extensions/pg-animations.js',
				],
				dest: 'src/js/pg-scripts.js',
				options: {
					separator: ';',
				},
			},
		},

		copy: {
			main: {
				// copy src/images, src/fonts, src/vendor to assets
				files: [
					{
						expand: true,
						cwd: 'src/images/',
						src: '**',
						dest: 'assets/images/',
					},
					{
						expand: true,
						cwd: 'src/admin/images/',
						src: '**',
						dest: 'admin/assets/images/',
					},
					{
						expand: true,
						cwd: 'src/fonts/',
						src: '**',
						dest: 'assets/fonts/',
					},
				],
			},
		},

		less: {
			development: {
				options: {
					compress: true,
					yuicompress: true,
					optimization: 2,
				},
				files: [
					{
						'assets/css/pg-helper.css': 'src/less/helper.less',
						'assets/css/pg-editor.css': 'src/less/editor.less',
						'assets/css/pg-font.css': 'src/less/font.less',
					},
					{
						expand: true,
						cwd: 'src/less/widgets/',
						src: ['**/*.less', '!**/admin.less'],
						dest: 'assets/css/',
						ext: '.css',
						rename: function (dest, src) {
							return dest + src.replace(/(.+)\.css$/, 'pg-$1.css');
						},
					},
					{
						expand: true,
						cwd: 'src/less/',
						src: 'admin.less',
						dest: 'admin/assets/css/',
						ext: '.css',
						rename: function (dest, src) {
							return dest + src.replace(/(.+)\.css$/, 'pg-$1.css');
						},
					},

					{
						expand: true,
						cwd: 'src/less/',
						src: 'admin-notice.less',
						dest: 'admin/assets/css/',
						ext: '.css',
						rename: function (dest, src) {
							return dest + src.replace(/(.+)\.css$/, 'pg-$1.css');
						},
					},

					{
						// combine all css files in assets/css to pg-styles.css
						'assets/css/pg-styles.css': ['src/less/widgets/*.less', '!assets/css/pg-styles.css'],
					},
				],
			},
		},

		terser: {
			options: {
				mangle: true,
			},
			my_target: {
				files: [
					{
						expand: true,
						cwd: 'src/js/common/',
						src: 'helper.js',
						dest: 'assets/js/common/',
						ext: '.min.js',
					},
					{
						expand: true,
						cwd: 'src/js/modules/',
						src: '*.js',
						dest: 'assets/js/modules/',
						ext: '.min.js',
					},
					{
						expand: true,
						cwd: 'src/js/extensions/',
						src: '*.js',
						dest: 'assets/js/extensions/',
						ext: '.min.js',
					},
					{
						expand: true,
						cwd: 'src/js/',
						src: '*.js',
						dest: 'assets/js/',
						ext: '.min.js',
					},
					{
						expand: true,
						cwd: 'src/admin/js/',
						src: '*.js',
						dest: 'admin/assets/js/',
						ext: '.min.js',
					},
				],
			},
		},

		rtlcss: {
			siteRTL: {
				// task options
				options: {
					// rtlcss options
					opts: {
						clean: true,
					},
					// rtlcss plugins
					plugins: [],
					// save unmodified files
					saveUnmodified: true,
					useCalc: true,
				},
				expand: true,
				cwd: 'assets/css/',
				dest: 'assets/css/',
				src: ['**/*.css', '!**/*.rtl.css'],
				ext: '.rtl.css',
			},
			adminRTL: {
				// task options
				options: {
					// rtlcss options
					opts: {
						clean: true,
					},
					// rtlcss plugins
					plugins: [],
					// save unmodified files
					saveUnmodified: true,
				},
				expand: true,
				cwd: 'admin/assets/css/',
				dest: 'admin/assets/css/',
				src: ['**/*.css', '!**/*.rtl.css'],
				ext: '.rtl.css',
			},
		},

		watch: {
			styles: {
				files: ['src/less/**/*.less'], // which files to watch
				tasks: ['less', 'rtlcss'],
				options: {
					nospawn: true,
				},
			},
			scripts: {
				files: ['src/js/**/*.js'],
				tasks: ['concat', 'terser'],
				options: {
					spawn: false,
				},
			},
		},
	});

	grunt.loadNpmTasks('grunt-terser');
	grunt.loadNpmTasks('grunt-rtlcss');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-copy');

	if (process.env.NODE_ENV === 'development') {
		grunt.registerTask('default', ['less', 'concat', 'terser', 'rtlcss', 'copy', 'watch']);
	} else {
		grunt.registerTask('default', ['less', 'concat', 'terser', 'rtlcss', 'copy']);
	}
};
