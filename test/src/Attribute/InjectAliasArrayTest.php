<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Attribute;

use BluePsyduck\LaminasAutoWireFactory\Attribute\InjectAliasArray;
use BluePsyduck\LaminasAutoWireFactory\Resolver\AliasArrayResolver;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the InjectAliasArray class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Attribute\InjectAliasArray
 */
class InjectAliasArrayTest extends TestCase
{
    public function testCreateResolver(): void
    {
        $configKeys = ['abc', 'def'];
        $expectedResult = new AliasArrayResolver($configKeys);

        $instance = new InjectAliasArray(...$configKeys);
        $result = $instance->createResolver();

        $this->assertEquals($expectedResult, $result);
    }
}
