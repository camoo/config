<?php

namespace Camoo\Config\Test\Writer;

use Camoo\Config\Enum\Writer;
use Camoo\Config\Writer\Xml;
use PHPUnit\Framework\TestCase;

class XmlTest extends TestCase
{
    /** @var Xml */
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
        $this->writer = new Xml();
        $this->temp_file = tempnam(sys_get_temp_dir(), 'config.xml');
        $this->data = [
            'application' => [
                'name' => 'configuration',
                'secret' => 's3cr3t',
            ],
            'host' => 'localhost',
            'port' => 80,
            'servers' => [
                'server1' => 'host1',
                'server2' => 'host2',
                'server3' => 'host3',
            ],
        ];
    }

    /** @covers \Camoo\Config\Writer\Xml::getSupportedExtensions() */
    public function testGetSupportedExtensions()
    {
        $expected = [Writer::XML];
        $actual = $this->writer->getSupportedExtensions();
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Camoo\Config\Writer\Xml::toString()
     * @covers \Camoo\Config\Writer\Xml::toXML()
     */
    public function testEncodeXml()
    {
        $actual = $this->writer->toString($this->data, false);
        $expected = <<<'EOD'
<?xml version="1.0"?>
<config><application><name>configuration</name><secret>s3cr3t</secret></application><host>localhost</host><port>80</port><servers><server1>host1</server1><server2>host2</server2><server3>host3</server3></servers></config>

EOD;
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Camoo\Config\Writer\Xml::toFile()
     * @covers \Camoo\Config\Writer\Xml::toString()
     * @covers \Camoo\Config\Writer\Xml::toXML()
     */
    public function testWriteXml()
    {
        $this->writer->toFile($this->data, $this->temp_file);

        $this->assertFileExists($this->temp_file);
        $this->assertFileEquals($this->temp_file, __DIR__ . '/../mocks/pass/config4.xml');
    }

    /**
     * @covers \Camoo\Config\Writer\Xml::toFile()
     * @covers \Camoo\Config\Writer\Xml::toXML()
     */
    public function testUnwritableFile()
    {
        $this->expectException(\Camoo\Config\Exception\WriteException::class);
        $this->expectExceptionMessage('There was an error writing the file');
        chmod($this->temp_file, 0444);

        $this->writer->toFile($this->data, $this->temp_file);
    }
}
