<?php

namespace Camoo\Config\Test\Parser;

use Camoo\Config\Enum\Parser;
use Camoo\Config\Parser\Ini;
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-04-21 at 22:37:22.
 */
class IniTest extends TestCase
{
    /** @var Ini */
    protected $ini;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->ini = new Ini();
    }

    /** @covers \Camoo\Config\Parser\Ini::getSupportedExtensions() */
    public function testGetSupportedExtensions()
    {
        $expected = [Parser::INI];
        $actual = $this->ini->getSupportedExtensions();
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Camoo\Config\Parser\Ini::parseFile()
     * @covers \Camoo\Config\Parser\Ini::parse()
     * Tests the case where an INI string contains no parsable data at all, resulting in parse_ini_string
     * returning NULL, but not setting an error retrievable by error_get_last()
     */
    public function testLoadInvalidIniGBH()
    {
        $this->expectException(\Camoo\Config\Exception\ParseException::class);
        $this->expectExceptionMessage('No parsable content');
        $this->ini->parseFile(__DIR__ . '/../mocks/fail/error2.ini');
    }

    /**
     * @covers \Camoo\Config\Parser\Ini::parseString()
     * @covers \Camoo\Config\Parser\Ini::parse()
     */
    public function testLoadInvalidIni()
    {
        if (PHP_VERSION_ID < 70400 && PHP_VERSION_ID >= 50500) {
            $exceptionMessage = "syntax error, unexpected \$end, expecting ']'";
        } else {
            $exceptionMessage = "syntax error, unexpected end of file, expecting ']' in Unknown on line 1";
        }

        $this->expectException(\Camoo\Config\Exception\ParseException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->ini->parseString(file_get_contents(__DIR__ . '/../mocks/fail/error.ini'));
    }

    /**
     * @covers \Camoo\Config\Parser\Ini::parseFile()
     * @covers \Camoo\Config\Parser\Ini::parseString()
     * @covers \Camoo\Config\Parser\Ini::parse()
     */
    public function testLoadIni()
    {
        $file = $this->ini->parseFile(__DIR__ . '/../mocks/pass/config.ini');
        $string = $this->ini->parseString(file_get_contents(__DIR__ . '/../mocks/pass/config.ini'));

        $this->assertSame('localhost', $file['host']);
        $this->assertSame('80', $file['port']);

        /*$this->assertSame('localhost', $string['host']);
        $this->assertSame(80, $string['port']);*/
    }

    /**
     * @covers \Camoo\Config\Parser\Ini::parseFile()
     * @covers \Camoo\Config\Parser\Ini::parseString()
     * @covers \Camoo\Config\Parser\Ini::parse()
     * @covers \Camoo\Config\Parser\Ini::expandDottedKey()
     */
    public function testLoadIniWithDottedName()
    {
        $file = $this->ini->parseFile(__DIR__ . '/../mocks/pass/config2.ini');
        $string = $this->ini->parseString(file_get_contents(__DIR__ . '/../mocks/pass/config2.ini'));

        $expected = ['host1', 'host2', 'host3'];

        $this->assertSame($expected, $file['network']['group']['servers']);
        $this->assertSame('localhost', $file['network']['http']['host']);
        $this->assertSame('80', $file['network']['http']['port']);

        $this->assertSame($expected, $string['network']['group']['servers']);
        $this->assertSame('localhost', $string['network']['http']['host']);
        $this->assertSame('80', $string['network']['http']['port']);
    }
}
