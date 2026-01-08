<?php

namespace Netmex\Lumina\Directives\Definition;

use Doctrine\ORM\QueryBuilder;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\NamedTypeNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\NonNullTypeNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Netmex\Lumina\Context\Context;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\FieldTypeModifierInterface;
use Netmex\Lumina\Contracts\FieldValueInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class DeleteDirective extends AbstractDirective implements FieldResolverInterface, FieldArgumentDirectiveInterface, FieldTypeModifierInterface
{
    private NormalizerInterface $normalizer;

    public function __construct(SerializerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public static function name(): string
    {
        return 'delete';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @delete(
                field: String = id
            ) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | FIELD_DEFINITION
        GRAPHQL;
    }

    public function argumentNodes(): array
    {
        return [
            new InputValueDefinitionNode([
                'name' => new NameNode(['value' => 'id']),
                'type' => new NonNullTypeNode([
                    'type' => new NamedTypeNode([
                        'name' => new NameNode(['value' => 'ID']),
                    ]),
                ]),
                'directives' => new NodeList([]),
                'description' => null,
                'defaultValue' => null,
            ])
        ];
    }

    public function resolveField(FieldValueInterface $value, ?QueryBuilder $queryBuilder): callable
    {
        $entityManager = $queryBuilder->getEntityManager();

        $baseEntity = $this->getModel();
        $model = $this->resolveEntityFQCN($baseEntity, $entityManager);
        $normalizer = $this->normalizer;

        return static function (mixed $root, array $arguments, Context $context, ResolveInfo $info) use ($entityManager, $model, $normalizer)
        {
            $entity = $entityManager->find($model, $arguments['id']);
            $deletedEntity = clone $entity;

            $entityManager->remove($entity);
            $entityManager->flush();

            return [
                'data' => $normalizer->normalize($deletedEntity),
                'status' => 'DELETED',
                'timestamp' => (new \DateTime())->format(DATE_ATOM),
            ];
        };
    }

    public function modifyFieldType(FieldDefinitionNode $fieldNode, DocumentNode $document): void
    {
        $deleteTypeName = $this->getOrCreateDeleteType($fieldNode, $document);

        $fieldNode->type = new NamedTypeNode([
            'name' => new NameNode(['value' => $deleteTypeName])
        ]);
    }

    public function getOrCreateDeleteType(FieldDefinitionNode $fieldNode, DocumentNode $document): string
    {
        $originalType = $fieldNode->type;
        $originalTypeName = $this->getNamedTypeName($fieldNode->type);
        $deleteTypeName = $originalTypeName . 'DeletePayload';

        foreach ($document->definitions as $definition) {
            if ($definition instanceof ObjectTypeDefinitionNode && $definition->name->value === $deleteTypeName) {
                return $deleteTypeName;
            }
        }

        // Create the delete type
        $deleteTypeDefinition = new ObjectTypeDefinitionNode([
            'name' => new NameNode(['value' => $deleteTypeName]),
            'fields' => new NodeList([
                new FieldDefinitionNode([
                    'name' => new NameNode(['value' => 'data']),
                    'type' => $originalType,
                    'directives' => new NodeList([]),
                    'arguments' => new NodeList([]),
                ]),
                new FieldDefinitionNode([
                    'name' => new NameNode(['value' => 'status']),
                    'type' => new NamedTypeNode([
                        'name' => new NameNode(['value' => 'String'])
                    ]),
                    'directives' => new NodeList([]),
                    'arguments' => new NodeList([]),
                ]),
                new FieldDefinitionNode([
                    'name' => new NameNode(['value' => 'timestamp']),
                    'type' => new NamedTypeNode([
                        'name' => new NameNode(['value' => 'String'])
                    ]),
                    'directives' => new NodeList([]),
                    'arguments' => new NodeList([]),
                ]),
            ]),
            'directives' => new NodeList([]),
            'interfaces' => new NodeList([]),
        ]);

        // Add the new delete type definition to the document
        $document->definitions[] = $deleteTypeDefinition;

        return $deleteTypeName;
    }
}