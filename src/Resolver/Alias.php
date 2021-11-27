<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Resolver;

use Attribute;
use Psr\Container\ContainerInterface;

/**
 * The resolver using a specific alias for the parameter.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Alias implements ResolverInterface
{
    public function __construct(
        private string $alias
    ) {
    }

    public function resolve(ContainerInterface $container): mixed
    {
        return $container->get($this->alias);
    }

    public function canResolve(ContainerInterface $container): bool
    {
        return $container->has($this->alias);
    }

    /**
     * @return array{a:string}
     */
    public function __serialize(): array
    {
        return ['a' => $this->alias];
    }

    /**
     * @param array{a?:string} $data
     */
    public function __unserialize(array $data): void
    {
        $this->alias = $data['a'] ?? '';
    }
}
