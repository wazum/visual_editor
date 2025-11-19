<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Dto;

use TYPO3\CMS\Core\Imaging\Icon;

final readonly class BrickTemplate {
    public function __construct(
        public string $name,
        public string $title,
        public string $templatePath,
        public Icon $icon,
        public string $description,
    )
    {
    }
}
