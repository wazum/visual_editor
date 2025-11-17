<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\ViewHelpers;

use Andersundsehr\Editara\Service\BrickService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

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
