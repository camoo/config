<?php

declare(strict_types=1);

namespace Camoo\Config\Test;

use Camoo\Config\AbstractConfig;
use Camoo\Config\Config;
use Camoo\Config\Test\Fixture\SimpleConfig;
use PHPUnit\Framework\TestCase;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-04-21 at 22:37:22.
 */
class AbstractConfigTest extends TestCase
{
    private SimpleConfig|Config|null $config;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->config = new SimpleConfig(
            [
                'host' => 'localhost',
                'port' => 80,
                'servers' => [
                    'host1',
                    'host2',
                    'host3',
                ],
                'application' => [
                    'name' => 'configuration',
                    'secret' => 's3cr3t',
                    'runtime' => null,
                ],
                'user' => null,
            ]
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->config = null;
    }

    /**
     * @covers \Camoo\Config\AbstractConfig::__construct()
     * @covers \Camoo\Config\AbstractConfig::getDefaults()
     */
    public function testDefaultOptionsSetOnInstantiation(): void
    {
        $config = new SimpleConfig(
            [
                'host' => 'localhost',
                'port' => 80,
            ]
        );
        $this->assertSame('localhost', $config->get('host'));
        $this->assertSame(80, $config->get('port'));
    }

    /** @covers \Camoo\Config\AbstractConfig::get() */
    public function testGet(): void
    {
        $this->assertSame('localhost', $this->config->get('host'));
    }

    /** @covers \Camoo\Config\AbstractConfig::get() */
    public function testGetWithDefaultValue(): void
    {
        $this->assertSame(128, $this->config->get('ttl', 128));
    }

    /** @covers \Camoo\Config\AbstractConfig::get() */
    public function testGetNestedKey(): void
    {
        $this->assertSame('configuration', $this->config->get('application.name'));
    }

    /** @covers \Camoo\Config\AbstractConfig::get() */
    public function testGetNestedKeyWithDefaultValue(): void
    {
        $this->assertSame(128, $this->config->get('application.ttl', 128));
    }

    /** @covers \Camoo\Config\AbstractConfig::get() */
    public function testGetNonexistentKey(): void
    {
        $this->assertNull($this->config->get('proxy'));
    }

    /** @covers \Camoo\Config\AbstractConfig::get() */
    public function testGetNonexistentNestedKey(): void
    {
        $this->assertNull($this->config->get('proxy.name'));
    }

    /** @covers \Camoo\Config\AbstractConfig::get() */
    public function testGetReturnsArray(): void
    {
        $this->assertArrayHasKey('name', $this->config->get('application'));
        $this->assertSame('configuration', $this->config->get('application.name'));
        $this->assertCount(3, $this->config->get('application'));
    }

    /** @covers \Camoo\Config\AbstractConfig::set() */
    public function testSet(): void
    {
        $this->config->set('region', 'apac');
        $this->assertSame('apac', $this->config->get('region'));
    }

    /** @covers \Camoo\Config\AbstractConfig::set() */
    public function testSetNestedKey(): void
    {
        $this->config->set('location.country', 'Singapore');
        $this->assertSame('Singapore', $this->config->get('location.country'));
    }

    /** @covers \Camoo\Config\AbstractConfig::set() */
    public function testSetArray(): void
    {
        $this->config->set('database', [
            'host' => 'localhost',
            'name' => 'mydatabase',
        ]);
        $this->assertIsArray($this->config->get('database'));
        $this->assertSame('localhost', $this->config->get('database.host'));
    }

    /** @covers \Camoo\Config\AbstractConfig::set() */
    public function testCacheWithNestedArray(): void
    {
        $this->config->set('database', [
            'host' => 'localhost',
            'name' => 'mydatabase',
        ]);
        $this->assertIsArray($this->config->get('database'));
        $this->config->set('database.host', '127.0.0.1');
        $expected = [
            'host' => '127.0.0.1',
            'name' => 'mydatabase',
        ];
        $this->assertSame($expected, $this->config->get('database'));

        $this->config->set('config', [
            'database' => [
                'host' => 'localhost',
                'name' => 'mydatabase',
            ],
        ]);
        $this->config->get('config'); //Just get to set related cache
        $this->config->get('config.database'); //Just get to set related cache

        $this->config->set('config.database.host', '127.0.0.1');
        $expected = [
            'database' => [
                'host' => '127.0.0.1',
                'name' => 'mydatabase',
            ],
        ];
        $this->assertSame($expected, $this->config->get('config'));

        $expected = [
            'host' => '127.0.0.1',
            'name' => 'mydatabase',
        ];
        $this->assertSame($expected, $this->config->get('config.database'));
    }

    /** @covers \Camoo\Config\AbstractConfig::set() */
    public function testCacheWithNestedMiddleArray(): void
    {
        $this->config->set('config', [
            'database' => [
                'host' => 'localhost',
                'name' => 'mydatabase',
            ],
        ]);
        $this->config->get('config'); //Just get to set related cache
        $this->config->get('config.database'); //Just get to set related cache
        $this->config->get('config.database.host'); //Just get to set related cache
        $this->config->get('config.database.name'); //Just get to set related cache

        $this->config->set('config.database', [
            'host' => '127.0.0.1',
            'name' => 'mynewdatabase',
        ]);
        $this->assertSame('127.0.0.1', $this->config->get('config.database.host'));
        $this->assertSame('mynewdatabase', $this->config->get('config.database.name'));
    }

    /** @covers \Camoo\Config\AbstractConfig::set() */
    public function testSetAndUnsetArray(): void
    {
        $this->config->set('database', [
            'host' => 'localhost',
            'name' => 'mydatabase',
        ]);
        $this->assertIsArray($this->config->get('database'));
        $this->assertSame('localhost', $this->config->get('database.host'));
        $this->config->set('database.host', null);
        $this->assertNull($this->config->get('database.host'));
        $this->config->set('database', null);
        $this->assertNull($this->config->get('database'));
    }

    /** @covers \Camoo\Config\AbstractConfig::has() */
    public function testHas(): void
    {
        $this->assertTrue($this->config->has('application'));
        $this->assertTrue($this->config->has('user'));
        $this->assertFalse($this->config->has('not_exist'));
    }

    /** @covers \Camoo\Config\AbstractConfig::has() */
    public function testHasNestedKey(): void
    {
        $this->assertTrue($this->config->has('application.name'));
        $this->assertTrue($this->config->has('application.runtime'));
        $this->assertFalse($this->config->has('application.not_exist'));
        $this->assertFalse($this->config->has('not_exist.name'));
    }

    /** @covers \Camoo\Config\AbstractConfig::has() */
    public function testHasCache(): void
    {
        $this->assertTrue($this->config->has('application.name'));
        $this->assertTrue($this->config->has('application.name'));
    }

    /** @covers \Camoo\Config\AbstractConfig::all() */
    public function testAll(): void
    {
        $all = [
            'host' => 'localhost',
            'port' => 80,
            'servers' => [
                'host1',
                'host2',
                'host3',
            ],
            'application' => [
                'name' => 'configuration',
                'secret' => 's3cr3t',
                'runtime' => null,
            ],
            'user' => null,
        ];
        $this->assertSame($all, $this->config->all());
    }

    /** @covers \Camoo\Config\AbstractConfig::merge() */
    public function testMerge(): void
    {
        $remote = new SimpleConfig(
            [
                'host' => '127.0.0.1',
            ]
        );

        // Trigger the cache
        $this->config->get('host');
        $this->config->merge($remote);

        $this->assertSame('127.0.0.1', $this->config['host']);
    }

    /** @covers \Camoo\Config\AbstractConfig::offsetGet() */
    public function testOffsetGet(): void
    {
        $this->assertSame('localhost', $this->config['host']);
    }

    /** @covers \Camoo\Config\AbstractConfig::offsetGet() */
    public function testOffsetGetNestedKey(): void
    {
        $this->assertSame('configuration', $this->config['application.name']);
    }

    /** @covers \Camoo\Config\AbstractConfig::offsetExists() */
    public function testOffsetExists(): void
    {
        $this->assertTrue(isset($this->config['host']));
    }

    /** @covers \Camoo\Config\AbstractConfig::offsetExists() */
    public function testOffsetExistsReturnsFalseOnNonexistentKey(): void
    {
        $this->assertFalse(isset($this->config['database']));
    }

    /** @covers \Camoo\Config\AbstractConfig::offsetSet() */
    public function testOffsetSet(): void
    {
        $this->config['newkey'] = 'newvalue';
        $this->assertSame('newvalue', $this->config['newkey']);
    }

    /** @covers \Camoo\Config\AbstractConfig::offsetUnset() */
    public function testOffsetUnset(): void
    {
        unset($this->config['application']);
        $this->assertNull($this->config['application']);
    }

    /** @covers \Camoo\Config\AbstractConfig::current() */
    public function testCurrent(): void
    {
        /* Reset to the beginning of the test config */
        $this->config->rewind();
        $this->assertSame($this->config['host'], $this->config->current());

        /* Step through each of the other elements of the test config */
        $this->config->next();
        $this->assertSame($this->config['port'], $this->config->current());
        $this->config->next();
        $this->assertSame($this->config['servers'], $this->config->current());
        $this->config->next();
        $this->assertSame($this->config['application'], $this->config->current());
        $this->config->next();
        $this->assertSame($this->config['user'], $this->config->current());

        /* Step beyond the end and confirm the result */
        $this->config->next();
        $this->assertFalse($this->config->current());
    }

    /** @covers \Camoo\Config\AbstractConfig::key() */
    public function testKey(): void
    {
        /* Reset to the beginning of the test config */
        $this->config->rewind();
        $this->assertSame('host', $this->config->key());

        /* Step through each of the other elements of the test config */
        $this->config->next();
        $this->assertSame('port', $this->config->key());
        $this->config->next();
        $this->assertSame('servers', $this->config->key());
        $this->config->next();
        $this->assertSame('application', $this->config->key());
        $this->config->next();
        $this->assertSame('user', $this->config->key());

        /* Step beyond the end and confirm the result */
        $this->config->next();
        $this->assertNull($this->config->key());
    }

    /** @covers \Camoo\Config\AbstractConfig::next() */
    public function testNext(): void
    {
        /* Reset to the beginning of the test config */
        $this->config->rewind();

        /* Step through each of the other elements of the test config */
        $this->config->next();
        $this->assertSame(80, $this->config['port']);
        $this->config->next();
        $this->assertSame($this->config['servers'], ['host1', 'host2', 'host3']);
        $this->config->next();
        $this->assertSame($this->config['application'], ['name' => 'configuration', 'secret' => 's3cr3t', 'runtime' => null]);
        $this->config->next();
        $this->assertNull($this->config['user']);
    }

    /** @covers \Camoo\Config\AbstractConfig::rewind() */
    public function testRewind(): void
    {
        /* Rewind from somewhere out in the array */
        $this->config->next();
        $this->config->next();
        $this->config->rewind();
        $this->assertSame($this->config['host'], 'localhost');
        $this->config->rewind();
        /* Rewind again from the beginning of the array */
        $this->assertSame($this->config['host'], 'localhost');
    }

    /** @covers \Camoo\Config\AbstractConfig::valid() */
    public function testValid(): void
    {
        /* Reset to the beginning of the test config */
        $this->config->rewind();
        $this->assertTrue($this->config->valid());

        /* Step through each of the other elements of the test config */
        $this->config->next();
        $this->assertTrue($this->config->valid());
        $this->config->next();
        $this->assertTrue($this->config->valid());
        $this->config->next();
        $this->assertTrue($this->config->valid());
        $this->config->next();
        $this->assertTrue($this->config->valid());

        /* Step beyond the end and confirm the result */
        $this->config->next();
        $this->assertFalse($this->config->valid());
    }

    /**
     * Tests to verify that Iterator is properly implemented by using a foreach
     * loop on the test config
     *
     * @covers \Camoo\Config\Config::current()
     * @covers \Camoo\Config\Config::next()
     * @covers \Camoo\Config\Config::key()
     * @covers \Camoo\Config\Config::valid()
     * @covers \Camoo\Config\Config::rewind()
     */
    public function testIterator(): void
    {
        /* Create numerically indexed copies of the test config */
        $expectedKeys = ['host', 'port', 'servers', 'application', 'user'];
        $expectedValues = [
            'localhost',
            80,
            ['host1', 'host2', 'host3'],
            [
                'name' => 'configuration',
                'secret' => 's3cr3t',
                'runtime' => null,
            ],
            null,
        ];

        $idxConfig = 0;

        foreach ($this->config as $configKey => $configValue) {
            $this->assertSame($expectedKeys[$idxConfig], $configKey);
            $this->assertSame($expectedValues[$idxConfig], $configValue);
            $idxConfig++;
        }
    }

    /** @covers \Camoo\Config\Config::get() */
    public function testGetShouldNotSet(): void
    {
        $this->config->get('invalid', 'default');
        $actual = $this->config->get('invalid', 'expected');
        $this->assertSame('expected', $actual);
    }

    /** @covers \Camoo\Config\AbstractConfig::remove() */
    public function testRemove(): void
    {
        $this->config->remove('application');
        $this->assertNull($this->config['application']);
    }

    /**
     * @covers \Camoo\Config\AbstractConfig::next
     * @covers \Camoo\Config\AbstractConfig::rewind
     */
    public function testCannotRewindAndNextWhenDataIsEmpty(): void
    {
        $config = new class ([]) extends AbstractConfig {
        };

        $config->next();
        $config->rewind();
        $this->assertInstanceOf(AbstractConfig::class, $config);
    }
}
