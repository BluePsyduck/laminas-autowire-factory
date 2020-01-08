<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory\Exception;

use BluePsyduck\LaminasAutoWireFactory\Exception\MissingConfigException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * The PHPUnit test of the MissingConfigException class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\LaminasAutoWireFactory\Exception\MissingConfigException
 */
class MissingConfigExceptionTest extends TestCase
{
    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $keys = ['abc', 'def'];
        $expectedMessage = 'Failed to read config: abc -> def';

        /* @var Exception&MockObject $previous */
        $previous = $this->createMock(Exception::class);

        $exception = new MissingConfigException($keys, $previous);

        $this->assertSame($expectedMessage, $exception->getMessage());
        $this->assertSame($previous, $exception->getPrevious());
    }
}
