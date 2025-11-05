<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\ViewHelpers;

use Andersundsehr\Editara\Service\BrickService;
use TYPO3Fluid\Fluid\Core\Variables\ScopedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class BlocksViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeOutput = false;
    public function __construct(
        private readonly BrickService $brickService,
    ) {}


    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'The editable name', true);
    }

    public function render(): string
    {
        $name = $this->arguments['name'];

        $scope = $this->brickService->getScope($this->templateVariableContainer);

        $globalVariableProvider = $this->renderingContext->getVariableProvider();
        $localVariableProvider = new StandardVariableProvider();
        $this->renderingContext->setVariableProvider(new ScopedVariableProvider($globalVariableProvider, $localVariableProvider));

        $output = '';
        $index = 0;
        foreach (range(0, 3) as $value) {
            $childScope = $scope->createChild($name . '[' . $index . ']');
            $localVariableProvider->add('editara', $childScope);

            $output .= $this->renderChildren();
            $index++;
        }

        $this->renderingContext->setVariableProvider($globalVariableProvider);

        return $output;
    }
}
