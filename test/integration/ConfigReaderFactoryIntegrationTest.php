<?php

declare(strict_types=1);

namespace BluePsyduckIntegrationTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\AutoWireUtils;
use BluePsyduck\LaminasAutoWireFactory\Exception\MissingConfigException;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * The integration test of the ConfigReaderFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Factory\ConfigReaderFactory
 */
class ConfigReaderFactoryIntegrationTest extends TestCase
{
    private function createContainerWithConfig(): ContainerInterface
    {
        $config = [
            'abc' => [
                'def' => 'ghi',
                'jkl' => 42,
                'mno' => ['pqr', 'stu'],
            ],
            'vwx' => null,
        ];

        $container = new ServiceManager();
        $container->setService('config', $config);
        return $container;
    }

    /**
     * @return array<mixed>
     */
    public function provideReadConfigFactory(): array
    {
        return [
            [['abc', 'def'], 'ghi'],
            [['abc', 'jkl'], 42],
            [['abc', 'mno'], ['pqr', 'stu']],
            [['vwx'], null],
        ];
    }

    /**
     * @param array<array-key> $keys
     * @param mixed $expectedResult
     * @throws ContainerExceptionInterface
     * @dataProvider provideReadConfigFactory
     */
    public function testReadConfig(array $keys, mixed $expectedResult): void
    {
        $container = $this->createContainerWithConfig();

        $callable = AutoWireUtils::readConfig(...$keys);
        $result = $callable($container, 'foo');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testReadConfigWithException(): void
    {
        $container = $this->createContainerWithConfig();

        $this->expectException(MissingConfigException::class);

        $callable = AutoWireUtils::readConfig('abc', 'foo', 'bar');
        $callable($container, 'foo');
    }
}
