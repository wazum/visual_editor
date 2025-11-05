<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\ViewHelpers;

use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumn;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\LanguageColumn;
use TYPO3\CMS\Backend\View\PageLayoutContext;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

use function assert;
use function date_date_set;


final class BackendIframeViewHelper extends AbstractTagBasedViewHelper
{
    protected $tagName = 'iframe';

    public function __construct(private SiteFinder $siteFinder, private readonly AssetCollector $assetCollector)
    {
        parent::__construct();
    }


    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('all', 'array', '_all', true);
    }

    public function render(): string
    {
//        return 'NOPE';

        $this->assetCollector->addJavaScriptModule('@andersundsehr/editara/middleFrameScript.js');

        $context = $this->arguments['all']['context'];
        assert($context instanceof PageLayoutContext);

        $site = $this->siteFinder->getSiteByPageId($context->getPageRecord()['uid']);

        $column = $this->arguments['all']['column'] ?? null;
        if ($column instanceof GridColumn) {
            $siteLanguage = $column->getContext()->getSiteLanguage();
        }

        $languageColumn = $this->arguments['all']['languageColumn'] ?? null;
        if ($languageColumn instanceof LanguageColumn) {
            $siteLanguage = $languageColumn->getContext()->getSiteLanguage();
        }

        $uri = $site->getRouter()->generateUri(
            $context->getPageRecord()['uid'],
            [
                '_language' => $siteLanguage->getLanguageId(),
                'editara' => 1,
                // this dose not ignore the fluid cache. but CTRL + SHIFT + R does:
                // TODO make it so if editara is set, it is handled as if CTRL + SHIFT + R was pressed?
                //'no_cache' => 1,
            ],
        );

        $this->tag->addAttribute('src', $uri);
        $this->tag->addAttribute('style', 'width: 100%; height: 600px; border: 1px solid rgba(0,0,0,.1); border-radius: 6px; background: #fff;');
        $this->tag->forceClosingTag(true);
        return $this->tag->render();
    }
}
