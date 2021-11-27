<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Resolver;

use BluePsyduck\LaminasAutoWireFactory\Resolver\InjectAliasArray;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * The PHPUnit test of the InjectAliasArray class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Resolver\InjectAliasArray
 */
class InjectAliasArrayTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function testResolve(): void
    {
        $config = [
            'abc' => [
                'def' => ['ghi', 'jkl'],
            ],
        ];

        $object1 = $this->createMock(stdClass::class);
        $object2 = $this->createMock(stdClass::class);
        $expectedResult = [$object1, $object2];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
                  ->method('get')
                  ->willReturnMap([
                      ['config', $config],
                      ['ghi', $object1],
                      ['jkl', $object2],
                  ]);

        $instance = new InjectAliasArray('abc', 'def');

        $result = $instance->resolve($container);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testResolveWithSingleAlias(): void
    {
        $config = [
            'abc' => [
                'def' => 'ghi',
            ],
        ];

        $object1 = $this->createMock(stdClass::class);
        $expectedResult = [$object1];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
                  ->method('get')
                  ->willReturnMap([
                      ['config', $config],
                      ['ghi', $object1],
                  ]);

        $instance = new InjectAliasArray('abc', 'def');

        $result = $instance->resolve($container);
        $this->assertEquals($expectedResult, $result);
    }

    public function testSerialize(): void
    {
        $instance = new InjectAliasArray('abc', 'def');

        $result = unserialize(serialize($instance));
        $this->assertEquals($instance, $result);
    }
}
