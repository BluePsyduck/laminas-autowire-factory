<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Resolver;

use Attribute;
use Psr\Container\ContainerInterface;

/**
 * The resolver injecting an array of aliases from the config.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class InjectAliasArray extends ReadConfig
{
    public function resolve(ContainerInterface $container): mixed
    {
        $aliases = parent::resolve($container);

        $result = [];
        foreach (is_array($aliases) ? $aliases : [$aliases] as $key => $alias) {
            $result[$key] = $container->get($alias);
        }
        return $result;
    }
}
