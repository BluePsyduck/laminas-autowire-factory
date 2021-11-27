<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Resolver;

use BluePsyduck\LaminasAutoWireFactory\Resolver\Alias;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * The PHPUnit test of the Alias class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Resolver\Alias
 */
class AliasTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     */
    public function testResolve(): void
    {
        $alias = 'abc';
        $value = new stdClass();

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo($alias))
                  ->willReturn($value);

        $instance = new Alias($alias);

        $result = $instance->resolve($container);
        $this->assertSame($value, $result);
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
        $alias = 'abc';

        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('has')
                  ->with($this->identicalTo($alias))
                  ->willReturn($resultHas);

        $instance = new Alias($alias);

        $result = $instance->canResolve($container);
        $this->assertSame($expectedResult, $result);
    }

    public function testSerialize(): void
    {
        $instance = new Alias('abc');

        $result = unserialize(serialize($instance));
        $this->assertEquals($instance, $result);
    }
}
