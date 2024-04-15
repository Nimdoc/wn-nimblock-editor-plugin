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
                'blocks' => [$this, 'convertJsonToHtml'],
            ],
        ];
    }

    public function convertJsonToHtml($field)
    {
        // return (new ConvertToHtml)->convertJsonToHtml($field);

        $controller = new Controller();

        $html = '';

        try {
            $html = Block::render('blocks/heading', ['text' => 'hello']);
        } catch (\Exception $e) {
            echo "heck! :-)";
        }

        return $html;
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
