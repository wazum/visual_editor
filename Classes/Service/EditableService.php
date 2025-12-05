<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Service;

use Andersundsehr\Editara\Dto\Editable;
use Andersundsehr\Editara\Enum\EditableType;
use Andersundsehr\Editara\ViewHelpers\Editable\LinkViewHelper;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

final readonly class EditableService
{
    public function editButtonForEditable(ServerRequestInterface $request, Editable $editable, ?string $title = null): string
    {
        $params = [
            'edit' => [$editable->record->getMainType() => [$editable->record->getUid() => 'edit']],
            'columnsOnly' => [$editable->record->getMainType() => [$editable->field],],
            'returnUrl' => $request->getAttribute('normalizedParams')->getRequestUri(),
        ];
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $editRecordUri = (string)$uriBuilder->buildUriFromRoute('record_edit', $params);

        $iframePopup = new TagBuilder('iframe-popup');
        $iframePopup->addAttribute('title', $title);
        $iframePopup->addAttribute('src', $editRecordUri);
        $iframePopup->addAttribute('size', 'full');
        $iframePopup->setContent('✍️');
        return $iframePopup->render();
    }
}
