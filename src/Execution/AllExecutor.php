<?php

namespace Netmex\Lumina\Execution;

use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Intent\QueryIntent;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class AllExecutor implements QueryExecutorInterface
{
    public function __construct(
        private SerializerInterface $serializer
    ) {}

    public function strategy(): string
    {
        return QueryIntent::STRATEGY_ALL;
    }

    public function execute(QueryIntent $intent, array $args, Context $context): array
    {
        $repository = $context->entityManager->getRepository($intent->model);

        $qb = $repository->createQueryBuilder('e');

        foreach ($intent->filters as $filter) {
            $filter->apply($qb, $args);
        }

        return $this->serializer->normalize(
            $qb->getQuery()->getResult()
        );
    }
}