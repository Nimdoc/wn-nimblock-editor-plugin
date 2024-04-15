<?php namespace Nimdoc\NimblockEditor\Behaviors;
/*********************************************************************
* Copyright (c) 2024 Tom Busby
*
* This program and the accompanying materials are made
* available under the terms of the Eclipse Public License 2.0
* which is available at https://www.eclipse.org/legal/epl-2.0/
*
* SPDX-License-Identifier: EPL-2.0
**********************************************************************/

use View;
use Log;
use Event;
use EditorJS\EditorJS;
use \Cms\Classes\Theme;
use \Cms\Classes\Partial;
use \Cms\Classes\Controller;

use Winter\Storm\Extension\ExtensionBase;
use System\Classes\PluginManager;

use EditorJS\EditorJSException;

class ConvertToHtml extends ExtensionBase
{
    /**
     * @var array Settings for the editor block plugins
     */
    protected $editorConfig;

    /**
     * @var array Settings for validation
     */
    protected $validationSettings;

    /**
     * @var array Views for blocks
     */
    protected $blocksViews;

    /**
     * Convert an EditorJS JSON string to blocks
     * @param string $jsonField
     * @return string
     */
    public function convertJsonToHtml(string $jsonField): string
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
            $editor = new EditorJS($jsonField, json_encode($this->validationSettings));
            $blocks = $editor->getBlocks();
        } catch (EditorJSException $e) {
            return $e->getMessage();
        }
    
        return $this->renderBlocks($blocks);
    }

    public function renderBlocks($blocks)
    {
        $html = array_map(
            function ($block) {
                $blockType = strtolower($block['type']);
                if (array_key_exists($blockType, $this->blocksViews)) {
                    try {
                        $viewPath = array_get($this->blocksViews, $block['type']);
                        return View::make($viewPath, $block['data']);
                    } catch (\Exception $e) {
                        trace_log($e);
                    }
                }
            },
            $blocks
        );

        return html_entity_decode(implode("\n", $html));
    }

    public function getEditorBlockConfig()
    {
        return array_merge(...Event::fire('nimdoc.nimblockeditor.editor.config'));
    }
}
