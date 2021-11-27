<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Resolver;

use ReflectionParameter;

/**
 * The interface signaling the awareness of the actual parameter.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface ParameterAwareInterface
{
    /**
     * Sets the reflected parameter.
     * @param ReflectionParameter $parameter
     */
    public function setParameter(ReflectionParameter $parameter): void;
}
