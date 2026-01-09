<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\AST;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use Netmex\Lumina\Contracts\ArgumentBuilderDirectiveInterface;
use Netmex\Lumina\Contracts\DirectiveFactoryInterface;
use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Netmex\Lumina\Intent\Intent;
use Symfony\Component\DependencyInjection\ServiceLocator;

abstract class AbstractASTDirectiveVisitor
{
    abstract protected function getDirectiveLocator(): ServiceLocator;
    abstract protected function getDirectiveRegistry(): DirectiveRegistry;
    protected DirectiveFactoryInterface $directiveFactory;

    public function __construct(DirectiveFactoryInterface $directiveFactory)
    {
        $this->directiveFactory = $directiveFactory;
    }

    public function collectTypeDirectives($typeNode): array
    {
        $directives = [];

        foreach ($typeNode->directives as $directiveNode) {
            $directives[] = $this->instantiateDirectiveFromNode($directiveNode, $typeNode);
        }

        return $directives;
    }

    public function applyTypeDirectivesToIntent(Intent $intent, array $typeDirectives): void
    {
        foreach ($typeDirectives as $directive) {
            $intent->applyTypeDirective($directive->name(), $directive);
        }
    }

    public function applyFieldDirectives(Intent $intent, FieldDefinitionNode $fieldNode, array &$existingArgs, DocumentNode $document): void {
        foreach ($fieldNode->directives as $directiveNode) {
            $directive = $this->instantiateDirectiveFromNode($directiveNode, $fieldNode);

            $this->applyResolverDirective($intent, $fieldNode, $directive, $document);
            $this->applyArgumentDirective($intent, $fieldNode, $directive, $directiveNode, $existingArgs);
        }
    }

    protected function instantiateDirectiveFromNode(object $directiveNode, object $definitionNode): AbstractDirective
    {
        return $this->directiveFactory->create($directiveNode, $definitionNode);
    }

    private function applyResolverDirective(Intent $intent, FieldDefinitionNode $fieldNode, AbstractDirective $directive, DocumentNode $document): void
    {
        if (!($directive instanceof FieldResolverInterface)) {
            return;
        }

        $directive->setModel($this->getNamedType($fieldNode->type));
        $intent->setResolver($directive);

        if (method_exists($directive, 'modifyFieldType')) {
            $directive->modifyFieldType($fieldNode, $document);
        }
    }

    private function applyArgumentDirective(Intent $intent, FieldDefinitionNode $fieldNode, AbstractDirective $directive, object $directiveNode, array &$existingArgs): void
    {
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
            $this->injectDirectiveArguments($fieldNode, $directive, $directiveNode, $existingArgs);
        }
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
