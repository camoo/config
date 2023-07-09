<?php

declare(strict_types=1);

namespace Camoo\Config\Test\Writer;

use Camoo\Config\Enum\Writer;
use Camoo\Config\Exception\WriteException;
use Camoo\Config\Writer\Properties;
use PHPUnit\Framework\TestCase;

class PropertiesTest extends TestCase
{
    protected Properties $writer;

    protected string|false $temp_file;

    protected array $data;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->writer = new Properties();
        $this->temp_file = tempnam(sys_get_temp_dir(), 'config.properties');
        $this->data = [
            'website' => 'https://en.wikipedia.org/',
            'language' => 'English',
            'message' => "Welcome to \nWikipedia!",
            'key' => 'valueOverOneLine\\',
            'key with spaces' => 'This is the value that could be looked up with the key "key with spaces".',
            'key:with=colonAndEqualsSign' => 'This is the value for the key "key:with=colonAndEqualsSign"',
            'path' => 'c:\wiki\templates',
        ];
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        unlink($this->temp_file);
    }

    /** @covers \Camoo\Config\Writer\Properties::getSupportedExtensions() */
    public function testGetSupportedExtensions()
    {
        $expected = [Writer::PROPERTIES];
        $actual = $this->writer->getSupportedExtensions();
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Camoo\Config\Writer\Properties::toString()
     * @covers \Camoo\Config\Writer\Properties::toProperties()
     */
    public function testEncodeProperties()
    {
        $actual = $this->writer->toString($this->data);
        $expected = <<< 'EOD'
website = https://en.wikipedia.org/
language = English
message = Welcome to \
Wikipedia!
key = valueOverOneLine\\
key\ with\ spaces = This is the value that could be looked up with the key "key with spaces".
key\:with\=colonAndEqualsSign = This is the value for the key "key:with=colonAndEqualsSign"
path = c:\\wiki\\templates

EOD;
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Camoo\Config\Writer\Properties::toString()
     * @covers \Camoo\Config\Writer\Properties::toFile()
     * @covers \Camoo\Config\Writer\Properties::toProperties()
     */
    public function testWriteProperties()
    {
        $this->writer->toFile($this->data, $this->temp_file);

        $this->assertFileExists($this->temp_file);
        $this->assertFileEquals(__DIR__ . '/../mocks/pass/config1.properties', $this->temp_file);
    }

    /**
     * @covers \Camoo\Config\Writer\Properties::toString()
     * @covers \Camoo\Config\Writer\Properties::toFile()
     * @covers \Camoo\Config\Writer\Properties::toProperties()
     */
    public function testUnwritableFile()
    {
        $this->expectException(WriteException::class);
        $this->expectExceptionMessage('There was an error writing the file');
        chmod($this->temp_file, 0444);

        $this->writer->toFile($this->data, $this->temp_file);
    }

    /**
     * @covers \Camoo\Config\Writer\Properties::toString()
     * @covers \Camoo\Config\Writer\Properties::toProperties()
     */
    public function testToStringWithArrayReturnsEmptyString(): void
    {
        $data = [
            ['unit' => 'test'],
        ];
        $this->assertSame('', $this->writer->toString($data));
    }
}
