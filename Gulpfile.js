// grab our gulp packages
var gulp  = require('gulp'),
    gutil = require('gulp-util');

gulp.task('build-test', function() {
  gulp.src('./src/*.php').pipe(gulp.dest('./wp-dev/wordpress/wp-content/plugins/wp-track/'));
});

gulp.task('watch-build-test',function() {
  gulp.watch('./src/**.php',['build-test']);
});
