<?php

/**
 * File containing helper function to shorten the code required for container configs.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace BluePsyduck\LaminasAutoWireFactory;

if (!function_exists('\BluePsyduck\LaminasAutoWireFactory\injectAliasArray')) {
    /**
     * Injects an array of aliases to the container.
     * @param string ...$configKeys
     * @return callable
     */
    function injectAliasArray(string ...$configKeys): callable
    {
        return new AliasArrayInjectorFactory(...$configKeys);
    }
}

if (!function_exists('\BluePsyduck\LaminasAutoWireFactory\readConfig')) {
    /**
     * Reads a value from the config.
     * @param string ...$keys
     * @return callable
     */
    function readConfig(string ...$keys): callable
    {
        return new ConfigReaderFactory(...$keys);
    }
}
