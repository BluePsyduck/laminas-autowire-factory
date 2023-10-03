<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Resolver;

use BluePsyduck\LaminasAutoWireFactory\Resolver\DefaultValueResolver;
use BluePsyduck\TestHelper\ReflectionTrait;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use ReflectionException;
use ReflectionParameter;

/**
 * The PHPUnit test of the DefaultValueResolver class.
 *
 * @author ricwein <git@ricwein.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Resolver\DefaultValueResolver
 */
class DefaultValueResolverTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @throws ReflectionException
     */
    public function testSetParameter(): void
    {
        $expectedValue = 'default-value';

        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->any())
            ->method('isDefaultValueAvailable')
            ->willReturn(true);
        $parameter->expects($this->any())
            ->method('getDefaultValue')
            ->willReturn($expectedValue);

        $instance = new DefaultValueResolver();
        $instance->setParameter($parameter);

        $this->assertEquals($expectedValue, $this->extractProperty($instance, 'defaultValue'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function testResolve(): void
    {
        $expectedValue = 'default-value';

        $container = $this->createMock(ContainerInterface::class);

        $instance = new DefaultValueResolver();
        $this->injectProperty($instance, 'defaultValue', $expectedValue);
        $this->injectProperty($instance, 'hasDefaultValue', true);

        $result = $instance->resolve($container);
        $this->assertSame($expectedValue, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCanResolve(): void
    {
        $expectedValue = 'default-value';
        $container = $this->createMock(ContainerInterface::class);

        $instance = new DefaultValueResolver();
        $this->injectProperty($instance, 'defaultValue', $expectedValue);
        $this->injectProperty($instance, 'hasDefaultValue', true);

        $result = $instance->canResolve($container);
        $this->assertTrue($result);
    }

    /**
     * @throws ReflectionException
     */
    public function testCanResolveReturningFalse(): void
    {
        $expectedValue = 'default-value';
        $container = $this->createMock(ContainerInterface::class);

        $instance = new DefaultValueResolver();
        $this->injectProperty($instance, 'defaultValue', $expectedValue);
        $this->injectProperty($instance, 'hasDefaultValue', false);

        $result = $instance->canResolve($container);
        $this->assertFalse($result);
    }

    public function testSerialize(): void
    {
        $expectedValue = 'default-value';

        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->any())
            ->method('isDefaultValueAvailable')
            ->willReturn(true);
        $parameter->expects($this->any())
            ->method('getDefaultValue')
            ->willReturn($expectedValue);

        $instance = new DefaultValueResolver();
        $instance->setParameter($parameter);

        $result = unserialize(serialize($instance));
        $this->assertEquals($instance, $result);
    }
}
