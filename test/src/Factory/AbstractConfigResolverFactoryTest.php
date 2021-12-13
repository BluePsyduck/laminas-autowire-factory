<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Factory;

use BluePsyduck\LaminasAutoWireFactory\Factory\AbstractConfigResolverFactory;
use BluePsyduck\LaminasAutoWireFactory\Factory\AliasArrayInjectorFactory;
use BluePsyduck\LaminasAutoWireFactory\Factory\ConfigReaderFactory;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use stdClass;

/**
 * The PHPUnit test of the AbstractConfigResolverFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Factory\AbstractConfigResolverFactory
 */
class AbstractConfigResolverFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function testInvoke(): void
    {
        $keys = ['abc', 'def'];
        $object = $this->createMock(stdClass::class);
        $container = $this->createMock(ContainerInterface::class);

        $resolver = $this->createMock(ResolverInterface::class);
        $resolver->expects($this->once())
                 ->method('resolve')
                 ->with($this->identicalTo($container))
                 ->willReturn($object);

        $instance = $this->getMockBuilder(AbstractConfigResolverFactory::class)
                         ->onlyMethods(['createResolver'])
                         ->setConstructorArgs($keys)
                         ->getMockForAbstractClass();
        $instance->expects($this->once())
                 ->method('createResolver')
                 ->with($this->identicalTo($keys))
                 ->willReturn($resolver);

        $result = $instance($container, 'foo');
        $this->assertSame($object, $result);
    }

    /**
     * @return array<mixed>
     */
    public function provideSetState(): array
    {
        return [
            [AliasArrayInjectorFactory::class],
            [ConfigReaderFactory::class],
        ];
    }

    /**
     * @param class-string<AbstractConfigResolverFactory> $className
     * @dataProvider provideSetState
     */
    public function testSetState(string $className): void
    {
        $array = [
            'keys' => ['abc', 'def'],
        ];
        $expectedResult = new $className('abc', 'def');

        $result = $className::__set_state($array);
        $this->assertEquals($expectedResult, $result);
    }
}
