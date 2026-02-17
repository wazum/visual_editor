<?php

declare(strict_types=1);

namespace TYPO3\CMS\VisualEditor\Service;

use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Extbase\DomainObject\DomainObjectInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapFactory;
use function get_debug_type;
use function in_array;

final readonly class ModelToRawRecordService
{

    public function __construct(
        private RecordFactory $recordFactory,
        private DataMapFactory $dataMapFactory,
    ) {
    }

    /**
     * Extracts all possible information from the given Extbase model
     * and transforms it into a RawRecord that can be used there a RecordInterface is needed.
     */
    public function modelToRawRecord(DomainObjectInterface $model): RawRecord
    {
        $dataMap = $this->dataMapFactory->buildDataMap($model::class);
        $table = $dataMap->getTableName();
        $recordTypeField = $dataMap->getRecordTypeColumnName();
        $languageField = $dataMap->getLanguageIdColumnName();

        $row = [
            'uid' => $model->getUid(),
            'pid' => $model->getPid(),
            '_ORIG_uid' => $model->_getProperty('_versionedUid'),
            '_LOCALIZED_UID' => $model->_getProperty('_localizedUid'),
        ];
        if ($languageField) {
            $row[$languageField] = (int)$model->_getProperty('_languageUid');
        }

        foreach ($model->_getProperties() as $propertyName => $value) {
            $columnName = $dataMap->getColumnMap($propertyName)?->getColumnName();
            if (!$columnName) {
                continue;
            }
            // for now we only support scalar values
            if (!in_array(get_debug_type($value), ['null', 'bool', 'int', 'float', 'string'], true)) {
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
