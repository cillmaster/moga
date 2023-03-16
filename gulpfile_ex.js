'use strict';

const del = require('del');

const gulp = require('gulp');
const typescript = require('gulp-typescript');
const ngFileSort = require('gulp-angular-filesort');
const concat = require('gulp-concat');
const merge2 = require('merge2');
//const sass = require('gulp-sass');
const less = require('gulp-less');
const path = require('path');
const cssnano = require('gulp-cssnano'); // Подключаем пакет для минификации CSS

let conf = {
  src: 'work',
  dist: 'nox-themes/default',
  tsc: {
    target: "es5",
    allowJs: true
  }
};

gulp.task('js', () => {
  let s1 = gulp
    .src([conf.src + '/app/**/*.ts', conf.src + '/app/bootstrap.ts'])
    .pipe(typescript(conf.tsc));

  let s2 = gulp.src(conf.src + '/app/**/*.js');

  merge2(s1, s2)
    .pipe(concat('main.js'))
    .pipe(gulp.dest(conf.dist  + '/js'))
});

gulp.task('css', () => {
    gulp.src(conf.src + '/css/{common,style}.less')
        .pipe(less())
        //.pipe(cssnano())
        .pipe(gulp.dest(conf.dist  + '/css'));
});

gulp.task('watch', () => {
  gulp.watch(conf.src + '/app/**/*.{ts,js}', ['js']);
  gulp.watch(conf.src + '/css/*.less', ['css']);
});

gulp.task('serve', ['js', 'css', 'watch']);
gulp.task('default', ['serve']);
