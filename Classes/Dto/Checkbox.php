<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Dto;

final class Checkbox implements EditableResult
{
    public function __construct(
        public readonly string $name,
        public readonly string $html,
        public readonly bool $isEmpty,
    ) {}

    public function isChecked(): bool {
        return $this->isEmpty;
    }

    public function __toString(): string
    {
        return $this->html;
    }
}
