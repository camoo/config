<?php

namespace Camoo\Config\Test\Writer;

use Camoo\Config\Enum\Writer;
use Camoo\Config\Writer\Ini;
use PHPUnit\Framework\TestCase;

class IniTest extends TestCase
{
    /** @var Ini */
    protected $writer;

    /** @var string */
    protected $temp_file;

    /** @var array */
    protected $data;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->writer = new Ini();
        $this->temp_file = tempnam(sys_get_temp_dir(), 'config.ini');
        $this->data = [
            'database' => [
                'host' => 'localhost',
                'port' => '3306',
            ],
            'app' => [
                'name' => 'config',
                'description' => 'Config Reader and Writer',
            ],
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

    /** @covers \Camoo\Config\Writer\Ini::getSupportedExtensions() */
    public function testGetSupportedExtensions()
    {
        $expected = [Writer::INI];
        $actual = $this->writer->getSupportedExtensions();
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Camoo\Config\Writer\Ini::toString()
     * @covers \Camoo\Config\Writer\Ini::toINI()
     */
    public function testEncodeIni()
    {
        $actual = $this->writer->toString($this->data);
        $expected = <<< 'EOD'
[database]
host=localhost
port=3306
[app]
name=config
description=Config Reader and Writer

EOD;
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Camoo\Config\Writer\Ini::toString()
     * @covers \Camoo\Config\Writer\Ini::toFile()
     * @covers \Camoo\Config\Writer\Ini::toINI()
     */
    public function testWriteIni()
    {
        $this->writer->toFile($this->data, $this->temp_file);

        $this->assertFileExists($this->temp_file);
        $this->assertFileEquals($this->temp_file, __DIR__ . '/../mocks/pass/config4.ini');
    }

    /**
     * @covers \Camoo\Config\Writer\Ini::toString()
     * @covers \Camoo\Config\Writer\Ini::toFile()
     * @covers \Camoo\Config\Writer\Ini::toINI()
     */
    public function testUnwritableFile()
    {
        $this->expectException(\Camoo\Config\Exception\WriteException::class);
        $this->expectExceptionMessage('There was an error writing the file');
        chmod($this->temp_file, 0444);

        $this->writer->toFile($this->data, $this->temp_file);
    }
}
