<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Service;

use Exception;
use RuntimeException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Schema\Capability\TcaSchemaCapability;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Frontend\Page\PageInformation;

use function json_decode;

final readonly class RecordService
{
    public function __construct(private TcaSchemaFactory $tcaSchemaFactory, private ConnectionPool $connectionPool, private RecordFactory $recordFactory) {}

    /**
     * TODO find equal CORE function in TYPO3
     */
    public function getSyncLanguageForField(Site $site, Record $record, string $field): ?SiteLanguage
    {
        $schema = $this->tcaSchemaFactory->get($record->getMainType());
        if (!$schema->hasCapability(TcaSchemaCapability::Language)) {
            return null;
        }
        if (!$record->getLanguageId()) {
            return null;
        }
        $l10nState = $record->getRawRecord()?->get('l10n_state');
        if (!$l10nState) {
            return null;
        }
        $state = json_decode($l10nState, true, 512, JSON_THROW_ON_ERROR)[$field] ?? null;
        if (!$state) {
            return null;
        }
        if ($state === 'custom') {
            return null;
        }

        $syncItemUid = null;
        if ($state === 'parent') {
            $syncItemUid = $record->getLanguageInfo()?->getTranslationParent();
        }
        if ($state === 'source') {
            $syncItemUid = $record->getLanguageInfo()?->getTranslationSource();
        }

        if (!$syncItemUid) {
            throw new RuntimeException('No sync item uid found for l10n_state "' . $state . '" for field "' . $field . '" for record ' . $record->getMainType() . ':' . $record->getUid());
        }
        // get all uid => sys_language_uid mapping for current page
        $languageMapping = $this->getLanguageMappingForPage($record->getMainType(), $record->getPid());
        return $site->getLanguageById($languageMapping[$syncItemUid] ?? throw new Exception('No language found for ' . $record->getMainType() . ':' . $syncItemUid . ' on pid ' . $record->getPid()));
    }

    /**
     * @return array<int, int> mapping of uid => sys_language_uid for all records on the given pid
     */
    private function getLanguageMappingForPage(string $table, int $pid): array
    {
        // TODO add Cache
        // TODO move to different service
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        return $queryBuilder
            ->select('uid', 'sys_language_uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('pid', $pid),
                $queryBuilder->expr()->eq('deleted', 0),
            )
            ->executeQuery()
            ->fetchAllKeyValue();
    }

    /**
     * @param string $table
     * @param int $pageUid
     * @return array<int, array<int, int>> parent uid => (language uid => record uid)
     */
    public function getFullLanguageMappingForPage(string $table, int $pageUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $schema = $this->tcaSchemaFactory->get($table);
        $languageCapability = $schema->getCapability(TcaSchemaCapability::Language);

        $queryPart = null;
        if ($schema->hasCapability(TcaSchemaCapability::SoftDelete)) {
            $softDelete = $schema->getCapability(TcaSchemaCapability::SoftDelete)->getFieldName();
            $queryPart = $queryBuilder->expr()->eq($softDelete, 0);
        }

        $languageField = $languageCapability->getLanguageField()->getName();
        $languageParentField = $languageCapability->getTranslationOriginPointerField()->getName();
        $rows = $queryBuilder
            ->select('uid', $languageField, $languageParentField)
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('pid', $pageUid),
                $queryPart,
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $languageMapping = [];
        foreach ($rows as $row) {
            $uid = (int)$row['uid'];
            $parentUid = (int)($row[$languageParentField] ?? $uid);
            $languageUid = (int)$row[$languageField];
            if ($parentUid) {
                $languageMapping[$parentUid] ??= [];
                $languageMapping[$parentUid][$languageUid] = $uid;
            }
            $languageMapping[$uid][0] = $parentUid ?: $uid;
        }
        return $languageMapping;
    }

    public function getPageRecordAsRecordInterface(PageInformation $pageInformation): Record
    {
        return $this->recordFactory->createResolvedRecordFromDatabaseRow('pages', $pageInformation->getPageRecord());
    }
}
