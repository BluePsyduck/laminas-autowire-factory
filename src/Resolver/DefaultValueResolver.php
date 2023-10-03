<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Resolver;

use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionParameter;

/**
 * @author richard.weinhold
 * @since 12.09.2023
 */
class DefaultValueResolver implements ResolverInterface, ParameterAwareInterface
{
    private bool $hasDefaultValue;
    private mixed $defaultValue;

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    public function setParameter(ReflectionParameter $parameter): void
    {
        $this->hasDefaultValue = $parameter->isDefaultValueAvailable();
        $this->defaultValue = $parameter->getDefaultValue();
    }

    public function resolve(ContainerInterface $container): mixed
    {
        return $this->defaultValue;
    }

    public function canResolve(ContainerInterface $container): bool
    {
        return $this->hasDefaultValue;
    }

    /**
     * @return array{h:bool,v:mixed}
     */
    public function __serialize(): array
    {
        return ['h' => $this->hasDefaultValue, 'v' => $this->defaultValue];
    }

    /**
     * @param array{h?:bool,v?:mixed} $data
     */
    public function __unserialize(array $data): void
    {
        $this->hasDefaultValue = $data['h'] ?? false;
        $this->defaultValue = $data['v'] ?? null;
    }
}
