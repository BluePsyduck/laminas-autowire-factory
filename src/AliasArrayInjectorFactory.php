<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory;

use Interop\Container\ContainerInterface;

/**
 * The factory creating an array of aliases.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class AliasArrayInjectorFactory
{
    /**
     * The config keys.
     * @var array|string[]
     */
    protected $configKeys;

    /**
     * Sets the state of the factory on deserialization.
     * @param array<mixed> $array
     * @return self
     */
    public static function __set_state(array $array): self
    {
        return new self(
            ...($array['configKeys'] ?? [])
        );
    }

    /**
     * Creates the factory.
     * @param string ...$configKeys The config keys to the aliases to be injected.
     */
    public function __construct(string ...$configKeys)
    {
        $this->configKeys = $configKeys;
    }

    /**
     * Creates the service.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     * @return array<mixed>
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $aliases = $this->createConfigReaderFactory($this->configKeys)($container, $requestedName, $options);

        $result = [];
        foreach (is_array($aliases) ? $aliases : [$aliases] as $key => $alias) {
            $result[$key] = $container->get($alias);
        }
        return $result;
    }

    /**
     * Creates the factory to read the aliases from the config.
     * @param array|string[] $configKeys
     * @return ConfigReaderFactory
     */
    protected function createConfigReaderFactory(array $configKeys): ConfigReaderFactory
    {
        return new ConfigReaderFactory(...$configKeys);
    }
}
