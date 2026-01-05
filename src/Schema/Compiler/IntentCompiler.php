<?php

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Netmex\Lumina\Directives\DirectiveContext;
use Netmex\Lumina\Directives\DirectiveRegistry;
use Netmex\Lumina\Intent\Builder\IntentBuilder;
use Netmex\Lumina\Intent\Builder\IntentBuilderInterface;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Intent\IntentRegistry;

final readonly class IntentCompiler
{
    private DirectiveRegistry $directives;

    public function __construct(DirectiveRegistry $directives) {
        $this->directives = $directives;
    }

    public function compile(DocumentNode $document): IntentRegistry
    {
        $registry = new IntentRegistry();

        foreach ($document->definitions as $definition) {
            if (!$definition instanceof TypeDefinitionNode) {
                continue;
            }

            foreach ($definition->fields as $field) {
                $intent = $this->compileField($definition, $field);
                $registry->add($intent);
            }
        }

        return $registry;
    }

    private function compileField(TypeDefinitionNode $typeDefinitionNode, FieldDefinitionNode $fieldDefinitionNode): Intent
    {
        $typeName = $typeDefinitionNode->name->value;
        $fieldName = $fieldDefinitionNode->name->value;

        $builder = new IntentBuilder();
        $builder->type($typeName)->field($fieldName);

        $this->applyFieldDirectives($builder, $fieldDefinitionNode);
        $this->applyArgumentDirectives($builder, $fieldDefinitionNode);

        return $builder->build();
    }

    private function applyFieldDirectives(IntentBuilderInterface $builder, FieldDefinitionNode $fieldDefinitionNode): void
    {
        $context = new DirectiveContext();
        $context->setParentNode($fieldDefinitionNode);
        $context->setName($fieldDefinitionNode->name->value);

        foreach ($fieldDefinitionNode->directives as $directiveNode) {
            $this->directives->field($directiveNode->name->value)?->intent($builder, $context->withNode($directiveNode));
        }
    }

    private function applyArgumentDirectives(IntentBuilderInterface $builder, FieldDefinitionNode $fieldDefinitionNode): void
    {
        $context = new DirectiveContext();
        $context->setParentNode($fieldDefinitionNode);

        foreach ($fieldDefinitionNode->arguments as $inputValueDefinitionNode) {
            foreach ($inputValueDefinitionNode->directives as $directiveNode) {
                $this->directives->argument($directiveNode->name->value)?->intent(
                    $builder,
                    $context
                        ->withNode($directiveNode)
                        ->withName($inputValueDefinitionNode->name->value)
                );
            }
        }
    }
}