<?php

declare(strict_types=1);

namespace BluePsyduckIntegrationTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithClassTypeHintConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithoutConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithParameterlessConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithScalarTypeHintConstructor;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

/**
 * The integration test of the AutoWireFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\LaminasAutoWireFactory\AutoWireFactory
 */
class AutoWireFactoryIntegrationTest extends TestCase
{
    /**
     * Provides the data for the autoWiring test.
     * @return array<mixed>
     */
    public function provideAutoWiredClasses(): array
    {
        return [
            [ClassWithoutConstructor::class],
            [ClassWithParameterlessConstructor::class],
            [ClassWithClassTypeHintConstructor::class],
            [ClassWithScalarTypeHintConstructor::class],
        ];
    }

    /**
     * Creates the container for the test.
     * @return ContainerInterface
     */
    protected function createContainerWithExplicitFactories(): ContainerInterface
    {
        $config = new Config([
            'services' => [
                'string $property' => 'abc',
                'array $instances' => ['def', 'ghi'],
            ],
            'factories' => [
                ClassWithClassTypeHintConstructor::class => AutoWireFactory::class,
                ClassWithoutConstructor::class => AutoWireFactory::class,
                ClassWithParameterlessConstructor::class => AutoWireFactory::class,
                ClassWithScalarTypeHintConstructor::class => AutoWireFactory::class,
            ],
        ]);

        $container = new ServiceManager();
        $config->configureServiceManager($container);

        return $container;
    }

    /**
     * Tests the autoWiring method.
     * @dataProvider provideAutoWiredClasses
     * @param class-string $className
     */
    public function testAutoWiringWithExplicitFactories($className): void
    {
        $container = $this->createContainerWithExplicitFactories();
        $instance = $container->get($className);

        $this->assertInstanceOf($className, $instance);
    }

    /**
     * Creates the container for the test.
     * @return ContainerInterface
     */
    protected function createContainerWithAbstractFactory(): ContainerInterface
    {
        $config = new Config([
            'services' => [
                'string $property' => 'abc',
                'array $instances' => ['def', 'ghi'],
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
     * Tests the autoWiring method.
     * @dataProvider provideAutoWiredClasses
     * @param class-string $className
     */
    public function testAutoWiringWithAbstractFactory($className): void
    {
        $container = $this->createContainerWithAbstractFactory();
        $instance = $container->get($className);

        $this->assertInstanceOf($className, $instance);
    }
}
