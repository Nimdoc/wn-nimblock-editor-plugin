<?php namespace Nimdoc\NimblockEditor\Models;
/*********************************************************************
* Copyright (c) 2024 Tom Busby
*
* This program and the accompanying materials are made
* available under the terms of the Eclipse Public License 2.0
* which is available at https://www.eclipse.org/legal/epl-2.0/
*
* SPDX-License-Identifier: EPL-2.0
**********************************************************************/

use Model;

/**
 * Nimdoc\NimblockEditor\Models\Settings
 *
 * @property int $id
 * @property string|null $item
 * @property string|null $value
 * @method static \Winter\Storm\Database\Collection<int, static> all($columns = ['*'])
 * @method static \Winter\Storm\Database\Collection<int, static> get($columns = ['*'])
 * @method static \Winter\Storm\Database\Builder|Settings lists(string $column, string $key = null)
 * @method static \Winter\Storm\Database\Builder|Settings newModelQuery()
 * @method static \Winter\Storm\Database\Builder|Settings newQuery()
 * @method static \Winter\Storm\Database\Builder|Settings orSearchWhere(string $term, string $columns = [], string $mode = 'all')
 * @method static \Winter\Storm\Database\Builder|Settings query()
 * @method static \Winter\Storm\Database\Builder|Settings searchWhere(string $term, string $columns = [], string $mode = 'all')
 * @method static \Winter\Storm\Database\Builder|Settings whereId($value)
 * @method static \Winter\Storm\Database\Builder|Settings whereItem($value)
 * @method static \Winter\Storm\Database\Builder|Settings whereValue($value)
 * @mixin \Eloquent
 */
class Settings extends Model
{
    public $implement = ['@System.Behaviors.SettingsModel'];

    public $settingsCode = 'nimdoc_nimblockeditor_settings';

    public $settingsFields = 'fields.yaml';
}
