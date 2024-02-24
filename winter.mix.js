const mix = require('laravel-mix');
mix.setPublicPath(__dirname);

mix
  .js('assets/js/plugins.js', 'assets/dist/plugins.js')
  .js('assets/js/plugin-manager.js', 'assets/dist/plugin-manager.js')
  .js('assets/js/scripts.js', 'assets/dist/editor.js');
