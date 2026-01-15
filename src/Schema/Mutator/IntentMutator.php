<?php

namespace Netmex\Lumina\Schema\Mutator;

use Netmex\Lumina\Intent\Intent;
use Netmex\Lumina\Intent\IntentMetaData;

class IntentMutator
{
    public function getOrCreateIntent(Intent $parent, string $fieldName, bool $isRoot = false): Intent
    {
        if ($isRoot) {
            return $parent;
        }

        $child = $parent->getChildByName($fieldName) ?? new Intent($parent->typeName, $fieldName);

        if (!$child->getMetaData()) {
            $child->setMetaData(new IntentMetaData());
        }

        $child->getMetaData()->setFields([$fieldName]);

        if (!$child->getParent()) {
            $child->setParent($parent);
            $parent->addChild($child);
        }

        return $child;
    }

    public function mergeChildFields(Intent $intent): void
    {
        $columns = $intent->getMetaData()->getFields();

        foreach ($intent->getChildrenMetaData() as $childMetaArray) {
            foreach ($childMetaArray as $childMeta) {
                $fields = $childMeta->getFields() ?? [];
                array_push($columns, ...$fields);
            }
        }

        $intent->getMetaData()->setFields(array_unique($columns));
    }
}