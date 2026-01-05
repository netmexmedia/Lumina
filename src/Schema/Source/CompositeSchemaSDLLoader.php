<?php

namespace Netmex\Lumina\Schema\Source;

final readonly class CompositeSchemaSDLLoader implements SchemaSDLLoaderInterface
{
    /**
     * @param iterable<SchemaSDLLoaderInterface> $loaders
     */
    public function __construct(
        private iterable $loaders
    ) {}

    public function load(): string
    {
        $sdl = '';

        foreach ($this->loaders as $loader) {
            $chunk = trim($loader->load());
            if ($chunk !== '') {
                $sdl .= "\n\n" . $chunk;
            }
        }

        return ltrim($sdl);
    }
}