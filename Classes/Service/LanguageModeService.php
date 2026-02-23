<?php

declare(strict_types=1);

namespace TYPO3\CMS\VisualEditor\Service;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\DataProcessing\PageContentFetchingProcessor;
use TYPO3\CMS\Frontend\Page\PageInformation;
use TYPO3\CMS\VisualEditor\Enum\LanguageMode;

use function assert;
use function is_object;
use function method_exists;

final readonly class LanguageModeService
{
    public function __construct(private PageContentFetchingProcessor $pageContentFetchingProcessor)
    {
    }

    public function getBackendLanguageMode(PageInformation $pageInformation): LanguageMode
    {
        if ($pageInformation->getPageRecord()['sys_language_uid'] == 0) {
            return LanguageMode::Connected;
        }

        $isConnectedMode = false;
        $isFreeMode = false;

        $contentAreas = $pageInformation->getPageLayout()->getContentAreas();
        if (is_object($contentAreas) && method_exists($contentAreas, 'getGroupedRecords')) {
            $groupedRecords = $contentAreas->getGroupedRecords();
        } else {
            $groupedRecords = $this->getGroupedRecordsTYPO3v13($pageInformation->getPageRecord());
        }

        foreach ($groupedRecords as $contentArea) {
            foreach ($contentArea['records'] as $record) {
                if (!$record instanceof Record) {
                    throw new \InvalidArgumentException('Record array must implement ' . Record::class);
                }
                if ($record->getPid() !== $pageInformation->getId()) {
                    // skip records that are from slideMode
                    continue;
                }
                if ($record->getLanguageInfo()->getTranslationParent()) {
                    $isConnectedMode = true;
                } else {
                    $isFreeMode = true;
                }
            }
        }
        if ($isConnectedMode && $isFreeMode) {
            return LanguageMode::Mixed;
        }
        if ($isConnectedMode) {
            return LanguageMode::Connected;
        }
        if ($isFreeMode) {
            return LanguageMode::Free;
        }
        return LanguageMode::Connected;
    }

    /**
     * logic from: \TYPO3\CMS\Backend\View\PageLayoutContext::getAllowNewContent (v14)
     */
    public function getAllowNewContent(PageInformation $pageInformation, SiteLanguage $language): bool
    {
        if ($language->getLanguageId() === 0) {
            return true;
        }
        $pageTsConfig = BackendUtility::getPagesTSconfig($pageInformation->getId());
        $allowInconsistentLanguageHandling = (bool)($pageTsConfig['mod.']['web_layout.']['allowInconsistentLanguageHandling'] ?? false);
        $langaugeMode = $this->getBackendLanguageMode($pageInformation);
        if (!$allowInconsistentLanguageHandling && $langaugeMode === LanguageMode::Connected) {
            return false;
        }
        return true;
    }

    /**
     * @deprecated will be removed after TYPO3 v13 support is dropped
     */
    private function getGroupedRecordsTYPO3v13(array $pageRow): array
    {
        $request = $GLOBALS['TYPO3_REQUEST'];
        assert($request instanceof ServerRequestInterface);
        $contentObjectRenderer = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $contentObjectRenderer->setRequest($request);
        $contentObjectRenderer->start($pageRow, 'pages');
        return $this->pageContentFetchingProcessor->process(
            cObj: $contentObjectRenderer,
            contentObjectConfiguration: [],
            processorConfiguration: [
                'as' => 'groupedRecords',
            ],
            processedData: [],
        )['groupedRecords'];
    }
}
