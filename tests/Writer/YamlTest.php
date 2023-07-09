<?php

namespace Camoo\Config\Test\Writer;

use Camoo\Config\Enum\Writer;
use Camoo\Config\Writer\Yaml;
use PHPUnit\Framework\TestCase;

class YamlTest extends TestCase
{
    protected Yaml $writer;

    protected string|false $temp_file;

    protected array $data;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->writer = new Yaml();
        $this->temp_file = tempnam(sys_get_temp_dir(), 'config.yaml');
        $this->data = [
            'application' => [
                'name' => 'configuration',
                'secret' => 's3cr3t',
            ],
            'host' => 'localhost',
            'port' => 80,
            'servers' => [
                'host1',
                'host2',
                'host3',
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

    /** @covers \Camoo\Config\Writer\Yaml::getSupportedExtensions() */
    public function testGetSupportedExtensions()
    {
        $expected = [Writer::YAML, Writer::YML];
        $actual = $this->writer->getSupportedExtensions();
        $this->assertSame($expected, $actual);
    }

    /** @covers \Camoo\Config\Writer\Yaml::toString() */
    public function testEncodeYaml()
    {
        $actual = $this->writer->toString($this->data);
        $expected = <<<'EOD'
application:
    name: configuration
    secret: s3cr3t
host: localhost
port: 80
servers:
    - host1
    - host2
    - host3

EOD;
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Camoo\Config\Writer\Yaml::toString()
     * @covers \Camoo\Config\Writer\Yaml::toFile()
     */
    public function testWriteYaml()
    {
        $this->writer->toFile($this->data, $this->temp_file);
        $this->assertFileExists($this->temp_file);
        $this->assertFileEquals($this->temp_file, __DIR__ . '/../mocks/pass/config4.yaml');
    }

    /**
     * @covers \Camoo\Config\Writer\Yaml::toString()
     * @covers \Camoo\Config\Writer\Yaml::toFile()
     */
    public function testUnwritableFile()
    {
        $this->expectException(\Camoo\Config\Exception\WriteException::class);
        $this->expectExceptionMessage('There was an error writing the file');
        chmod($this->temp_file, 0444);

        $this->writer->toFile($this->data, $this->temp_file);
    }
}
