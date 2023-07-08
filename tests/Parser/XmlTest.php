<?php

namespace Noodlehaus\Test\Parser;

use Noodlehaus\Parser\Xml;
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-04-21 at 22:37:22.
 */
class XmlTest extends TestCase
{
    /** @var Xml */
    protected $xml;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->xml = new Xml();
    }

    /** @covers \Noodlehaus\Parser\Xml::getSupportedExtensions() */
    public function testGetSupportedExtensions()
    {
        $expected = ['xml'];
        $actual = $this->xml->getSupportedExtensions();
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Noodlehaus\Parser\Xml::parseFile()
     * @covers \Noodlehaus\Parser\Xml::parse()
     */
    public function testLoadInvalidXml()
    {
        $this->expectException(\Noodlehaus\Exception\ParseException::class);
        $this->expectExceptionMessage('Opening and ending tag mismatch: name line 4');
        $this->xml->parseFile(__DIR__ . '/../mocks/fail/error.xml');
    }

    /**
     * @covers \Noodlehaus\Parser\Xml::parseFile()
     * @covers \Noodlehaus\Parser\Xml::parseString()
     * @covers \Noodlehaus\Parser\Xml::parse()
     */
    public function testLoadXml()
    {
        $file = $this->xml->parseFile(__DIR__ . '/../mocks/pass/config.xml');
        $string = $this->xml->parseString(file_get_contents(__DIR__ . '/../mocks/pass/config.xml'));

        $this->assertSame('localhost', $file['host']);
        $this->assertSame('80', $file['port']);

        $this->assertSame('localhost', $string['host']);
        $this->assertSame('80', $string['port']);
    }
}
