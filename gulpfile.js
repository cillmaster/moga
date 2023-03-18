'use strict';

const gulp = require('gulp');

const babel = require('gulp-babel');
const concat = require('gulp-concat');
const cssnano = require('gulp-cssnano');
const less = require('gulp-less');
const rename = require('gulp-rename');
const typescript = require('gulp-typescript');
const uglify = require('gulp-uglify-es').default;

let conf = {
    src: 'work',
    dist: 'nox-themes/default',
    tsc: {
        target: "es5",
        allowJs: true
    }
};

function jsPolyfill() {
    return gulp.src('node_modules/@babel/polyfill/dist/polyfill.min.js')
        .pipe(gulp.dest(conf.dist  + '/js'));
}

function jsBabel() {
    return gulp.src([
        conf.src + '/vue/vc.js',
        conf.src + '/vue/mix.js',
        conf.src + '/vue/scope.js',
        conf.src + '/vue/cart.js'
    ])
        .pipe(babel())
        .pipe(uglify({output: {
                comments: /^!/
            }}))
        .pipe(rename({extname: '.min.js'}))
        .pipe(gulp.dest(conf.dist + '/js'));
}

function tsPrepare() {
    return gulp.src([conf.src + '/app/**/*.ts', conf.src + '/app/bootstrap.ts'])
        .pipe(typescript(conf.tsc))
        .pipe(concat('ts.js'))
        .pipe(gulp.dest(conf.src + '/tmp'));
}

function jsPrepare() {
    return gulp.src(conf.src + '/app/**/*.js')
        .pipe(concat('js.js'))
        .pipe(gulp.dest(conf.src + '/tmp'));
}

function jsBuild() {
    return gulp.src([conf.src + '/tmp/ts.js', conf.src + '/tmp/js.js'])
        .pipe(concat('main.js'))
        .pipe(gulp.dest(conf.dist  + '/js'));
}

function cssBuild() {
    return gulp.src(conf.src + '/css/{common,style}.less')
        .pipe(less())
        //.pipe(cssnano())
        .pipe(gulp.dest(conf.dist  + '/css'));
}

exports.default = gulp.series(
    gulp.parallel(jsPolyfill, jsBabel, tsPrepare, jsPrepare, cssBuild),
    jsBuild)
;
