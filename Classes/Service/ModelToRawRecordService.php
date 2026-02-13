<?php

declare(strict_types=1);

namespace TYPO3\CMS\VisualEditor\Service;

use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;

final readonly class ModelToRawRecordService
{

    public function __construct(
        private RecordFactory $recordFactory,
        private DataMapFactory $dataMapFactory,
    ) {
    }

    public function modelToRawRecord(DomainObjectInterface $model): RawRecord
    {
        $dataMap = $this->dataMapFactory->buildDataMap($model::class);
        $table = $dataMap->getTableName();
        $recordTypeField = $dataMap->getRecordTypeColumnName();
        $langaugeField = $dataMap->getLanguageIdColumnName();

        $row = [
            'uid' => $model->getUid(),
            'pid' => $model->getPid(),
            '_ORIG_uid' => $model->_getProperty('_versionedUid'),
            '_LOCALIZED_UID' => $model->_getProperty('_localizedUid'),
            $langaugeField => $model->_getProperty('_languageUid'),
        ];

        foreach ($model->_getProperties() as $propertyName => $value) {
            $columnName = $dataMap->getColumnMap($propertyName)?->getColumnName();
            if (!$columnName) {
                continue;
            }
            $row[$columnName] = $value;
        }

        if ($recordTypeField && $dataMap->getRecordType()) {
            $row[$recordTypeField] = $dataMap->getRecordType();
        }

        return $this->recordFactory->createRawRecord($table, $row);
    }
}
