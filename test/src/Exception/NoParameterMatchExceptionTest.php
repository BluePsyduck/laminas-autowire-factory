<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Exception;

use BluePsyduck\LaminasAutoWireFactory\Exception\NoParameterMatchException;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * The PHPUnit test of the NoParameterMatchException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\LaminasAutoWireFactory\Exception\NoParameterMatchException
 */
class NoParameterMatchExceptionTest extends TestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $className = 'abc';
        $parameterName = 'def';
        $expectedMessage = 'Unable to auto-wire parameter def of class abc.';
        $previous = $this->createMock(Exception::class);

        $exception = new NoParameterMatchException($className, $parameterName, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
