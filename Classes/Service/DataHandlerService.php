<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Service;

use Andersundsehr\Editara\Service\RecordService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Domain\RecordInterface;
use TYPO3\CMS\Core\Schema\Capability\TcaSchemaCapability;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use function array_combine;
use function array_fill;
use function assert;
use function count;
use function implode;

final readonly class DataHandlerService
{

    public function __construct(
        private RecordFactory $recordFactory,
        private TcaSchemaFactory $tcaSchema,
        private RecordService $recordService,
    ) {}

    public function createRecordInDb(string $table, array $row): Record
    {
        $newUid = $this->createRow($table, $row);
        // saved, now loading...

        $rowFromDb = BackendUtility::getRecord($table, $newUid);
        $record = $this->recordFactory->createResolvedRecordFromDatabaseRow($table, $rowFromDb);
        assert($record instanceof Record, 'Record is not instance of Record');
        return $record;
    }

    public function createRow(string $table, array $row): mixed
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class); // never use DataHandler over DI!!
        $data = [
            $table => [
                'NEW12345' => $row,
            ],
        ];
        $cmd = [];
        $dataHandler->start($data, $cmd);
        $dataHandler->process_datamap();
        if (count($dataHandler->errorLog) > 0) {
            throw new \RuntimeException('Error creating row in table ' . $table . ': ' . implode(', ', $dataHandler->errorLog));
        }
        $newUid = $dataHandler->substNEWwithIDs['NEW12345'];
        unset($dataHandler);
        return $newUid;
    }

    public function setL10nStateForFields(string $table, int $uid, int $pageUid, int|null $languageSyncUid): void
    {
        $fields = \TYPO3\CMS\Core\DataHandling\Localization\State::getFieldNames($table);
        if ($languageSyncUid === null) {
            $this->updateRow($table, $uid, [
                'l10n_state' => array_combine($fields, array_fill(0, count($fields), 'custom')),
            ]);
            return;
        }
        if ($languageSyncUid === 0) {
            $this->updateRow($table, $uid, [
                'l10n_state' => array_combine($fields, array_fill(0, count($fields), 'parent')),
            ]);
            return;
        }

        $mapping = $this->recordService->getFullLanguageMappingForPage($table, $pageUid);
        $parentUid = $mapping[$uid][0] ?? throw new \RuntimeException('Could not find parent for ' . $table . ':' . $uid . ' on pid ' . $pageUid);

        $recordUidOfTargetLanguage = $mapping[$parentUid][$languageSyncUid] ?? throw new \Exception('No translation found for ' . $table . ':' . $uid . ' on pid ' . $pageUid . ' for sys_language_uid ' . $languageSyncUid);

        $translationSource = $this->tcaSchema->get($table)->getCapability(TcaSchemaCapability::Language)->getTranslationSourceField()->getName();
        $this->updateRow($table, $uid, [
            $translationSource => $recordUidOfTargetLanguage,
            'l10n_state' => array_combine($fields, array_fill(0, count($fields), 'source')),
        ]);
    }

    public function updateRow(string $table, int $uid, array $fields): void
    {
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class); // never use DataHandler over DI!!
        $data = [
            $table => [
                $uid => $fields,
            ],
        ];
        $cmd = [];
        $dataHandler->start($data, $cmd);
        $dataHandler->process_datamap();
        if (count($dataHandler->errorLog) > 0) {
            throw new \RuntimeException('Error updating row in table ' . $table . ': ' . implode(', ', $dataHandler->errorLog));
        }
        unset($dataHandler);
    }


}
