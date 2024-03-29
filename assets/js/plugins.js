import Code from '@editorjs/code';
import Delimiter from '@editorjs/delimiter';
import Header from '@editorjs/header';
import InlineCode from '@editorjs/inline-code';
import Link from '@editorjs/link';
import Marker from '@editorjs/marker';
import Quote from '@editorjs/quote';
import Raw from '@editorjs/raw';
import Table from '@editorjs/table';
import Underline from '@editorjs/underline';
import WinterImage from 'winter-image';

+function ($) { "use strict";
    window.editorJSPluginManager.addPlugins({
        Code,
        Delimiter,
        Header,
        InlineCode,
        Link,
        Marker,
        Quote,
        Raw,
        Table,
        Underline,
        WinterImage
    });
}(window.jQuery);
