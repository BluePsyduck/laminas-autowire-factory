<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Factory;

use BluePsyduck\LaminasAutoWireFactory\Factory\AliasArrayInjectorFactory;
use BluePsyduck\LaminasAutoWireFactory\Resolver\InjectAliasArray;
use BluePsyduck\TestHelper\ReflectionTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the AliasArrayInjectorFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Factory\AliasArrayInjectorFactory
 */
class AliasArrayInjectorFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @throws ReflectionException
     */
    public function testCreateResolver(): void
    {
        $keys = ['abc', 'def'];
        $expectedResult = new InjectAliasArray(...$keys);

        $instance = new AliasArrayInjectorFactory(...$keys);

        $result = $this->invokeMethod($instance, 'createResolver', $keys);
        $this->assertEquals($expectedResult, $result);
    }
}
