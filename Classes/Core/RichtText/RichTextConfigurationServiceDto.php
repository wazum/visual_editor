<?php

declare(strict_types=1);

namespace TYPO3\CMS\VisualEditor\Core\RichtText;

final readonly class RichTextConfigurationServiceDto
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $additionalConfiguration
     * @param array<string, mixed> $externalPlugins
     */
    public function __construct(
        public string $tableName,
        public int $uid,
        public string $fieldName,
        public string $recordTypeValue,
        public int $effectivePid,
        public string $richtextConfigurationName,
        public string $label = '',
        public string $placeholder = '',
        public bool $readOnly = false,
        private array $data = [],
        private array $additionalConfiguration = [],
        private array $externalPlugins = [],
    ) {
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return mixed[]
     */
    public function getAdditionalConfiguration(): array
    {
        return $this->additionalConfiguration;
    }

    /**
     * @return mixed[]
     */
    public function getExternalPlugins(): array
    {
        return $this->externalPlugins;
    }
}
