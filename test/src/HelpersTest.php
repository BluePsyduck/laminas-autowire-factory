<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\AliasArrayInjectorFactory;
use BluePsyduck\LaminasAutoWireFactory\ConfigReaderFactory;
use PHPUnit\Framework\TestCase;

use function BluePsyduck\LaminasAutoWireFactory\injectAliasArray;
use function BluePsyduck\LaminasAutoWireFactory\readConfig;

/**
 * The PHPUnit test of the helper functions.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 */
class HelpersTest extends TestCase
{
    /**
     * Tests the injectAliasArray method.
     * @covers \BluePsyduck\LaminasAutoWireFactory\injectAliasArray
     */
    public function testInjectAliasArray(): void
    {
        $expectedResult = new AliasArrayInjectorFactory('abc', 'def');

        $result = injectAliasArray('abc', 'def');

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the readConfig method.
     * @covers \BluePsyduck\LaminasAutoWireFactory\readConfig
     */
    public function testReadConfig(): void
    {
        $expectedResult = new ConfigReaderFactory('abc', 'def');

        $result = readConfig('abc', 'def');

        $this->assertEquals($expectedResult, $result);
    }
}
