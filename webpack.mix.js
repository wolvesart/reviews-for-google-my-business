const mix = require('laravel-mix');

mix
    //Global
    .sass('src/scss/admin.scss', 'css')
    .sass('src/scss/frontend.scss', 'css')
    .js('src/js/app.js', 'js')
    .js('src/js/admin.js', 'js')
    .js('src/js/categories.js', 'js')
    .setPublicPath('assets');
