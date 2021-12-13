<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Factory;

use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;

/**
 * The abstract class for factories using a config resolver.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
abstract class AbstractConfigResolverFactory implements FactoryInterface
{
    /**
     * @var array<array-key>
     */
    private array $keys;

    final public function __construct(string|int ...$keys)
    {
        $this->keys = $keys;
    }

    /**
     * @param array{keys?:array<array-key>} $array
     * @return self
     */
    public static function __set_state(array $array): self
    {
        return new static(...($array['keys'] ?? []));
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     * @return mixed
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): mixed
    {
        return $this->createResolver($this->keys)->resolve($container);
    }

    /**
     * Creates the resolver to use.
     * @param array<array-key> $keys
     * @return ResolverInterface
     */
    abstract protected function createResolver(array $keys): ResolverInterface;
}
