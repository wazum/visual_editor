<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\UserFunction;

use Andersundsehr\Editara\Service\BrickService;
use Andersundsehr\Editara\Service\EditableService;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Form\Service\TranslationService;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder;
use function assert;

#[Autoconfigure(public: true)]
final readonly class TtContentStdWrapPreUserFunc
{
    public function __construct(
        private RecordFactory $recordFactory,
        private BrickService $brickService,
    )
    {
    }

    public function __invoke(string $content, array $conf, ServerRequestInterface $request): string
    {
        if (!$this->brickService->isEditMode()) {
            return $content;
        }

        $currentContentObject = $request->getAttribute('currentContentObject');
        assert($currentContentObject instanceof ContentObjectRenderer);

        $table = $currentContentObject->getCurrentTable();
        $data = $currentContentObject->data;
        $record = $this->recordFactory->createResolvedRecordFromDatabaseRow($table, $data);
        assert($record instanceof Record);

        $colPos = $record->get('colPos');

        $cmd = [
            $table => [
                $record->getUid() => [
                    'move' => [
                        'action' => 'paste',
                        'target' => $record->getPid(),
                        // pid if moving at the top of colPos
                        // -tt_content.uid if moving below a specific record
                        'update' => [
                            'colPos' => $colPos,
                            'sys_language_uid' => $record->getLanguageId(), // if you move between languages
                        ],
                    ],
                ],
            ],
        ];

        $div = GeneralUtility::makeInstance(TagBuilder::class, 'editara-area-brick');
        $recordTypeLabel = $this->getRecirdTypeLabel($record);
        $div->addAttribute('elementName', $recordTypeLabel);
        $div->addAttribute('table', $table);
        $div->addAttribute('id', 'cc' . $record->getUid());
        $div->addAttribute('uid', (string)$record->getUid());
        $div->addAttribute('pid', (string)$record->getPid());
        $div->addAttribute('colPos', $colPos);
        $div->addAttribute('sys_language_uid', $record->getLanguageId());
        $div->setContent($content);

        return $div->render();
    }

    private function getRecirdTypeLabel(Record $record): string
    {
        $recordType = $record->getRecordType();
        foreach ($GLOBALS['TCA']['tt_content']['columns']['CType']['config']['items'] as $item) {
            if ($item['value'] === $recordType) {
                return LocalizationUtility::translate($item['label']);
            }
        }
        return $recordType;
    }
}
