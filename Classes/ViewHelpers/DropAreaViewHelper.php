<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\ViewHelpers;

use Andersundsehr\Editara\Service\BrickService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

final class DropAreaViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function __construct(private readonly BrickService $brickService)
    {
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('colPos', 'int', 'The colPos number', true);
        $this->registerArgument('table', 'string', 'The table of the dropArea', false, 'tt_content');
        $this->registerArgument('sys_language_uid', 'int', 'The sys_language_uid of the dropArea', false, null);
    }

    public function render(): mixed
    {
        if (!$this->brickService->isEditMode()) {
            return $this->renderChildren();
        }

        $tag = GeneralUtility::makeInstance(TagBuilder::class, 'editara-drop-area', $this->renderChildren());
        $tag->addAttribute('colPos', (string)$this->arguments['colPos']);
        $tag->addAttribute('table', $this->arguments['table']);
        $sysLanguageUid = $this->arguments['sys_language_uid'] ?? GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id');
        $tag->addAttribute('sys_language_uid', (string)$sysLanguageUid);
        return $tag->render();
    }
}
