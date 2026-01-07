<?php

declare(strict_types=1);

namespace Netmex\Lumina\Context;

interface ContextBuilderInterface
{
    public function build(): Context;
}