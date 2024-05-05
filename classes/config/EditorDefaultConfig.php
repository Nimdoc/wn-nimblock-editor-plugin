<?php namespace Nimdoc\NimblockEditor\Classes\Config;
/*********************************************************************
* Copyright (c) 2024 Tom Busby
*
* This program and the accompanying materials are made
* available under the terms of the Eclipse Public License 2.0
* which is available at https://www.eclipse.org/legal/epl-2.0/
*
* SPDX-License-Identifier: EPL-2.0
**********************************************************************/

class EditorDefaultConfig
{
    public static function getConfig()
    {
        return [
            'paragraph' => [
                'validation' => [
                    'text' => [
                        'type' => 'string',
                        'allowedTags' => 'i,b,u,a[href],span[class],code[class],mark[class]'
                    ]
                ],
                'view' => 'nimdoc.nimblockeditor::blocks.paragraph'
            ],
            'header' => [
                'settings' => [
                    'class' => 'Header',
                    'shortcut' => 'CMD+SHIFT+H',
                ],
                'validation' => [
                    'text' => [
                        'type' => 'string',
                    ],
                    'level' => [
                        'type' => 'int',
                        'canBeOnly' => [1, 2, 3, 4, 5]
                    ]
                ],
                'view' => 'nimdoc.nimblockeditor::blocks.heading',
                'scripts' => [
                    '/plugins/nimdoc/nimblockeditor/assets/dist/header.umd.js'
                ]
            ],
            'Marker' => [
                'settings' => [
                    'class' => 'Marker',
                    'shortcut' => 'CMD+SHIFT+M',
                ],
                'scripts' => [
                    '/plugins/nimdoc/nimblockeditor/assets/dist/marker.umd.js'
                ],
            ],
            'table' => [
                'settings' => [
                    'class' => 'Table',
                    'inlineToolbar' => true,
                    'config' => [
                        'rows' => 2,
                        'cols' => 3,
                    ],
                ],
                'validation' => [
                    'withHeadings' => [
                        'type' => 'boolean'
                    ],
                    'content' => [
                        'type' => 'array',
                        'data' => [
                            '-' => [
                                'type' => 'array',
                                'data' => [
                                    '-' => [
                                        'type' => 'string',
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'view' => 'nimdoc.nimblockeditor::blocks.table',
                'scripts' => [
                    '/plugins/nimdoc/nimblockeditor/assets/dist/table.umd.js'
                ]
            ],
            'quote' => [
                'settings' => [
                    'class' => 'Quote',
                    'inlineToolbar' => true,
                    'shortcut' => 'CMD+SHIFT+O',
                    'config' => [
                        'quotePlaceholder' => 'Enter a quote',
                        'captionPlaceholder' => 'Quote\'s author',
                    ],
                ],
                'validation' => [
                    'text' => [
                        'type' => 'string',
                    ],
                    'alignment' => [
                        'type' => 'string',
                    ],
                    'caption' => [
                        'type' => 'string',
                    ],
                ],
                'view' => 'nimdoc.nimblockeditor::blocks.quote',
                'scripts' => [
                    '/plugins/nimdoc/nimblockeditor/assets/dist/quote.umd.js'
                ]
            ],
            'code' => [
                'settings' => [
                    'class' => 'CodeTool',
                ],
                'validation' => [
                    'code' => [
                        'type' => 'string'
                    ]
                ],
                'view' => 'nimdoc.nimblockeditor::blocks.code',
                'scripts' => [
                    '/plugins/nimdoc/nimblockeditor/assets/dist/code.umd.js'
                ]
            ],
            'raw' => [
                'settings' => [
                    'class' => 'RawTool'
                ],
                'validation' => [
                    'html' => [
                        'type' => 'string',
                        'allowedTags' => '*',
                    ]
                ],
                'view' => 'nimdoc.nimblockeditor::blocks.raw',
                'scripts' => [
                    '/plugins/nimdoc/nimblockeditor/assets/dist/raw.umd.js'
                ]
            ],
            'delimiter' => [
                'settings' => [
                    'class' => 'Delimiter'
                ],
                'validation' => [],
                'view' => 'nimdoc.nimblockeditor::blocks.delimiter',
                'scripts' => [
                    '/plugins/nimdoc/nimblockeditor/assets/dist/delimiter.umd.js'
                ]
            ],
            'underline' => [
                'settings' => [
                    'class' => 'Underline'
                ],
                'scripts' => [
                    '/plugins/nimdoc/nimblockeditor/assets/dist/bundle.js'
                ]
            ],
            'list' => [
                'settings' => [
                    'class' => 'List',
                    'inlineToolbar' => true,
                ],
                'validation' => [
                    'style' => [
                        'type' => 'boolean',
                        'canBeOnly' =>
                            [
                                false => 'ordered',
                                true => 'unordered',
                            ],
                    ],
                    'items' => [
                        'type' => 'array',
                        'data' => [
                            '-' => [
                                'type' => 'string',
                                'allowedTags' => 'i,b,u,br',
                            ],
                        ],
                    ],
                ],
                'view' => 'nimdoc.nimblockeditor::blocks.list',
                'scripts' => [
                    '/plugins/nimdoc/nimblockeditor/assets/dist/list.umd.js'
                ]
            ],
            'image' => [
                'settings' => [
                    'class' => 'WinterImage',
                    'config' => [
                        'endpoints' => [
                            'byFile' => config('app.url') . '/editorjs/plugins/image/uploadFile',
                            'byUrl' => config('app.url') . '/editorjs/plugins/image/fetchUrl',
                        ]
                    ]
                ],
                'validation' => [
                    'file' => [
                        'type' => 'array',
                        'data' => [
                            'url' => [
                                'type' => 'string',
                            ],
                            'thumbnails' => [
                                'type' => 'array',
                                'required' => false,
                                'data' => [
                                    '-' => [
                                        'type' => 'string',
                                    ]
                                ],
                            ]
                        ],
                    ],
                    'caption' => [
                        'type' => 'string',
                        'required' => false,
                    ],
                    'alt' => [
                        'type' => 'string',
                        'required' => false,
                    ],
                    'withBorder' => [
                        'type' => 'boolean'
                    ],
                    'withBackground' => [
                        'type' => 'boolean'
                    ],
                    'stretched' => [
                        'type' => 'boolean'
                    ]
                ],
                'view' => 'nimdoc.nimblockeditor::blocks.image',
                'scripts' => [
                    '/plugins/nimdoc/nimblockeditor/assets/dist/winter-image.umd.js'
                ]
            ],
            'video' => [
                'settings' => [
                    'class' => 'WinterVideo',
                    'config' => [
                        'endpoints' => [
                            'byFile' => config('app.url') . '/editorjs/plugins/video/uploadFile',
                            'byUrl' => config('app.url') . '/editorjs/plugins/video/fetchUrl',
                        ]
                    ]
                ],
                'validation' => [
                    'file' => [
                        'type' => 'array',
                        'data' => [
                            'url' => [
                                'type' => 'string',
                            ],
                            'thumbnails' => [
                                'type' => 'array',
                                'required' => false,
                                'data' => [
                                    '-' => [
                                        'type' => 'string',
                                    ]
                                ],
                            ]
                        ],
                    ],
                    'caption' => [
                        'type' => 'string',
                        'required' => false,
                    ],
                    'alt' => [
                        'type' => 'string',
                        'required' => false,
                    ],
                    'withBorder' => [
                        'type' => 'boolean'
                    ],
                    'withBackground' => [
                        'type' => 'boolean'
                    ],
                    'stretched' => [
                        'type' => 'boolean'
                    ]
                ],
                'view' => 'nimdoc.nimblockeditor::blocks.video',
                'scripts' => [
                    '/plugins/nimdoc/nimblockeditor/assets/dist/winter-video.umd.js'
                ]
            ],
            'inline-code' => [
                'settings' => [
                    'class' => 'InlineCode'
                ],
                'scripts' => [
                    '/plugins/nimdoc/nimblockeditor/assets/dist/inline-code.umd.js'
                ]
            ],
        ];
    }
}
