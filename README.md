# 🐘 Kirby PHP Cache-Driver

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-php-cachedriver?color=ae81ff&icon=github&label)
[![Discord](https://flat.badgen.net/badge/discord/bnomei?color=7289da&icon=discord&label)](https://discordapp.com/users/bnomei)
[![Buymecoffee](https://flat.badgen.net/badge/icon/donate?icon=buymeacoffee&color=FF813F&label)](https://www.buymeacoffee.com/bnomei)

Elephant - a highly performant PHP Cache Driver for Kirby

## Installation

- unzip [master.zip](https://github.com/bnomei/kirby3-php-cachedriver/archive/master.zip) as folder `site/plugins/kirby3-php-cachedriver` or
- `git submodule add https://github.com/bnomei/kirby3-php-cachedriver.git site/plugins/kirby3-php-cachedriver` or
- `composer require bnomei/kirby3-php-cachedriver`

## Usage 

### Cache methods

```php
$cache = \Bnomei\PHPCache::singleton(); // or
$cache = elephant();

$cache->set('key', 'value', $expireInMinutes);
$value = elephant()->get('key', $default);

elephant()->remove('key');
elephant()->flush();
```

### Cache file(s)

All data is written at the end of the PHP script life-cycle. This does not count against your script execution time, but for example, when you change the value in the cache with each request, writing that big file every time time might prove inefficient. Furthermore, more incremental updates will be written during script execution depending on the `mono_dump` setting. Additions to the cache will also be written in temporary files to improve the stability of the cache.

### Serialization of data

This plugin defaults to a simple serialization logic, which is quick but only serializes primitive data types, closures, and objects that have a `toArray()`-method like `Kirby\Cms\Field` and `Kirby\Toolkit\Obj`. This should be enough for most use-cases.
If you need broader support set `bnomei.php-cachedriver.serialize` to `json` which will en- and decode your data as JSON before storing it. That is a bit slower but will ensure your data contains only primitive types without the hassle of serializing it manually before caching it.

### OPCache

Make sure [OPCache is configured](https://www.php.net/manual/en/opcache.configuration.php) to load the PHP files from the cache without any delay. Most probably you will have to set these values in your `user.ini` or something similar! If you do not set these values, you might have outdated data being loaded from php files cached by OPCache instead of loading the right ones you want from disk.

```shell
opcache.enable=1
opcache.enable_cli=0 # default is 0, leave it like that
opcache.validate_timestamps=1
opcache.revalidate_freq=0 # default is 2, 0 => on every request
```

> Thanks, Al, for helping me get these config values right.

### Benchmark

```php
elephant()->benchmark(1000);
```

```shell script
php : 0.03383207321167  
file : 2.3811981678009
```

> ATTENTION: This will create and remove a lot of cache files and php-cache files

### No cache when debugging

When Kirbys global debug config is set to `true` the complete plugin cache will be flushed and no caches will be read. But entries will be created.

### How to use Elephant with Lapse or Boost

You need to set the cache driver for the [lapse plugin](https://github.com/bnomei/kirby3-lapse) to `php`. Please be aware that the *mono*-mode is not suited for concurrent writes.

**site/config/config.php**
```php
<?php
return [
    'bnomei.lapse.cache' => ['type' => 'php'],
    'bnomei.boost.cache' => ['type' => 'php'],
    //... other options
];
```

### Setup Content-File Cache

Use [Kirby 3 Boost](https://github.com/bnomei/kirby3-boost) to set up a cache for content files.


## Settings

| bnomei.php-cachedriver. | Default       | Description                                  |            
|-------------------------|---------------|----------------------------------------------|
| mono_dump               | `2048`         | write to cache file every n changes          |
| check_opcache           | `true`        | check OPCache settings                       |
| serialize               | `'primitive'` | which is fastest or `'json'` for less hassle |

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-php-cachedriver/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
