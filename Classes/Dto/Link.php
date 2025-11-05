<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Dto;

use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;

final readonly class Link implements EditableResult
{
    public function __construct(
        public string $name,
        public string $html,
        public bool $isEmpty,
        public string $href,
        public TypolinkParameter $typolinkParameter,
    ) {}

    public function __toString(): string
    {
        return $this->html;
    }
}
