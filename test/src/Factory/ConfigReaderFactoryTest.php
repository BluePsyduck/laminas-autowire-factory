<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Factory;

use BluePsyduck\LaminasAutoWireFactory\Factory\ConfigReaderFactory;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ConfigResolver;
use BluePsyduck\TestHelper\ReflectionTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ConfigReaderFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Factory\ConfigReaderFactory
 */
class ConfigReaderFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @throws ReflectionException
     */
    public function testCreateResolver(): void
    {
        $keys = ['abc', 'def'];
        $expectedResult = new ConfigResolver($keys);

        $instance = new ConfigReaderFactory(...$keys);

        $result = $this->invokeMethod($instance, 'createResolver', $keys);
        $this->assertEquals($expectedResult, $result);
    }
}
