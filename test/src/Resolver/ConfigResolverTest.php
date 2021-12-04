<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Resolver;

use BluePsyduck\LaminasAutoWireFactory\Exception\MissingConfigException;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ConfigResolver;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * The PHPUnit test of the ReadConfig class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Resolver\ConfigResolver
 */
class ConfigResolverTest extends TestCase
{
    /**
     * @return array<mixed>
     */
    public function provideResolve(): array
    {
        return [
            [['abc'], false, 'def'],
            [['ghi', 'jkl'], false, 'mno'],
            [['ghi', 'abc'], false, 'pqr'],
            [['abc', 'ghi'], true, null],
            [['foo'], true, null]
        ];
    }

    /**
     * @param array<string> $keys
     * @param bool $expectException
     * @param string|null $expectedResult
     * @throws ContainerExceptionInterface
     * @dataProvider provideResolve
     */
    public function testResolve(array $keys, bool $expectException, ?string $expectedResult): void
    {
        $config = [
            'abc' => 'def',
            'ghi' => [
                'jkl' => 'mno',
                'abc' => 'pqr',
            ],
        ];

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('config'))
                  ->willReturn($config);

        if ($expectException) {
            $this->expectException(MissingConfigException::class);
        }

        $instance = new ConfigResolver($keys);

        $result = $instance->resolve($container);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return array<mixed>
     */
    public function provideCanResolve(): array
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    /**
     * @dataProvider provideCanResolve
     */
    public function testCanResolve(bool $resultHas, bool $expectedResult): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('has')
                  ->with($this->identicalTo('config'))
                  ->willReturn($resultHas);

        $instance = new ConfigResolver(['abc', 'def']);

        $result = $instance->canResolve($container);
        $this->assertSame($expectedResult, $result);
    }

    public function testSerialize(): void
    {
        $instance = new ConfigResolver(['abc', 'def', 'ghi']);

        $result = unserialize(serialize($instance));
        $this->assertEquals($instance, $result);
    }
}
