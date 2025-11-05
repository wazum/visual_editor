<?php

declare(strict_types=1);

namespace Andersundsehr\Editara\Fluid;

use Andersundsehr\Editara\Dto\Editable;
use Andersundsehr\Editara\Enum\EditableType;
use Andersundsehr\Editara\Service\BrickService;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RecordInterface;

use function array_filter;
use function implode;

final class Scope
{
    private array $editables = [];
    private array $children = [];

    public function __construct(
        public readonly string $name,
        public readonly Record $templateBrick,
        public readonly BrickService $brickService,
        public readonly ?Scope $parent = null,
    ) {}

    public function getEditable(string $name, EditableType $type): Editable
    {
        if (!$name) {
            throw new \Exception('name must be not empty');
        }
        if (isset($this->editables[$name])) {
            throw new \RuntimeException('Editable with name "' . $name . '" already exists in this scope'); // TODO allow overwriting?
        }

//        $key = $this->getName($name);
        // TODO handle nesting

        $record = null;
        foreach ($this->templateBrick->get('editables') as $editable) {
            if ($editable->get('name') === $name) {
                $record = $editable;
                break;
            }
        }
        // TODO create editable if not found
        if (!$record) {
            $record = $this->brickService->createEditableRecord($this->templateBrick, $name, $type);
        }

        return $this->editables[$name] = new Editable($name, $type, $type->getField(), $record);
    }

    public function createChild(string $name): self
    {
        if (isset($this->children[$name])) {
            throw new \RuntimeException('Child scope with name "' . $name . '" already exists in this scope');
        }

        $templateBrick = $this->templateBrick->get('template_bricks')->filter(function(RecordInterface $brick) use ($name) {
            return $brick->get('name') === $name; // TODO fix this
        })->first();
        $child = new self($name, $templateBrick, $this->brickService, $this);
        $this->children[$name] = $child;
        return $child;
    }

    public function getName(?string $childName = null): string
    {
        return implode('.', array_filter([$this->parent?->getName(), $this->name, $childName]));
    }
}
