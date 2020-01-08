<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use BluePsyduck\LaminasAutoWireFactory\Exception\FailedReflectionException;
use BluePsyduck\LaminasAutoWireFactory\Exception\NoParameterMatchException;
use BluePsyduck\LaminasAutoWireFactory\ParameterAliasResolver;
use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithClassTypeHintConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithoutConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithParameterlessConstructor;
use Interop\Container\ContainerInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use stdClass;

/**
 * The PHPUnit test of the AutoWireFactory class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\LaminasAutoWireFactory\AutoWireFactory
 */
class AutoWireFactoryTest extends TestCase
{
    use ReflectionTrait;

    /**
     * The mocked parameter alias resolver.
     * @var ParameterAliasResolver&MockObject
     */
    protected $parameterAliasResolver;

    /**
     * Sets up the test case.
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->parameterAliasResolver = $this->createMock(ParameterAliasResolver::class);
    }

    /**
     * Tests the setCacheFile method.
     * @throws ReflectionException
     * @runInSeparateProcess // Unable to backup state of ParameterAliasResolver with @backupStaticAttributes
     * @covers ::setCacheFile
     */
    public function testSetCacheFile(): void
    {
        $root = vfsStream::setup('root');
        $root->addChild(vfsStream::newFile('cache-file'));

        $cacheFile = vfsStream::url('root/cache-file');

        $parameterAliasesCache = [
            'abc' => [
                'def' => ['ghi', 'jkl'],
                'mno' => ['pqr', 'stu'],
            ],
            'vwx' => [],
        ];
        file_put_contents($cacheFile, sprintf('<?php return %s;', var_export($parameterAliasesCache, true)));

        AutoWireFactory::setCacheFile($cacheFile);

        $this->assertEquals(
            $parameterAliasesCache,
            $this->extractProperty(ParameterAliasResolver::class, 'parameterAliasesCache')
        );
    }

    /**
     * Tests the constructing.
     * @throws ReflectionException
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $factory = new AutoWireFactory();

        $this->assertInstanceOf(
            ParameterAliasResolver::class,
            $this->extractProperty($factory, 'parameterAliasResolver')
        );
    }
    
    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $requestedName = 'abc';
        $parameterAliases = [
            'def' => ['ghi', 'jkl'],
        ];
        $parameters = [
            $this->createMock(stdClass::class),
            $this->createMock(stdClass::class),
        ];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        /* @var stdClass&MockObject $instance */
        $instance = $this->createMock(stdClass::class);

        $this->parameterAliasResolver->expects($this->once())
                                     ->method('getParameterAliasesForConstructor')
                                     ->with($this->identicalTo($requestedName))
                                     ->willReturn($parameterAliases);
        
        /* @var AutoWireFactory&MockObject $factory */
        $factory = $this->getMockBuilder(AutoWireFactory::class)
                        ->setMethods(['createParameterInstances', 'createInstance'])
                        ->getMock();
        $factory->expects($this->once())
                ->method('createParameterInstances')
                ->with(
                    $this->identicalTo($container),
                    $this->identicalTo($requestedName),
                    $this->identicalTo($parameterAliases)
                )
                ->willReturn($parameters);
        $factory->expects($this->once())
                ->method('createInstance')
                ->with($this->identicalTo($requestedName), $this->identicalTo($parameters))
                ->willReturn($instance);
        $this->injectProperty($factory, 'parameterAliasResolver', $this->parameterAliasResolver);

        $result = $factory($container, $requestedName);

        $this->assertSame($instance, $result);
    }

    /**
     * Tests the invoking.
     * @throws ReflectionException
     * @covers ::__invoke
     */
    public function testInvokeWithException(): void
    {
        $requestedName = 'abc';

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $this->parameterAliasResolver->expects($this->once())
                                     ->method('getParameterAliasesForConstructor')
                                     ->with($this->identicalTo($requestedName))
                                     ->willThrowException($this->createMock(ReflectionException::class));

        $this->expectException(FailedReflectionException::class);

        /* @var AutoWireFactory&MockObject $factory */
        $factory = $this->getMockBuilder(AutoWireFactory::class)
                        ->setMethods(['createParameterInstances', 'createInstance'])
                        ->getMock();
        $factory->expects($this->never())
                ->method('createParameterInstances');
        $factory->expects($this->never())
                ->method('createInstance');
        $this->injectProperty($factory, 'parameterAliasResolver', $this->parameterAliasResolver);

        $factory($container, $requestedName);
    }

    /**
     * Tests the createParameterInstances method.
     * @throws ReflectionException
     * @covers ::createParameterInstances
     */
    public function testCreateParameterInstances(): void
    {
        $className = 'abc';
        $parameterAliases = [
            'def' => ['ghi', 'jkl'],
            'mno' => ['pqr', 'stu'],
        ];
        
        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        /* @var stdClass&MockObject $instance1 */
        $instance1 = $this->createMock(stdClass::class);
        /* @var stdClass&MockObject $instance2 */
        $instance2 = $this->createMock(stdClass::class);

        $expectedResult = [$instance1, $instance2];
        
        /* @var AutoWireFactory&MockObject $factory */
        $factory = $this->getMockBuilder(AutoWireFactory::class)
                        ->setMethods(['createInstanceOfFirstAvailableAlias'])
                        ->getMock();
        $factory->expects($this->exactly(2))
                ->method('createInstanceOfFirstAvailableAlias')
                ->withConsecutive(
                    [
                        $this->identicalTo($container),
                        $this->identicalTo($className),
                        $this->identicalTo('def'),
                        $this->identicalTo(['ghi', 'jkl'])
                    ],
                    [
                        $this->identicalTo($container),
                        $this->identicalTo($className),
                        $this->identicalTo('mno'),
                        $this->identicalTo(['pqr', 'stu'])
                    ]
                )
                ->willReturnOnConsecutiveCalls(
                    $instance1,
                    $instance2
                );

        $result = $this->invokeMethod($factory, 'createParameterInstances', $container, $className, $parameterAliases);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * Tests the createInstanceOfFirstAvailableAlias method.
     * @throws ReflectionException
     * @covers ::createInstanceOfFirstAvailableAlias
     */
    public function testCreateInstanceOfFirstAvailableAlias(): void
    {
        $className = 'abc';
        $parameterName = 'def';
        $aliases = ['ghi', 'jkl', 'mno'];

        /* @var stdClass&MockObject $instance */
        $instance = $this->createMock(stdClass::class);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
                  ->method('has')
                  ->withConsecutive(
                      [$this->identicalTo('ghi')],
                      [$this->identicalTo('jkl')]
                  )
                  ->willReturnOnConsecutiveCalls(
                      false,
                      true
                  );
        $container->expects($this->once())
                  ->method('get')
                  ->with($this->identicalTo('jkl'))
                  ->willReturn($instance);

        $factory = new AutoWireFactory();
        $result = $this->invokeMethod(
            $factory,
            'createInstanceOfFirstAvailableAlias',
            $container,
            $className,
            $parameterName,
            $aliases
        );

        $this->assertEquals($instance, $result);
    }

    /**
     * Tests the createInstanceOfFirstAvailableAlias method.
     * @throws ReflectionException
     * @covers ::createInstanceOfFirstAvailableAlias
     */
    public function testCreateInstanceOfFirstAvailableAliasWithoutMatch(): void
    {
        $className = 'abc';
        $parameterName = 'def';
        $aliases = ['ghi'];

        /* @var stdClass&MockObject $instance */
        $instance = $this->createMock(stdClass::class);

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('has')
                  ->with($this->identicalTo('ghi'))
                  ->willReturn(false);
        $container->expects($this->never())
                  ->method('get');

        $this->expectException(NoParameterMatchException::class);

        $factory = new AutoWireFactory();
        $result = $this->invokeMethod(
            $factory,
            'createInstanceOfFirstAvailableAlias',
            $container,
            $className,
            $parameterName,
            $aliases
        );

        $this->assertEquals($instance, $result);
    }

    /**
     * Tests the createInstance method.
     * @throws ReflectionException
     * @covers ::createInstance
     */
    public function testCreateInstance(): void
    {
        /* @var ClassWithoutConstructor&MockObject $foo */
        $foo = $this->createMock(ClassWithoutConstructor::class);
        /* @var ClassWithParameterlessConstructor&MockObject $bar */
        $bar = $this->createMock(ClassWithParameterlessConstructor::class);

        $className = ClassWithClassTypeHintConstructor::class;
        $parameters = [$foo, $bar];
        $expectedResult = new ClassWithClassTypeHintConstructor($foo, $bar);

        $factory = new AutoWireFactory();
        $result = $this->invokeMethod($factory, 'createInstance', $className, $parameters);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Tests the canCreate method.
     * @throws ReflectionException
     * @covers ::canCreate
     */
    public function testCanCreate(): void
    {
        $requestedName = __CLASS__;
        $parameterAliases = [
            'abc' => ['def', 'ghi'],
        ];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $this->parameterAliasResolver->expects($this->once())
                                     ->method('getParameterAliasesForConstructor')
                                     ->with($this->identicalTo($requestedName))
                                     ->willReturn($parameterAliases);

        /* @var AutoWireFactory&MockObject $factory */
        $factory = $this->getMockBuilder(AutoWireFactory::class)
                        ->setMethods(['canAutoWire'])
                        ->getMock();
        $factory->expects($this->once())
                ->method('canAutoWire')
                ->with($this->identicalTo($container), $this->identicalTo($parameterAliases))
                ->willReturn(true);
        $this->injectProperty($factory, 'parameterAliasResolver', $this->parameterAliasResolver);

        $result = $factory->canCreate($container, $requestedName);

        $this->assertTrue($result);
    }

    /**
     * Tests the canCreate method.
     * @throws ReflectionException
     * @covers ::canCreate
     */
    public function testCanCreateWithException(): void
    {
        $requestedName = __CLASS__;

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $this->parameterAliasResolver->expects($this->once())
                                     ->method('getParameterAliasesForConstructor')
                                     ->with($this->identicalTo($requestedName))
                                     ->willThrowException($this->createMock(ReflectionException::class));

        /* @var AutoWireFactory&MockObject $factory */
        $factory = $this->getMockBuilder(AutoWireFactory::class)
                        ->setMethods(['canAutoWire'])
                        ->getMock();
        $factory->expects($this->never())
                ->method('canAutoWire');
        $this->injectProperty($factory, 'parameterAliasResolver', $this->parameterAliasResolver);

        $result = $factory->canCreate($container, $requestedName);

        $this->assertFalse($result);
    }

    /**
     * Tests the canCreate method.
     * @throws ReflectionException
     * @covers ::canCreate
     */
    public function testCanCreateWithoutClass(): void
    {
        $requestedName = 'array';

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        $this->parameterAliasResolver->expects($this->never())
                                     ->method('getParameterAliasesForConstructor');

        /* @var AutoWireFactory&MockObject $factory */
        $factory = $this->getMockBuilder(AutoWireFactory::class)
                        ->setMethods(['canAutoWire'])
                        ->getMock();
        $factory->expects($this->never())
                ->method('canAutoWire');
        $this->injectProperty($factory, 'parameterAliasResolver', $this->parameterAliasResolver);

        $result = $factory->canCreate($container, $requestedName);

        $this->assertFalse($result);
    }

    /**
     * Tests the canAutoWire method.
     * @throws ReflectionException
     * @covers ::canAutoWire
     */
    public function testCanAutoWire(): void
    {
        $parameterAliases = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno', 'pqr'],
        ];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        /* @var AutoWireFactory&MockObject $factory */
        $factory = $this->getMockBuilder(AutoWireFactory::class)
                        ->setMethods(['hasAnyAlias'])
                        ->getMock();
        $factory->expects($this->exactly(2))
                ->method('hasAnyAlias')
                ->withConsecutive(
                    [$this->identicalTo($container), $this->identicalTo(['def', 'ghi'])],
                    [$this->identicalTo($container), $this->identicalTo(['mno', 'pqr'])]
                )
                ->willReturnOnConsecutiveCalls(
                    true,
                    true
                );

        $result = $this->invokeMethod($factory, 'canAutoWire', $container, $parameterAliases);

        $this->assertTrue($result);
    }

    /**
     * Tests the canAutoWire method.
     * @throws ReflectionException
     * @covers ::canAutoWire
     */
    public function testCanAutoWireWithoutHavingAliases(): void
    {
        $parameterAliases = [
            'abc' => ['def', 'ghi'],
            'jkl' => ['mno', 'pqr'],
        ];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);

        /* @var AutoWireFactory&MockObject $factory */
        $factory = $this->getMockBuilder(AutoWireFactory::class)
                        ->setMethods(['hasAnyAlias'])
                        ->getMock();
        $factory->expects($this->once())
                ->method('hasAnyAlias')
                ->with($this->identicalTo($container), $this->identicalTo(['def', 'ghi']))
                ->willReturn(false);

        $result = $this->invokeMethod($factory, 'canAutoWire', $container, $parameterAliases);

        $this->assertFalse($result);
    }

    /**
     * Tests the hasAnyAlias method.
     * @throws ReflectionException
     * @covers ::hasAnyAlias
     */
    public function testHasAnyAliasWithMatch(): void
    {
        $aliases = ['abc', 'def', 'ghi'];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->exactly(2))
                  ->method('has')
                  ->withConsecutive(
                      [$this->identicalTo('abc')],
                      [$this->identicalTo('def')]
                  )
                  ->willReturnOnConsecutiveCalls(
                      false,
                      true
                  );

        $factory = new AutoWireFactory();
        $result = $this->invokeMethod($factory, 'hasAnyAlias', $container, $aliases);

        $this->assertTrue($result);
    }

    /**
     * Tests the hasAnyAlias method.
     * @throws ReflectionException
     * @covers ::hasAnyAlias
     */
    public function testHasAnyAliasWithoutMatch(): void
    {
        $aliases = ['abc'];

        /* @var ContainerInterface&MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->once())
                  ->method('has')
                  ->with($this->identicalTo('abc'))
                  ->willReturn(false);

        $factory = new AutoWireFactory();
        $result = $this->invokeMethod($factory, 'hasAnyAlias', $container, $aliases);

        $this->assertFalse($result);
    }
}
