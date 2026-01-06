<?php

namespace Netmex\Lumina\Directives;

use Netmex\Lumina\Directives\Definition\AbstractDirective;

final class DirectiveRegistry
{
    /** @var array<string, AbstractDirective> */
    private array $sdlContributors = [];

    /** @var array<string, FieldDirective> */
    private array $fieldDirectives = [];

    /** @var array<string, AbstractDirective> */
    private array $directives = [];

    // Should become Directives later
    private array $classnames = [];

    public function add(string $key, string $classname): void
    {
        $this->classnames[$key] = $classname;
    }

    public function get(string $key): ?string
    {
        return $this->classnames[$key] ?? null;
    }

    public function all(): array
    {
        return $this->classnames;
    }

    public function registerSDL(AbstractDirective $directive): void
    {
        $this->sdlContributors[] = $directive;
    }

    /** @var array<string, ArgumentDirective> */
    private array $argumentDirectives = [];

    public function register(AbstractDirective $directive): void
    {
        $this->directives[AbstractDirective::name()] = $directive;
    }

    public function getDirective(string $name): ?AbstractDirective
    {
        return $this->directives[$name] ?? null;
    }

    public function getDirectives(): ?array
    {
        return $this->directives ?? null;
    }

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
                static fn (AbstractDirective $c) => trim($c->definition()),
                $this->sdlContributors
            )
        );
    }
}

