<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\TCA;

use TYPO3\CMS\Backend\Utility\BackendUtility;

use function sprintf;

final readonly class TcaHelper
{

    public function templateBrickLabel(array &$parameters)
    {
        $record = BackendUtility::getRecord($parameters['table'], $parameters['row']['uid']);
        $title = $record['area_name'] ?? '';
        if ($record['template_name'] ?? '') {
            $title .= ' (' . $record['template_name'] . ')';
        }
        $parameters['title'] = $title;
    }
}
