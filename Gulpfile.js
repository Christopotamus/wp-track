// grab our gulp packages
var gulp  = require('gulp'),
    zip   = require('gulp-zip'),
    gutil = require('gulp-util');

gulp.task('build-test', function() {
  gulp.src('./src/*.php').pipe(gulp.dest('./wp-dev/wordpress/wp-content/plugins/wp-track/'));
});

gulp.task('build-dist', function() {
  gulp.src('./src/*.php').pipe(zip('wp-track.zip')).pipe(gulp.dest('./dist/'));
});

gulp.task('watch-build-test',function() {
  gulp.watch('./src/**.php',['build-test']);
});

gulp.task('default',['watch-build-test']);
