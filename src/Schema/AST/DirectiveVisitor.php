<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\NodeList;
use Netmex\Lumina\Contracts\DirectiveInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldInputDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Intent\IntentMetaData;
use Netmex\Lumina\Schema\Directive\DirectiveProcessor;
use Netmex\Lumina\Schema\Directive\DirectiveTypeRegistryVisitor;
use Netmex\Lumina\Schema\Factory\DirectiveFactory;
use Netmex\Lumina\Schema\Helpers\ASTTypeHelper;
use Netmex\Lumina\Schema\Helpers\DirectiveHelper;
use Netmex\Lumina\Schema\Mutator\IntentMutator;

final class DirectiveVisitor
{
    private DirectiveHelper $directiveHelper;
    private DirectiveProcessor $processor;
    private IntentMutator $mutator;
    public DirectiveTypeRegistryVisitor $types;

    public function __construct(DirectiveHelper $directiveHelper, DirectiveProcessor $processor, IntentMutator $mutator, DirectiveTypeRegistryVisitor $types)
    {
        $this->directiveHelper = $directiveHelper;
        $this->processor = $processor;
        $this->mutator = $mutator;
        $this->types = $types;
    }

    public function visitArguments(Intent $parentIntent, array|NodeList $arguments, $parentFieldNode = null, $document = null): void
    {
        foreach ($arguments as $argNode) {
            $fieldName = $argNode->name->value;
            $namedType = ASTTypeHelper::getNamedType($argNode->type);
            $hasDirective = !empty($argNode->directives);

            $argIntent = $this->mutator->getOrCreateIntent($parentIntent, $fieldName, false);

            if ($hasDirective) {
                foreach ($argNode->directives as $directiveNode) {
                    $directive = $this->directiveHelper->instantiateDirective($directiveNode, $argNode);
                    $this->processor->process(
                        $directive,
                        $argIntent,
                        $argNode,
                        $document
                    );
                }
            }

            if ($this->types->isInputType($namedType)) {
                $this->visitArguments(
                    $argIntent,
                    $this->types->getInputType($namedType)->fields,
                    $parentFieldNode,
                    $document
                );
            }

            $this->mutator->mergeChildFields($argIntent);
        }
    }

    public function visitReturnType(Intent $parentIntent, array|NodeList $fields, $document = null, array $typeStack = []): void
    {
        foreach ($fields as $fieldNode) {
            $fieldName = $fieldNode->name->value;
            $namedType = ASTTypeHelper::getNamedType($fieldNode->type);

            if ($this->types->isObjectType($namedType) && in_array($namedType, $typeStack, true)) {
                continue;
            }

            $isRootField = $parentIntent->fieldName === $fieldName;
            $childIntent = $this->mutator->getOrCreateIntent($parentIntent, $fieldName, $isRootField);

            if (!empty($fieldNode->directives)) {
                foreach ($fieldNode->directives as $directiveNode) {
                    $directive = $this->directiveHelper->instantiateDirective($directiveNode, $fieldNode);
                    $this->processor->process(
                        $directive,
                        $childIntent,
                        $fieldNode,
                        $document
                    );
                }
            }

            if ($this->types->isObjectType($namedType)) {
                $newTypeStack = array_merge($typeStack, [$namedType]);
                $this->visitReturnType(
                    $childIntent,
                    $this->types->getObjectType($namedType)->fields,
                    $document,
                    $newTypeStack
                );
            }

            $this->mutator->mergeChildFields($childIntent);
        }
    }
}
