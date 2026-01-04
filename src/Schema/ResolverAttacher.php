<?php

namespace Netmex\Lumina\Schema;

use GraphQL\Type\Schema;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\Schema\Execution\ExecutorRegistry;

final readonly class ResolverAttacher
{
    public function __construct(
        private ExecutorRegistry $executorRegistry
    ) {}

    public function attach(Schema $schema, IntentRegistry $intents): void
    {
        $queryType = $schema->getQueryType();

        foreach ($intents->all() as $intent) {
            if ($intent->type !== 'Query') {
                continue;
            }

            $field = $queryType->getField($intent->field);

            $field->resolveFn = function ($root, $args, $context) use ($intent) {
                return $this->executorRegistry
                    ->forStrategy($intent->strategy)
                    ->execute($intent, $args, $context);
            };
        }
    }
}