const mix = require('laravel-mix');

mix
    //Global
    .sass('src/scss/admin.scss', 'css')
    .sass('src/scss/frontend.scss', 'css')
    .js('src/js/app.js', 'js')
    .setPublicPath('assets');
