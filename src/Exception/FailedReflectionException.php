<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Exception;

use Throwable;

/**
 * The exception thrown when reflecting a class to auto-wire failed.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class FailedReflectionException extends AutoWireException
{
    /**
     * The message template of the exception.
     */
    private const MESSAGE = 'Failed to auto-wire %s: Unable to reflect class.';

    /**
     * Initializes the exception.
     * @param string $className
     * @param Throwable|null $previous
     */
    public function __construct(string $className, ?Throwable $previous = null)
    {
        parent::__construct(sprintf(self::MESSAGE, $className), 0, $previous);
    }
}
