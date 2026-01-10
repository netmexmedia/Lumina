<?php

namespace Netmex\Lumina\Contracts;

interface ValidatorInterface
{
    public static function name(): string;

    public function handle($value): bool;
}