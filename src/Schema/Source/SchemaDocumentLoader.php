<?php

namespace Netmex\Lumina\Schema\Source;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\Parser;

final readonly class SchemaDocumentLoader implements SchemaDocumentLoaderInterface
{
    public function __construct(
        private SchemaSDLLoaderInterface $sdlLoader
    ) {}

    public function load(): DocumentNode
    {
        return Parser::parse($this->sdlLoader->load());
    }
}