<?php
defined('TYPO3') or die();

// Add the template_brick select to pages
$GLOBALS['TCA']['pages']['columns']['template_brick'] = [
//    'exclude' => 1,
    'label' => 'Template Bricks',
    'config' => [
        'type' => 'inline',
        'foreign_table' => 'template_brick',
        'foreign_field' => 'parent_uid',
        'foreign_table_field' => 'parent_table',
        'maxitems' => 1,
        'appearance' => [
            'showAllLocalizationLink' => false,
            'showPossibleLocalizationRecords' => true,
        ],
        'behaviour' => [
            'enableCascadingDelete' => true,
        ],
    ],
];

// Add to all types
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'template_brick', '', 'after:title');
