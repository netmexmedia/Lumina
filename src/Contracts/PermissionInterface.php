<?php

namespace Netmex\Lumina\Contracts;

interface PermissionInterface
{
    public static function name(): string;
    
    public function handle(): bool;
}