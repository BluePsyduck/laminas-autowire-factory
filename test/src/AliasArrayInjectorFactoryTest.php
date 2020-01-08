<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\AliasArrayInjectorFactory;
use BluePsyduck\LaminasAutoWireFactory\ConfigReaderFactory;
use BluePsyduck\TestHelper\ReflectionTrait;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
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
     * Tests the __set_state method.
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
     * Tests the __set_state method.
     * @covers ::__set_state
     */
    public function testSetStateWithoutArray(): void
    {
        $expectedResult = new AliasArrayInjectorFactory();

        $result = AliasArrayInjectorFactory::__set_state([]);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the constructing.
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
     * Tests the invoking.
     * @throws ReflectionException
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

        /* @var stdClass&MockObject $instance1 */
        $instance1 = $this->createMock(stdClass::class);
        /* @var stdClass&MockObject $instance2 */
        $instance2 = $this->createMock(stdClass::class);

        $expectedResult = [
            'pqr' => $instance1,
            'vwx' => $instance2,
        ];

        /* @var ContainerInterface&MockObject $container */
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


        /* @var ConfigReaderFactory&MockObject $configReaderFactory */
        $configReaderFactory = $this->createMock(ConfigReaderFactory::class);
        $configReaderFactory->expects($this->once())
                            ->method('__invoke')
                            ->with(
                                $this->identicalTo($container),
                                $this->identicalTo($requestedName),
                                $this->identicalTo($options)
                            )
                            ->willReturn($aliases);

        /* @var AliasArrayInjectorFactory&MockObject $factory */
        $factory = $this->getMockBuilder(AliasArrayInjectorFactory::class)
                        ->setMethods(['createConfigReaderFactory'])
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
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvokeWithSingleAlias(): void
    {
        $configKeys = ['abc', 'def'];
        $requestedName = 'ghi';
        $options = ['jkl', 'mno'];
        $aliases = 'stu';

        /* @var stdClass&MockObject $instance */
        $instance = $this->createMock(stdClass::class);

        $expectedResult = [$instance];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('stu'))
                  ->willReturn($instance);

        /* @var ConfigReaderFactory&MockObject $configReaderFactory */
        $configReaderFactory = $this->createMock(ConfigReaderFactory::class);
        $configReaderFactory->expects($this->once())
                            ->method('__invoke')
                            ->with(
                                $this->identicalTo($container),
                                $this->identicalTo($requestedName),
                                $this->identicalTo($options)
                            )
                            ->willReturn($aliases);

        /* @var AliasArrayInjectorFactory&MockObject $factory */
        $factory = $this->getMockBuilder(AliasArrayInjectorFactory::class)
                        ->setMethods(['createConfigReaderFactory'])
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
     * Tests the createConfigReaderFactory method.
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
