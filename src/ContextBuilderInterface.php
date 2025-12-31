<?php

namespace Netmex\Lumina;

use Netmex\Lumina\Context\Context;

interface ContextBuilderInterface
{
    public function build(): Context;
}