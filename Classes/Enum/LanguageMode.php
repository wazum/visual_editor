<?php

declare(strict_types=1);

namespace TYPO3\CMS\VisualEditor\Enum;

enum LanguageMode: string
{
    case Mixed = 'mixed';
    case Connected = 'connected';
    case Free = 'free';
}
