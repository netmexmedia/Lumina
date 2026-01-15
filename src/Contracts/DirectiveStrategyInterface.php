<?php

namespace Netmex\Lumina\Contracts;

use Netmex\Lumina\Intent\Intent;

interface DirectiveStrategyInterface
{
    public function supports(object $directive): bool;

    public function apply(object $directive, Intent $intent, object $fieldNode, ?object $document = null): void;
}