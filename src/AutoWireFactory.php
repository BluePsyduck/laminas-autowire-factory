<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\Exception\AutoWireException;
use BluePsyduck\LaminasAutoWireFactory\Exception\FailedReflectionException;
use BluePsyduck\LaminasAutoWireFactory\Exception\NoParameterMatchException;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use ReflectionException;

/**
 * The factory auto-wiring the parameters of services.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AutoWireFactory implements FactoryInterface, AbstractFactoryInterface
{
    /**
     * The parameter alias resolver.
     * @var ParameterAliasResolver
     */
    protected $parameterAliasResolver;

    /**
     * Sets the cache file to use.
     * @param string $cacheFile
     */
    public static function setCacheFile(string $cacheFile): void
    {
        ParameterAliasResolver::setCacheFile($cacheFile);
    }

    /**
     * Initializes the factory.
     */
    public function __construct()
    {
        $this->parameterAliasResolver = new ParameterAliasResolver();
    }

    /**
     * Creates the service.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     * @return object
     * @throws AutoWireException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        try {
            $parameterAliases = $this->parameterAliasResolver->getParameterAliasesForConstructor($requestedName);
        } catch (ReflectionException $e) {
            throw new FailedReflectionException($requestedName, $e);
        }

        $parameters = $this->createParameterInstances($container, $requestedName, $parameterAliases);
        return $this->createInstance($requestedName, $parameters);
    }

    /**
     * Creates the instances for the parameter aliases.
     * @param ContainerInterface $container
     * @param string $className
     * @param array|string[][] $parameterAliases
     * @return array|object[]
     * @throws AutoWireException
     */
    protected function createParameterInstances(
        ContainerInterface $container,
        string $className,
        array $parameterAliases
    ): array {
        $result = [];
        foreach ($parameterAliases as $parameterName => $aliases) {
            $result[] = $this->createInstanceOfFirstAvailableAlias($container, $className, $parameterName, $aliases);
        }
        return $result;
    }

    /**
     * Creates the instance of the first alias actually available in the container.
     * @param ContainerInterface $container
     * @param string $className
     * @param string $parameterName
     * @param array|string[] $aliases
     * @return mixed
     * @throws AutoWireException
     */
    protected function createInstanceOfFirstAvailableAlias(
        ContainerInterface $container,
        string $className,
        string $parameterName,
        array $aliases
    ) {
        foreach ($aliases as $alias) {
            if ($container->has($alias)) {
                return $container->get($alias);
            }
        }

        throw new NoParameterMatchException($className, $parameterName);
    }

    /**
     * Creates the actual instance.
     * @param string $className
     * @param array<mixed> $parameters
     * @return mixed
     */
    protected function createInstance(string $className, array $parameters)
    {
        return new $className(...$parameters);
    }

    /**
     * Returns whether the requested name can be auto-wired by this factory.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $result = false;
        if (class_exists($requestedName)) {
            try {
                $parameterAliases = $this->parameterAliasResolver->getParameterAliasesForConstructor($requestedName);
                $result = $this->canAutoWire($container, $parameterAliases);
            } catch (ReflectionException $e) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Returns whether the parameter aliases can be auto wired.
     * @param ContainerInterface $container
     * @param array|string[][] $parameterAliases
     * @return bool
     */
    protected function canAutoWire(ContainerInterface $container, array $parameterAliases): bool
    {
        foreach ($parameterAliases as $aliases) {
            if (!$this->hasAnyAlias($container, $aliases)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns whether the container has any of the aliases.
     * @param ContainerInterface $container
     * @param array|string[] $aliases
     * @return bool
     */
    protected function hasAnyAlias(ContainerInterface $container, array $aliases): bool
    {
        foreach ($aliases as $alias) {
            if ($container->has($alias)) {
                return true;
            }
        }
        return false;
    }
}
