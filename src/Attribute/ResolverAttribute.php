<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Attribute;

use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;

/**
 * The interface of the resolver attributes.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface ResolverAttribute
{
    /**
     * Provides the resolver to use for the parameter of the attribute.
     * @return ResolverInterface
     */
    public function createResolver(): ResolverInterface;
}
