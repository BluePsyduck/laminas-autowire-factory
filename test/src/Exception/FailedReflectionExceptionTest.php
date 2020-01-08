<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Exception;

use BluePsyduck\LaminasAutoWireFactory\Exception\FailedReflectionException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the FailedReflectionException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\LaminasAutoWireFactory\Exception\FailedReflectionException
 */
class FailedReflectionExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $className = 'abc';
        $expectedMessage = 'Failed to auto-wire abc: Unable to reflect class.';

        /* @var Exception&MockObject $previous */
        $previous = $this->createMock(Exception::class);

        $exception = new FailedReflectionException($className, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
