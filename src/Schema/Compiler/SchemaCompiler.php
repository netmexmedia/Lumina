<?php

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Netmex\Lumina\Schema\Runtime\ResolverAttacher;
use Netmex\Lumina\Schema\Source\SchemaDocumentLoaderInterface;
use Netmex\Lumina\Schema\Source\SchemaSDLLoaderInterface;

final readonly class SchemaCompiler
{
    protected Schema $schema;

    public function __construct(
        private SchemaSDLLoaderInterface      $sdlLoader,
        private SchemaDocumentLoaderInterface $documentLoader,
        private IntentCompiler                $compiler,
        private ResolverAttacher              $resolver,
    ) {}

    public function schema(): Schema
    {
        return $this->schema ??= $this->compile();
    }

    public function compile(): Schema
    {
        $sdl = $this->sdlLoader->load();

        $schema   = BuildSchema::build($sdl);
        $document = $this->documentLoader->load();

        $intents = $this->compiler->build($document);
        $this->resolver->attach($schema, $intents);

        return $schema;
    }
}
