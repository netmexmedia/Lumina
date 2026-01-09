<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\DirectiveFactoryInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Netmex\Lumina\Intent\Intent;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class ArgumentDirectiveVisitor extends AbstractASTDirectiveVisitor
{
    private array $inputTypes = [];
    private DirectiveRegistry $directiveRegistry;
    private ServiceLocator $directiveLocator;
    private FieldDefinitionNode $fieldDefinitionNode;

    public function __construct(DirectiveFactoryInterface $directiveFactory, DirectiveRegistry $directiveRegistry, ServiceLocator $directiveLocator)
    {
        parent::__construct($directiveFactory);

        $this->directiveRegistry = $directiveRegistry;
        $this->directiveLocator = $directiveLocator;
    }

    public function visitArgument(Intent $intent, InputValueDefinitionNode $argNode, FieldDefinitionNode $fieldNode, array $inputTypes, string $parentPath = ''): void
    {
        $this->inputTypes = $inputTypes;
        $this->fieldDefinitionNode = $fieldNode;

        $argPath = $this->buildArgumentPath($argNode, $parentPath);
        $this->applyArgumentNodeDirectives($intent, $argNode, $argPath);

        $namedType = $this->getNamedType($argNode->type);
        if (isset($inputTypes[$namedType])) {
            foreach ($inputTypes[$namedType]->fields as $nestedArg) {
                $this->visitArgument($intent, $nestedArg, $fieldNode, $inputTypes, $argPath);
            }
        }
    }

    public function traverseReturnTypeFields(Intent $intent, string $typeName, string $prefix = ''): void
    {
        $typeDef = $this->getObjectTypeDefinition($typeName);
        if (!$typeDef) {
            return;
        }

        foreach ($typeDef->fields as $fieldNode) {
            $fieldPath = $prefix === '' ? $fieldNode->name->value : $prefix . '.' . $fieldNode->name->value;

            foreach ($fieldNode->directives as $directiveNode) {
                $directive = $this->instantiateDirectiveFromNode($directiveNode, $fieldNode);

                if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                    $intent->addArgumentDirective($fieldPath, $directive);
                }
            }

            $nestedType = $this->getNamedType($fieldNode->type);
            $this->traverseReturnTypeFields($intent, $nestedType, $fieldPath);
        }
    }

    public function getDirectiveLocator(): ServiceLocator
    {
        return $this->directiveLocator;
    }

    public function getDirectiveRegistry(): DirectiveRegistry
    {
        return $this->directiveRegistry;
    }

    private function getObjectTypeDefinition(string $typeName): ?ObjectTypeDefinitionNode
    {
        return $this->inputTypes[$typeName] ?? null;
    }

    private function buildArgumentPath(InputValueDefinitionNode $argNode, string $parentPath): string
    {
        return $parentPath === '' ? $argNode->name->value : $parentPath . '.' . $argNode->name->value;
    }

    private function applyArgumentNodeDirectives(Intent $intent, InputValueDefinitionNode $argNode, string $argPath): void
    {
        foreach ($argNode->directives as $directiveNode) {
            $directive = $this->instantiateDirectiveFromNode($directiveNode, $argNode);

            if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                $intent->addArgumentDirective($argPath, $directive);
            }

            if ($directive instanceof FieldArgumentDirectiveInterface) {
                $existingArgs = [];
                $this->injectDirectiveArguments($this->fieldDefinitionNode, $directive, $argNode, $existingArgs);
            }
        }
    }
}
