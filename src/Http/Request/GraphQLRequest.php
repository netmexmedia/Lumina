<?php

declare(strict_types=1);

namespace Netmex\Lumina\Http\Request;

use GraphQL\Language\AST\DocumentNode;

final readonly class GraphQLRequest
{
    public function __construct(
        public DocumentNode $query,
        public array $variables = [],
        public ?string $operationName = null,
    ) {}
}