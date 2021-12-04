<?php

declare(strict_types=1);

namespace BluePsyduck\LaminasAutoWireFactory\Attribute;

use Attribute;
use BluePsyduck\LaminasAutoWireFactory\Resolver\AliasResolver;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;

/**
 * The attribute using the alias resolver.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Alias implements ResolverAttribute
{
    public function __construct(
        private string $alias,
    ) {
    }

    public function createResolver(): ResolverInterface
    {
        return new AliasResolver($this->alias);
    }
}
