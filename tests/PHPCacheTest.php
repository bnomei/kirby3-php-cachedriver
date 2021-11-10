<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Bnomei\PHPCache;

final class PHPCacheTest extends TestCase
{
    /**
     * @var PHPCache
     */
    private PHPCache $cache;

    protected function setUp(): void
    {
        $this->cache = PHPCache::singleton();
        $this->cache->flush();
    }

    protected function tearDown(): void
    {
        //$this->cache->flush();
        //unset($this->cache);
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(PHPCache::class, $this->cache);
    }

    public function testOption()
    {
        $this->assertIsArray($this->cache->option());
        $this->assertEquals(null, $this->cache->option('debug'));
    }

    public function testFlush()
    {
        $this->assertNull($this->cache->get('something'));
    }

    public function testCacheSetGet()
    {
        $this->assertTrue($this->cache->set('some', 'value'));
        $this->assertEquals('value', $this->cache->get('some'));
    }

    public function testCacheWithWriteAndRemove()
    {
        $date = date('c');

        $this->assertTrue($this->cache->set('persist', $date));
        $this->assertNotNull($this->cache->get('persist'));
        unset($this->cache); // will happen at end of pageview

        $this->cache = PHPCache::singleton();
        $this->assertEquals($date, $this->cache->get('persist'));
        $this->assertTrue($this->cache->remove('persist', $date));
        unset($this->cache); // will happen at end of pageview

        $this->cache = PHPCache::singleton();
        $this->assertNull($this->cache->get('persist'));
    }

    public function testJSONValue()
    {
        // https://github.com/getkirby/kql
        $json = <<<JSON
        {
            "code": 200,
            "result": {
                "data": [
                    {
                        "url": "https://example.com/photography/trees",
                        "title": "Trees",
                        "text": "Lorem <strong>ipsum</strong> …",
                        "images": [
                            { "url": "https://example.com/media/pages/photography/trees/1353177920-1579007734/cheesy-autumn.jpg" },
                            { "url": "https://example.com/media/pages/photography/trees/1940579124-1579007734/last-tree-standing.jpg" },
                            { "url": "https://example.com/media/pages/photography/trees/3506294441-1579007734/monster-trees-in-the-fog.jpg" }
                        ]
                    },
                    {
                        "url": "https://example.com/photography/sky",
                        "title": "Sky",
                        "text": "<h1>Dolor sit amet</h1> …",
                        "images": [
                            { "url": "https://example.com/media/pages/photography/sky/183363500-1579007734/blood-moon.jpg" },
                            { "url": "https://example.com/media/pages/photography/sky/3904851178-1579007734/coconut-milkyway.jpg" }
                        ]
                    }
                ],
                "pagination": {
                    "page": 1,
                    "pages": 1,
                    "offset": 0,
                    "limit": 10,
                    "total": 2
                }
            },
            "status": "ok"
        }
        JSON;
        $array = json_decode($json, true);

        $this->cache->set('json_string', $json);
        $this->cache->set('json_array', $array);

        $this->assertEquals($json, $this->cache->get('json_string'));
        $this->assertEquals(
            $array['result']['data'][1]['title'],
            $this->cache->get('json_array')['result']['data'][1]['title']
        );
    }

    public function testBenchmark()
    {
        $this->cache->flush();
        $this->cache->benchmark(1000);
        unset($this->cache); // will happen at end of pageview
        $this->assertTrue(true);
    }
}
