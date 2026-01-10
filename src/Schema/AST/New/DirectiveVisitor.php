<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\AST\New;

use GraphQL\Language\AST\NodeList;
use Netmex\Lumina\Contracts\DirectiveInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
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

    /**
     * Recursively visit argument nodes and apply directives, including AST mutations.
     */
    public function visitArguments(Intent $parentIntent, array|NodeList $arguments, $parentFieldNode = null, $document = null): void {
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

                    // Apply AST mutations for argument directives
                    if ($directive instanceof FieldArgumentDirectiveInterface && $parentFieldNode && $document) {
                        $this->injectDirectiveArguments($parentFieldNode, $directive, $directiveNode->name->value);
                    }
                }
            }

            // Recurse into nested input types
            if (isset($this->inputTypes[$namedType])) {
                $this->visitArguments(
                    $argIntent ?? $parentIntent,
                    $this->inputTypes[$namedType]->fields,
                    $parentFieldNode,
                    $document
                );
            }
        }
    }

    /**
     * Recursively visit return type fields and apply directives, including AST mutations.
     */
    public function visitReturnType(Intent $parentIntent, array|NodeList $fields, $document = null): void {
        foreach ($fields as $fieldNode) {
            $namedType = $this->getNamedType($fieldNode->type);
            $hasDirective = !empty($fieldNode->directives);

            $childIntent = null;

            if ($hasDirective) {
                foreach ($fieldNode->directives as $directiveNode) {
                    $directive = $this->instantiateDirective($directiveNode, $fieldNode);

                    if ($directive instanceof FieldArgumentDirectiveInterface && $document) {
                        $this->injectDirectiveArguments($fieldNode, $directive, $directiveNode->name->value);
                    }

                    if ($directive instanceof FieldResolverInterface) {
                        $directive->setModel($this->getNamedType($fieldNode->type));
                        $parentIntent->setResolver($directive);

                        if ($document && method_exists($directive, 'modifyFieldType')) {
                            $directive->modifyFieldType($fieldNode, $document);
                        }
                    } else {
                        if (!$childIntent) {
                            $childIntent = new Intent($parentIntent->typeName, $fieldNode->name->value);
                            $childIntent->setParent($parentIntent);
                            $parentIntent->addChild($childIntent);
                        }

                        $childIntent->addModifier($fieldNode->name->value, $directive);

                        // Apply AST mutation if directive supports it
                        if ($document && method_exists($directive, 'modifyFieldType')) {
                            $directive->modifyFieldType($fieldNode, $document);
                        }
                    }
                }
            }

            // Recurse into nested object types
            if (isset($this->objectTypes[$namedType])) {
                $this->visitReturnType(
                    $childIntent ?? $parentIntent,
                    $this->objectTypes[$namedType]->fields,
                    $document
                );
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

    private function injectDirectiveArguments($fieldNode, FieldArgumentDirectiveInterface $directive, string $directiveName): void
    {
        foreach ($directive->argumentNodes() as $argNode) {
            $name = $argNode->name->value;

            // Avoid duplicate arguments
            foreach ($fieldNode->arguments as $existingArg) {
                if ($existingArg->name->value === $name) {
                    continue 2;
                }
            }

            $fieldNode->arguments[] = $argNode;
        }
    }
}
