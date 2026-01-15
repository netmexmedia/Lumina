<?php

namespace Netmex\Lumina\Schema\Helpers;

use Netmex\Lumina\Contracts\DirectiveInterface;
use Netmex\Lumina\Schema\Factory\DirectiveFactory;

final class DirectiveHelper
{
    private DirectiveFactory $factory;

    public function __construct(DirectiveFactory $directiveFactory)
    {
        $this->factory = $directiveFactory;
    }

    public function instantiateDirective($directiveNode, $definitionNode): DirectiveInterface
    {
        return $this->factory->create($directiveNode, $definitionNode);
    }
}