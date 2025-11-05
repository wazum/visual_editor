<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\ViewHelpers\Editable;

use Andersundsehr\Editara\Dto\Editable;
use Andersundsehr\Editara\Dto\Link;
use Andersundsehr\Editara\Enum\EditableType;
use Andersundsehr\Editara\Service\BrickService;
use Andersundsehr\Editara\Service\EditableService;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Link\TypolinkViewHelper;
use TYPO3\CMS\Fluid\ViewHelpers\Uri\TypolinkViewHelper as UriTypolinkViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

use function assert;
use function str_replace;


#[Autoconfigure(public: true)]
final class LinkViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function __construct(
        private readonly BrickService $brickService,
        private readonly UriBuilder $uriBuilder,
        private readonly EditableService $editableService,
    ) {
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'The editable name', true);

        // copied from TypolinkViewHelper
        $this->registerArgument('target', 'string', 'Define where to display the linked URL', false, '');
        $this->registerArgument('class', 'string', 'Define classes for the link element', false, '');
        $this->registerArgument('title', 'string', 'Define the title for the link element', false, '');
        $this->registerArgument('language', 'string', 'link to a specific language - defaults to the current language, use a language ID or "current" to enforce a specific language');
        $this->registerArgument('additionalParams', 'string', 'Additional query parameters to be attached to the resulting URL', false, '');
        $this->registerArgument('additionalAttributes', 'array', 'Additional tag attributes to be added directly to the resulting HTML tag', false, []);
        $this->registerArgument(
            'addQueryString',
            'string',
            'If set, the current query parameters will be kept in the URL. If set to "untrusted", then ALL query parameters will be added. Be aware, that this might lead to problems when the generated link is cached.',
            false,
            false,
        );
        $this->registerArgument('addQueryStringExclude', 'string', 'Define parameters to be excluded from the query string (only active if addQueryString is set)', false, '');
        $this->registerArgument('absolute', 'bool', 'Ensure the resulting URL is an absolute URL', false, false);
        $this->registerArgument('parts-as', 'string', 'Variable name containing typoLink parts (if any)', false, 'typoLinkParts');
        $this->registerArgument('textWrap', 'string', 'Wrap the link using the typoscript "wrap" data type', false, '');
        // TODO add (all?) arguments from TypolinkViewHelper?
    }

    public function render(): Link
    {
        $name = $this->arguments['name'];
        unset($this->arguments['name']);

        $editable = $this->brickService->getEditable($this->renderingContext, $name, EditableType::link);

        $typolinkParameter = $editable->record->get(EditableType::link->getField());
        $rawTypolinkParameter = $editable->record->getRawRecord()->get(EditableType::link->getField());
        assert($typolinkParameter instanceof TypolinkParameter);

        unset($this->arguments['name']);
        $this->arguments['parameter'] = $typolinkParameter;
        $href = $this->renderingContext->getViewHelperInvoker()->invoke(
            UriTypolinkViewHelper::class,
            $this->arguments,
            $this->renderingContext,
            $this->renderChildren(...),
        );
        if ($this->brickService->isEditMode()) {
            $typoLinkParts = $typolinkParameter->toArray();
            $typoLinkParts['url'] = '#REPLACE_ME';
            $typolinkParameter = TypolinkParameter::createFromTypolinkParts($typoLinkParts);
        }
        $this->arguments['parameter'] = $typolinkParameter;
        $html = $this->renderingContext->getViewHelperInvoker()->invoke(
            TypolinkViewHelper::class,
            $this->arguments,
            $this->renderingContext,
            $this->renderChildren(...),
        );

        if ($this->brickService->isEditMode()) {
            // remove the href so all click events go to the children (we do not want to navigate away anyway)
            // TODO fix this properly, with the solution here, we have the problem that the link styles are lost
            $html = str_replace('href="#REPLACE_ME"', '', $html);
            $request = $this->getRenderingContext()->getAttribute(ServerRequestInterface::class);
            $html .= ' ' . $this->editableService->editButtonForEditable($request, $editable, "Edit field $name\n$href\n[$rawTypolinkParameter]");
        }
        return new Link($name, $html, $typolinkParameter->url === '', $href, $typolinkParameter);
    }
}
