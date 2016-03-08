
module.exports = function(grunt) {


	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		uglify: {
			options: {
				banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
			},
			build: {
				src: ['public/js/mc.app.js','public/js/mcedit.app.js'],
				dest: 'public/js/<%= pkg.name %>.min.js'
			}
		},
		sass: {
			dist: {
				options: {
					// style: 'compressed'
				},
				files: {
					'public/css/build/app.css': 'public/css/app.scss',
					'public/css/build/custom.css': 'public/css/custom.scss',
					'public/css/build/edit.css': 'public/css/edit.scss',
				}
			}
		},
		watch: {
			options: {
				livereload: true,
			},
			sass: {
				files: ['public/css/*.scss'],
				tasks: ['sass'],
			},
			options: {
				spawn: false,
			}
		},
	});

	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-sass');


	// A very basic default task.
	grunt.registerTask('default', 'starting watch', ['watch','uglify']);

};
