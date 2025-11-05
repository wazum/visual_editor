<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\ViewHelpers\Editable;

use Andersundsehr\Editara\Dto\Editable;
use Andersundsehr\Editara\Dto\Image;
use Andersundsehr\Editara\Enum\EditableType;
use Andersundsehr\Editara\Service\BrickService;
use Andersundsehr\Editara\Service\EditableService;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Resource\Collection\LazyFileReferenceCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;

use function assert;


#[Autoconfigure(public: true)]
final class ImageViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function __construct(
        private readonly BrickService $brickService,
        private readonly UriBuilder $uriBuilder,
        private readonly EditableService $editableService,
    ) {}

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('name', 'string', 'The editable name', true);

        // copied from TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
        $this->registerArgument('crop', 'string|bool|array', 'overrule cropping of image (setting to FALSE disables the cropping set in FileReference)');
        $this->registerArgument('cropVariant', 'string', 'select a cropping variant, in case multiple croppings have been specified or stored in FileReference', false, 'default');
        $this->registerArgument('fileExtension', 'string', 'Custom file extension to use');

        $this->registerArgument(
            'width',
            'string',
            'width of the image. This can be a numeric value representing the fixed width of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.width in the TypoScript Reference on https://docs.typo3.org/permalink/t3tsref:confval-imgresource-width for possible options.',
        );
        $this->registerArgument(
            'height',
            'string',
            'height of the image. This can be a numeric value representing the fixed height of the image in pixels. But you can also perform simple calculations by adding "m" or "c" to the value. See imgResource.height in the TypoScript Reference https://docs.typo3.org/permalink/t3tsref:confval-imgresource-height for possible options.',
        );
        $this->registerArgument('minWidth', 'int', 'minimum width of the image');
        $this->registerArgument('minHeight', 'int', 'minimum height of the image');
        $this->registerArgument('maxWidth', 'int', 'maximum width of the image');
        $this->registerArgument('maxHeight', 'int', 'maximum height of the image');
        $this->registerArgument('absolute', 'bool', 'Force absolute URL', false, false);
        $this->registerArgument('base64', 'bool', 'Adds the image data base64-encoded inline to the image‘s "src" attribute. Useful for FluidEmail templates.', false, false);
    }

    public function render(): Image
    {
        $name = $this->arguments['name'];
        unset($this->arguments['name']);

        $editable = $this->brickService->getEditable($this->renderingContext, $name, EditableType::image);

        $imageObject = $editable->getValue();
        assert($imageObject instanceof LazyFileReferenceCollection);
        $image = $imageObject->offsetGet(0);

        $html = '';
        if ($image) {
            $this->arguments['image'] = $image;
            $html = $this->renderingContext->getViewHelperInvoker()->invoke(
                \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper::class,
                $this->arguments,
                $this->renderingContext,
                $this->renderChildren(...),
            );
        }
        if ($this->brickService->isEditMode()) {
            $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);
            $html .= ' ' . $this->editableService->editButtonForEditable($request, $editable, "Edit image $name");
        }
        return new Image($name, $html, $image === null, $image);
    }
}
