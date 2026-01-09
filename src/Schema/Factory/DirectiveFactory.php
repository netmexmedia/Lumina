<?php

namespace Netmex\Lumina\Schema\Factory;

use GraphQL\Language\AST\ListValueNode;
use GraphQL\Language\AST\ObjectValueNode;
use GraphQL\Language\AST\ValueNode;
use Netmex\Lumina\Contracts\DirectiveFactoryInterface;
use Netmex\Lumina\Directives\AbstractDirective;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;
use Symfony\Component\DependencyInjection\ServiceLocator;

final class DirectiveFactory implements DirectiveFactoryInterface
{
    private DirectiveRegistry $registry;
    private ServiceLocator $directiveLocator;

    public function __construct(DirectiveRegistry $registry, ServiceLocator $directiveLocator) {
        $this->registry = $registry;
        $this->directiveLocator = $directiveLocator;
    }

    public function create(object $directiveNode, object $definitionNode): AbstractDirective
    {
        $name = $directiveNode->name->value;
        $serviceId = $this->registry->get($name);

        if ($serviceId === null) {
            throw new \RuntimeException(sprintf(
                'Directive "%s" used on %s is not registered.',
                $name,
                $definitionNode::class
            ));
        }

        $directive = clone $this->directiveLocator->get($serviceId);

        if (!$directive instanceof AbstractDirective) {
            throw new \RuntimeException(sprintf(
                'Directive "%s" is not an AbstractDirective.',
                $name
            ));
        }

        return $this->hydrateDirective($directive, $directiveNode, $definitionNode);
    }

    public function hydrateDirective($directive, $directiveNode, $definitionNode): AbstractDirective
    {
        $directive->directiveNode = $directiveNode;
        $directive->definitionNode = $definitionNode;

        $directive->setArguments(
            $this->resolveArguments($directiveNode)
        );

        return $directive;
    }

    private function resolveArguments(object $directiveNode): array
    {
        $args = [];

        foreach ($directiveNode->arguments ?? [] as $argNode) {
            $args[$argNode->name->value] = $this->resolveValueNode($argNode->value);
        }

        return $args;
    }

    private function resolveValueNode(ValueNode $node): mixed
    {
        switch (true) {
            case property_exists($node, 'value'):
                return $node->value;

            case $node instanceof ListValueNode:
                return array_map(
                    fn ($item) => $this->resolveValueNode($item),
                    iterator_to_array($node->values)
                );

            case $node instanceof ObjectValueNode:
                $value = [];
                foreach ($node->fields as $field) {
                    $value[$field->name->value] = $this->resolveValueNode($field->value);
                }
                return $value;

            default:
                throw new \RuntimeException(
                    'Unsupported directive argument value node: ' . $node::class
                );
        }
    }
}