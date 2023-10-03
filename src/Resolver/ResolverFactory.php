<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Resolver;

use BluePsyduck\LaminasAutoWireFactory\Attribute\ResolverAttribute;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

/**
 * THe factory for the resolvers.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ResolverFactory
{
    /**
     * Creates the resolvers for the parameters of the constructor from the specified class.
     * @param class-string $className
     * @return array<ResolverInterface>
     * @throws ReflectionException
     */
    public function createResolversForClass(string $className): array
    {
        $constructor = (new ReflectionClass($className))->getConstructor();
        if ($constructor === null) {
            return [];
        }

        return array_map([$this, 'createResolverForParameter'], $constructor->getParameters());
    }

    /**
     * Creates the resolver for the specified parameter.
     * @param ReflectionParameter $parameter
     * @return ResolverInterface
     * @throws ReflectionException
     */
    public function createResolverForParameter(ReflectionParameter $parameter): ResolverInterface
    {
        $attributes = $parameter->getAttributes(ResolverAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
        if (count($attributes) > 0) {
            /* @var ResolverAttribute $attribute */
            $attribute = $attributes[0]->newInstance();
            $resolver = $attribute->createResolver();
        } elseif ($parameter->isDefaultValueAvailable()) {
            $resolver = new DefaultValueResolver();
        } else {
            $resolver = new DefaultResolver();
        }

        if ($resolver instanceof ParameterAwareInterface) {
            $resolver->setParameter($parameter);
        }

        return $resolver;
    }
}
