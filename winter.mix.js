const mix = require('laravel-mix');
mix.setPublicPath(__dirname);

mix.js('assets/js/editor.js', 'assets/dist/editor.js');
