<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Attribute;

use Attribute;
use BluePsyduck\LaminasAutoWireFactory\Resolver\AliasArrayResolver;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;

/**
 * The attribute using the alias array resolver.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class InjectAliasArray implements ResolverAttribute
{
    /** @var array<array-key> */
    private array $keys;

    public function __construct(string|int ...$keys)
    {
        $this->keys = $keys;
    }

    public function createResolver(): ResolverInterface
    {
        return new AliasArrayResolver($this->keys);
    }
}
