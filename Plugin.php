<?php namespace Nimdoc\NimblockEditor;
/*********************************************************************
* Copyright (c) 2024 Tom Busby
*
* This program and the accompanying materials are made
* available under the terms of the Eclipse Public License 2.0
* which is available at https://www.eclipse.org/legal/epl-2.0/
*
* SPDX-License-Identifier: EPL-2.0
**********************************************************************/

use System\Classes\PluginBase;
use Event;

use Nimdoc\NimblockEditor\Classes\Event\ExtendIndikatorNews;
use Nimdoc\NimblockEditor\Classes\Config\EditorDefaultConfig;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'NimblockEditor',
            'description' => 'Provides a block editor for Nimdoc plugin',
            'author' => 'Tom Busby',
            'icon' => 'icon-pencil-square-o'
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'Nimblock Editor Settings',
                'description' => 'Manage settings for the Nimblock Editor',
                'category' => 'Nimdoc',
                'icon' => 'icon-cog',
                'class' => 'Nimdoc\NimblockEditor\Models\Settings',
                'order' => 500,
                'keywords' => 'nimdoc nimblock editor'
            ]
        ];
    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array|void
     */
    public function boot()
    {
        // Event::subscribe(ExtendIndikatorNews::class);

        Event::listen('nimdoc.nimblockeditor.editor.config', function (&$config) {
            $config = array_merge(EditorDefaultConfig::getConfig(), $config);
        }, 100);
        Event::listen('nimdoc.nimblockeditor.editor.tunes', function () {
            return [];
        }, 100);
        Event::listen('nimdoc.nimblockeditor.editor.inline.toolbar', function () {
            return [];
        }, 100);

        Event::listen('backend.form.extendFields', function ($formWidget) {
            // Check that we're extending the correct Form widget instance
            if (
                !($formWidget->getController() instanceof \System\Controllers\Settings)
                || !($formWidget->model instanceof \Nimdoc\NimblockEditor\Models\Settings)
                || $formWidget->isNested
            ) {
                return;
            }

            $config = [];
            Event::fire('nimdoc.nimblockeditor.editor.config', [&$config]);

            $eligibleTools = array_filter($config, fn($tool) => array_key_exists('view', $tool));
            $toolOptions = [];
            foreach($eligibleTools as $key => $value) {
                $toolOptions[$key] = $key;
            }

            $formWidget->addFields([
                'nimblock_custom_settings' => [
                    'label'   => 'Custom Tool Settings',
                    'span'    => 'auto',
                    'type'    => 'datatable',
                    'columns' => [
                        'class_label' => [
                            'title' => 'Label'
                        ],
                        'class_name' => [
                            'title' => 'Setting'
                        ],
                        'tool' => [
                            'title' => 'Apply to',
                            'type' => 'dropdown',
                            'options' => $toolOptions
                        ]
                    ]
                ]
            ]);
        });

        // Add the customToolSettings validation to all valid blocks
        // Event::listen('nimdoc.nimblockeditor.editor.config', function (&$config) {
        //     foreach($config as $key => &$value) {
        //         if(array_key_exists('view', $value)) {
        //             $value['validation']['customToolSettings'] = [
        //                 'type' => 'array',
        //                 'required' => false,
        //                 'data' => [
        //                     '-' => [
        //                         'type' => 'string'
        //                     ]
        //                 ]
        //             ];
        //         }
        //     }
        // }, 100);
    }

    /**
     * Registers formWidgets.
     *
     * @return array
     */
    public function registerFormWidgets()
    {
        return [
            'Nimdoc\NimblockEditor\FormWidgets\EditorJS' => 'editorjs',
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'editorjs' => [$this, 'convertJsonToHtml'],
                'convertBytes' => [$this, 'convertBytes'],
            ],
        ];
    }

    public function convertJsonToHtml($field)
    {
        return (new ConvertToHtml)->convertJsonToHtml($field);
    }

    /**
     * Converts bytes to more sensible string
     *
     * @param int $bytes
     * @return string
     * @see \File::sizeToString($bytes);
     */
    public function convertBytes($bytes)
    {
        return \File::sizeToString($bytes);
    }

    /**
     * Registers additional blocks for EditorJS
     * @return array
     */
    public function registerEditorBlocks()
    {
        return EditorDefaultConfig::getConfig();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function registerErrorHandler(): void
    {
        \App::error(function (PluginErrorException $exception) {
            return app(ResponseFactory::class)->make(
                $exception->render(),
                $exception->getCode()
            );
        });
    }
}
