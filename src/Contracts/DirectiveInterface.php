<?php

declare(strict_types=1);

namespace Netmex\Lumina\Contracts;

interface DirectiveInterface
{
    public static function definition(): string;
}