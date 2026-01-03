<?php

namespace Netmex\Lumina\Query;

use Netmex\Lumina\Directive\DirectiveMetadataRegistry;

final class QueryIntentBuilder
{
    public function __construct(
        private DirectiveMetadataRegistry $registry
    ) {}

    public function build(
        string $type,
        string $field,
        array $args
    ): QueryIntent {
        $intent = new QueryIntent();

        foreach ($this->registry->get("$type.$field") as $directive) {
            $this->applyFieldDirective($intent, $directive);
        }

        foreach ($args as $arg => $value) {
            foreach ($this->registry->get("$type.$field.$arg") as $directive) {
                $this->applyArgumentDirective($intent, $directive, $arg, $value);
            }
        }

        return $intent;
    }

    private function applyFieldDirective(QueryIntent $intent, array $directive): void
    {
        if ($directive['name'] === 'all') {
            $intent->setRoot($directive['args']['model']);
            $intent->setMode('many');
        }

        if ($directive['name'] === 'find') {
            $intent->setRoot($directive['args']['model']);
            $intent->setMode('one');
        }
    }

    private function applyArgumentDirective(
        QueryIntent $intent,
        array $directive,
        string $arg,
        mixed $value
    ): void {
        if ($directive['name'] === 'where') {
            $intent->where($arg, $value);
        }
    }
}