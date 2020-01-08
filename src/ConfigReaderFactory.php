<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\Exception\MissingConfigException;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * The factory reading a value from the application config.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ConfigReaderFactory implements FactoryInterface
{
    /**
     * The alias with which the application config is registered to the container.
     * @var string
     */
    protected static $configAlias = 'config';

    /**
     * The keys of the config.
     * @var array|string[]
     */
    protected $keys;

    /**
     * Sets alias with which the application config is registered to the container.
     * @param string $configAlias
     */
    public static function setConfigAlias(string $configAlias): void
    {
        self::$configAlias = $configAlias;
    }

    /**
     * Sets the state of the factory on deserialization.
     * @param array<mixed> $array
     * @return self
     */
    public static function __set_state(array $array): self
    {
        return new self(
            ...($array['keys'] ?? [])
        );
    }

    /**
     * Initializes the factory.
     * @param string ...$keys
     */
    public function __construct(string ...$keys)
    {
        $this->keys = $keys;
    }

    /**
     * Creates the service.
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array<mixed>|null $options
     * @return mixed
     * @throws MissingConfigException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $result = $container->get(self::$configAlias);
        foreach ($this->keys as $key) {
            if (!is_array($result) || !array_key_exists($key, $result)) {
                throw new MissingConfigException($this->keys);
            }
            $result = $result[$key];
        }
        return $result;
    }
}
