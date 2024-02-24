<?php namespace Nimdoc\NimblockEditor\Classes\Event;
/*********************************************************************
* Copyright (c) 2024 Tom Busby
*
* This program and the accompanying materials are made
* available under the terms of the Eclipse Public License 2.0
* which is available at https://www.eclipse.org/legal/epl-2.0/
*
* SPDX-License-Identifier: EPL-2.0
**********************************************************************/

use System\Classes\PluginManager;
use Nimdoc\NimblockEditor\Models\Settings;

class ExtendIndikatorNews
{
    /**
     * Adds listeners to extend the Indikator News plugin.
     * If the 'override_indikator_news_editor' setting is enabled and the Indikator News plugin is installed,
     * it listens for the 'backend.form.extendFields' event to change the 'content' field type to 'editorjs'.
     * It also extends the Indikator News Posts model to implement the 'ConvertToHtml' behavior and replace the 'content_render' attribute.
     *
     * @param \Illuminate\Events\Dispatcher $event The event dispatcher instance.
     */
    public function subscribe($event)
    {
        if (Settings::get('override_indikator_news_editor', false) &&
            PluginManager::instance()->hasPlugin('Indikator.News')) {

            $event->listen('backend.form.extendFields', function ($widget) {
                if (PluginManager::instance()->hasPlugin('Indikator.News') && Settings::get('override_indikator_news_editor')) {
                    if ($widget->model instanceof \Indikator\News\Models\Posts) {
                        $fieldType = 'editorjs';
                        $fieldWidgetPath = 'Nimdoc\NimblockEditor\FormWidgets\EditorJS';

                        // Finding content field and changing it's type regardless whatever it already is.
                        foreach ($widget->getFields() as $field) {
                            if ($field->fieldName === 'content') {
                                $field->config['type'] = $fieldType;
                                $field->config['widget'] = $fieldWidgetPath;
                                $field->config['stretch'] = true;
                            }
                        }
                    }
                }
            });

            \Indikator\News\Models\Posts::extend(function ($model) {
                $model->implement[] = 'Nimdoc.NimblockEditor.Behaviors.ConvertToHtml';
                $model->addDynamicMethod('getContentRenderAttribute', function () use ($model) {
                    return $model->convertJsonToHtml($model->getAttribute('content'));
                });
            });
        }
    }
}