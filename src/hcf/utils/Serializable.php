<?php

declare(strict_types=1);

namespace hcf\utils;

use ReflectionClass;

abstract class Serializable {

    /**
     * @param array $merge
     * @param bool  $static
     *
     * @return array
     */
    public function serialize(array $merge = [], bool $static = false): array {
        $serialize = [];

        $reflection = new ReflectionClass($this);

        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);

            if ($property->isStatic() && !$static) {
                continue;
            }

            $serialize[$property->getName()] = $property->getValue($this);
        }

        return array_merge($serialize, $merge);
    }

    /**
     * @param array $merge
     * @param bool  $static
     *
     * @return string
     */
    public function serializeString(array $merge = [], bool $static = false): string {
        return serialize($this->serialize($merge, $static));
    }
}