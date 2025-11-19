<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Dto;

use TYPO3\CMS\Core\Resource\FileReference;

final readonly class Image implements EditableResult
{
    public function __construct(
        public string $name,
        public string $html,
        public Editable $editable,
        public bool $isEmpty,
        public ?FileReference $image,
    ) {}

    public function __toString(): string
    {
        return $this->html;
    }
}
