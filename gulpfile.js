
var gulp = require('gulp');
var concat = require('gulp-concat');
var minify = require('gulp-minify');

var glueAppsSrc = [
	'src/FrontEnd/GluePHP/GluePHP.js',
	'src/FrontEnd/GluePHP/EventDispatcher.js',
	'src/FrontEnd/GluePHP/ComponentContainer.js',
	'src/FrontEnd/GluePHP/BaseEntity.js',
	'src/FrontEnd/GluePHP/**/*.js',
];

gulp.task('GluePHP', function () {
	return gulp.src(glueAppsSrc)
		.pipe(concat('GluePHP.js'))
		.pipe(gulp.dest('src/FrontEnd/Dist/'));
});

gulp.task('compress', ['GluePHP'], function() {
	gulp.src('src/FrontEnd/Dist/GluePHP.js')
		.pipe(minify({
			ext:{
				// src:'-debug.js',
				min:'.min.js'
			},
		}))
		.pipe(gulp.dest('src/FrontEnd/Dist/'))
});

gulp.task('watch', function() {
	gulp.watch(glueAppsSrc, ['GluePHP', 'compress']);
});

gulp.task('default', ['GluePHP', 'compress', 'watch']);
