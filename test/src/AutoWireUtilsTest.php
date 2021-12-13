<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\AutoWireUtils;
use BluePsyduck\LaminasAutoWireFactory\Factory\AliasArrayInjectorFactory;
use BluePsyduck\LaminasAutoWireFactory\Factory\ConfigReaderFactory;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the AutoWireUtils class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 *
 * @covers \BluePsyduck\LaminasAutoWireFactory\AutoWireUtils
 */
class AutoWireUtilsTest extends TestCase
{
    public function testReadConfig(): void
    {
        $expectedResult = new ConfigReaderFactory('abc', 'def');

        $result = AutoWireUtils::readConfig('abc', 'def');
        $this->assertEquals($expectedResult, $result);
    }

    public function testInjectAliasArray(): void
    {
        $expectedResult = new AliasArrayInjectorFactory('abc', 'def');

        $result = AutoWireUtils::injectAliasArray('abc', 'def');
        $this->assertEquals($expectedResult, $result);
    }
}
