
var gulp = require('gulp');
var concat = require('gulp-concat');
var minify = require('gulp-minify');

var glueAppsSrc = [
	'src/FrontEnd/GlueApps/GlueApps.js',
	'src/FrontEnd/GlueApps/EventDispatcher.js',
	'src/FrontEnd/GlueApps/ComponentContainer.js',
	'src/FrontEnd/GlueApps/BaseEntity.js',
	'src/FrontEnd/GlueApps/**/*.js',
];

gulp.task('GlueApps', function () {
	return gulp.src(glueAppsSrc)
		.pipe(concat('GlueApps.js'))
		.pipe(gulp.dest('src/FrontEnd/Dist/'));
});

gulp.task('compress', ['GlueApps'], function() {
	gulp.src('src/FrontEnd/Dist/GlueApps.js')
		.pipe(minify({
			ext:{
				// src:'-debug.js',
				min:'.min.js'
			},
		}))
		.pipe(gulp.dest('src/FrontEnd/Dist/'))
});

gulp.task('watch', function() {
	gulp.watch(glueAppsSrc, ['GlueApps', 'compress']);
});

gulp.task('default', ['GlueApps', 'compress', 'watch']);
