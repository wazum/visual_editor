<?php

declare(strict_types=1);


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['f'][] = 'TYPO3\\CMS\\VisualEditor\\ViewHelpers';

ExtensionManagementUtility::addTypoScriptSetup("@import 'EXT:visual_editor/Configuration/TypoScript/setup.typoscript'");
