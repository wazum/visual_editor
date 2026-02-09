<?php

declare(strict_types=1);

namespace TYPO3\CMS\VisualEditor\ViewHelpers\Render;

use TYPO3\CMS\Core\Configuration\Richtext as RichtextConfiguration;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Domain\RecordInterface;
use TYPO3\CMS\Core\Html\RteHtmlParser;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Schema\Field\InputFieldType;
use TYPO3\CMS\Core\Schema\Field\TextFieldType;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\ViewHelpers\Format\HtmlViewHelper;
use TYPO3\CMS\Frontend\Page\PageInformation;
use TYPO3\CMS\VisualEditor\Core\RichtText\RichTextConfigurationService;
use TYPO3\CMS\VisualEditor\Core\RichtText\RichTextConfigurationServiceDto;
use TYPO3\CMS\VisualEditor\EditableResult\RichText;
use TYPO3\CMS\VisualEditor\Service\EditModeService;
use TYPO3Fluid\Fluid\Core\Parser\UnsafeHTML;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use function dd;
use function json_encode;

/**
 * @deprecated TODO Will be removed after https://review.typo3.org/c/Packages/TYPO3.CMS/+/92746 is merged
 */
final class RichTextViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument('record', RecordInterface::class . '|' . PageInformation::class, 'A Record API Object (field is also needed)');
        $this->registerArgument('field', 'string', 'the field that should be rendered', true);
    }

    public function validateAdditionalArguments(array $arguments): void
    {
        // This prevents the default Fluid exception from being thrown for this ViewHelper if it's used
        // with arguments that aren't defined in initialArguments(). We do this to make it possible for
        // extensions to offer additional functionality by overriding this ViewHelper, which sometimes
        // requires adding more (most likely optional) arguments to the ViewHelper's definition.
        // Note that this is probably not a long-term solution and might change with future TYPO3 major
        // versions. Currently, it has minimal impact to template authors and makes things possible
        // for extensions that wouldn't be possible otherwise.
    }

    public function getContentArgumentName(): string
    {
        return 'record';
    }

    public function render(): UnsafeHTML
    {
        return $this->renderingContext->getViewHelperInvoker()->invoke(
            TextViewHelper::class,
            $this->arguments,
            $this->renderingContext,
            $this->buildRenderChildrenClosure(),
        );
    }
}
