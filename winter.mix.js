const mix = require('laravel-mix');
mix.setPublicPath(__dirname);

mix.js('assets/js/editor.js', 'assets/dist/editor.js')
    .copy([
        '../../../node_modules/@editorjs/header/dist/header.umd.js',
        '../../../node_modules/@editorjs/marker/dist/marker.umd.js',
        '../../../node_modules/@editorjs/table/dist/table.umd.js',
        '../../../node_modules/@editorjs/quote/dist/quote.umd.js',
        '../../../node_modules/@editorjs/code/dist/code.umd.js',
        '../../../node_modules/@editorjs/raw/dist/raw.umd.js',
        '../../../node_modules/@editorjs/delimiter/dist/delimiter.umd.js',
        '../../../node_modules/@editorjs/underline/dist/bundle.js',
        '../../../node_modules/@editorjs/list/dist/list.umd.js',
        '../../../node_modules/@editorjs/inline-code/dist/inline-code.umd.js',
        '../../../node_modules/winter-image/dist/winter-image.umd.js',
        '../../../node_modules/winter-video/dist/winter-video.umd.js'
    ], 'assets/dist');

