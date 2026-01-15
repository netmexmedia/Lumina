<?php

namespace Netmex\Lumina\Schema\Directive\Strategy;

use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Schema\Directive\DirectiveProcessor;

class FieldArgumentInjectionStrategy
{
    private DirectiveProcessor $processor;

    public function __construct(DirectiveProcessor $processor) {
        $this->processor = $processor;
    }

    public function supports(object $directive): bool
    {
        return $directive instanceof FieldArgumentDirectiveInterface;
    }

    public function apply(object $directive, Intent $intent, object $fieldNode, ?object $document = null): void
    {
        if (!$document) {
            return;
        }

        $this->processor->injectDirectiveArguments($fieldNode, $directive);
    }
}