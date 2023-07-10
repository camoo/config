<?php

declare(strict_types=1);

namespace Camoo\Config\Test;

use Camoo\Config\Config;
use Camoo\Config\Enum\Parser;
use Camoo\Config\Enum\Writer;
use Camoo\Config\Exception\EmptyDirectoryException;
use Camoo\Config\Exception\FileNotFoundException;
use Camoo\Config\Exception\UnsupportedFormatException;
use Camoo\Config\Parser\Json as JsonParser;
use Camoo\Config\Parser\Php;
use Camoo\Config\Writer\Json;
use Camoo\Config\Writer\Json as JsonWriter;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    protected Config $config;

    protected function setUp(): void
    {
        parent::setUp();
        mkdir(__DIR__ . '/mocks/empty/unit_test', 0777, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        rmdir(__DIR__ . '/mocks/empty/unit_test');
    }

    /**
     * @covers Config::load()
     * @covers Config::loadFromFile()
     */
    public function testLoadWithUnsupportedFormat(): void
    {
        $this->expectException(UnsupportedFormatException::class);
        $this->expectExceptionMessage('Unsupported configuration format');
        Config::load(__DIR__ . '/mocks/fail/error.lib');
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     */
    public function testConstructWithUnsupportedFormat(): void
    {
        $this->expectException(UnsupportedFormatException::class);
        $this->expectExceptionMessage('Unsupported configuration format');
        new Config(__DIR__ . '/mocks/fail/error.lib');
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     * @covers Config::getPathFromArray()
     * @covers Config::getValidPath()
     */
    public function testConstructWithInvalidPath(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('Configuration file: [ladadeedee] cannot be found');
        new Config('ladadeedee');
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     * @covers Config::getPathFromArray()
     * @covers Config::getValidPath()
     */
    public function testConstructWithEmptyDirectory(): void
    {
        $this->expectException(EmptyDirectoryException::class);
        new Config(__DIR__ . '/mocks/empty/unit_test');
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     * @covers Config::getPathFromArray()
     * @covers Config::getValidPath()
     */
    public function testConstructWithDirectoryContainingUnsupportedExtension(): void
    {
        $this->expectException(UnsupportedFormatException::class);
        new Config(__DIR__ . '/mocks/empty/');
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     * @covers Config::getPathFromArray()
     * @covers Config::getValidPath()
     */
    public function testConstructWithArray(): void
    {
        $paths = [__DIR__ . '/mocks/pass/config.xml', __DIR__ . '/mocks/pass/config2.json'];
        $config = new Config($paths);

        $expected = 'localhost';
        $actual = $config->get('host');

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     * @covers Config::getPathFromArray()
     * @covers Config::getValidPath()
     */
    public function testConstructWithArrayWithNonexistentFile(): void
    {
        $this->expectException(FileNotFoundException::class);
        $paths = [__DIR__ . '/mocks/pass/config.xml', __DIR__ . '/mocks/pass/config3.json'];
        $config = new Config($paths);

        $expected = 'localhost';
        $actual = $config->get('host');

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     * @covers Config::getPathFromArray()
     * @covers Config::getValidPath()
     */
    public function testConstructWithArrayWithOptionalFile(): void
    {
        $paths = [__DIR__ . '/mocks/pass/config.xml', '?' . __DIR__ . '/mocks/pass/config2.json'];
        $config = new Config($paths);

        $expected = 'localhost';
        $actual = $config->get('host');

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     * @covers Config::getPathFromArray()
     * @covers Config::getValidPath()
     */
    public function testConstructWithArrayWithOptionalNonexistentFile(): void
    {
        $paths = [__DIR__ . '/mocks/pass/config.xml', '?' . __DIR__ . '/mocks/pass/config3.json'];
        $config = new Config($paths);

        $expected = 'localhost';
        $actual = $config->get('host');

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     * @covers Config::getPathFromArray()
     * @covers Config::getValidPath()
     */
    public function testConstructWithDirectory(): void
    {
        $config = new Config(__DIR__ . '/mocks/dir');

        $expected = 'localhost';
        $actual = $config->get('host');

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     * @covers Config::getPathFromArray()
     * @covers Config::getValidPath()
     * @covers Config::__toString()
     */
    public function testConstructWithYml(): void
    {
        $filename = __DIR__ . '/mocks/pass/config.yml';
        $config = new Config($filename);

        $expected = 'localhost';
        $actual = $config->get('host');

        $this->assertSame($expected, $actual);

        $this->assertSame('1. ' . $filename . PHP_EOL . file_get_contents($filename) . PHP_EOL, $config->__toString());
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     * @covers Config::getPathFromArray()
     * @covers Config::getValidPath()
     */
    public function testConstructWithYmlDist(): void
    {
        $config = new Config(__DIR__ . '/mocks/pass/config.yml.dist');

        $expected = 'localhost';
        $actual = $config->get('host');

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     * @covers Config::getPathFromArray()
     * @covers Config::getValidPath()
     */
    public function testConstructWithEmptyYml(): void
    {
        $config = new Config(__DIR__ . '/mocks/pass/empty.yaml');

        $expected = [];
        $actual = $config->all();

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromFile()
     * @covers Config::getPathFromArray()
     * @covers Config::getValidPath()
     */
    public function testConstructWithFileParser(): void
    {
        $config = new Config(__DIR__ . '/mocks/pass/json.config', Parser::JSON);

        $expected = 'localhost';
        $actual = $config->get('host');

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Config::__construct()
     * @covers Config::loadFromString()
     */
    public function testConstructWithStringParser(): void
    {
        $settings = file_get_contents(__DIR__ . '/mocks/pass/config.php');
        $config = new Config($settings, new Php(), true);

        $expected = 'localhost';
        $actual = $config->get('host');

        $this->assertSame($expected, $actual);
    }

    /**
     * @covers Config::__construct()
     * @covers Config::get()
     *
     * @dataProvider specialConfigProvider()
     */
    public function testGetReturnsArrayMergedArray(Config $config): void
    {
        $this->assertCount(4, $config->get('servers'));
    }

    /** @covers Config::toString() */
    public function testWritesToString(): void
    {
        $config = new Config(json_encode(['foo' => 'bar']), new JsonParser(), true);

        $string = $config->toString(new JsonWriter());

        $this->assertNotEmpty($string);
    }

    /** @covers Config::toString */
    public function testWritesToStringWithFactory(): void
    {
        $config = new Config(json_encode(['foo' => 'bar']), Parser::JSON, true);

        $string = $config->toString(Writer::JSON);

        $this->assertNotEmpty($string);
    }

    /**
     * @covers Config::toFile()
     * @covers Config::__toString()
     *
     * @dataProvider provideWriterInstance
     */
    public function testWritesToFile(mixed $writer, string $extension): void
    {
        $content = json_encode(['foo' => 'bar']);
        $config = new Config($content, new JsonParser(), true);
        $filename = tempnam(sys_get_temp_dir(), 'config') . '.' . $extension;

        $config->toFile($filename, $writer);

        $this->assertSame($content, $config->__toString());
        $this->assertFileExists($filename);
    }

    /** @covers Config::toFile() */
    public function testCannotWritesToFile(): void
    {
        $extension = 'camoo';
        $this->expectException(UnsupportedFormatException::class);
        $this->expectExceptionMessage('Unsupported configuration format: .' . $extension);
        $content = json_encode(['foo' => 'bar']);
        $config = new Config($content, new JsonParser(), true);
        $filename = tempnam(sys_get_temp_dir(), 'config') . '.' . $extension;

        $config->toFile($filename);
    }

    public function provideWriterInstance(): array
    {
        return [
            [null, 'json.dist'],
            [Writer::JSON, 'json.dist'],
            [new Json(), 'json'],
        ];
    }

    /** Provides names of example configuration files */
    public function configProvider(): array
    {
        return array_merge(
            [
                [new Config(__DIR__ . '/mocks/pass/config-exec.php')],
                [new Config(__DIR__ . '/mocks/pass/config.ini')],
                [new Config(__DIR__ . '/mocks/pass/config.json')],
                [new Config(__DIR__ . '/mocks/pass/config.php')],
                [new Config(__DIR__ . '/mocks/pass/config.xml')],
                [new Config(__DIR__ . '/mocks/pass/config.yaml')],
            ]
        );
    }

    /** Provides names of example configuration files (for array and directory) */
    public function specialConfigProvider(): array
    {
        return [
            [
                new Config(
                    [
                        __DIR__ . '/mocks/pass/config2.json',
                        __DIR__ . '/mocks/pass/config.yaml',
                    ]
                ),
                new Config(__DIR__ . '/mocks/dir/'),
            ],
        ];
    }
}
