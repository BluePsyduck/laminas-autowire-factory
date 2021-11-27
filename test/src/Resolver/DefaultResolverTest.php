<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Resolver;

use BluePsyduck\LaminasAutoWireFactory\Exception\NoParameterMatchException;
use BluePsyduck\LaminasAutoWireFactory\Resolver\DefaultResolver;
use BluePsyduck\TestHelper\ReflectionTrait;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use stdClass;

/**
 * The PHPUnit test of the DefaultResolver class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Resolver\DefaultResolver
 */
class DefaultResolverTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @throws ReflectionException
     */
    public function testSetParameter(): void
    {
        $parameterName = 'abc';
        $typeName = 'def';
        $expectedAliases = ['def $abc', 'def', '$abc'];

        $type = $this->createMock(ReflectionNamedType::class);
        $type->expects($this->any())
             ->method('getName')
             ->willReturn($typeName);
        $type->expects($this->any())
             ->method('isBuiltin')
             ->willReturn(false);

        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->any())
                  ->method('getName')
                  ->willReturn($parameterName);
        $parameter->expects($this->any())
                  ->method('getType')
                  ->willReturn($type);

        $instance = new DefaultResolver();
        $instance->setParameter($parameter);

        $this->assertEquals($expectedAliases, $this->extractProperty($instance, 'aliases'));
    }

    /**
     * @throws ReflectionException
     */
    public function testSetParameterWithBuiltInType(): void
    {
        $parameterName = 'abc';
        $typeName = 'string';
        $expectedAliases = ['string $abc', '$abc'];

        $type = $this->createMock(ReflectionNamedType::class);
        $type->expects($this->any())
             ->method('getName')
             ->willReturn($typeName);
        $type->expects($this->any())
             ->method('isBuiltin')
             ->willReturn(true);

        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->any())
                  ->method('getName')
                  ->willReturn($parameterName);
        $parameter->expects($this->any())
                  ->method('getType')
                  ->willReturn($type);

        $instance = new DefaultResolver();
        $instance->setParameter($parameter);

        $this->assertEquals($expectedAliases, $this->extractProperty($instance, 'aliases'));
    }


    /**
     * @throws ReflectionException
     */
    public function testSetParameterWithoutNamedType(): void
    {
        $parameterName = 'abc';
        $expectedAliases = ['$abc'];

        $type = $this->createMock(ReflectionType::class);

        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->any())
                  ->method('getName')
                  ->willReturn($parameterName);
        $parameter->expects($this->any())
                  ->method('getType')
                  ->willReturn($type);

        $instance = new DefaultResolver();
        $instance->setParameter($parameter);

        $this->assertEquals($expectedAliases, $this->extractProperty($instance, 'aliases'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testResolve(): void
    {
        $aliases = ['abc', 'def', 'ghi'];
        $object = $this->createMock(stdClass::class);

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
                  ->method('has')
                  ->willReturnMap([
                      ['abc', false],
                      ['def', true],
                      ['ghi', true],
                  ]);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('def'))
                  ->willReturn($object);

        $instance = new DefaultResolver();
        $this->injectProperty($instance, 'aliases', $aliases);

        $result = $instance->resolve($container);
        $this->assertSame($object, $result);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testResolveWithException(): void
    {
        $aliases = ['abc', 'def'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
                  ->method('has')
                  ->willReturnMap([
                      ['abc', false],
                      ['def', false],
                  ]);
        $container->expects($this->never())
                  ->method('get');

        $this->expectException(NoParameterMatchException::class);

        $instance = new DefaultResolver();
        $this->injectProperty($instance, 'aliases', $aliases);

        $instance->resolve($container);
    }

    /**
     * @throws ReflectionException
     */
    public function testCanResolve(): void
    {
        $aliases = ['abc', 'def', 'ghi'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
                  ->method('has')
                  ->willReturnMap([
                      ['abc', false],
                      ['def', true],
                      ['ghi', false],
                  ]);

        $instance = new DefaultResolver();
        $this->injectProperty($instance, 'aliases', $aliases);

        $result = $instance->canResolve($container);
        $this->assertTrue($result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCanResolveReturningFalse(): void
    {
        $aliases = ['abc', 'def', 'ghi'];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
                  ->method('has')
                  ->willReturnMap([
                      ['abc', false],
                      ['def', false],
                      ['ghi', false],
                  ]);

        $instance = new DefaultResolver();
        $this->injectProperty($instance, 'aliases', $aliases);

        $result = $instance->canResolve($container);
        $this->assertFalse($result);
    }
}
