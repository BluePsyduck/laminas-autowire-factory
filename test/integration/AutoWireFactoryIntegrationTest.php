<?php

declare(strict_types=1);

namespace BluePsyduckIntegrationTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithAttributes;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithClassTypeHintConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithoutConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithParameterlessConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithScalarTypeHintConstructor;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * The integration test of the AutoWireFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\AutoWireFactory
 */
class AutoWireFactoryIntegrationTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function provideAutoWiredClasses(): array
    {
        return [
            [ClassWithoutConstructor::class],
            [ClassWithParameterlessConstructor::class],
            [ClassWithClassTypeHintConstructor::class],
            [ClassWithScalarTypeHintConstructor::class],
            [ClassWithAttributes::class],
        ];
    }

    private function createContainerWithExplicitFactories(): ContainerInterface
    {
        // @phpstan-ignore-next-line
        $config = new Config([
            'services' => [
                'string $property' => 'abc',
                'array $instances' => ['def', 'ghi'],
                'test.foo' => new ClassWithoutConstructor(),
                'config' => [
                    'test' => [
                        'property' => 'jkl',
                        'instances' => [
                            ClassWithoutConstructor::class,
                            ClassWithParameterlessConstructor::class,
                        ],
                    ],
                ],
            ],
            'factories' => [
                ClassWithClassTypeHintConstructor::class => AutoWireFactory::class,
                ClassWithoutConstructor::class => AutoWireFactory::class,
                ClassWithParameterlessConstructor::class => AutoWireFactory::class,
                ClassWithScalarTypeHintConstructor::class => AutoWireFactory::class,
                ClassWithAttributes::class => AutoWireFactory::class,
            ],
        ]);

        $container = new ServiceManager();
        $config->configureServiceManager($container);

        return $container;
    }

    /**
     * @param class-string $className
     * @throws ContainerExceptionInterface
     * @dataProvider provideAutoWiredClasses
     */
    public function testAutoWiringWithExplicitFactories(string $className): void
    {
        $container = $this->createContainerWithExplicitFactories();
        $instance = $container->get($className);

        $this->assertInstanceOf($className, $instance);
    }

    protected function createContainerWithAbstractFactory(): ContainerInterface
    {
        // @phpstan-ignore-next-line
        $config = new Config([
            'services' => [
                'string $property' => 'abc',
                'array $instances' => ['def', 'ghi'],
                'test.foo' => new ClassWithoutConstructor(),
                'config' => [
                    'test' => [
                        'property' => 'jkl',
                        'instances' => [
                            ClassWithoutConstructor::class,
                            ClassWithParameterlessConstructor::class,
                        ],
                    ],
                ],
            ],
            'abstract_factories' => [
                AutoWireFactory::class,
            ],
        ]);

        $container = new ServiceManager();
        $config->configureServiceManager($container);

        return $container;
    }

    /**
     * @param class-string $className
     * @throws ContainerExceptionInterface
     * @dataProvider provideAutoWiredClasses
     */
    public function testAutoWiringWithAbstractFactory(string $className): void
    {
        $container = $this->createContainerWithAbstractFactory();
        $instance = $container->get($className);

        $this->assertInstanceOf($className, $instance);
    }
}
