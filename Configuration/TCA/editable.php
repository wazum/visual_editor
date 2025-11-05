<?php
return [
    'ctrl' => [
        'title' => 'Editable',
        'label' => 'name',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'languageField' => 'sys_language_uid',
        'translationSource' => 'l10n_source',
        'searchFields' => 'name,type,value',
        'iconfile' => 'EXT:editara/Resources/Public/Icons/editable.svg',
//        'hideTable' => true,
        'type' => 'type',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'palettes' => [
        'nameType' => [
            'showitem' => 'name, type',
        ],
        'access' => [
            'showitem' => 'starttime, endtime',
        ],
        'visibility' => [
            'showitem' => 'hidden',
        ],
        'language' => [
            'showitem' => '
                sys_language_uid,l10n_parent,l10n_source,
            ',
        ],
    ],
    'types' => [
        'input' => ['showitem' => '--palette--;;nameType, value, --div--;Relation, template_brick, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
        'link' => ['showitem' => '--palette--;;nameType, link, --div--;Relation, template_brick, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
        'image' => ['showitem' => '--palette--;;nameType, image, --div--;Relation, template_brick, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
        'select' => ['showitem' => '--palette--;;nameType, value, --div--;Relation, template_brick, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
        'rte' => ['showitem' => '--palette--;;nameType, rte, --div--;Relation, template_brick, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
        'checkbox' => ['showitem' => '--palette--;;nameType, checkbox, --div--;Relation, template_brick, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
        'datetime' => ['showitem' => '--palette--;;nameType, datetime, --div--;Relation, template_brick, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
        'number' => ['showitem' => '--palette--;;nameType, number, --div--;Relation, template_brick, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
        'color' => ['showitem' => '--palette--;;nameType, color, --div--;Relation, template_brick, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
        'video' => ['showitem' => '--palette--;;nameType, video, --div--;Relation, template_brick, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
        'password' => ['showitem' => '--palette--;;nameType, password, --div--;Relation, template_brick, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
        '0' => ['showitem' => '--palette--;;nameType, value, --div--;Relation, template_brick, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
    ],
    'columns' => [
        'l10n_source' => [
            'label' => 'Localization Source',
//            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'config' => [
                'default' => 0,
                'foreign_table' => 'editable',
                'foreign_table_where' => 'AND {#editable}.{#pid}=###CURRENT_PID### AND {#editable}.{#sys_language_uid} NOT IN(###REC_FIELD_sys_language_uid### )',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'maxitems' => 1,
                'relationship' => 'manyToOne',
                'renderType' => 'selectSingle',
                'type' => 'select',
            ],
        ],
//        'l10n_state' => [
//            'label' => 'Localization State',
//            'config' => [
//                'type' => 'json',
//                'readOnly' => true,
//            ],
//        ],
        'template_brick' => [
            'label' => 'Template Brick',
            'config' => [
                'type' => 'number',
                'readOnly' => true,
            ],
        ],
        'name' => [
            'label' => 'Name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required',
            ],
        ],
        'type' => [
            'label' => 'Type',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['Input', 'input'],
                    ['Link', 'link'],
                    ['Image', 'image'],
                    ['Select', 'select'],
                    ['RTE', 'rte'],
                    ['Checkbox', 'checkbox'],
                    ['Datetime', 'datetime'],
                    ['Number', 'number'],
                    ['Color', 'color'],
                    ['Video', 'video'],
                    ['Password', 'password'],
                ],
                'default' => 'input',
            ],
        ],
        'value' => [
            'label' => 'Value',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 2,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'link' => [
            'label' => 'Link',
            'config' => [
                'type' => 'link',
                'allowedTypes' => ['*'],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'image' => [
            'label' => 'Image',
            'config' => [
                'type' => 'file',
                'maxitems' => 1,
                'allowed' => 'common-image-types',
                'appearance' => [
                    'createNewRelationLinkTitle' => 'Add Image',
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'rte' => [
            'label' => 'Rich Text',
            'config' => [
                'type' => 'text',
                'enableRichtext' => true,
                'richtextConfiguration' => 'default',
                'cols' => 40,
                'rows' => 5,
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        'checkbox' => [
            'label' => 'Checkbox',
            'config' => [
                'type' => 'check',
                'items' => [
                    ['Checked', 1],
                ],
            ],
        ],
        'datetime' => [
            'label' => 'Datetime',
            'config' => [
                'type' => 'input',
                'renderType' => 'inputDateTime',
                'eval' => 'datetime',
            ],
        ],
        'number' => [
            'label' => 'Number',
            'config' => [
                'type' => 'input',
                'eval' => 'double',
                'size' => 10,
            ],
        ],
        'color' => [
            'label' => 'Color',
            'config' => [
                'type' => 'input',
                'renderType' => 'colorpicker',
                'size' => 10,
                'eval' => 'trim',
            ],
        ],
        'video' => [
            'label' => 'Video',
            'config' => [
                'type' => 'file',
                'maxitems' => 1,
                'allowed' => 'wav,mp4,ogg,flac,opus,webm,youtube,vimeo',
                'appearance' => [
                    'createNewRelationLinkTitle' => 'Add Video',
                ],
                'behaviour' => [
                    'allowLanguageSynchronization' => true,
                ],
            ],
        ],
        // TODO remove password field?
        'password' => [
            'label' => 'Password',
            'config' => [
                'type' => 'password',
            ],
        ],
    ],
];
