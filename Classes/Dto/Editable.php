<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Dto;

use Andersundsehr\Editara\Enum\EditableType;
use TYPO3\CMS\Core\Domain\Record;

final readonly class Editable
{
    public function __construct(
        public string $name,
        public EditableType $type,
        public string $field,
        public Record $record,
    ) {}

    public function getValue(): mixed
    {
        return $this->record->get($this->field);
    }

    public function getRawValue(): mixed
    {
        return $this->record->getRawRecord()->get($this->field);
    }
}
