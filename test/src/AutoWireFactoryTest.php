<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\AutoWireFactory;
use BluePsyduck\LaminasAutoWireFactory\Exception\FailedReflectionException;
use BluePsyduck\LaminasAutoWireFactory\Resolver\AliasResolver;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ConfigResolver;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverFactory;
use BluePsyduck\LaminasAutoWireFactory\Resolver\ResolverInterface;
use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithClassTypeHintConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithoutConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithParameterlessConstructor;
use Interop\Container\ContainerInterface;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

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

    // phpcs:ignore
    private const TEST_CACHE = 'a:1:{s:3:"foo";a:2:{i:0;O:57:"BluePsyduck\LaminasAutoWireFactory\Resolver\AliasResolver":1:{s:1:"a";s:3:"abc";}i:1;O:58:"BluePsyduck\LaminasAutoWireFactory\Resolver\ConfigResolver":1:{s:1:"k";a:2:{i:0;s:3:"def";i:1;s:3:"ghi";}}}}';

    /** @var ResolverFactory&MockObject */
    private ResolverFactory $resolverFactory;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->resolverFactory = $this->createMock(ResolverFactory::class);

        $this->injectStaticProperty(AutoWireFactory::class, 'cache', []);
        $this->injectStaticProperty(AutoWireFactory::class, 'cacheFile', null);
    }

    private function createInstance(): AutoWireFactory
    {
        $instance =  new AutoWireFactory();

        try {
            $this->assertInstanceOf(ResolverFactory::class, $this->extractProperty($instance, 'resolverFactory'));
            $this->injectProperty($instance, 'resolverFactory', $this->resolverFactory);
        } catch (ReflectionException $e) {
            $this->fail($e->getMessage());
        }

        return $instance;
    }

    /**
     * Creates the resolvers to use for the tests.
     * @param ContainerInterface $container
     * @param ClassWithoutConstructor $object1
     * @param ClassWithParameterlessConstructor $object2
     * @return array<ResolverInterface>
     */
    private function createResolvers(
        ContainerInterface $container,
        ClassWithoutConstructor $object1,
        ClassWithParameterlessConstructor $object2
    ): array {
        $resolver1 = $this->createMock(ResolverInterface::class);
        $resolver1->expects($this->once())
                  ->method('resolve')
                  ->with($this->identicalTo($container))
                  ->willReturn($object1);

        $resolver2 = $this->createMock(ResolverInterface::class);
        $resolver2->expects($this->once())
                  ->method('resolve')
                  ->with($this->identicalTo($container))
                  ->willReturn($object2);

        return [$resolver1, $resolver2];
    }

    /**
     * @throws ReflectionException
     */
    public function testCacheWithoutFile(): void
    {
        $root = vfsStream::setup();
        $cacheFile = vfsStream::url('root/cache-file');

        $cache = [
            'foo' => [
                new AliasResolver('abc'),
                new ConfigResolver(['def', 'ghi']),
            ]
        ];

        AutoWireFactory::setCacheFile($cacheFile);
        $this->assertEquals([], $this->extractStaticProperty(AutoWireFactory::class, 'cache'));
        $this->assertFalse($this->extractStaticProperty(AutoWireFactory::class, 'isCacheDirty'));

        $this->injectStaticProperty(AutoWireFactory::class, 'cache', $cache);
        $this->injectStaticProperty(AutoWireFactory::class, 'isCacheDirty', true);

        $instance = new AutoWireFactory();
        unset($instance);

        $this->assertTrue($root->hasChild('cache-file'));
        $this->assertFalse($this->extractStaticProperty(AutoWireFactory::class, 'isCacheDirty'));
        $this->assertEquals(self::TEST_CACHE, file_get_contents($cacheFile));
    }

    /**
     * @throws ReflectionException
     */
    public function testCacheWithNonDirtyCache(): void
    {
        $root = vfsStream::setup();
        $cacheFile = vfsStream::url('root/cache-file');

        $cache = [
            'foo' => [
                new AliasResolver('abc'),
                new ConfigResolver(['def', 'ghi']),
            ]
        ];

        AutoWireFactory::setCacheFile($cacheFile);
        $this->assertEquals([], $this->extractStaticProperty(AutoWireFactory::class, 'cache'));
        $this->assertFalse($this->extractStaticProperty(AutoWireFactory::class, 'isCacheDirty'));

        $this->injectStaticProperty(AutoWireFactory::class, 'cache', $cache);
        $this->injectStaticProperty(AutoWireFactory::class, 'isCacheDirty', false);

        $instance = new AutoWireFactory();
        unset($instance);

        $this->assertFalse($root->hasChild('cache-file'));
    }

    /**
     * @return array<mixed>
     */
    public function provideSetCacheFileWithCacheFile(): array
    {
        $cache = [
            'foo' => [
                new AliasResolver('abc'),
                new ConfigResolver(['def', 'ghi']),
            ],
        ];

        return [
            [self::TEST_CACHE, $cache],
            ['{invalid', []],
        ];
    }

    /**
     * @param string $cacheContents
     * @param array<class-string, array<ResolverInterface>> $expectedCache
     * @throws ReflectionException
     * @dataProvider provideSetCacheFileWithCacheFile
     */
    public function testSetCacheFileWithCacheFile(string $cacheContents, array $expectedCache): void
    {
        vfsStream::setup();
        $cacheFile = vfsStream::url('root/cache-file');
        file_put_contents($cacheFile, $cacheContents);

        AutoWireFactory::setCacheFile($cacheFile);
        $this->assertEquals($expectedCache, $this->extractStaticProperty(AutoWireFactory::class, 'cache'));
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testInvoke(): void
    {
        $className = ClassWithClassTypeHintConstructor::class;
        $container = $this->createMock(ContainerInterface::class);
        $object1 = $this->createMock(ClassWithoutConstructor::class);
        $object2 = $this->createMock(ClassWithParameterlessConstructor::class);
        $expectedResult = new ClassWithClassTypeHintConstructor($object1, $object2);

        $this->resolverFactory->expects($this->once())
                              ->method('createResolversForClass')
                              ->with($this->identicalTo($className))
                              ->willReturn($this->createResolvers($container, $object1, $object2));

        $instance = $this->createInstance();

        $result = $instance($container, $className);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ContainerExceptionInterface|ReflectionException
     */
    public function testInvokeUsingCache(): void
    {
        $className = ClassWithClassTypeHintConstructor::class;
        $container = $this->createMock(ContainerInterface::class);
        $object1 = $this->createMock(ClassWithoutConstructor::class);
        $object2 = $this->createMock(ClassWithParameterlessConstructor::class);
        $expectedResult = new ClassWithClassTypeHintConstructor($object1, $object2);

        $cache = [
            $className => $this->createResolvers($container, $object1, $object2),
        ];
        $this->injectStaticProperty(AutoWireFactory::class, 'cache', $cache);

        $this->resolverFactory->expects($this->never())
                              ->method('createResolversForClass');

        $instance = $this->createInstance();

        $result = $instance($container, $className);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWithReflectionException(): void
    {
        $className = ClassWithClassTypeHintConstructor::class;
        $container = $this->createMock(ContainerInterface::class);

        $this->resolverFactory->expects($this->once())
                              ->method('createResolversForClass')
                              ->with($this->identicalTo($className))
                              ->willThrowException($this->createMock(ReflectionException::class));

        $this->expectException(FailedReflectionException::class);

        $instance = $this->createInstance();
        $instance($container, $className);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function testInvokeWithNonExistingClass(): void
    {
        $className = 'abc';
        $container = $this->createMock(ContainerInterface::class);

        $this->resolverFactory->expects($this->never())
                              ->method('createResolversForClass');

        $this->expectException(FailedReflectionException::class);

        $instance = $this->createInstance();
        $instance($container, $className);
    }

    public function testCanCreate(): void
    {
        $className = ClassWithClassTypeHintConstructor::class;
        $container = $this->createMock(ContainerInterface::class);

        $resolver1 = $this->createMock(ResolverInterface::class);
        $resolver1->expects($this->once())
                  ->method('canResolve')
                  ->with($this->identicalTo($container))
                  ->willReturn(true);

        $resolver2 = $this->createMock(ResolverInterface::class);
        $resolver2->expects($this->once())
                  ->method('canResolve')
                  ->with($this->identicalTo($container))
                  ->willReturn(true);

        $this->resolverFactory->expects($this->once())
                              ->method('createResolversForClass')
                              ->with($this->identicalTo($className))
                              ->willReturn([$resolver1, $resolver2]);

        $instance = $this->createInstance();

        $result = $instance->canCreate($container, $className);
        $this->assertTrue($result);
    }

    public function testCanCreateReturningFalse(): void
    {
        $className = ClassWithClassTypeHintConstructor::class;
        $container = $this->createMock(ContainerInterface::class);

        $resolver1 = $this->createMock(ResolverInterface::class);
        $resolver1->expects($this->once())
                  ->method('canResolve')
                  ->with($this->identicalTo($container))
                  ->willReturn(true);

        $resolver2 = $this->createMock(ResolverInterface::class);
        $resolver2->expects($this->once())
                  ->method('canResolve')
                  ->with($this->identicalTo($container))
                  ->willReturn(false);

        $this->resolverFactory->expects($this->once())
                              ->method('createResolversForClass')
                              ->with($this->identicalTo($className))
                              ->willReturn([$resolver1, $resolver2]);

        $instance = $this->createInstance();

        $result = $instance->canCreate($container, $className);
        $this->assertFalse($result);
    }

    public function testCanCreateWithReflectionException(): void
    {
        $className = ClassWithClassTypeHintConstructor::class;
        $container = $this->createMock(ContainerInterface::class);

        $this->resolverFactory->expects($this->once())
                              ->method('createResolversForClass')
                              ->with($this->identicalTo($className))
                              ->willThrowException($this->createMock(ReflectionException::class));

        $instance = $this->createInstance();

        $result = $instance->canCreate($container, $className);
        $this->assertFalse($result);
    }

    public function testCanCreateWithNonExistingClass(): void
    {
        $className = 'abc';
        $container = $this->createMock(ContainerInterface::class);

        $this->resolverFactory->expects($this->never())
                              ->method('createResolversForClass');

        $instance = $this->createInstance();

        $result = $instance->canCreate($container, $className);
        $this->assertFalse($result);
    }
}
