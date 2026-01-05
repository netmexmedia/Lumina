<?php

namespace Netmex\Lumina\Schema;

use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Netmex\Lumina\Intent\IntentBuilder;
use Netmex\Lumina\Schema\Source\SchemaDocumentLoaderInterface;
use Netmex\Lumina\Schema\Source\SchemaSDLLoaderInterface;
use Netmex\Lumina\SchemaBuilderInterface;

final readonly class SchemaBuilder implements SchemaBuilderInterface
{
    protected Schema $schema;

    public function __construct(
        private SchemaSDLLoaderInterface $sdlLoader,
        private SchemaDocumentLoaderInterface $documentLoader,
        private IntentBuilder $intentBuilder,
        private ResolverAttacher $resolverAttacher,
    ) {}

    public function schema(): Schema
    {
        return $this->schema ??= $this->build();
    }

    public function build(): Schema
    {
        $sdl = $this->sdlLoader->load();

        $schema   = BuildSchema::build($sdl);
        $document = $this->documentLoader->load();

        $intents = $this->intentBuilder->build($document);
        $this->resolverAttacher->attach($schema, $intents);

        return $schema;
    }
}
