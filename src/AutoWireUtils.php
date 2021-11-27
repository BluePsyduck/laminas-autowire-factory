<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\Factory\AliasArrayInjectorFactory;
use BluePsyduck\LaminasAutoWireFactory\Factory\ConfigReaderFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * The utils class for creating the helper factories to use in the config.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AutoWireUtils
{
    /**
     * Reads a value from the config.
     * @param array-key ...$keys
     * @return FactoryInterface
     */

    public static function readConfig(string|int ...$keys): FactoryInterface
    {
        return new ConfigReaderFactory(...$keys);
    }

    /**
     * Injects an array of aliases to the container.
     * @param array-key ...$keys
     * @return FactoryInterface
     */
    public static function injectAliasArray(string|int ...$keys): FactoryInterface
    {
        return new AliasArrayInjectorFactory(...$keys);
    }
}
