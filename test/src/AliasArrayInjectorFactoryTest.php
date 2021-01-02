<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\AliasArrayInjectorFactory;
use BluePsyduck\LaminasAutoWireFactory\ConfigReaderFactory;
use BluePsyduck\TestHelper\ReflectionTrait;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

/**
 * The PHPUnit test of the AliasArrayInjectorFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\LaminasAutoWireFactory\AliasArrayInjectorFactory
 */
class AliasArrayInjectorFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @covers ::__set_state
     */
    public function testSetState(): void
    {
        $array = [
            'configKeys' => ['abc', 'def'],
        ];
        $expectedResult = new AliasArrayInjectorFactory('abc', 'def');

        $result = AliasArrayInjectorFactory::__set_state($array);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers ::__set_state
     */
    public function testSetStateWithoutArray(): void
    {
        $expectedResult = new AliasArrayInjectorFactory();

        $result = AliasArrayInjectorFactory::__set_state([]);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $expectedConfigKeys = ['abc', 'def'];

        $factory = new AliasArrayInjectorFactory('abc', 'def');

        $this->assertSame($expectedConfigKeys, $this->extractProperty($factory, 'configKeys'));
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $configKeys = ['abc', 'def'];
        $requestedName = 'ghi';
        $options = ['jkl', 'mno'];
        $aliases = [
            'pqr' => 'stu',
            'vwx' => 'yza',
        ];

        $instance1 = $this->createMock(stdClass::class);
        $instance2 = $this->createMock(stdClass::class);

        $expectedResult = [
            'pqr' => $instance1,
            'vwx' => $instance2,
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
                  ->method('get')
                  ->withConsecutive(
                      [$this->identicalTo('stu')],
                      [$this->identicalTo('yza')]
                  )
                  ->willReturnOnConsecutiveCalls(
                      $instance1,
                      $instance2
                  );


        $configReaderFactory = $this->createMock(ConfigReaderFactory::class);
        $configReaderFactory->expects($this->once())
                            ->method('__invoke')
                            ->with(
                                $this->identicalTo($container),
                                $this->identicalTo($requestedName),
                                $this->identicalTo($options)
                            )
                            ->willReturn($aliases);

        $factory = $this->getMockBuilder(AliasArrayInjectorFactory::class)
                        ->onlyMethods(['createConfigReaderFactory'])
                        ->setConstructorArgs($configKeys)
                        ->getMock();
        $factory->expects($this->once())
                ->method('createConfigReaderFactory')
                ->with($this->identicalTo($configKeys))
                ->willReturn($configReaderFactory);

        $result = $factory($container, $requestedName, $options);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeWithSingleAlias(): void
    {
        $configKeys = ['abc', 'def'];
        $requestedName = 'ghi';
        $options = ['jkl', 'mno'];
        $aliases = 'stu';

        $instance = $this->createMock(stdClass::class);

        $expectedResult = [$instance];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('stu'))
                  ->willReturn($instance);

        $configReaderFactory = $this->createMock(ConfigReaderFactory::class);
        $configReaderFactory->expects($this->once())
                            ->method('__invoke')
                            ->with(
                                $this->identicalTo($container),
                                $this->identicalTo($requestedName),
                                $this->identicalTo($options)
                            )
                            ->willReturn($aliases);

        $factory = $this->getMockBuilder(AliasArrayInjectorFactory::class)
                        ->onlyMethods(['createConfigReaderFactory'])
                        ->setConstructorArgs($configKeys)
                        ->getMock();
        $factory->expects($this->once())
                ->method('createConfigReaderFactory')
                ->with($this->identicalTo($configKeys))
                ->willReturn($configReaderFactory);

        $result = $factory($container, $requestedName, $options);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::createConfigReaderFactory
     */
    public function testCreateConfigReaderFactory(): void
    {
        $configKeys = ['abc', 'def'];
        $expectedResult = new ConfigReaderFactory('abc', 'def');

        $factory = new AliasArrayInjectorFactory('abc', 'def');
        $result = $this->invokeMethod($factory, 'createConfigReaderFactory', $configKeys);

        $this->assertEquals($expectedResult, $result);
    }
}
