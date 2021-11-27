<?php

declare(strict_types=1);

namespace BluePsyduckTestAsset\LaminasAutoWireFactory;

/**
 * A class with a constructor using classes as type hints.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ClassWithClassTypeHintConstructor
{
    public function __construct(
        public ClassWithoutConstructor $foo,
        public ClassWithParameterlessConstructor $bar,
    ) {
    }
}
