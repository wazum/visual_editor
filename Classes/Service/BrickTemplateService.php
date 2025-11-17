<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Service;

use Andersundsehr\Editara\Dto\BrickTemplate;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class BrickTemplateService
{
    public function __construct(private IconFactory $iconFactory)
    {

    }

    /**
     * @return array<string, BrickTemplate>
     */
    public function getAll(): array
    {
        // TODO load from every extension in location EXT:your_extension/Resources/Private/Bricks/
        // templateName is the same as the file name without extension
        // path is the full path to the template file (without EXT: prefix)
        // icon is from the <e:brick icon="..."/> inside the file (not required?)
        // description is from the <e:brick description="..."/> inside the file (not required?)

        $result = [];
        $result['TemplateA'] = new BrickTemplate(
            'TemplateA',
            GeneralUtility::getFileAbsFileName('EXT:aus_project/Resources/Private/Bricks/TemplateA.html'),
            $this->iconFactory->getIcon('mimetypes-text-html', IconSize::SMALL),
            'A simple test template',
        );
        return $result;
    }

    public function get(string $templateName): BrickTemplate
    {
        return $this->getAll()[$templateName] ?? throw new \RuntimeException('Brick template "' . $templateName . '" not found');
    }
}
