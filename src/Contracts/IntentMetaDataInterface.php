<?php

namespace Netmex\Lumina\Contracts;

use Netmex\Lumina\Intent\Intent;

interface IntentMetaDataInterface
{
    public function getChild(string $index): IntentMetaDataInterface;

    public function getStrategy(): ?string;

    public function getParent(): ?Intent;
    public function setParent(Intent $parent): void;

    /** @return IntentMetaDataInterface[] */
    public function getChildren(): array;
    public function addChild(IntentMetaDataInterface $metaData): void;

    public function getFields(): array;
    public function setFields(array $fields): void;
}
