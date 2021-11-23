<?php


$finder = PhpCsFixer\Finder::create()
    ->exclude('content')
    ->exclude('kirby')
    ->exclude('node_modules')
    //->exclude('site/plugins')
    ->exclude('src')
    ->exclude('tests/site/cache')
    ->exclude('vendor')
    ->in(__DIR__)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR2' => true,
    ])
    ->setFinder($finder)
    ;
