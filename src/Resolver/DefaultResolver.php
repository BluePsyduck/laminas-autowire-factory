<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Resolver;

use BluePsyduck\LaminasAutoWireFactory\Exception\NoParameterMatchException;
use Psr\Container\ContainerInterface;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * The default resolver strategy, using the type and name of the parameter.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class DefaultResolver implements ResolverInterface, ParameterAwareInterface
{
    /** @var array<string> */
    private array $aliases = [];

    public function setParameter(ReflectionParameter $parameter): void
    {
        $this->aliases = [];

        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType) {
            $this->aliases[] = "{$type->getName()} \${$parameter->getName()}";
            if (!$type->isBuiltin()) {
                $this->aliases[] = "{$type->getName()}";
            }
        }
        $this->aliases[] = "\${$parameter->getName()}";
    }

    public function resolve(ContainerInterface $container): mixed
    {
        foreach ($this->aliases as $alias) {
            if ($container->has($alias)) {
                return $container->get($alias);
            }
        }

        throw new NoParameterMatchException(array_shift($this->aliases) ?? 'unknown');
    }

    public function canResolve(ContainerInterface $container): bool
    {
        foreach ($this->aliases as $alias) {
            if ($container->has($alias)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{a:array<string>}
     */
    public function __serialize(): array
    {
        return ['a' => $this->aliases];
    }

    /**
     * @param array{a?:array<string>} $data
     */
    public function __unserialize(array $data): void
    {
        $this->aliases = $data['a'] ?? [];
    }
}
