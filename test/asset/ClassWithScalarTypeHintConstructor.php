<?php

declare(strict_types=1);

namespace BluePsyduckTestAsset\LaminasAutoWireFactory;

/**
 * A class using a scalar type hint in the constructor.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ClassWithScalarTypeHintConstructor
{
    public $property;
    public $instances;

    public function __construct(string $property, array $instances)
    {
        $this->property = $property;
        $this->instances = $instances;
    }
}
