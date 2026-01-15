<?php

namespace Netmex\Lumina\Schema\Directive\Strategy;

use Netmex\Lumina\Contracts\DirectiveStrategyInterface;
use Netmex\Lumina\Contracts\FieldResolverInterface;
use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Schema\Helpers\ASTTypeHelper;

final class FieldResolverStrategy implements DirectiveStrategyInterface
{
    public function supports(object $directive): bool
    {
        return $directive instanceof FieldResolverInterface;
    }

    public function apply(object  $directive, Intent  $intent, object  $fieldNode, ?object $document = null): void
    {
        $intent->setResolver($directive);

        $directive->setModel(
            ASTTypeHelper::getNamedType($fieldNode->type)
        );

        $intent->getMetaData()->setStrategy($directive::name());
        $intent->getMetaData()->setModel($directive->modelClass());

        $intent->getParent()?->getMetaData()->addChild($intent->getMetaData());

        // Should be an interface method, but keeping as is for backward compatibility
        if ($document && method_exists($directive, 'modifyFieldType')) {
            $directive->modifyFieldType($fieldNode, $document);
        }
    }
}