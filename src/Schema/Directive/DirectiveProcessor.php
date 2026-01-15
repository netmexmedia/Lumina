<?php

namespace Netmex\Lumina\Schema\Directive;

use Netmex\Lumina\Contracts\DirectiveStrategyInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Intent\Intent;

final class DirectiveProcessor
{
    /** @var DirectiveStrategyInterface[] */
    private iterable $strategies;

    public function __construct(iterable $strategies)
    {
        $this->strategies = $strategies;
    }

    public function process(object $directive, Intent $intent, object $fieldNode, ?object $document = null): void
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($directive)) {
                $strategy->apply($directive, $intent, $fieldNode, $document);
            }
        }
    }

    public function injectDirectiveArguments($fieldNode, FieldArgumentDirectiveInterface $directive): void
    {
        foreach ($directive->argumentNodes() as $argNode) {
            $name = $argNode->name->value;

            foreach ($fieldNode->arguments as $existingArg) {
                if ($existingArg->name->value === $name) {
                    continue 2;
                }
            }

            $fieldNode->arguments[] = $argNode;
        }
    }
}