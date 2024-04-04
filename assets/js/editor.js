/*********************************************************************
* Copyright (c) 2024 Tom Busby
*
* This program and the accompanying materials are made
* available under the terms of the Eclipse Public License 2.0
* which is available at https://www.eclipse.org/legal/epl-2.0/
*
* SPDX-License-Identifier: EPL-2.0
**********************************************************************/

import EditorJS from '@editorjs/editorjs';

/*
 * EditorJS block editor form field control (WYSIWYG)
 *
 * Data attributes:
 * - data-control="editor" - enables the editorjs plugin
 *
 * JavaScript API:
 * $('div#id').editor()
 *
 */

if("function" == typeof define && define.amd) {
    delete define.amd;
}

+function ($) { "use strict";
    var Base = $.wn.foundation.base,
        BaseProto = Base.prototype

    // Editor CLASS DEFINITION
    // ============================

    class Editor extends Base {
        constructor(element, options) {
            super();
            this.options = options;
            this.$el = $(element);
            this.$form = this.$el.closest('form');
            this.$textarea = this.$el.find('>textarea:first');
            this.$editor = null;
            this.settings = this.$el.data('settings');
            this.blockSettings = this.$el.data('blocks-settings');
            this.tunesSettings = this.$el.data('tunes-settings');
            this.inlineToolbarSettings = this.$el.data('inlineToolbar-settings');
            this.simpleTunes = this.$el.data('simple-tunes');

            $.wn.foundation.controlUtils.markDisposable(element);
            Base.call(this);

            this.init();
        }

        init() {
            this.initEditorJS();
            this.$form.on('wn.beforeRequest', this.proxy(this.syncContent));
        }

        initEditorJS() {

            if(this.simpleTunes) {
                this.simpleTunes.forEach(element => {
                    window.editorJSPluginManager.createSimpleBlockTune(element.tool, element.tune_prop, element.tune_label);
                });
            }

            const tools = window.editorJSPluginManager.getToolsWithTunes();

            // Parameters for EditorJS
            let parameters = {
                holder: this.$el.attr('id'),
                placeholder: this.settings.placeholder ? this.settings.placeholder : 'Type or choose a block...',
                defaultBlock: this.settings.defaultBlock ? this.settings.defaultBlock : 'paragraph',
                autofocus: this.settings.autofocus,
                i18n: this.settings.i18n,
                tools: tools,
                tunes: this.tunesSettings,
                inlineToolbar: this.inlineToolbarSettings,
                onChange: () => {
                    this.syncContent();
                },
                onReady: () => {
                    // new DragDrop(this.$editor);
                },
            };

            // Parsing already existing data from textarea
            if (this.$textarea.val().length > 0 && this.isJson(this.$textarea.val()) === true) {
                parameters.data = JSON.parse(this.$textarea.val());
            }

            this.$editor = new EditorJS(parameters);
        }

        dispose() {
            this.$form.off('wn.beforeRequest', this.proxy(this.syncContent));
            this.$el.off('dispose-control', this.proxy(this.dispose));
            this.$editor.destroy();

            this.options = null;
            this.$el = null;
            this.$form = null;
            this.$textarea = null;
            this.settings = null;
            this.blockSettings = null;
            this.blockSettings = null;
            this.tunesSettings = null;
            this.inlineToolbarSettings = null;
            this.$editor = null;

            BaseProto.dispose.call(this);
        }

        /*
        * Instantly synchronizes HTML content.
        */
        syncContent(e) {
            this.$editor.save().then(outputData => {
                this.$textarea.val(JSON.stringify(outputData));
                this.$textarea.trigger('syncContent.wn.editorjs', [this, outputData]);
            })
            .catch(error => console.error('editorjs - Error get content: ', error.message));
        }

        isJson(string) {
            try {
                JSON.parse(string);
            } catch (e) {
                return false;
            }
            return true;
        }
    }

    // Editor PLUGIN DEFINITION
    // ============================

    var old = $.fn.Editor

    $.fn.Editor = function (option) {
        var args = Array.prototype.slice.call(arguments, 1), result
        this.each(function () {
            var $this = $(this)
            var data = $this.data('wn.editorjs')
            var options = $.extend({}, Editor.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('wn.editorjs', (data = new Editor(this, options)))
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.Editor.Constructor = Editor

    // Editor NO CONFLICT
    // =================

    $.fn.Editor.noConflict = function () {
        $.fn.Editor = old
        return this
    }

    // Editor DATA-API
    // ===============

    $(document).render(function () {
        $('[data-control="editorjs"]').Editor();
    })

}(window.jQuery);
