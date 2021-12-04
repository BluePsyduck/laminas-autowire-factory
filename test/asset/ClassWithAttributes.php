<?php

declare(strict_types=1);

namespace BluePsyduckTestAsset\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use BluePsyduck\LaminasAutoWireFactory\Attribute\InjectAliasArray;
use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;

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
