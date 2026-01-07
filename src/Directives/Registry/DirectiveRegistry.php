<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives\Registry;

use Netmex\Lumina\Directives\AbstractDirective;

final class DirectiveRegistry
{
    /** @var array<string, AbstractDirective> */
    private array $sdlContributors = [];

    /** @var array<string, string> */
    private array $directives = [];

    public function register(string $key, string $classname): void
    {
        $this->directives[$key] = $classname;
    }

    public function get(string $key): ?string
    {
        return $this->directives[$key] ?? null;
    }

    public function all(): array
    {
        return $this->directives;
    }

    // TODO: Seems redundant
    public function registerSDL(AbstractDirective $directive): void
    {
        $this->sdlContributors[] = $directive;
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

