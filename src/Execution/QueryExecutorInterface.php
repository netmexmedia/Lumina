<?php

namespace Netmex\Lumina\Execution;

use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Intent\QueryIntent;

interface QueryExecutorInterface
{
    public function strategy(): string;

    public function execute(QueryIntent $intent, array $args, Context $context): mixed;
}