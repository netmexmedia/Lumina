<?php

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Netmex\Lumina\Schema\Runtime\ResolverAttacher;
use Netmex\Lumina\Schema\SchemaBuilderInterface;
use Netmex\Lumina\Schema\Source\SchemaDocumentLoaderInterface;
use Netmex\Lumina\Schema\Source\SchemaSDLLoaderInterface;

final readonly class SchemaCompiler implements SchemaBuilderInterface
{
    protected Schema $schema;

    public function __construct(
        private SchemaSDLLoaderInterface      $sdlLoader,
        private SchemaDocumentLoaderInterface $documentLoader,
        private IntentCompiler                $intentBuilder,
        private ResolverAttacher              $resolverAttacher,
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
