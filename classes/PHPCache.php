<?php

declare(strict_types=1);

namespace Bnomei;

use Kirby\Cache\FileCache;
use Kirby\Cache\Value;
use Kirby\Cms\Dir;
use Kirby\Toolkit\A;
use Kirby\Toolkit\F;
use Kirby\Toolkit\Str;

final class PHPCache extends FileCache
{

    private $shutdownCallbacks = [];

    /** @var array $database */
    private $database;

    /** @var bool $isDirty */
    private $isDirty;

    public const DB_FILENAME = 'phpcache-mono';

    public function __construct(array $options = [])
    {
        $this->setOptions($options);

        parent::__construct($this->options);

        $this->isDirty = false;
        $this->load();
        $this->garbagecollect();

        if ($this->options['debug']) {
            $this->flush();
        }
    }

    public function __destruct()
    {
        foreach($this->shutdownCallbacks as $callback) {
            if (!is_string($callback) && is_callable($callback)) {
                $callback();
            }
        }
        $this->writeMono();
    }

    public function register_shutdown_function($callback) {
        $this->shutdownCallbacks[] = $callback;
    }

    public function garbagecollect(): bool
    {
        $count = 0;
        foreach (array_keys($this->database) as $key) {
            $expires = null;
            $data = $this->database[$key];
            if($data) {
                $expires = Value::fromArray($data)->expires();
            }
            if (!$data || ($expires && $expires < time())) {
                $this->remove($key);
                $count++;
            }
        }
        return $count > 0;
    }

    /**
     * @param string|null $key
     * @return array
     */
    public function option(?string $key = null)
    {
        if ($key) {
            return A::get($this->options, $key);
        }
        return $this->options;
    }

    private function load()
    {
        $this->database = [];

        $monoFile = $this->file(static::DB_FILENAME);
        if ($this->option('mono')) {
            $this->database = F::exists($monoFile) ? include $monoFile : [];
        } else {
            foreach (Dir::files($this->root()) as $file) {
                if (F::filename($file) !== F::filename($monoFile) &&
                    F::extension($file) === $this->option('extension')) {
                    $this->database[F::filename($file)] = include $this->root() . '/' . $file;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value, int $minutes = 0): bool
    {
        /* SHOULD SET EVEN IN DEBUG
        if ($this->option('debug')) {
            return true;
        }
        */

        return $this->removeAndSet($key, $value, $minutes);
    }

    private function removeAndSet(string $key, $value, int $minutes = 0): bool
    {
        $this->remove($key);

        $key = $this->key($key);
        $value = new Value($value, $minutes);

        // serialize
        $data = json_decode($value->toJson(), true);
        $this->database[$key] = $data;
        $this->isDirty = true;

        if (!$this->option('mono')) {
            $this->write($key, $data);
        }

        return true;
    }

    protected function file(string $key): string
    {
        $file = $this->root . '/' . md5($key);

        if (isset($this->options['extension'])) {
            return $file . '.' . $this->options['extension'];
        } else {
            return $file;
        }
    }

    private function write($key, $data): bool
    {
        $this->isDirty = false;
        return file_put_contents(
            $this->file($key),
            '<?php' . PHP_EOL .' return ' . var_export($data, true) . ';'
        ) !== false;
    }

    public function writeMono(): bool
    {
        if ($this->option('mono') && $this->isDirty) {
            $this->isDirty = false;
            return file_put_contents(
                $this->file(static::DB_FILENAME),
                '<?php' . PHP_EOL .' return ' . var_export($this->database, true) . ';'
            ) !== false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function retrieve(string $key): ?Value
    {
        $key = $this->key($key);
        $value = A::get($this->database, $key);

        return $value ? Value::fromArray($value) : null;
    }

    public function get(string $key, $default = null)
    {
        if ($this->option('debug')) {
            return $default;
        }

        $value = $this->retrieve($key);

        return $value ? $value->value() : $default;
    }

    /**
     * @inheritDoc
     */
    public function remove(string $key): bool
    {
        $key = $this->key($key);

        if (array_key_exists($key, $this->database)) {
            unset($this->database[$key]);
            $this->isDirty = true;
        }
        if (!$this->option('mono')) {
            $file = $this->file($key);
            if (F::exists($file)) {
                return unlink($file);
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function flush(): bool
    {
        $this->isDirty = true;
        return Dir::remove($this->root()) && Dir::make($this->root());
    }

    private static $singleton;
    public static function singleton(array $options = []): self
    {
        if (self::$singleton) {
            return self::$singleton;
        }
        self::$singleton = new self($options);
        return self::$singleton;
    }


    private function setOptions(array $options)
    {
        $root = null;
        $cache = kirby()->cache('bnomei.php-cachedriver');
        if (is_a($cache, FileCache::class)) {
            $root = A::get($cache->options(), 'root');
            if ($prefix =  A::get($cache->options(), 'prefix')) {
                $root .= '/' . $prefix;
            }
        } else {
            $root = kirby()->roots()->cache();
        }

        $this->options = array_merge([
            'root' => $root,
            'debug' => \option('debug'),
            'mono' => \option('bnomei.php-cachedriver.mono'),
        ], $options);

        // overwrite *.cache in all constructors
        $this->options['extension'] = 'php';
    }

    public function benchmark(int $count = 10)
    {
        $prefix = "elephant-benchmark-";
        $php = $this;
        $file = kirby()->cache('bnomei.php-cachedriver'); // neat, right? ;-)

        foreach (['php' => $php, 'file' => $file] as $label => $driver) {
            $time = microtime(true);
            for ($i = 0; $i < $count; $i++) {
                $key = $prefix . $i;
                if (!$driver->get($key)) {
                    $driver->set($key, Str::random(1000));
                }
            }
            if ($label === 'php') {
                $driver->writeMono();
            }
            for ($i = $count * 0.6; $i < $count * 0.8; $i++) {
                $key = $prefix . $i;
                $driver->remove($key);
            }
            if ($label === 'php') {
                $driver->writeMono();
            }
            for ($i = $count * 0.8; $i < $count; $i++) {
                $key = $prefix . $i;
                $driver->set($key, Str::random(1000));
            }
            if ($label === 'php') {
                $driver->writeMono();
            }
            for ($i = 0; $i < $count; $i++) {
                $key = $prefix . $i;
                if (!$driver->get($key)) {
                    // just read all again
                }
            }
            echo $label . ' : ' . (microtime(true) - $time) . PHP_EOL;
        }

        // cleanup
        for ($i = 0; $i < $count; $i++) {
            $key = $prefix . $i;
            $driver->remove($key);
        }
    }
}
