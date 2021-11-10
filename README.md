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

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-php-cachedriver/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.
