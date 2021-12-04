<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Factory;

use BluePsyduck\LaminasAutoWireFactory\Resolver\ConfigResolver;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;

/**
 * The factory reading a value from the application config.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ConfigReaderFactory extends AbstractConfigResolverFactory
{
    protected function createResolver(array $keys): ResolverInterface
    {
        return new ConfigResolver($keys);
    }
}
