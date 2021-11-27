<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Exception;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Throwable;

/**
 * The exception thrown when a requested config item is not found.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MissingConfigException extends ServiceNotCreatedException
{
    private const MESSAGE = 'Failed to read config: %s';

    /**
     * @param array<array-key> $configKeys
     * @param Throwable|null $previous
     */
    public function __construct(array $configKeys, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, implode(' -> ', $configKeys)), 0, $previous);
    }
}
