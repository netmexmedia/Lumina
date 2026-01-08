<?php

declare(strict_types=1);

namespace Netmex\Lumina\Schema\Source;

use Netmex\Lumina\Contracts\FieldArgumentDirectiveInterface;
use Netmex\Lumina\Contracts\FieldInputDirectiveInterface;
use Netmex\Lumina\Contracts\SchemaSourceInterface;
use Netmex\Lumina\Directives\Registry\DirectiveRegistry;

final readonly class DirectiveSchemaSource implements SchemaSourceInterface
{
    private DirectiveRegistry $directives;

    public function __construct(DirectiveRegistry $directives) {
        $this->directives = $directives;
    }

    public function load(): string
    {
        $chunks = [];

        foreach ($this->directives->all() as $name => $className) {
            $chunks[] = $className::definition();

            if (is_subclass_of($className, FieldArgumentDirectiveInterface::class)) {
//                $chunks[] = $className::argumentsDefinition();
            }

            if (is_subclass_of($className, FieldInputDirectiveInterface::class)) {
                $chunks[] = $className::inputsDefinition();
            }
        }

        return implode("\n\n", $chunks);
    }
}
