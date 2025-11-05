<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Enum;

enum EditableType
{
    case input;
    case link;
    case image;
    case checkbox;

    public function getField(): string
    {
        return match ($this) {
            EditableType::input => 'value',
//            EditableType::select => 'value',
            default => $this->name,
        };
    }
}
