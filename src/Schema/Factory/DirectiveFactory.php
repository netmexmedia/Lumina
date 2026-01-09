<?php

namespace Netmex\Lumina\Schema\Factory;

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
            $args[$argNode->name->value] = $argNode->value->value;
        }

        return $args;
    }
}