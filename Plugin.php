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

use Cms\Classes\Controller;
use Cms\Classes\Partial;
use Cms\Classes\Theme;
use Winter\Storm\Filesystem\PathResolver;
use File;
use Nimdoc\NimblockEditor\Classes\BlocksDatasource;
use Cms\Classes\AutoDatasource;
use Nimdoc\NimblockEditor\Classes\Block;

use EditorJS\EditorJS;

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

    public function register()
    {
        return [
            'Nimdoc\NimblockEditor\Components\Editor' => 'editor',
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
            $config = EditorDefaultConfig::getConfig();
        }, PHP_INT_MAX);
        Event::listen('nimdoc.nimblockeditor.editor.tunes', function () {
            return [];
        }, PHP_INT_MAX);
        Event::listen('nimdoc.nimblockeditor.editor.inline.toolbar', function () {
            return [];
        }, PHP_INT_MAX);

        // @TODO: Find a better way to handle rendering blocks that doesn't require a "blocks" partial in the theme
        // or require hooking into the CMS beforeRenderPartial event
        Event::listen('cms.page.beforeRenderPartial', function (Controller $controller, string $partialName) {

            $blockViews = Block::getEditorBlockViews();

            if(!in_array($partialName, $blockViews)) {
                return;
            }

            $block = Block::loadCached(Theme::getActiveTheme(), $partialName);

            return $block;

            if ($block = Partial::loadCached(Theme::getActiveTheme(), $partialName)) {
                // Execute the block lifecycle events and return the block object
            } else {
                throw new SystemException("The block '$partialName' can not found.");
            }
        });

        // Register the block manager instance
        Event::listen('cms.theme.registerHalcyonDatasource', function (Theme $theme, $resolver) {
            $source = $theme->getDatasource();
            if ($source instanceof AutoDatasource) {
                /* @var AutoDatasource $source */
                $source->appendDatasource('blocks', new BlocksDatasource());
                return;
            } else {
                $resolver->addDatasource($theme->getDirName(), new AutoDatasource([
                    'theme' => $source,
                    'blocks' => new BlocksDatasource(),
                ], 'blocks-autodatasource'));
            }
        });

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
                        'tune_label' => [
                            'title' => 'Label'
                        ],
                        'tune_prop' => [
                            'title' => 'Property'
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
            'functions' => [
                'blocks' => [[$this, 'convertJsonToHtml'], 'options' => ['needs_environment' => true, 'needs_context' => true]],
            ],
        ];
    }

    public function convertJsonToHtml($twigEnv, $context, $field)
    {
        $this->editorConfig = $this->getEditorBlockConfig();

        $this->validationSettings['tools'] = 
            array_map(function ($block) {
                return array_get($block, 'validation', []);
            }, array_filter($this->editorConfig, function ($block) {
                return array_key_exists('validation', $block);
            }));
    
        $this->blocksViews = array_map(function ($block) {
                return array_get($block, 'view');
            }, $this->editorConfig);

        try {
            $editor = new EditorJS($field, json_encode($this->validationSettings));
            $blocks = $editor->getBlocks();
        } catch (EditorJSException $e) {
            return $e->getMessage();
        }
        
        return $this->renderBlocks($blocks, $twigEnv->getGlobals()['this']['controller']);
    }

    public function renderBlocks($blocks, $controller=null)
    {
        $html = array_map(
            function ($block) use ($controller) {
                $blockType = strtolower($block['type']);
                if (array_key_exists($blockType, $this->blocksViews)) {
                    $viewPath = array_get($this->blocksViews, $block['type']);

                    $viewName = $this->processViewName($viewPath);

                    $data = $block['data'];
                    $data['tunes'] = $block['tunes'];

                    try {
                        return Block::render($viewName, $data, $controller);
                    } catch (\Exception $e) {
                        trace_log($e);
                    }
                }
            },
            $blocks
        );

        return html_entity_decode(implode("\n", $html));
    }

    public function processViewName($viewName)
    {
        $viewParts = explode('::', $viewName);

        $viewNameString = array_pop($viewParts);

        $viewNameString = str_replace('.', '/', $viewNameString);

        return $viewNameString;
    }

    public static function getEditorBlockConfig()
    {
        $config = [];
        Event::fire('nimdoc.nimblockeditor.editor.config', [&$config]);
        return $config;
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
