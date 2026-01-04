<?php

namespace Netmex\Lumina\Schema;

final readonly class RegistrySchemaSDLLoader implements SchemaSDLLoaderInterface
{
    public function __construct(
        private SchemaSourceRegistry $sourceRegistry
    ) {}

    public function load(): string
    {
        $sdl = '';

        foreach ($this->sourceRegistry->all() as $source) {
            $sdl .= "\n" . $source->load();
        }

        return $sdl;
    }
}