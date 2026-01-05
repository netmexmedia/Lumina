<?php

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Netmex\Lumina\Directives\DirectiveRegistry;
use Netmex\Lumina\Intent\IntentRegistry;
use Netmex\Lumina\Intent\QueryIntent;

final readonly class IntentCompiler
{
    public function __construct(
        private DirectiveRegistry $directives
    ) {}

    public function compile(DocumentNode $document): IntentRegistry
    {
        $registry = new IntentRegistry();

        foreach ($document->definitions as $definition) {
            if (!$definition instanceof TypeDefinitionNode) {
                continue;
            }

            $typeName = $definition->name->value;

            foreach ($definition->fields as $field) {
                $intent = new QueryIntent(
                    type: $typeName,
                    field: $field->name->value
                );

                // 1️⃣ FIELD directives
                foreach ($field->directives as $directiveNode) {
                    $this->directives->field($directiveNode->name->value)?->applyToField($intent, $field, $definition);
                }

                // 2️⃣ ARGUMENT directives
                foreach ($field->arguments as $argument) {
                    foreach ($argument->directives as $directiveNode) {
                        $this->directives->argument($directiveNode->name->value)?->applyToArgument($intent, $argument, $field, $definition);
                    }
                }

                $registry->add($intent);
            }
        }

        return $registry;
    }
}