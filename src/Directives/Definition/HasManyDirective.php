<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;

class HasManyDirective extends AbstractDirective implements FieldResolverInterface
{
    public static function name(): string
    {
        return 'hasMany';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @hasMany(
                column: String,
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        $shortName = $this->modelClass();
        $fqcn = $this->resolveEntityFQCN($shortName);

        if (!$fqcn) {
            throw new \RuntimeException("Cannot resolve entity FQCN for $shortName");
        }

        return function ($root, array $arguments, $context, $info) use ($fqcn) {

            $manySelection = null;
            foreach ($info->fieldNodes[0]->selectionSet->selections ?? [] as $selection) {
                if ($selection->name->value === 'many') {
                    $manySelection = $selection;
                    break;
                }
            }

            $selectedFields = ['id'];
            if ($manySelection && $manySelection->selectionSet) {
                $selectedFields = [];
                foreach ($manySelection->selectionSet->selections as $child) {
                    $selectedFields[] = $child->name->value;
                }
            }

            $em = $context->entityManager;
            $qb = $em->getRepository($fqcn)->createQueryBuilder('c')
                ->where('c.test = :parentId')
                ->setParameter('parentId', $root['id']);

            // Only select requested columns
            $qb->select(array_map(fn($f) => "c.$f", $selectedFields));

            $children = $qb->getQuery()->getArrayResult();

            return $children;
        };
    }
}
