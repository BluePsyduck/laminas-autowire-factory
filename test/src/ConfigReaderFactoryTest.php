<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\ConfigReaderFactory;
use BluePsyduck\LaminasAutoWireFactory\Exception\MissingConfigException;
use BluePsyduck\TestHelper\ReflectionTrait;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the ConfigReaderFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\LaminasAutoWireFactory\ConfigReaderFactory
 */
class ConfigReaderFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @throws ReflectionException
     * @covers ::setConfigAlias
     * @runInSeparateProcess
     */
    public function testSetConfigAlias(): void
    {
        $configAlias = 'abc';

        $this->assertSame('config', $this->extractStaticProperty(ConfigReaderFactory::class, 'configAlias'));

        ConfigReaderFactory::setConfigAlias($configAlias);
        $this->assertSame($configAlias, $this->extractStaticProperty(ConfigReaderFactory::class, 'configAlias'));
    }

    /**
     * @covers ::__set_state
     */
    public function testSetState(): void
    {
        $array = [
            'keys' => ['abc', 'def'],
        ];
        $expectedResult = new ConfigReaderFactory('abc', 'def');

        $result = ConfigReaderFactory::__set_state($array);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers ::__set_state
     */
    public function testSetStateWithoutArray(): void
    {
        $expectedResult = new ConfigReaderFactory();

        $result = ConfigReaderFactory::__set_state([]);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $expectedKeys = ['abc', 'def'];
        $factory = new ConfigReaderFactory('abc', 'def');

        $this->assertSame($expectedKeys, $this->extractProperty($factory, 'keys'));
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $configAlias = 'abc';
        $requestedName = 'def';
        $config = [
            'ghi' => [
                'jkl' => 'mno',
            ],
        ];
        $keys = ['ghi', 'jkl'];
        $expectedResult = 'mno';

        ConfigReaderFactory::setConfigAlias($configAlias);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo($configAlias))
                  ->willReturn($config);

        $factory = new ConfigReaderFactory(...$keys);
        $result = $factory($container, $requestedName);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeWithMissingKey(): void
    {
        $configAlias = 'abc';
        $requestedName = 'def';
        $config = [
            'ghi' => [],
        ];
        $keys = ['ghi', 'jkl'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo($configAlias))
                  ->willReturn($config);

        $this->expectException(MissingConfigException::class);

        $factory = new ConfigReaderFactory(...$keys);
        $factory($container, $requestedName);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeWithNonArrayValue(): void
    {
        $configAlias = 'abc';
        $requestedName = 'def';
        $config = [
            'ghi' => [
                'jkl' => 'mno',
            ],
        ];
        $keys = ['ghi', 'jkl', 'foo'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo($configAlias))
                  ->willReturn($config);

        $this->expectException(MissingConfigException::class);

        $factory = new ConfigReaderFactory(...$keys);
        $factory($container, $requestedName);
    }
}
