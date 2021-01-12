<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory;

use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * The class resolving the parameters to their possible aliases in the container.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ParameterAliasResolver
{
    /**
     * The cache file path to use.
     * @var string|null
     */
    protected static ?string $cacheFile = null;

    /**
     * The already resolved parameter aliases.
     * @var array<string, array<string, array<string>>>
     */
    protected static array $parameterAliasesCache = [];

    /**
     * Sets the cache file to use.
     * @param string $cacheFile
     */
    public static function setCacheFile(string $cacheFile): void
    {
        self::$cacheFile = $cacheFile;
        if (file_exists($cacheFile)) {
            $parameterAliasesCache = require($cacheFile);
            if (is_array($parameterAliasesCache)) {
                self::$parameterAliasesCache = $parameterAliasesCache;
            }
        }
    }

    /**
     * Returns the aliases for the parameters of the constructor.
     * @param string $className
     * @return array<string, array<string>>
     * @throws ReflectionException
     */
    public function getParameterAliasesForConstructor(string $className): array
    {
        if (!isset(self::$parameterAliasesCache[$className])) {
            self::$parameterAliasesCache[$className] = $this->resolveParameterAliasesForConstructor($className);
            $this->writeCacheToFile();
        }
        return self::$parameterAliasesCache[$className];
    }

    /**
     * Resolves the parameter aliases for the constructor.
     * @param string $className
     * @return array<string, array<string>>
     * @throws ReflectionException
     */
    protected function resolveParameterAliasesForConstructor(string $className): array
    {
        $result = [];
        foreach ($this->getReflectedParametersForConstructor($className) as $parameter) {
            $result[$parameter->getName()] = $this->getAliasesForParameter($parameter);
        }
        return $result;
    }

    /**
     * Returns the reflected parameters of the constructor.
     * @param string $className
     * @return array<ReflectionParameter>
     * @throws ReflectionException
     */
    protected function getReflectedParametersForConstructor(string $className): array
    {
        $result = [];
        $reflectedClass = new ReflectionClass($className);
        if ($reflectedClass->getConstructor() !== null) {
            $result = $reflectedClass->getConstructor()->getParameters();
        }
        return $result;
    }

    /**
     * Returns the aliases for the parameter.
     * @param ReflectionParameter $parameter
     * @return array<string>
     */
    protected function getAliasesForParameter(ReflectionParameter $parameter): array
    {
        $result = [];

        $type = $parameter->getType();
        if ($type instanceof ReflectionNamedType) {
            $result[] = $type->getName() . ' $' . $parameter->getName();
            if (!$type->isBuiltin()) {
                $result[] = $type->getName();
            }
        }

        $result[] = '$' . $parameter->getName();
        return $result;
    }

    /**
     * Writes the current cache to the cache file, if set.
     */
    protected function writeCacheToFile(): void
    {
        if (self::$cacheFile !== null) {
            $contents = sprintf('<?php return %s;', var_export(self::$parameterAliasesCache, true));
            file_put_contents(self::$cacheFile, $contents);
        }
    }
}
