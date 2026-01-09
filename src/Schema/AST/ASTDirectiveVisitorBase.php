<?php

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\DocumentNode;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Intent\Intent;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;

abstract class ASTDirectiveVisitorBase
{
    abstract protected function getDirectiveLocator(): ServiceLocator;
    abstract protected function getDirectiveRegistry(): DirectiveRegistry;

    public function collectTypeDirectives($typeNode): array
    {
        $directives = [];
        foreach ($typeNode->directives as $directiveNode) {
            $directives[] = $this->instantiateDirective(
                $directiveNode->name->value,
                $typeNode,
                $directiveNode,
                $this->getDirectiveLocator(),
                $this->getDirectiveRegistry()
            );
        }
        return $directives;
    }

    public function applyTypeDirectivesToIntent(Intent $intent, array $typeDirectives): void
    {
        foreach ($typeDirectives as $directive) {
            $intent->applyTypeDirective($directive->name(), $directive);
        }
    }

    public function applyFieldDirectives(
        Intent $intent,
        FieldDefinitionNode $fieldNode,
        array &$existingArgs,
        DocumentNode $document
    ): void {
        foreach ($fieldNode->directives as $directiveNode) {
            $directive = $this->instantiateDirective(
                $directiveNode->name->value,
                $fieldNode,
                $directiveNode,
                $this->getDirectiveLocator(),
                $this->getDirectiveRegistry()
            );

            if ($directive instanceof FieldResolverInterface) {
                $directive->setModel($this->getNamedType($fieldNode->type));
                $intent->setResolver($directive);

                if (method_exists($directive, 'modifyFieldType')) {
                    $directive->modifyFieldType($fieldNode, $document);
                }
            }

            if ($directive instanceof ArgumentBuilderDirectiveInterface) {
                if ($directive instanceof FieldArgumentDirectiveInterface) {
                    foreach ($directive->argumentNodes() as $argNode) {
                        $intent->addArgumentDirective($argNode->name->value, $directive);
                    }
                } else {
                    $intent->addArgumentDirective($directiveNode->name->value, $directive);
                }
            }

            if ($directive instanceof FieldArgumentDirectiveInterface) {
                $this->injectDirectiveArguments(
                    $fieldNode,
                    $directive,
                    $directiveNode,
                    $existingArgs
                );
            }
        }
    }

    protected function instantiateDirective(
        string $name,
        object $definitionNode,
        object $directiveNode,
        ServiceLocator $locator,
        DirectiveRegistry $registry
    ): AbstractDirective {
        $serviceId = $registry->get($name);

        if ($serviceId === null) {
            throw new \RuntimeException(sprintf(
                'Directive "%s" used on %s is not registered. Make sure the directive exists in the DirectiveRegistry.',
                $name,
                $definitionNode::class
            ));
        }

        $directive = clone $locator->get($serviceId);

        if (!$directive instanceof AbstractDirective) {
            throw new \RuntimeException(sprintf(
                'Directive "%s" retrieved from the ServiceLocator is not an instance of AbstractDirective.',
                $name
            ));
        }

        $directive->directiveNode = $directiveNode;
        $directive->definitionNode = $definitionNode;

        return $directive;
    }

    protected function getNamedType(object $typeNode): string
    {
        if (property_exists($typeNode, 'name') && $typeNode->name !== null) {
            return $typeNode->name->value;
        }

        if (property_exists($typeNode, 'type') && $typeNode->type !== null) {
            return $this->getNamedType($typeNode->type);
        }

        throw new \RuntimeException('Cannot resolve named type from AST node');
    }

    public function injectDirectiveArguments(FieldDefinitionNode $fieldNode, FieldArgumentDirectiveInterface $directive, object $directiveNode, array &$existingArgs): void
    {
        foreach ($directive->argumentNodes() as $argNode) {
            $name = $argNode->name->value;

            if (isset($existingArgs[$name])) {
                throw new \RuntimeException(sprintf(
                    'Argument "%s" on field "%s" conflicts with system argument added by @%s',
                    $name, $fieldNode->name->value, $directiveNode->name->value
                ));
            }

            $fieldNode->arguments[] = $argNode;
            $existingArgs[$name] = true;
        }
    }
}
