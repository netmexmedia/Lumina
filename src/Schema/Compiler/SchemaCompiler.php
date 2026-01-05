<?php

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Netmex\Lumina\Schema\Source\SchemaDocumentLoaderInterface;
use Netmex\Lumina\Schema\Source\SchemaSDLLoaderInterface;

final readonly class SchemaCompiler
{
    protected Schema $schema;
    private SchemaSDLLoaderInterface $sdlLoader;
    private SchemaDocumentLoaderInterface $documentLoader;
    private IntentCompiler $intentCompiler;
    private FieldResolverCompiler $fieldResolverCompiler;

    public function __construct(SchemaSDLLoaderInterface $sdlLoader, SchemaDocumentLoaderInterface $documentLoader, IntentCompiler $intentCompiler, FieldResolverCompiler $fieldResolverCompiler) {
        $this->sdlLoader = $sdlLoader;
        $this->documentLoader = $documentLoader;
        $this->intentCompiler = $intentCompiler;
        $this->fieldResolverCompiler = $fieldResolverCompiler;
    }

    public function schema(): Schema
    {
        return $this->schema ??= $this->compile();
    }

    public function compile(): Schema
    {
        $sdl = $this->sdlLoader->load();

        $schema   = BuildSchema::build($sdl);
        $document = $this->documentLoader->load();

        $intents = $this->intentCompiler->compile($document);
        $this->fieldResolverCompiler->compile($schema, $intents);

        return $schema;
    }
}
