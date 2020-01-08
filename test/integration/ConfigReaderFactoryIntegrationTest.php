<?php

declare(strict_types=1);

namespace BluePsyduckIntegrationTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\Exception\MissingConfigException;
use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Laminas\ServiceManager\ServiceManager;

use function BluePsyduck\LaminasAutoWireFactory\readConfig;

/**
 * The integration test of the ConfigReaderFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class ConfigReaderFactoryIntegrationTest extends TestCase
{
    /**
     * Creates a container with a test config.
     * @return ContainerInterface
     */
    protected function createContainerWithConfig(): ContainerInterface
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
     * Provides the data for the readConfigFactory test.
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
     * Tests the readConfig function.
     * @param array<string> $keys
     * @param mixed $expectedResult
     * @dataProvider provideReadConfigFactory
     */
    public function testReadConfig(array $keys, $expectedResult): void
    {
        $container = $this->createContainerWithConfig();

        $callable = readConfig(...$keys);
        $result = $callable($container, 'foo');

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the readConfig function.
     */
    public function testReadConfigWithException(): void
    {
        $container = $this->createContainerWithConfig();

        $this->expectException(MissingConfigException::class);

        $callable = readConfig('abc', 'foo', 'bar');
        $callable($container, 'foo');
    }
}
