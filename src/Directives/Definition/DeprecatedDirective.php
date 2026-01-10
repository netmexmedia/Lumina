<?php

declare(strict_types=1);

namespace Netmex\Lumina\Directives\Definition;

use GraphQL\Language\AST\ArgumentNode;
use GraphQL\Language\AST\DirectiveNode;
use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\NameNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\StringValueNode;
use Netmex\Lumina\Contracts\FieldTypeModifierInterface;
use Netmex\Lumina\Directives\AbstractDirective;

final class DeprecatedDirective extends AbstractDirective implements FieldTypeModifierInterface
{
    public static function name(): string
    {
        return 'deprecated';
    }

    public static function definition(): string
    {
        return <<<'GRAPHQL'
            directive @deprecated(
                reason: String
            ) on FIELD_DEFINITION | OBJECT | INTERFACE | ENUM
        GRAPHQL;
    }

    public function modifyFieldType(FieldDefinitionNode $fieldNode, DocumentNode $document): void
    {
        $reason = $this->getArgument(
            'message',
            'This field is deprecated and will be removed in future versions.'
        );

        foreach ($fieldNode->directives as $directive) {
            if ($directive->name->value === 'deprecated') {
                return;
            }
        }

        $fieldNode->directives[] = new DirectiveNode([
            'name' => new NameNode(['value' => 'deprecated']),
            'arguments' => new NodeList([
                new ArgumentNode([
                    'name' => new NameNode(['value' => 'reason']),
                    'value' => new StringValueNode(['value' => $reason]),
                ]),
            ]),
        ]);
    }
}