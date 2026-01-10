<?php

namespace Netmex\Lumina\Validators;

class ValidatorRegistry
{
    private array $validators = [];

    public function register(string $name, string $className): void
    {
        $this->validators[$name] = $className;
    }

    public function resolve(string $name): string
    {
        if (!isset($this->validators[$name])) {
            throw new \LogicException("Validator identifier '{$name}' is not registered.");
        }

        return $this->validators[$name];
    }
}