<?php

declare(strict_types=1);

namespace Netmex\Lumina\Http\ArgumentResolver;

use Netmex\Lumina\Http\Parser\GraphQLRequestParser;
use Netmex\Lumina\Http\Request\GraphQLRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

readonly class GraphQLRequestValueResolver implements ValueResolverInterface
{
    public function __construct(
        private GraphQLRequestParser $parser
    ) {}

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === GraphQLRequest::class;
    }

public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $this->parser->parse($request);
    }
}