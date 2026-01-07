<?php

declare(strict_types=1);

namespace Netmex\Lumina\Http\Parser;

use GraphQL\Language\Parser;
use Netmex\Lumina\Http\Request\GraphQLRequest;
use Symfony\Component\HttpFoundation\Request;

final class GraphQLRequestParser
{
    public function parse(Request $request): GraphQLRequest
    {
        $payload = json_decode(
            $request->getContent(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $query = Parser::parse($payload['query'], [
            'noLocation' => true,
        ]);

        return new GraphQLRequest(
            query: $query,
            variables: $payload['variables'] ?? [],
            operationName: $payload['operationName'] ?? null,
        );
    }
}