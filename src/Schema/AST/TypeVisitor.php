<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\DocumentNode;
use Netmex\Lumina\Contracts\IntentFactoryInterface;
use Netmex\Lumina\Intent\IntentRegistry;

final class TypeVisitor
{
    private FieldVisitorAbstract $fieldVisitor;
    private IntentRegistry $intentRegistry;
    private IntentFactoryInterface $intentFactory;

    public function __construct(FieldVisitorAbstract $fieldVisitor, IntentRegistry $intentRegistry, IntentFactoryInterface $intentFactory)
    {
        $this->fieldVisitor = $fieldVisitor;
        $this->intentRegistry = $intentRegistry;
        $this->intentFactory = $intentFactory;
    }

    public function visitType(TypeDefinitionNode $typeNode, array $inputTypes, DocumentNode $document): void
    {
        $typeName = $typeNode->name->value;

        $typeDirectives = $this->fieldVisitor->collectTypeDirectives($typeNode);

        foreach ($typeNode->fields as $fieldNode) {
            if (!$fieldNode instanceof FieldDefinitionNode) {
                continue;
            }

            $intent = $this->intentFactory->create($typeName, $fieldNode->name->value, $typeDirectives);

            $this->fieldVisitor->applyTypeDirectivesToIntent($intent, $typeDirectives);
            $this->fieldVisitor->visitField($intent, $fieldNode, $inputTypes, $document);

            $this->intentRegistry->add($intent);
        }
    }

    public function getIntentRegistry(): IntentRegistry
    {
        return $this->intentRegistry;
    }
}
