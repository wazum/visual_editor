<?php

declare(strict_types=1);

namespace TYPO3\CMS\VisualEditor\BackwardsCompatibility;

/**
 * @internal
 */
final readonly class ContentArea
{
    public function __construct(private int $colPos, private int $tx_container_parent)
    {
    }

    public function getColPos(): int
    {
        return $this->colPos;
    }

    public function getConfiguration(): array
    {
        return [
            'tx_container_parent' => $this->tx_container_parent,
        ];
    }
}
