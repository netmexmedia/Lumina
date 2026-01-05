<?php

namespace Netmex\Lumina\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InputValueDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Netmex\Lumina\Intent\Builder\IntentBuilderInterface;
use Netmex\Lumina\Intent\Intent;

interface ArgumentDirective
{
    public function name(): string;

    public function intent(IntentBuilderInterface $builder, DirectiveContext $context): void;
}