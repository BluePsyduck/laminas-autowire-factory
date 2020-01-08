<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Exception;

use Throwable;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

/**
 * The exception thrown when a requested config item is not found.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class MissingConfigException extends ServiceNotCreatedException
{
    /**
     * The message template of the exception.
     */
    private const MESSAGE = 'Failed to read config: %s';

    /**
     * Initializes the exception.
     * @param array|string[] $keys
     * @param Throwable|null $previous
     */
    public function __construct(array $keys, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, implode(' -> ', $keys)), 0, $previous);
    }
}
