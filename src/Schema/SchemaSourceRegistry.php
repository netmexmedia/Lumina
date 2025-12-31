<?php

namespace Netmex\Lumina\Schema;

use Netmex\Lumina\SchemaSource;

final class SchemaSourceRegistry
{
    /** @var SchemaSource[] */
    private iterable $sources;

    public function __construct(iterable $sources)
    {
        $this->sources = $sources;
    }

    /**
     * @return SchemaSource[]
     */
    public function all(): iterable
    {
        return $this->sources;
    }
}