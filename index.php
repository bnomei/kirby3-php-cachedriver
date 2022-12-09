<?php

@include_once __DIR__ . '/vendor/autoload.php';

if (!class_exists('Bnomei\PHPCache')) {
    require_once __DIR__ . '/classes/PHPCache.php';
}

if (! function_exists('elephant')) {
    function elephant(array $options = [])
    {
        return \Bnomei\PHPCache::singleton($options);
    }
}

Kirby::plugin('bnomei/php-cachedriver', [
    'options' => [
        'cache' => true, // create cache folder
        'mono_dump' => 2048, // write to mono file after n values changed
        'check_opcache' => true,
        'serialize' => 'primitive', // or 'json'
    ],
    'cacheTypes' => [
        'php' => \Bnomei\PHPCache::class
    ],
]);
