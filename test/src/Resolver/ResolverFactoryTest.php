<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Resolver;

use BluePsyduck\LaminasAutoWireFactory\Attribute\ResolverAttribute;
use BluePsyduck\LaminasAutoWireFactory\Resolver\DefaultResolver;
use BluePsyduck\LaminasAutoWireFactory\Resolver\DefaultValueResolver;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverFactory;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithClassTypeHintConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithoutConstructor;
use PHPUnit\Framework\TestCase;
use ReflectionAttribute;
use ReflectionException;
use ReflectionParameter;

/**
 * The PHPUnit test of the ResolverFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverFactory
 */
class ResolverFactoryTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testCreateResolversForClass(): void
    {
        $resolver1 = $this->createMock(ResolverInterface::class);
        $resolver2 = $this->createMock(ResolverInterface::class);
        $className = ClassWithClassTypeHintConstructor::class;
        $expectedResult = [$resolver1, $resolver2];

        $instance = $this->getMockBuilder(ResolverFactory::class)
                         ->onlyMethods(['createResolverForParameter'])
                         ->getMock();
        $instance->expects($this->exactly(2))
                 ->method('createResolverForParameter')
                 ->withConsecutive(
                     [$this->isInstanceOf(ReflectionParameter::class)],
                     [$this->isInstanceOf(ReflectionParameter::class)],
                 )
                 ->willReturnOnConsecutiveCalls(
                     $resolver1,
                     $resolver2
                 );

        $result = $instance->createResolversForClass($className);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateResolversForClassWithoutConstructor(): void
    {
        $className = ClassWithoutConstructor::class;
        $expectedResult = [];

        $instance = $this->getMockBuilder(ResolverFactory::class)
                         ->onlyMethods(['createResolverForParameter'])
                         ->getMock();
        $instance->expects($this->never())
                 ->method('createResolverForParameter');

        $result = $instance->createResolversForClass($className);
        $this->assertSame($expectedResult, $result);
    }

    public function testCreateResolverForParameter(): void
    {
        $resolver = $this->createMock(ResolverInterface::class);

        $resolverAttribute = $this->createMock(ResolverAttribute::class);
        $resolverAttribute->expects($this->once())
                          ->method('createResolver')
                          ->willReturn($resolver);

        $attribute = $this->createMock(ReflectionAttribute::class);
        $attribute->expects($this->once())
                  ->method('newInstance')
                  ->willReturn($resolverAttribute);

        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->once())
                  ->method('getAttributes')
                  ->willReturn([$attribute, $this->createMock(ReflectionAttribute::class)]);

        $instance = new ResolverFactory();

        $result = $instance->createResolverForParameter($parameter);
        $this->assertSame($resolver, $result);
    }

    public function testCreateResolverForParameterWithoutAttributes(): void
    {
        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->once())
                  ->method('getAttributes')
                  ->willReturn([]);
        $parameter->expects($this->once())
                  ->method('isDefaultValueAvailable')
                  ->willReturn(false);

        $instance = new ResolverFactory();
        $result = $instance->createResolverForParameter($parameter);

        $this->assertInstanceOf(DefaultResolver::class, $result);
    }

    public function testCreateResolverForParameterWithDefaultValue(): void
    {
        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->once())
                  ->method('getAttributes')
                  ->willReturn([]);
        $parameter->expects($this->atLeastOnce())
                  ->method('isDefaultValueAvailable')
                  ->willReturn(true);

        $instance = new ResolverFactory();
        $result = $instance->createResolverForParameter($parameter);

        $this->assertInstanceOf(DefaultValueResolver::class, $result);
    }
}
