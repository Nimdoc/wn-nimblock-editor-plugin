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
use Nimdoc\NimblockEditor\Models\Settings as NimblockSettings;

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

    public $editorConfig = [];

    public $blocksSettings = [];

    public $tunesSettings = [];

    public $inlineToolbarSettings = [];

    public $blocksScripts = [];

    /**
     * @inheritDoc
     */
    public function init()
    {
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
        $this->vars['simpleTunes'] = e(json_encode($this->getSimpleTunes()));
    }

    /**
     * @inheritDoc
     */
    protected function loadAssets()
    {
        $this->prepareBlocks();

        $blockScripts = [];

        foreach ($this->editorConfig as $block) {
            $blockScripts = array_merge($blockScripts, $block['scripts'] ?? []);
        }

        foreach ($blockScripts as $script) {
            $this->addJs($script);
        }

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
        $editorConfig = [];
        Event::fire('nimdoc.nimblockeditor.editor.config', [&$editorConfig]);
        $this->editorConfig = $editorConfig;

        $this->processEditorBlocks();
        $this->processEditorTunes();
        $this->processEditorInlineToolbar();
    }

    protected function processEditorBlocks(): void
    {
        $editorBlockSettings = array_filter(array_map(function ($block) {
                return $block['settings'] ?? null;
            }, $this->editorConfig)
        );

        $this->blocksSettings = $editorBlockSettings;
    }

    protected function processEditorTunes(): void
    {
        $tunes = [];
        Event::fire('nimdoc.nimblockeditor.editor.tunes', [&$tunes]);
        $this->tunesSettings = $tunes;
    }
    
    protected function processEditorInlineToolbar(): void
    {
        $this->tunesSettings = array_merge(...Event::fire('nimdoc.nimblockeditor.editor.inline.toolbar'));
    }

    protected function getSimpleTunes()
    {
        return NimblockSettings::getConfiguredSettings('nimblock_custom_settings');
    }
}
