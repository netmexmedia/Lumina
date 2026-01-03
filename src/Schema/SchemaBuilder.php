<?php

namespace Netmex\Lumina\Schema;

use GraphQL\Error\Error;
use GraphQL\Error\SyntaxError;
use GraphQL\Language\Parser;
use GraphQL\Type\Schema;
use GraphQL\Utils\BuildSchema;
use Netmex\Lumina\Directive\DirectiveMetadataBuilder;
use Netmex\Lumina\Directive\DirectiveMetadataRegistry;
use Netmex\Lumina\SchemaBuilderInterface;

final readonly class SchemaBuilder implements SchemaBuilderInterface
{
    public function __construct(
        private SchemaSourceRegistry $registry,
        private DirectiveMetadataBuilder $directiveBuilder,
        private DirectiveMetadataRegistry $directiveRegistry
    ) {}

    public function build(): Schema
    {
        $sdl = '';

        foreach ($this->registry->all() as $source) {
            $sdl .= "\n" . $source->load();
        }

        $ast = Parser::parse($sdl);
        $this->directiveBuilder->build($ast, $this->directiveRegistry);

        // TODO: implment propper error handling
        return BuildSchema::build($sdl);
    }
}
