<?php

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\TypeDefinitionNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\DocumentNode;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\Intent\Intent;

final readonly class TypeVisitor
{
    public function __construct(
        private FieldVisitor   $fieldVisitor,
        private IntentRegistry $intentRegistry
    ) {}

    public function visitType(
        TypeDefinitionNode $typeNode,
        array $inputTypes,
        DocumentNode $document
    ): void {
        $typeName = $typeNode->name->value;

        // 1️⃣ Collect type directives ONCE
        $typeDirectives = $this->fieldVisitor->collectTypeDirectives($typeNode);

        // 2️⃣ 🔥 APPLY TYPE-LEVEL SCHEMA MUTATIONS FIRST
        foreach ($typeDirectives as $directive) {
            if ($directive instanceof FieldArgumentDirectiveInterface) {
                foreach ($typeNode->fields as $fieldNode) {
                    if (!$fieldNode instanceof FieldDefinitionNode) {
                        continue;
                    }

                    $existingArgs = [];
                    foreach ($fieldNode->arguments as $argNode) {
                        $existingArgs[$argNode->name->value] = true;
                    }

                    $this->fieldVisitor->injectDirectiveArguments(
                        $fieldNode,
                        $directive,
                        $directive->directiveNode,
                        $existingArgs
                    );
                }
            }
        }

        // 3️⃣ NOW build intents against the mutated schema
        foreach ($typeNode->fields as $fieldNode) {
            if (!$fieldNode instanceof FieldDefinitionNode) {
                continue;
            }

            $intent = new Intent($typeName, $fieldNode->name->value);

            $this->fieldVisitor->applyTypeDirectivesToIntent(
                $intent,
                $typeDirectives
            );

            $this->fieldVisitor->visitField(
                $intent,
                $fieldNode,
                $inputTypes,
                $document
            );

            $this->intentRegistry->add($intent);
        }
    }

    public function getIntentRegistry(): IntentRegistry
    {
        return $this->intentRegistry;
    }
}
