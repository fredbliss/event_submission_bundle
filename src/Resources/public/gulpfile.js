'use strict';

var gulp = require( 'gulp' );
var rigger = require( 'gulp-rigger' );
var cssmin = require( 'gulp-cssmin' );
var rename = require( 'gulp-rename' );
var sourceMaps = require( 'gulp-sourcemaps' );
var rimraf = require( 'rimraf' );
var wait = require( 'gulp-wait' );
var concat = require( 'gulp-concat' );
var strip = require( 'gulp-strip-comments' );
var uglify = require( 'gulp-uglify' );

var path = {
  src: {
    js: [
      'node_modules/jquery-ui/ui/widget.js'
      ,'node_modules/jquery-ui/ui/widgets/datepicker.js'
      ,'node_modules/datepair.js/dist/jquery.datepair.js'
      ,'node_modules/timepicker/jquery.timepicker.js'
      ,'node_modules/moment/moment.js'
    ]
    ,css: [
      ,'node-modules/jquery-ui/themes/base/datepicker.css'
      ,'node_modules/timepicker/jquery.timepicker.css'
      ,'css/*.css'
    ]
  }
  ,js: {
    concat: 'event-submission.js'
    ,min: 'event-submission.min.js'
  }
  ,css: {
    staging: 'css'
    ,concat: 'event-submission.css'
    ,min: 'event-submission.min.css'
  }
  ,build: {
    js: '',
    css: ''
  }
};

//task for css min & concat
gulp.task('css:build', function(){
  gulp.src( path.src.css )
    .pipe( concat(path.css.concat) )
    .pipe( gulp.dest( path.build.css ))
    .pipe( cssmin({keepSpecialComments: 0}) )
    .pipe( rename(path.css.min) )
    .pipe( gulp.dest( path.build.css ) );
});

//task for js concat
gulp.task( 'js:build', function(){
  gulp.src( path.src.js )
    .pipe( concat( path.js.concat ) )
    .pipe( gulp.dest( path.build.js ))
    .pipe( rename( path.js.min ))
    .pipe( uglify() )
    .pipe( gulp.dest( path.build.js ))
    .pipe(wait(1000));
} );

gulp.task( 'default', ['css:build','js:build']);