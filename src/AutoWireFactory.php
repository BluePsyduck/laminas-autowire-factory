<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\Exception\FailedReflectionException;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverFactory;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

/**
 * The factory auto-wiring the parameters of services.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AutoWireFactory implements FactoryInterface, AbstractFactoryInterface
{
    /** @var array<class-string, array<ResolverInterface>> */
    private static array $cache = [];
    private static bool $isCacheDirty = false;
    private static ?string $cacheFile = null;

    private ResolverFactory $resolverFactory;

    public function __construct()
    {
        $this->resolverFactory = new ResolverFactory();
    }

    /**
     * Sets the cache file to use.
     * @param string $cacheFile
     */
    public static function setCacheFile(string $cacheFile): void
    {
        self::$cacheFile = $cacheFile;
        if (file_exists($cacheFile)) {
            $contents = @file_get_contents($cacheFile);
            if ($contents !== false) {
                /** @noinspection UnserializeExploitsInspection */
                $data = @unserialize($contents);
                if (is_array($data)) {
                    /** @var array<class-string, array<ResolverInterface>> $data */
                    self::$cache = $data;
                }
            }
            self::$isCacheDirty = false;
        }
    }

    public function __destruct()
    {
        if (self::$isCacheDirty && self::$cacheFile !== null) {
            file_put_contents(self::$cacheFile, serialize(self::$cache));
            self::$isCacheDirty = false;
        }
    }

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     * @return object
     * @throws ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): object
    {
        if (!class_exists($requestedName)) {
            throw new FailedReflectionException($requestedName);
        }

        try {
            $resolvers = $this->getResolversForClass($requestedName);
        } catch (ReflectionException $e) {
            throw new FailedReflectionException($requestedName, $e);
        }

        $values = array_map(static fn (ResolverInterface $resolver) => $resolver->resolve($container), $resolvers);
        return new $requestedName(...$values);
    }

    /**
     * Returns whether the requested name can be auto-wired by this factory.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        if (!class_exists($requestedName)) {
            return false;
        }

        try {
            $resolvers = $this->getResolversForClass($requestedName);
        } catch (ReflectionException) {
            return false;
        }

        foreach ($resolvers as $resolver) {
            if (!$resolver->canResolve($container)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns the resolvers for the provided class.
     * @param class-string $className
     * @return array<ResolverInterface>
     * @throws ReflectionException
     */
    private function getResolversForClass(string $className): array
    {
        if (!isset(self::$cache[$className])) {
            self::$cache[$className] = $this->resolverFactory->createResolversForClass($className);
            self::$isCacheDirty = true;
        }
        return self::$cache[$className];
    }
}
