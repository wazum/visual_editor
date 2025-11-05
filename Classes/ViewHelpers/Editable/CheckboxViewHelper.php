<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\ViewHelpers\Editable;

use Andersundsehr\Editara\Dto\Checkbox;
use Andersundsehr\Editara\Dto\Editable;
use Andersundsehr\Editara\Enum\EditableType;
use Andersundsehr\Editara\Service\BrickService;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;


#[Autoconfigure(public: true)]
final class CheckboxViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function __construct(
        private readonly BrickService $brickService,
        private readonly UriBuilder $uriBuilder,
    ) {}

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'The editable name', true);
        $this->registerArgument('label', 'string', 'label text (if not empty will remove label tag)', false, '');
    }

    public function render(): Checkbox
    {
        $name = $this->arguments['name'];
        $label = $this->arguments['label']; // TODO support LLL: syntax

        $editable = $this->brickService->getEditable($this->renderingContext, $name, EditableType::checkbox);

        $bool = (bool)$editable->getValue();

        $html = (string)(int)$bool;
        if ($this->brickService->isEditMode()) {
            $tag = new TagBuilder('input');
            $tag->addAttribute('type', 'checkbox');
            $tag->addAttribute('table', $editable->record->getMainType());
            $tag->addAttribute('uid', (string)$editable->record->getUid());
            $tag->addAttribute('field', $editable->field);
            $tag->addAttribute('initial', $bool ? 'true' : 'false');
            if ($bool) {
                $tag->addAttribute('checked', null);
            }
            $html = $tag->render();
            if($label){
                $labelTag = new TagBuilder('label');
                $labelTag->setContent($html . ' ' . $label);
                $html = $labelTag->render();
            }

        }

        return new Checkbox($name, $html, $bool);
    }
}
