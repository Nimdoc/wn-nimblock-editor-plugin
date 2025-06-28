<?php namespace Nimdoc\NimblockEditor\Classes;
/*********************************************************************
* Copyright (c) 2024 Tom Busby
*
* This program and the accompanying materials are made
* available under the terms of the Eclipse Public License 2.0
* which is available at https://www.eclipse.org/legal/epl-2.0/
*
* SPDX-License-Identifier: EPL-2.0
**********************************************************************/

use Event;
use EditorJS\EditorJS;
use EditorJS\EditorJSException;

class ConvertToHtml
{
    public function convertJsonToHtml($field)
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

        return $this->renderBlocks($blocks);
    }

    public function renderBlocks($blocks)
    {
        // Get Twig environment through the controller
        $controller = \Cms\Classes\Controller::getController();

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
}