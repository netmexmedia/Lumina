<?php

namespace Netmex\Lumina\Query;

final class QueryIntent
{
    private ?string $root = null;
    private string $mode = 'many';
    private array $filters = [];

    public function setRoot(string $root): void
    {
        $this->root = $root;
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    public function where(string $field, mixed $value, string $op = '='): void
    {
        $this->filters[] = compact('field', 'op', 'value');
    }

    public function getRoot(): ?string {
        return $this->root;

    }
    public function getMode(): string {
        return $this->mode;
    }

    public function getFilters(): array {
        return $this->filters;
    }
}