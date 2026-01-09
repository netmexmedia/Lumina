<?php

namespace Netmex\Lumina\Contracts;

use Netmex\Lumina\Directives\AbstractDirective;

interface DirectiveFactoryInterface
{
    public function create(object $directiveNode, object $definitionNode): AbstractDirective;
}