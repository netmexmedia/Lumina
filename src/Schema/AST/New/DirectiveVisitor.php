<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\AST\New;

use GraphQL\Language\AST\NodeList;
use Netmex\Lumina\Contracts\DirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Schema\Factory\DirectiveFactory;

final class DirectiveVisitor
{
    private array $inputTypes = [];
    private array $objectTypes = [];
    private DirectiveFactory $directiveFactory;

    public function __construct(DirectiveFactory $directiveFactory)
    {
        $this->directiveFactory = $directiveFactory;
    }

    public function setInputTypes(array $inputTypes): void
    {
        $this->inputTypes = $inputTypes;
    }

    public function setObjectTypes(array $objectTypes): void
    {
        $this->objectTypes = $objectTypes;
    }

    public function visitArguments(Intent $parentIntent, array|NodeList $arguments): void
    {
        foreach ($arguments as $argNode) {
            $namedType = $this->getNamedType($argNode->type);
            $hasDirective = !empty($argNode->directives);

            $argIntent = null;

            if ($hasDirective) {
                $argIntent = new Intent($parentIntent->typeName, $argNode->name->value);
                $argIntent->setParent($parentIntent);
                $parentIntent->addChild($argIntent);

                foreach ($argNode->directives as $directiveNode) {
                    $directive = $this->instantiateDirective($directiveNode, $argNode);
                    $argIntent->addModifier($directiveNode->name->value, $directive);
                }
            }

            if (isset($this->inputTypes[$namedType])) {
                $this->visitArguments($argIntent ?? $parentIntent, $this->inputTypes[$namedType]->fields);
            }
        }
    }

    public function visitReturnType(Intent $parentIntent, array|NodeList $fields): void
    {
        foreach ($fields as $fieldNode) {
            $namedType = $this->getNamedType($fieldNode->type);

            $hasDirective = !empty($fieldNode->directives);

            $childIntent = null;

            if ($hasDirective) {
                foreach ($fieldNode->directives as $directiveNode) {
                    $directive = $this->instantiateDirective($directiveNode, $fieldNode);

                    if ($directive instanceof FieldResolverInterface) {
                        // Resolver goes to parent
                        $parentIntent->setResolver($directive);
                    } else {
                        // Modifier directive → create child intent if not yet created
                        if (!$childIntent) {
                            $childIntent = new Intent($parentIntent->typeName, $fieldNode->name->value);
                            $childIntent->setParent($parentIntent);
                            $parentIntent->addChild($childIntent);
                        }
                        $childIntent->addModifier($fieldNode->name->value, $directive);
                    }
                }
            }

            // Always recurse into object types
            if (isset($this->objectTypes[$namedType])) {
                $recurseParent = $childIntent ?? $parentIntent;
                $this->visitReturnType($recurseParent, $this->objectTypes[$namedType]->fields);
            }
        }
    }


    private function getNamedType($typeNode): string
    {
        if (property_exists($typeNode, 'name') && $typeNode->name) {
            return $typeNode->name->value;
        }
        if (property_exists($typeNode, 'type') && $typeNode->type) {
            return $this->getNamedType($typeNode->type);
        }
        throw new \RuntimeException('Cannot resolve named type');
    }

    private function instantiateDirective($directiveNode, $definitionNode): DirectiveInterface
    {
        return $this->directiveFactory->create($directiveNode, $definitionNode);
    }
}
