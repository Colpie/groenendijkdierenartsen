const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const cleanCSS = require('gulp-clean-css');
const sourcemaps = require('gulp-sourcemaps');
const sassGlob = require('gulp-sass-glob');
// Compile Sass files
gulp.task('sass', function() {
    return gulp.src('./assets/scss/child.scss')
        .pipe(sourcemaps.init())
        .pipe(sassGlob())
        .pipe(sass({
            noCache: true,
            outputStyle: "expanded",
            lineNumbers: false,
            loadPath: './assets/scss/child.scss*',
            sourceMap: true
        }))
        .on('error', sass.logError)
        .pipe(sourcemaps.write('./maps'))
        .pipe(gulp.dest('./css/'))
});

// Watch task
gulp.task('watch', function() {
    gulp.watch('assets/scss/**/*.scss', gulp.series('sass'));
});

// Default task
gulp.task('default', gulp.series('sass', 'watch'));