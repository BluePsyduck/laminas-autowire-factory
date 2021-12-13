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
    public function __construct(
        public string $property,
        /** @var array<mixed> */
        public array $instances,
    ) {
    }
}
