<?php

namespace Netmex\Lumina\Directives;

use Netmex\Lumina\Schema\FieldDirective;
use Netmex\Lumina\SchemaSDLContributorInterface;

final class DirectiveRegistry
{
    /** @var array<string, SchemaSDLContributorInterface> */
    private array $sdlContributors = [];

    /** @var array<string, FieldDirective> */
    private array $fieldDirectives = [];

    public function registerSDL(SchemaSDLContributorInterface $directive): void
    {
        $this->sdlContributors[] = $directive;
    }

    /** @var array<string, ArgumentDirective> */
    private array $argumentDirectives = [];

    public function registerField(FieldDirective $directive): void
    {
        $this->fieldDirectives[$directive->name()] = $directive;
    }

    public function registerArgument(ArgumentDirective $directive): void
    {
        $this->argumentDirectives[$directive->name()] = $directive;
    }

    public function field(string $name): ?FieldDirective
    {
        return $this->fieldDirectives[$name] ?? null;
    }

    public function argument(string $name): ?ArgumentDirective
    {
        return $this->argumentDirectives[$name] ?? null;
    }

    public function definitionsSDL(): string
    {
        return implode(
            "\n\n",
            array_map(
                static fn (SchemaSDLContributorInterface $c) => trim($c->definition()),
                $this->sdlContributors
            )
        );
    }
}

