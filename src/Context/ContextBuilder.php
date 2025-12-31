<?php

namespace Netmex\Lumina\Context;

use Netmex\Lumina\ContextBuilderInterface;

// TODO, Comes later
class ContextBuilder implements ContextBuilderInterface
{
    public function build(): Context
    {
        return new Context(
            user: null
        );
    }
}