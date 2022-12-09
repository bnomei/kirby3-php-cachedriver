<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Bnomei\PHPCache;
use Kirby\Cms\Dir;

final class PHPCacheTest extends TestCase
{
    protected function setUp(): void
    {
        Dir::remove(__DIR__ . '/site/cache/');
    }

    protected function tearDown(): void
    {
        Dir::remove(__DIR__ . '/site/cache/');
    }

    public function testConstruct()
    {
        $cache = new PHPCache();
        $this->assertInstanceOf(PHPCache::class, $cache);
    }

    public function testOption()
    {
        $cache = new PHPCache();
        $this->assertIsArray($cache->option());
        $this->assertEquals(null, $cache->option('debug'));
    }

    public function testFlush()
    {
        $cache = new PHPCache();
        $this->assertNull($cache->get('something'));
    }

    public function testCacheSetGet()
    {
        $cache = new PHPCache();
        $this->assertTrue($cache->set('some', 'value'));
        $this->assertEquals('value', $cache->get('some'));
    }

    public function testCacheWithWriteAndRemove()
    {
        $cache = new PHPCache();
        $date = date('c');

        $this->assertTrue($cache->set('persist', $date));
        $this->assertNotNull($cache->get('persist'));
        $cache->writeMono();

        $this->assertEquals($date, $cache->get('persist'));
        $this->assertTrue($cache->remove('persist', $date));
        $this->assertNull($cache->get('persist'));
        $cache->writeMono();

        //$this->assertNull($cache->get('persist'));
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

        $cache = new PHPCache();

        $cache->set('json_string', $json);
        $cache->set('json_array', $array);

        $this->assertEquals($json, $cache->get('json_string'));
        $this->assertEquals(
            $array['result']['data'][1]['title'],
            $cache->get('json_array')['result']['data'][1]['title']
        );
    }

    public function testBenchmarkMono()
    {
        $cache = new PHPCache();

        $cache->flush();
        $cache->benchmark(100);
        unset($cache); // will happen at end of pageview
        $this->assertTrue(true);
    }

    public function testBenchmark()
    {
        $cache = new PHPCache();

        $cache->flush();
        $cache->benchmark(100);
        unset($cache); // will happen at end of pageview
        $this->assertTrue(true);
    }

    public function testLoading()
    {
        $cache = new PHPCache();

        $this->assertNull($cache->get('loadingmono1'));
        $this->assertNull($cache->get('loadingmono2'));
        $this->assertTrue($cache->set('loadingmono1', 'does work 1'));
        $this->assertTrue($cache->set('loadingmono2', 'does work 2'));
        $this->assertEquals('does work 1', $cache->get('loadingmono1'));
        $this->assertEquals('does work 2', $cache->get('loadingmono2'));
        $this->assertTrue($cache->writeMono());

        $cache2 = new PHPCache();
        $this->assertEquals('does work 1', $cache2->get('loadingmono1'));
        $this->assertEquals('does work 2', $cache2->get('loadingmono2'));
    }

    public function testGarbageCollection()
    {
        $cache = new PHPCache();

        $this->assertNull($cache->get('loadingmono1'));
        $this->assertNull($cache->get('loadingmono2'));
        $this->assertNull($cache->get('loadingmono3'));
        $this->assertTrue($cache->set('loadingmono1', 'does work 1', 1));
        $this->assertTrue($cache->set('loadingmono2', 'does work 2', 2));
        $this->assertTrue($cache->set('loadingmono3', 'does work 3', 0));
        $this->assertEquals('does work 1', $cache->get('loadingmono1'));
        $this->assertEquals('does work 2', $cache->get('loadingmono2'));
        $this->assertEquals('does work 3', $cache->get('loadingmono3'));
        $cache->writeMono();

        sleep(61);

        $cache2 = new PHPCache();

        $this->assertNull($cache2->get('loadingmono1'));
        $this->assertNotNull($cache2->get('loadingmono2'));
        $this->assertNotNull($cache2->get('loadingmono3'));

        sleep(61);

        $cache3 = new PHPCache();

        $this->assertNull($cache3->get('loadingmono1'));
        $this->assertNull($cache3->get('loadingmono2'));
        $this->assertNotNull($cache3->get('loadingmono3'));
    }
}
