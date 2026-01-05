<?php

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Netmex\Lumina\Directives\DirectiveRegistry;
use Netmex\Lumina\Intent\Factory\IntentFactory;
use Netmex\Lumina\Intent\IntentRegistry;

final readonly class IntentCompiler
{
    private DirectiveRegistry $directives;
    private IntentFactory $intentFactory;

    public function __construct(DirectiveRegistry $directives, IntentFactory $intentFactory) {
        $this->directives = $directives;
        $this->intentFactory = $intentFactory;
    }

    public function compile(DocumentNode $document): IntentRegistry
    {
        $registry = new IntentRegistry();

        foreach ($document->definitions as $definition) {
            if (!$definition instanceof TypeDefinitionNode) {
                continue;
            }

            $typeName = $definition->name->value;

            foreach ($definition->fields as $field) {
                $intent = $this->intentFactory->create(
                    $typeName,
                    $field->name->value
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