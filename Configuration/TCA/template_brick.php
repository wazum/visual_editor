<?php

return [
    'ctrl' => [
        'title' => 'Template Brick',
        'label' => 'area_name',
        'label_userFunc' => \Andersundsehr\Editara\TCA\TcaHelper::class . '->templateBrickLabel',
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
        'searchFields' => 'name',
        'iconfile' => 'EXT:editara/Resources/Public/Icons/template_brick.svg',
        'security' => [
            'ignorePageTypeRestriction' => true,
        ],
    ],
    'palettes' => [
        'names' => [
            'showitem' => 'area_name, template_name',
        ],
        'parent' => [
            'showitem' => 'parent_table, parent_uid',
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
        '0' => ['showitem' => '--palette--;;names, editables, --div--;Children, children, --div--;Parent, --palette--;;parent, --div--;Access, --palette--;;visibility, --palette--;;access, --div--;Language, --palette--;;language, '],
    ],
    'columns' => [
        'l10n_source' => [
            'label' => 'Localization Source',
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'config' => [
                'default' => 0,
                'foreign_table' => 'template_brick',
                'foreign_table_where' => 'AND {#template_brick}.{#pid}=###CURRENT_PID### AND {#template_brick}.{#sys_language_uid} NOT IN(###REC_FIELD_sys_language_uid### )',
                'items' => [
                    ['label' => '', 'value' => 0],
                ],
                'maxitems' => 1,
                'relationship' => 'manyToOne',
                'renderType' => 'selectSingle',
                'type' => 'select',
            ],
        ],
        'area_name' => [
            'label' => 'Area Name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'l10n_mode' => 'exclude',
            ],
        ],
        'template_name' => [
            'label' => 'Template Name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'l10n_mode' => 'exclude',
            ],
        ],
        'editables' => [
            'label' => 'Editables',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'editable',
                'foreign_field' => 'template_brick',
                'foreign_default_sortby' => 'name',
                'appearance' => [
                    'showAllLocalizationLink' => false,
                    'showPossibleLocalizationRecords' => true,
                ],
                'behaviour' => [
                    'enableCascadingDelete' => true,
                ],
            ],
        ],
        'children' => [
            'label' => 'Children',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'template_brick',
                'foreign_field' => 'parent_uid',
                'foreign_sortby' => 'parent_sort',
                'foreign_table_field' => 'parent_table',
                'appearance' => [
                    'showAllLocalizationLink' => false,
                    'showPossibleLocalizationRecords' => true,
//                    'showPossibleRecordsSelector' => true,
//                    'elementBrowserEnabled' => true,
                ],
                'behaviour' => [
                    'enableCascadingDelete' => true,
                ],
            ],
        ],
        'parent_table' => [
            'label' => 'Linked table',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'parent_uid' => [
            'label' => 'Linked uid',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
                'readOnly' => true,
            ],
        ],
        'parent_sort' => [
            'label' => 'Linked uid',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
                'readOnly' => true,
            ],
        ],
    ],
];
