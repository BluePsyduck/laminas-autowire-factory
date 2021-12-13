<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Attribute;

use Attribute;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ConfigResolver;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;

/**
 * The attribute using the config resolver.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class ReadConfig implements ResolverAttribute
{
    /**
     * @var array<array-key>
     */
    private array $keys;

    public function __construct(string|int ...$keys)
    {
        $this->keys = $keys;
    }

    public function createResolver(): ResolverInterface
    {
        return new ConfigResolver($this->keys);
    }
}
