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
                $argIntent->setMetaData(new IntentMetaData());
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
    public function visitReturnType(Intent $parentIntent, array|NodeList $fields, $document = null, $rootFieldNode = null): void
    {
        foreach ($fields as $fieldNode) {
            $fieldName = $fieldNode->name->value;
            $namedType = $this->getNamedType($fieldNode->type);

            // --- Step 0: Determine child intent
            $isRootField = $parentIntent->fieldName === $fieldName;
            $childIntent = $isRootField
                ? $parentIntent
                : ($parentIntent->getChildByName($fieldName) ?? new Intent($parentIntent->typeName, $fieldName));

            // Ensure metaData exists
            if (!$childIntent->metaData) {
                $childIntent->setMetaData(new IntentMetaData());
            }

            // Add field to its own metaData
            $childIntent->getMetaData()->setFields([$fieldName]);

            // If this is a new child, attach to parent
            if (!$isRootField && !$childIntent->getParent()) {
                $childIntent->setParent($parentIntent);
                $parentIntent->addChild($childIntent);
            }

            $rootNodeForInjection = $fieldNode;

            // --- Step 1: Process directives
            if (!empty($fieldNode->directives)) {
                foreach ($fieldNode->directives as $directiveNode) {
                    $directive = $this->instantiateDirective($directiveNode, $fieldNode);

                    if ($directive instanceof FieldResolverInterface) {
                        // Resolver lives on child
                        $childIntent->setResolver($directive);
                        $directive->setModel($namedType);
                        $childIntent->getMetaData()->setStrategy($directive->name());
                        $childIntent->getMetaData()->setModel($directive->modelClass());
                        // Attach child metaData to parent
                        if ($childIntent->getParent()) {
                            $childIntent->getParent()->getMetaData()->addChild($childIntent->getMetaData());
                        }

                        if ($document && method_exists($directive, 'modifyFieldType')) {
                            $directive->modifyFieldType($fieldNode, $document);
                        }

                        // Inject argument/input nodes for resolver
                        if ($directive instanceof FieldArgumentDirectiveInterface && $document) {
                            $this->injectDirectiveArguments($rootNodeForInjection, $directive, $directiveNode->name->value);
                        }
                        if ($directive instanceof FieldInputDirectiveInterface && $document) {
                            $this->injectDirectiveArguments($rootNodeForInjection, $directive, $directiveNode->name->value);
                        }
                    } else {
                        // Modifiers live on child intent
                        $childIntent->addModifier($directiveNode->name->value, $directive);

                        if ($document && method_exists($directive, 'modifyFieldType')) {
                            $directive->modifyFieldType($fieldNode, $document);
                        }

                        if ($directive instanceof FieldArgumentDirectiveInterface && $document) {
                            $this->injectDirectiveArguments($rootNodeForInjection, $directive, $directiveNode->name->value);
                        }
                        if ($directive instanceof FieldInputDirectiveInterface && $document) {
                            $this->injectDirectiveArguments($rootNodeForInjection, $directive, $directiveNode->name->value);
                        }
                    }
                }
            }

            // --- Step 2: Recurse into nested object types
            if (isset($this->objectTypes[$namedType])) {
                $this->visitReturnType(
                    $childIntent,
                    $this->objectTypes[$namedType]->fields,
                    $document,
                    $rootNodeForInjection
                );
            }

            // --- Step 3: Merge children fields into this intent's metaData
            $columns = $childIntent->getMetaData()->getFields();
            foreach ($childIntent->getChildrenMetaData() as $childMetaArray) {
                foreach ($childMetaArray as $childMeta) {
                    $columns = array_merge($columns, $childMeta->getFields());
                }
            }
            $columns = array_unique($columns);
            $childIntent->getMetaData()->setFields($columns);
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
