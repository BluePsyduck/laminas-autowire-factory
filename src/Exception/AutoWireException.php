<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Exception;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;

/**
 * The exception thrown when the auto-wire failed for a service.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AutoWireException extends ServiceNotCreatedException
{
}
