<?php

namespace Netmex\Lumina\Schema\Compiler;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\TypeDefinitionNode;
use Netmex\Lumina\Directives\DirectiveRegistry;
use Netmex\Lumina\Intent\Builder\IntentBuilder;
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
            if ($definition instanceof TypeDefinitionNode) {
                $this->compileType($definition, $registry);
            }
        }

        return $registry;
    }

    private function compileType(TypeDefinitionNode $type, IntentRegistry $registry): void
    {
        foreach ($type->fields as $field) {

            $this->compileField($type, $field);
            $registry->add($this->compileField($type, $field));
        }
    }

    private function compileField(TypeDefinitionNode $type, $field): Intent
    {
        $builder = new IntentBuilder();

        $builder->type($type->name->value)->field($field->name->value);

        $this->applyFieldDirectives($builder, $field);
        $this->applyArgumentDirectives($builder, $field);

        return $builder->build();
    }

    private function applyFieldDirectives(IntentBuilder $builder, $field): void
    {
        foreach ($field->directives as $directiveNode) {
            $this->directives->field($directiveNode->name->value)?->intent($builder, $field);
        }
    }

    private function applyArgumentDirectives(IntentBuilder $builder, $field): void
    {
        foreach ($field->arguments as $argument) {
            foreach ($argument->directives as $directiveNode) {
                $this->directives->argument($directiveNode->name->value)?->intent($builder, $argument);
            }
        }
    }
}