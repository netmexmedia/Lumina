<?php

namespace Netmex\Lumina\Schema\Directive;

class DirectiveTypeRegistryVisitor
{
    private array $inputTypes = [];
    private array $objectTypes = [];

    public function setInputTypes(array $types): void
    {
        $this->inputTypes = $types;
    }

    public function setObjectTypes(array $types): void
    {
        $this->objectTypes = $types;
    }

    public function getInputType(string $name): ?object
    {
        return $this->inputTypes[$name] ?? null;
    }

    public function getObjectType(string $name): ?object
    {
        return $this->objectTypes[$name] ?? null;
    }

    public function isObjectType(string $name): bool
    {
        return isset($this->objectTypes[$name]);
    }

    public function isInputType(string $name): bool
    {
        return isset($this->inputTypes[$name]);
    }
}