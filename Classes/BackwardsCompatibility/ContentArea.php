<?php

declare(strict_types=1);

namespace TYPO3\CMS\VisualEditor\BackwardsCompatibility;

/**
 * @internal
 */
final readonly class ContentArea
{
    public function __construct(
        private int $colPos,
        private string $name,
        private int $tx_container_parent,
    ) {
    }

    public function getColPos(): int
    {
        return $this->colPos;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array{tx_container_parent: int}
     */
    public function getConfiguration(): array
    {
        return [
            'tx_container_parent' => $this->tx_container_parent,
        ];
    }

    /**
     * @return array{}
     */
    public function getAllowedContentTypes(): array
    {
        // TODO use content EXT:content_defender here?
        return [];
    }

    /**
     * @return array{}
     */
    public function getDisallowedContentTypes(): array
    {
        // TODO use content EXT:content_defender here?
        return [];
    }
}
