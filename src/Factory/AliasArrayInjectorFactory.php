<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Factory;

use BluePsyduck\LaminasAutoWireFactory\Resolver\AliasArrayResolver;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;

/**
 * The factory creating an array of aliases.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AliasArrayInjectorFactory extends AbstractConfigResolverFactory
{
    protected function createResolver(array $keys): ResolverInterface
    {
        return new AliasArrayResolver($keys);
    }
}
