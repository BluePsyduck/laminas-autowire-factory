<?php

declare(strict_types=1);

namespace BluePsyduckIntegrationTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\AutoWireUtils;
use BluePsyduck\LaminasAutoWireFactory\Exception\MissingConfigException;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithoutConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithParameterlessConstructor;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

/**
 * The integration test of the AliasArrayInjectorFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\AliasArrayInjectorFactory
 */
class AliasArrayInjectorFactoryTest extends TestCase
{
    private function createContainerWithConfig(): ContainerInterface
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
     * @throws ContainerExceptionInterface
     */
    public function testInjectAliasArray(): void
    {
        $container = $this->createContainerWithConfig();
        $expectedResult = [
            new ClassWithParameterlessConstructor(),
            new ClassWithoutConstructor(),
        ];

        $callable = AutoWireUtils::injectAliasArray('foo', 'bar');
        $result = $callable($container, 'test');

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testInjectAliasArrayWithError(): void
    {
        $container = $this->createContainerWithConfig();

        $this->expectException(MissingConfigException::class);

        $callable = AutoWireUtils::injectAliasArray('unknown');
        $callable($container, 'test');
    }
}
