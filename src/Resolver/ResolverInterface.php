<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Resolver;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * The interface of a parameter resolver.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
interface ResolverInterface
{
    /**
     * Resolves the parameter using the provided container.
     * @param ContainerInterface $container
     * @return mixed
     * @throws ContainerExceptionInterface
     */
    public function resolve(ContainerInterface $container): mixed;

    /**
     * Checks whether the parameter can actually be resolved to a value.
     * @param ContainerInterface $container
     * @return bool
     */
    public function canResolve(ContainerInterface $container): bool;
}
