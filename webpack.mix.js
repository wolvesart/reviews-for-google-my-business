const mix = require('laravel-mix');

mix
    //Global
    .sass('src/scss/admin.scss', 'css')
    .sass('src/scss/frontend.scss', 'css')
    .sass('src/scss/slider.scss', 'css')
    .sass('src/scss/masonry.scss', 'css')
    .js('src/js/app.js', 'js')
    .js('src/js/slider.js', 'js')
    .js('src/js/masonry.js', 'js')
    .js('src/js/admin.js', 'js')
    .js('src/js/categories.js', 'js')
    .js('src/js/manage-reviews.js', 'js')
    .setPublicPath('assets');
