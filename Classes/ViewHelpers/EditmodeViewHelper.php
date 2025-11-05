<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\ViewHelpers;

use Andersundsehr\Editara\Service\BrickService;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\ScopedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class EditmodeViewHelper extends AbstractConditionViewHelper
{
    public function __construct(
        private readonly BrickService $brickService,
    ) {}


    /**
     * Renders <f:then> child if $condition is true, otherwise renders <f:else> child.
     *
     * @return mixed
     * @api
     */
    public function render()
    {
        if ($this->brickService->isEditMode()) {
            return $this->renderThenChild();
        }
        return $this->renderElseChild();
    }
}
