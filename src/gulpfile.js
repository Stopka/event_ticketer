const { src, dest, parallel } = require('gulp')
const babel = require('gulp-babel')
const modernizr = require('gulp-modernizr')
const concat = require('gulp-concat')
const autoprefixer = require('gulp-autoprefixer')
const urlAdjuster = require('gulp-css-url-adjuster')
const branch = require('branch-pipe')

function javascript(cb) {
    const files = [
        './node_modules/jquery/dist/jquery.js',
        './node_modules/daterangepicker/moment.min.js',
        './node_modules/daterangepicker/daterangepicker.js',
        './node_modules/bootstrap/dist/js/bootstrap.js',
        './node_modules/live-form-validation/live-form-validation.js',
        './assets/scripts/live-form-validation.js',
        './node_modules/nette.ajax.js/nette.ajax.js',
        './node_modules/nette.ajax.js/extensions/spinner.ajax.js',
        './node_modules/nette.ajax.js/extensions/confirm.ajax.js',
        './assets/scripts/price.nette.ajax.js',
        './assets/scripts/datePicker.nette.ajax.js',
        './assets/scripts/nette.ajax.js',
        './node_modules/ublaboo-datagrid/assets/datagrid.js',
        './node_modules/ublaboo-datagrid/assets/datagrid-spinners.js',
        //recaptcha
        './assets/scripts/main.js',

    ];
    src(files)
        .pipe(modernizr())
        .pipe(src(files))
        .pipe(babel())
        .pipe(concat('index.js'))
        .pipe(dest('./www/build'))
    cb()
}

function styles(cb) {
    src([
        './node_modules/normalize-css/normalize.css',
        './node_modules/daterangepicker/daterangepicker.css',
        './node_modules/ublaboo-datagrid/assets/datagrid.css',
        './node_modules/ublaboo-datagrid/assets/datagrid-spinners.css',
        './node_modules/font-awesome/css/font-awesome.css',
        './assets/styles/thuanlematerialspinner.css',
        './assets/styles/style.css',
    ])
        .pipe(autoprefixer())
        .pipe(urlAdjuster({
            replace: ['../fonts', './fonts'],
        }))
        .pipe(concat('index.css'))
        .pipe(dest('./public/build'))
    cb()
}

function fonts(cb) {
    src([
        './node_modules/font-awesome/fonts/*',
    ])
        .pipe(dest('./public/build/fonts'))
    cb()
}

exports.default = parallel(javascript, styles, fonts)
