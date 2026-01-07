<?php

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Netmex\Lumina\Contracts\SchemaSourceInterface;

final readonly class SchemaCompiler
{
    protected Schema $schema;

    private SchemaSourceInterface $sourceLoader;
    private IntentCompiler $intentCompiler;
    private FieldResolverCompiler $fieldResolverCompiler;

    public function __construct(SchemaSourceInterface $sourceLoader,IntentCompiler $intentCompiler, FieldResolverCompiler $fieldResolverCompiler) {
        $this->sourceLoader = $sourceLoader;
        $this->intentCompiler = $intentCompiler;
        $this->fieldResolverCompiler = $fieldResolverCompiler;
    }

    public function schema(): Schema
    {
        return $this->schema ??= $this->compile();
    }

    public function compile(): Schema
    {
        $sdl = $this->sourceLoader->load();

        $schema   = BuildSchema::build($sdl);
        $document =  Parser::parse($sdl);

        $this->intentCompiler->compile($document);
        $this->fieldResolverCompiler->compile($schema);

        return $schema;
    }
}
