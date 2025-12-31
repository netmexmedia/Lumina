<?php

namespace Netmex\Lumina\Context;

readonly class Context
{
    public function __construct(
        public readonly ?object $user = null
    ) {}
}