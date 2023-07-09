<?php

namespace Camoo\Config\Test\Parser;

use Camoo\Config\Enum\Parser;
use Camoo\Config\Parser\Properties;
use PHPUnit\Framework\TestCase;

class PropertiesTest extends TestCase
{
    /** @var Properties */
    protected $properties;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->properties = new Properties();
    }

    /** @covers \Camoo\Config\Parser\Properties::getSupportedExtensions() */
    public function testGetSupportedExtensions()
    {
        $expected = [Parser::PROPERTIES];
        $actual = $this->properties->getSupportedExtensions();
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Camoo\Config\Parser\Properties::parseFile()
     * @covers \Camoo\Config\Parser\Properties::parseString()
     * @covers \Camoo\Config\Parser\Properties::parse()
     */
    public function testLoadProperties()
    {
        $config = $this->properties->parseFile(__DIR__ . '/../mocks/pass/config.properties');

        $this->assertSame('https://en.wikipedia.org/', @$config['website']);
        $this->assertSame('English', @$config['language']);
        $this->assertSame('Welcome to Wikipedia!', @$config['message']);
        $this->assertSame('valueOverOneLine\\', @$config['key']);
        $this->assertSame('This is the value that could be looked up with the key "key with spaces".', @$config['key with spaces']);
        $this->assertSame('This is the value for the key "key:with=colonAndEqualsSign"', @$config['key:with=colonAndEqualsSign']);
        $this->assertSame('c:\\wiki\\templates', @$config['path']);
    }

    /**
     * @covers \Camoo\Config\Parser\Ini::parseFile()
     * @covers \Camoo\Config\Parser\Ini::parse()
     */
    public function testLoadInvalidIniGBH()
    {
        $config = $this->properties->parseFile(__DIR__ . '/../mocks/fail/error.properties');

        $this->assertEmpty($config);
    }
}
