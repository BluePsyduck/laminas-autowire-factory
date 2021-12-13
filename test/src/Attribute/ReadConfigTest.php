<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Attribute;

use BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ConfigResolver;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the ReadConfig class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\Attribute\ReadConfig
 */
class ReadConfigTest extends TestCase
{
    public function testCreateResolver(): void
    {
        $configKeys = ['abc', 'def'];
        $expectedResult = new ConfigResolver($configKeys);

        $instance = new ReadConfig(...$configKeys);
        $result = $instance->createResolver();

        $this->assertEquals($expectedResult, $result);
    }
}
