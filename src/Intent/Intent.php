<?php

namespace Netmex\Lumina\Intent;

class Intent
{
    public const STRATEGY_ALL = 'all';

    public function __construct(
        public string $type,
        public string $field,
    ) {}


    /** Strategy: all, find, custom, etc */ // TODO: Enum
    public ?string $strategy = null;

    /** Domain model (optional) */
    public ?string $model = null;

    /** @var FilterIntentInterface[] */
    public array $filters = [];

    /** @var SortIntentInterface[] */
    public array $sorting = [];

    public ?int $limit = null;
    public ?int $offset = null;
}