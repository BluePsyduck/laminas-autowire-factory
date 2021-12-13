<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Attribute;

use BluePsyduck\LaminasAutoWireFactory\Attribute\Alias;
use BluePsyduck\LaminasAutoWireFactory\Resolver\AliasResolver;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the Alias class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Attribute\Alias
 */
class AliasTest extends TestCase
{
    public function testCreateResolver(): void
    {
        $alias = 'abc';
        $expectedResult = new AliasResolver($alias);

        $instance = new Alias($alias);
        $result = $instance->createResolver();

        $this->assertEquals($expectedResult, $result);
    }
}
