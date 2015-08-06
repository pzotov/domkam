module.exports = function (grunt) {
	grunt.initConfig({
		watch: {
			svg: {
				files: ['assets/images/*.svg'],
				tasks: [/*'svgo', */'svg2png'],
				options: {
					spawn: false
				}
			},
			css: {
				files: [
					'assets/css/reset.css',
					'assets/css/styles.css',
					'assets/css/jquery.fancybox.css',
					'assets/css/swiper.css',
					'assets/css/content.css',
					'assets/css/medias.css',
					'assets/css/forms.css'
				],
				tasks: ['autoprefixer','cssmin','ftp_push:css'],
				options: {
					spawn: false
				}
			},
			js: {
				files: [
					'assets/js/jquery.js',
					'assets/js/jquery.fancybox.js',
					'assets/js/jquery.maskedinput.js',
					'assets/js/swiper.jquery.js',
					'assets/js/s.js'
				],
				tasks: ['uglify','ftp_push:js'],
				options: {
					spawn: false
				}
			}
		},
		autoprefixer: {
			dist: {
				files: {
					'assets/css/styles.min.css': 'assets/css/styles.css',
					'assets/css/forms.min.css': 'assets/css/forms.css',
					'assets/css/medias.min.css': 'assets/css/medias.css',
					'assets/css/content.min.css': 'assets/css/content.css'
				}
			}
		},
		cssmin: {
			options: {
				banner: '/* \n' +
				'* http://zotov.info/\n'+
				'*\n'+
				'* Верстка и настройка cms Павел Зотов\n'+
				'* mailto: pavel.v.zotov@gmail.com\n'+
				'* http://zotov.info/\n'+
				'*/\n'
			},
			combine: {
				files: {
					'assets/css/styles.min.css': [
						'assets/css/reset.css',
						'assets/css/jquery.fancybox.css',
						'assets/css/swiper.css',
						'assets/css/styles.min.css',
						'assets/css/forms.min.css',
						'assets/css/content.min.css',
						'assets/css/medias.min.css'
					]
				}
			}
		},
		/*
		svgo: {
			all: {
				files: [{
					expand: true,
					cwd: 'assets/images/',
					src: ['*.svg'],
					dest: 'assets/images/'
				}]
			}
		},
		*/
		svg2png: {
			all: {
				files: [{
					cwd: 'assets/images/',
					src: ['*.svg'],
					dest: 'assets/images/png/'
				}]
			}
		},
		uglify: {
			main: {
				files: {
					'assets/js/s.min.js': [
						'assets/js/jquery-1.11.2.js',
						'assets/js/jquery.fancybox.js',
						'assets/js/jquery.maskedinput.js',
						'assets/js/swiper.jquery.js',
						'assets/js/s.js'
					]
				}
			}
		},
		ftp_push: {
			css:{
				options: {
					authKey: "hetzner1",
					host: "zotov.info",
					dest: "/www/domkam.zotov.info/assets/",
					port: 21
				},
				files: [{
					expand:true,
					cwd:"",
					src:["assets/css/styles.min.css"]
				}]
			},
			js:{
				options: {
					authKey: "hetzner1",
					host: "zotov.info",
					dest: "/www/domkam.zotov.info/assets/",
					port: 21
				},
				files: [{
					expand:false,
					cwd:"",
					src:["assets/js/s.min.js"]
				}]
			}
		}
	});
	grunt.loadNpmTasks('grunt-autoprefixer');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-ftp-push');
	grunt.loadNpmTasks('grunt-svg2png');
	grunt.loadNpmTasks('grunt-svgo');
};