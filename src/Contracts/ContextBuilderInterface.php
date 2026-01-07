<?php

declare(strict_types=1);

namespace Netmex\Lumina\Contracts;

use Netmex\Lumina\Context\Context;

interface ContextBuilderInterface
{
    public function build(): Context;
}