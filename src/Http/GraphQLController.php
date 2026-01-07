<?php

declare(strict_types=1);

namespace Netmex\Lumina\Http;

use GraphQL\Error\DebugFlag;
use Netmex\Lumina\Http\Request\GraphQLRequest;
use Netmex\Lumina\Kernel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class GraphQLController extends AbstractController
{
    public function __construct(private readonly Kernel $kernel) {}

    public function __invoke(GraphQLRequest $request): JsonResponse
    {
        $isDev = $this->getParameter('kernel.environment') === 'dev';
        return new JsonResponse(
            $this->kernel->execute($request)->toArray(
                $isDev ? DebugFlag::INCLUDE_DEBUG_MESSAGE | DebugFlag::INCLUDE_TRACE : DebugFlag::NONE
            )
        );
    }
}
