<?php

declare(strict_types=1);

namespace BluePsyduckTest\LaminasAutoWireFactory;

use BluePsyduck\LaminasAutoWireFactory\ParameterAliasResolver;
use BluePsyduck\TestHelper\ReflectionTrait;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithClassTypeHintConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithoutConstructor;
use BluePsyduckTestAsset\LaminasAutoWireFactory\ClassWithParameterlessConstructor;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * The PHPUnit test of the ParameterAliasResolver class.
 *
 * @author BluePsyduck <bluepsyduck@gmx.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPL v3
 * @coversDefaultClass \BluePsyduck\LaminasAutoWireFactory\ParameterAliasResolver
 */
class ParameterAliasResolverTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @throws ReflectionException
     * @backupStaticAttributes enabled
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

        ParameterAliasResolver::setCacheFile($cacheFile);

        $this->assertEquals(
            $parameterAliasesCache,
            $this->extractProperty(ParameterAliasResolver::class, 'parameterAliasesCache')
        );
    }

    /**
     * @throws ReflectionException
     * @backupStaticAttributes enabled
     * @covers ::setCacheFile
     */
    public function testSetCacheFileWithInvalidCache(): void
    {
        $root = vfsStream::setup('root');
        $root->addChild(vfsStream::newFile('cache-file'));

        $cacheFile = vfsStream::url('root/cache-file');
        file_put_contents($cacheFile, sprintf('<?php return %s;', var_export('foo', true)));

        ParameterAliasResolver::setCacheFile($cacheFile);

        $this->assertEquals([], $this->extractProperty(ParameterAliasResolver::class, 'parameterAliasesCache'));
    }

    /**
     * @throws ReflectionException
     * @backupStaticAttributes enabled
     * @covers ::setCacheFile
     */
    public function testSetCacheFileWithMissingFile(): void
    {
        vfsStream::setup('root');
        $cacheFile = vfsStream::url('root/cache-file');

        ParameterAliasResolver::setCacheFile($cacheFile);

        $this->assertEquals([], $this->extractProperty(ParameterAliasResolver::class, 'parameterAliasesCache'));
    }

    /**
     * @throws ReflectionException
     * @covers ::getParameterAliasesForConstructor
     */
    public function testGetParameterAliasesForConstructorWithoutCache(): void
    {
        $className = 'abc';
        $parameterAliases = [
            'def' => ['ghi', 'jkl'],
            'mno' => ['pqr', 'stu'],
        ];
        $cache = [
            'foo' => [
                'bar' => [],
            ],
        ];
        $expectedCache = [
            'foo' => [
                'bar' => [],
            ],
            'abc' => [
                'def' => ['ghi', 'jkl'],
                'mno' => ['pqr', 'stu'],
            ],
        ];

        $resolver = $this->getMockBuilder(ParameterAliasResolver::class)
                         ->onlyMethods(['resolveParameterAliasesForConstructor', 'writeCacheToFile'])
                         ->getMock();
        $resolver->expects($this->once())
                 ->method('resolveParameterAliasesForConstructor')
                 ->with($this->identicalTo($className))
                 ->willReturn($parameterAliases);
        $resolver->expects($this->once())
                 ->method('writeCacheToFile');
        $this->injectProperty(ParameterAliasResolver::class, 'parameterAliasesCache', $cache);

        $result = $resolver->getParameterAliasesForConstructor($className);

        $this->assertSame($parameterAliases, $result);
        $this->assertEquals(
            $expectedCache,
            $this->extractProperty(ParameterAliasResolver::class, 'parameterAliasesCache')
        );
    }

    /**
     * @throws ReflectionException
     * @covers ::getParameterAliasesForConstructor
     */
    public function testGetParameterAliasesForConstructorWithCache(): void
    {
        $className = 'abc';
        $cache = [
            'foo' => [
                'bar' => [],
            ],
            'abc' => [
                'def' => ['ghi', 'jkl'],
                'mno' => ['pqr', 'stu'],
            ],
        ];
        $expectedResult = [
            'def' => ['ghi', 'jkl'],
            'mno' => ['pqr', 'stu'],
        ];

        $resolver = $this->getMockBuilder(ParameterAliasResolver::class)
                         ->onlyMethods(['resolveParameterAliasesForConstructor', 'writeCacheToFile'])
                         ->getMock();
        $resolver->expects($this->never())
                 ->method('resolveParameterAliasesForConstructor');
        $resolver->expects($this->never())
                 ->method('writeCacheToFile');
        $this->injectProperty(ParameterAliasResolver::class, 'parameterAliasesCache', $cache);

        $result = $resolver->getParameterAliasesForConstructor($className);

        $this->assertSame($expectedResult, $result);
        $this->assertSame($cache, $this->extractProperty(ParameterAliasResolver::class, 'parameterAliasesCache'));
    }

    /**
     * @throws ReflectionException
     * @covers ::resolveParameterAliasesForConstructor
     */
    public function testResolveParameterAliasesForConstructor(): void
    {
        $className = 'abc';
        $aliases1 = ['def', 'ghi'];
        $aliases2 = ['jkl', 'mno'];
        $expectedResult = [
            'pqr' => ['def', 'ghi'],
            'stu' => ['jkl', 'mno'],
        ];

        $parameter1 = $this->createMock(ReflectionParameter::class);
        $parameter1->expects($this->once())
                   ->method('getName')
                   ->willReturn('pqr');

        $parameter2 = $this->createMock(ReflectionParameter::class);
        $parameter2->expects($this->once())
                   ->method('getName')
                   ->willReturn('stu');

        $resolver = $this->getMockBuilder(ParameterAliasResolver::class)
                         ->onlyMethods([
                             'getReflectedParametersForConstructor',
                             'getAliasesForParameter',
                         ])
                         ->getMock();
        $resolver->expects($this->once())
                 ->method('getReflectedParametersForConstructor')
                 ->with($this->identicalTo($className))
                 ->willReturn([$parameter1, $parameter2]);
        $resolver->expects($this->exactly(2))
                 ->method('getAliasesForParameter')
                 ->withConsecutive(
                     [$this->identicalTo($parameter1)],
                     [$this->identicalTo($parameter2)]
                 )
                 ->willReturnOnConsecutiveCalls(
                     $aliases1,
                     $aliases2
                 );

        $result = $this->invokeMethod($resolver, 'resolveParameterAliasesForConstructor', $className);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::getReflectedParametersForConstructor
     */
    public function testGetReflectedParametersForConstructor(): void
    {
        $className = ClassWithClassTypeHintConstructor::class;

        $resolver = new ParameterAliasResolver();
        $result = $this->invokeMethod($resolver, 'getReflectedParametersForConstructor', $className);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(ReflectionParameter::class, $result[0]);
        $this->assertInstanceOf(ReflectionParameter::class, $result[1]);
    }

    /**
     * @throws ReflectionException
     * @covers ::getReflectedParametersForConstructor
     */
    public function testGetReflectedParametersForConstructorWithoutConstructor(): void
    {
        $className = ClassWithoutConstructor::class;
        $expectedResult = [];

        $resolver = new ParameterAliasResolver();
        $result = $this->invokeMethod($resolver, 'getReflectedParametersForConstructor', $className);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::getReflectedParametersForConstructor
     */
    public function testGetReflectedParametersForConstructorWithEmptyConstructor(): void
    {
        $className = ClassWithParameterlessConstructor::class;
        $expectedResult = [];

        $resolver = new ParameterAliasResolver();
        $result = $this->invokeMethod($resolver, 'getReflectedParametersForConstructor', $className);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::getAliasesForParameter
     */
    public function testGetAliasesForParameterWithClassHint(): void
    {
        $className = 'abc';
        $parameterName = 'def';
        $expectedResult = [
            'abc $def',
            'abc',
            '$def',
        ];

        $class = $this->createMock(ReflectionClass::class);
        $class->expects($this->any())
              ->method('getName')
              ->willReturn($className);

        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->any())
                  ->method('getClass')
                  ->willReturn($class);
        $parameter->expects($this->any())
                  ->method('getName')
                  ->willReturn($parameterName);

        $resolver = new ParameterAliasResolver();
        $result = $this->invokeMethod($resolver, 'getAliasesForParameter', $parameter);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::getAliasesForParameter
     */
    public function testGetAliasesForParameterWithTypeHint(): void
    {
        $typeName = 'abc';
        $parameterName = 'def';
        $expectedResult = [
            'abc $def',
            '$def',
        ];

        $type = $this->createMock(ReflectionNamedType::class);
        $type->expects($this->any())
             ->method('getName')
             ->willReturn($typeName);

        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->any())
                  ->method('getClass')
                  ->willReturn(null);
        $parameter->expects($this->any())
                  ->method('getType')
                  ->willReturn($type);
        $parameter->expects($this->any())
                  ->method('getName')
                  ->willReturn($parameterName);

        $resolver = new ParameterAliasResolver();
        $result = $this->invokeMethod($resolver, 'getAliasesForParameter', $parameter);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @covers ::getAliasesForParameter
     */
    public function testGetAliasesForParameterWithoutHint(): void
    {
        $parameterName = 'abc';
        $expectedResult = [
            '$abc',
        ];

        $parameter = $this->createMock(ReflectionParameter::class);
        $parameter->expects($this->any())
                  ->method('getClass')
                  ->willReturn(null);
        $parameter->expects($this->any())
                  ->method('getType')
                  ->willReturn(null);
        $parameter->expects($this->any())
                  ->method('getName')
                  ->willReturn($parameterName);

        $resolver = new ParameterAliasResolver();
        $result = $this->invokeMethod($resolver, 'getAliasesForParameter', $parameter);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @throws ReflectionException
     * @backupStaticAttributes enabled
     * @covers ::writeCacheToFile
     */
    public function testWriteCacheToFile(): void
    {
        $root = vfsStream::setup('root');

        $parameterAliasesCache = [
            'abc' => [
                'def' => ['ghi', 'jkl'],
                'mno' => ['pqr', 'stu'],
            ],
            'vwx' => [],
        ];
        $cacheFile = vfsStream::url('root/cache-file');

        $this->injectProperty(ParameterAliasResolver::class, 'cacheFile', $cacheFile);
        $this->injectProperty(ParameterAliasResolver::class, 'parameterAliasesCache', $parameterAliasesCache);

        $resolver = new ParameterAliasResolver();
        $this->invokeMethod($resolver, 'writeCacheToFile');

        $this->assertTrue($root->hasChild('cache-file'));

        $cacheContents = require($cacheFile);
        $this->assertEquals($parameterAliasesCache, $cacheContents);
    }

    /**
     * @throws ReflectionException
     * @backupStaticAttributes enabled
     * @covers ::writeCacheToFile
     */
    public function testWriteCacheToFileWithExistingFile(): void
    {
        $root = vfsStream::setup('root');
        $root->addChild(vfsStream::newFile('cache-file'));

        $parameterAliasesCache = [
            'abc' => [
                'def' => ['ghi', 'jkl'],
                'mno' => ['pqr', 'stu'],
            ],
            'vwx' => [],
        ];
        $cacheFile = vfsStream::url('root/cache-file');

        $this->injectProperty(ParameterAliasResolver::class, 'cacheFile', $cacheFile);
        $this->injectProperty(ParameterAliasResolver::class, 'parameterAliasesCache', $parameterAliasesCache);

        $resolver = new ParameterAliasResolver();
        $this->invokeMethod($resolver, 'writeCacheToFile');

        $this->assertTrue($root->hasChild('cache-file'));

        $cacheContents = require($cacheFile);
        $this->assertEquals($parameterAliasesCache, $cacheContents);
    }


    /**
     * @throws ReflectionException
     * @backupStaticAttributes enabled
     * @covers ::writeCacheToFile
     */
    public function testWriteCacheToFileWithoutCacheFile(): void
    {
        $resolver = new ParameterAliasResolver();
        $this->invokeMethod($resolver, 'writeCacheToFile');

        $this->expectNotToPerformAssertions();
    }
}
