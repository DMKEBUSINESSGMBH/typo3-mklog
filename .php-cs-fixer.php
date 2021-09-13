<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('Resources')
    ->exclude('Documentation')
    ->in(__DIR__);

$config = new \PhpCsFixer\Config();

return $config
    ->setCacheFile('.Build/.php_cs.cache')
    ->setFinder($finder)
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'phpdoc_align' => false,
        'no_superfluous_phpdoc_tags' => false,
    ])
    ->setLineEnding("\n");
