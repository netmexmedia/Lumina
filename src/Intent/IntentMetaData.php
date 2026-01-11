<?php

namespace Netmex\Lumina\Intent;

use Netmex\Lumina\Contracts\IntentMetaDataInterface;

final class IntentMetaData implements IntentMetaDataInterface
{
    private ?string $strategy;
    private ?string $model;

    private array $fields = [];


    /** @var IntentMetaData[] */
    private array $children = [];

    private intent $parent;


    public function __construct(?string $strategy = null)
    {
        $this->strategy = $strategy;
    }

    public function setStrategy(?string $strategy): void
    {
        $this->strategy = $strategy;
    }

    public function getStrategy(): ?string
    {
        return $this->strategy;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setFields(array $fields): void
    {
        $this->fields = $fields;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function addChild(IntentMetaDataInterface $metaData): void
    {
        $this->children[] = $metaData;
    }

    public function getChild(string $index): IntentMetaData
    {
        return $this->children[$index];
    }

    /** @return IntentMetaData[] */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function setParent(Intent $parent): void
    {
        $this->parent = $parent;
    }

    public function getParent(): Intent
    {
        return $this->parent;
    }
}
