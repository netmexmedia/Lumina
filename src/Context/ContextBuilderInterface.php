<?php

namespace Netmex\Lumina\Context;

interface ContextBuilderInterface
{
    public function build(): Context;
}