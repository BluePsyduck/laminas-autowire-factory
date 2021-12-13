<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Resolver;

use BluePsyduck\LaminasAutoWireFactory\Exception\MissingConfigException;
use Psr\Container\ContainerInterface;

/**
 * The resolver reading a value from the config.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ConfigResolver implements ResolverInterface
{
    /**
     * The alias with which the application config is registered to the container.
     * @var string
     */
    public static string $configAlias = 'config';

    /**
     * @param array<array-key> $keys
     */
    public function __construct(
        private array $keys
    ) {
    }

    public function resolve(ContainerInterface $container): mixed
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

    public function canResolve(ContainerInterface $container): bool
    {
        return $container->has(self::$configAlias);
    }

    /**
     * @return array{k:array<array-key>}
     */
    public function __serialize(): array
    {
        return ['k' => $this->keys];
    }

    /**
     * @param array{k?:array<array-key>} $data
     */
    public function __unserialize(array $data): void
    {
        $this->keys = $data['k'] ?? [];
    }
}
