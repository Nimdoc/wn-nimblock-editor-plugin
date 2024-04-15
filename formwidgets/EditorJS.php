<?php namespace Nimdoc\NimblockEditor\FormWidgets;
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
use System\Classes\PluginManager;
use Backend\Classes\FormWidgetBase;

/**
 * EditorJS Form Widget
 * @package Nimdoc\NimblockEditor\FormWidgets
 */
class EditorJS extends FormWidgetBase
{
    /**
     * @inheritDoc
     */
    protected $defaultAlias = 'editorjs';

    public $stretch;

    public $settings = [];

    public $blocksSettings = [];

    public $tunesSettings = [];

    public $inlineToolbarSettings = [];

    public $blocksScripts = [];

    /**
     * @inheritDoc
     */
    public function init()
    {
        // Probably can delete this function...
    }

    /**
     * @inheritDoc
     */
    public function render()
    {
        $this->fillFromConfig([
            'settings'
        ]);
        $this->prepareVars();
        return $this->makePartial('editorjs');
    }

    /**
     * Prepares the variables for the form widget view.
     * It sets the 'name', 'value', 'model', 'settings', 'blockSettings', 'tunesSettings', and 'inlineToolbarSettings' 
     * variables which are used in the 'editorjs' partial.
     */
    public function prepareVars()
    {
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $this->getLoadValue();
        $this->vars['model'] = $this->model;
        $this->vars['settings'] = e(json_encode($this->settings));
        $this->vars['blockSettings'] = e(json_encode($this->blocksSettings));
        $this->vars['tunesSettings'] = e(json_encode($this->tunesSettings));
        $this->vars['inlineToolbarSettings'] = e(json_encode($this->inlineToolbarSettings));
    }

    /**
     * @inheritDoc
     */
    protected function loadAssets()
    {
        $this->prepareBlocks();
        $this->addJs('/plugins/nimdoc/nimblockeditor/assets/dist/plugin-manager.js');
        $this->addJs('/plugins/nimdoc/nimblockeditor/assets/dist/plugins.js');
        $this->addJs('/plugins/nimdoc/nimblockeditor/assets/dist/editor.js');
    }

    /**
     * @inheritDoc
     */
    public function getSaveValue($value)
    {
        return $value;
    }

    protected function prepareBlocks()
    {
        $this->processEditorBlocks();
        $this->processEditorTunes();
        $this->processEditorInlineToolbar();
    }

    protected function processEditorBlocks(): void
    {
        $editorConfig = array_merge(...Event::fire('nimdoc.nimblockeditor.editor.config'));

        $editorBlockSettings = array_filter(array_map(function ($block) {
                return $block['settings'] ?? null;
            }, $editorConfig)
        );

        $this->blocksSettings = $editorBlockSettings;
    }

    protected function processEditorTunes(): void
    {
        $this->tunesSettings = array_merge(...Event::fire('nimdoc.nimblockeditor.editor.tunes'));
    }
    
    protected function processEditorInlineToolbar(): void
    {
        $this->tunesSettings = array_merge(...Event::fire('nimdoc.nimblockeditor.editor.inline.toolbar'));
    }
}
