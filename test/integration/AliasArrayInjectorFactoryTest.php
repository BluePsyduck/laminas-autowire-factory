<?php

declare(strict_types=1);

namespace BluePsyduckIntegrationTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\Exception\MissingConfigException;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithoutConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithParameterlessConstructor;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

use function BluePsyduck\LaminasAutoWireFactory\injectAliasArray;

/**
 * The integration test of the AliasArrayInjectorFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\LaminasAutoWireFactory\AliasArrayInjectorFactory
 */
class AliasArrayInjectorFactoryTest extends TestCase
{
    /**
     * Creates a container with a test config.
     * @return ContainerInterface
     */
    protected function createContainerWithConfig(): ContainerInterface
    {
        $config = [
            'foo' => [
                'bar' => ['abc', 'def'],
            ],
        ];
        $dependencies = [
            'aliases' => [
                'abc' => ClassWithParameterlessConstructor::class,
                'def' => ClassWithoutConstructor::class,
            ],
            'invokables' => [
                ClassWithParameterlessConstructor::class => ClassWithParameterlessConstructor::class,
                ClassWithoutConstructor::class => ClassWithoutConstructor::class ,
            ],
        ];

        $container = new ServiceManager();
        $container->setService('config', $config);
        (new Config($dependencies))->configureServiceManager($container);

        return $container;
    }

    /**
     * Tests the injectAliasArray method.
     */
    public function testInjectAliasArray(): void
    {
        $container = $this->createContainerWithConfig();
        $expectedResult = [
            new ClassWithParameterlessConstructor(),
            new ClassWithoutConstructor(),
        ];

        $callable = injectAliasArray('foo', 'bar');
        $result = $callable($container, 'test');

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the injectAliasArray method.
     */
    public function testInjectAliasArrayWithError(): void
    {
        $container = $this->createContainerWithConfig();

        $this->expectException(MissingConfigException::class);

        $callable = injectAliasArray('unknown');
        $callable($container, 'test');
    }
}
