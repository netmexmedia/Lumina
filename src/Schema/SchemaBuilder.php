<?php

namespace Netmex\Lumina\Schema;

use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Netmex\Lumina\SchemaBuilderInterface;

final readonly class SchemaBuilder implements SchemaBuilderInterface
{
    public function __construct(
        private SchemaSourceRegistry $registry
    ) {}

    public function build(): Schema
    {
        $sdl = '';

        foreach ($this->registry->all() as $source) {
            $sdl .= "\n" . $source->load();
        }

        // TODO: implment propper error handling
        return BuildSchema::build($sdl);
    }
}
