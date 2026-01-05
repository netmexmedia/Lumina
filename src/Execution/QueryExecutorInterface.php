<?php

namespace Netmex\Lumina\Execution;

use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Intent\Intent;

interface QueryExecutorInterface
{
    public function strategy(): string;

    public function execute(Intent $intent, array $args, Context $context): mixed;
}