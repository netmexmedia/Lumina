<?php

namespace Netmex\Lumina;

use Netmex\Lumina\Query\QueryIntent;

interface QueryExecutorInterface
{
    public function execute(QueryIntent $intent): mixed;
}