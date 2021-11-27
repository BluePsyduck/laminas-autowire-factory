<?php

declare(strict_types=1);

namespace BluePsyduckTestAsset\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\Resolver\Alias;
use BluePsyduck\LaminasAutoWireFactory\Resolver\InjectAliasArray;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ReadConfig;

/**
 * A class using attributes to resolve the parameters.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ClassWithAttributes
{
    public function __construct(
        #[Alias('test.foo')]
        public ClassWithoutConstructor $foo,
        #[ReadConfig('test', 'property')]
        public string $property,
        /** @var array<mixed> */
        #[InjectAliasArray('test', 'instances')]
        public array $instances,
    ) {
    }
}
