<?php

namespace Netmex\Lumina\Controller;

use Netmex\Lumina\ExecutorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class GraphQLController
{
    private ExecutorInterface $executor;

    public function __construct(ExecutorInterface $executor) {
        $this->executor = $executor;
    }

    public function __invoke(Request $request): JsonResponse
    {
        // TODO : Add proper error handling
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return new JsonResponse(
            $this->executor->execute(
                $payload['query'] ?? '',
                $payload['variables'] ?? [],
                $payload['operationName'] ?? null
            )
        );
    }
}
