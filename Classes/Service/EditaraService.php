<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Frontend\Page\PageInformation;
use function assert;

final readonly class EditaraService
{
    public function __construct(
        private AssetCollector $assetCollector,
    )
    {
    }

    public function isEditMode(): bool
    {
        $queryParams = $this->getRequest()->getQueryParams();

        if (!($queryParams['editara'] ?? false)) {
            return false;
        }

        return $this->isBeUser();
    }


    public function init(): void
    {
        if (!$this->isEditMode()) {
            return;
        }
        $this->assetCollector->addStyleSheet('editable', 'EXT:editara/Resources/Public/Css/editable.css');
        $this->assetCollector->addJavaScriptModule('@andersundsehr/editara/Frontend/index.mjs');

        if (!$this->assetCollector->hasInlineJavaScript('editaraLangInfo')) {
            $request = $GLOBALS['TYPO3_REQUEST'];
            assert($request instanceof ServerRequestInterface);

            $attribute = $request->getAttribute('frontend.page.information');
            assert($attribute instanceof PageInformation);
            $pageId = $attribute->getId();

            $data = [
                'pageId' => $pageId,
            ];
            $this->assetCollector->addInlineJavaScript(
                'editaraLangInfo',
                'window.TYPO3 = window.TYPO3 || {};
window.editaraInfo = ' . json_encode($data, JSON_THROW_ON_ERROR) . ';',
                [
                    'type' => 'text/javascript',
                ],
            );
        }
    }

    private function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    private function isBeUser(): bool
    {
        return ($GLOBALS['BE_USER'] ?? null) instanceof BackendUserAuthentication;
    }
}
