<?php

namespace Netmex\Lumina\Intent;

interface FilterIntentInterface
{
    public function apply(object $builder, array $args): void;
}