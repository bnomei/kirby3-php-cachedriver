# 🐘 Kirby3 PHP Cache-Driver

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-php-cachedriver?color=ae81ff)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-php-cachedriver?color=272822)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-php-cachedriver)](https://travis-ci.com/bnomei/kirby3-php-cachedriver)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-php-cachedriver)](https://coveralls.io/github/bnomei/kirby3-php-cachedriver) 
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-php-cachedriver)](https://codeclimate.com/github/bnomei/kirby3-php-cachedriver) 
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

Elephant - a highly performant PHP Cache Driver for Kirby 3

## Commerical Usage

> <br>
><b>Support open source!</b><br><br>
> This plugin is free but if you use it in a commercial project please consider to sponsor me or make a donation.<br>
> If my work helped you to make some cash it seems fair to me that I might get a little reward as well, right?<br><br>
> Be kind. Share a little. Thanks.<br><br>
> &dash; Bruno<br>
> &nbsp; 

| M | O | N | E | Y |
|---|----|---|---|---|
| [Github sponsor](https://github.com/sponsors/bnomei) | [Patreon](https://patreon.com/bnomei) | [Buy Me a Coffee](https://buymeacoff.ee/bnomei) | [Paypal dontation](https://www.paypal.me/bnomei/15) | [Buy a Kirby license using this affiliate link](https://a.paddle.com/v2/click/1129/35731?link=1170) |

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

### Mono or multile files

With the `bnomei.php-cachedriver.mono` (default: true) setting you can change if the cache driver uses a single or multiple files to store the cached data. In either case all files will be loaded so there is no gain here. But when writing data the behaviour is different. 

In the *mono*-mode all data is written at the end of the php skript life-cycle. This does not count against your script execution time but for example when you change value in the cache with each request writing that big file everytime the time might prove inefficient. If the data in your cache changes very rarely or a lot use this behaviour.

When storing the data in one file per cache key then writing to the cache happens right when calling `$cache->set()`. This means you only write small changes and fast but it counts towards your max script execution time. This behaviour is well suited when a small amount of data changes often.

### Serialization of data

This plugin defaults to a simple serialization logic which is quick but only serializes primitive data types, closures, `Kirby\Cms\Field` and `Kirby\Toolkit\Obj`. This should be enough for must usecases.
If your need broader support set `bnomei.php-cachedriver.serialize` to `json` which will en- and decode your data as json before storing it. That make is a tick slower but will ensure your data are only primitive types as well without the hassle of serializing it manually before caching it.

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

When Kirbys global debug config is set to `true` the complete plugin cache will be flushed and no caches will be read. But entries will be created. This will make you live easier – trust me.

### How to use Elephant with Lapse or Boost

You need to set the cache driver for the [lapse plugin](https://github.com/bnomei/kirby3-lapse) to `php`.

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

Use [Kirby 3 Boost](https://github.com/bnomei/kirby3-boost) to setup a cache for content files.


## Settings

| bnomei.php-cachedriver.            | Default        | Description               |            
|---------------------------|----------------|---------------------------|
| mono | `true` | use a single file instead of one for each key  |
| serialize | `'primitive'` | which is fastest or `'json'` for less hassle |

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-php-cachedriver/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
